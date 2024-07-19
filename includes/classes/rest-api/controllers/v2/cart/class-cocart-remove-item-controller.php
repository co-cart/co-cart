<?php
/**
 * REST API: CoCart_REST_Remove_Item_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0 Introduced.
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Remove_Item_V2_Controller', 'CoCart_Remove_Item_V2_Controller' );

/**
 * Controller for removing items from the cart (API v2).
 *
 * This REST API controller handles the request to remove items
 * in the cart via "cocart/v2/cart/item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Remove_Item_V2_Controller extends CoCart_REST_Cart_V2_Controller {

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
		// Remove Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (DELETE).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'remove_item' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // END register_routes()

	/**
	 * Removes an Item in Cart.
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
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function remove_item( $request = array() ) {
		try {
			$request_params = $request->get_params();
			$item_key       = ! isset( $request_params['item_key'] ) ? '0' : wc_clean( sanitize_text_field( wp_unslash( $request_params['item_key'] ) ) );

			$item_key = CoCart_Utilities_Cart_Helpers::throw_missing_item_key( $item_key, 'remove' );

			// Ensure we have calculated before we handle any data.
			$this->get_cart_instance()->calculate_totals();

			// Checks to see if the cart contains item before attempting to remove it.
			if ( $this->get_cart_instance()->get_cart_contents_count() <= 0 && count( $this->get_cart_instance()->get_removed_cart_contents() ) <= 0 ) {
				$message = __( 'No items in cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about no items in cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_no_items_message', $message );

				throw new CoCart_Data_Exception( 'cocart_no_items', $message, 404 );
			}

			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $item_key, 'remove' );

			$product = wc_get_product( $current_data['product_id'] );

			$item_removed_title = apply_filters( 'cocart_cart_item_removed_title', $product ? sprintf(
				/* translators: %s: Item name. */
				_x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ),
				$product->get_name()
			) : __( 'Item', 'cart-rest-api-for-woocommerce' ), $current_data );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$removed_contents = $this->get_cart_instance()->get_removed_cart_contents();

				// Check if the item has already been removed.
				if ( isset( $removed_contents[ $item_key ] ) ) {
					$product = wc_get_product( $removed_contents[ $item_key ]['product_id'] );

					$item_already_removed_title = apply_filters( 'cocart_cart_item_already_removed_title', $product ? sprintf(
						/* translators: %s: Item name. */
						_x( '"%s"', 'Item name in quotes', 'cart-rest-api-for-woocommerce' ),
						$product->get_name()
					) : __( 'Item', 'cart-rest-api-for-woocommerce' ) );

					$message = sprintf(
						/* translators: %s: Item name. */
						__( '%s has already been removed from cart.', 'cart-rest-api-for-woocommerce' ),
						$item_already_removed_title
					);
				} else {
					$message = sprintf(
						/* translators: %s: Item name. */
						__( '%s does not exist in cart.', 'cart-rest-api-for-woocommerce' ),
						$item_removed_title
					);
				}

				/**
				 * Filters message about item removed from cart.
				 *
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_item_removed_message', $message );

				throw new CoCart_Data_Exception( 'cocart_item_not_in_cart', $message, 404 );
			}

			if ( $this->get_cart_instance()->remove_cart_item( $item_key ) ) {
				/**
				 * Hook: cocart_item_removed
				 *
				 * @since 2.0.0 Introduced.
				 *
				 * @param WC_Product $current_data Product data.
				 */
				do_action( 'cocart_item_removed', $current_data );

				/**
				 * Calculates the cart totals now an item has been removed.
				 *
				 * @since 2.1.0 Introduced.
				 */
				$this->get_cart_instance()->calculate_totals();

				$message = sprintf(
					/* translators: %s: Item name. */
					__( '%s has been removed from cart.', 'cart-rest-api-for-woocommerce' ),
					$item_removed_title
				);

				// Add notice.
				wc_add_notice( $message );

				$response = $this->get_cart_contents( $request );

				// Was it requested to return status once item removed?
				if ( $request['return_status'] ) {
					/* translators: %s: Item name. */
					$response = $message;
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			} else {
				$message = __( 'Unable to remove item from cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about can not remove item.
				 *
				 * @since 2.1.0 Introduced.
				 *
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
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Remove item parameters.
		$params += array(
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
