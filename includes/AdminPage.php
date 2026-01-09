<?php
namespace HarmonyCheck;

/**
 * Admin page showing detected conflicts and site status
 */
class AdminPage {

	private $detector;

	public function __construct( ConflictDetector $detector ) {
		$this->detector = $detector;

		add_action( 'admin_menu', [ $this, 'register_menu' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Register the admin menu
	 */
	public function register_menu() {
		add_menu_page(
			__( 'Harmony Check', 'harmony-check' ),        // Page title
			__( 'Harmony Check', 'harmony-check' ),        // Menu title
			'manage_options',                               // Capability
			'harmony-check',                                // Menu slug
			[ $this, 'render_page' ],                      // Callback function
			'dashicons-admin-tools',                        // Icon (diagnostic/monitoring tool)
			80                                              // Position
		);
	}

	/**
	 * Load minimal CSS for the admin page
	 */
	public function enqueue_styles( $hook ) {
		if ( 'toplevel_page_harmony-check' !== $hook ) {
			return;
		}

		wp_add_inline_style( 'common', $this->get_inline_css() );
	}

	/**
	 * Simple inline CSS - no need for a separate file
	 */
	private function get_inline_css() {
		return '
			.harmony-check-container {
				max-width: 900px;
				margin: 20px 0;
			}
			.harmony-conflict-card {
				background: #fff;
				border-left: 4px solid #f0b849;
				padding: 20px;
				margin-bottom: 20px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			.harmony-conflict-card.severity-critical {
				border-left-color: #dc3232;
			}
			.harmony-conflict-card.severity-info {
				border-left-color: #2271b1;
			}
			.harmony-conflict-card h3 {
				margin-top: 0;
			}
			.harmony-detected-plugins {
				background: #f6f7f7;
				padding: 10px;
				border-radius: 3px;
				margin: 10px 0;
			}
			.harmony-detected-plugins code {
				background: #fff;
				padding: 2px 6px;
				border-radius: 2px;
				margin-right: 5px;
			}
			.harmony-all-clear {
				background: #edfaef;
				border-left: 4px solid #46b450;
				padding: 20px;
				margin-bottom: 20px;
			}
			.harmony-stats {
				background: #f6f7f7;
				padding: 15px;
				border-radius: 3px;
				margin-top: 20px;
			}
		';
	}

	/**
	 * Render the main admin page
	 */
	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have permission to access this page.' );
		}

		$conflicts      = $this->detector->get_active_conflicts();
		$active_plugins = $this->detector->get_all_active_plugins();
		$rule_count     = $this->detector->get_rule_count();

		?>
		<div class="wrap">
			<h1>Harmony Check</h1>
			<p>This plugin monitors your setup for common plugin conflicts based on real support experience.</p>

			<div class="harmony-check-container">
				<?php if ( empty( $conflicts ) ) : ?>
					<div class="harmony-all-clear">
						<h2 style="margin-top: 0;">✓ All clear</h2>
						<p>No common conflicts detected in your current plugin setup. This doesn't guarantee everything is working perfectly, but it's a good sign.</p>
					</div>
				<?php else : ?>
					<h2>Detected Issues</h2>
					<?php foreach ( $conflicts as $conflict ) : ?>
						<div class="harmony-conflict-card severity-<?php echo esc_attr( $conflict['severity'] ); ?>">
							<h3><?php echo esc_html( $conflict['title'] ); ?></h3>
							<p><?php echo esc_html( $conflict['message'] ); ?></p>

							<div class="harmony-detected-plugins">
								<strong><?php echo isset( $conflict['type'] ) && $conflict['type'] === 'log_error' ? 'Source:' : 'Detected plugins:'; ?></strong><br>
								<?php foreach ( $conflict['detected'] as $slug ) : ?>
									<code><?php echo esc_html( $slug ); ?></code>
								<?php endforeach; ?>
							</div>

							<?php if ( isset( $conflict['type'] ) && $conflict['type'] === 'plugin_conflict' ) : ?>
								<p><em>What to do:</em> Review whether you actually need all of these plugins. If possible, consolidate to just one. If you need multiple plugins for different reasons, monitor your site for issues.</p>
							<?php elseif ( isset( $conflict['type'] ) && $conflict['type'] === 'log_error' ) : ?>
								<p><em>What to do:</em> Check your debug.log file for the full error details. This may require reviewing recent plugin updates or changes to your site.</p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				<?php endif; ?>

				<hr>

				<h2>Your Active Plugins</h2>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th>Plugin Name</th>
							<th>Version</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $active_plugins as $plugin ) : ?>
							<tr>
								<td><?php echo esc_html( $plugin['name'] ); ?></td>
								<td><?php echo esc_html( $plugin['version'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="harmony-stats">
					<strong>Stats:</strong> 
					Currently monitoring <?php echo esc_html( $rule_count ); ?> conflict patterns. 
					Found <?php echo esc_html( count( $conflicts ) ); ?> potential issues.
				</div>

				<hr>

				<h2>About This Plugin</h2>
				<p>Harmony Check doesn't automatically disable plugins or modify your site. It just points out common patterns that often cause problems in real WordPress support scenarios.</p>
				<p>The conflict definitions are based on years of troubleshooting experience, not academic theory. That said, your mileage may vary - not every combination listed here will definitely cause problems on your specific site.</p>
				<p><strong>Known limitations:</strong></p>
				<ul>
					<li>Only checks for active plugins, not themes or must-use plugins</li>
					<li>Can't detect custom conflicts specific to your setup</li>
					<li>Won't catch new issues that haven't been added to the ruleset yet</li>
				</ul>
			</div>
		</div>
		<?php
	}
}
