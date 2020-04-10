<?php
/**
 * CoCart - Item controller
 *
 * Handles the request to update, delete and restore items in the cart with /item endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Item controller class.
 *
 * @package CoCart/API
 */
class CoCart_Item_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Update, Remove or Restore Item - cocart/v1/item (GET, POST, DELETE)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'args' => array(
				'cart_item_key' => array(
					'description' => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'refresh_totals' => array(
					'description' => __( 'Re-calculates the totals once item has been updated.', 'cart-rest-api-for-woocommerce' ),
					'default'     => true,
					'type'        => 'boolean',
				),
				'return_cart' => array(
					'description' => __( 'Returns the whole cart to reduce API requests.', 'cart-rest-api-for-woocommerce' ),
					'default'     => false,
					'type'        => 'boolean',
				)
			),
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'restore_item' ),
			),
			array(
				'methods'  => WP_REST_Server::CREATABLE,
				'callback' => array( $this, 'update_item' ),
				'args'     => array(
					'quantity' => array(
						'default'           => 1,
						'type'              => 'integer',
						'validate_callback' => function( $param, $request, $key ) {
							return is_numeric( $param );
						}
					),
				),
			),
			array(
				'methods'  => WP_REST_Server::DELETABLE,
				'callback' => array( $this, 'remove_item' ),
			),
		) );
	} // register_routes()

	/**
	 * Remove Item in Cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  WP_Error|WP_REST_Response
	 */
	public function remove_item( $data = array() ) {
		$cart_item_key  = ! isset( $data['cart_item_key'] ) ? '0' : wc_clean( wp_unslash( $data['cart_item_key'] ) );

		// Checks to see if the cart is empty before attempting to remove item.
		if ( WC()->cart->is_empty() ) {
			$message = __( 'No items in cart.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about no items in cart.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_no_items_message', $message );

			return new WP_Error( 'cocart_no_items', $message, array( 'status' => 500 ) );
		}

		if ( $cart_item_key != '0' ) {
			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $cart_item_key, 'remove' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about item not in cart.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_item_not_in_cart_message', $message, 'remove' );

				return new WP_Error( 'cocart_item_not_in_cart', $message, array( 'status' => 404 ) );
			}

			if ( WC()->cart->remove_cart_item( $cart_item_key ) ) {
				do_action( 'cocart_item_removed', $current_data );

				// Was it requested to return the whole cart once item removed?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				return new WP_REST_Response( __( 'Item has been removed from cart.', 'cart-rest-api-for-woocommerce' ), 200 );
			} else {
				$message = __( 'Unable to remove item from cart.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about can not remove item.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_remove_item_message', $message );

				return new WP_Error( 'cocart_can_not_remove_item', $message, array( 'status' => 500 ) );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, 'remove' );

			return new WP_Error( 'cocart_cart_item_key_required', $message, array( 'status' => 500 ) );
		}
	} // END remove_item()

	/**
	 * Restore Item in Cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  WP_Error|WP_REST_Response
	 */
	public function restore_item( $data = array() ) {
		$cart_item_key = ! isset( $data['cart_item_key'] ) ? '0' : wc_clean( $data['cart_item_key'] );

		if ( $cart_item_key != '0' ) {
			if ( WC()->cart->restore_cart_item( $cart_item_key ) ) {
				$current_data = $this->get_cart_item( $cart_item_key, 'restore' ); // Fetches the cart item data once it is restored.

				do_action( 'cocart_item_restored', $current_data );

				// Was it requested to return the whole cart once item restored?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				return new WP_REST_Response( __( 'Item has been restored to the cart.', 'cart-rest-api-for-woocommerce' ), 200 );
			} else {
				$message = __( 'Unable to restore item to the cart.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about can not restore item.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_restore_item_message', $message );

				return new WP_Error( 'cocart_can_not_restore_item', $message, array( 'status' => 500 ) );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, 'restore' );

			return new WP_Error( 'cocart_cart_item_key_required', $message, array( 'status' => 500 ) );
		}
	} // END restore_item()

	/**
	 * Update Item in Cart.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  WP_Error|WP_REST_Response
	 */
	public function update_item( $data = array() ) {
		$cart_item_key = ! isset( $data['cart_item_key'] ) ? '0' : wc_clean( $data['cart_item_key'] );
		$quantity      = ! isset( $data['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $data['quantity'] ) );

		// Allows removing of items if quantity is zero should for example the item was with a product bundle.
		if ( $quantity === 0 ) {
			return $this->remove_item( $data );
		}

		$this->validate_quantity( $quantity );

		if ( $cart_item_key != '0' ) {
			// Check item exists in cart before fetching the cart item data to update.
			$current_data = $this->get_cart_item( $cart_item_key, 'container' );

			// If item does not exist in cart return response.
			if ( empty( $current_data ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about cart item key required.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_item_not_in_cart_message', $message, 'update' );

				return new WP_Error( 'cocart_item_not_in_cart', $message, array( 'status' => 404 ) );
			}

			$this->has_enough_stock( $current_data, $quantity ); // Checks if the item has enough stock before updating.

			if ( WC()->cart->set_quantity( $cart_item_key, $quantity, $data['refresh_totals'] ) ) {
				$new_data = $this->get_cart_item( $cart_item_key, 'update' );

				$product_id   = ! isset( $new_data['product_id'] ) ? 0 : absint( wp_unslash( $new_data['product_id'] ) );
				$variation_id = ! isset( $new_data['variation_id'] ) ? 0 : absint( wp_unslash( $new_data['variation_id'] ) );

				$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

				if ( $quantity != $new_data['quantity'] ) {
					do_action( 'cocart_item_quantity_changed', $cart_item_key, $new_data );
				}

				// Was it requested to return the whole cart once item updated?
				if ( $data['return_cart'] ) {
					$cart_contents = $this->get_cart_contents( $data );

					return new WP_REST_Response( $cart_contents, 200 );
				}

				$response = array();

				// Return response based on product quantity increment.
				if ( $quantity > $current_data['quantity'] ) {
					/* translators: 1: product name, 2: new quantity */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%1$s" has increased to "%2$s".', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $new_data['quantity'] ),
						'quantity' => $new_data['quantity']
					);
				} else if ( $quantity < $current_data['quantity'] ) {
					/* translators: 1: product name, 2: new quantity */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%1$s" has decreased to "%2$s".', 'cart-rest-api-for-woocommerce' ), $product_data->get_name(), $new_data['quantity'] ),
						'quantity' => $new_data['quantity']
					);
				} else {
					/* translators: %s: product name */
					$response = array(
						'message'  => sprintf( __( 'The quantity for "%s" has not changed.', 'cart-rest-api-for-woocommerce' ), $product_data->get_name() ),
						'quantity' => $quantity
					);
				}

				return new WP_REST_Response( apply_filters( 'cocart_update_item', $response, $new_data, $quantity, $product_data ), 200 );
			} else {
				$message = __( 'Unable to update item quantity in cart.', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'error' );

				/**
				 * Filters message about can not update item.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_can_not_update_item_message', $message );

				return new WP_Error( 'cocart_can_not_update_item', $message, array( 'status' => 500 ) );
			}
		} else {
			$message = __( 'Cart item key is required!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about cart item key required.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_cart_item_key_required_message', $message, 'update' );

			return new WP_Error( 'cocart_cart_item_key_required', $message, array( 'status' => 500 ) );
		}
	} // END update_item()

} // END class
