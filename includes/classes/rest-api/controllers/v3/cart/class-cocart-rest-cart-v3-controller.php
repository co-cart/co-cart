<?php
/**
 * REST API: CoCart_REST_Cart_V3_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v3
 * @since   x.x.x Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main cart controller that gets the requested cart in session
 * containing customers information, items added,
 * shipping options (if any), totals and more. (API v3)
 *
 * This REST API controller handles the request to get the cart
 * via "cocart/v3/cart" endpoint.
 *
 * @since x.x.x Introduced.
 * @extends CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Cart_V3_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v3';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart';

	/**
	 * Schema.
	 *
	 * @var array
	 */
	protected $schema = array();

	/**
	 * Register the routes for cart.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Get Cart - cocart/v3/cart (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access public
	 *
	 * @since x.x.x Introduced.
	 *
	 * @see cocart_format_attribute_data()
	 * @see cocart_format_variation_data()
	 * @see cocart_format_money()
	 * @see CoCart_Utilities_Product_Helpers::get_tax_display_mode()
	 * @see CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode()
	 * @see CoCart_Utilities_Product_Helpers::get_product_slug()
	 * @see CoCart_Utilities_Cart_Helpers::prepare_item()
	 *
	 * @param WC_Product $product      The product object.
	 * @param array      $cart_item    The cart item data.
	 * @param string     $item_key     The item key generated based on the details of the item.
	 * @param boolean    $show_thumb   Determines if requested to return the item featured thumbnail.
	 * @param boolean    $removed_item Determines if the item in the cart is removed.
	 *
	 * @return array $item Returns the item prepared for the cart response.
	 */
	public function get_item( $product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$tax_display_mode = CoCart_Utilities_Product_Helpers::get_tax_display_mode();
		$price_function   = CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode( $tax_display_mode );
		$regular_price    = $product->get_regular_price();
		$sale_price       = $product->get_sale_price();
		$discounted_price = 0;

		/**
		 * Filter allows the quantity of the item to change.
		 *
		 * Warning: This filter does not represent the quantity of the item that totals will be calculated on.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param string     $item_quantity Item quantity.
		 * @param string     $item_key      The item key generated based on the details of the item.
		 * @param array      $cart_item     The cart item data.
		 * @param WC_Product $product       The product object.
		 */
		$quantity = apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item, $product );

		if ( $product->is_on_sale() ) {
			$discounted_price = ( $regular_price - $sale_price );
		}

		$item = array(
			'item_key'       => $item_key,
			'id'             => $product->get_id(),
			'parent_id'      => $product->get_parent_id(),
			'type'           => $product->get_type(),
			/**
			 * Filter allows the product name of the item to change.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string     $product_name Product name.
			 * @param WC_Product $product      The product object.
			 * @param array      $cart_item    The cart item data.
			 * @param string     $item_key     The item key generated based on the details of the item.
			 */
			'name'           => apply_filters( 'cocart_cart_item_name', $product->get_name(), $product, $cart_item, $item_key ),
			/**
			 * Filter allows the product title of the item to change.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string     $product_title Product title.
			 * @param WC_Product $product       The product object.
			 * @param array      $cart_item     The cart item data.
			 * @param string     $item_key      The item key generated based on the details of the item.
			 */
			// 'title'          => apply_filters( 'cocart_cart_item_title', $product->get_title(), $product, $cart_item, $item_key ),
			'sku'            => $product->get_sku(),
			'attributes'     => cocart_format_attribute_data( $product ),
			'variation'      => cocart_format_variation_data( $cart_item['variation'], $product ),
			'prices'         => array(
				/**
				 * Filter allows the price of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string     $product_price Product price.
				 * @param array      $cart_item     The cart item data.
				 * @param string     $item_key      The item key generated based on the details of the item.
				 * @param WC_Product $product      The product object.
				 */
				'price'      => apply_filters( 'cocart_cart_item_price', cocart_format_money( $price_function( $product ) ), $cart_item, $item_key, $product ),
				'regular'    => cocart_format_money( $regular_price ),
				'sale'       => cocart_format_money( $sale_price ),
				'discounted' => (string) cocart_format_money( $discounted_price ),
			),
			'quantity'       => array(
				'value'        => $quantity,
				'min_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_minimum_requirement( $product ),
				'max_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_maximum_allowed( $product ),
			),
			'totals'         => array(
				/**
				 * Filter allows the subtotal of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_subtotal Item subtotal.
				 * @param array  $cart_item     The cart item data.
				 * @param string $item_key      The item key generated based on the details of the item.
				 */
				'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', cocart_format_money( $cart_item['line_subtotal'] ), $cart_item, $item_key ),
				/**
				 * Filter allows the subtotal tax of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_subtotal_tax Item subtotal tax.
				 * @param array  $cart_item         The cart item data.
				 * @param string $item_key          The item key generated based on the details of the item.
				 */
				'subtotal_tax' => apply_filters( 'cocart_cart_item_subtotal_tax', $cart_item['line_subtotal_tax'], $cart_item, $item_key ),
				/**
				 * Filter allows the total of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_total Item total.
				 * @param array  $cart_item  The cart item data.
				 * @param string $item_key   The item key generated based on the details of the item.
				 */
				'total'        => apply_filters( 'cocart_cart_item_total', $cart_item['line_total'], $cart_item, $item_key ),
				/**
				 * Filter allows the tax of the item to change.
				 *
				 * Warning: This filter does not represent the true value that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_tax  Item tax.
				 * @param array  $cart_item The cart item data.
				 * @param string $item_key  The item key generated based on the details of the item.
				 */
				'tax'          => apply_filters( 'cocart_cart_item_tax', $cart_item['line_tax'], $cart_item, $item_key ),
			),
			// 'slug'           => CoCart_Utilities_Product_Helpers::get_product_slug( $product ),
			'backorders'     => '',
			'permalink'      => $product->is_visible() ? $product->get_permalink( $cart_item ) : '',
			'featured_image' => '',
			'cart_item_data' => array(),
		);

		// Backorder notification.
		if ( $product->backorders_require_notification() && $product->is_on_backorder( $cart_item['quantity'] ) ) {
			$item['backorders'] = wp_kses_post( apply_filters( 'cocart_cart_item_backorder_notification', esc_html__( 'Available on backorder', 'cart-rest-api-for-woocommerce' ), $product->get_id() ) );
		}

		// If thumbnail is requested then add it to each item in cart.
		if ( $show_thumb ) {
			$thumbnail_id = ! empty( $product->get_image_id() ) ? $product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

			/**
			 * Filters the item thumbnail ID.
			 *
			 * @since 2.0.0 Introduced.
			 * @since 3.0.0 Added $removed_item parameter.
			 *
			 * @param int    $thumbnail_id Product thumbnail ID.
			 * @param array  $cart_item    Cart item.
			 * @param string $item_key     Generated ID based on the product information when added to the cart.
			 * @param bool   $removed_item Determines if the item in the cart is removed.
			 */
			$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $thumbnail_id, $cart_item, $item_key, $removed_item );

			/**
			 * Filters the thumbnail size of the product image.
			 *
			 * @since 2.0.0 Introduced.
			 * @since 3.0.0 Added $removed_item parameter.
			 *
			 * @param bool $removed_item Determines if the item in the cart is removed.
			 */
			$thumbnail_size = apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail', $removed_item );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, $thumbnail_size );
			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

			/**
			 * Filters the source of the product thumbnail.
			 *
			 * @since 2.1.0 Introduced.
			 * @since 3.0.0 Added parameter $removed_item.
			 *
			 * @param string $thumbnail_src URL of the product thumbnail.
			 * @param array  $cart_item     Cart item.
			 * @param string $item_key      Generated ID based on the product information when added to the cart.
			 * @param bool   $removed_item  Determines if the item in the cart is removed.
			 */
			$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src, $cart_item, $item_key, $removed_item );

			// Add main featured image.
			$item['featured_image'] = esc_url( $thumbnail_src );
		}

		if ( ! $removed_item ) {
			/**
			 * Filter allows extensions to add additional data to be returned for item in cart.
			 *
			 * Data is only returned for items in the cart, not removed items.
			 *
			 * @since 4.4.0 Introduced.
			 *
			 * @param array      $extensions The extension data added.
			 * @param array      $cart_item  The cart item data.
			 * @param WC_Product $product    The product object.
			 */
			$item['extensions'] = apply_filters( 'cocart_cart_item_extensions', array(), $cart_item, $product );
		}

		// Prepares the remaining cart item data.
		$cart_item = CoCart_Utilities_Cart_Helpers::prepare_item( $cart_item );

		// Collect all cart item data if any thing is left.
		if ( ! empty( $cart_item ) ) {
			/**
			 * Filter allows you to alter the remaining cart item data.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param array  $cart_item The cart item data.
			 * @param string $item_key  Generated ID based on the product information when added to the cart.
			 */
			$item['cart_item_data'] = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key );
		}

		return $item;
	} // END get_item()

	/**
	 * Get cart template.
	 *
	 * Used as a base even if the cart is empty along with
	 * customer information should the user be logged in.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @access protected
	 *
	 * @since 3.0.3 Introduced.
	 *
	 * @see cocart_format_money()
	 * @see CoCart_Utilities_Cart_Helpers::get_cart_key()
	 * @see CoCart_Utilities_Cart_Helpers::get_customer_fields()
	 * @see CoCart_Utilities_Cart_Helpers::get_applied_coupons()
	 * @see CoCart_Utilities_Cart_Helpers::get_fees()
	 * @see CoCart_Utilities_Cart_Helpers::maybe_return_notices()
	 * @see CoCart_REST_Cart_V2_Controller::get_cart_template_limited()
	 * @see CoCart_REST_Cart_V2_Controller::get_shipping_details()
	 * @see CoCart_REST_Cart_V2_Controller::get_removed_items()
	 * @see CoCart_REST_Cart_V2_Controller::get_cross_sells()
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array Returns the default cart response.
	 */
	protected function get_cart_template( $request = array() ) {
		$fields = ! empty( $request['fields'] ) ? $request['fields'] : '';

		if ( ! empty( $fields ) ) {
			return self::get_cart_template_limited( $request );
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		return array(
			'cart_hash'      => ! empty( $this->get_cart_instance()->get_cart_hash() ) ? $this->get_cart_instance()->get_cart_hash() : __( 'No items in cart so no hash', 'cart-rest-api-for-woocommerce' ),
			'cart_key'       => CoCart_Utilities_Cart_Helpers::get_cart_key(),
			'currency'       => cocart_get_store_currency(),
			'customer'       => array(
				'billing_address'  => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() ),
				'shipping_address' => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() ),
			),
			'items'          => array(),
			'item_count'     => $this->get_cart_instance()->get_cart_contents_count(),
			'items_weight'   => (string) wc_get_weight( $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) ),
			'coupons'        => CoCart_Utilities_Cart_Helpers::get_applied_coupons( $this->get_cart_instance() ),
			'needs_payment'  => $this->get_cart_instance()->needs_payment(),
			'needs_shipping' => $this->get_cart_instance()->needs_shipping(),
			'shipping'       => CoCart_Utilities_Cart_Helpers::get_shipping_details( $this->get_cart_instance() ),
			'fees'           => CoCart_Utilities_Cart_Helpers::get_fees( $this->get_cart_instance() ),
			'taxes'          => array(),
			'totals'         => array(
				'subtotal'       => cocart_format_money( $this->get_cart_instance()->get_subtotal() ),
				'subtotal_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_subtotal_tax() ),
				'fee_total'      => cocart_format_money( $this->get_cart_instance()->get_fee_total() ),
				'fee_tax'        => (string) cocart_format_money( $this->get_cart_instance()->get_fee_tax() ),
				'discount_total' => (string) cocart_format_money( $this->get_cart_instance()->get_discount_total() ),
				'discount_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_discount_tax() ),
				'shipping_total' => cocart_format_money( $this->get_cart_instance()->get_shipping_total() ),
				'shipping_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_shipping_tax() ),
				'total'          => cocart_format_money( $this->get_cart_instance()->get_total() ),
				'total_tax'      => (string) cocart_format_money( $this->get_cart_instance()->get_total_tax() ),
			),
			'removed_items'  => $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $show_thumb ),
			'cross_sells'    => $this->get_cross_sells(),
			'notices'        => CoCart_Utilities_Cart_Helpers::maybe_return_notices(),
		);
	} // END get_cart_template()

	/**
	 * Get cart template - Limited.
	 *
	 * Same as original cart template only it returns the fields requested.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array $template Returns requested cart response.
	 */
	protected function get_cart_template_limited( $request = array() ) {
		$fields     = ! empty( $request['fields'] ) ? explode( ',', $request['fields'] ) : '';
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		$template = array();

		foreach ( $fields as $field ) {
			$field        = explode( ':', $field );
			$parent_field = $field[0];
			$child_field  = ! empty( $field[1] ) ? $field[1] : '';

			switch ( $parent_field ) {
				case 'cart_hash':
					$template['cart_hash'] = $this->get_cart_instance()->get_cart_hash();
					break;
				case 'cart_key':
					$template['cart_key'] = CoCart_Utilities_Cart_Helpers::get_cart_key();
					break;
				case 'currency':
					$template['currency'] = cocart_get_store_currency();
					break;
				case 'customer':
					if ( ! empty( $child_field ) ) {
						if ( 'billing_address' === $child_field ) {
							$template['customer']['billing_address'] = CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() );
						}
						if ( 'shipping_address' === $child_field ) {
							$template['customer']['shipping_address'] = CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() );
						}
					} else {
						$template['customer'] = array(
							'billing_address'  => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_cart_instance()->get_customer() ),
							'shipping_address' => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_cart_instance()->get_customer() ),
						);
					}
					break;
				case 'items':
					$template['items'] = array();
					break;
				case 'item_count':
					$template['item_count'] = $this->get_cart_instance()->get_cart_contents_count();
					break;
				case 'items_weight':
					$template['items_weight'] = (string) wc_get_weight( $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
					break;
				case 'coupons':
					$template['coupons'] = CoCart_Utilities_Cart_Helpers::get_applied_coupons( $this->get_cart_instance() );
					break;
				case 'needs_payment':
					$template['needs_payment'] = $this->get_cart_instance()->needs_payment();
					break;
				case 'needs_shipping':
					$template['needs_shipping'] = $this->get_cart_instance()->needs_shipping();
					break;
				case 'shipping':
					$template['shipping'] = $this->get_shipping_details();
					break;
				case 'fees':
					$template['fees'] = CoCart_Utilities_Cart_Helpers::get_fees( $this->get_cart_instance() );
					break;
				case 'taxes':
					$template['taxes'] = array();
					break;
				case 'totals':
					if ( ! empty( $child_field ) ) {
						$child_field = explode( '-', $child_field );

						foreach ( $child_field as $total ) {
							if ( 'subtotal' === $total ) {
								$template['totals']['subtotal'] = cocart_format_money( $this->get_cart_instance()->get_subtotal() );
							}
							if ( 'subtotal_tax' === $total ) {
								$template['totals']['subtotal_tax'] = (string) cocart_format_money( $this->get_cart_instance()->get_subtotal_tax() );
							}
							if ( 'fee_total' === $total ) {
								$template['totals']['fee_total'] = cocart_format_money( $this->get_cart_instance()->get_fee_total() );
							}
							if ( 'fee_tax' === $total ) {
								$template['totals']['fee_tax'] = (string) cocart_format_money( $this->get_cart_instance()->get_fee_tax() );
							}
							if ( 'discount_total' === $total ) {
								$template['totals']['discount_total'] = (string) cocart_format_money( $this->get_cart_instance()->get_discount_total() );
							}
							if ( 'discount_tax' === $total ) {
								$template['totals']['discount_tax'] = (string) cocart_format_money( $this->get_cart_instance()->get_discount_tax() );
							}
							if ( 'shipping_total' === $total ) {
								$template['totals']['shipping_total'] = cocart_format_money( $this->get_cart_instance()->get_shipping_total() );
							}
							if ( 'shipping_tax' === $total ) {
								$template['totals']['shipping_tax'] = (string) cocart_format_money( $this->get_cart_instance()->get_shipping_tax() );
							}
							if ( 'total' === $total ) {
								$template['totals']['total'] = cocart_format_money( $this->get_cart_instance()->get_total() );
							}
							if ( 'total_tax' === $total ) {
								$template['totals']['total_tax'] = (string) cocart_format_money( $this->get_cart_instance()->get_total_tax() );
							}
						}
					} else {
						$template['totals'] = array(
							'subtotal'       => cocart_format_money( $this->get_cart_instance()->get_subtotal() ),
							'subtotal_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_subtotal_tax() ),
							'fee_total'      => cocart_format_money( $this->get_cart_instance()->get_fee_total() ),
							'fee_tax'        => (string) cocart_format_money( $this->get_cart_instance()->get_fee_tax() ),
							'discount_total' => (string) cocart_format_money( $this->get_cart_instance()->get_discount_total() ),
							'discount_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_discount_tax() ),
							'shipping_total' => cocart_format_money( $this->get_cart_instance()->get_shipping_total() ),
							'shipping_tax'   => (string) cocart_format_money( $this->get_cart_instance()->get_shipping_tax() ),
							'total'          => cocart_format_money( $this->get_cart_instance()->get_total() ),
							'total_tax'      => (string) cocart_format_money( $this->get_cart_instance()->get_total_tax() ),
						);
					}
					break;
				case 'removed_items':
					$template['removed_items'] = $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $show_thumb );
					break;
				case 'cross_sells':
					$template['cross_sells'] = $this->get_cross_sells();
					break;
				case 'notices':
					$template['notices'] = CoCart_Utilities_Cart_Helpers::maybe_return_notices();
					break;
			}
		}

		return $template;
	} // END get_cart_template_limited()

	/**
	 * Retrieves the item schema for returning the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_cart',
			'type'       => 'object',
			'properties' => array(
				'cart_hash'      => array(
					'description' => __( 'A Unique hash key of the carts contents.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_key'       => array(
					'description' => __( 'The cart key identifying the cart in session.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'currency'       => array(
					'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'currency_code'               => array(
							'description' => __( 'The currency code (in ISO format) for returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_symbol'             => array(
							'description' => __( 'The currency symbol for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_symbol_pos'         => array(
							'description' => __( 'The currency symbol position to which the currency needs to return for the prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_minor_unit'         => array(
							'description' => __( 'The currency minor unit (number of digits after the decimal separator) for returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_decimal_separator'  => array(
							'description' => __( 'The decimal separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_thousand_separator' => array(
							'description' => __( 'The thousand separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_prefix'             => array(
							'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'currency_suffix'             => array(
							'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'customer'       => array(
					'description' => __( 'The customer information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'billing_address'  => array(
							'description' => __( 'Customers billing address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'billing_first_name' => array(
									'description' => __( 'Customers billing first name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_last_name'  => array(
									'description' => __( 'Customers billing last name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_company'    => array(
									'description' => __( 'Customers billing company name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_country'    => array(
									'description' => __( 'Customers billing country.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_address_1'  => array(
									'description' => __( 'Customers billing address line one.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_address_2'  => array(
									'description' => __( 'Customers billing address line two.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_city'       => array(
									'description' => __( 'Customers billing address city.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_state'      => array(
									'description' => __( 'Customers billing state.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_postcode'   => array(
									'description' => __( 'Customers billing postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_phone'      => array(
									'description' => __( 'Customers billing phone.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'billing_email'      => array(
									'description' => __( 'Customers billing email address.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'shipping_address' => array(
							'description' => __( 'Customers shipping address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'shipping_first_name' => array(
									'description' => __( 'Customers shipping first name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_last_name'  => array(
									'description' => __( 'Customers shipping last name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_company'    => array(
									'description' => __( 'Customers shipping company name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_country'    => array(
									'description' => __( 'Customers shipping country.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_address_1'  => array(
									'description' => __( 'Customers shipping address line one.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_address_2'  => array(
									'description' => __( 'Customers shipping address line two.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_city'       => array(
									'description' => __( 'Customers shipping address city.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_state'      => array(
									'description' => __( 'Customers shipping state.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'shipping_postcode'   => array(
									'description' => __( 'Customers shipping postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'items'          => array(
					'description' => __( 'The list of items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'item_key'       => array(
								'description' => __( 'Unique ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'id'             => array(
								'description' => __( 'Product ID or Variation ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'           => array(
								'description' => __( 'The name of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'title'          => array(
								'description' => __( 'The title of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'price'          => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'quantity'       => array(
								'description' => __( 'The quantity of the item in the cart and minimum and maximum purchase capability.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'value'        => array(
										'description' => __( 'The quantity of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'min_purchase' => array(
										'description' => __( 'The minimum purchase amount required.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'max_purchase' => array(
										'description' => __( 'The maximum purchase amount allowed. If -1 the item has an unlimited purchase amount.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'totals'         => array(
								'description' => __( 'The totals of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'subtotal'     => array(
										'description' => __( 'The subtotal of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'subtotal_tax' => array(
										'description' => __( 'The subtotal tax of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total'        => array(
										'description' => __( 'The total of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total_tax'    => array(
										'description' => __( 'The total tax of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'slug'           => array(
								'description' => __( 'The product slug of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'meta'           => array(
								'description' => __( 'The meta data of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'product_type' => array(
										'description' => __( 'The product type of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'sku'          => array(
										'description' => __( 'The SKU of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'dimensions'   => array(
										'description' => __( 'The dimensions of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(
											'length' => array(
												'description' => __( 'The length of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'width'  => array(
												'description' => __( 'The width of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'height' => array(
												'description' => __( 'The height of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'unit'   => array(
												'description' => __( 'The unit measurement of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
										),
									),
									'weight'       => array(
										'description' => __( 'The weight of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'variation'    => array(
										'description' => __( 'The variation attributes of the item in the cart (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(),
									),
								),
							),
							'backorders'     => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'cart_item_data' => array(
								'description' => __( 'Custom item data applied to the item in the cart (if any).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(),
							),
							'featured_image' => array(
								'description' => __( 'The featured image of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'item_count'     => array(
					'description' => __( 'The number of items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'items_weight'   => array(
					'description' => __( 'The total weight of all items in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'coupons'        => array(
					'description' => __( 'Coupons added to the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'coupon'      => array(
								'description' => __( 'The coupon code.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'label'       => array(
								'description' => __( 'The coupon label.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'saving'      => array(
								'description' => __( 'The amount discounted from the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'saving_html' => array(
								'description' => __( 'The amount discounted from the cart (HTML formatted).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'needs_payment'  => array(
					'description' => __( 'True if the cart needs payment. False for carts with only free products and no shipping costs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'needs_shipping' => array(
					'description' => __( 'True if the cart needs shipping and requires the showing of shipping costs.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'shipping'       => array(
					'description' => __( 'The shipping packages available to the customer.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'total_packages'          => array(
							'description' => __( 'The number of shipping packages available calculated on the shipping address.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'show_package_details'    => array(
							'description' => __( 'True if the cart meets the criteria for showing items in the cart assigned to package.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'has_calculated_shipping' => array(
							'description' => __( 'True if the cart meets the criteria for showing shipping costs, and rates have been calculated and included in the totals.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'packages'                => array(
							'description' => __( 'The packages returned after calculating shipping.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'default' => array(
									'description' => __( 'The default package.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'readonly'    => true,
									'properties'  => array(
										'package_name'    => array(
											'description' => __( 'The package name.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'rates'           => array(
											'description' => __( 'The packages returned after calculating shipping.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'object',
											'context'     => array( 'view' ),
											'readonly'    => true,
											'properties'  => array(
												'[a-z0-9]' => array(
													'key'  => array(
														'description' => __( 'The rate key.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'method_id' => array(
														'description' => __( 'The method ID.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'instance_id' => array(
														'description' => __( 'The instance ID.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'label' => array(
														'description' => __( 'The rate label.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'cost' => array(
														'description' => __( 'The rate cost.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'html' => array(
														'description' => __( 'The rate label and cost formatted.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'taxes' => array(
														'description' => __( 'The rate tax cost.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'string',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'chosen_method' => array(
														'description' => __( 'The chosen method.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'boolean',
														'context'     => array( 'view' ),
														'readonly'    => true,
													),
													'meta_data' => array(
														'description' => __( 'The rate meta data.', 'cart-rest-api-for-woocommerce' ),
														'type'        => 'object',
														'context'     => array( 'view' ),
														'readonly'    => true,
														'properties' => array(
															'items' => array(
																'description' => __( 'The items the shipping rate has calculated based on.', 'cart-rest-api-for-woocommerce' ),
																'type'        => 'string',
																'context'     => array( 'view' ),
																'readonly'    => true,
															),
														),
													),
												),
											),
										),
										'package_details' => array(
											'description' => __( 'The package details if any.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'index'           => array(
											'description' => __( 'The package index.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'chosen_method'   => array(
											'description' => __( 'The chosen method set from the available rates.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'formatted_destination' => array(
											'description' => __( 'The destination the package.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
							),
						),
					),
				),
				'fees'           => array(
					'description' => __( 'The cart fees.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'[a-zA-Z0-9]' => array(
							'name' => array(
								'description' => __( 'The fee name.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'fee'  => array(
								'description' => __( 'The fee value.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'taxes'          => array(
					'description' => __( 'The cart taxes.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'[A-Z-TAX-0-9]' => array(
							'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'properties'  => array(
								'name'  => array(
									'description' => __( 'The tax name.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'price' => array(
									'description' => __( 'The tax price.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
					),
				),
				'totals'         => array(
					'description' => __( 'The store currency information.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'subtotal'       => array(
							'description' => __( 'The subtotal of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'subtotal_tax'   => array(
							'description' => __( 'The subtotal tax of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'fee_total'      => array(
							'description' => __( 'The fee total.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'fee_tax'        => array(
							'description' => __( 'The fee tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'discount_total' => array(
							'description' => __( 'The discount total.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'discount_tax'   => array(
							'description' => __( 'The discount tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'shipping_total' => array(
							'description' => __( 'The shipping total of the selected packages.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'shipping_tax'   => array(
							'description' => __( 'The shipping tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'total'          => array(
							'description' => __( 'The total of everything in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'total_tax'      => array(
							'description' => __( 'The total tax.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'removed_items'  => array(
					'description' => __( 'Items that have been removed from the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'items'       => array(
						'type'       => 'object',
						'properties' => array(
							'item_key'       => array(
								'description' => __( 'Unique ID of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'id'             => array(
								'description' => __( 'Product ID or Variation ID of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'name'           => array(
								'description' => __( 'The name of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'title'          => array(
								'description' => __( 'The title of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'price'          => array(
								'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'quantity'       => array(
								'description' => __( 'The quantity of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'float',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'totals'         => array(
								'description' => __( 'The totals of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'subtotal'     => array(
										'description' => __( 'The subtotal of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'subtotal_tax' => array(
										'description' => __( 'The subtotal tax of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total'        => array(
										'description' => __( 'The total of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'total_tax'    => array(
										'description' => __( 'The total tax of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'float',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
								),
							),
							'slug'           => array(
								'description' => __( 'The product slug of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'meta'           => array(
								'description' => __( 'The meta data of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'readonly'    => true,
								'properties'  => array(
									'product_type' => array(
										'description' => __( 'The product type of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'sku'          => array(
										'description' => __( 'The SKU of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'dimensions'   => array(
										'description' => __( 'The dimensions of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(
											'length' => array(
												'description' => __( 'The length of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'width'  => array(
												'description' => __( 'The width of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'height' => array(
												'description' => __( 'The height of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
											'unit'   => array(
												'description' => __( 'The unit measurement of the item.', 'cart-rest-api-for-woocommerce' ),
												'type'     => 'string',
												'context'  => array( 'view' ),
												'readonly' => true,
											),
										),
									),
									'weight'       => array(
										'description' => __( 'The weight of the item.', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'string',
										'context'     => array( 'view' ),
										'readonly'    => true,
									),
									'variation'    => array(
										'description' => __( 'The variation attributes of the item (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
										'type'        => 'object',
										'context'     => array( 'view' ),
										'properties'  => array(),
									),
								),
							),
							'backorders'     => array(
								'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'cart_item_data' => array(
								'description' => __( 'Custom item data applied to the item (if any).', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'object',
								'context'     => array( 'view' ),
								'properties'  => array(),
							),
							'featured_image' => array(
								'description' => __( 'The featured image of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
					),
				),
				'cross_sells'    => array(
					'description' => __( 'Items you may be interested in adding to the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'array',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'id'             => array(
							'description' => __( 'Product ID or Variation ID of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'           => array(
							'description' => __( 'The name of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'title'          => array(
							'description' => __( 'The title of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'slug'           => array(
							'description' => __( 'The slug of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'price'          => array(
							'description' => __( 'The price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'regular_price'  => array(
							'description' => __( 'The regular price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'sale_price'     => array(
							'description' => __( 'The sale price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'image'          => array(
							'description' => __( 'The image of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'average_rating' => array(
							'description' => __( 'The average rating of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'on_sale'        => array(
							'description' => __( 'The sale status of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'type'           => array(
							'description' => __( 'The product type of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'notices'        => array(
					'description' => __( 'The cart notices.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'success' => array(
							'description' => __( 'Notices for successful actions.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type' => 'string',
							),
						),
						'info'    => array(
							'description' => __( 'Notices for informational actions.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type' => 'string',
							),
						),
						'error'   => array(
							'description' => __( 'Notices for error actions.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view' ),
							'readonly'    => true,
							'items'       => array(
								'type' => 'string',
							),
						),
					),
				),
			),
		);

		/**
		 * This filter is now deprecated and is replaced with `cocart_cart_items_schema`.
		 *
		 * @deprecated 3.1.0 Use `cocart_cart_items_schema` filter instead.
		 *
		 * @see cocart_cart_items_schema
		 */
		cocart_do_deprecated_filter( 'cocart_cart_schema', '3.1.0', 'cocart_cart_items_schema', __( 'Changed for the purpose of not overriding default properties.', 'cart-rest-api-for-woocommerce' ), array( $this->schema['properties'] ) );

		/**
		 * Extend the cart schema properties for items.
		 *
		 * This filter allows you to extend the cart schema properties for items without removing any default properties.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$this->schema['properties']['items']['items']['properties'] += apply_filters( 'cocart_cart_items_schema', array() );

		return $this->schema;
	} // END get_public_item_schema()

	/**
	 * Get the query params for getting the cart.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return array $params The query params.
	 */
	public function get_collection_params() {
		$params = array(
			'cart_key' => array(
				'description'       => __( 'Unique identifier for the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_key',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'fields'   => array(
				'description'       => __( 'Specify each parent field you want to request separated by (,) in the response before the data is fetched.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'required'          => false,
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
			'thumb'    => array(
				'description'       => __( 'True if you want to return the URL of the featured product image for each item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'default'           => true,
				'type'              => 'boolean',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
			'default'  => array(
				'description'       => __( 'Return the default cart data if set to true.', 'cart-rest-api-for-woocommerce' ),
				'default'           => false,
				'type'              => 'boolean',
				'required'          => false,
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		/**
		 * Extend the query parameters for the cart.
		 *
		 * This filter allows you to extend the query parameters without removing any default parameters.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$params += apply_filters( 'cocart_cart_query_parameters', array() );

		return $params;
	} // END get_collection_params()
} // END class
