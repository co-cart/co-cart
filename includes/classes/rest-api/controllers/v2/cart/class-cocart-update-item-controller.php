<?php
/**
 * REST API: CoCart_REST_Update_Item_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Update_Item_V2_Controller', 'CoCart_Update_Item_V2_Controller' );

/**
 * Controller for updating an item in the cart (API v2).
 *
 * This REST API controller handles the request to update items in the cart
 * via "cocart/v2/cart/item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Update_Item_V2_Controller extends CoCart_REST_Cart_V2_Controller {

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
		// Update Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_item' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'allow_batch' => array( 'v1' => true ),
			)
		);
	} // END register_routes()

	/**
	 * Update Item in Cart.
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
	public function update_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? 0 : wc_clean( sanitize_text_field( wp_unslash( $request['item_key'] ) ) );
			$quantity = ! isset( $request['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $request['quantity'] ) );

			$item_key = CoCart_Utilities_Cart_Helpers::throw_missing_item_key( $item_key, 'update' );

			// Ensure we have calculated before we handle any data.
			$this->get_cart_instance()->calculate_totals();

			// Allows removing of items if quantity is zero should for example the item was with a product bundle.
			if ( 0 === $quantity ) {
				$controller = new CoCart_REST_Remove_Item_V2_Controller();

				return $controller->remove_item( $request );
			}

			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $item_key, 'container' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				/**
				 * Filters message about cart item key required.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string $message Message.
				 * @param string $method  Method.
				 */
				$message = apply_filters( 'cocart_item_not_in_cart_message', $message, 'update' );

				throw new CoCart_Data_Exception( 'cocart_item_not_in_cart', $message, 404 );
			}

			$product = ! is_null( $current_data['data'] ) ? $current_data['data'] : null;

			// If product data is somehow not there on a rare occasion then we need to get that product data to validate it.
			if ( is_null( $product ) ) {
				$product = wc_get_product( $current_data['variation_id'] ? $current_data['variation_id'] : $current_data['product_id'] );
			}

			$quantity = $this->validate_quantity( $quantity, $product );

			// If validation returned an error return error response.
			if ( is_wp_error( $quantity ) ) {
				return $quantity;
			}

			$has_stock = $this->has_enough_stock( $current_data, $quantity ); // Checks if the item has enough stock before updating.

			// If not true, return error response.
			if ( is_wp_error( $has_stock ) ) {
				return $has_stock;
			}

			/**
			 * Filter allows you to determine if the updated item in cart passed validation.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param bool   $cart_valid   True by default.
			 * @param string $item_key     Item key.
			 * @param array  $current_data Product data of the item in cart.
			 * @param float  $quantity     The requested quantity to change to.
			 */
			$passed_validation = apply_filters( 'cocart_update_cart_validation', true, $item_key, $current_data, $quantity );

			// If validation returned an error return error response.
			if ( is_wp_error( $passed_validation ) ) {
				return $passed_validation;
			}

			// Return error if product is_sold_individually.
			if ( $product->is_sold_individually() && $quantity > 1 ) {
				$message = sprintf(
					/* translators: %s Product name. */
					__( 'You can only have 1 "%s" in your cart.', 'cart-rest-api-for-woocommerce' ),
					$product->get_name()
				);

				/**
				 * Filters message about product not being allowed to increase quantity.
				 *
				 * @since 1.0.0 Introduced.
				 *
				 * @param string     $message Message.
				 * @param WC_Product $product The product object.
				 */
				$message = apply_filters( 'cocart_can_not_increase_quantity_message', $message, $product );

				throw new CoCart_Data_Exception( 'cocart_can_not_increase_quantity', $message, 405 );
			}

			// Only update cart item quantity if passed validation.
			if ( $passed_validation ) {
				if ( $quantity !== $current_data['quantity'] ) {
					$new_data = $this->get_cart_item( $item_key, 'update' );

					$product_id   = ! isset( $new_data['product_id'] ) ? 0 : absint( wp_unslash( $new_data['product_id'] ) );
					$variation_id = ! isset( $new_data['variation_id'] ) ? 0 : absint( wp_unslash( $new_data['variation_id'] ) );
					$product      = wc_get_product( $variation_id ? $variation_id : $product_id );

					if ( $this->get_cart_instance()->set_quantity( $item_key, $quantity ) ) {
						/**
						 * Hook: cocart_item_quantity_changed
						 *
						 * @since 2.0.0 Introduced.
						 *
						 * @param string $item_key Item key.
						 * @param array  $new_data Item data.
						 */
						do_action( 'cocart_item_quantity_changed', $item_key, $new_data );

						/**
						 * Calculates the cart totals if an item has changed it's quantity.
						 *
						 * @since 2.1.0 Introduced.
						 * @since 3.1.0 Changed to calculate all totals.
						 */
						$this->calculate_totals();
					} else {
						$message = __( 'Unable to update item quantity in cart.', 'cart-rest-api-for-woocommerce' );

						/**
						 * Filters message about can not update item.
						 *
						 * @since 2.1.0 Introduced.
						 *
						 * @param string $message Message.
						 */
						$message = apply_filters( 'cocart_can_not_update_item_message', $message );

						throw new CoCart_Data_Exception( 'cocart_can_not_update_item', $message, 400 );
					}
				}

				$response = $this->get_cart_contents( $request );

				// Was it requested to return status once item updated?
				if ( $request['return_status'] ) {
					$response = array();

					// Return response based on product quantity increment.
					if ( $quantity > $current_data['quantity'] ) {
						$response = array(
							'message'  => sprintf(
								/* translators: 1: product name, 2: new quantity */
								__( 'The quantity for "%1$s" has increased to "%2$s".', 'cart-rest-api-for-woocommerce' ),
								$product->get_name(),
								$new_data['quantity']
							),
							'quantity' => $new_data['quantity'],
						);
					} elseif ( $quantity < $current_data['quantity'] ) {
						$response = array(
							'message'  => sprintf(
								/* translators: 1: product name, 2: new quantity */
								__( 'The quantity for "%1$s" has decreased to "%2$s".', 'cart-rest-api-for-woocommerce' ),
								$product->get_name(),
								$new_data['quantity']
							),
							'quantity' => $new_data['quantity'],
						);
					} else {
						$response = array(
							'message'  => sprintf(
								/* translators: %s: product name */
								__( 'The quantity for "%s" has not changed.', 'cart-rest-api-for-woocommerce' ),
								$product->get_name()
							),
							'quantity' => $quantity,
						);
					}

					/**
					 * Filters the update status.
					 *
					 * @since 2.0.1 Introduced.
					 *
					 * @param array      $response Status response.
					 * @param array      $new_data Cart item.
					 * @param int        $quantity Quantity.
					 * @param WC_Product $product  The product object.
					 */
					$response = apply_filters( 'cocart_update_item', $response, $new_data, $quantity, $product );
				}

				return CoCart_Response::get_response( $response, $this->namespace, $this->rest_base );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END update_item()

	/**
	 * Get the query params for updating an item.
	 *
	 * @access public
	 *
	 * @since 3.0.0  Introduced.
	 * @since 4.0.0 Updated quantity parameter to validate any number values.
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Update item query parameters.
		$params += array(
			'item_key'      => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'      => array(
				'description'       => __( 'Quantity of this item to update to.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => true,
				'validate_callback' => array( $this, 'rest_validate_quantity_arg' ),
			),
			'return_status' => array(
				'description'       => __( 'Returns a message and quantity value after updating item in cart.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
