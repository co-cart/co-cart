<?php
/**
 * Plugin Name: CoCart Lite
 * Plugin URI:  https://cocart.xyz
 * Description: A <strong>RESTful API</strong> made for <strong>WooCommerce</strong>, focusing on <strong>the front-end</strong> of the store helping you to manage shopping carts and allows developers to build a <strong>headless store</strong>.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     4.0.0-alpha.1
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 4.3
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
