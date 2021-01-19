<?php
/**
 * CoCart REST API Session controller.
 *
 * Returns specified details from a specific session.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API Session v2 controller class.
 *
 * @package CoCart REST API/API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Session_V2_Controller extends CoCart_Cart_V2_Controller {

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
	protected $rest_base = 'session';

	/**
	 * Register the routes for index.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<cart_key>[\w]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_in_session' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );

		// Get Cart Items in Session - cocart/v2/session/items/ec2b1f30a304ed513d2975b7b9f222f6 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/items/(?P<cart_key>[\w]+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_items_in_session' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );
	} // register_routes()

	/**
	 * Returns a saved cart in session if one exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   array $request   The cart key is a required variable.
	 * @return  WP_REST_Response Returns the cart data from the database.
	 */
	public function get_cart_in_session( $request = array() ) {
		$cart_key = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';

		try {
			if ( empty( $cart_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Get the cart in the database.
			$handler = new CoCart_Session_Handler();
			$cart    = $handler->get_cart( $cart_key );

			// If no cart is saved with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $this->return_cart_contents( $request, maybe_unserialize( $cart['cart'] ), '', true ), $this->namespace, $this->rest_base );
		} catch( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_in_session()

	/**
	 * Returns the cart items from the session.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  array $request The cart key is a required variable.
	 * @return WP_REST_Response 
	 */
	public function get_cart_items_in_session( $request = array() ) {
		$cart_key   = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		try {
			if ( empty( $cart_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Get the cart in the database.
			$handler = new CoCart_Session_Handler();
			$cart    = $handler->get_cart( $cart_key );

			// If no cart is saved with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $this->get_items( maybe_unserialize( $cart['cart'] ), $show_thumb ), $this->namespace, $this->rest_base );
		} catch( CoCart_Data_Exception $e) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_items_in_session()

} // END class
