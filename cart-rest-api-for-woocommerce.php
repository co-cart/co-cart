<?php
/**
 * Plugin Name: CoCart - Headless ecommerce
 * Plugin URI:  https://cocart.xyz
 * Description: Customizable REST API for WooCommerce that lets you build headless ecommerce using your favorite technologies.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     3.7.11
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.3
 * WC requires at least: 4.3
 * WC tested up to: 7.3
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
 * @version 3.0.7
 * @return CoCart
 */
if ( ! function_exists( 'CoCart' ) ) {
	/**
	 * Initialize CoCart.
	 */
	function CoCart() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
		return CoCart::init();
	}

	CoCart();
}
