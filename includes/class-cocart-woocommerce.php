<?php
/**
 * Handles tweaks made to WooCommerce to support CoCart.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Classes
 * @since    2.1.2
 * @version  3.0.3
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
		 * @access  public
		 * @since   2.1.2
		 * @version 3.0.3
		 */
		public function __construct() {
			// Removes WooCommerce filter that validates the quantity value to be an integer.
			remove_filter( 'woocommerce_stock_amount', 'intval' );

			// Validates the quantity value to be a float.
			add_filter( 'woocommerce_stock_amount', 'floatval' );

			// Force WooCommerce to accept CoCart requests when authenticating.
			add_filter( 'woocommerce_rest_is_request_to_rest_api', array( $this, 'allow_cocart_requests_wc' ) );

			// Loads cart from session.
			add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_from_session' ), 0 );

			// Delete user data.
			add_action( 'delete_user', array( $this, 'delete_user_data' ) );
		}

		/**
		 * Force WooCommerce to accept CoCart API requests when authenticating.
		 *
		 * @access  public
		 * @static
		 * @since   2.0.5
		 * @version 2.6.0
		 * @param   bool $request Current status of allowing WooCommerce request.
		 * @return  bool true|$request Status after checking if CoCart is allowed.
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
		 * @version 3.0.0
		 */
		public static function load_cart_from_session() {
			// Return nothing if WP-GraphQL is requested.
			if ( function_exists( 'is_graphql_http_request' ) && is_graphql_http_request() ) {
				return;
			}

			// Check the CoCart session handler is used but is NOT a CoCart REST API request.
			if ( WC()->session instanceof CoCart_Session_Handler && ! CoCart_Authentication::is_rest_api_request() ) {
				return;
			}

			$cookie = WC()->session->get_session_cookie();

			$cart_key = '';

			// If cookie exists then return cart key from it.
			if ( $cookie ) {
				$cart_key = $cookie[0];
			}

			// Check if we requested to load a specific cart.
			if ( isset( $_REQUEST['cart_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
				$cart_key = trim( esc_html( wp_unslash( $_REQUEST['cart_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			}

			// Check if the user is logged in.
			if ( is_user_logged_in() ) {
				$customer_id = strval( get_current_user_id() );

				// Compare the customer ID with the requested cart key. If they match then return error message.
				if ( isset( $_REQUEST['cart_key'] ) && $customer_id === $_REQUEST['cart_key'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$error = new WP_Error( 'cocart_already_authenticating_user', __( 'You are already authenticating as the customer. Cannot set cart key as the user.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
					wp_send_json_error( $error, 403 );
					exit;
				}
			} else {
				$user = get_user_by( 'id', $cart_key );

				// If the user exists then return error message.
				if ( ! empty( $user ) ) {
					$error = new WP_Error( 'cocart_must_authenticate_user', __( 'Must authenticate customer as the cart key provided is a registered customer.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
					wp_send_json_error( $error, 403 );
					exit;
				}
			}

			// Get requested cart.
			$cart = WC()->session->get_cart( $cart_key );

			// Get current cart contents.
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
		 * When a user is deleted in WordPress, delete corresponding CoCart data.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  int $user_id User ID being deleted.
		 */
		public function delete_user_data( $user_id ) {
			global $wpdb;

			// Clean up cart in session.
			$wpdb->delete(
				$wpdb->prefix . 'cocart_carts',
				array(
					'cart_key' => $user_id,
				)
			);
		} // END delete_user_data()

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
