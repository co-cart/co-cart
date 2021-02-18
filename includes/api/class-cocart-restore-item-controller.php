<?php
/**
 * CoCart - Restore Item controller
 *
 * Handles the request to restore items in the cart with /cart/restore-item endpoint.
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
 * REST API Restore Item controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Item_v2_Controller
 */
class CoCart_Restore_Item_v2_Controller extends CoCart_Item_v2_Controller {

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
	protected $rest_base = 'cart/restore-item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Restore Item - cocart/v2/restore-item (PUT)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args' => $this->get_collection_params(),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'restore_item' ),
				'permission_callback' => '__return_true',
			),
		) );
	} // register_routes()

	/**
	 * Restore Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function restore_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '0' : sanitize_text_field( wp_unslash( wc_clean( $request['item_key'] ) ) );

			if ( 0 === $item_key || $item_key < 0 ) {
				$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about cart item key required.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_cart_item_key_required_message', $message, 'update' );

				throw new CoCart_Data_Exception( 'cocart_cart_item_key_required', $message, 404 );
			}

			$controller = new CoCart_Cart_V2_Controller();

			if ( $controller->get_cart_instance()->restore_cart_item( $item_key ) ) {
				$restored_item = $controller->get_cart_item( $item_key, 'restore' ); // Fetches the cart item data once it is restored.

				do_action( 'cocart_item_restored', $restored_item );

				/**
				 * Calculates the cart totals now an item has been restored.
				 *
				 * @since 2.1.0
				 */
				$controller->get_cart_instance()->calculate_totals();

				// Get cart contents.
				$response = $controller->get_cart_contents( $request );

				// Was it requested to return just the restored item?
				if ( $request['return_item'] ) {
					$response = $restored_item;
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to restore item to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not restore item.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_restore_item_message', $message );

				throw new CoCart_Data_Exception( 'cocart_can_not_restore_item', $message, 403 );
			}
		} catch( CoCart_Data_Exception $e) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END restore_item()

	/**
	 * Get the query params for restoring an item.
	 *
	 * @access public
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = array(
			'item_key' => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_item'   => array(
				'description' => __( 'Returns the item details once restored.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
