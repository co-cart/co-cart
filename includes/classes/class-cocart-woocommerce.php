<?php
/**
 * Class: CoCart_WooCommerce
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.2 Introduced.
 * @version 4.9.5
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

		// Delete user data.
		add_action( 'delete_user', array( $this, 'delete_user_data' ) );
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

		if ( apply_filters( 'woocommerce_persistent_cart_enabled', true ) ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$saved_cart_meta = get_user_meta( get_current_user_id(), '_woocommerce_persistent_cart_' . get_current_blog_id(), true );

			if ( isset( $saved_cart_meta['cart'] ) ) {
				$saved_cart = array_filter( (array) $saved_cart_meta['cart'] );
			}
		}

		return $saved_cart;
	} // END get_saved_cart()
} // END class

return new CoCart_WooCommerce();
