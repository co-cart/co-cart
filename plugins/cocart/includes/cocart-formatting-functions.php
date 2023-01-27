<?php
/**
 * CoCart Formatting.
 *
 * Functions for formatting values.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.7.0 Introduced.
 * @version 4.0.0
 */

use CoCart\ProductsAPI\DateTime as ProductDateTime;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Parses and formats a date for ISO8601/RFC3339.
 *
 * Requires WP 4.4 or later.
 *
 * @link https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
 *
 * @since 3.1.0 Introduced.
 *
 * @param string|null|ProductDateTime $date Date.
 * @param bool                        $utc  Send false to get local/offset time.
 *
 * @return string|null ISO8601/RFC3339 formatted datetime.
 */
function cocart_prepare_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new ProductDateTime( "@$date", new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new ProductDateTime( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	}

	if ( ! is_a( $date, 'CoCart\ProductsAPI\DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
} // END cocart_prepare_date_response()

/**
 * Format the price with a currency symbol without HTML wrappers.
 *
 * Forked "wc_price()" function and altered to remove HTML wrappers
 * for the use of the REST API.
 *
 * @since 3.0.0 Introduced.
 * @since 4.0.0 Cleans the price value to remove any HTML and currency symbols.
 *
 * @param float $price Raw price.
 * @param array $args {
 *     Optional. Arguments to format a price.
 *
 *     @type bool   $ex_tax_label       Adds exclude tax label. Defaults to false.
 *     @type string $currency           Currency code. Defaults to result from `get_woocommerce_currency()`
 *     @type string $decimal_separator  Decimal separator. Defaults to the result of `wc_get_price_decimal_separator()`.
 *     @type string $thousand_separator Thousand separator. Defaults to the result of `wc_get_price_thousand_separator()`.
 *     @type string $decimals           Number of decimals. Defaults to the result of `wc_get_price_decimals()`.
 *     @type string $price_format       Price format depending on the currency position. Defaults to the result of `get_woocommerce_price_format()`.
 * }
 * @return string
 */
function cocart_price_no_html( $price, $args = array() ) {
	$args = apply_filters(
		'cocart_price_args',
		wp_parse_args(
			$args,
			array(
				'ex_tax_label'       => false,
				'currency'           => get_woocommerce_currency(),
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			)
		)
	);

	// If $price is a string, clean it first.
	if ( is_string( $price ) ) {
		$price = html_entity_decode( wp_strip_all_tags( $price ) ); // Decode html span wrapper, if any.
		$price = str_replace( html_entity_decode( get_woocommerce_currency_symbol( $args['currency'] ) ), '', $price ); // Remove currency symbol, if any.
	}

	$original_price = $price;

	// Convert to float to avoid issues on PHP 8.
	$price = (float) $price;

	$unformatted_price = $price;
	$negative          = $price < 0;

	/**
	 * Filter raw price.
	 *
	 * @param float        $raw_price      Raw price.
	 * @param float|string $original_price Original price as float, or empty string.
	 */
	$price = apply_filters( 'raw_woocommerce_price', $negative ? $price * -1 : $price, $original_price );

	/**
	 * Filter formatted price.
	 *
	 * @param float        $formatted_price    Formatted price.
	 * @param float        $price              Unformatted price.
	 * @param int          $decimals           Number of decimals.
	 * @param string       $decimal_separator  Decimal separator.
	 * @param string       $thousand_separator Thousand separator.
	 * @param float|string $original_price     Original price as float, or empty string.
	 */
	$price = apply_filters( 'formatted_woocommerce_price', number_format( $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'] ), $price, $args['decimals'], $args['decimal_separator'], $args['thousand_separator'], $original_price );

	if ( apply_filters( 'woocommerce_price_trim_zeros', false ) && $args['decimals'] > 0 ) {
		$price = wc_trim_zeros( $price );
	}

	$formatted_price = ( $negative ? '-' : '' ) . sprintf( $args['price_format'], get_woocommerce_currency_symbol( $args['currency'] ), $price );
	$return          = $formatted_price;

	if ( $args['ex_tax_label'] && wc_tax_enabled() ) {
		$return .= ' ' . WC()->countries->ex_tax_or_vat();
	}

	$return = html_entity_decode( $return );

	/**
	 * Filters the string of price markup.
	 *
	 * @param string       $return            Price HTML markup.
	 * @param string       $price             Formatted price.
	 * @param array        $args              Pass on the args.
	 * @param float        $unformatted_price Price as float to allow plugins custom formatting.
	 * @param float|string $original_price    Original price as float, or empty string.
	 */
	return apply_filters( 'cocart_price_no_html', $return, $price, $args, $unformatted_price, $original_price );
} // END cocart_price_no_html()

/**
 * Convert monetary values from store settings to string based integers, using
 * the smallest unit of a currency.
 *
 * @since 3.1.0 Introduced.
 * @since 4.0.0 Dropped `$decimals` and `$rounding_mode` in favor of a new array parameter `$options`.
 *
 * @param mixed $value   Value to format.
 * @param array $options Options that influence the formatting.
 *
 * @return mixed Formatted value.
 */
function cocart_prepare_money_response( $amount, array $options = array() ) {
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

	// Identify the format of $amount value so we can return it back the same.
	if ( is_float( $amount ) ) {
		$return_format = 'float';
	} else {
		$return_format = 'string';
	}

	// If $amount is a string, clean it first.
	if ( is_string( $amount ) ) {
		$amount = html_entity_decode( wp_strip_all_tags( $amount ) ); // Decode html span wrapper, if any.
		$amount = str_replace( html_entity_decode( get_woocommerce_currency_symbol( $options['currency'] ) ), '', $amount ); // Remove currency symbol, if any.
	}

	// Trim zeros.
	if ( $options['trim_zeros'] ) {
		$amount = wc_trim_zeros( $amount );
	}

	/**
	 * Filter allows you to disable the decimals.
	 *
	 * If set to "True" the decimals will be forced to "Zero".
	 *
	 * @param bool false False by default.
	 */
	$disable_decimals = apply_filters( 'cocart_prepare_money_disable_decimals', false );

	if ( $disable_decimals ) {
		$options['decimals'] = 0;
	}

	// Format value with decimals.
	$formatted_value = ( (float) wc_format_decimal( $amount, absint( $options['decimals'] ), $options['trim_zeros'] ) ) * ( 10 ** absint( $options['decimals'] ) );

	// Round up/down the value.
	$formatted_value = round( $formatted_value, 0, absint( $options['rounding_mode'] ) );

	// Get the integer value of a variable.
	$formatted_value = intval( $formatted_value );

	// Return value based on input format.
	if ( $return_format === 'float' ) {
		return (float) $formatted_value;
	} else {
		return (string) $formatted_value;
	}
} // END cocart_prepare_money_response()

/**
 * Notation to numbers.
 *
 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
 *
 * @param string $size Size value.
 *
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
 * Formats the product attributes for a variation.
 *
 * Converts slugs such as "attribute_pa_size" to "Size".
 *
 * @since 4.0.0 Introduced.
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
 * @since 4.0.0 Introduced.
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

			// If this is a custom option slug, get each options name.
			$values = apply_filters( 'cocart_attribute_option_name', $attribute, $values, $product );
		}

		$label = wc_attribute_label( $attribute->get_name() );

		$return[ $label ] = wptexturize( implode( ', ', $values ) );
	}

	return $return;
} // END cocart_format_attribute_data()
