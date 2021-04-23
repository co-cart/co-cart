<?php
/**
 * CoCart - Remove Item controller
 *
 * Handles the request to remove items in the cart with /cart/item endpoint.
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
 * REST API Remove Item controller class.
 *
 * @package CoCart\API
 */
class CoCart_Remove_Item_v2_Controller extends CoCart_Item_Controller {

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
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Remove Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (DELETE)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * Removes an Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 3.0.0
	 * @param   WP_REST_Request $request Full details about the request.
	 * @return  WP_REST_Response
	 */
	public function remove_item( $request = array() ) {
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

			// Checks to see if the cart is empty before attempting to remove item.
			if ( $controller->get_cart_instance()->is_empty() ) {
				$message = __( 'No items in cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about no items in cart.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_no_items_message', $message );

				throw new CoCart_Data_Exception( 'cocart_no_items', $message, 404 );
			}

			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $controller->get_cart_item( $item_key, 'remove' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about item not in cart.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_item_not_in_cart_message', $message, 'remove' );

				throw new CoCart_Data_Exception( 'cocart_item_not_in_cart', $message, 404 );
			}

			if ( $controller->get_cart_instance()->remove_cart_item( $item_key ) ) {
				do_action( 'cocart_item_removed', $current_data );

				/**
				 * Calculates the cart totals now an item has been removed.
				 *
				 * @since 2.1.0
				 */
				$controller->get_cart_instance()->calculate_totals();

				$response = $controller->get_cart_contents( $request );

				$product = wc_get_product( $current_data['product_id'] );

				/* translators: %s: Item name. */
				$item_removed_title = apply_filters( 'cocart_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ), $current_data );

				// Was it requested to return status once item removed?
				if ( $request['return_status'] ) {
					/* translators: %s: Item name. */
					$response = sprintf( __( '%s has been removed from cart.', 'cart-rest-api-for-woocommerce' ), $item_removed_title );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to remove item from cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not remove item.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_remove_item_message', $message );

				throw new CoCart_Data_Exception( 'cocart_can_not_remove_item', $message, 403 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END remove_item()

	/**
	 * Get the query params for item.
	 *
	 * @access public
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = array(
			'item_key'      => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_status' => array(
				'description'       => __( 'Returns a message after removing item from cart.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
