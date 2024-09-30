<?php
/**
 * REST API: CoCart_REST_Session_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Sessions\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Session_V2_Controller', 'CoCart_Session_V2_Controller' );

/**
 * Controller for returning details of a specific cart via the REST API. (API v2)
 *
 * This REST API controller handles requests to returns details of a singular cart
 * via "cocart/v2/session" endpoint.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Session_V2_Controller extends CoCart_REST_Cart_V2_Controller {

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
	protected $rest_base = 'session';

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
	 * Register the routes for index.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<session_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);

		// Delete Cart in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6 (DELETE).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<session_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_cart' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			)
		);

		// Get Cart Items in Session - cocart/v2/session/ec2b1f30a304ed513d2975b7b9f222f6/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<session_key>[\w]+)/items',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_cart_items_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_item_schema' ),
			)
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns a saved cart in session if one exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function get_cart_in_session( $request = array() ) {
		$session_key = ! empty( $request['session_key'] ) ? $request['session_key'] : '';

		try {
			// The cart key is a required variable.
			if ( empty( $session_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_key_missing', __( 'Session Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Get the cart in the database.
			$cart = WC()->session->get_session( $session_key );

			// If no cart is saved with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $this->return_session_data( $request, maybe_unserialize( $cart ) ), $this->namespace, $this->rest_base );
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_in_session()

	/**
	 * Deletes the cart in session. Once a Cart has been deleted it can not be recovered.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function delete_cart( $request = array() ) {
		try {
			$session_key = ! empty( $request['session_key'] ) ? $request['session_key'] : '';

			if ( empty( $session_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_key_missing', __( 'Session Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// If no session is saved with the ID specified return error.
			if ( empty( WC()->session->get_session( $session_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_not_valid', __( 'Session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Delete cart session.
			WC()->session->delete_cart( $session_key );

			if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
				delete_user_meta( $session_key, '_woocommerce_persistent_cart_' . get_current_blog_id() );
			}

			if ( ! empty( WC()->session->get_session( $session_key ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_not_deleted', __( 'Session could not be deleted!', 'cart-rest-api-for-woocommerce' ), 500 );
			}

			return CoCart_Response::get_response( __( 'Session successfully deleted!', 'cart-rest-api-for-woocommerce' ), $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END delete_cart()

	/**
	 * Returns the cart items from the session.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function get_cart_items_in_session( $request = array() ) {
		$session_key = ! empty( $request['session_key'] ) ? $request['session_key'] : '';
		$show_thumb  = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		try {
			// The cart key is a required variable.
			if ( empty( $session_key ) ) {
				throw new CoCart_Data_Exception( 'cocart_session_key_missing', __( 'Session Key is required!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			// Get the cart in the database.
			$cart = WC()->session->get_session( $session_key );

			// If no cart is saved with the ID specified return error.
			if ( empty( $cart ) ) {
				throw new CoCart_Data_Exception( 'cocart_cart_in_session_not_valid', __( 'Cart in session is not valid!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $this->get_items( maybe_unserialize( $cart['cart'] ), $show_thumb ), $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart_items_in_session()

	/**
	 * Return session data.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WP_REST_Request $request      The request object.
	 * @param array           $session_data Session data.
	 *
	 * @return array $session
	 */
	public function return_session_data( $request = array(), $session_data = array() ) {
		// Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		// Customer.
		$customer = '';

		if ( isset( $session_data['customer'] ) ) {
			$customer = maybe_unserialize( $session_data['customer'] );
		}

		// Session response.
		$session = array(
			'cart_key'      => $request['session_key'],
			'customer'      => array(
				'billing_address'  => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'billing', $this->get_customer( $customer ) ),
				'shipping_address' => CoCart_Utilities_Cart_Helpers::get_customer_fields( 'shipping', $this->get_customer( $customer ) ),
			),
			'items'         => array(),
			'item_count'    => $this->get_cart_contents_count( $session_data ),
			'items_weight'  => (string) wc_get_weight( $this->get_cart_contents_weight( $session_data ), get_option( 'woocommerce_weight_unit' ) ),
			'coupons'       => array(),
			'fees'          => $this->get_fees( $session_data ),
			'totals'        => array(
				'subtotal'       => cocart_format_money( $this->get_subtotal( $session_data ) ),
				'subtotal_tax'   => cocart_format_money( $this->get_subtotal_tax( $session_data ) ),
				'fee_total'      => cocart_format_money( $this->get_fee_total( $session_data ) ),
				'fee_tax'        => cocart_format_money( $this->get_fee_tax( $session_data ) ),
				'discount_total' => cocart_format_money( $this->get_discount_total( $session_data ) ),
				'discount_tax'   => cocart_format_money( $this->get_discount_tax( $session_data ) ),
				'shipping_total' => cocart_format_money( $this->get_shipping_total( $session_data ) ),
				'shipping_tax'   => cocart_format_money( $this->get_shipping_tax( $session_data ) ),
				'total'          => cocart_format_money( $this->get_total( $session_data ) ),
				'total_tax'      => cocart_format_money( $this->get_total_tax( $session_data ) ),
			),
			'needs_payment' => $this->needs_payment( $session_data ),
			'removed_items' => $this->get_removed_items( $this->get_removed_cart_contents( $session_data ), $show_thumb ),
		);

		if ( array_key_exists( 'coupons', $session ) ) {
			// Returns each coupon applied and coupon total applied if store has coupons enabled.
			$coupons = wc_coupons_enabled() ? $this->get_applied_coupons( $session_data ) : array();

			if ( ! empty( $coupons ) ) {
				foreach ( $coupons as $coupon ) {
					$session['coupons'][] = array(
						'coupon'      => wc_format_coupon_code( wp_unslash( $coupon ) ),
						'label'       => esc_attr( wc_cart_totals_coupon_label( $coupon, false ) ),
						'saving'      => $this->coupon_html( $coupon, false ),
						'saving_html' => $this->coupon_html( $coupon ),
					);
				}
			}
		}

		// Returns items.
		if ( array_key_exists( 'items', $session ) ) {
			if ( isset( $session_data['cart_cache'] ) ) {
				$session['items'] = $this->get_items( maybe_unserialize( $session_data['cart_cache'] ), $show_thumb );
			} else {
				$session['items'] = $this->get_items( maybe_unserialize( $session_data['cart'] ), $show_thumb );
			}
		}

		return $session;
	} // END return_session_data()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product      The product data of the item in the cart.
	 * @param array      $cart_item    The item in the cart containing the default cart item data.
	 * @param string     $item_key     The item key generated based on the details of the item.
	 * @param boolean    $show_thumb   Determines if requested to return the item featured thumbnail.
	 * @param boolean    $removed_item Determines if the item in the cart is removed.
	 *
	 * @return array $item Full details of the item in the cart and it's purchase limits.
	 */
	public function get_item( $product, $cart_item = array(), $item_key = '', $show_thumb = true, $removed_item = false ) {
		$tax_display_mode = CoCart_Utilities_Cart_Helpers::get_tax_display_mode();
		$price_function   = CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode( $tax_display_mode );

		$item = array(
			'item_key'       => $item_key,
			'id'             => $product->get_id(),
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
			'title'          => apply_filters( 'cocart_cart_item_title', $product->get_title(), $product, $cart_item, $item_key ),
			/**
			 * Filter allows the price of the item to change.
			 *
			 * Warning: This filter does not represent the true value that totals will be calculated on.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $product_price Product price.
			 * @param array  $cart_item     The cart item data.
			 * @param string $item_key      The item key generated based on the details of the item.
			 */
			'price'          => apply_filters( 'cocart_cart_item_price', cocart_format_money( $price_function( $product ) ), $cart_item, $item_key ),
			'quantity'       => array(
				/**
				 * Filter allows the quantity of the item to change.
				 *
				 * Warning: This filter does not represent the quantity of the item that totals will be calculated on.
				 *
				 * @since 3.0.0 Introduced.
				 *
				 * @param string $item_quantity Item quantity.
				 * @param string $item_key      The item key generated based on the details of the item.
				 * @param array  $cart_item     The cart item data.
				 */
				'value'        => apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item ),
				'min_purchase' => $product->get_min_purchase_quantity(),
				'max_purchase' => $product->get_max_purchase_quantity(),
			),
			'totals'         => array(
				'subtotal'     => cocart_format_money( $cart_item['line_subtotal'] ),
				'subtotal_tax' => cocart_format_money( $cart_item['line_subtotal_tax'] ),
				'total'        => cocart_format_money( $cart_item['line_total'] ),
				'tax'          => cocart_format_money( $cart_item['line_tax'] ),
			),
			'slug'           => $this->get_product_slug( $product ),
			'meta'           => array(
				'product_type' => $product->get_type(),
				'sku'          => $product->get_sku(),
				'dimensions'   => array(),
				'weight'       => $product->has_weight() ? (string) wc_get_weight( $product->get_weight() * (int) $cart_item['quantity'], get_option( 'woocommerce_weight_unit' ) ) : '0.0',
			),
			'backorders'     => '',
			'cart_item_data' => array(),
			'featured_image' => '',
		);

		// Item dimensions.
		$dimensions = $product->get_dimensions( false );
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
		$item['meta']['variation'] = cocart_format_variation_data( $cart_item['variation'], $product );

		// Backorder notification.
		$item['backorders'] = $product->backorders_require_notification() && $product->is_on_backorder( $cart_item['quantity'] ) ? wp_kses_post( apply_filters( 'cocart_cart_item_backorder_notification', esc_html__( 'Available on backorder', 'cart-rest-api-for-woocommerce' ), $product->get_id() ) ) : '';

		// Prepares the remaining cart item data.
		$cart_item = CoCart_Utilities_Cart_Helpers::prepare_item( $cart_item );

		/**
		 * Filter allows you to alter the remaining cart item data.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array  $cart_item The cart item data.
		 * @param string $item_key  Generated ID based on the product information when added to the cart.
		 */
		$cart_item_data = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key );

		// Returns remaining cart item data.
		$cart_item_data         = ! empty( $cart_item ) ? $cart_item_data : array();
		$item['cart_item_data'] = $cart_item_data;

		// If thumbnail is requested then add it to each item in cart.
		if ( $show_thumb ) {
			// Get thumbnail ID.
			$thumbnail_id = CoCart_Utilities_Cart_Helpers::get_item_thumbnail_id( $product, $cart_item, $item_key, $removed_item );

			// Get thumbnail size.
			$thumbnail_size = CoCart_Utilities_Cart_Helpers::get_thumbnail_size( $removed_item );

			// Get thumbnail source.
			$thumbnail_src = CoCart_Utilities_Cart_Helpers::get_thumbnail_source( $thumbnail_id, $thumbnail_size, $cart_item, $item_key, $removed_item );

			// Add main featured image.
			$item['featured_image'] = $thumbnail_src;
		}

		return $item;
	} // END get_item()

	/**
	 * Gets the array of applied coupon codes.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return array Of applied coupons.
	 */
	public function get_applied_coupons( $session_data = array() ) {
		return (array) maybe_unserialize( $session_data['applied_coupons'] );
	} // END get_applied_coupons()

	/**
	 * Get number of items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return int
	 */
	public function get_cart_contents_count( $session_data = array() ) {
		return array_sum( wp_list_pluck( maybe_unserialize( $session_data['cart'] ), 'quantity' ) );
	} // END get_cart_contents_count()

	/**
	 * Get weight of items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_cart_contents_weight( $session_data = array() ) {
		$weight = 0.0;

		$cart_contents = maybe_unserialize( $session_data['cart'] );

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
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return bool
	 */
	public function needs_payment( $session_data = array() ) {
		return 0 < $this->get_total( $session_data );
	} // END needs_payment()

	/**
	 * Get cart fees.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return array
	 */
	public function get_fees( $session_data = array() ) {
		$cart_fees = isset( $session_data['cart_fees'] ) ? maybe_unserialize( $session_data['cart_fees'] ) : array();

		$fees = array();

		if ( ! empty( $cart_fees ) ) {
			foreach ( $cart_fees as $key => $fee ) {
				$fees[ $key ] = array(
					'name' => esc_html( $fee['name'] ),
					'fee'  => cocart_format_money( $this->fee_html( $session_data, $fee ) ),
				);
			}
		}

		return $fees;
	} // END get_fees()

	/**
	 * Get the fee value.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 * @param array $fee          Fee data.
	 *
	 * @return string Returns the fee value.
	 */
	public function fee_html( $session_data = array(), $fee = object ) {
		$cart_totals_fee_html = $this->display_prices_including_tax( $session_data ) ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );

		return apply_filters( 'cocart_cart_totals_fee_html', $cart_totals_fee_html, $fee );
	} // END fee_html()

	/**
	 * Get a total.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array  $session_data Session data.
	 * @param string $key          Key of element in $totals array.
	 *
	 * @return mixed
	 */
	protected function get_totals_var( $session_data = array(), $key = '' ) {
		$totals = maybe_unserialize( $session_data['cart_totals'] );

		return isset( $totals[ $key ] ) ? $totals[ $key ] : $this->default_totals[ $key ];
	} // END get_totals_var()

	/**
	 * Get subtotal.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_subtotal( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'subtotal' );
	} // END get_subtotal()

	/**
	 * Get subtotal_tax.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_subtotal_tax( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'subtotal_tax' );
	} // END get_subtotal_tax()

	/**
	 * Get discount_total.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_discount_total( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'discount_total' );
	} // END get_discount_total()

	/**
	 * Get discount_tax.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_discount_tax( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'discount_tax' );
	} // END get_discount_tax()

	/**
	 * Get shipping_total.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_shipping_total( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'shipping_total' );
	} // END get_shipping_total()

	/**
	 * Get shipping_tax.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_shipping_tax( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'shipping_tax' );
	} // END get_shipping_tax()

	/**
	 * Gets cart total after calculation.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float|string
	 */
	public function get_total( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'total' );
	} // END get_total()

	/**
	 * Get total tax amount.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_total_tax( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'total_tax' );
	} // END get_total_tax()

	/**
	 * Get total fee amount.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_fee_total( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'fee_total' );
	} // END get_fee_total()

	/**
	 * Get total fee tax amount.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return float
	 */
	public function get_fee_tax( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'fee_tax' );
	} // END get_fee_tax()

	/**
	 * Get shipping taxes.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 */
	public function get_shipping_taxes( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'shipping_taxes' );
	} // END get_shipping_taxes()

	/**
	 * Get cart content taxes.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 */
	public function get_cart_contents_taxes( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'cart_contents_taxes' );
	} // END get_cart_contents_taxes()

	/**
	 * Get fee taxes.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 */
	public function get_fee_taxes( $session_data = array() ) {
		return $this->get_totals_var( $session_data, 'fee_taxes' );
	} // END get_fee_taxes()

	/**
	 * Return whether or not the cart is displaying prices including tax, rather than excluding tax.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return bool
	 */
	public function display_prices_including_tax( $session_data = array() ) {
		return 'incl' === $this->get_tax_price_display_mode( $session_data );
	} // END display_prices_including_tax()

	/**
	 * Returns 'incl' if tax should be included in cart, otherwise returns 'excl'.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return string
	 */
	public function get_tax_price_display_mode( $session_data = array() ) {
		$customer = '';

		if ( isset( $session_data['customer'] ) ) {
			$customer = maybe_unserialize( $session_data['customer'] );
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
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param mixed $customer Customer object or ID.
	 *
	 * @return WC_Customer $customer Customer object or ID.
	 */
	public function get_customer( $customer = 0 ) {
		if ( is_numeric( $customer ) ) {
			$user = get_user_by( 'id', $customer );

			// If user id does not exist then set as new customer.
			if ( false === $user ) {
				$customer = 0;
			}
		}

		return new WC_Customer( $customer, true );
	} // END get_customer()

	/**
	 * Return items removed from the cart.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $session_data Session data.
	 *
	 * @return array
	 */
	public function get_removed_cart_contents( $session_data = array() ) {
		return (array) maybe_unserialize( $session_data['removed_cart_contents'] );
	} // END get_removed_cart_contents()
} // END class
