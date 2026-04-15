<?php
/**
 * Class: CoCart_Load_Cart
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.6.2
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
		add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_action' ) );

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
			$cart_key        = isset( $_GET[ $action ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $action ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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

			// Check if we are notifying the customer via the web.
			if ( ! empty( $_GET['notify'] ) && is_bool( $_GET['notify'] ) !== true ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$notify_customer = true;
			}

			$wc_session = WC()->session;

			// Get the cart in the database.
			$requested_cart = $wc_session->get_session( $cart_key );

			if ( empty( $requested_cart ) ) {
				CoCart_Logger::log(
					sprintf(
						/* translators: %s: cart key */
						__( 'Unable to find cart for: %s', 'cart-rest-api-for-woocommerce' ),
						$cart_key
					),
					'info'
				);

				wc_clear_notices();
				wc_add_notice(
					esc_html__( 'Cart is not valid! If this is an error, contact for help.', 'cart-rest-api-for-woocommerce' ),
					'error'
				);

				return;
			}

			// Get the cart currently in session if any.
			$cart_in_session = (array) array_filter( $wc_session->get( 'cart', array() ) );

			$new_cart = array();

			$new_cart['cart']                       = isset( $requested_cart['cart'] ) ? maybe_unserialize( $requested_cart['cart'] ) : array();
			$new_cart['cart_totals']                = isset( $requested_cart['cart_totals'] ) ? maybe_unserialize( $requested_cart['cart_totals'] ) : null;
			$new_cart['applied_coupons']            = isset( $requested_cart['applied_coupons'] ) ? maybe_unserialize( $requested_cart['applied_coupons'] ) : null;
			$new_cart['coupon_discount_totals']     = isset( $requested_cart['coupon_discount_totals'] ) ? maybe_unserialize( $requested_cart['coupon_discount_totals'] ) : null;
			$new_cart['coupon_discount_tax_totals'] = isset( $requested_cart['coupon_discount_tax_totals'] ) ? maybe_unserialize( $requested_cart['coupon_discount_tax_totals'] ) : null;
			$new_cart['removed_cart_contents']      = isset( $requested_cart['removed_cart_contents'] ) ? maybe_unserialize( $requested_cart['removed_cart_contents'] ) : null;

			if ( ! empty( $requested_cart['chosen_shipping_methods'] ) ) {
				$new_cart['chosen_shipping_methods'] = maybe_unserialize( $requested_cart['chosen_shipping_methods'] );
			}

			if ( ! empty( $requested_cart['cart_fees'] ) ) {
				$new_cart['cart_fees'] = maybe_unserialize( $requested_cart['cart_fees'] );
			}

			// Checks for any items cached.
			if ( ! empty( $requested_cart['cart_cached'] ) ) {
				$new_cart['cart_cached'] = maybe_unserialize( $requested_cart['cart_cached'] );
			}

			cocart_do_deprecated_action( 'cocart_load_cart_override', '4.6.4' );

			// Check if we are keeping the cart currently set via the web.
			$keep_cart = ! empty( $_GET['keep-cart'] ) && filter_var( wp_unslash( $_GET['keep-cart'] ), FILTER_VALIDATE_BOOLEAN ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( $keep_cart ) {
				$new_cart_content = array_merge( $new_cart['cart'], maybe_unserialize( $cart_in_session ) );
				/**
				 * Filter allows you to adjust the merged cart contents.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param array $new_cart_content The new cart content to be merged.
				 * @param array $new_cart         The new cart to be set in session.
				 * @param array $cart_in_session  The cart currently in session.
				 */
				$new_cart['cart'] = apply_filters( 'cocart_merge_cart_content', $new_cart_content, $new_cart['cart'], $cart_in_session );

				$applied_coupons            = $wc_session->get( 'applied_coupons', array() );
				$coupon_discount_totals     = $wc_session->get( 'coupon_discount_totals', array() );
				$coupon_discount_tax_totals = $wc_session->get( 'coupon_discount_tax_totals', array() );
				$removed_cart_contents      = $wc_session->get( 'removed_cart_contents', array() );

				if ( ! is_null( $applied_coupons ) ) {
					$new_cart['applied_coupons'] = ! is_null( $new_cart['applied_coupons'] ) ? array_unique( array_merge( $new_cart['applied_coupons'], $applied_coupons ) ) : $applied_coupons;
				}

				if ( ! is_null( $coupon_discount_totals ) ) {
					$new_cart['coupon_discount_totals'] = ! is_null( $new_cart['coupon_discount_totals'] ) ? array_merge( $new_cart['coupon_discount_totals'], $coupon_discount_totals ) : $coupon_discount_totals;
				}

				if ( ! is_null( $coupon_discount_tax_totals ) ) {
					$new_cart['coupon_discount_tax_totals'] = ! is_null( $new_cart['coupon_discount_tax_totals'] ) ? array_merge( $new_cart['coupon_discount_tax_totals'], $coupon_discount_tax_totals ) : $coupon_discount_tax_totals;
				}

				if ( ! is_null( $removed_cart_contents ) ) {
					$new_cart['removed_cart_contents'] = ! is_null( $new_cart['removed_cart_contents'] ) ? array_merge( $new_cart['removed_cart_contents'], $removed_cart_contents ) : $removed_cart_contents;
				}

				/**
				 * Hook: cocart_load_cart.
				 *
				 * Manipulate the merged cart before it set in session.
				 *
				 * @since 2.1.0 Introduced.
				 *
				 * @param array $new_cart        The new cart to be set in session.
				 * @param array $requested_cart  The requested cart to be loaded.
				 * @param array $cart_in_session The cart currently in session.
				 */
				do_action( 'cocart_load_cart', $new_cart, $requested_cart, $cart_in_session );
			}

			// Sets the PHP session data for the loaded cart.
			// If either cart, applied_coupons, coupon_discount_totals, coupon_discount_tax_totals or removed_cart_contents are not set then they are nulled as fallback.
			$wc_session->set( 'cart', ! empty( $new_cart['cart'] ) ? maybe_unserialize( $new_cart['cart'] ) : array() );
			$wc_session->set( 'cart_totals', ! empty( $new_cart['cart_totals'] ) ? maybe_unserialize( $new_cart['cart_totals'] ) : null );
			$wc_session->set( 'applied_coupons', ! empty( $new_cart['applied_coupons'] ) ? maybe_unserialize( $new_cart['applied_coupons'] ) : null );
			$wc_session->set( 'coupon_discount_totals', ! empty( $new_cart['applied_coupons'] ) ? maybe_unserialize( $new_cart['coupon_discount_totals'] ) : null );
			$wc_session->set( 'coupon_discount_tax_totals', ! empty( $new_cart['applied_coupons'] ) ? maybe_unserialize( $new_cart['coupon_discount_tax_totals'] ) : null );
			$wc_session->set( 'removed_cart_contents', ! empty( $new_cart['applied_coupons'] ) ? maybe_unserialize( $new_cart['removed_cart_contents'] ) : null );
			$wc_session->set( 'chosen_shipping_methods', ! empty( $new_cart['chosen_shipping_methods'] ) ? $new_cart['chosen_shipping_methods'] : null );
			$wc_session->set( 'cart_fees', ! empty( $new_cart['cart_fees'] ) ? $new_cart['cart_fees'] : null );
			$wc_session->set( 'cart_cached', ! empty( $new_cart['cart_cached'] ) ? $new_cart['cart_cached'] : null );

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

			// Set guest customer's cart into session. - This allows the cart to stay synced with the REST API.
			if ( ! is_user_logged_in() && self::maybe_use_cookie_monster() ) {
				$wc_session->set_customer_id( $cart_key );
				$wc_session->set_cart_hash();
				$wc_session->set_session_expiration();
				$wc_session->set_customer_session_cookie( true );
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

		// Make sure we are not accessing this feature via REST API to prevent conflicting loops.
		if ( CoCart::is_rest_api_request() ) {
			return false;
		}

		$action = self::get_action_query();

		// If we did not request to load a cart then just return.
		if ( ! isset( $_GET[ $action ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
		 * @param string $action_query Default is 'cocart-load-cart'
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
			$cart_key = isset( $_GET[ $action ] ) ? trim( sanitize_text_field( wp_unslash( $_GET[ $action ] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

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
} // END class

return new CoCart_Load_Cart();
