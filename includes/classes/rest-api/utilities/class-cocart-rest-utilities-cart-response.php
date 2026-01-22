<?php
/**
 * REST API Utilities: Cart Response class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Utilities
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Response class.
 */
class CoCart_REST_Utilities_Cart_Response {

	/**
	 * Add cart headers to a response object.
	 *
	 * @access public
	 *
	 * @param WP_REST_Response $response Reference to the response object.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function add_headers( $response, $request ) {
		$cart_hash       = WC()->session->get_cart_hash();
		$cart_expiring   = WC()->session->get_cart_is_expiring();
		$cart_expiration = WC()->session->get_carts_expiration();

		// Get cart key.
		$cart_key = CoCart_Utilities_Cart_Helpers::get_cart_key();

		// Send cart key in the header if it's not empty or ZERO.
		if ( ! empty( $cart_key ) && '0' !== $cart_key ) {
			$response->header( 'Cart-Key', $cart_key );
		}

		// Send cart hash in the header if it's not empty.
		if ( ! empty( $cart_hash ) ) {
			$response->header( 'Cart-Hash', $cart_hash );
		}
		$response->header( 'Cart-Expiring', $cart_expiring );
		$response->header( 'Cart-Expiration', $cart_expiration );

		return $response;
	} // END add_headers()
} // END class.
