<?php
/*
 * Plugin Name: CoCart Lite
 * Plugin URI:  https://cocart.xyz
 * Description: CoCart is a <strong>REST API for WooCommerce</strong>. It focuses on <strong>the front-end</strong> of the store to manage the shopping cart allowing developers to build a headless store.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     2.9.0-RC.2
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.3
 * Requires PHP: 7.0
 * WC requires at least: 4.3
 * WC tested up to: 4.9
 *
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

// Include the main CoCart class.
if ( ! class_exists( 'CoCart', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/class-cocart.php';
}

/**
 * Returns the main instance of CoCart and only runs if it does not already exists.
 *
 * @since   2.1.0
 * @version 2.9.0
 * @return CoCart
 */
if ( ! function_exists( 'CoCart' ) ) {
	function CoCart() {
		return CoCart::init();
	}

	CoCart();

	/**
	 * Load backend features only if COCART_WHITE_LABEL constant is
	 * NOT set or IS set to false in user's wp-config.php file.
	 */
	if (
		! defined( 'COCART_WHITE_LABEL' ) || false === COCART_WHITE_LABEL &&
		is_admin() || ( defined( 'WP_CLI' ) && WP_CLI )
	) {
		include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/admin/class-cocart-admin.php';
	} else {
		include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/admin/class-cocart-wc-admin-system-status.php';
	}
}
