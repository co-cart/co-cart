<?php
/**
 * CoCart REST Functions.
 *
 * Functions for REST specific things.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns image mime types users are allowed to upload via the API.
 *
 * @since 3.0.0 Introduced.
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
 * @since 3.0.0 Introduced.
 *
 * @param array $pathdata Array of paths.
 *
 * @return array Array of paths.
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
 * Change filename to append random text.
 *
 * @since 4.0.0 Introduced.
 *
 * @param string $full_filename Original filename with extension.
 * @param string $ext           File Extension.
 *
 * @return string Modified filename.
 */
function cocart_unique_filename( $full_filename, $ext ) {
	$ideal_random_char_length = 6;   // Not going with a larger length because then downloaded filename will not be pretty.
	$max_filename_length      = 255; // Max file name length for most file systems.
	$length_to_prepend        = min( $ideal_random_char_length, $max_filename_length - strlen( $full_filename ) - 1 );

	if ( 1 > $length_to_prepend ) {
		return $full_filename;
	}

	$suffix   = strtolower( wp_generate_password( $length_to_prepend, false, false ) );
	$filename = $full_filename;

	if ( strlen( $ext ) > 0 ) {
		$filename = substr( $filename, 0, strlen( $filename ) - strlen( $ext ) );
	}

	$full_filename = str_replace(
		$filename,
		"$filename-$suffix",
		$full_filename
	);

	return $full_filename;
} // END cocart_unique_filename()

/**
 * Handles an upload via multipart/form-data ($_FILES).
 *
 * Uses "wp_handle_sideload()" to upload the file.
 *
 * @since 3.0.0 Introduced.
 * @since 4.0.0 Added unique file name append.
 *
 * @param files $file The file to upload.
 *
 * @return array|WP_Error Data from wp_handle_upload().
 */
function cocart_upload_file( $file ) {
	if ( empty( $file['name'] ) ) {
		return new WP_Error( 'cocart_no_file_data', __( 'No file data', 'cart-rest-api-for-woocommerce' ), array( 'status' => 40 ) );
	}

	// wp_handle_sideload function is part of wp-admin.
	if ( ! function_exists( 'wp_handle_sideload' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
	}

	include_once ABSPATH . 'wp-admin/includes/media.php';

	add_filter( 'upload_dir', 'cocart_upload_dir' );
	add_filter( 'wp_unique_filename', 'cocart_unique_filename', 10, 2 );

	// Now, sideload it in.
	$file_data = array(
		'error'    => null,
		'tmp_name' => $file['tmp_name'],
		'name'     => $file['name'],
		'type'     => $file['type'],
	);

	$file = wp_handle_sideload(
		$file_data,
		array(
			'test_form' => false,
		),
		current_time( 'Y/m' )
	);

	remove_filter( 'upload_dir', 'cocart_upload_dir' );

	return $file;
} // END cocart_upload_file()

/**
 * Upload image from URL.
 *
 * Uses "wp_parse_url()" to parse the URL.
 *
 * @since 3.0.0 Introduced.
 * @since 4.0.0 Added unique file name append.
 *
 * @param string $image_url Image URL.
 *
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
	add_filter( 'wp_unique_filename', 'cocart_unique_filename', 10, 2 );

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

	/**
	 * Fires after an image has been uploaded via a URL.
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array  $file Uploaded file.
	 * @param string $image_url Image URL.
	 */
	do_action( 'cocart_uploaded_image_from_url', $file, $image_url );

	return $file;
} // END cocart_upload_image_from_url()

/**
 * Set uploaded image as attachment.
 *
 * @since 3.0.0 Introduced.
 *
 * @param array $upload Upload information from "wp_upload_bits()".
 * @param int   $id     Post ID. Default to 0.
 *
 * @return int $attachment_id Attachment ID.
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

	$attachment_id = wp_insert_attachment( $attachment, $upload['file'], $id, true );
	if ( ! is_wp_error( $attachment_id ) ) {
		wp_update_attachment_metadata( $attachment_id, wp_generate_attachment_metadata( $attachment_id, $upload['file'] ) );
	}

	return $attachment_id;
} // END cocart_set_uploaded_image_as_attachment()

/**
 * Add to cart messages.
 *
 * Forked "wc_add_to_cart_message()" function and altered to remove HTML context
 * for the use of the REST API returning clean notices once products have
 * been added to cart.
 *
 * @since 3.0.0 Introduced.
 *
 * @param array|int $products Product ID list or single product ID.
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

	/**
	 * Filters the "Add to Cart" message without HTML.
	 *
	 * @since 3.0.0 Introduced.
	 */
	$message = apply_filters( 'cocart_add_to_cart_message_html', esc_html( $added_text ), $products, $show_qty );

	if ( $return ) {
		return $message;
	} else {
		wc_add_notice( $message, 'success' );
	}
} // END cocart_add_to_cart_message()

/**
 * Prepares a list of store currency data to return in responses.
 *
 * @since 3.1.0 Introduced.
 *
 * @return array The store currency data.
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
 * @since 3.4.0 Introduced.
 *
 * @global array $wp_rest_additional_fields Holds registered fields, organized by object type.
 *
 * @param string|array $object_type Object(s) the field is being registered to, "post"|"term"|"comment" etc.
 * @param string       $attribute   The attribute name.
 *
 * @ignore Function ignored when parsed into Code Reference.
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
 * @since 3.4.1 Introduced.
 *
 * @param array $args Min price and max price arguments.
 *
 * @return array Min/max price meta query args.
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

/**
 * Returns the permalink for a product and
 * replaces the frontend URL if set.
 *
 * @since 4.0.0 Introduced.
 *
 * @param string $url Permalink.
 *
 * @return string Permalink.
 */
function cocart_get_permalink( $url ) {
	$settings = get_option( 'cocart_settings', array() );

	$frontend_url = ! empty( $settings['general']['frontend_url'] ) ? $settings['general']['frontend_url'] : '';

	return str_replace( home_url(), $frontend_url, $url );
} // END cocart_get_permalink()

/**
 * Returns the salt key for CoCart if defined.
 *
 * Used to help prevent session hijacking.
 *
 * @since 4.0.0 Introduced.
 *
 * @return mixed The salt key for CoCart or false if not defined.
 */
function maybe_cocart_require_salt() {
	/**
	 * Check if the salt key is defined.
	 * Should be hashed already to remain secure.
	 */
	if ( defined( 'COCART_SALT_KEY' ) ) {
		return md5( COCART_SALT_KEY );
	}

	$settings = get_option( 'cocart_settings', array() );

	$salt_key = ! empty( $settings['general']['salt_key'] ) ? $settings['general']['salt_key'] : '';

	if ( ! empty( $salt_key ) ) {
		return $salt_key;
	}

	return false;
} // END maybe_cocart_require_salt()
