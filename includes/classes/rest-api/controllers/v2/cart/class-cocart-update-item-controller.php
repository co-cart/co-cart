<?php
/**
 * REST API: CoCart_REST_Update_Item_V2_Controller class.
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
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/item';

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->get_path_regex();
	}

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/item/(?P<item_key>[\w]+)';
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
				'callback'            => array( $this, 'update_item' ),
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

		// Update Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Update Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 5.0.0 Added support to change custom item data or select a different variation of a variable product.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function update_item( $request ) {
		try {
			$cart = $this->get_cart_instance();

			$params = $request->get_params();

			$request = array_merge(
				array(
					'item_key'     => '0',
					'id'           => '0',
					'quantity'     => null,
					'variation_id' => 0,
					'variation'    => array(),
					'item_data'    => array(),
				),
				$params
			);

			if ( ! empty( $request['item_key'] ) ) {
				$request['item_key'] = wc_clean( sanitize_text_field( wp_unslash( $request['item_key'] ) ) );
			}

			// If we don't have the item key we cannot update the item.
			$item_key = CoCart_Utilities_Cart_Helpers::throw_missing_item_key( $request['item_key'], 'update' );

			// Check item exists in cart before fetching the cart item data to update.
			$cart_item = $this->get_cart_item( $item_key, 'container' );

			// Get custom item data from the current product if any.
			$cart_item_data = CoCart_Utilities_Cart_Helpers::prepare_item( $cart_item );

			// If item does not exist in cart return response.
			if ( empty( $cart_item ) ) {
				$message = __( 'Item specified does not exist in cart.', 'cocart-core' );

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

			// If product data is somehow not there on a rare occasion then we need to get that product data to validate it.
			$product = ! is_null( $cart_item['data'] ) ? $cart_item['data'] : wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );

			// Product type.
			$request['product_type'] = $product->get_type();

			// Get the parent ID if the product is a variation.
			$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : 0;

			// Are we changing the quantity of said item?
			if ( ! is_null( $request['quantity'] ) ) {
				$request['quantity'] = (int) CoCart_Utilities_Cart_Helpers::validate_quantity( wc_stock_amount( wp_unslash( $request['quantity'] ) ), $product );

				// If validation returned an error return error response.
				if ( is_wp_error( $request['quantity'] ) ) {
					return $request['quantity'];
				}

				if ( $request['quantity'] > 0 ) {
					$has_stock = CoCart_Utilities_Cart_Helpers::has_enough_stock( $cart_item, $request['quantity'] ); // Checks if the item has enough stock before updating.

					// If not true, return error response.
					if ( is_wp_error( $has_stock ) ) {
						return $has_stock;
					}

					// Return error if product is still set to an individual item.
					if ( $product->is_sold_individually() && $request['quantity'] > 1 ) {
						$message = sprintf(
							/* translators: %s Product name. */
							__( 'You can only have 1 "%s" in your cart.', 'cocart-core' ),
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
				}
			} else {
				$request['quantity'] = $cart_item['quantity'];
			}

			// Removes item if quantity is zero.
			if ( 0 === (int) $request['quantity'] ) {
				$controller = new CoCart_REST_Remove_Item_V2_Controller();

				return $controller->remove_item( $request );
			}

			// If we are not simply removing an item then continue.

			// Are we attempting to replace a variation of a product?
			if ( 0 !== $request['id'] && $product->is_type( 'variation' ) ) {
				$request['id'] = wc_clean( wp_unslash( $request['id'] ) );

				// Validate product ID before continuing and return correct product ID if SKU was used.
				$request['id'] = CoCart_Utilities_Cart_Helpers::validate_product_id( $request['id'] );

				// Return error response if product ID is not found.
				if ( is_wp_error( $request['id'] ) ) {
					return $request['id'];
				}

				// The product we are attempting to replace in the cart.
				$new_product = CoCart_Utilities_Cart_Helpers::validate_product_for_cart( $request );

				// Product type.
				$request['product_type'] = $new_product->get_type();

				// Filter requested data and variation data if any.
				$request = $this->filter_request_data( $this->parse_variation_data( $request, $new_product ) );

				if ( is_wp_error( $request ) ) {
					return $request;
				}

				// If the new variation is not connected to the parent product that was initially added to the cart, return an error.
				if ( $parent_id !== $new_product->get_parent_id() ) {
					throw new CoCart_Data_Exception( 'cocart_can_not_change_variation', __( 'The variation you are attempting to change is not connected to the original product.', 'cocart-core' ), 400 );
				}

				if ( $new_product->is_type( 'variation' ) ) {
					$product = $new_product;
				}
			}

			// Are we replacing the custom item data of the product?
			if ( empty( $request['item_data'] ) ) {
				$request['item_data'] = CoCart_Utilities_Cart_Helpers::set_cart_item_data( $request );
			}

			$quantity_changed = false;
			$product_changed  = false;

			/**
			 * Filter allows you to determine if the cart item passed validation.
			 *
			 * @since 5.0.0 Introduced.
			 *
			 * @param bool       $cart_valid True by default.
			 * @param string     $item_key   Item key.
			 * @param array      $cart_item  Product data of the current item in cart.
			 * @param array      $request    The requested data.
			 * @param WC_Product $product    The product object.
			 */
			$passed_validation = apply_filters( 'cocart_update_cart_item_validation', true, $item_key, $cart_item, $request, $product );

			// If validation returned an error return error response.
			if ( is_wp_error( $passed_validation ) ) {
				return $passed_validation;
			}

			// Only update cart item if passed validation.
			if ( $passed_validation ) {
				// Request to change variation or change custom data.
				if (
					$request['variation_id'] !== $cart_item['variation_id'] ||
					$request['item_data'] !== $cart_item_data
				) {
					// Add new item.
					$controller = new CoCart_REST_Add_Item_V2_Controller();

					$request['no_notice']      = true; // Don't add "Add to Cart" success notice.
					$request['dont_calculate'] = true; // Stops calculating totals until later on.
					$replaced_product          = $controller->add_to_cart( $request );
					$product_changed           = true;

					// Remove the old item if the new variation added successfully.
					if ( ! is_wp_error( $replaced_product ) ) {
						$controller = new CoCart_REST_Remove_Item_V2_Controller();

						$controller->remove_item( $request ); // Make the request but don't return anything here.
					}
				}

				// If we are only updating the item quantity then check if the product changed.
				if ( ! $product_changed ) {
					if ( $request['quantity'] !== $cart_item['quantity'] && $cart->set_quantity( $item_key, $request['quantity'] ) ) {
						$quantity_changed = true;

						// Ensure we have the updated cart item data for the response.
						$updated_cart_item = $this->get_cart_item( $item_key, 'update' );

						/**
						 * Hook: cocart_item_quantity_changed
						 *
						 * @since 2.0.0 Introduced.
						 *
						 * @param string $item_key          Item key.
						 * @param array  $updated_cart_item Item data.
						 */
						do_action( 'cocart_item_quantity_changed', $item_key, $updated_cart_item );

						/**
						 * Calculates the cart totals if an item has changed its quantity.
						 *
						 * @since 2.1.0 Introduced.
						 * @since 3.1.0 Changed to calculate all totals.
						 */
						$this->calculate_totals();
					} else {
						$message = __( 'Unable to update item quantity in cart.', 'cocart-core' );

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

				/**
				 * Hook: cocart_item_updated
				 *
				 * @since 5.0.0 Introduced.
				 *
				 * @param WP_REST_Request $request The request object.
				 */
				do_action( 'cocart_item_updated', $request );

				$request['dont_calculate'] = false; // Reset to allow totals to be calculated.
				// $request['dont_check'] = true;
				$response = $this->get_cart( $request );

				// Add notice if product has changed?
				if ( $product_changed ) {
					wc_add_notice( sprintf(
						/* translators: %s: product name */
						__( '"%s" has been updated.', 'cocart-core' ),
						$product->get_name()
					), 'success' );
				}

				// Add notice if quantity was changed?
				if ( $quantity_changed ) {
					// Return response based on product quantity increment.
					if ( $request['quantity'] > $cart_item['quantity'] ) {
						$status_message = sprintf(
							/* translators: 1: product name, 2: new quantity */
							__( 'The quantity for "%1$s" has increased to "%2$s".', 'cocart-core' ),
							$product->get_name(),
							$updated_cart_item['quantity']
						);
					} elseif ( $request['quantity'] < $cart_item['quantity'] ) {
						$status_message = sprintf(
							/* translators: 1: product name, 2: new quantity */
							__( 'The quantity for "%1$s" has decreased to "%2$s".', 'cocart-core' ),
							$product->get_name(),
							$updated_cart_item['quantity']
						);
					} else {
						$status_message = sprintf(
							/* translators: %s: product name */
							__( 'The quantity for "%s" has not changed.', 'cocart-core' ),
							$product->get_name()
						);
					}

					/**
					 * Filters the quantity update status.
					 *
					 * @since 2.0.1 Introduced.
					 *
					 * @param array      $status_message    Status response.
					 * @param array      $updated_cart_item Cart item.
					 * @param int        $quantity          Quantity.
					 * @param WC_Product $product           The product object.
					 */
					$status_message = apply_filters( 'cocart_update_item', $status_message, $updated_cart_item, $request['quantity'], $product );

					wc_add_notice( $status_message, 'success' );
				}

				$response = rest_ensure_response( $response );
				$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

				return $response;
			}
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END update_item()

	/**
	 * Get the query params for updating an item.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Updated quantity parameter to validate any number values.
	 * @since 5.0.0 Added support to change custom item data or select a different variation of a variable product. Removed return status parameter.
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Update item query parameters.
		$params += array(
			'item_key'  => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'quantity'  => array(
				'description'       => __( 'Quantity of the item to update to.', 'cocart-core' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => 'rest_validate_quantity_arg',
			),
			'id'        => array(
				'description'       => __( 'Variation ID of the item to update to.', 'cocart-core' ),
				'type'              => 'integer',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'variation' => array(
				'description'       => __( 'Variable attributes that identify the variation of the item.', 'cocart-core' ),
				'type'              => 'object',
				'items'             => array(
					'type'       => 'object',
					'properties' => array(
						'attribute' => array(
							'description'       => __( 'Variation attribute name.', 'cocart-core' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
						'value'     => array(
							'description'       => __( 'Variation attribute value.', 'cocart-core' ),
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_text_field',
							'validate_callback' => 'rest_validate_request_arg',
						),
					),
				),
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'item_data' => array(
				'description'       => __( 'Add or replace custom item data to make item unique.', 'cocart-core' ),
				'type'              => 'object',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
