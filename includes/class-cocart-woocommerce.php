<?php
/**
 * Handles tweaks made to WooCommerce to support CoCart.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart\WooCommerce
 * @since    2.1.2
 * @version  2.8.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_WooCommerce' ) ) {

	class CoCart_WooCommerce {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			// Removes WooCommerce filter that validates the quantity value to be an integer.
			remove_filter( 'woocommerce_stock_amount', 'intval' );

			// Validates the quantity value to be a float.
			add_filter( 'woocommerce_stock_amount', 'floatval' );

			// Overrides the session handler used for the web.
			add_filter( 'woocommerce_session_handler', array( $this, 'cocart_session_handler' ) );

			// Force WooCommerce to accept CoCart requests when authenticating.
			add_filter( 'woocommerce_rest_is_request_to_rest_api', array( $this, 'allow_cocart_requests_wc' ) );

			// Loads cart from session.
			add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_from_session' ), 0 );
		}

		/**
		 * Returns CoCart session handler class name.
		 *
		 * @access  public
		 * @since   2.1.2
		 * @version 2.6.0
		 * @param   string WooCommerce Session Handler
		 * @return  string
		 */
		public static function cocart_session_handler( $handler ) {
			if ( ! class_exists( 'WC_Session' ) ) {
				return $handler;
			}

			if ( ! defined( 'DOING_AJAX' ) || ! defined( 'DOING_CRON' ) || ! CoCart_Helpers::is_rest_api_request() ) {
				$handler = 'CoCart_Session_Handler';
			}

			return $handler;
		} // END cocart_session_handler()

		/**
		 * Force WooCommerce to accept CoCart API requests when authenticating.
		 *
		 * @access  public
		 * @static
		 * @since   2.0.5
		 * @version 2.6.0
		 * @param   bool $request
		 * @return  bool true|$request
		 */
		public static function allow_cocart_requests_wc( $request ) {
			if ( empty( $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			$rest_prefix = trailingslashit( rest_get_url_prefix() );
			$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) );

			// Check if the request is to the CoCart API endpoints.
			$cocart = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) );

			if ( $cocart ) {
				return true;
			}

			return $request;
		} // END allow_cocart_requests_wc()

		/**
		 * Loads a specific cart into session and merge cart contents 
		 * with a logged in customer if cart contents exist.
		 * 
		 * Triggered when "woocommerce_load_cart_from_session" is called 
		 * to make sure the cart from session is loaded in time.
		 *
		 * @access  public
		 * @static
		 * @since   2.1.0
		 * @version 2.9.1
		 */
		public static function load_cart_from_session() {
			if ( ! WC()->session instanceof CoCart_Session_Handler && ! CoCart_Helpers::is_rest_api_request() ) {
				return;
			}

			$customer_id = strval( get_current_user_id() );

			$cookie = WC()->session->get_session_cookie();

			// If cookie exists then return customer ID from it.
			if ( $cookie ) {
				$customer_id = $cookie[0];
			}

			// Check if we requested to load a specific cart.
			if ( isset( $_REQUEST['cart_key'] ) || isset( $_REQUEST['id'] ) ) {
				$cart_id = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : $_REQUEST['id'];

				// Set customer ID in session.
				$customer_id = $cart_id;
			}

			// Get cart for customer.
			$cart = WC()->session->get_cart( $customer_id );

			$cart_contents = WC()->session->get( 'cart', null );

			// Merge saved cart with current cart.
			if ( ! empty( $cart_contents ) && strval( get_current_user_id() ) > 0 ) {
				$saved_cart    = self::get_saved_cart();
				$cart_contents = array_merge( $saved_cart, $cart_contents );
			}

			// Set cart for customer if not empty.
			if ( ! empty( $cart ) ) {
				WC()->session->set( 'cart', $cart_contents );
				WC()->session->set( 'cart_totals', maybe_unserialize( $cart['cart_totals'] ) );
				WC()->session->set( 'applied_coupons', maybe_unserialize( $cart['applied_coupons'] ) );
				WC()->session->set( 'coupon_discount_totals', maybe_unserialize( $cart['coupon_discount_totals'] ) );
				WC()->session->set( 'coupon_discount_tax_totals', maybe_unserialize( $cart['coupon_discount_tax_totals'] ) );
				WC()->session->set( 'removed_cart_contents', maybe_unserialize( $cart['removed_cart_contents'] ) );

				if ( ! empty( $cart['chosen_shipping_methods'] ) ) {
					WC()->session->set( 'chosen_shipping_methods', maybe_unserialize( $cart['chosen_shipping_methods'] ) );
				}

				if ( ! empty( $cart['cart_fees'] ) ) {
					WC()->session->set( 'cart_fees', maybe_unserialize( $cart['cart_fees'] ) );
				}
			}
		} // END load_cart_from_session()

		/**
		 * Get the persistent cart from the database.
		 *
		 * @access private
		 * @static
		 * @since  2.9.1
		 * @return array
		 */
		private static function get_saved_cart() {
			$saved_cart = array();

			if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) {
				$saved_cart_meta = get_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

				if ( isset( $saved_cart_meta['cart'] ) ) {
					$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
				}
			}

			return $saved_cart;
		} // END get_saved_cart()

	} // END class

} // END if class exists.

return new CoCart_WooCommerce();
