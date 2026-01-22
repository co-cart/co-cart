<?php
/**
 * Class: CoCart_WooCommerce
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.2 Introduced.
 * @version 4.6.2
 * @license GPL-3.0
 */

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
class CoCart_WooCommerce {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @since 2.1.2 Introduced.
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

		// Validate cart session requested.
		add_action( 'woocommerce_load_cart_from_session', array( $this, 'validate_cart_requested' ), 0 );

		// Delete user data.
		add_action( 'delete_user', array( $this, 'delete_user_data' ) );

		// Restore unset default address fields.
		add_filter( 'woocommerce_default_address_fields', array( $this, 'restore_unset_default_address_fields' ), 99 );

		// Override address fields.
		add_filter( 'woocommerce_billing_fields', array( $this, 'override_address_fields' ), 99 );
	} // END __construct()

	/**
	 * Force WooCommerce to accept CoCart API requests when authenticating.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.0.5 Introduced.
	 * @since 5.0.0 Gets the API namespace set instead of being hardcoded.
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
		if ( ( false !== strpos( $request_uri, $rest_prefix . CoCart::get_api_namespace() . '/' ) ) ) {
			return true;
		}

		return $request;
	} // END allow_cocart_requests_wc()

	/**
	 * Validates the cart requested and warns user if accessing it incorrectly.
	 *
	 * Triggered when "woocommerce_load_cart_from_session" is called
	 * to make sure the cart from session is valid.
	 *
	 * THIS IS FOR REST API USE ONLY!
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 2.8.0 Set chosen shipping methods.
	 * @since 2.9.1 Merge persistent cart with session and check REST is not requesting CoCart.
	 * @since 3.0.0 Added check for WP-GraphQL requests.
	 * @since 4.6.2 Simplified to avoid unnecessary checks.
	 */
	public static function validate_cart_requested() {
		// Return nothing if WP-GraphQL is requested.
		if ( function_exists( 'is_graphql_http_request' ) && is_graphql_http_request() ) {
			return;
		}

		// Return nothing if CoCart REST API is NOT requested.
		if ( ! CoCart::is_rest_api_request() ) {
			return;
		}

		$cart_key = '';

		// Check if we requested to load a specific cart.
		$cart_key = WC()->session->get_requested_cart();

		// Do nothing if the cart key is empty.
		if ( empty( $cart_key ) ) {
			return;
		}

		// Check if the user is logged in.
		if ( is_user_logged_in() ) {
			$customer_id = strval( get_current_user_id() );

			// Compare the customer ID with the requested cart key. If they match then return error message.
			if ( ! empty( $cart_key ) && $customer_id === $cart_key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$error = new WP_Error( 'cocart_already_authenticating_user', __( 'You are already authenticating as the customer. Cannot set cart key as the user.', 'cocart-core' ), array( 'status' => 403 ) );
				wp_send_json_error( $error, 403 );
				exit;
			}
		} else {
			$user = get_user_by( 'id', $cart_key );

			// If the user exists then return error message.
			if ( ! empty( $user ) && apply_filters( 'cocart_secure_registered_users', true ) ) {
				$error = new WP_Error( 'cocart_must_authenticate_user', __( 'Must authenticate customer as the cart key provided is a registered customer.', 'cocart-core' ), array( 'status' => 403 ) );
				wp_send_json_error( $error, 403 );
				exit;
			}
		}

		// Add explicit return for successful validation.
		return true;
	} // END validate_cart_requested()

	/**
	 * When a user is deleted in WordPress, delete corresponding CoCart data.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param int $user_id User ID being deleted.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function delete_user_data( $user_id ) {
		global $wpdb;

		// Clean up cart in session.
		$wpdb->delete( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prefix . 'cocart_carts',
			array(
				'cart_key' => $user_id,
			)
		);
	} // END delete_user_data()

	/**
	 * This ensures that any fields removed by WooCommerce blocks is restored during
	 * a CoCart REST API request but remains hidden.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $fields Default fields.
	 *
	 * @return array $fields Default fields.
	 */
	public function restore_unset_default_address_fields( $fields ) {
		if ( ! CoCart::is_rest_api_request() ) {
			return $fields;
		}

		$fields['company'] = array(
			'label'        => __( 'Company name', 'cocart-core' ),
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'organization',
			'priority'     => 30,
			'required'     => 'hidden',
		);

		return $fields;
	} // END restore_unset_default_address_fields()

	/**
	 * This ensures that specific fields are not removed during a CoCart REST API request.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param array $address_fields Address fields.
	 *
	 * @return array $address_fields Address fields.
	 */
	public function override_address_fields( $address_fields ) {
		if ( ! CoCart::is_rest_api_request() ) {
			return $address_fields;
		}

		if ( ! in_array( 'billing_phone', $address_fields, true ) ) {
			$address_fields['billing_phone'] = array(
				'label'        => __( 'Phone', 'cocart-core' ),
				'required'     => 'hidden',
				'type'         => 'tel',
				'class'        => array( 'form-row-wide' ),
				'validate'     => array( 'phone' ),
				'autocomplete' => 'tel',
				'priority'     => 100,
			);
		}

		return $address_fields;
	} // END override_address_fields()
} // END class

return new CoCart_WooCommerce();
