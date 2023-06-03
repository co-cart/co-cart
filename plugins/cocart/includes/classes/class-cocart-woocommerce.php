<?php
/**
 * Class: CoCart\WooCommerce.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.2 Introduced.
 * @version 4.0.0
 */

namespace CoCart;

use CoCart\RestApi\Authentication;
use CoCart\Session\Handler;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Tweaks.
 *
 * This class handles tweaks made to WooCommerce to support CoCart.
 *
 * @since 2.1.2 Introduced.
 */
class WooCommerce {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @since 2.1.2 Introduced.
	 * @since 4.0.0 Declared support for High-Performance Order Storage.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
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

		// Declares support for High-Performance Order Storage.
		add_action( 'before_woocommerce_init', function() {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', COCART_FILE, true );
			}
		} );
	} // END __construct()

	/**
	 * Force WooCommerce to accept CoCart API requests when authenticating.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.0.5 Introduced.
	 * @version 2.6.0
	 *
	 * @param bool $request Current status of allowing WooCommerce request.
	 *
	 * @return bool true|$request Status after checking if CoCart is allowed.
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
	 * THIS IS FOR REST API USE ONLY!
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.1.0 Introduced.
	 * @version 4.0.0
	 */
	public static function load_cart_from_session() {
		// Return nothing if WP-GraphQL is requested.
		if ( function_exists( 'is_graphql_http_request' ) && is_graphql_http_request() ) {
			return;
		}

		// Check the CoCart session handler is used but is NOT a CoCart REST API request.
		if ( ! WC()->session instanceof Handler || WC()->session instanceof Handler && ! Authentication::is_rest_api_request() ) {
			return;
		}

		$cart_key = WC()->session->get_requested_cart();

		// Check if the user is logged in.
		if ( is_user_logged_in() ) {
			$customer_id = strval( get_current_user_id() );

			// Compare the customer ID with the requested cart key. If they match then return error message.
			if ( isset( $_REQUEST['cart_key'] ) && $customer_id === $_REQUEST['cart_key'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$error = new \WP_Error( 'cocart_already_authenticating_user', __( 'You are already authenticating as the customer. Cannot set cart key as the user.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
				wp_send_json( $error->get_error_message(), 403 );
				exit;
			}
		} else {
			$user = get_user_by( 'id', $cart_key );

			// If the user exists then return error message.
			if ( ! empty( $user ) && apply_filters( 'cocart_secure_registered_users', true ) ) {
				$error = new \WP_Error( 'cocart_must_authenticate_user', __( 'Must authenticate customer as the cart key provided is a registered customer.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 403 ) );
				wp_send_json( $error->get_error_message(), 403 );
				exit;
			}
		}

		// Do nothing if the cart key is empty.
		if ( empty( $cart_key ) ) {
			return;
		}

		// Get requested cart.
		$cart = WC()->session->get_session( $cart_key );

		// Get current cart contents.
		$cart_contents = WC()->session->get( 'cart', array() );

		// Merge requested cart. - ONLY ITEMS, COUPONS AND FEES THAT ARE NOT APPLIED TO THE CART IN SESSION WILL MERGE!!!
		if ( ! empty( $cart_key ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$merge_cart = array();

			$applied_coupons       = WC()->session->get( 'applied_coupons', array() );
			$removed_cart_contents = WC()->session->get( 'removed_cart_contents', array() );
			$cart_fees             = WC()->session->get( 'cart_fees', array() );

			$merge_cart['cart']                  = isset( $cart['cart'] ) ? maybe_unserialize( $cart['cart'] ) : array();
			$merge_cart['applied_coupons']       = isset( $cart['applied_coupons'] ) ? maybe_unserialize( $cart['applied_coupons'] ) : array();
			$merge_cart['applied_coupons']       = array_unique( array_merge( $applied_coupons, $merge_cart['applied_coupons'] ) ); // Merge applied coupons.
			$merge_cart['removed_cart_contents'] = isset( $cart['removed_cart_contents'] ) ? maybe_unserialize( $cart['removed_cart_contents'] ) : array();
			$merge_cart['removed_cart_contents'] = array_merge( $removed_cart_contents, $merge_cart['removed_cart_contents'] ); // Merge removed cart contents.
			$merge_cart['cart_fees']             = isset( $cart['cart_fees'] ) ? maybe_unserialize( $cart['cart_fees'] ) : array();

			// Check cart fees return as an array so not to crash if PHP 8 or higher is used.
			if ( is_array( $merge_cart['cart_fees'] ) ) {
				$merge_cart['cart_fees'] = array_merge( $cart_fees, $merge_cart['cart_fees'] ); // Merge cart fees.
			}

			// Checking if there is cart content to merge.
			if ( ! empty( $merge_cart['cart'] ) ) {
				$cart_contents = array_merge( $merge_cart['cart'], $cart_contents ); // Merge carts.
			}
		}

		// Merge saved cart with current cart.
		if ( ! empty( $cart_contents ) && strval( get_current_user_id() ) > 0 ) {
			$saved_cart    = self::get_saved_cart();
			$cart_contents = array_merge( $saved_cart, $cart_contents );
		}

		// Set cart for customer if not empty.
		if ( ! empty( $cart ) ) {
			WC()->session->set( 'cart', $cart_contents );
			WC()->session->set( 'cart_totals', maybe_unserialize( $cart['cart_totals'] ) );
			WC()->session->set( 'applied_coupons', ! empty( $merge_cart['applied_coupons'] ) ? $merge_cart['applied_coupons'] : maybe_unserialize( $cart['applied_coupons'] ) );
			WC()->session->set( 'coupon_discount_totals', maybe_unserialize( $cart['coupon_discount_totals'] ) );
			WC()->session->set( 'coupon_discount_tax_totals', maybe_unserialize( $cart['coupon_discount_tax_totals'] ) );
			WC()->session->set( 'removed_cart_contents', ! empty( $merge_cart['removed_cart_contents'] ) ? $merge_cart['removed_cart_contents'] : maybe_unserialize( $cart['removed_cart_contents'] ) );

			if ( ! empty( $cart['chosen_shipping_methods'] ) ) {
				WC()->session->set( 'chosen_shipping_methods', maybe_unserialize( $cart['chosen_shipping_methods'] ) );
			}

			if ( ! empty( $cart['cart_fees'] ) ) {
				WC()->session->set( 'cart_fees', ! empty( $merge_cart['cart_fees'] ) ? $merge_cart['cart_fees'] : maybe_unserialize( $cart['cart_fees'] ) );
			}
		}
	} // END load_cart_from_session()

	/**
	 * When a user is deleted in WordPress, delete corresponding CoCart data.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Changed to delete based on `cart_user_id` instead of `cart_key`.
	 *
	 * @param int $user_id User ID being deleted.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function delete_user_data( $user_id ) {
		global $wpdb;

		// Clean up cart in session.
		$wpdb->delete(
			$wpdb->prefix . 'cocart_carts',
			array(
				'cart_user_id' => $user_id,
			)
		);
	} // END delete_user_data()

	/**
	 * Get the persistent cart from the database.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.9.1 Introduced.
	 *
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

return new WooCommerce();
