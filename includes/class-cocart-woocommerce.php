<?php
/**
 * Handles tweaks made to WooCommerce to support CoCart.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart/Classes/WooCommerce
 * @since    2.1.2
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_WooCommerce' ) ) {

	class CoCart_WooCommerce {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			// Removes WooCommerce filter that validates the quantity value to be an integer.
			remove_filter( 'woocommerce_stock_amount', 'intval' );

			// Validates the quantity value to be a float.
			add_filter( 'woocommerce_stock_amount', 'floatval' );
		}

	} // END class

} // END if class exists.

return new CoCart_WooCommerce();
