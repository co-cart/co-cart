<?php
/**
 * Plugin Name: CoCart API - Decoupling Made Easy for WooCommerce
 * Plugin URI:  https://cocartapi.com
 * Description: Decouple your WooCommerce store with ease with our developer friendly REST API extension.
 * Author:      CoCart Headless, LLC
 * Author URI:  https://cocartapi.com
 * Version:     4.3.6
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.6
 * Tested up to: 6.6
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 *
 * Copyright:   CoCart Headless, LLC
 * License:     GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
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
	function CoCart() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return CoCart::init();
	}

	CoCart();
}
