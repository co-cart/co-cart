<?php
/**
 * This file is designed to be used to load as package NOT a WP plugin!
 *
 * @version 5.0.0-beta.10
 * @package CoCart
 */

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'COCART_FILE' ) ) {
	define( 'COCART_FILE', __FILE__ );
}

if ( ! defined( 'COCART_CORE_FILE' ) ) {
	define( 'COCART_CORE_FILE', __FILE__ );
}

if ( ! defined( 'COCART_SLUG' ) ) {
	define( 'COCART_SLUG', 'cocart-core' );
}

require_once untrailingslashit( __DIR__ ) . '/switch-core-cocart.php';

require_once untrailingslashit( __DIR__ ) . '/class-cocart-integrity-check.php';
// Include the main CoCart class.
if ( ! class_exists( 'CoCart', false ) ) {
	include_once untrailingslashit( __DIR__ ) . '/includes/class-cocart.php';
}

// Check if CoCart Community is active and disable it. This is to prevent conflicts between the two plugins since they share the same core files.
if ( defined( 'COCART_FILE' ) && defined( 'COCART_CORE_FILE' ) && COCART_FILE !== COCART_CORE_FILE ) {
	disable_cocart_community_version();
	return;
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
