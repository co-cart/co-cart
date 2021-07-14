<?php
/**
 * CoCart REST API Session controller.
 *
 * Returns specified details from a specific session.
 *
 * @author   Sébastien Dumont
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
		// Get Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Delete Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (DELETE).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_cart' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// Get Cart Items in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_items_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns a saved cart in session if one exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response          Returns the cart data from the database.
	 */
	public function get_cart_in_session( $request = array() ) {
		$cart_key = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';

		try {
			// The cart key is a required variable.
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
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_in_session()

	/**
	 * Deletes the cart in session. Once a Cart has been deleted it can not be recovered.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function delete_cart( $request = array() ) {
		try {
			$cart_key = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';

			if ( empty( $cart_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$handler = new CoCart_Session_Handler();

			// If no cart is saved with the ID specified return error.
			if ( empty( $handler->get_cart( $cart_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$handler->delete_cart( $cart_key );

			if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
				delete_user_meta( $cart_key, '_woocommerce_persistent_cart_' . get_current_blog_id() );
			}

			if ( ! empty( $handler->get_cart( $cart_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_not_deleted', __( 'Cart could not be deleted!', 'cart-rest-api-for-woocommerce' ), 500 );
			}

			return CoCart_Response::get_response( __( 'Cart successfully deleted!', 'cart-rest-api-for-woocommerce' ), $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END delete_cart()

	/**
	 * Returns the cart items from the session.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response         Returns the cart items from the session.
	 */
	public function get_cart_items_in_session( $request = array() ) {
		$cart_key   = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		try {
			// The cart key is a required variable.
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
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_items_in_session()

} // END class
