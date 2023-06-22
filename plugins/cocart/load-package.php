<?php
/**
 * This file is designed to be used to load as package NOT a WP plugin!
 *
 * @version 4.0.0-beta.1
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( version_compare( PHP_VERSION, '7.3', '<' ) ) {
	return;
}

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

// Load the autoloader.
require __DIR__ . '/src/autoloader.php';

if ( ! CoCart\Autoloader::init() ) {
	return;
}

// Include the main CoCart class.
if ( ! class_exists( 'CoCart\Core', false ) ) {
	include_once untrailingslashit( plugin_dir_path( COCART_FILE ) ) . '/includes/classes/class-cocart.php';
}

/**
 * Returns the main instance of CoCart and only runs if it does not already exists.
 *
 * @since   2.1.0
 * @version 4.0.0
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
