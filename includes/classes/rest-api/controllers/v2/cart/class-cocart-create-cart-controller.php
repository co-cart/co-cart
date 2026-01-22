<?php
/**
 * REST API: CoCart_REST_Create_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for creating the cart (API v2).
 *
 * This REST API controller handles the request to create the cart
 * via "cocart/v2/cart" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_Controller
 */
class CoCart_REST_Create_Cart_V2_Controller extends CoCart_REST_Cart_Controller {

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_cart' ),
				'permission_callback' => array( $this, 'get_permission_callback' ),
				'args'                => array(),
			),
			'allow_batch' => array( 'v1' => true ),
		);
	} // END get_args()

	/**
	 * Check if request has permission to create a cart.
	 *
	 * @access public
	 *
	 * @return WP_Error|boolean
	 */
	public function get_permission_callback() {
		if ( strval( get_current_user_id() ) > 0 ) {
			return new \WP_Error( 'cocart_rest_cart_creation_not_allowed', __( 'You are already logged in so a cart is already created for you.', 'cocart-core' ), array( 'status' => 403 ) );
		}

		return true;
	} // END get_permission_callback()

	/**
	 * Creates a cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function create_cart( $request ) {
		try {
			// Get a cart key.
			$cart_key = WC()->session->get_customer_unique_id();

			// Store the cart key in session so the cart can be created.
			WC()->session->set( 'cart_key', $cart_key );

			/**
			 * We force the session to update in the database as we
			 * cannot wait for PHP to shutdown to trigger the save
			 * should it fail to do so later.
			 */
			WC()->session->update_cart( $cart_key );

			/**
			 * Triggers when a cart is created.
			 *
			 * @since 5.0.0 Introduced.
			 *
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'cocart_cart_created', $request );

			$response = array(
				'message'  => __( 'Here is your cart key. Either use it as a global parameter or set the cart key via the header for all future Cart API requests. See "Cart Key" section in the documentation to learn more.', 'cocart-core' ),
				'cart_key' => $cart_key,
			);

			$response = rest_ensure_response( $response );
			$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END create_cart()
} // END class
