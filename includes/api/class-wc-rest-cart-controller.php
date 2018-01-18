<?php
/**
 * REST API Cart controller
 *
 * Handles requests to the /cart endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  WooCommerce Cart REST API/API
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Cart controller class.
 *
 * @package WooCommerce Cart REST API/API
 */
class WC_REST_Cart_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'wc/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart';

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 */
	public function register_routes() {
		// View Cart - wc/v2/cart
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_cart' ),
		));

		// View Cart - wc/v2/cart/clear
		register_rest_route( $this->namespace, '/' . $this->rest_base  . '/clear', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'clear_cart' ),
		));

		// Add Item - wc/v2/cart/add
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/add', array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'add_to_cart' ),
			'args'     => array_merge(
				$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				array(
					'product_id' => array(
						'validate_callback' => 'is_numeric'
					),
					'quantity' => array(
						'validate_callback' => 'is_numeric'
					),
					'variation_id' => array(
						'validate_callback' => 'is_numeric'
					),
					'variation' => array(
						'validate_callback' => 'is_array'
					),
					'cart_item_data' => array(
						'validate_callback' => 'is_array'
					),
				),
			),
		) );

		// Update Item - wc/v2/cart/update/1
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/update/(?P<cart_item_key>[0-9a-z\-_]+)', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'update_item' ),
			'args'     => array(
				'cart_item_key' => array(
					'default' => null
				),
				'quantity' => array(
					'default' => null,
					'validate_callback' => 'is_numeric'
				),
			),
		) );

		// Remove Item - wc/v2/cart/remove/1
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/remove/(?P<cart_item_key>[0-9a-z\-_]+)', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'remove_item' ),
			'args'     => array(
				'cart_item_key' => array(
					'default' => null
				),
			),
		) );

		// Restore Item - wc/v2/cart/restore/1
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/restore/(?P<cart_item_key>[0-9a-z\-_]+)', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'restore_item' ),
			'args'     => array(
				'cart_item_key' => array(
					'default' => null
				),
			),
		) );

		// Calculate Cart Totals - wc/v2/cart/calculate-totals
		register_rest_route( $this->namespace, '/' . $this->rest_base  . '/calculate-total', array(
			'methods'  => WP_REST_Server::EDITABLE,
			'callback' => array( $this, 'calculate_totals' ),
		));
	}

	/**
	 * Get cart.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @return array
	 */
	protected function get_cart() {
		return WC()->cart->get_cart();
	} // END get_cart()

	/**
	 * Clear cart.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @return array
	 */
	protected function clear_cart() {
		if ( WC()->cart->empty_cart() == null ) {
			return new WP_REST_Response( __( 'Success: Cart is now cleared!', 'woocommerce-cart-rest-api' ), 200 );
		} else {
			return new WP_Error( 'clear_cart_failed', __( 'Error: Clearing the cart failed.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}
	} // END clear_cart()

	/**
	 * Validate the product id argument.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $product_id
	 * @return WP_Error
	 */
	protected function validate_product_id( $product_id ) {
		if ( $product_id <= 0 ) {
			return new WP_Error( 'product_id_required', __( 'Error: Product ID number is required.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}

		if ( ! is_numeric( $product_id ) ) {
			return new WP_Error( 'product_id_not_numeric', __( 'Error: Product ID must be numeric.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}
	} // END validate_product_id()

	/**
	 * Validate the product quantity argument.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $quantity
	 * @return WP_Error
	 */
	protected function validate_quantity( $quantity ) {
		if ( ! is_numeric( $quantity ) ) {
			return new WP_Error( 'quantity_not_numeric', __( 'Error: Quantity must be numeric.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}
	} // END validate_quantity()

	/**
	 * Validate product before it is added to the cart, updated or removed.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  int $product_id
	 * @param  int $quantity
	 * @return WP_Error
	 */
	protected function validate_product( $product_id, $quantity ) {
		$this->validate_product_id( $product_id );

		$this->validate_quantity( $quantity );
	} // END validate_product()

	/**
	 * Add to Cart.
	 *
	 * @access protected
	 * @since  1.0.0
	 * @param  array $data
	 * @return WP_Error|WP_REST_Response
	 */
	protected function add_to_cart( $data = array() ) {
		$product_id     = ! isset( $data['product_id'] ) ? 0 : absint( $data['product_id'] );
		$quantity       = ! isset( $data['quantity'] ) ? 1 : absint( $data['quantity'] );
		$variation_id   = ! isset( $data['variation_id'] ) ? 0 : absint( $data['variation_id'] );
		$variation      = ! isset( $data['variation'] ) ? array() : $data['variation'];
		$cart_item_data = ! isset( $data['cart_item_data'] ) ? array() : $data['cart_item_data'];

		$this->validate_product( $product_id, $quantity );

		$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( $quantity <= 0 || ! $product_data || 'trash' === $product_data->get_status() ) {
			return new WP_Error( 'product_does_not_exist', __( 'Error: This product does not exist.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}

		// Force quantity to 1 if sold individually and check for existing item in cart.
		if ( $product_data->is_sold_individually() ) {
			$quantity = 1;

			$cart_contents = WC()->cart->cart_contents;

			$found_in_cart = apply_filters( 'woocommerce_add_to_cart_sold_individually_found_in_cart', $cart_item_key && $cart_contents[ $cart_item_key ]['quantity'] > 0, $product_id, $variation_id, $cart_item_data, $cart_id );

			if ( $found_in_cart ) {
				/* translators: %s: product name */
				return new WP_Error( 'product_sold_individually', sprintf( __( 'Error: You cannot add another "%s" to your cart.', 'woocommerce-cart-rest-api' ), $product_data->get_name() ), array( 'status' => 500 ) );
			}
		}

		// Product is purchasable check.
		if ( ! $product_data->is_purchasable() ) {
			throw new WP_Error( 'cannot_be_purchased', __( 'Sorry, this product cannot be purchased.', 'woocommerce-cart-rest-api' ), array( 'status' => 500 ) );
		}

		// Stock check - only check if we're managing stock and backorders are not allowed.
		if ( ! $product_data->is_in_stock() ) {
			throw new WP_Error( 'product_out_of_stock', sprintf( __( 'You cannot add &quot;%s&quot; to the cart because the product is out of stock.', 'woocommerce-cart-rest-api' ), $product_data->get_name() ), array( 'status' => 500 ) );
		}
		if ( ! $product_data->has_enough_stock( $quantity ) ) {
			/* translators: 1: product name 2: quantity in stock */
			throw new WP_Error( 'not_enough_in_stock', sprintf( __( 'You cannot add that amount of &quot;%1$s&quot; to the cart because there is not enough stock (%2$s remaining).', 'woocommerce-cart-rest-api' ), $product_data->get_name(), wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ) ), array( 'status' => 500 ) );
		}

		// Stock check - this time accounting for whats already in-cart.
		if ( $product_data->managing_stock() ) {
			$products_qty_in_cart = WC()->cart->get_cart_item_quantities();

			if ( isset( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] ) && ! $product_data->has_enough_stock( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ] + $quantity ) ) {
				throw new WP_Error(
					'not_enough_stock_remaining',
					sprintf(
						__( 'You cannot add that amount to the cart &mdash; we have %1$s in stock and you already have %2$s in your cart.', 'woocommerce-cart-rest-api' ),
						wc_format_stock_quantity_for_display( $product_data->get_stock_quantity(), $product_data ),
						wc_format_stock_quantity_for_display( $products_qty_in_cart[ $product_data->get_stock_managed_by_id() ], $product_data )
					),
					array( 'status' => 500 )
				);
			}
		}

		// Add item to cart.
		$item_key = WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation, $cart_item_data );

		// Return response to added item to cart or return error.
		if ( $item_key ) {
			$data = WC()->cart->get_cart_item( $item_key );

			if ( is_array( $data ) ) {
				return new WP_REST_Response( $data, 200 );
			}
		} else {
			return new WP_Error( 'cannot_add_to_cart', sprintf( __( 'Error: You cannot add "%s" to your cart.', 'woocommerce-cart-rest-api' ), $product_data->get_name() ), array( 'status' => 500 ) );
		}
	} // END add_to_cart()

	/**
	 * Remove Item in Cart.
	 *
	 * @TODO
	 * @access protected
	 * @since  1.0.0
	 * @return WP_Error|WP_REST_Response
	 */
	protected function remove_item() {
		// Add function code here.
	} // END remove_item()

	/**
	 * Restore Item in Cart.
	 *
	 * @TODO
	 * @access protected
	 * @since  1.0.0
	 * @return WP_Error|WP_REST_Response
	 */
	protected function restore_item() {
		// Add function code here.
	} // END restore_item()

	/**
	 * Update Item in Cart.
	 *
	 * @TODO
	 * @access protected
	 * @since  1.0.0
	 * @return WP_Error|WP_REST_Response
	 */
	protected function update_item() {
		// Add function code here.
	} // END update_item()

	/**
	 * Calculate Cart Totals.
	 *
	 * @TODO
	 * @access protected
	 * @since  1.0.0
	 * @return WP_Error|WP_REST_Response
	 */
	protected function calculate_totals() {
		// Add function code here.
	} // END calculate_totals()

} // END class
