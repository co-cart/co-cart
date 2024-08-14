<?php
/**
 * CoCart Formatting
 *
 * Functions for formatting.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.7.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notation to numbers.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @param  string $size Size value.
 * @return int
 */
function cocart_let_to_num( $size ) {
	$l   = substr( $size, -1 );
	$ret = (int) substr( $size, 0, -1 );
	switch ( strtoupper( $l ) ) {
		case 'P':
			$ret *= 1024;
			// No break.
		case 'T':
			$ret *= 1024;
			// No break.
		case 'G':
			$ret *= 1024;
			// No break.
		case 'M':
			$ret *= 1024;
			// No break.
		case 'K':
			$ret *= 1024;
			// No break.
	}
	return $ret;
} // END cocart_let_to_num()

/**
 * Convert monetary values from store settings to string based integers, using
 * the smallest unit of a currency.
 *
 * @since 4.4.0 Introduced.
 *
 * @param int|float|string $value   Value to format. Int is allowed, as it may also represent a valid price.
 * @param array            $options Options that influence the formatting.
 *
 * @return string Formatted value.
 */
function cocart_format_money( $value, array $options = array() ) {
	// Values that don't need converting just return the original value.
	if ( empty( $value ) || 0 === $value || '0.00' === $value ) {
		return $value;
	}

	if ( ! is_int( $value ) && ! is_string( $value ) && ! is_float( $value ) ) {
		wc_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Function expects a $value arg of type INT, STRING or FLOAT.%s', ! empty( $value ) ? ' Given value: ' . $value : '' ),
			'4.4'
		);

		return '';
	}

	$default_options = array(
		'currency'      => get_woocommerce_currency(),
		'decimals'      => wc_get_price_decimals(),
		'rounding_mode' => PHP_ROUND_HALF_UP,
		'trim_zeros'    => false,
	);

	if ( ! empty( $options ) ) {
		$options = wp_parse_args( $options, $default_options );
	} else {
		$options = $default_options;
	}

	/**
	 * If $value is a string, clean it first.
	 *
	 * This is required should the $value parse any html in the string
	 * that may have been added by an extension like subscriptions.
	 */
	if ( is_string( $value ) ) {
		$value = html_entity_decode( wp_strip_all_tags( $value ) ); // Decode html span wrapper, if any.
		$value = str_replace( html_entity_decode( get_woocommerce_currency_symbol( $options['currency'] ) ), '', $value ); // Remove currency symbol, if any.
	}

	if ( ! intval( $value ) ) {
		wc_doing_it_wrong(
			__FUNCTION__,
			sprintf( 'Value did not return as just numbers. Expects $value to be integer.%s', ! empty( $value ) ? ' Given value: ' . $value : '' ),
			'4.4'
		);

		return '';
	}

	// Trim zeros.
	if ( $options['trim_zeros'] ) {
		$value = wc_trim_zeros( $value );
	}

	/**
	 * Filter allows you to disable the decimals.
	 *
	 * If set to "True" the decimals will be forced to "Zero".
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param bool $disable_decimals False by default.
	 */
	$disable_decimals = apply_filters( 'cocart_prepare_money_disable_decimals', false );

	if ( $disable_decimals ) {
		$options['decimals'] = 0;
	}

	// Ensure rounding mode is valid.
	$rounding_modes           = array( PHP_ROUND_HALF_UP, PHP_ROUND_HALF_DOWN, PHP_ROUND_HALF_EVEN, PHP_ROUND_HALF_ODD );
	$options['rounding_mode'] = absint( $options['rounding_mode'] );

	// If rounding is not valid then force it to only round half up.
	if ( ! in_array( $options['rounding_mode'], $rounding_modes, true ) ) {
		$options['rounding_mode'] = PHP_ROUND_HALF_UP;
	}

	$value = floatval( $value );

	// Remove the price decimal points for rounding purposes.
	$value = $value * pow( 10, absint( $options['decimals'] ) );

	// Round up/down the value.
	$value = round( $value, 0, $options['rounding_mode'] );

	// This ensures returning the value as a string without decimal points ready for price parsing.
	return wc_format_decimal( $value, 0, $options['trim_zeros'] );
} // END cocart_format_money()

/**
 * Formats the product attributes for a variation.
 *
 * Converts slugs such as "attribute_pa_size" to "Size".
 *
 * @since 4.4.0 Introduced.
 *
 * @param array      $attributes Array of data from the cart.
 * @param WC_Product $product    Product data.
 *
 * @return array Formatted attribute data.
 */
function cocart_format_variation_data( $attributes, $product ) {
	$return = array();

	foreach ( $attributes as $key => $value ) {
		$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $key ) ) );

		if ( taxonomy_exists( $taxonomy ) ) {
			// If this is a term slug, get the term's nice name.
			$term = get_term_by( 'slug', $value, $taxonomy );
			if ( ! is_wp_error( $term ) && $term && $term->name ) {
				$value = $term->name;
			}
			$label = wc_attribute_label( $taxonomy );
		} else {
			// If this is a custom option slug, get the options name.
			$value = apply_filters( 'cocart_variation_option_name', $value, $product );
			$label = wc_attribute_label( str_replace( 'attribute_', '', $key ), $product );
		}

		$return[ $label ] = $value;
	}

	return $return;
} // END cocart_format_variation_data()

/**
 * Formats product attribute data.
 *
 * Converts slugs such as "attribute_pa_size" to "Size".
 *
 * @since x.x.x Introduced.
 *
 * @param WC_Product $product Product data.
 *
 * @return array Formatted attribute data.
 */
function cocart_format_attribute_data( $product ) {
	$return = array();

	$attributes = array_filter( $product->get_attributes(), 'wc_attributes_array_filter_visible' );

	foreach ( $attributes as $attribute ) {
		$values = array();

		if ( $attribute->is_taxonomy() ) {
			$attribute_taxonomy = $attribute->get_taxonomy_object();
			$attribute_values   = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

			foreach ( $attribute_values as $attribute_value ) {
				$value_name = esc_html( $attribute_value->name );

				$values[] = $value_name;
			}
		} else {
			$values = $attribute->get_options();

			/**
			 * Filters the attribute option name.
			 *
			 * @since 3.1.0 Introduced.
			 *
			 * @param object     $attribute The attribute.
			 * @param object     $values    Get each options name.
			 * @param WC_Product $product   Product data.
			 */
			$values = apply_filters( 'cocart_attribute_option_name', $attribute, $values, $product );
		}

		$label = wc_attribute_label( $attribute->get_name() );

		$return[ $label ] = wptexturize( implode( ', ', $values ) );
	}

	return $return;
} // END cocart_format_attribute_data()
