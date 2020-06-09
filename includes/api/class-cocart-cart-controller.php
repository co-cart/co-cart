<?php
/**
 * CoCart REST API controller
 *
 * Handles requests to the cart endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 controller class.
 *
 * @package CoCart REST API/API
 */
class CoCart_Cart_V2_Controller extends CoCart_API_Controller {

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
	protected $rest_base = 'cart';

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart - cocart/v2/cart (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_cart' ),
				'args'     => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );

		// Get Cart in Session - cocart/v1/cart/1654654321 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w]+)', array(
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $this, 'get_cart_in_session' ),
				'args'     => $this->get_collection_params()
			),
			'schema' => array( $this, 'get_item_schema' )
		) );
	} // register_routes()

	/**
	 * Gets the cart instance so we only call it once in the API.
	 *
	 * @access public
	 * @since  3.0.0
	 * @return WC_Cart
	 */
	public function get_cart_instance() {
		return WC()->cart;
	} // END get_cart_instance()

	/**
	 * Return cart contents.
	 *
	 * @access  public
	 * @since   2.0.0
	 * @version 3.0.0
	 * @param   array  $request
	 * @param   array  $cart_contents
	 * @param   string $cart_item_key
	 * @param   bool   $from_session
	 * @return  array  $cart_contents
	 */
	public function return_cart_contents( $request = array(), $cart_contents = array(), $cart_item_key = '', $from_session = false ) {
		if ( CoCart_Count_Items_Controller::get_cart_contents_count( array( 'return' => 'numeric' ), $cart_contents ) <= 0 || empty( $cart_contents ) ) {
			/**
			 * Filter response for empty cart.
			 *
			 * @since 2.0.8
			 */
			$empty_cart = apply_filters( 'cocart_return_empty_cart', array() );

			return $empty_cart;
		}

		// Find the cart item key in the existing cart.
		if ( ! empty( $cart_item_key ) ) {
			$cart_item_key = $this->find_product_in_cart( $cart_item_key );

			return $cart_contents[ $cart_item_key ];
		}

		/**
		 * Return the default cart data if set to true.
		 *
		 * @since 3.0.0
		 */
		if ( $request['default'] ) {
			return $cart_contents;
		}

		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
				$cart_contents[ $item_key ]['data'] = $cart_item['data'];
			}

			$_product = apply_filters( 'cocart_item_product', $cart_item['data'], $cart_item, $item_key );

			// If product is no longer purchasable then don't return it and notify customer.
			if ( ! $_product->is_purchasable() ) {
				/* translators: %s: product name */
				$message = sprintf( __( '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.', 'cart-rest-api-for-woocommerce' ), $_product->get_name() );

				/**
				 * Filter message about item removed from the cart.
				 *
				 * @since 2.1.0
				 * @param string     $message Message.
				 * @param WC_Product $_product Product data.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $_product );

				$this->get_cart_instance->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.

				wc_add_notice( $message, 'error' );
			} else {
				// Adds the product name and title as new variables.
				$cart_contents[ $item_key ]['product_name']  = apply_filters( 'cocart_product_name', $_product->get_name(), $_product, $cart_item, $item_key );
				$cart_contents[ $item_key ]['product_title'] = apply_filters( 'cocart_product_title', $_product->get_title(), $_product, $cart_item, $item_key );

				// Add product price as a new variable.
				$cart_contents[ $item_key ]['product_price'] = html_entity_decode( strip_tags( wc_price( $_product->get_price() ) ) );

				// If main product thumbnail is requested then add it to each item in cart.
				if ( $show_thumb ) {
					$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $_product->get_image_id(), $cart_item, $item_key );

					$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail' ) );

					/**
					 * Filters the source of the product thumbnail.
					 *
					 * @since 2.1.0
					 * @param string $thumbnail_src URL of the product thumbnail.
					 */
					$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src[0], $cart_item, $item_key );

					// Add main product image as a new variable.
					$cart_contents[ $item_key ]['product_image'] = esc_url( $thumbnail_src );
				}

				// This filter allows additional data to be returned for a specific item in cart.
				$cart_contents = apply_filters( 'cocart_cart_contents', $cart_contents, $item_key, $cart_item, $_product );
			}
		}

		/**
		 * Return cart content from session if set.
		 *
		 * @since 2.1.0
		 * @param $cart_contents
		 */
		if ( $from_session ) {
			return apply_filters( 'cocart_return_cart_session_contents', $cart_contents );
		} else {
			return apply_filters( 'cocart_return_cart_contents', $cart_contents );
		}
	} // END return_cart_contents()

	/**
	 * Get the schema for returning the cart, conforming to JSON Schema.
	 *
	 * @access public
	 * @since  2.1.2
	 * @return array
	 */
	public function get_item_schema() {
		$schema         = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'items'   => array(
					'description' => __( 'List of cart items.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'properties'  => array(
						'key'             => array(
							'description' => __( 'Unique identifier for the item within the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'product_id'      => array(
							'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'variation_id' => array(
							'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'variation'       => array(
							'description' => __( 'Chosen attributes (for variations).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => array(
									'attribute' => array(
										'description' => __( 'Variation attribute slug.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'value'     => array(
										'description' => __( 'Variation attribute value.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
						),
						'quantity'        => array(
							'description' => __( 'Quantity of this item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'float',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_tax_data'   => array(
							'description' => '',
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type'    => 'object',
								'properties' => array(
									'subtotal' => array(
										'description' => __( 'Line subtotal tax data.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'integer',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total' => array(
										'description' => __( 'Line total tax data.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'integer',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								)
							)
						),
						'line_subtotal' => array(
							'description' => __( 'Line subtotal (the price of the product before coupon discounts have been applied).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_subtotal_tax' => array(
							'description' => __( 'Line subtotal tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_total' => array(
							'description' => __( 'Line total (the price of the product after coupon discounts have been applied).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'line_tax' => array(
							'description' => __( 'Line total tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'product_name'    => array(
							'description' => __( 'Product name.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => ( 'view' ),
							'readonly'    => true,
						),
						'product_price'   => array(
							'description' => __( 'Current product price.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
					'readonly'          => true,
				),
			)
		);

		$schema['properties'] = apply_filters( 'cocart_cart_schema', $schema['properties'] );

		return $schema;
	} // END get_item_schema()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 3.0.0
	 * @return  array $params
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart/customer.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
			),
			'thumb'    => array(
				'description'       => __( 'Returns the thumbnail of the featured product image URL for each item in cart.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
			),
			'default'  => array(
				'description'       => __( 'Return the default cart data if set to true.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
			),
			'thumb' => array(
				'description' => __( 'Returns the URL of the product image thumbnail.', 'cart-rest-api-for-woocommerce' ),
				'default'     => false,
				'type'        => 'boolean',
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
