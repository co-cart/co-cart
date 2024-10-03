<?php
/**
 * REST API: CoCart_REST_Create_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   4.4.0 Introduced.
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
 * @since 4.4.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Create_Cart_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Create Cart - cocart/v2/cart (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_cart' ),
					'permission_callback' => array( $this, 'get_permission_callback' ),
					'args'                => array(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // END register_routes()

	/**
	 * Check if request has permission to create a cart.
	 *
	 * @access public
	 *
	 * @return WP_Error|boolean
	 */
	public function get_permission_callback() {
		if ( strval( get_current_user_id() ) > 0 ) {
			return new WP_Error( 'cocart_rest_cart_creation_not_allowed', __( 'You are already logged in so a cart is already created for you.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
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
	public function create_cart( $request = array() ) {
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

			$response = array(
				'message'  => __( 'Here is your cart key. Either use it as a global parameter or set the CoCart cart key header for all future Cart API requests. See "Cart Key" section in the documentation to learn more.', 'cart-rest-api-for-woocommerce' ),
				'cart_key' => $cart_key,
			);

			return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END create_cart()
} // END class
