<?php
/**
 * CoCart - Cart controller
 *
 * Handles requests to the cart endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v3
 * @since   4.0.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v3 - Cart controller class.
 *
 * @package CoCart REST API/API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Cart_V3_Controller extends CoCart_Cart_V2_Controller {

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
	 * Total defaults.
	 *
	 * @var array
	 */
	protected $default_totals = array(
		'subtotal'            => 0,
		'subtotal_tax'        => 0,
		'shipping_total'      => 0,
		'shipping_tax'        => 0,
		'shipping_taxes'      => array(),
		'discount_total'      => 0,
		'discount_tax'        => 0,
		'cart_contents_total' => 0,
		'cart_contents_tax'   => 0,
		'cart_contents_taxes' => array(),
		'fee_total'           => 0,
		'fee_tax'             => 0,
		'fee_taxes'           => array(),
		'total'               => 0,
		'total_tax'           => 0,
	);

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Get Cart Items - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_items' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				//'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Count Items in Cart - cocart/v3/cart/ec2b1f30a304ed513d2975b7b9f222f6/items/count (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<cart_key>[\w]+)/items/count',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'count_cart_items' ),
					'permission_callback' => array( $this, 'validate_cart' ),
					'args'                => $this->get_collection_params(),
				),
				//'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Checks whether the requested cart exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function validate_cart( $request ) {
		$cart_key = ! empty( $request['cart_key'] ) ? trim( $request['cart_key'] ) : '';

		try {
			// The cart key is a required variable.
			if ( empty( $cart_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_key_missing', __( 'Cart Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Load session handler.
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';

			$handler = new CoCart_Session_Handler();

			// Get the cart in the database.
			$cart = $handler->get_cart( $cart_key );

			// TODO: Add possibly validation of the cart expiration.

			// If no cart with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_not_valid', __( 'Cart is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return true;
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END validate_cart()

	/**
	 * Gets the cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access     public
	 * @since      4.0.0 Introduced.
	 * @param      WP_REST_Request $request       Full details about the request.
	 * @deprecated string          $cart_item_key Originally the cart item key. Now deprecated.
	 * @return     WP_REST_Response               Returns the cart data from the database.
	 */
	public function get_cart( $request = array(), $cart_item_key = null ) {
		$cart_key = ! empty( $request['cart_key'] ) ? trim( $request['cart_key'] ) : '';

		try {
			// Load session handler.
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';

			$handler = new CoCart_Session_Handler();

			// Get the cart in the database.
			$cart = $handler->get_cart( $cart_key );

			return CoCart_Response::get_response( $this->return_cart_data( $request, maybe_unserialize( $cart ) ), $this->namespace, $this->rest_base );
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart()

	/**
	 * Returns the cart items.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response         Returns the cart items.
	 */
	public function get_cart_items( $request = array() ) {
		$cart_key   = ! empty( $request['cart_key'] ) ? trim( $request['cart_key'] ) : '';
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		try {
			// Load session handler.
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';

			$handler = new CoCart_Session_Handler();

			// Get the cart in the database.
			$cart = $handler->get_cart( $cart_key );

			return CoCart_Response::get_response( $this->get_items( maybe_unserialize( $cart['cart'] ), $show_thumb ), $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_items()

	/**
	 * Return cart data.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  WP_REST_Request $request   Full details about the request.
	 * @param  array           $cart_data Cart data.
	 * @return array           $cart
	 */
	public function return_cart_data( $request = array(), $cart_data = array() ) {
		// Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		// Customer.
		$customer = '';

		if ( isset( $cart_data['customer'] ) ) {
			$customer = maybe_unserialize( $cart_data['customer'] );
		}

		// Cart response.
		$cart = array(
			'customer'       => array(
				//'billing_address'  => $this->get_customer_fields( 'billing', $this->get_customer( $customer ) ), // TODO: Needs a fix or an override.
				//'shipping_address' => $this->get_customer_fields( 'shipping', $this->get_customer( $customer ) ),
			),
			'items'          => array(),
			'item_count'     => $this->get_cart_contents_count( $cart_data ),
			'items_weight'   => wc_get_weight( (float) $this->get_cart_contents_weight( $cart_data ), get_option( 'woocommerce_weight_unit' ) ),
			'coupons'        => array(),
			'needs_payment'  => $this->needs_payment( $cart_data ),
			'needs_shipping' => array(), // TODO: Requires new shipping calculation system.
			'shipping'       => array(), // TODO: Needs new shipping calculation system.
			'fees'           => $this->get_fees( $cart_data ),
			'taxes'          => array(), // TODO: Needs new tax calculation system.
			'totals'         => array(
				'subtotal'       => cocart_prepare_money_response( $this->get_subtotal( $cart_data ), wc_get_price_decimals() ),
				'subtotal_tax'   => cocart_prepare_money_response( $this->get_subtotal_tax( $cart_data ), wc_get_price_decimals() ),
				'fee_total'      => cocart_prepare_money_response( $this->get_fee_total( $cart_data ), wc_get_price_decimals() ),
				'fee_tax'        => cocart_prepare_money_response( $this->get_fee_tax( $cart_data ), wc_get_price_decimals() ),
				'discount_total' => cocart_prepare_money_response( $this->get_discount_total( $cart_data ), wc_get_price_decimals() ),
				'discount_tax'   => cocart_prepare_money_response( $this->get_discount_tax( $cart_data ), wc_get_price_decimals() ),
				'shipping_total' => cocart_prepare_money_response( $this->get_shipping_total( $cart_data ), wc_get_price_decimals() ),
				'shipping_tax'   => cocart_prepare_money_response( $this->get_shipping_tax( $cart_data ), wc_get_price_decimals() ),
				'total'          => cocart_prepare_money_response( $this->get_total( $cart_data ), wc_get_price_decimals() ),
				'total_tax'      => cocart_prepare_money_response( $this->get_total_tax( $cart_data ), wc_get_price_decimals() ),
			),
			'removed_items'  => $this->get_removed_items( $this->get_removed_cart_contents( $cart_data ), $show_thumb ),
			'cross_sells'    => '', // TODO: Needs reverse engineering.
			'notices'        => '', // TODO: Needs reverse engineering.
		);

		if ( array_key_exists( 'coupons', $cart ) ) {
			// Returns each coupon applied and coupon total applied if store has coupons enabled.
			$coupons = wc_coupons_enabled() ? $this->get_applied_coupons( $cart_data ) : array();

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					$cart['coupons'][] = array(
						'coupon'      => wc_format_coupon_code( wp_unslash( $coupon ) ),
						'label'       => esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ),
						'saving'      => $this->coupon_html( $coupon, false ),
						'saving_html' => $this->coupon_html( $coupon ),
					);
				}
			}
		}

		// Returns items.
		if ( array_key_exists( 'items', $cart ) ) {
			if ( isset( $cart_data['cart_cache'] ) ) {
				$cart['items'] = $this->get_items( maybe_unserialize( $cart_data['cart_cache'] ), $show_thumb );
			} else {
				$cart['items'] = $this->get_items( maybe_unserialize( $cart_data['cart'] ), $show_thumb );
			}
		}

		return $cart;
	} // END return_cart_data()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  WC_Product $_product     The product data of the item in the cart.
	 * @param  array      $cart_item    The item in the cart containing the default cart item data.
	 * @param  string     $item_key     The item key generated based on the details of the item.
	 * @param  boolean    $show_thumb   Determines if requested to return the item featured thumbnail.
	 * @param  boolean    $removed_item Determines if the item in the cart is removed.
	 * @return array      $item         Full details of the item in the cart and it's purchase limits.
	 */
	public function get_item( $_product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$item = array(
			'item_key'       => $item_key,
			'id'             => $_product->get_id(),
			'name'           => apply_filters( 'cocart_cart_item_name', $_product->get_name(), $_product, $cart_item, $item_key ),
			'title'          => apply_filters( 'cocart_cart_item_title', $_product->get_title(), $_product, $cart_item, $item_key ),
			'price'          => apply_filters( 'cocart_cart_item_price', wc_format_decimal( $_product->get_price(), wc_get_price_decimals() ), $cart_item, $item_key ),
			'quantity'       => array(
				'value'        => apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item ),
				'min_purchase' => $_product->get_min_purchase_quantity(),
				'max_purchase' => $_product->get_max_purchase_quantity(),
			),
			'totals'         => array(
				'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', $cart_item['line_subtotal'], $cart_item, $item_key ),
				'subtotal_tax' => apply_filters( 'cocart_cart_item_subtotal_tax', $cart_item['line_subtotal_tax'], $cart_item, $item_key ),
				'total'        => apply_filters( 'cocart_cart_item_total', $cart_item['line_total'], $cart_item, $item_key ),
				'tax'          => apply_filters( 'cocart_cart_item_tax', $cart_item['line_tax'], $cart_item, $item_key ),
			),
			'slug'           => $this->get_product_slug( $_product ),
			'meta'           => array(
				'product_type' => $_product->get_type(),
				'sku'          => $_product->get_sku(),
				'dimensions'   => array(),
				'weight'       => wc_get_weight( (float) $_product->get_weight() * (int) $cart_item['quantity'], get_option( 'woocommerce_weight_unit' ) ),
			),
			'backorders'     => '',
			'cart_item_data' => array(),
			'featured_image' => '',
		);

		// Item dimensions.
		$dimensions = $_product->get_dimensions( false );
		if ( ! empty( $dimensions ) ) {
			$item['meta']['dimensions'] = array(
				'length' => $dimensions['length'],
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			);
		}

		// Variation data.
		if ( ! isset( $cart_item['variation'] ) ) {
			$cart_item['variation'] = array();
		}
		$item['meta']['variation'] = $this->format_variation_data( $cart_item['variation'], $_product );

		// Backorder notification.
		if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
			$item['backorders'] = wp_kses_post( apply_filters( 'cocart_cart_item_backorder_notification', esc_html__( 'Available on backorder', 'cart-rest-api-for-woocommerce' ), $_product->get_id() ) );
		}

		// Prepares the remaining cart item data.
		$cart_item = $this->prepare_item( $cart_item );

		// Collect all cart item data if any thing is left.
		if ( ! empty( $cart_item ) ) {
			$item['cart_item_data'] = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key, $cart_item );
		}

		// If thumbnail is requested then add it to each item in cart.
		if ( $show_thumb ) {
			$thumbnail_id = ! empty( $_product->get_image_id() ) ? $_product->get_image_id() : get_option( 'woocommerce_placeholder_image', 0 );

			$thumbnail_id = apply_filters( 'cocart_item_thumbnail', $thumbnail_id, $cart_item, $item_key, $removed_item );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_item_thumbnail_size', 'woocommerce_thumbnail', $removed_item ) );

			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';

			/**
			 * Filters the source of the product thumbnail.
			 *
			 * @since   2.1.0
			 * @version 3.0.0
			 * @param   string $thumbnail_src URL of the product thumbnail.
			 */
			$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src, $cart_item, $item_key, $removed_item );

			// Add main featured image.
			$item['featured_image'] = esc_url( $thumbnail_src );
		}

		return $item;
	} // END get_item()

	/**
	 * Gets the array of applied coupon codes.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return array Applied coupons.
	 */
	public function get_applied_coupons( $cart_data = array() ) {
		return (array) maybe_unserialize( $cart_data['applied_coupons'] );
	} // END get_applied_coupons()

	/**
	 * Get number of items in the cart.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return int
	 */
	public function get_cart_contents_count( $cart_data = array() ) {
		return array_sum( wp_list_pluck( maybe_unserialize( $cart_data['cart'] ), 'quantity' ) );
	} // END get_cart_contents_count()

	/**
	 * Get weight of items in the cart.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_cart_contents_weight( $cart_data = array() ) {
		$weight = 0.0;

		$cart_contents = maybe_unserialize( $cart_data['cart'] );

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// Product data will be missing so we need to apply it.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			if ( $cart_item['data']->has_weight() ) {
				$weight += (float) $cart_item['data']->get_weight() * $cart_item['quantity'];
			}
		}

		return $weight;
	} // END get_cart_contents_weight()

	/**
	 * Looks at the totals to see if payment is actually required.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return bool
	 */
	public function needs_payment( $cart_data = array() ) {
		return 0 < $this->get_total( $cart_data );
	} // END needs_payment()

	/**
	 * Get cart fees.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return array
	 */
	public function get_fees( $cart_data = array() ) {
		$cart_fees = isset( $cart_data['cart_fees'] ) ? maybe_unserialize( $cart_data['cart_fees'] ) : array();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee->name ),
					'fee'  => cocart_prepare_money_response( $this->fee_html( $cart_data, $fee ), wc_get_price_decimals() ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Get the fee value.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array  $cart_data Cart data.
	 * @param  object $fee       Fee data.
	 * @return string            Returns the fee value.
	 */
	public function fee_html( $cart_data = array(), $fee = object ) {
		$cart_totals_fee_html = $this->display_prices_including_tax( $cart_data ) ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );

		return apply_filters( 'cocart_cart_totals_fee_html', $cart_totals_fee_html, $fee );
	} // END fee_html()

	/**
	 * Get a total.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array  $cart_data Cart data.
	 * @param  string $key       Key of element in $totals array.
	 * @return mixed
	 */
	protected function get_totals_var( $cart_data = array(), $key = '' ) {
		$totals = maybe_unserialize( $cart_data['cart_totals'] );

		return isset( $totals[ $key ] ) ? $totals[ $key ] : $this->default_totals[ $key ];
	} // END get_totals_var()

	/**
	 * Get subtotal.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_subtotal( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'subtotal' );
	} // END get_subtotal()

	/**
	 * Get subtotal_tax.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_subtotal_tax( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'subtotal_tax' );
	} // END get_subtotal_tax()

	/**
	 * Get discount_total.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_discount_total( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'discount_total' );
	} // END get_discount_total()

	/**
	 * Get discount_tax.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_discount_tax( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'discount_tax' );
	} // END get_discount_tax()

	/**
	 * Get shipping_total.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_shipping_total( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'shipping_total' );
	} // END get_shipping_total()

	/**
	 * Get shipping_tax.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_shipping_tax( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'shipping_tax' );
	} // END get_shipping_tax()

	/**
	 * Gets cart total after calculation.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float|string
	 */
	public function get_total( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'total' );
	} // END get_total()

	/**
	 * Get total tax amount.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_total_tax( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'total_tax' );
	} // END get_total_tax()

	/**
	 * Get total fee amount.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_fee_total( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'fee_total' );
	} // END get_fee_total()

	/**
	 * Get total fee tax amount.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return float
	 */
	public function get_fee_tax( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'fee_tax' );
	} // END get_fee_tax()

	/**
	 * Get shipping taxes.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 */
	public function get_shipping_taxes( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'shipping_taxes' );
	} // END get_shipping_taxes()

	/**
	 * Get cart content taxes.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 */
	public function get_cart_contents_taxes( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'cart_contents_taxes' );
	} // END get_cart_contents_taxes()

	/**
	 * Get fee taxes.
	 *
	 * @access public
	 * @since 4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 */
	public function get_fee_taxes( $cart_data = array() ) {
		return $this->get_totals_var( $cart_data, 'fee_taxes' );
	} // END get_fee_taxes()

	/**
	 * Return whether or not the cart is displaying prices including tax, rather than excluding tax.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return bool
	 */
	public function display_prices_including_tax( $cart_data = array() ) {
		return 'incl' === $this->get_tax_price_display_mode( $cart_data );
	} // END display_prices_including_tax()

	/**
	 * Returns 'incl' if tax should be included in cart, otherwise returns 'excl'.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return string
	 */
	public function get_tax_price_display_mode( $cart_data = array() ) {
		$customer = '';

		if ( isset( $cart_data['customer'] ) ) {
			$customer = maybe_unserialize( $cart_data['customer'] );
		}

		if ( $this->get_customer( $customer ) && $this->get_customer( $customer )->get_is_vat_exempt() ) {
			return 'excl';
		}

		return get_option( 'woocommerce_tax_display_cart' );
	} // END get_tax_price_display_mode()

	/**
	 * Get cart's owner.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @return WC_Customer $customer Customer object or ID.
	 */
	public function get_customer( $customer = 0 ) {
		if ( is_numeric( $customer ) ) {
			$user = get_user_by( 'id', $customer );

			// If user id does not exist then set as new customer.
			if ( is_wp_error( $user ) ) {
				$customer = 0;
			}
		}

		return new WC_Customer( $customer, true );
	} // END get_customer()

	/**
	 * Return items removed from the cart.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  array $cart_data Cart data.
	 * @return array
	 */
	public function get_removed_cart_contents( $cart_data = array() ) {
		return (array) maybe_unserialize( $cart_data['removed_cart_contents'] );
	} // END get_removed_cart_contents()

	/**
	 * Count cart contents.
	 *
	 * @access public
	 * @since  4.0.0 Introduced.
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function count_cart_items( $request = array() ) {
		$cart_key      = ! empty( $request['cart_key'] ) ? $request['cart_key'] : '';
		$removed_items = isset( $request['removed_items'] ) ? $request['removed_items'] : false;

		try {
			// Load session handler.
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';

			$handler = new CoCart_Session_Handler();

			// Get the cart in the database.
			$cart = $handler->get_cart( $cart_key );

			// Return count items in cart.
			if ( isset( $request['removed_items'] ) && is_bool( $request['removed_items'] ) && $request['removed_items'] ) {
				$count = array_sum( wp_list_pluck( $this->get_removed_cart_contents(), 'quantity' ) );
			} else {
				$count = $this->get_cart_contents_count( maybe_unserialize( $cart ) );
			}

			if ( $count <= 0 ) {
				$message = __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' );

				CoCart_Logger::log( $message, 'notice' );

				/**
				 * Filters message about no items in the cart.
				 *
				 * @since 2.1.0
				 * @param string $message Message.
				 */
				$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

				return CoCart_Response::get_response( $message, $this->namespace, $this->rest_base );
			}

			return CoCart_Response::get_response( $count, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END count_cart_items()

} // END class
