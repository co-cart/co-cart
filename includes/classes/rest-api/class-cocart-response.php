<?php
/**
 * REST API: CoCart_Response class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the REST API response even if it returns an error.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_Response {

	/**
	 * Returns either the default response of the API requested or a filtered response.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added two response headers; a timestamp and the version of CoCart.
	 * @since 3.3.0 Added new custom headers without the prefix `X-`
	 *
	 * @param mixed  $data       The original data response of the API requested.
	 * @param string $name_space The namespace of the API requested.
	 * @param string $endpoint   The endpoint of the API requested.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public static function get_response( $data, $name_space = '', $endpoint = '' ) {
		try {
			if ( empty( $endpoint ) ) {
				$endpoint = 'cart';
			}

			$raw_endpoint = $endpoint;
			$endpoint     = str_replace( '-', '_', $endpoint );

			/**
			 * Filter decides if the responses return as default or
			 * use the modified response which is filtered by the rest base.
			 *
			 * @see cocart_{$endpoint}_response
			 *
			 * @since 3.0.0 Introduced.
			 */
			$default_response = apply_filters( 'cocart_return_default_response', true );

			if ( ! $default_response ) {
				/**
				 * Filter is to be used as a final straw for changing the response
				 * based on the endpoint.
				 *
				 * @since 3.0.0 Introduced.
				 */
				$data = apply_filters( 'cocart_' . $endpoint . '_response', $data );
			}

			/**
			 * Data can only return empty if:
			 *
			 * 1. Something seriously has gone wrong server side and no data could be provided.
			 * 2. The response returned nothing because the cart is empty.
			 * 3. The developer filtered the response incorrectly and returned nothing.
			 */
			$endpoints = array(
				'cart',
				'session',
				'cart/items',
				'cart/items/count',
			);

			foreach ( $endpoints as $route ) {
				if ( $route !== $endpoint && empty( $data ) ) {
					throw new CoCart_Data_Exception(
						'cocart_response_returned_empty',
						sprintf(
							/* translators: %s: REST API URL */
							__( 'Request returned nothing for "%s"! Please seek assistance.', 'cart-rest-api-for-woocommerce' ),
							rest_url( sprintf( '/%s/%s/', $name_space, $endpoint ) )
						)
					);
				}
			}

			// Return response.
			$response = rest_ensure_response( $data );

			// Add timestamp of response.
			$response->header( 'CoCart-Timestamp', time() );

			// Add version of CoCart.
			$response->header( 'CoCart-Version', COCART_VERSION );

			// Returns additional headers for the cart endpoint.
			if ( strpos( $raw_endpoint, 'cart' ) !== false ) {
				$cart_expiring   = WC()->session->get_cart_is_expiring();
				$cart_expiration = WC()->session->get_carts_expiration();

				// Get cart key.
				$cart_key = CoCart_Utilities_Cart_Helpers::get_cart_key();

				// Send cart key in the header if it's not empty or ZERO.
				if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
					$response->header( 'CoCart-API-Cart-Key', $cart_key );
				}

				$response->header( 'CoCart-API-Cart-Expiring', $cart_expiring );
				$response->header( 'CoCart-API-Cart-Expiration', $cart_expiration );
			}

			return $response;
		} catch ( \CoCart_Data_Exception $e ) {
			$response = self::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		} catch ( \Exception $e ) {
			$response = self::get_error_response( 'cocart_unknown_server_error', $e->getMessage(), 500 );
		}

		if ( is_wp_error( $response ) ) {
			$response = self::error_to_response( $response );
		}

		return $response;
	} // END get_response()

	/**
	 * Converts an error to a response object. Based on \WP_REST_Server.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Added debug backtrace if WP_DEBUG is true.
	 *
	 * @param WP_Error $error WP_Error instance.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public static function error_to_response( $error ) {
		$error_data = $error->get_error_data();
		$status     = isset( $error_data, $error_data['status'] ) ? $error_data['status'] : 500;
		$errors     = array();

		foreach ( (array) $error->errors as $code => $messages ) {
			foreach ( (array) $messages as $message ) {
				$errors[ $code ] = array(
					'code'    => $code,
					'message' => $message,
					'data'    => $error->get_error_data( $code ),
				);

				if ( function_exists( 'debug_backtrace' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$errors[ $code ]['trace'] = debug_backtrace(); // phpcs:ignore PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection, WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace
				}
			}
		}

		$data = array_shift( $errors );

		if ( count( $errors ) ) {
			$data['additional_errors'] = $errors;
		}

		return new \WP_REST_Response( $data, $status );
	} // END error_to_response()

	/**
	 * Get route response when something went wrong.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $error_code String based error code.
	 * @param string $error_message User facing error message.
	 * @param int    $http_status_code HTTP status. Defaults to 500.
	 * @param array  $additional_data  Extra data (key value pairs) to expose in the error response.
	 *
	 * @return \WP_Error WP Error object.
	 */
	public static function get_error_response( $error_code, $error_message, $http_status_code = 500, $additional_data = array() ) {
		return new \WP_Error( $error_code, $error_message, array_merge( $additional_data, array( 'status' => $http_status_code ) ) );
	}
} // END class
