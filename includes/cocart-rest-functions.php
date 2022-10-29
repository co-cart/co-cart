<?php
/**
 * CoCart REST Functions.
 *
 * Functions for REST specific things.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper for deprecated hook so we can apply some extra logic.
 *
 * @since   3.0.7  Introduced.
 * @since   3.1.0  Changed function `is_ajax()` to `wp_doing_ajax()`.
 * @version 3.1.0
 * @uses    wp_doing_ajax()
 * @param   string $hook        The hook that was used.
 * @param   string $version     The version of WordPress that deprecated the hook.
 * @param   string $replacement The hook that should have been used.
 * @param   string $message     A message regarding the change.
 */
function cocart_deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || CoCart_Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $hook, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		CoCart_Logger::log( $log_string . $message, 'debug' );
	} else {
		_deprecated_hook( $hook, $version, $replacement, $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} // END cocart_deprecated_hook()

/**
 * Wrapper for deprecated filter so we can apply some extra logic.
 *
 * @since   3.0.0  Introduced.
 * @since   3.1.0  Changed function `is_ajax()` to `wp_doing_ajax()`.
 * @version 3.1.0
 * @uses    wp_doing_ajax()
 * @param   string $filter      The filter that was used.
 * @param   array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param   string $version     The version of WordPress that deprecated the filter.
 * @param   string $replacement The filter that should have been used.
 * @param   string $message     A message regarding the change.
 */
function cocart_deprecated_filter( $filter, $args = array(), $version = '', $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || CoCart_Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_filter_run', $filter, $args, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $filter, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		CoCart_Logger::log( $log_string . $message, 'debug' );
	} else {
		return apply_filters_deprecated( $filter, $args, $version, $replacement, $message );
	}
} // END cocart_deprecated_filter()

/**
 * Parses and formats a date for ISO8601/RFC3339.
 *
 * Requires WP 4.4 or later.
 * See https://developer.wordpress.org/reference/functions/mysql_to_rfc3339/
 *
 * @param  string|null|CoCart_DateTime $date Date.
 * @param  bool                        $utc  Send false to get local/offset time.
 * @return string|null ISO8601/RFC3339 formatted datetime.
 */
function cocart_prepare_date_response( $date, $utc = true ) {
	if ( is_numeric( $date ) ) {
		$date = new CoCart_DateTime( "@$date", new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	} elseif ( is_string( $date ) ) {
		$date = new CoCart_DateTime( $date, new DateTimeZone( 'UTC' ) );
		$date->setTimezone( new DateTimeZone( wc_timezone_string() ) );
	}

	if ( ! is_a( $date, 'CoCart_DateTime' ) ) {
		return null;
	}

	// Get timestamp before changing timezone to UTC.
	return gmdate( 'Y-m-d\TH:i:s', $utc ? $date->getTimestamp() : $date->getOffsetTimestamp() );
} // END cocart_prepare_date_response()

/**
 * Returns image mime types users are allowed to upload via the API.
 *
 * @return array
 */
function cocart_allowed_image_mime_types() {
	return apply_filters(
		'cocart_allowed_image_mime_types',
		array(
			'jpg|jpeg|jpe' => 'image/jpeg',
			'gif'          => 'image/gif',
			'png'          => 'image/png',
			'bmp'          => 'image/bmp',
			'tiff|tif'     => 'image/tiff',
			'ico'          => 'image/x-icon',
		)
	);
} // END cocart_allowed_image_mime_types()

/**
 * CoCart upload directory.
 *
 * @param  array $pathdata Array of paths.
 * @return array
 */
function cocart_upload_dir( $pathdata ) {
	if ( empty( $pathdata['subdir'] ) ) {
		$pathdata['path']   = $pathdata['path'] . '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['url']    = $pathdata['url'] . '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['subdir'] = '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
	} else {
		$subdir             = '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['path']   = str_replace( $pathdata['subdir'], $subdir, $pathdata['path'] );
		$pathdata['url']    = str_replace( $pathdata['subdir'], $subdir, $pathdata['url'] );
		$pathdata['subdir'] = str_replace( $pathdata['subdir'], $subdir, $pathdata['subdir'] );
	}

	return apply_filters( 'cocart_upload_dir', $pathdata );
} // END cocart_upload_dir()

/**
 * Upload a file.
 *
 * @param  files $file The file to upload.
 * @return array|WP_Error File data or error message.
 */
function cocart_upload_file( $file ) {
	// wp_handle_upload function is part of wp-admin.
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	include_once ABSPATH . 'wp-admin/includes/media.php';

	add_filter( 'upload_dir', 'cocart_upload_dir' );

	$upload = wp_handle_upload( $file, array( 'test_form' => false ) );

	remove_filter( 'upload_dir', 'cocart_upload_dir' );

	return $upload;
} // END cocart_upload_file()

/**
 * Upload image from URL.
 *
 * @param  string $image_url Image URL.
 * @return array|WP_Error Attachment data or error message.
 */
function cocart_upload_image_from_url( $image_url ) {
	$parsed_url = wp_parse_url( $image_url );

	// Check parsed URL.
	if ( ! $parsed_url || ! is_array( $parsed_url ) ) {
		/* translators: %s: image URL */
		return new WP_Error( 'cocart_invalid_image_url', sprintf( __( 'Invalid URL %s.', 'cart-rest-api-for-woocommerce' ), $image_url ), array( 'status' => 400 ) );
	}

	// Ensure url is valid.
	$image_url = esc_url_raw( $image_url );

	// download_url function is part of wp-admin.
	if ( ! function_exists( 'download_url' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	$file_array         = array();
	$file_array['name'] = basename( current( explode( '?', $image_url ) ) );

	// Download file to temp location.
	$file_array['tmp_name'] = download_url( $image_url );

	// If error storing temporarily, return the error.
	if ( is_wp_error( $file_array['tmp_name'] ) ) {
		return new WP_Error(
			'cocart_invalid_remote_image_url',
			/* translators: %s: image URL */
			sprintf( __( 'Error getting remote image %s.', 'cart-rest-api-for-woocommerce' ), $image_url ) . ' '
			/* translators: %s: error message */
			. sprintf( __( 'Error: %s', 'cart-rest-api-for-woocommerce' ), $file_array['tmp_name']->get_error_message() ),
			array( 'status' => 400 )
		);
	}

	add_filter( 'upload_dir', 'cocart_upload_dir' );

	// Do the validation and storage stuff.
	$file = wp_handle_sideload(
		$file_array,
		array(
			'test_form' => false,
			'mimes'     => cocart_allowed_image_mime_types(),
		),
		current_time( 'Y/m' )
	);

	remove_filter( 'upload_dir', 'cocart_upload_dir' );

	if ( isset( $file['error'] ) ) {
		@unlink( $file_array['tmp_name'] ); // @codingStandardsIgnoreLine.

		/* translators: %s: error message */
		return new WP_Error( 'cocart_invalid_image', sprintf( __( 'Invalid image: %s', 'cart-rest-api-for-woocommerce' ), $file['error'] ), array( 'status' => 400 ) );
	}

	do_action( 'cocart_uploaded_image_from_url', $file, $image_url );

	return $file;
} // END cocart_upload_image_from_url()

/**
 * Set uploaded image as attachment.
 *
 * @param  array $upload        Upload information from wp_upload_bits.
 * @param  int   $id            Post ID. Default to 0.
 * @return int   $attachment_id Attachment ID.
 */
function cocart_set_uploaded_image_as_attachment( $upload, $id = 0 ) {
	$info    = wp_check_filetype( $upload['file'] );
	$title   = '';
	$content = '';

	if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
		include_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$image_meta = wp_read_image_metadata( $upload['file'] );
	if ( $image_meta ) {
		if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) ) {
			$title = wc_clean( $image_meta['title'] );
		}
		if ( trim( $image_meta['caption'] ) ) {
			$content = wc_clean( $image_meta['caption'] );
		}
	}

	$attachment = array(
		'post_mime_type' => $info['type'],
		'guid'           => $upload['url'],
		'post_parent'    => $id,
		'post_title'     => $title ? $title : basename( $upload['file'] ),
		'post_content'   => $content,
	);

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id );
	if ( ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $attachment_id;
} // END cocart_set_uploaded_image_as_attachment()

/**
 * Format the price with a currency symbol without HTML wrappers.
 *
 * Forked wc_price() function and altered to remove HTML wrappers
 * for the use of the REST API.
 *
 * @since   3.0.0
 * @version 3.0.4
 * @param   float $price Raw price.
 * @param   array $args  Arguments to format a price {
 *     Array of arguments.
 *     Defaults to empty array.
 *
 *     @type bool   $ex_tax_label       Adds exclude tax label.
 *                                      Defaults to false.
 *     @type string $currency           Currency code.
 *                                      Defaults to empty string (Use the result from get_woocommerce_currency()).
 *     @type string $decimal_separator  Decimal separator.
 *                                      Defaults the result of wc_get_price_decimal_separator().
 *     @type string $thousand_separator Thousand separator.
 *                                      Defaults the result of wc_get_price_thousand_separator().
 *     @type string $decimals           Number of decimals.
 *                                      Defaults the result of wc_get_price_decimals().
 *     @type string $price_format       Price format depending on the currency position.
 *                                      Defaults the result of get_woocommerce_price_format().
 * }
 * @return  string
 */
function cocart_price_no_html( $price, $args = array() ) {
	$args = apply_filters(
		'cocart_price_args',
		wp_parse_args(
			$args,
			array(
				'ex_tax_label'       => false,
				'currency'           => '',
				'decimal_separator'  => wc_get_price_decimal_separator(),
				'thousand_separator' => wc_get_price_thousand_separator(),
				'decimals'           => wc_get_price_decimals(),
				'price_format'       => get_woocommerce_price_format(),
			)
		)
	);

	$original_price = $price;

	// Convert to float to avoid issues on PHP 8.
	$price = (float) $price;

	$unformatted_price = $price;
	$negative          = $price < 0;

	/**
	 * Filter raw price.
	 *
	 * @param float        $raw_price      Raw price.
	 * @param float|string $original_price Original price as float, or empty string. Since 5.0.0.
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
	 * @param float|string $original_price     Original price as float, or empty string. Since 5.0.0.
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
	 * @param float        $unformatted_price Price as float to allow plugins custom formatting. Since 3.2.0.
	 * @param float|string $original_price    Original price as float, or empty string. Since 5.0.0.
	 */
	return apply_filters( 'cocart_price_no_html', $return, $price, $args, $unformatted_price, $original_price );
} // END cocart_price_no_html()

/**
 * Add to cart messages.
 *
 * Forked wc_add_to_cart_message() function and altered to remove HTML context
 * for the use of the REST API returning clean notices once products have
 * been added to cart.
 *
 * @param int|array $products Product ID list or single product ID.
 * @param bool      $show_qty Should qty's be shown.
 * @param bool      $return   Return message rather than add it.
 *
 * @return mixed
 */
function cocart_add_to_cart_message( $products, $show_qty = false, $return = false ) {
	$titles = array();
	$count  = 0;

	if ( ! is_array( $products ) ) {
		$products = array( $products => 1 );
		$show_qty = false;
	}

	if ( ! $show_qty ) {
		$products = array_fill_keys( array_keys( $products ), 1 );
	}

	foreach ( $products as $product_id => $qty ) {
		/* translators: %s: product name */
		$titles[] = apply_filters( 'cocart_add_to_cart_qty_html', ( $qty > 1 ? $qty . ' &times; ' : '' ), $product_id ) . apply_filters( 'cocart_add_to_cart_item_name_in_quotes', sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), wp_strip_all_tags( get_the_title( $product_id ) ) ), $product_id );
		$count   += $qty;
	}

	$titles = array_filter( $titles );

	/* translators: %s: product name */
	$added_text = sprintf( _n( '%s has been added to your cart.', '%s have been added to your cart.', $count, 'cart-rest-api-for-woocommerce' ), wc_format_list_of_items( $titles ) );

	$message = apply_filters( 'cocart_add_to_cart_message_html', esc_html( $added_text ), $products, $show_qty );

	if ( $return ) {
		return $message;
	} else {
		wc_add_notice( $message, 'success' );
	}
} // END cocart_add_to_cart_message()

/**
 * Convert monetary values from WooCommerce to string based integers, using
 * the smallest unit of a currency.
 *
 * @since  3.1.0 Introduced.
 *
 * @param  string|float $amount        - Monetary amount with decimals.
 * @param  int          $decimals      - Number of decimals the amount is formatted with.
 * @param  int          $rounding_mode - Defaults to the PHP_ROUND_HALF_UP constant.
 * @return string       The new amount.
 */
function cocart_prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
	// If string, clean it first.
	if ( is_string( $amount ) ) {
		$amount = wc_format_decimal( html_entity_decode( wp_strip_all_tags( $amount ) ) );
		$amount = (float) $amount;
	}

	/**
	 * This filter allows you to disable the decimals.
	 * If set to "True" the decimals will be set to "Zero".
	 */
	$disable_decimals = apply_filters( 'cocart_prepare_money_disable_decimals', false );

	if ( $disable_decimals ) {
		$decimals = 0;
	}

	return (string) intval(
		round(
			( (float) wc_format_decimal( $amount ) ) * ( 10 ** absint( $decimals ) ),
			0,
			absint( $rounding_mode )
		)
	);
} // END cocart_prepare_money_response()

/**
 * Prepares a list of store currency data to return in responses.
 *
 * @since  3.1.0 Introduced.
 * @return array
 */
function cocart_get_store_currency() {
	$position = get_option( 'woocommerce_currency_pos' );
	$symbol   = html_entity_decode( get_woocommerce_currency_symbol() );
	$prefix   = '';
	$suffix   = '';

	switch ( $position ) {
		case 'left_space':
			$prefix = $symbol . ' ';
			break;
		case 'left':
			$prefix = $symbol;
			break;
		case 'right_space':
			$suffix = ' ' . $symbol;
			break;
		case 'right':
			$suffix = $symbol;
			break;
	}

	return array(
		'currency_code'               => get_woocommerce_currency(),
		'currency_symbol'             => $symbol,
		'currency_minor_unit'         => wc_get_price_decimals(),
		'currency_decimal_separator'  => wc_get_price_decimal_separator(),
		'currency_thousand_separator' => wc_get_price_thousand_separator(),
		'currency_prefix'             => $prefix,
		'currency_suffix'             => $suffix,
	);
} // END cocart_get_store_currency()

/**
 * Unregister's a field on an existing WordPress object type.
 *
 * @todo Submit a ticket to have this part of WordPress.
 *
 * @since 3.4.0
 *
 * @global array        $wp_rest_additional_fields Holds registered fields, organized by object type.
 * @param  string|array $object_type               Object(s) the field is being registered to, "post"|"term"|"comment" etc.
 * @param  string       $attribute                 The attribute name.
 */
if ( ! function_exists( 'unregister_rest_field' ) ) {
	function unregister_rest_field( $object_type, $attribute ) {
		global $wp_rest_additional_fields;

		$object_types = (array) $object_type;

		foreach ( $object_types as $object_type ) {
			unset( $wp_rest_additional_fields[ $object_type ][ $attribute ] );
		}
	} // END unregister_rest_field()
}

/**
 * Get min/max price meta query args.
 *
 * @since  3.4.1 Introduced.
 * @param  array $args Min price and max price arguments.
 * @return array
 */
function cocart_get_min_max_price_meta_query( $args ) {
	$current_min_price = isset( $args['min_price'] ) ? floatval( $args['min_price'] ) : 0;
	$current_max_price = isset( $args['max_price'] ) ? floatval( $args['max_price'] ) : PHP_INT_MAX;

	return apply_filters(
		'woocommerce_get_min_max_price_meta_query',
		array(
			'key'     => '_price',
			'value'   => array( $current_min_price, $current_max_price ),
			'compare' => 'BETWEEN',
			'type'    => 'DECIMAL(10,' . wc_get_price_decimals() . ')',
		),
		$args
	);
} // END cocart_get_min_max_price_meta_query()
