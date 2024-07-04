<?php
/**
 * Class: CoCart_Load_Cart
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load cart from Session.
 *
 * Handles loading cart from session.
 *
 * @since 2.1.2 Introduced.
 */
class CoCart_Load_Cart {

	/**
	 * Setup class.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		// Loads a cart in session if still valid.
		add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_action' ), 10 );

		// Append cart to load for proceed to checkout url.
		add_action( 'woocommerce_get_checkout_url', array( $this, 'proceed_to_checkout' ) );
	} // END __construct()

	/**
	 * Returns true or false if the cart key is saved in the database.
	 *
	 * @todo Deprecate in the future.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @uses WC()->session->get_session()
	 *
	 * @param string $cart_key Requested cart key.
	 *
	 * @return boolean
	 */
	public function is_cart_saved( $cart_key ) {
		$cart_saved = WC()->session->get_session( $cart_key );

		if ( ! empty( $cart_saved ) ) {
			return true;
		}

		return false;
	} // END is_cart_saved()

	/**
	 * Clears all carts from the database.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced
	 *
	 * @deprecated 3.1.2 Replaced with `cocart_task_clear_carts()` instead.
	 *
	 * @see cocart_task_clear_carts()
	 */
	public static function clear_carts() {
		cocart_deprecated_function( 'clear_carts', '3.1.2', 'cocart_task_clear_carts' );

		cocart_task_clear_carts();
	} // END clear_cart()

	/**
	 * Cleans up carts from the database that have expired.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced
	 *
	 * @deprecated 3.1.2 Replaced with `cocart_task_cleanup_carts()` instead.
	 *
	 * @see cocart_task_cleanup_carts()
	 */
	public static function cleanup_carts() {
		cocart_deprecated_function( 'cleanup_carts', '3.1.2', 'cocart_task_cleanup_carts' );

		cocart_task_cleanup_carts();
	} // END cleanup_carts()

	/**
	 * Load cart action.
	 *
	 * Loads a cart in session if still valid and overrides the current cart.
	 * Unless specified not to override, the carts will merge the current cart
	 * and the loaded cart items together.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.2.0 Replaced `wc_nocache_headers()` with `cocart_nocache_headers()`.
	 *
	 * @uses CoCart_Load_Cart::maybe_load_cart()
	 * @uses CoCart_Load_Cart::get_action_query()
	 * @uses CoCart_Logger::log()
	 * @uses get_user_by()
	 * @uses is_user_logged_in()
	 * @uses wp_get_current_user()
	 * @uses wc_add_notice()
	 *
	 * @see cocart_nocache_headers()
	 */
	public static function load_cart_action() {
		if ( self::maybe_load_cart() ) {
			$action          = self::get_action_query();
			$cart_key        = isset( $_REQUEST[ $action ] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST[ $action ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$override_cart   = true;  // Override the cart by default.
			$notify_customer = false; // Don't notify the customer by default.

			cocart_nocache_headers();

			// Check the cart doesn't belong to a registered user - only guest carts should be loadable from session.
			$user = get_user_by( 'id', $cart_key );

			// If the user exists, the cart key is for a registered user so we should just return.
			if ( ! empty( $user ) ) {
				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					$user_id      = $current_user->ID;

					// Compare the user ID with the cart key.
					if ( $user_id === $cart_key ) {
						CoCart_Logger::log(
							sprintf(
								/* translators: %s: cart key */
								__( 'Cart key "%s" is already loaded as the currently logged in user.', 'cart-rest-api-for-woocommerce' ),
								$cart_key
							),
							'error'
						);
					} else {
						CoCart_Logger::log(
							sprintf(
								/* translators: %s: cart key */
								__( 'Customer is logged in as a different user. Cart key "%s" cannot be loaded into session for a different user.', 'cart-rest-api-for-woocommerce' ),
								$cart_key
							),
							'error'
						);
					}
				} else {
					CoCart_Logger::log(
						__( 'Cart key is recognized as a registered user on site. Cannot be loaded into session as a guest.', 'cart-rest-api-for-woocommerce' ),
						'error'
					);
				}

				// Display notice to developer if debug mode is enabled.
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					wc_add_notice(
						__( 'Technical error made! See error log for reason.', 'cart-rest-api-for-woocommerce' ),
						'error'
					);
				}

				return;
			}

			// At this point, the cart should load into session with no issues as we have passed verification.

			// Check if we are keeping the cart currently set via the web.
			if ( ! empty( $_REQUEST['keep-cart'] ) && is_bool( $_REQUEST['keep-cart'] ) !== true ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$override_cart = false;
			}

			// Check if we are notifying the customer via the web.
			if ( ! empty( $_REQUEST['notify'] ) && is_bool( $_REQUEST['notify'] ) !== true ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$notify_customer = true;
			}

			// Get the cart in the database.
			$stored_cart = WC()->session->get_session( $cart_key );

			if ( empty( $stored_cart ) ) {
				CoCart_Logger::log(
					sprintf(
						/* translators: %s: cart key */
						__( 'Unable to find cart for: %s', 'cart-rest-api-for-woocommerce' ),
						$cart_key
					),
					'info'
				);

				if ( $notify_customer ) {
					wc_add_notice(
						__( 'Sorry but this cart has expired!', 'cart-rest-api-for-woocommerce' ),
						'error'
					);
				}

				return;
			}

			// Get the cart currently in session if any.
			$cart_in_session = WC()->session->get( 'cart', array() );

			$new_cart = array();

			$new_cart['cart']                       = maybe_unserialize( $stored_cart['cart'] );
			$new_cart['applied_coupons']            = maybe_unserialize( $stored_cart['applied_coupons'] );
			$new_cart['coupon_discount_totals']     = maybe_unserialize( $stored_cart['coupon_discount_totals'] );
			$new_cart['coupon_discount_tax_totals'] = maybe_unserialize( $stored_cart['coupon_discount_tax_totals'] );
			$new_cart['removed_cart_contents']      = maybe_unserialize( $stored_cart['removed_cart_contents'] );

			if ( ! empty( $stored_cart['chosen_shipping_methods'] ) ) {
				$new_cart['chosen_shipping_methods'] = maybe_unserialize( $stored_cart['chosen_shipping_methods'] );
			}

			if ( ! empty( $stored_cart['cart_fees'] ) ) {
				$new_cart['cart_fees'] = maybe_unserialize( $stored_cart['cart_fees'] );
			}

			// Checks for any items cached.
			if ( ! empty( $stored_cart['cart_cached'] ) ) {
				$new_cart['cart_cached'] = maybe_unserialize( $stored_cart['cart_cached'] );
			}

			// Check if we are overriding the cart currently in session via the web.
			if ( $override_cart ) {
				// Only clear the cart if it's not already empty.
				if ( ! WC()->cart->is_empty() ) {
					WC()->cart->empty_cart( false );

					/**
					 * Hook: cocart_load_cart_override.
					 *
					 * Manipulate the overriding cart before it set in session.
					 *
					 * @since 2.1.0 Introduced.
					 */
					do_action( 'cocart_load_cart_override', $new_cart, $stored_cart );
				}
			} else {
				$new_cart_content = array_merge( $new_cart['cart'], maybe_unserialize( $cart_in_session ) );
				/**
				 * Filter allows you to adjust the merged cart contents.
				 *
				 * @since 2.1.0 Introduced.
				 */
				$new_cart['cart']                       = apply_filters( 'cocart_merge_cart_content', $new_cart_content, $new_cart['cart'], $cart_in_session );
				$new_cart['applied_coupons']            = array_unique( array_merge( $new_cart['applied_coupons'], WC()->cart->get_applied_coupons() ) );
				$new_cart['coupon_discount_totals']     = array_merge( $new_cart['coupon_discount_totals'], WC()->cart->get_coupon_discount_totals() );
				$new_cart['coupon_discount_tax_totals'] = array_merge( $new_cart['coupon_discount_tax_totals'], WC()->cart->get_coupon_discount_tax_totals() );
				$new_cart['removed_cart_contents']      = array_merge( $new_cart['removed_cart_contents'], WC()->cart->get_removed_cart_contents() );

				/**
				 * Hook: cocart_load_cart.
				 *
				 * Manipulate the merged cart before it set in session.
				 *
				 * @since 2.1.0 Introduced.
				 */
				do_action( 'cocart_load_cart', $new_cart, $stored_cart, $cart_in_session );
			}

			// Destroy cart and cookie if user is a guest customer before creating a new one.
			if ( ! is_user_logged_in() && self::maybe_use_cookie_monster() ) {
				WC()->session->delete_cart( WC()->session->get_customer_id() );
				WC()->session->destroy_cookie();
			}

			// Sets the php session data for the loaded cart.
			WC()->session->set( 'cart', $new_cart['cart'] );
			WC()->session->set( 'applied_coupons', $new_cart['applied_coupons'] );
			WC()->session->set( 'coupon_discount_totals', $new_cart['coupon_discount_totals'] );
			WC()->session->set( 'coupon_discount_tax_totals', $new_cart['coupon_discount_tax_totals'] );
			WC()->session->set( 'removed_cart_contents', $new_cart['removed_cart_contents'] );

			if ( ! empty( $new_cart['chosen_shipping_methods'] ) ) {
				WC()->session->set( 'chosen_shipping_methods', $new_cart['chosen_shipping_methods'] );
			}

			if ( ! empty( $new_cart['cart_fees'] ) ) {
				WC()->session->set( 'cart_fees', $new_cart['cart_fees'] );
			}

			if ( ! empty( $new_cart['cart_cached'] ) ) {
				WC()->session->set( 'cart_cached', $new_cart['cart_cached'] );
			}

			// Set loaded cart for guest customer.
			if ( ! is_user_logged_in() && self::maybe_use_cookie_monster() ) {
				WC()->session->set_cart_hash();
				WC()->session->set_customer_id( $cart_key );
				WC()->session->set_cart_expiration();
				WC()->session->set_customer_session_cookie( true );
			}

			// If true, notify the customer that there cart has transferred over via the web.
			if ( ! empty( $new_cart ) && $notify_customer ) {
				wc_add_notice(
					apply_filters( 'cocart_cart_loaded_successful_message',
						sprintf(
							/* translators: %1$s: Start of link to Shop archive. %2$s: Start of link to checkout page. %3$s: Closing link tag. */
							__( 'Your 🛒 cart has been transferred over. You may %1$scontinue shopping%3$s or %2$scheckout%3$s.', 'cart-rest-api-for-woocommerce' ),
							'<a href="' . wc_get_page_permalink( 'shop' ) . '">',
							'<a href="' . wc_get_checkout_url() . '">',
							'</a>'
						)
					),
					'notice'
				);
			}

			/**
			 * Hook: cocart_cart_loaded.
			 *
			 * Fires once a cart has loaded. Can be used to trigger a webhook.
			 *
			 * @since 3.8.0 Introduced.
			 *
			 * @param string $cart_key The cart key.
			 */
			do_action( 'cocart_cart_loaded', $cart_key );
		}
	} // END load_cart_action()

	/**
	 * Checks if we are loading a cart from session
	 * and if this feature is not disabled.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @uses CoCart_Load_Cart::get_action_query()
	 *
	 * @return boolean
	 */
	public static function maybe_load_cart() {
		/**
		 * Filter checks if "Load Cart from Session" feature is disabled.
		 *
		 * @since 3.0.0 Introduced.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'cocart_disable_load_cart', false ) ) {
			return false;
		}

		$action = self::get_action_query();

		// If we did not request to load a cart then just return.
		if ( ! isset( $_REQUEST[ $action ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		return true;
	} // END maybe_load_cart()

	/**
	 * Get the load cart action query name.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string
	 */
	protected static function get_action_query() {
		/**
		 * Filter allows developers to add more white labelling when loading the cart via web.
		 *
		 * @since 2.8.2 Introduced.
		 *
		 * @param string
		 */
		$load_cart = apply_filters( 'cocart_load_cart_query_name', 'cocart-load-cart' );

		return $load_cart;
	} // END get_action_query()

	/**
	 * Proceed to Checkout. (Legacy Checkout)
	 *
	 * Appends the cart query to the checkout URL so when a user proceeds
	 * to the checkout page it loads that same cart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.3.0 Introduced.
	 *
	 * @param string $checkout_url Checkout URL.
	 *
	 * @return string $checkout_url Original checkout URL or checkout URL with added query argument.
	 */
	public static function proceed_to_checkout( $checkout_url ) {
		if ( ! is_user_logged_in() && self::maybe_load_cart() ) {
			$action   = self::get_action_query();
			$cart_key = isset( $_REQUEST[ $action ] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST[ $action ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

			if ( ! empty( $cart_key ) ) {
				$checkout_url = add_query_arg( $action, $cart_key, $checkout_url );
			}
		}

		return $checkout_url;
	} // END proceed_to_checkout()

	/**
	 * Cookie Monster
	 *
	 * Do we eat the cookie before baking a new one? LOL
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @return boolean
	 */
	protected static function maybe_use_cookie_monster() {
		return apply_filters( 'cocart_use_cookie_monster', true );
	} // END maybe_use_cookie_monster()
} // END class

return new CoCart_Load_Cart();
