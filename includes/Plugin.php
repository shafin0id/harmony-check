<?php
namespace HarmonyCheck;

/**
 * Main plugin orchestrator
 * 
 * This is intentionally simple - it just wires up the pieces
 */
class Plugin {

	private static $instance = null;
	private $detector;
	private $admin_page;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		// Singleton
	}

	public function init() {
		$this->detector   = new ConflictDetector();
		$this->admin_page = new AdminPage( $this->detector );

		add_action( 'admin_notices', [ $this, 'show_notices' ] );
		add_action( 'admin_init', [ $this, 'handle_notice_dismissal' ] );
		add_action( 'admin_init', [ $this, 'show_activation_notice' ] );
	}

	/**
	 * Show conflict notices to admins
	 */
	public function show_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$conflicts = $this->detector->get_active_conflicts();

		if ( empty( $conflicts ) ) {
			return;
		}

		foreach ( $conflicts as $conflict ) {
			$dismissed = get_option( 'harmony_check_dismissed_' . $conflict['id'], false );

			if ( $dismissed ) {
				continue;
			}

			$this->render_conflict_notice( $conflict );
		}
	}

	/**
	 * Render a single conflict notice
	 */
	private function render_conflict_notice( $conflict ) {
		$notice_id = 'harmony-check-' . esc_attr( $conflict['id'] );
		?>
		<div class="notice notice-warning is-dismissible" id="<?php echo $notice_id; ?>">
			<p>
				<strong><?php echo esc_html( $conflict['title'] ); ?></strong>
			</p>
			<p><?php echo esc_html( $conflict['message'] ); ?></p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'tools.php?page=harmony-check' ) ); ?>">
					View details
				</a>
				|
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?harmony_check_dismiss=' . $conflict['id'] ), 'harmony_check_dismiss' ) ); ?>">
					Dismiss this notice
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle manual dismissal
	 */
	public function handle_notice_dismissal() {
		if ( ! isset( $_GET['harmony_check_dismiss'] ) || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer( 'harmony_check_dismiss' );

		$conflict_id = sanitize_text_field( $_GET['harmony_check_dismiss'] );
		update_option( 'harmony_check_dismissed_' . $conflict_id, true );

		wp_safe_redirect( remove_query_arg( [ 'harmony_check_dismiss', '_wpnonce' ] ) );
		exit;
	}

	/**
	 * Show a friendly notice on activation
	 */
	public function show_activation_notice() {
		if ( ! get_transient( 'harmony_check_activated' ) ) {
			return;
		}

		delete_transient( 'harmony_check_activated' );

		add_action( 'admin_notices', function() {
			?>
			<div class="notice notice-success is-dismissible">
				<p>
					<strong>Harmony Check is now active.</strong> 
					It's quietly monitoring your plugin setup. 
					<a href="<?php echo esc_url( admin_url( 'tools.php?page=harmony-check' ) ); ?>">View report</a>
				</p>
			</div>
			<?php
		} );
	}
}
