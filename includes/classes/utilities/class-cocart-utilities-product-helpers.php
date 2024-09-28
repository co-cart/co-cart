<?php
/**
 * Utilities: Product Helpers class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.2.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to handle product functions for the API.
 *
 * @since 4.2.0 Introduced.
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
	 * @since 4.2.0 Introduced.
	 *
	 * @return array
	 */
	public static function get_product_image_sizes() {
		return apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );
	} // END get_product_image_sizes()

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array $images Array of image data.
	 */
	public static function get_images( $product ) {
		$images           = array();
		$attachment_ids   = array();
		$attachment_sizes = self::get_product_image_sizes();

		// Add featured image.
		if ( $product->get_image_id() ) {
			$attachment_ids[] = $product->get_image_id();
		}

		// Add gallery images.
		$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

		$attachments = array();

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( $attachment_id, $size ) );
			}

			$featured = 0 === $position ? true : false;

			$images[] = array(
				'id'       => (int) $attachment_id,
				'src'      => $attachments,
				'name'     => get_the_title( $attachment_id ),
				'alt'      => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position' => (int) $position,
				'featured' => $featured,
			);
		}

		// Set a placeholder image if the product has no images set.
		if ( empty( $images ) ) {
			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = wc_placeholder_img_src( $size );
			}

			$images[] = array(
				'id'       => 0,
				'src'      => $attachments,
				'name'     => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'alt'      => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'position' => 0,
				'featured' => true,
			);
		}

		return $images;
	} // END get_images()

	// ** Product Details **//

	/**
	 * Returns the product quantity minimum requirement.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @param WC_Product The product object.
	 *
	 * @return int Quantity
	 */
	public static function get_quantity_minimum_requirement( $product ) {
		/**
		 * Filters the minimum quantity requirement the product allows to be purchased.
		 *
		 * @since 3.1.0 Introduced.
		 */
		return (int) apply_filters( 'cocart_quantity_minimum_requirement', $product->get_min_purchase_quantity(), $product );
	} // END get_quantity_minimum_requirement()

	/**
	 * Returns the product maximum quantity allowed.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @param WC_Product The product object.
	 *
	 * @return int Quantity
	 */
	public static function get_quantity_maximum_allowed( $product ) {
		/**
		 * Filters the products maximum quantity allowed to be purchased.
		 *
		 * @since 3.1.0 Introduced.
		 */
		return apply_filters( 'cocart_quantity_maximum_allowed', $product->get_max_purchase_quantity(), $product );
	} // END get_quantity_maximum_allowed()

	/**
	 * Returns the price range for variable or grouped product.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @see CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode()
	 *
	 * @param WC_Product|WC_Product_Variable $product          The product object.
	 * @param string                         $tax_display_mode If returned prices are incl or excl of tax.
	 *
	 * @return array
	 */
	public static function get_price_range( $product, $tax_display_mode ) {
		$price = array();

		if ( $product->is_type( 'variable' ) && $product->has_child() || $product->is_type( 'variable-subscription' ) && $product->has_child() ) {
			$prices = $product->get_variation_prices( true );

			$price = isset( $prices['price'] ) ? $prices['price'] : array();

			if ( empty( $price ) ) {
				/**
				 * Filter the variable products empty prices.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param array      $empty_prices Empty array.
				 * @param WC_Product $product      The project object.
				 */
				$price = apply_filters( 'cocart_products_variable_empty_price', array(), $product );
			} else {
				$min_price     = current( $prices['price'] );
				$max_price     = end( $prices['price'] );
				$min_reg_price = current( $prices['regular_price'] );
				$max_reg_price = end( $prices['regular_price'] );

				if ( $min_price !== $max_price ) {
					$price = array(
						'from' => cocart_prepare_money_response( $min_price, wc_get_price_decimals() ),
						'to'   => cocart_prepare_money_response( $max_price, wc_get_price_decimals() ),
					);
				} else {
					$price = array(
						'from' => cocart_prepare_money_response( $min_price, wc_get_price_decimals() ),
						'to'   => '',
					);
				}
			}
		}

		if ( $product->is_type( 'grouped' ) ) {
			$children       = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
			$price_function = self::get_price_from_tax_display_mode( $tax_display_mode );

			foreach ( $children as $child ) {
				if ( '' !== $child->get_price() ) {
					$child_prices[] = $price_function( $child );
				}
			}

			if ( ! empty( $child_prices ) ) {
				$price = array(
					'from' => cocart_prepare_money_response( min( $child_prices ), wc_get_price_decimals() ),
					'to'   => cocart_prepare_money_response( max( $child_prices ), wc_get_price_decimals() ),
				);
			}
		}

		/**
		 * Filters the products price range.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @param array      $price   The current product price range.
		 * @param WC_Product $product The product object.
		 */
		$price_range = apply_filters( 'cocart_products_get_price_range', $price, $product );

		return $price_range;
	} // END get_price_range()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param string $tax_display_mode Provided tax display mode.
	 *
	 * @return string Valid tax display mode.
	 */
	public static function get_tax_display_mode( $tax_display_mode = '' ) {
		return in_array( $tax_display_mode, array( 'incl', 'excl' ), true ) ? $tax_display_mode : get_option( 'woocommerce_tax_display_shop' );
	} // END get_tax_display_mode()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param string $tax_display_mode If returned prices are incl or excl of tax.
	 *
	 * @return string Function name.
	 */
	public static function get_price_from_tax_display_mode( $tax_display_mode ) {
		return 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';
	} // END get_price_from_tax_display_mode()

	/**
	 * Gets the product meta data.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.11.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array
	 */
	public static function get_meta_data( $product ) {
		$meta_data = $product->get_meta_data();
		$safe_meta = array();

		/**
		 * Filter allows you to ignore private meta data for the product to return.
		 *
		 * When filtering, only list the meta key!
		 *
		 * @since 3.11.0 Introduced.
		 *
		 * @param array      $ignored_meta_keys Ignored meta keys.
		 * @param WC_Product $product           The product object.
		 */
		$ignore_private_meta_keys = apply_filters( 'cocart_products_ignore_private_meta_keys', array(), $product );

		foreach ( $meta_data as $meta ) {
			$ignore_meta = false;

			foreach ( $ignore_private_meta_keys as $ignore ) {
				if ( str_starts_with( $meta->key, $ignore ) ) {
					$ignore_meta = true;
					break; // Exit the inner loop once a match is found.
				}
			}

			// Add meta data only if it's not ignored.
			if ( ! $ignore_meta ) {
				$safe_meta[ $meta->key ] = $meta;
			}
		}

		/**
		 * Filter allows you to control what remaining product meta data is safe to return.
		 *
		 * @since 3.11.0 Introduced.
		 *
		 * @param array      $safe_meta Safe meta.
		 * @param WC_Product $product   The product object.
		 */
		return array_values( apply_filters( 'cocart_products_get_safe_meta_data', $safe_meta, $product ) );
	} // END get_meta_data()
} // END class
