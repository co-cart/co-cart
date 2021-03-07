<?php
/**
 * CoCart - Clear Cart controller
 *
 * Handles the request to clear the cart with /cart/clear endpoint.
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
 * REST API Clear Cart controller class.
 *
 * @package CoCart\API
 */
class CoCart_Clear_Cart_v2_Controller extends CoCart_Clear_Cart_Controller {

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
	 */
	public function register_routes() {
		// Clear Cart - cocart/v2/cart/clear (POST)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'clear_cart' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

	/**
	 * Clears the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @return  WP_REST_Response
	 */
	public function clear_cart() {
		try {
			$controller = new CoCart_Cart_V2_Controller();

			$controller->get_cart_instance()->empty_cart();

			if ( $controller->get_cart_instance()->is_empty() ) {
				do_action( 'cocart_cart_cleared' );

				$message = __( 'Cart is cleared.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'notice' );

				/**
				 * Filters message about the cart being cleared.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_cart_cleared_message', $message );

				return CoCart_Response::get_response( $message, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Clearing the cart failed!', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about the cart failing to clear.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_clear_cart_failed_message', $message );

				throw new CoCart_Data_Exception( 'cocart_clear_cart_failed', $message, 404 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END clear_cart()

} // END class
