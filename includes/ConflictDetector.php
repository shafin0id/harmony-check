<?php
namespace HarmonyCheck;

/**
 * Detects known plugin conflicts
 * 
 * Conflict definitions are based on real support patterns, not theory.
 * This list is intentionally small to start - you can add more over time.
 */
class ConflictDetector {

	private $active_plugins = [];
	private $conflict_rules = [];

	public function __construct() {
		$this->active_plugins = $this->get_active_plugin_slugs();
		$this->conflict_rules = $this->define_conflict_rules();
	}

	/**
	 * Get list of active plugin slugs
	 */
	private function get_active_plugin_slugs() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active = get_option( 'active_plugins', [] );
		$slugs  = [];

		foreach ( $active as $plugin_path ) {
			// Extract slug from path like "plugin-folder/plugin-file.php"
			$slug = dirname( $plugin_path );
			if ( '.' === $slug ) {
				$slug = basename( $plugin_path, '.php' );
			}
			$slugs[] = $slug;
		}

		return $slugs;
	}

	/**
	 * Define conflict rules based on real-world experience
	 * 
	 * Each rule has:
	 * - id: unique identifier
	 * - title: short heading
	 * - plugins: array of plugin slugs that trigger this
	 * - message: calm explanation of what might go wrong
	 * - severity: info|warning|critical (future use)
	 */
	private function define_conflict_rules() {
		return [
			[
				'id'       => 'multiple-caching',
				'title'    => 'Multiple caching plugins detected',
				'plugins'  => [ 'wp-super-cache', 'w3-total-cache', 'wp-rocket', 'litespeed-cache' ],
				'min'      => 2,
				'message'  => 'You have more than one caching plugin active. This can cause unpredictable behavior like stale content, broken styles, or duplicate cache layers. Most sites only need one.',
				'severity' => 'warning',
			],
			[
				'id'       => 'multiple-seo',
				'title'    => 'Multiple SEO plugins active',
				'plugins'  => [ 'wordpress-seo', 'all-in-one-seo-pack', 'seo-by-rank-math', 'the-seo-framework' ],
				'min'      => 2,
				'message'  => 'More than one SEO plugin is active. This can lead to duplicate meta tags, conflicting sitemaps, and confusing search engines. Pick one and stick with it.',
				'severity' => 'warning',
			],
			[
				'id'       => 'elementor-divi',
				'title'    => 'Elementor and Divi both active',
				'plugins'  => [ 'elementor', 'divi-builder' ],
				'min'      => 2,
				'message'  => 'Both Elementor and Divi are page builders that load significant CSS and JS. Running both can slow your site and cause layout issues. Consider using just one.',
				'severity' => 'info',
			],
			[
				'id'       => 'woocommerce-security-plugins',
				'title'    => 'WooCommerce + aggressive security plugin',
				'plugins'  => [ 'woocommerce', 'wordfence' ],
				'min'      => 2,
				'message'  => 'WooCommerce checkout sometimes conflicts with strict firewall rules in security plugins. If customers report failed checkouts, try temporarily disabling firewall features.',
				'severity' => 'info',
			],
			[
				'id'       => 'jetpack-standalone-features',
				'title'    => 'Jetpack might be duplicating features',
				'plugins'  => [ 'jetpack', 'akismet', 'wordpress-seo' ],
				'min'      => 2,
				'message'  => 'Jetpack includes spam protection and some SEO features. If you\'re using standalone plugins for these, you might have duplicate functionality running.',
				'severity' => 'info',
			],
			[
				'id'       => 'contact-form-overload',
				'title'    => 'Multiple contact form plugins',
				'plugins'  => [ 'contact-form-7', 'wpforms-lite', 'ninja-forms', 'formidable' ],
				'min'      => 2,
				'message'  => 'You have multiple contact form plugins installed. Each one loads scripts and styles, which can slow down your pages even if you\'re only using one.',
				'severity' => 'info',
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
				];

				// Log if debugging is enabled
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
					error_log( sprintf(
						'Harmony Check: Detected conflict "%s" with plugins: %s',
						$rule['id'],
						implode( ', ', $matches )
					) );
				}
			}
		}

		return $found;
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
		$result         = [];

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
