<?php
/**
 * Handles loading cart from session.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Session
 * @since    2.1.0
 * @version  2.4.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API session class.
 *
 * @package CoCart REST API/Session
 */
class CoCart_API_Session {

	/**
	 * Setup class.
	 *
	 * @access public
	 */
	public function __construct() {
		// Cleans up carts from the database that have expired.
		add_action( 'cocart_cleanup_carts', array( $this, 'cleanup_carts' ) );

		// Merges cart content when loaded to the web.
		add_filter( 'cocart_merge_cart_content', array( $this, 'merge_quantity' ), 10, 3 );

		// Loads a cart in session if still valid.
		add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_action' ), 10 );
	} // END __construct()

	/**
	 * Returns true or false if the cart key is saved in the database.
	 *
	 * @access public
	 * @param  string $cart_key
	 * @return bool
	 */
	public function is_cart_saved( $cart_key ) {
		$handler    = new CoCart_Session_Handler();
		$cart_saved = $handler->get_cart( $cart_key );

		if ( ! empty( $cart_saved ) ) {
			return true;
		}

		return false;
	} // END is_cart_saved()

	/**
	 * Clears all carts from the database.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.0
	 * @version 2.1.2
	 * @global  $wpdb
	 * @return  int $results The number of saved carts.
	 */
	public static function clear_carts() {
		global $wpdb;

		$wpdb->query( "TRUNCATE {$wpdb->prefix}cocart_carts" );

		/**
		 * Clear saved carts.
		 *
		 * @since 2.1.2
		 */
		$results = absint( $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';" ) );
		wp_cache_flush();

		return $results;
	} // END clear_cart()

	/**
	 * Cleans up carts from the database that have expired.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.0
	 * @version 2.4.0
	 */
	public static function cleanup_carts() {
		if ( class_exists( 'CoCart_Session_Handler' ) ) {
			$handler = new CoCart_Session_Handler();
			$handler->cleanup_sessions();
		}
	} // END cleanup_carts()

	/**
	 * Looks at both carts for matching products and merges the item quantities.
	 *
	 * @access public
	 * @param  array $new_cart_content - The merged cart content before altering.
	 * @param  array $load_cart        - The cart we are loading.
	 * @param  array $cart_in_session  - The cart currently in session.
	 * @return array $new_cart_content - The merged cart content after altering.
	 */
	public function merge_quantity( $new_cart_content, $load_cart, $cart_in_session ) {
		return $new_cart_content;
	} // END merge_quantity()

	/**
	 * Load cart action.
	 *
	 * Loads a cart in session if still valid and overrides the current cart. 
	 * Unless specified not to override, the carts will merge the current cart 
	 * and the loaded cart items together.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.0
	 * @version 2.2.1
	 */
	public static function load_cart_action() {
		// If we did not request to load a cart then just return.
		if ( ! isset( $_REQUEST['cocart-load-cart'] ) ) {
			return;
		}

		$cart_key        = trim( wp_unslash( $_REQUEST['cocart-load-cart'] ) );
		$override_cart   = true;  // Override the cart by default.
		$notify_customer = false; // Don't notify the customer by default.
		$redirect        = false; // Don't safely redirect the customer to the cart after loading by default.

		wc_nocache_headers();

		// Check if we are keeping the cart currently set via the web.
		if ( ! empty( $_REQUEST['keep-cart'] ) && is_bool( $_REQUEST['keep-cart'] ) !== true ) {
			$override_cart = false;
		}

		// Check if we are notifying the customer via the web.
		if ( ! empty( $_REQUEST['notify'] ) && is_bool( $_REQUEST['notify'] ) !== true ) {
			$notify_customer = true;
		}

		// Check if we are safely redirecting the customer to the cart via the web.
		if ( ! empty( $_REQUEST['redirect'] ) && is_bool( $_REQUEST['redirect'] ) !== true ) {
			$redirect = true;
		}

		// Get the cart in the database.
		$handler     = new CoCart_Session_Handler();
		$stored_cart = $handler->get_cart( $cart_key );

		if ( empty( $stored_cart ) ) {
			CoCart_Logger::log( sprintf( __( 'Unable to find cart for: %s', 'cart-rest-api-for-woocommerce' ), $cart_key ), 'info' );

			if ( $notify_customer ) {
				wc_add_notice( __( 'Sorry but this cart has expired!', 'cart-rest-api-for-woocommerce' ), 'error' );
			}

			return;
		}

		// Get the cart currently in session if any.
		$cart_in_session = WC()->session->get( 'cart', null );

		$new_cart = array();

		$new_cart['cart']                       = maybe_unserialize( $stored_cart['cart'] );
		$new_cart['applied_coupons']            = maybe_unserialize( $stored_cart['applied_coupons'] );
		$new_cart['coupon_discount_totals']     = maybe_unserialize( $stored_cart['coupon_discount_totals'] );
		$new_cart['coupon_discount_tax_totals'] = maybe_unserialize( $stored_cart['coupon_discount_tax_totals'] );
		$new_cart['removed_cart_contents']      = maybe_unserialize( $stored_cart['removed_cart_contents'] );

		// Check if we are overriding the cart currently in session via the web.
		if ( $override_cart ) {
			// Only clear the cart if it's not already empty.
			if ( ! WC()->cart->is_empty() ) {
				WC()->cart->empty_cart( false );

				do_action( 'cocart_load_cart_override', $new_cart, $stored_cart );
			}
		} else {
			$new_cart_content                       = array_merge( $new_cart['cart'], $cart_in_session );
			$new_cart['cart']                       = apply_filters( 'cocart_merge_cart_content', $new_cart_content, $new_cart['cart'], $cart_in_session );

			$new_cart['applied_coupons']            = array_merge( $new_cart['applied_coupons'], WC()->cart->get_applied_coupons() );
			$new_cart['coupon_discount_totals']     = array_merge( $new_cart['coupon_discount_totals'], WC()->cart->get_coupon_discount_totals() );
			$new_cart['coupon_discount_tax_totals'] = array_merge( $new_cart['coupon_discount_tax_totals'], WC()->cart->get_coupon_discount_tax_totals() );
			$new_cart['removed_cart_contents']      = array_merge( $new_cart['removed_cart_contents'], WC()->cart->get_removed_cart_contents() );

			do_action( 'cocart_load_cart', $new_cart, $stored_cart, $cart_in_session );
		}

		// Sets the php session data for the loaded cart.
		WC()->session->set( 'cart', $new_cart['cart'] );
		WC()->session->set( 'applied_coupons', $new_cart['applied_coupons'] );
		WC()->session->set( 'coupon_discount_totals', $new_cart['coupon_discount_totals'] );
		WC()->session->set( 'coupon_discount_tax_totals', $new_cart['coupon_discount_tax_totals'] );
		WC()->session->set( 'removed_cart_contents', $new_cart['removed_cart_contents'] );

		// If true, notify the customer that there cart has transferred over via the web.
		if ( ! empty( $new_cart ) && $notify_customer ) {
			wc_add_notice( apply_filters( 'cocart_cart_loaded_successful_message', sprintf( __( 'Your 🛒 cart has been transferred over. You may %1$scontinue shopping%3$s or %2$scheckout%3$s.', 'cart-rest-api-for-woocommerce' ), '<a href="' . wc_get_page_permalink( "shop" ) . '">', '<a href="' . wc_get_checkout_url() . '">', '</a>' ) ), 'notice' );
		}

		// If true, redirect the customer to the cart safely.
		if ( $redirect ) {
			wp_safe_redirect( wc_get_cart_url() );
			exit;
		}
	} // END load_cart_action()

} // END class

return new CoCart_API_Session();
