<?php
/**
 * Plugin Name: Beautia Core
 * Plugin URI: https://beautia.local/
 * Description: Core functionality, booking engine, Elementor Free widgets and portable demo packages for the Beautia theme.
 * Version: 4.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: Beautia Studio
 * Text Domain: beautia-core
 * Domain Path: /languages
 * License: GPL-2.0-or-later
 */

defined( 'ABSPATH' ) || exit;

define( 'BEAUTIA_CORE_VERSION', '4.0.0' );
define( 'BEAUTIA_CORE_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'BEAUTIA_CORE_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Load the reusable product layer after the active theme has registered its
 * visual helpers. The plugin owns data and business logic; the theme owns UI.
 */
function beautia_core_load() {
	if ( ! defined( 'BEAUTIA_DIR' ) ) {
		add_action(
			'admin_notices',
			static function() {
				echo '<div class="notice notice-warning"><p>' . esc_html__( 'Beautia Core is active, but the Beautia theme is not active.', 'beautia-core' ) . '</p></div>';
			}
		);
		return;
	}
	// Activation happens during an already-booted request. In that one request
	// the theme compatibility layer may already own the classes; avoid a second
	// declaration and let Core take ownership from the next request onward.
	if ( class_exists( 'Beautia_CPT', false ) ) {
		if ( ! class_exists( 'Beautia_Core_Demo_Packages', false ) ) {
			require_once BEAUTIA_CORE_DIR . 'includes/class-beautia-core-demo-packages.php';
			Beautia_Core_Demo_Packages::boot();
		}
		return;
	}

	$files = array(
		'class-beautia-cpt.php',
		'class-beautia-metaboxes.php',
		'class-beautia-sms.php',
		'class-beautia-otp.php',
		'class-beautia-booking.php',
		'class-beautia-account.php',
		'class-beautia-rest.php',
		'class-beautia-admin.php',
		'class-beautia-demo-importer.php',
		'shortcodes.php',
		'shortcodes-plus.php',
		'blocks.php',
		'class-beautia-elementor.php',
		'live-chat.php',
		'class-beautia-core-demo-packages.php',
	);
	foreach ( $files as $file ) {
		require_once BEAUTIA_CORE_DIR . 'includes/' . $file;
	}
	Beautia_Core_Demo_Packages::boot();
}
add_action( 'after_setup_theme', 'beautia_core_load', 1 );

/** Install persistent data tables when the core plugin is activated. */
function beautia_core_activate() {
	if ( did_action( 'after_setup_theme' ) ) {
		beautia_core_load();
	}
	if ( class_exists( 'Beautia_Booking' ) ) {
		Beautia_Booking::install();
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'beautia_core_activate' );

register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );
