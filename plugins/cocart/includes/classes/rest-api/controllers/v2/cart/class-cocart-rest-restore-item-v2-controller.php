<?php
/**
 * REST API: CoCart_REST_Restore_Item_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for restoring an item to the cart (API v2).
 *
 * This REST API controller handles the request to restore items in the cart
 * via "cocart/v2/cart/item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Restore_Item_v2_Controller extends CoCart_REST_Cart_v2_Controller {

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
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Restore Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (PUT).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'restore_item' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // register_routes()

	/**
	 * Restores an Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.7.8
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function restore_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '0' : wc_clean( wp_unslash( sanitize_text_field( $request['item_key'] ) ) );

			$item_key = $this->throw_missing_item_key( $item_key, 'restore' );

			// Check item removed from cart before fetching the cart item data.
			$current_data = $this->get_cart_instance()->get_removed_cart_contents();

			// If item does not exist as an item removed check if the item is in the cart.
			if ( empty( $current_data ) ) {
				$restored_item = $this->get_cart_item( $item_key, 'restore' );

				// Check if the item has already been restored.
				if ( ! empty( $restored_item ) ) {
					$product = wc_get_product( $restored_item['product_id'] );

					/* translators: %s: Item name. */
					$item_already_restored_title = apply_filters( 'cocart_cart_item_already_restored_title', $product ? sprintf( _x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ) );

					/* translators: %s: Item name. */
					$message       = sprintf( __( '%s has already been restored to the cart.', 'cart-rest-api-for-woocommerce' ), $item_already_restored_title );
					$response_code = 405;
				} else {
					$message       = __( 'Item does not exist in cart.', 'cart-rest-api-for-woocommerce' );
					$response_code = 404;
				}

				/**
				 * Filters message about item already restored to cart.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_item_restored_message', $message );

				throw new CoCart_Data_Exception( 'cocart_item_restored_to_cart', $message, $response_code );
			}

			if ( $this->get_cart_instance()->restore_cart_item( $item_key ) ) {
				$restored_item = $this->get_cart_item( $item_key, 'restore' ); // Fetches the cart item data once it is restored.

				do_action( 'cocart_item_restored', $restored_item );

				/**
				 * Calculates the cart totals now an item has been restored.
				 *
				 * @since 2.1.0 Introduced.
				 */
				$this->get_cart_instance()->calculate_totals();

				$product = wc_get_product( $restored_item['product_id'] );

				/* translators: %s: Item name. */
				$item_restored_title = apply_filters( 'cocart_cart_item_restored_title', $product ? sprintf( _x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ), $product->get_name() ) : __( 'Item', 'cart-rest-api-for-woocommerce' ) );

				/* translators: %s: product name */
				$restored_message = sprintf( __( '%s has been added back to your cart.', 'cart-rest-api-for-woocommerce' ), $item_restored_title );

				// Add notice.
				wc_add_notice( apply_filters( 'cocart_cart_item_restored_message', $restored_message ), 'success' );

				// Get cart contents.
				$response = $this->get_cart_contents( $request );

				// Was it requested to return just the restored item?
				if ( $request['return_item'] ) {
					$response = $this->get_item( $restored_item['data'], $restored_item, $restored_item['key'], true );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to restore item to the cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not restore item.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_restore_item_message', $message );

				throw new CoCart_Data_Exception( 'cocart_can_not_restore_item', $message, 403 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END restore_item()

	/**
	 * Get the query params for restoring an item.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Restore item parameters.
		$params += array(
			'item_key'    => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'return_item' => array(
				'description'       => __( 'Returns the item details once restored.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
