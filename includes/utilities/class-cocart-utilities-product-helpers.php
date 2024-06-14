<?php
/**
 * Utilities: Product Helpers class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.x.x Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to handle product functions for the API.
 *
 * @since 4.x.x Introduced.
 */
class CoCart_Utilities_Product_Helpers {

	// ** Product images **//

	/**
	 * Returns product image sizes.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.x.x Introduced.
	 *
	 * @return array
	 */
	public static function get_product_image_sizes() {
		return apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );
	} // END get_product_image_sizes()

	// ** Product Details **//

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param string $tax_display_mode If returned prices are incl or excl of tax.
	 *
	 * @return string Function name.
	 */
	protected static function get_price_from_tax_display_mode( $tax_display_mode ) {
		return 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';
	} // END get_price_from_tax_display_mode()
} // END class
