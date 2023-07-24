<?php
/**
 * Plugin Name: CoCart - Decoupling WooCommerce Made Easy
 * Plugin URI:  https://cocart.xyz
 * Description: CoCart brings everything you need to build fast and flexible headless stores.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     4.0.0-beta.1
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * WC requires at least: 6.9
 * WC tested up to: 7.9
 *
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

// Load core packages and the autoloader.
require __DIR__ . '/src/autoloader.php';
require __DIR__ . '/src/packages.php';

if ( ! CoCart\Autoloader::init() ) {
	error_log( 'CoCart Autoloader not found. Please run "npm install" followed by "composer install".' );
	return;
}

CoCart\Packages::init();

// Include the main CoCart class.
if ( ! class_exists( 'CoCart\Core', false ) ) {
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
		return CoCart\Core::init();
	}

	CoCart();
}
