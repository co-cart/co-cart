<?php
/**
 * REST API: CoCart_REST_Clear_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 4.2.0
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
	protected $rest_base = 'cart/clear';

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
		// Clear Cart - cocart/v2/cart/clear (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'clear_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
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
	 * @version 4.2.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function clear_cart( $request = array() ) {
		try {
			// We need the cart key to force a session save later.
			$cart_key = WC()->session->get_customer_unique_id();

			// Ensure we have calculated before we handle data.
			$this->get_cart_instance()->calculate_totals();

			/**
			 * Hook: Triggers before the cart emptied.
			 *
			 * @since 1.0.0 Introduced.
			 */
			do_action( 'cocart_before_cart_emptied' );

			// Clear all cart fees via session as we cant do it via the fee api.
			WC()->session->set( 'cart_fees', array() );

			// Cache removed content should requested to keep it.
			$removed_contents = $this->get_cart_instance()->get_removed_cart_contents();

			// Clear cart.
			$this->get_cart_instance()->empty_cart();

			// Clear removed items if not kept.
			if ( ! $request['keep_removed_items'] ) {
				$this->get_cart_instance()->set_removed_cart_contents( array() );
			} else {
				$this->get_cart_instance()->set_removed_cart_contents( $removed_contents );
			}

			/**
			 * Hook: Triggers once the cart is emptied.
			 *
			 * @since 1.0.0 Introduced.
			 */
			do_action( 'cocart_cart_emptied' );

			// Ensure we have calculated to update the cart.
			$this->get_cart_instance()->calculate_totals();

			/**
			 * We force the session to update in the database as we
			 * cannot wait for PHP to shutdown to trigger the save
			 * should it fail to do so later.
			 */
			WC()->session->update_cart( $cart_key );

			if ( $this->get_cart_instance()->is_empty() ) {
				/**
				 * Hook: Triggers once the cart is cleared.
				 *
				 * @since 1.0.0 Introduced.
				 */
				do_action( 'cocart_cart_cleared' );

				// Notice message.
				$message = __( 'Cart is cleared.', 'cart-rest-api-for-woocommerce' );

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

				// Return cart response.
				$response = $this->get_cart_contents( $request );

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				// Notice message.
				$message = __( 'Clearing the cart failed!', 'cart-rest-api-for-woocommerce' );

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
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
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
			'description'       => __( 'Keeps removed items in session when clearing the cart.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	} // END get_collection_params()
} // END class
