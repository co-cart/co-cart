<?php
/**
 * CoCart - Cart controller
 *
 * Handles requests to the cart endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v3
 * @since   4.0.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v3 - Cart controller class.
 *
 * @package CoCart REST API/API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Cart_V3_Controller extends CoCart_Cart_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v3';

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
	 */
	public function register_routes() {
		// Get Cart - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Get Cart Items - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6/items (GET).
		/*register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_items' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				//'schema' => array( $this, 'get_item_schema' ),
			)
		);*/

		// Count Items in Cart - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6/items/count (GET).
		/*register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)/items/count',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'count_cart_items' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				//'schema' => array( $this, 'get_item_schema' ),
			)
		);*/
	} // register_routes()

	/**
	 * Checks whether the requested cart exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function validate_cart( $request ) {
		$cart_key = ! empty( $request['cart_key'] ) ? trim( $request['cart_key'] ) : '';

		try {
			// The cart key is a required variable.
			if ( empty( $cart_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Load session handler.
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';

			$handler = new CoCart_Session_Handler();

			// Get the cart in the database.
			$cart = $handler->get_cart( $cart_key );

			// TODO: Add possibly validation of the cart expiration.

			// If no cart with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_not_valid', __( 'Cart is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return true;
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_cart()

} // END class
