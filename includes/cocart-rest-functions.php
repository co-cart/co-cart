<?php
/**
 * CoCart REST Functions.
 *
 * Functions for REST specific things.
 *
 * @author   Sébastien Dumont
 * @category Functions
 * @package  CoCart\Functions
 * @since    3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wrapper for deprecated filter so we can apply some extra logic.
 *
 * @since 3.0.0
 * @param string $filter      The filter that was used.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of WordPress that deprecated the filter.
 * @param string $replacement The filter that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_deprecated_filter( $filter, $args = array(), $version, $replacement = null, $message = null ) {
	if ( is_ajax() || CoCart_Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_filter_run', $filter, $args, $replacement, $version, $message );

		$message    = empty( $message ) ? '' : ' ' . $message;
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $filter, $version );
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
 * @param  bool                    $utc  Send false to get local/offset time.
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
 * @param  mixed $pathdata
 * @return void
 */
function cocart_upload_dir( $pathdata ) {
	if ( empty( $pathdata['subdir'] ) ) {
		$pathdata['path']   = $pathdata['path'] . '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
		$pathdata['url']    = $pathdata['url']. '/cocart_uploads/' . md5( WC()->session->get_customer_id() );
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
 * @param  files
 * @return array|WP_Error File data or error message.
 */
function cocart_upload_file( $file ) {
	// wp_handle_upload function is part of wp-admin.
	if ( ! function_exists( 'wp_handle_upload' ) ) {
		include_once( ABSPATH . 'wp-admin/includes/file.php' );
	}

	include_once( ABSPATH . 'wp-admin/includes/media.php' );

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
 * @param array $upload Upload information from wp_upload_bits.
 * @param  int   $id Post ID. Default to 0.
 * @return int Attachment ID
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
