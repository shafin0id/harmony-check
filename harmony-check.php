<?php
/**
 * Plugin Name: Harmony Check
 * Plugin URI: https://github.com/shafinoid/harmony-check
 * Description: Quietly monitors your plugin setup for common conflicts and compatibility issues based on real support experience.
 * Version: 1.1.1
 * Author: Shafinoid
 * Author URI: https://shafinoid.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: harmony-check
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

namespace HarmonyCheck;

// Don't allow direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'HARMONY_CHECK_VERSION', '1.0.0' );
define( 'HARMONY_CHECK_FILE', __FILE__ );
define( 'HARMONY_CHECK_PATH', plugin_dir_path( __FILE__ ) );
define( 'HARMONY_CHECK_URL', plugin_dir_url( __FILE__ ) );

// Autoload classes
spl_autoload_register( function ( $class ) {
	// Only autoload our namespace
	if ( strpos( $class, 'HarmonyCheck\\' ) !== 0 ) {
		return;
	}

	$class_name = str_replace( 'HarmonyCheck\\', '', $class );
	$class_name = str_replace( '\\', DIRECTORY_SEPARATOR, $class_name );
	$file       = HARMONY_CHECK_PATH . 'includes/' . $class_name . '.php';

	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * Initialize the plugin
 * 
 * Only runs in admin because this plugin has no frontend impact
 */
function init() {
	if ( ! is_admin() ) {
		return;
	}

	$plugin = Plugin::instance();
	$plugin->init();
}

add_action( 'plugins_loaded', __NAMESPACE__ . '\\init' );

/**
 * Activation hook
 * 
 * Just sets a transient so we can show a welcome notice
 */
function activate() {
	set_transient( 'harmony_check_activated', true, 60 );
}

register_activation_hook( __FILE__, __NAMESPACE__ . '\\activate' );
