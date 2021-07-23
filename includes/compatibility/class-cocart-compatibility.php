<?php
/**
 * Extension Compatibility
 *
 * @author  Sébastien Dumont
 * @package CoCart\Compatibility
 * @since   3.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Compatibility' ) ) {

	class CoCart_Compatibility {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			self::include_compatibility();
		}

		/**
		 * Load support for extension compatibility.
		 *
		 * @access public
		 */
		public function include_compatibility() {
			include_once COCART_ABSPATH . 'includes/compatibility/modules/class-cocart-advanced-shipping-packages.php'; // Advanced Shipping Packages.
			include_once COCART_ABSPATH . 'includes/compatibility/modules/class-cocart-free-gift-coupons.php'; // Free Gift Coupons.
		}

	} // END class.

} // END if class exists.

return new CoCart_Compatibility();
