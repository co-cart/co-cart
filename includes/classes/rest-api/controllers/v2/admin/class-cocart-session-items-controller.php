<?php
/**
 * REST API: CoCart_REST_Session_Items_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Sessions\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for returning items in a specific cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to return items in a singular cart
 * via "cocart/v2/session" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @see CoCart_REST_Session_V2_Controller
 */
class CoCart_REST_Session_Items_V2_Controller extends CoCart_REST_Session_V2_Controller {

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public static function get_path_regex() {
		return '/session/(?P<session_key>[\w]+)/items';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_items_in_session' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ), // Needs fixing!!
		);
	} // END get_args()

	/**
	 * Register the routes for index.
	 *
	 * @access public
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Get Cart Items in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6/items (GET).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new \WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns the cart items from the session.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function get_cart_items_in_session( $request ) {
		$session_key = ! empty( $request['session_key'] ) ? $request['session_key'] : '';
		$show_thumb  = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		try {
			// The cart key is a required variable.
			if ( empty( $session_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_key_missing', __( 'Session Key is required!', 'cocart-core' ), 404 );
			}

			// Get the cart in the database.
			$cart = WC()->session->get_session( $session_key );

			// If no cart is saved with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cocart-core' ), 404 );
			}

			$session_data = $this->get_items( maybe_unserialize( $cart['cart'] ), $request );

			$response = rest_ensure_response( $session_data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_cart_items_in_session()
} // END class
