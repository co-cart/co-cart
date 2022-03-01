<?php
/**
 * Plugin Name: CoCart Lite
 * Plugin URI:  https://cocart.xyz
 * Description: Customizable REST API for WooCommerce that lets you build headless ecommerce using your favorite technologies.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     4.0.0-alpha.1
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 6.2
 *
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

// Package loader.
require __DIR__ . '/src/packages.php';

\CoCart\CoCart\Packages::init();

// Include the main CoCart class.
if ( ! class_exists( 'CoCart\CoCart\Core', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/classes/class-cocart.php';
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
		return \CoCart\CoCart\Core::init();
	}

	CoCart();
}
