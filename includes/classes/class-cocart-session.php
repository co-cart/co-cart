<?php
/**
 * Class: CoCart_Load_Cart
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.4.0
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
		// Loads a users cart.
		add_action( 'wp', array( $this, 'maybe_load_users_cart' ), 0 );

		// Loads a cart in session if valid.
		add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_action' ) );
	} // END __construct()

	/**
	 * Loads a cart in session if valid.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.2.0 Replaced `wc_nocache_headers()` with `cocart_nocache_headers()`.
	 * @since 4.4.0 No longer return debug logs, merge carts together or optionally notify customers of any messages.
	 *
	 * @uses CoCart_Load_Cart::maybe_load_cart()
	 * @uses CoCart_Load_Cart::get_action_query()
	 * @uses is_user_logged_in()
	 * @uses wc_clear_notices()
	 * @uses wc_add_notice()
	 *
	 * @see cocart_nocache_headers()
	 */
	public static function load_cart_action() {
		if ( self::maybe_load_cart() ) {
			$action         = self::get_action_query();
			$cart_key       = isset( $_REQUEST[ $action ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $action ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$is_cart_loaded = true;

			cocart_nocache_headers();

			// Get the requested cart in the database.
			$requested_cart = WC()->session->get_session( $cart_key );

			if ( empty( $requested_cart ) ) {
				$is_cart_loaded = false;

				wc_clear_notices();
				wc_add_notice(
					esc_html__( 'Cart is not valid! If this is an error, contact for help.', 'cart-rest-api-for-woocommerce' ),
					'error'
				);
			}

			if ( $is_cart_loaded ) {
				// Destroy guest cart if one already existed.
				if ( ! is_user_logged_in() && WC()->session->get_customer_id() !== $cart_key ) {
					WC()->session->delete_cart( WC()->session->get_customer_id() );
				}

				// Sets the php session data for the loaded cart.
				WC()->session->set( 'cart', maybe_unserialize( $requested_cart['cart'] ) );
				WC()->session->set( 'applied_coupons', maybe_unserialize( $requested_cart['applied_coupons'] ) );
				WC()->session->set( 'coupon_discount_totals', maybe_unserialize( $requested_cart['coupon_discount_totals'] ) );
				WC()->session->set( 'coupon_discount_tax_totals', maybe_unserialize( $requested_cart['coupon_discount_tax_totals'] ) );
				WC()->session->set( 'removed_cart_contents', maybe_unserialize( $requested_cart['removed_cart_contents'] ) );

				if ( ! empty( $requested_cart['chosen_shipping_methods'] ) ) {
					WC()->session->set( 'chosen_shipping_methods', maybe_unserialize( $requested_cart['chosen_shipping_methods'] ) );
				}

				if ( ! empty( $requested_cart['cart_fees'] ) ) {
					WC()->session->set( 'cart_fees', maybe_unserialize( $requested_cart['cart_fees'] ) );
				}

				// Checks for any items cached.
				if ( ! empty( $requested_cart['cart_cached'] ) ) {
					WC()->session->set( 'cart_cached', maybe_unserialize( $requested_cart['cart_cached'] ) );
				}

				// Setup cart session.
				WC()->session->set_customer_id( $cart_key );
				WC()->session->set_cart_hash();
				WC()->session->set_session_expiration();
				WC()->session->set_customer_session_cookie( true );

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
	 * @since 3.3.0 Introduced.
	 *
	 * @return boolean
	 */
	protected static function maybe_use_cookie_monster() {
		return apply_filters( 'cocart_use_cookie_monster', true );
	} // END maybe_use_cookie_monster()

	/**
	 * Loads a users cart.
	 *
	 * If the cart is associated with a registered user then we make sure that
	 * the user is logged in to help with managing the cart session.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @uses CoCart_Load_Cart::maybe_load_cart()
	 * @uses CoCart_Load_Cart::get_action_query()
	 * @uses is_user_logged_in()
	 * @uses get_user_by()
	 * @uses wp_get_current_user()
	 * @uses wp_logout()
	 * @uses wp_set_auth_cookie()
	 * @uses wp_safe_redirect()
	 * @uses wc_get_checkout_url()
	 */
	public static function maybe_load_users_cart() {
		if ( self::maybe_load_cart() ) {
			$action   = self::get_action_query();
			$cart_key = isset( $_REQUEST[ $action ] ) ? sanitize_text_field( wp_unslash( $_REQUEST[ $action ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$hash     = isset( $_REQUEST['c_hash'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['c_hash'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			/**
			 * Filter allows you to change where to redirect should loading the cart fail.
			 *
			 * @since 4.4.0 Introduced.
			 */
			$redirect_home = apply_filters( 'cocart_load_cart_redirect_home', home_url() );

			if ( ! empty( $cart_key ) ) {
				// Return if the cart key is not all digits.
				if ( ! ctype_digit( $cart_key ) ) {
					return;
				}

				$customer_id = absint( $cart_key );
			}

			// Get cart hash to check if it matches later.
			$cart_hash = cocart_get_cart_hash( $customer_id );

			// No cart hash or found then just redirect home. It's possible the session does not exist.
			if ( empty( $hash ) || $cart_hash !== $hash ) {
				// Determine if we redirect or not based on the cart key value type.
				if ( is_int( $customer_id ) && $cart_key == $customer_id ) {
					wp_safe_redirect( $redirect_home );
					exit;
				} else {
					return;
				}
			}

			// Check if the cart belongs to a registered user.
			$user = get_user_by( 'id', $customer_id );

			// If the user exists and the cart hash match, then the customers ID is a registered user so login user and load the cart.
			if ( ! empty( $user ) && $cart_hash === $hash ) {
				if ( is_user_logged_in() ) {
					$current_user = wp_get_current_user();
					$user_id      = $current_user->ID;

					// Compare the user ID with the customers ID. If not the same user then logout.
					if ( $user_id !== $customer_id ) {
						wp_logout(); // Logout current user.
					}
				} else {
					wp_set_auth_cookie( $customer_id ); // Login new user.
				}

				/**
				 * Fires after the user has successfully logged in.
				 *
				 * Note: This action hook is forked from WP Core so we can trigger any WooCommerce related hooks.
				 *
				 * @ignore Hook ignored when parsed into Code Reference.
				 *
				 * @param string  $user_login Username.
				 * @param WP_User $user       WP_User object of the logged-in user.
				 */
				do_action( 'wp_login', $user->user_login, $user );

				wp_safe_redirect( wc_get_checkout_url() );
				exit;
			}

			return;
		}
	} // END maybe_load_users_cart()
} // END class

return new CoCart_Load_Cart();
