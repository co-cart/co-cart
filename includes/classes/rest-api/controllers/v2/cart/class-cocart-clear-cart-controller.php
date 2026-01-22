<?php
/**
 * REST API: CoCart_REST_Clear_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Clear_Cart_V2_Controller', 'CoCart_Clear_Cart_V2_Controller' );

/**
 * Controller for clearing the cart (API v2).
 *
 * This REST API controller handles the request to clear the cart
 * via "cocart/v2/cart/clear" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Clear_Cart_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/clear';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/clear';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clear_cart' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
		);
	} // END get_args()

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Clear Cart - cocart/v2/cart/clear (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Clears the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 5.0.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function clear_cart( $request ) {
		try {
			// We need the cart key to force a session save later.
			$cart_key = WC()->session->get_customer_unique_id();

			$cart = $this->get_cart_instance();

			// Ensure we have calculated before we handle data.
			$cart->calculate_totals();

			/**
			 * Hook: Triggers before the cart emptied.
			 *
			 * @since 1.0.0 Introduced.
			 * @since 5.0.0 Added the request object as parameter.
			 *
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'cocart_before_cart_emptied', $request );

			// Clear all cart fees via session as we cant do it via the fee api.
			WC()->session->set( 'cart_fees', array() );

			// Cache removed content should requested to keep it.
			$removed_contents = $cart->get_removed_cart_contents();

			// Clear cart.
			$cart->empty_cart();

			// Clear removed items if not kept.
			if ( ! $request['keep_removed_items'] ) {
				$cart->set_removed_cart_contents( array() );
			} else {
				$cart->set_removed_cart_contents( $removed_contents );
			}

			/**
			 * Hook: Triggers once the cart is emptied.
			 *
			 * @since 1.0.0 Introduced.
			 * @since 5.0.0 Added the request object as parameter.
			 *
			 * @param WP_REST_Request $request The request object.
			 */
			do_action( 'cocart_cart_emptied', $request );

			// Ensure we have calculated to update the cart.
			$cart->calculate_totals();

			/**
			 * We force the session to update in the database as we
			 * cannot wait for PHP to shutdown to trigger the save
			 * should it fail to do so later.
			 */
			WC()->session->update_cart( $cart_key );

			if ( $cart->is_empty() ) {
				/**
				 * Hook: Triggers once the cart is cleared.
				 *
				 * @since 1.0.0 Introduced.
				 * @since 5.0.0 Added the request object as parameter.
				 *
				 * @param WP_REST_Request $request The request object.
				 */
				do_action( 'cocart_cart_cleared', $request );

				// Notice message.
				$message = __( 'Cart is cleared.', 'cocart-core' );

				/**
				 * Filters message about the cart being cleared.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_cart_cleared_message', $message );

				// Add notice.
				wc_add_notice( $message, 'notice' );

				// Makes sure the cart hash is correct before the headers return.
				WC()->session->set_cart_hash();

				// Return cart response.
				$request['dont_check'] = true;
				$response              = $this->get_cart( $request );

				$response = rest_ensure_response( $response );
				$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

				return $response;
			} else {
				// Notice message.
				$message = __( 'Clearing the cart failed!', 'cocart-core' );

				/**
				 * Filters message about the cart failing to clear.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_clear_cart_failed_message', $message );

				throw new CoCart_Data_Exception( 'cocart_clear_cart_failed', $message, 406 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END clear_cart()

	/**
	 * Get the query params for clearing the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params['keep_removed_items'] = array(
			'required'          => false,
			'default'           => false,
			'description'       => __( 'Keeps removed items in session when clearing the cart.', 'cocart-core' ),
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	} // END get_collection_params()
} // END class
