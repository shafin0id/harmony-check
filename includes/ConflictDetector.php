<?php
namespace HarmonyCheck;

/**
 * Detects known plugin conflicts and analyzes error logs.
 *
 * Author: Shafinoid
 *
 * PHILOSOPHY:
 * We don't flag "theoretical" conflicts. We flag combinations that
 * actively cause white screens, slow queries, or support tickets.
 */
class ConflictDetector {

    private $active_plugins = [];
    private $conflict_rules = [];

    public function __construct() {
        $this->active_plugins = $this->get_active_plugin_slugs();
        $this->conflict_rules = $this->define_conflict_rules();
    }

    /**
     * Get list of active plugin slugs.
     * Handles both standard plugins and network active plugins (for Multisite).
     */
    private function get_active_plugin_slugs() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Get standard active plugins
        $active = get_option( 'active_plugins', [] );

        // If multisite, merge with network active plugins
        if ( is_multisite() ) {
            $network_active = get_site_option( 'active_sitewide_plugins', [] );
            if ( ! empty( $network_active ) ) {
                $active = array_merge( $active, array_keys( $network_active ) );
            }
        }

        $slugs = [];

        foreach ( $active as $plugin_path ) {
            // Extract slug from path like "plugin-folder/plugin-file.php"
            $slug = dirname( $plugin_path );
            if ( '.' === $slug ) {
                $slug = basename( $plugin_path, '.php' );
            }
            $slugs[] = $slug;
        }

        return array_unique( $slugs );
    }

    /**
     * Define conflict rules based on real-world experience.
     *
     * Categories:
     * - Performance (Caching/Minification)
     * - SEO & Analytics
     * - Security & SSL
     * - E-commerce
     * - Page Builders
     * - Email/SMTP
     * - Backups
     */
    private function define_conflict_rules() {
        return [
            // --- PERFORMANCE & CACHING (The most common issue) ---
            [
                'id'       => 'multiple-caching',
                'title'    => 'Multiple Page Caching Plugins',
                'plugins'  => [ 'wp-super-cache', 'w3-total-cache', 'wp-rocket', 'litespeed-cache', 'comet-cache', 'hyper-cache' ],
                'min'      => 2,
                'message'  => 'Running multiple page caching plugins causes "race conditions" where one plugin caches the other\'s cache. This breaks layout and prevents updates from showing.',
                'severity' => 'critical',
            ],
            [
                'id'       => 'multiple-minification',
                'title'    => 'Double Minification (CSS/JS)',
                'plugins'  => [ 'autoptimize', 'fast-velocity-minify', 'w3-total-cache', 'wp-rocket', 'sg-optimizer' ],
                'min'      => 2,
                'message'  => 'Minifying CSS/JS twice usually results in JavaScript errors (undefined variables) or broken site layouts. Choose one plugin to handle minification.',
                'severity' => 'warning',
            ],
            [
                'id'       => 'image-optimization-overkill',
                'title'    => 'Multiple Image Optimizers',
                'plugins'  => [ 'smush', 'imagify', 'ewww-image-optimizer', 'shortpixel-image-optimiser', 'tiny-compress-images' ],
                'min'      => 2,
                'message'  => 'You have multiple plugins trying to compress images on upload. This wastes server resources and can cause timeouts during media uploads.',
                'severity' => 'info',
            ],

            // --- SEO & ANALYTICS ---
            [
                'id'       => 'multiple-seo',
                'title'    => 'SEO Plugin Conflict',
                'plugins'  => [ 'wordpress-seo', 'all-in-one-seo-pack', 'seo-by-rank-math', 'the-seo-framework', 'smartcrawl-seo' ],
                'min'      => 2,
                'message'  => 'Multiple SEO plugins will output duplicate meta tags (title, description, canonical). Google hates this. Stick to one.',
                'severity' => 'critical',
            ],
            [
                'id'       => 'multiple-analytics',
                'title'    => 'Double Analytics Tracking',
                'plugins'  => [ 'google-analytics-for-wordpress', 'google-site-kit', 'ga-google-analytics', 'matomo' ],
                'min'      => 2,
                'message'  => 'You might be double-counting your visitors. Check if multiple plugins are inserting the same Google Analytics ID.',
                'severity' => 'info',
            ],

            // --- SECURITY & SSL ---
            [
                'id'       => 'ssl-conflict',
                'title'    => 'HTTPS Enforcement Conflict',
                'plugins'  => [ 'really-simple-ssl', 'wordfence', 'better-wp-security', 'sg-optimizer' ],
                'min'      => 2,
                'message'  => 'Multiple plugins forcing HTTPS redirects can cause a "Too Many Redirects" error loop, locking you out of the site.',
                'severity' => 'warning',
            ],
            [
                'id'       => 'multiple-firewalls',
                'title'    => 'Multiple Firewalls (WAF)',
                'plugins'  => [ 'wordfence', 'better-wp-security', 'all-in-one-wp-security-and-firewall', 'ninja-firewall' ],
                'min'      => 2,
                'message'  => 'Firewalls are heavy. Running two (like Wordfence + iThemes) consumes massive memory and often blocks valid admin actions.',
                'severity' => 'warning',
            ],

            // --- PAGE BUILDERS ---
            [
                'id'       => 'builder-bloat',
                'title'    => 'Multiple Heavy Page Builders',
                'plugins'  => [ 'elementor', 'divi-builder', 'beaver-builder-lite-version', 'oxygen' ],
                'min'      => 2,
                'message'  => 'Page builders load heavy assets. Using Elementor and Divi together is rarely necessary and significantly hurts frontend performance (CWV).',
                'severity' => 'info',
            ],

            // --- BACKUP ---
            [
                'id'       => 'backup-battle',
                'title'    => 'Multiple Backup Solutions',
                'plugins'  => [ 'updraftplus', 'backwpup', 'backupwordpress', 'duplicator' ],
                'min'      => 2,
                'message'  => 'Backup plugins use intense server resources (zipping files). If two run at the same time, your server will likely crash or timeout.',
                'severity' => 'warning',
            ],

            // --- EMAIL (SMTP) ---
            [
                'id'       => 'smtp-conflict',
                'title'    => 'Multiple SMTP Plugins',
                'plugins'  => [ 'wp-mail-smtp', 'post-smtp', 'easy-wp-smtp', 'fluent-smtp' ],
                'min'      => 2,
                'message'  => 'Only one plugin can handle email delivery. Having two active usually means one overrides the other, or emails fail silently.',
                'severity' => 'critical',
            ],

            // --- SPECIFIC KNOWN BAD COMBOS ---
            [
                'id'       => 'woo-paypal-conflict',
                'title'    => 'WooCommerce PayPal Conflict',
                'plugins'  => [ 'woocommerce-gateway-paypal-express-checkout', 'woocommerce-paypal-payments' ],
                'min'      => 2,
                'message'  => 'You have the old PayPal Express plugin and the new PayPal Payments plugin active. This often breaks the checkout flow.',
                'severity' => 'warning',
            ],
            [
                'id'       => 'jetpack-standalone',
                'title'    => 'Jetpack + Standalone Duplicates',
                'plugins'  => [ 'jetpack', 'akismet', 'vaultpress' ],
                'min'      => 2,
                'message'  => 'Jetpack already includes Akismet (spam) and VaultPress (backup) features. Ensure you aren\'t paying for or running standalone versions unnecessarily.',
                'severity' => 'info',
            ],
            
            // --- DATABASE & OBJECT CACHE ---
            [
                'id'       => 'redis-memcached',
                'title'    => 'Redis and Memcached Object Cache',
                'plugins'  => [ 'redis-cache', 'memcached-is-your-friend' ],
                'min'      => 2,
                'message'  => 'You should use either Redis OR Memcached, not both. This creates a confused object cache drop-in file.',
                'severity' => 'critical',
            ],
        ];
    }

    /**
     * Get conflicts that are currently active
     */
    public function get_active_conflicts() {
        $found = [];

        foreach ( $this->conflict_rules as $rule ) {
            $matches = array_intersect( $rule['plugins'], $this->active_plugins );
            $count   = count( $matches );

            $min = isset( $rule['min'] ) ? $rule['min'] : count( $rule['plugins'] );

            if ( $count >= $min ) {
                $found[] = [
                    'id'         => $rule['id'],
                    'title'      => $rule['title'],
                    'message'    => $rule['message'],
                    'severity'   => $rule['severity'],
                    'detected'   => $matches,
                    'type'       => 'plugin_conflict'
                ];

                $this->log_conflict( $rule['id'], $matches );
            }
        }

        // Also check debug log for recent fatal errors
        $log_issues = $this->analyze_debug_log();
        if ( ! empty( $log_issues ) ) {
            $found = array_merge( $found, $log_issues );
        }

        return $found;
    }

    /**
     * Analyzes the WP debug.log for recent fatal errors.
     * * Reads only the end of the file to prevent memory exhaustion
     * on large log files.
     */
    private function analyze_debug_log() {
        if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
            return [];
        }

        $log_path = defined( 'WP_DEBUG_LOG' ) && is_string( WP_DEBUG_LOG ) ? WP_DEBUG_LOG : WP_CONTENT_DIR . '/debug.log';

        if ( ! file_exists( $log_path ) || ! is_readable( $log_path ) ) {
            return [];
        }

        // Don't read empty files
        if ( filesize( $log_path ) === 0 ) {
            return [];
        }

        $errors_found = [];
        
        // Read last 10KB of the log file
        $content = $this->tail_custom( $log_path, 10240 ); 
        $lines   = explode( "\n", $content );

        foreach ( $lines as $line ) {
            // We only care about Fatal Errors and Parse Errors for the "calm" summary
            if ( stripos( $line, 'PHP Fatal error' ) !== false || stripos( $line, 'PHP Parse error' ) !== false ) {
                
                // Clean up timestamp for display
                $clean_msg = preg_replace( '/^\[[^\]]*\]\s*/', '', $line );
                
                // Avoid duplicates in the UI
                $hash = md5( $clean_msg );
                
                $errors_found[ $hash ] = [
                    'id'       => 'log-fatal-error',
                    'title'    => 'Recent Fatal Error Detected',
                    'message'  => 'Your debug.log contains a recent fatal error: ' . substr( $clean_msg, 0, 150 ) . '...',
                    'severity' => 'critical',
                    'detected' => [ 'debug.log' ],
                    'type'     => 'log_error'
                ];
            }
            
            // Check for database connection errors
            if ( stripos( $line, 'WordPress database error' ) !== false ) {
                 $hash = md5( 'db_error' ); // Group all DB errors
                 $errors_found[ $hash ] = [
                    'id'       => 'log-db-error',
                    'title'    => 'Database Errors Detected',
                    'message'  => 'Your logs show database errors. This might indicate a broken query from a plugin or a crashed table.',
                    'severity' => 'warning',
                    'detected' => [ 'debug.log' ],
                    'type'     => 'log_error'
                 ];
            }
        }

        return array_values( $errors_found );
    }

    /**
     * Efficiently read the end of a file.
     */
    private function tail_custom( $filepath, $lines = 10240 ) {
        $f = @fopen( $filepath, "rb" );
        if ( $f === false ) return "";

        fseek( $f, -1, SEEK_END );
        if ( ftell( $f ) > $lines ) {
            fseek( $f, -$lines, SEEK_END );
        } else {
            rewind( $f );
        }
        
        $buffer = fread( $f, $lines );
        fclose( $f );
        
        return $buffer;
    }

    /**
     * Helper to log findings
     */
    private function log_conflict( $id, $matches ) {
        if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
            // Check if we already logged this recently to avoid spamming the log
            // (In a real scenario we'd use a transient, but for simplicity we just log)
            error_log( sprintf(
                'Harmony Check: Conflict "%s" found with: %s',
                $id,
                implode( ', ', $matches )
            ) );
        }
    }

    /**
     * Get all active plugins (for display purposes)
     */
    public function get_all_active_plugins() {
        if ( ! function_exists( 'get_plugins' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $all_plugins    = get_plugins();
        $active_plugins = get_option( 'active_plugins', [] );
        
        // Include network-activated plugins for multisite
        if ( is_multisite() ) {
            $network_active = get_site_option( 'active_sitewide_plugins', [] );
            if ( ! empty( $network_active ) ) {
                $active_plugins = array_merge( $active_plugins, array_keys( $network_active ) );
            }
        }
        
        $result = [];

        foreach ( $active_plugins as $plugin_path ) {
            if ( isset( $all_plugins[ $plugin_path ] ) ) {
                $result[] = [
                    'path' => $plugin_path,
                    'name' => $all_plugins[ $plugin_path ]['Name'],
                    'version' => $all_plugins[ $plugin_path ]['Version'],
                ];
            }
        }

        return $result;
    }

    /**
     * Get count of defined rules
     */
    public function get_rule_count() {
        return count( $this->conflict_rules );
    }
}
