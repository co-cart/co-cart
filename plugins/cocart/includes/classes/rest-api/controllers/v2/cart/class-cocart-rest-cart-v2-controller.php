<?php
/**
 * REST API: CoCart_REST_Cart_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//use CoCart\Abstracts;
use CoCart\Schemas\v2\CartSchema as Schema;
use CoCart\Session\Handler;

/**
 * Main cart controller that gets the requested cart in session
 * containing customers information, items added,
 * shipping options (if any), totals and more. (API v2)
 *
 * This REST API controller handles the request to get the cart
 * via "cocart/v2/cart" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_Cart_REST_Controller
 */
class CoCart_REST_Cart_v2_Controller extends CoCart_Cart_REST_Controller {

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
	 * Constructor.
	 */
	public function __construct() {
		$this->schema = new Schema;
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

	/**
	 * Return a cart item from the cart.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @param string $item_id   The item we are looking up in the cart.
	 * @param string $condition Default is 'add', other conditions are: container, update, remove, restore.
	 *
	 * @return array $item Returns details of the item in the cart if it exists.
	 */
	public function get_cart_item( $item_id, $condition = 'add' ) {
		$item = isset( $this->get_cart_instance()->cart_contents[ $item_id ] ) ? $this->get_cart_instance()->cart_contents[ $item_id ] : array();

		/**
		 * Filters the cart item before it is returned.
		 *
		 * Condition options: add, remove, restore and update.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array  $item      Details of the item in the cart if it exists.
		 * @param string $condition Condition of item. Default: add
		 */
		return apply_filters( 'cocart_get_cart_item', $item, $condition );
	} // EMD get_cart_item()

	/**
	 * Get cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param \WP_REST_Request $request       Request object.
	 * @param string           $cart_item_key Originally the cart item key.
	 *
	 * @return WP_REST_Response $response The response data.
	 */
	public function get_cart( \WP_REST_Request $request, $cart_item_key = null ) {
		try {
			// Checks that we are using CoCart session handler. If not detected, throw error response.
			if ( ! WC()->session instanceof Handler ) {
				throw new CoCart_Data_Exception( 'cocart_session_handler_not_found', __( 'CoCart session handler was not detected. Another plugin or third party code most likely is using `woocommerce_session_handler` filter to place another session handler in place.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$show_raw      = ! empty( $request['raw'] ) ? $request['raw'] : false; // Internal parameter request.
			$cart_contents = ! $this->is_completely_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

			// Return cart contents raw if requested.
			if ( $show_raw ) {
				return $cart_contents;
			}

			/**
			 * Filter allows you to modify the cart contents before it is returned.
			 *
			 * Useful for add-ons or 3rd party plugins.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param array           $cart_contents Cart contents.
			 * @param WC_Cart         Cart object.
			 * @param WP_REST_Request $request       Full details about the request.
			 */
			$cart_contents = apply_filters( 'cocart_before_get_cart', $cart_contents, $this->get_cart_instance(), $request );

			/**
			 * Deprecated action hook `cocart_get_cart`.
			 *
			 * @deprecated 3.0.0 Use `cocart_cart` hook instead.
			 *
			 * @see cocart_cart
			 */
			cocart_deprecated_hook( 'cocart_get_cart', '3.0.0', 'cocart_cart', null );

			// Ensures the cart totals are calculated before an API response is returned.
			$this->calculate_totals();

			$cart_contents = $this->return_cart_contents( $request, $cart_contents );

			return $this->get_response( $request, $cart_contents );
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_cart()

	/**
	 * Return cart contents.
	 *
	 * @access public
	 *
	 * @since 2.0.0 Introduced.
	 * @since 4.0.0 Deprecated use of `get_cart_template()` function and replaced by filtering the requested fields.
	 *
	 * @param WP_REST_Request $request       Full details about the request.
	 * @param array           $cart_contents Cart content.
	 * @param array           $cart_item_key Originally the cart item key.
	 * @param bool            $from_session  Identifies if the cart is called from a session.
	 *
	 * @return array $cart Returns cart contents.
	 */
	public function return_cart_contents( $request = array(), $cart_contents = array(), $cart_item_key = null, $from_session = false ) {
		/**
		 * Return the default cart data if set to true.
		 *
		 * @since 3.0.0 Introduced.
		 */
		if ( ! empty( $request['default'] ) && $request['default'] ) {
			return $cart_contents;
		}

		/**
		 * Gets requested fields to return in the response.
		 *
		 * Note: Pre-configured fields take priority over specified fields.
		 *
		 * @since 4.0.0 Introduced.
		 */
		if ( ! empty( $request['config']['fields'] ) ) {
			/**
			 * Returns fields in the response based on the configuration requested.
			 * They don't include any additional fields added to the cart by
			 * extending the schema from third-party plugins.
			 */
			$fields = $this->get_fields_configuration( $request );
		} else {
			$fields = $this->get_fields_for_response( $request );
		}

		// Cart response container.
		$cart = array();

		if ( rest_is_field_included( 'cart_hash', $fields ) ) {
			$cart['cart_hash'] = ! empty( $this->get_cart_instance()->get_cart_hash() ) ? $this->get_cart_instance()->get_cart_hash() : __( 'No items in cart so no hash', 'cart-rest-api-for-woocommerce' );
		}

		if ( rest_is_field_included( 'cart_key', $fields ) ) {
			$cart['cart_key'] = $this->get_cart_key( $request );
		}

		if ( rest_is_field_included( 'currency', $fields ) ) {
			$cart['currency'] = cocart_get_store_currency();
		}

		if ( rest_is_field_included( 'customer', $fields ) ) {
			$cart['customer'] = array(
				'billing_address'  => $this->get_customer_fields( 'billing' ),
				'shipping_address' => $this->get_customer_fields( 'shipping' ),
			);
		}

		if ( rest_is_field_included( 'items', $fields ) ) {
			$cart['items'] = $this->get_items( $cart_contents, $request );
		}

		if ( rest_is_field_included( 'item_count', $fields ) ) {
			$cart['item_count'] = $this->get_cart_instance()->get_cart_contents_count();
		}

		if ( rest_is_field_included( 'items_weight', $fields ) ) {
			$cart['items_weight'] = wc_get_weight( (float) $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
		}

		if ( rest_is_field_included( 'coupons', $fields ) ) {
			$cart['coupons'] = array();

			// Returns each coupon applied and coupon total applied if store has coupons enabled.
			$coupons = wc_coupons_enabled() ? $this->get_cart_instance()->get_applied_coupons() : array();

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

		if ( rest_is_field_included( 'needs_payment', $fields ) ) {
			$cart['needs_payment'] = $this->get_cart_instance()->needs_payment();
		}

		if ( rest_is_field_included( 'needs_shipping', $fields ) ) {
			$cart['needs_shipping'] = $this->get_cart_instance()->needs_shipping();
		}

		if ( rest_is_field_included( 'shipping', $fields ) ) {
			$cart['shipping'] = $this->get_shipping_details();
		}

		if ( rest_is_field_included( 'fees', $fields ) ) {
			$cart['fees'] = $this->get_fees( $this->get_cart_instance() );
		}

		if ( rest_is_field_included( 'taxes', $fields ) ) {
			$cart['taxes'] = array();

			// Return calculated tax based on store settings and customer details.
			if ( wc_tax_enabled() && ! $this->get_cart_instance()->display_prices_including_tax() ) {
				$taxable_address = WC()->customer->get_taxable_address();
				$estimated_text  = '';

				if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
					/* translators: %s location. */
					$estimated_text = sprintf( ' ' . esc_html__( '(estimated for %s)', 'cart-rest-api-for-woocommerce' ), WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
				}

				if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
					$cart['taxes'] = $this->get_tax_lines( $this->get_cart_instance() );
				} else {
					$cart['taxes'] = array(
						'label' => esc_html( WC()->countries->tax_or_vat() ) . $estimated_text,
						'total' => apply_filters( 'cocart_cart_totals_taxes_total', $this->get_cart_instance()->get_taxes_total(), $request ),
					);
				}
			}
		}

		if ( rest_is_field_included( 'totals', $fields ) ) {
			$cart['totals'] = $this->get_cart_totals( $request, $this->get_cart_instance(), $fields );
		}

		if ( rest_is_field_included( 'removed_items', $fields ) ) {
			$cart['removed_items'] = $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $request );
		}

		if ( rest_is_field_included( 'cross_sells', $fields ) ) {
			$cart['cross_sells'] = $this->get_cross_sells();
		}

		if ( rest_is_field_included( 'notices', $fields ) ) {
			$cart['notices'] = $this->maybe_return_notices();
		}

		// If the cart is empty then return nothing.
		if ( empty( $cart_contents ) ) {

			/**
			 * Filters the response for empty cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param array $cart The whole cart.
			 */
			return apply_filters( 'cocart_empty_cart', $cart );
		}

		/**
		 * Filters the cart contents before it is returned.
		 *
		 * @since 3.0.0 Introduced.
		 * @since 4.0.0 Added `$request` (REST API request) and `$this` (cart controller class) as parameters.
		 *
		 * @deprecated 4.0.0 No longer use `$from_session` parameter.
		 *
		 * @param array           $cart    The whole cart before it's returned.
		 * @param WP_REST_Request $request Full details about the request.
		 * @param object          $this    The cart controller.
		 */
		$cart = apply_filters( 'cocart_cart', $cart, $request, $this );

		return $cart;
	} // END return_cart_contents()

	/**
	 * Prepares a list of store currency data to return in responses.
	 *
	 * @access public
	 *
	 * @since      3.0.0 Introduced.
	 * @deprecated 3.1.0 Use `cocart_get_store_currency()` instead.
	 *
	 * @see cocart_get_store_currency()
	 *
	 * @return array
	 */
	public function get_store_currency() {
		cocart_deprecated_function( __FUNCTION__, '3.1', 'cocart_get_store_currency' );

		return cocart_get_store_currency();
	} // END get_store_currency()

	/**
	 * Get tax lines from the cart and format to match schema.
	 *
	 * @access protected
	 *
	 * @param WC_Cart $cart Cart class instance.
	 *
	 * @return array Tax lines.
	 */
	protected function get_tax_lines( $cart ) {
		$cart_tax_totals = $cart->get_tax_totals();
		$tax_lines       = array();

		foreach ( $cart_tax_totals as $code => $tax ) {
			$tax_lines[ $code ] = array(
				'name'  => $tax->label,
				'price' => (float) cocart_prepare_money_response( $tax->amount ),
			);
		}

		return $tax_lines;
	} // END get_tax_lines()

	/**
	 * Convert monetary values from WooCommerce to string based integers, using
	 * the smallest unit of a currency.
	 *
	 * @access public
	 *
	 * @since      3.0.0 Introduced.
	 * @deprecated 3.1.0 Use `cocart_prepare_money_response()` function instead.
	 *
	 * @see cocart_prepare_money_response()
	 *
	 * @param string|float $amount        Monetary amount with decimals.
	 * @param int          $decimals      Number of decimals the amount is formatted with.
	 * @param int          $rounding_mode Defaults to the PHP_ROUND_HALF_UP constant.
	 *
	 * @return string The new amount.
	 */
	public function prepare_money_response( $amount, $decimals = 2, $rounding_mode = PHP_ROUND_HALF_UP ) {
		cocart_deprecated_function( __FUNCTION__, '3.1', 'cocart_prepare_money_response' );

		return cocart_prepare_money_response( $amount );
	} // END prepare_money_response()

	/**
	 * Format variation data, for example convert slugs such as attribute_pa_size to Size.
	 *
	 * @access protected
	 *
	 * @since      3.0.0 Introduced.
	 * @deprecated 4.0.0 Replaced with a global function `cocart_format_variation_data()`
	 *
	 * @param array      $variation_data Array of data from the cart.
	 * @param WC_Product $product        Product object.
	 *
	 * @return array Formatted variation data.
	 */
	protected function format_variation_data( $variation_data, $product ) {
		cocart_deprecated_function( __FUNCTION__, '3.1', 'cocart_format_variation_data' );

		$return = array();

		if ( empty( $variation_data ) ) {
			return $return;
		}

		foreach ( $variation_data as $key => $value ) {
			$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $key ) ) );

			if ( taxonomy_exists( $taxonomy ) ) {
				// If this is a term slug, get the term's nice name.
				$term = get_term_by( 'slug', $value, $taxonomy );
				if ( ! is_wp_error( $term ) && $term && $term->name ) {
					$value = $term->name;
				}
				$label = wc_attribute_label( $taxonomy );
			} else {
				// If this is a custom option slug, get the options name.
				$value = apply_filters( 'cocart_variation_option_name', $value, $product );
				$label = wc_attribute_label( str_replace( 'attribute_', '', $key ), $product );
			}

			$return[ $label ] = $value;
		}

		return $return;
	} // END format_variation_data()

	/**
	 * Get coupon in HTML.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.0.4
	 *
	 * @param string|WC_Coupon $coupon    Coupon data or code.
	 * @param boolean          $formatted Formats the saving amount.
	 *
	 * @return string The coupon in HTML.
	 */
	public function coupon_html( $coupon, $formatted = true ) {
		if ( is_string( $coupon ) ) {
			$coupon = new WC_Coupon( $coupon );
		}

		$amount = $this->get_cart_instance()->get_coupon_discount_amount( $coupon->get_code(), $this->get_cart_instance()->display_cart_ex_tax );

		if ( $formatted ) {
			$savings = html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ) );
		} else {
			$savings = (float) cocart_prepare_money_response( $amount );
		}

		$discount_amount_html = '-' . $savings;

		if ( $coupon->get_free_shipping() && empty( $amount ) ) {
			$discount_amount_html = __( 'Free shipping coupon', 'cart-rest-api-for-woocommerce' );
		}

		$discount_amount_html = apply_filters( 'cocart_coupon_discount_amount_html', $discount_amount_html, $coupon );

		return $discount_amount_html;
	} // END coupon_html()

	/**
	 * Get the fee value.
	 *
	 * @access public
	 *
	 * @param object $cart Cart instance.
	 * @param object $fee  Fee data.
	 *
	 * @return string Returns the fee value.
	 */
	public function fee_html( $cart, $fee ) {
		$cart_totals_fee_html = $cart->display_prices_including_tax() ? wc_price( $fee->total + $fee->tax ) : wc_price( $fee->total );

		return apply_filters( 'cocart_cart_totals_fee_html', $cart_totals_fee_html, $fee );
	} // END fee_html()

	/**
	 * Filters additional requested data.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return $request
	 */
	public function filter_request_data( $request ) {
		/**
		 * Filters additional requested data.
		 *
		 * @since 3.0.0 Introduced.
		 */
		return apply_filters( 'cocart_filter_request_data', $request );
	} // END filter_request_data()

	/**
	 * Get the main product slug even if the product type is a variation.
	 *
	 * @access public
	 *
	 * @param WC_Product $_product Product object.
	 *
	 * @return string The product slug.
	 */
	public function get_product_slug( $_product ) {
		$product_type = $_product->get_type();

		if ( 'variation' === $product_type ) {
			$product = wc_get_product( $_product->get_parent_id() );

			$product_slug = $product->get_slug();
		} else {
			$product_slug = $_product->get_slug();
		}

		return $product_slug;
	} // END get_product_slug()

	/**
	 * Get a single item from the cart and present the data required.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Added new parameter `$request` (REST API request) to allow more arguments to be passed.
	 *
	 * @deprecated 4.0.0 No longer use `$show_thumb` as parameter.
	 *
	 * @param WC_Product      $_product     Product object.
	 * @param array           $cart_item    The item in the cart containing the default cart item data.
	 * @param WP_REST_Request $request      Full details about the request.
	 * @param boolean         $removed_item Determines if the item in the cart is removed.
	 *
	 * @return array $item Full details of the item in the cart and it's purchase limits.
	 */
	public function get_item( $_product, $cart_item = array(), $request = array(), $removed_item = false ) {
		$item_key   = $cart_item['key'];
		$quantity   = apply_filters( 'cocart_cart_item_quantity', $cart_item['quantity'], $item_key, $cart_item, $request );
		$dimensions = $_product->get_dimensions( false );
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		// Item container.
		$item = array();

		$item['item_key'] = $item_key;
		$item['id']       = $_product->get_id();
		$item['name']     = apply_filters( 'cocart_cart_item_name', $_product->get_name(), $_product, $cart_item, $item_key );
		$item['title']    = apply_filters( 'cocart_cart_item_title', $_product->get_title(), $_product, $cart_item, $item_key );
		$item['price']    = apply_filters( 'cocart_cart_item_price', $this->get_cart_instance()->get_product_price( $_product ), $cart_item, $item_key, $request );
		$item['quantity'] = array(
			'value'        => (float) $quantity,
			'min_purchase' => $_product->get_min_purchase_quantity(),
			'max_purchase' => $_product->get_max_purchase_quantity(),
		);
		$item['totals']   = array(
			'subtotal'     => apply_filters( 'cocart_cart_item_subtotal', $this->get_cart_instance()->get_product_subtotal( $_product, $quantity ), $cart_item, $item_key, $request ),
			'subtotal_tax' => apply_filters( 'cocart_cart_item_subtotal_tax', $cart_item['line_subtotal_tax'], $cart_item, $item_key, $request ),
			'total'        => apply_filters( 'cocart_cart_item_total', $cart_item['line_total'], $cart_item, $item_key, $request ),
			'tax'          => apply_filters( 'cocart_cart_item_tax', $cart_item['line_tax'], $cart_item, $item_key, $request ),
		);
		$item['slug']     = $this->get_product_slug( $_product );
		$item['meta']     = array(
			'product_type' => $_product->get_type(),
			'sku'          => $_product->get_sku(),
			'dimensions'   => ! empty( $dimensions ) ? array(
				'length' => $dimensions['length'],
				'width'  => $dimensions['width'],
				'height' => $dimensions['height'],
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			) : array(),
			'weight'       => wc_get_weight( (float) $_product->get_weight() * (int) $cart_item['quantity'], get_option( 'woocommerce_weight_unit' ) ),
			'variation'    => isset( $cart_item['variation'] ) ? cocart_format_variation_data( $cart_item['variation'], $_product ) : array(),
		);

		// Backorder notification.
		$item['backorders'] = $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ? wp_kses_post( apply_filters( 'cocart_cart_item_backorder_notification', esc_html__( 'Available on backorder', 'cart-rest-api-for-woocommerce' ), $_product->get_id() ) ) : '';

		// Prepares the remaining cart item data.
		$cart_item = $this->prepare_item( $cart_item );

		/**
		 * Filter allows you to alter the remaining cart item data.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @param array  $cart_item Remaining cart item data.
		 * @param string $item_key  Item key of the item in the cart.
		 */
		$cart_item_data = apply_filters( 'cocart_cart_item_data', $cart_item, $item_key );

		// Returns remaining cart item data.
		$item['cart_item_data'] = ! empty( $cart_item ) ? $cart_item_data : array();

		// If thumbnail is requested then add it to each item in cart.
		$item['featured_image'] = $show_thumb ? $this->get_item_thumbnail( $_product, $cart_item, $item_key, $removed_item ) : '';

		}

		return $item;
	} // END get_item()

	/**
	 * Gets the cart items.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Added new parameter `$request` (REST API request) to allow more arguments to be passed.
	 *
	 * @deprecated 4.0.0 No longer use `$show_thumb` as parameter.
	 *
	 * @param array           $cart_contents The cart contents passed.
	 * @param WP_REST_Request $request       Full details about the request.
	 *
	 * @return array $items Returns all items in the cart.
	 */
	public function get_items( $cart_contents = array(), $request = array() ) {
		$items = array();

		foreach ( $cart_contents as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data']          = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
				$items[ $item_key ]['data'] = $cart_item['data']; // Internal use only!
			}

			/**
			 * Filter allows you to alter the item product data returned.
			 *
			 * @since 3.0.0 Introduced.
			 * @since 4.0.0 Added `$request` (REST API request) as parameter.
			 *
			 * @param WC_Product      $_product  Product object.
			 * @param array           $cart_item The item in the cart containing the default cart item data.
			 * @param string          $item_key  The item key currently looped.
			 * @param WP_REST_Request $request   Full details about the request.
			 */
			$_product = apply_filters( 'cocart_item_product', $cart_item['data'], $cart_item, $item_key, $request );

			if ( ! $_product || ! $_product->exists() || 'trash' === $_product->get_status() ) {
				$this->get_cart_instance()->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.
				wc_add_notice( __( 'An item which is no longer available was removed from your cart.', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			// If product is no longer purchasable then don't return it and notify customer.
			if ( ! $_product->is_purchasable() ) {
				/* translators: %s: product name */
				$message = sprintf( __( '%s has been removed from your cart because it can no longer be purchased.', 'cart-rest-api-for-woocommerce' ), $_product->get_name() );

				/**
				 * Filter message about item removed from the cart.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param string     $message  Message.
				 * @param WC_Product $_product Product object.
				 */
				$message = apply_filters( 'cocart_cart_item_removed_message', $message, $_product );

				$this->get_cart_instance()->set_quantity( $item_key, 0 ); // Sets item quantity to zero so it's removed from the cart.

				wc_add_notice( $message, 'error' );
			} else {
				$items[ $item_key ] = $this->get_item( $_product, $cart_item, $request );

				/**
				 * Filter allows additional data to be returned for a specific item in cart.
				 *
				 * @since 2.1.0 Introduced.
				 * @since 4.0.0 Added `$request` (REST API request) as parameter.
				 *
				 * @param array           $items     Array of items in the cart.
				 * @param string          $item_key  The item key currently looped.
				 * @param array           $cart_item The item in the cart containing the default cart item data.
				 * @param WC_Product      $_product  Product object.
				 * @param WP_REST_Request $request   Full details about the request.
				 */
				$items = apply_filters( 'cocart_cart_items', $items, $item_key, $cart_item, $_product, $request );
			}
		}

		return $items;
	} // END get_items()

	/**
	 * Gets the cart removed items.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Added new parameter `$request` (REST API request) to allow more arguments to be passed.
	 *
	 * @deprecated 4.0.0 No longer use `$show_thumb` as parameter.
	 *
	 * @param array           $removed_items The removed cart contents passed.
	 * @param WP_REST_Request $request       Full details about the request.
	 *
	 * @return array $items Returns all removed items in the cart.
	 */
	public function get_removed_items( $removed_items = array(), $request = array() ) {
		$items = array();

		foreach ( $removed_items as $item_key => $cart_item ) {
			// If product data is missing then get product data and apply.
			if ( ! isset( $cart_item['data'] ) ) {
				$cart_item['data'] = wc_get_product( $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'] );
			}

			$_product = $cart_item['data'];

			// If the product no longer exists then remove it from the cart completely.
			if ( ! $_product || ! $_product->exists() || 'trash' === $_product->get_status() ) {
				unset( $this->get_cart_instance()->removed_cart_contents[ $item_key ] );
				unset( $removed_items[ $item_key ] );
				continue;
			}

			$items[ $item_key ] = $this->get_item( $_product, $cart_item, $request, true );

			// Move the quantity value to it's parent.
			$items[ $item_key ]['quantity'] = $items[ $item_key ]['quantity']['value'];
		}

		return $items;
	} // END get_removed_items()

	/**
	 * Returns cross sells based on the items in the cart.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Prices now return as monetary values.
	 * @since 4.0.0 Prices now return default values and uses filters instead.
	 *              Added new parameter `$request` (REST API request) to allow more arguments to be passed.
	 *
	 * @return array $cross_sells Returns cross sells.
	 */
	public function get_cross_sells( $request = array() ) {
		// Get visible cross sells then sort them at random.
		$get_cross_sells = array_filter( array_map( 'wc_get_product', $this->get_cart_instance()->get_cross_sells() ), 'wc_products_array_filter_visible' );

		// Handle orderby and limit results.
		$orderby         = apply_filters( 'cocart_cross_sells_orderby', 'rand' );
		$order           = apply_filters( 'cocart_cross_sells_order', 'desc' );
		$get_cross_sells = wc_products_array_orderby( $get_cross_sells, $orderby, $order );
		$limit           = apply_filters( 'cocart_cross_sells_total', 3 );
		$get_cross_sells = $limit > 0 ? array_slice( $get_cross_sells, 0, $limit ) : $get_cross_sells;

		$cross_sells = array();

		foreach ( $get_cross_sells as $cross_sell ) {
			$id = $cross_sell->get_id();

			$thumbnail_id  = apply_filters( 'cocart_cross_sell_item_thumbnail', $cross_sell->get_image_id() );
			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_cross_sell_item_thumbnail_size', 'woocommerce_thumbnail' ) );
			$thumbnail_src = apply_filters( 'cocart_item_thumbnail_src', $thumbnail_src[0] );

			$cross_sells[] = array(
				'id'             => $id,
				'name'           => $cross_sell->get_name(),
				'title'          => $cross_sell->get_title(),
				'slug'           => $this->get_product_slug( $cross_sell ),
				'price'          => apply_filters( 'cocart_cart_cross_item_price', $cross_sell->get_price(), $request ),
				'regular_price'  => apply_filters( 'cocart_cart_cross_item_regular_price', $cross_sell->get_regular_price(), $request ),
				'sale_price'     => apply_filters( 'cocart_cart_cross_item_sale_price', $cross_sell->get_sale_price(), $request ),
				'image'          => esc_url( $thumbnail_src ),
				'average_rating' => $cross_sell->get_average_rating() > 0 ? sprintf(
					/* translators: %s: average rating */
					esc_html__( 'Rated %s out of 5', 'cart-rest-api-for-woocommerce' ),
					esc_html( $cross_sell->get_average_rating() )
				) : '',
				'on_sale'        => $cross_sell->is_on_sale() ? true : false,
				'type'           => $cross_sell->get_type(),
			);
		}

		/**
		 * Filters the cross sell items.
		 *
		 * @since 3.0.0 Introduced.
		 * @since 4.0.0 Added parameter `$request` (REST API request).
		 *
		 * @param WP_REST_Request $request Full details about the request.
		 */
		$cross_sells = apply_filters( 'cocart_cross_sells', $cross_sells, $request );

		return $cross_sells;
	} // END get_cross_sells()

	/**
	 * Returns shipping details.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @return array Shipping details.
	 */
	public function get_shipping_details() {
		if ( ! wc_shipping_enabled() || 0 === wc_get_shipping_method_count( true ) ) {
			return array();
		}

		// Get shipping packages.
		$packages = WC()->shipping->get_packages();

		$details = array(
			'total_packages'          => count( (array) $packages ),
			'show_package_details'    => count( (array) $packages ) > 1,
			'has_calculated_shipping' => WC()->customer->has_calculated_shipping(),
			'packages'                => array(),
		);

		$package_key = 1;

		foreach ( $packages as $i => $package ) {
			$chosen_method = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
			$product_names = array();

			if ( count( (array) $packages ) > 1 ) {
				foreach ( $package['contents'] as $item_id => $values ) {
					$product_names[ $item_id ] = $values['data']->get_name() . ' x' . $values['quantity'];
				}

				$product_names = apply_filters( 'cocart_shipping_package_details_array', $product_names, $package );
			}

			if ( 0 === $i ) {
				$package_key = 'default'; // Identifies the default package.
			}

			// Check that there are rates available for the package.
			if ( count( (array) $package['rates'] ) > 0 ) {
				$details['packages'][ $package_key ] = array(
					/* translators: %d: shipping package number */
					'package_name'          => apply_filters( 'cocart_shipping_package_name', ( ( $i + 1 ) > 1 ) ? sprintf( _x( 'Shipping #%d', 'shipping packages', 'cart-rest-api-for-woocommerce' ), ( $i + 1 ) ) : _x( 'Shipping', 'shipping packages', 'cart-rest-api-for-woocommerce' ), $i, $package ),
					'rates'                 => array(),
					'package_details'       => implode( ', ', $product_names ),
					'index'                 => $i, // Shipping package number.
					'chosen_method'         => $chosen_method,
					'formatted_destination' => WC()->countries->get_formatted_address( $package['destination'], ', ' ),
				);

				$rates = array();

				// Return each rate.
				foreach ( $package['rates'] as $key => $method ) {
					$meta_data = $this->clean_meta_data( $method, 'shipping' );

					$rates[ $key ] = array(
						'key'           => $key,
						'method_id'     => $method->get_method_id(),
						'instance_id'   => $method->instance_id,
						'label'         => $method->get_label(),
						'cost'          => (float) cocart_prepare_money_response( $method->cost ),
						'html'          => html_entity_decode( wp_strip_all_tags( wc_cart_totals_shipping_method_label( $method ) ) ),
						'taxes'         => '',
						'chosen_method' => ( $chosen_method === $key ),
						'meta_data'     => $meta_data,
					);

					foreach ( $method->taxes as $shipping_cost => $tax_cost ) {
						$rates[ $key ]['taxes'] = (float) cocart_prepare_money_response( $tax_cost );
					}
				}

				$details['packages'][ $package_key ]['rates'] = $rates;
			}

			$package_key++; // Update package key for next inline if any.
		}

		return $details;
	} // END get_shipping_details()

	/**
	 * Adds item to cart internally rather than WC.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param int        $product_id     Contains the id of the product to add to the cart.
	 * @param int        $quantity       Contains the quantity of the item to add.
	 * @param int        $variation_id   ID of the variation being added to the cart.
	 * @param array      $variation      Attribute values.
	 * @param array      $cart_item_data Extra cart item data we want to pass into the item.
	 * @param WC_Product $product_data   Product object.
	 *
	 * @return string|boolean $item_key Cart item key or false if error.
	 */
	public function add_cart_item( int $product_id, int $quantity, $variation_id, array $variation, array $cart_item_data, WC_Product $product_data ) {
		try {
			// Generate a ID based on product ID, variation ID, variation data, and other cart item data.
			$item_key = $this->get_cart_instance()->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );

			// Add item after merging with $cart_item_data - hook to allow plugins to modify cart item.
			$this->get_cart_instance()->cart_contents[ $item_key ] = apply_filters(
				'cocart_add_cart_item',
				array_merge(
					$cart_item_data,
					array(
						'key'          => $item_key,
						'product_id'   => $product_id,
						'variation_id' => $variation_id,
						'variation'    => $variation,
						'quantity'     => $quantity,
						'data'         => $product_data,
						'data_hash'    => wc_get_cart_item_data_hash( $product_data ),
					)
				),
				$item_key
			);

			$this->get_cart_instance()->cart_contents = apply_filters( 'cocart_cart_contents_changed', $this->get_cart_instance()->cart_contents );

			do_action( 'cocart_add_to_cart', $item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data );

			return $item_key;
		} catch ( CoCart_Data_Exception $e ) {
			return $this->get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END add_cart_item()

	/**
	 * Returns the customers details from fields.
	 *
	 * @access protected
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.1.0
	 *
	 * @param string      $fields   The customer fields to return.
	 * @param WC_Customer $customer The customer object or ID.
	 *
	 * @return array Returns the customer details based on the field requested.
	 */
	protected function get_customer_fields( $fields = 'billing', $customer = '' ) {
		// If no customer is set then get customer from cart.
		if ( empty( $customer ) ) {
			$customer = $this->get_cart_instance()->get_customer();
		}

		/**
		 * We get the checkout fields so we return the fields the store uses during checkout.
		 * This is so we ONLY return the customers information for those fields used.
		 * These fields could be changed either via filter, another plugin or
		 * based on the conditions of the customers location or cart contents.
		 */
		$checkout_fields = WC()->checkout->get_checkout_fields( $fields );

		$results = array();

		/**
		 * We go through each field and check that we can return it's data as set by default.
		 * If we can't get the data we rely on getting customer data via a filter for that field.
		 * Any fields that can not return information will be empty.
		 */
		foreach ( $checkout_fields as $key => $value ) {
			$field_name = 'get_' . $key; // Name of the default field function. e.g. "get_billing_first_name".

			$results[ $key ] = method_exists( $customer, $field_name ) ? $customer->$field_name() : apply_filters( 'cocart_get_customer_' . $key, '' );
		}

		return $results;
	} // END get_customer_fields()

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
	 * @since      3.0.3 Introduced.
	 * @deprecated 4.0.0 No longer used. `return_cart_contents()` function has been improved.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array Returns the default cart response.
	 */
	protected function get_cart_template( $request = array() ) {
		cocart_deprecated_function( __FUNCTION__, '4.0' );

		$fields = ! empty( $request['fields'] ) ? $request['fields'] : '';

		if ( ! empty( $fields ) ) {
			return self::get_cart_template_limited( $request );
		}

		// Other Requested conditions.
		$show_thumb = ! empty( $request['thumb'] ) ? $request['thumb'] : false;

		return array(
			'cart_hash'      => ! empty( $this->get_cart_instance()->get_cart_hash() ) ? $this->get_cart_instance()->get_cart_hash() : __( 'No items in cart so no hash', 'cart-rest-api-for-woocommerce' ),
			'cart_key'       => $this->get_cart_key( $request ),
			'currency'       => cocart_get_store_currency(),
			'customer'       => array(
				'billing_address'  => $this->get_customer_fields( 'billing' ),
				'shipping_address' => $this->get_customer_fields( 'shipping' ),
			),
			'items'          => array(),
			'item_count'     => $this->get_cart_instance()->get_cart_contents_count(),
			'items_weight'   => wc_get_weight( (float) $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) ),
			'coupons'        => array(),
			'needs_payment'  => $this->get_cart_instance()->needs_payment(),
			'needs_shipping' => $this->get_cart_instance()->needs_shipping(),
			'shipping'       => $this->get_shipping_details(),
			'fees'           => $this->get_fees( $this->get_cart_instance() ),
			'taxes'          => array(),
			'totals'         => array(
				'subtotal'       => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal() ),
				'subtotal_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax() ),
				'fee_total'      => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total() ),
				'fee_tax'        => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax() ),
				'discount_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total() ),
				'discount_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax() ),
				'shipping_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total() ),
				'shipping_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax() ),
				'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total() ),
				'total_tax'      => cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax() ),
			),
			'removed_items'  => $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $request ),
			'cross_sells'    => $this->get_cross_sells(),
			'notices'        => $this->maybe_return_notices(),
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
	 * @since      3.1.0 Introduced.
	 * @deprecated 4.0.0 No longer used. `return_cart_contents()` function has been improved.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array $template Returns requested cart response.
	 */
	protected function get_cart_template_limited( $request = array() ) {
		cocart_deprecated_function( __FUNCTION__, '4.0' );

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
					$template['cart_key'] = $this->get_cart_key( $request );
					break;
				case 'currency':
					$template['currency'] = cocart_get_store_currency();
					break;
				case 'customer':
					if ( ! empty( $child_field ) ) {
						if ( 'billing_address' === $child_field ) {
							$template['customer']['billing_address'] = $this->get_customer_fields( 'billing' );
						}
						if ( 'shipping_address' === $child_field ) {
							$template['customer']['shipping_address'] = $this->get_customer_fields( 'shipping' );
						}
					} else {
						$template['customer'] = array(
							'billing_address'  => $this->get_customer_fields( 'billing' ),
							'shipping_address' => $this->get_customer_fields( 'shipping' ),
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
					$template['items_weight'] = wc_get_weight( (float) $this->get_cart_instance()->get_cart_contents_weight(), get_option( 'woocommerce_weight_unit' ) );
					break;
				case 'coupons':
					$template['coupons'] = array();
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
					$template['fees'] = $this->get_fees( $this->get_cart_instance() );
					break;
				case 'taxes':
					$template['taxes'] = array();
					break;
				case 'totals':
					if ( ! empty( $child_field ) ) {
						$child_field = explode( '-', $child_field );

						foreach ( $child_field as $total ) {
							if ( 'subtotal' === $total ) {
								$template['totals']['subtotal'] = cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal() );
							}
							if ( 'subtotal_tax' === $total ) {
								$template['totals']['subtotal_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax() );
							}
							if ( 'fee_total' === $total ) {
								$template['totals']['fee_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total() );
							}
							if ( 'fee_tax' === $total ) {
								$template['totals']['fee_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax() );
							}
							if ( 'discount_total' === $total ) {
								$template['totals']['discount_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total() );
							}
							if ( 'discount_tax' === $total ) {
								$template['totals']['discount_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax() );
							}
							if ( 'shipping_total' === $total ) {
								$template['totals']['shipping_total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total() );
							}
							if ( 'shipping_tax' === $total ) {
								$template['totals']['shipping_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax() );
							}
							if ( 'total' === $total ) {
								$template['totals']['total'] = cocart_prepare_money_response( $this->get_cart_instance()->get_total() );
							}
							if ( 'total_tax' === $total ) {
								$template['totals']['total_tax'] = cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax() );
							}
						}
					} else {
						$template['totals'] = array(
							'subtotal'       => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal() ),
							'subtotal_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_subtotal_tax() ),
							'fee_total'      => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_total() ),
							'fee_tax'        => cocart_prepare_money_response( $this->get_cart_instance()->get_fee_tax() ),
							'discount_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_total() ),
							'discount_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_discount_tax() ),
							'shipping_total' => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_total() ),
							'shipping_tax'   => cocart_prepare_money_response( $this->get_cart_instance()->get_shipping_tax() ),
							'total'          => cocart_prepare_money_response( $this->get_cart_instance()->get_total() ),
							'total_tax'      => cocart_prepare_money_response( $this->get_cart_instance()->get_total_tax() ),
						);
					}
					break;
				case 'removed_items':
					$template['removed_items'] = $this->get_removed_items( $this->get_cart_instance()->get_removed_cart_contents(), $request );
					break;
				case 'cross_sells':
					$template['cross_sells'] = $this->get_cross_sells();
					break;
				case 'notices':
					$template['notices'] = $this->maybe_return_notices();
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
		$schema = parent::get_public_item_schema();

		/**
		 * This filter is now deprecated and is replaced with `cocart_cart_items_schema`.
		 *
		 * @deprecated 3.1.0 Use `cocart_cart_items_schema` filter instead.
		 *
		 * @see cocart_cart_items_schema
		 */
		cocart_deprecated_filter( 'cocart_cart_schema', array( $schema['properties'] ), '3.1.0', 'cocart_cart_items_schema', __( 'Changed for the purpose of not overriding default properties.', 'cart-rest-api-for-woocommerce' ) );

		/**
		 * Extend the cart schema properties for items.
		 *
		 * This filter allows you to extend the cart schema properties for items without removing any default properties.
		 *
		 * @since 3.1.0 Introduced.
		 */
		$schema['properties']['items']['items']['properties'] += apply_filters( 'cocart_cart_items_schema', array() );

		return $schema;
	} // END get_public_item_schema()

} // END class
