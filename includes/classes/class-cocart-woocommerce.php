<?php
/**
 * Class: CoCart_WooCommerce
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.2 Introduced.
 * @version 4.8.3
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

		// Disable WooCommerce persistent cart.
		add_filter( 'woocommerce_persistent_cart_enabled', '__return_false' );

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
