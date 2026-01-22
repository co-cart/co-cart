<?php
/**
 * REST API: CoCart_REST_Session_Delete_V2_Controller class
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
 * Controller for deleting a specific cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to delete a singular cart
 * via "cocart/v2/session" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @see CoCart_REST_Session_V2_Controller
 */
class CoCart_REST_Session_Delete_V2_Controller extends CoCart_REST_Session_V2_Controller {

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/session/(?P<session_key>[\w]+)';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_cart' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			'allow_batch' => array( 'v1' => true ),
		);
	} // END get_args()

	/**
	 * Backwards compatible for registering route.
	 *
	 * @access public
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Delete Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (DELETE).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to edit site data.
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
		if ( ! wc_rest_check_manager_permissions( 'settings', 'edit' ) ) {
			return new \WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Deletes the cart in session. Once a Cart has been deleted it cannot be recovered.
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
	public function delete_cart( $request ) {
		try {
			$session_key = ! empty( $request['session_key'] ) ? $request['session_key'] : '';

			if ( empty( $session_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_key_missing', __( 'Session Key is required!', 'cocart-core' ), 404 );
			}

			// If no session is saved with the ID specified return error.
			if ( empty( WC()->session->get_session( $session_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_not_valid', __( 'Session is not valid!', 'cocart-core' ), 404 );
			}

			// Delete cart session.
			WC()->session->delete_cart( $session_key );

			if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
				delete_user_meta( $session_key, '_woocommerce_persistent_cart_' . get_current_blog_id() );
			}

			if ( ! empty( WC()->session->get_session( $session_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_not_deleted', __( 'Session could not be deleted!', 'cocart-core' ), 500 );
			}

			$response = rest_ensure_response( __( 'Session successfully deleted!', 'cocart-core' ) );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END delete_cart()
} // END class
