<?php
/**
 * Handles data for the cart (Legacy).
 *
 * Forked from WC_Session_Handler, changed default variables,
 * database table used, filters and made adjustments to accommodate
 * support for guest customers as well as registered customers via the REST API.
 *
 * All native uses still work as normal.
 *
 * @link https://github.com/woocommerce/woocommerce/blob/master/includes/class-wc-session-handler.php
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Session;

use CoCart\RestApi\Authentication;
use CoCart\Abstracts\Session;
use CoCart\Logger;
use WC_Customer;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session handler class.
 */
class Handler extends Session {

	/**
	 * Cookie name used for the cart.
	 *
	 * @access protected
	 *
	 * @var string cookie name
	 */
	protected $_cookie;

	/**
	 * True when the cookie exists.
	 *
	 * @access protected
	 *
	 * @var bool Based on whether a cookie exists.
	 */
	protected $_has_cookie = false;

	/**
	 * Table name for cart data.
	 *
	 * @access protected
	 *
	 * @var string Custom cart table name
	 */
	protected $_table;

	/**
	 * Constructor for the session class.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->_cookie = apply_filters( 'woocommerce_cookie', 'wp_cocart_session_' . COOKIEHASH );
		$this->_table  = $GLOBALS['wpdb']->prefix . 'cocart_carts';
	} // END __construct()

	/**
	 * Init hooks and cart data.
	 *
	 * @uses Authentication::is_rest_api_request()
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Rest requests don't require the use of cookies as backup.
	 */
	public function init() {
		if ( Authentication::is_rest_api_request() ) {
			$this->_cart_source = 'cocart';

			$this->init_session_without_cookie();

			$this->set_cart_hash();
		} else {
			$this->_cart_source = 'woocommerce';

			$this->init_session_cookie();
			add_action( 'woocommerce_set_cart_cookies', array( $this, 'set_customer_cart_cookie' ), 20 );
		}

		add_action( 'shutdown', array( $this, 'save_cart' ), 20 );
		add_action( 'wp_logout', array( $this, 'destroy_cart' ) );

		/**
		 * When a user is logged out, ensure they have a unique nonce by using the customer ID.
		 *
		 * @since 2.1.2 Introduced.
		 * @since 4.0.0 No longer needed for API requests.
		 */
		if ( ! Authentication::is_rest_api_request() && ! is_user_logged_in() ) {
			add_filter( 'nonce_user_logged_out', array( $this, 'maybe_update_nonce_user_logged_out' ), 10, 2 );
		}
	} // END init()

	/**
	 * Setup cart.
	 *
	 * This is the native session setup.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Removed parameter $current_user_id
	 */
	public function init_session_cookie() {
		// Get cart cookie... if any.
		$cookie = $this->get_session_cookie();

		// Does a cookie exist?
		if ( $cookie ) {
			// Get cookie details.
			$this->_customer_id     = $cookie[0];
			$this->_cart_expiration = $cookie[1];
			$this->_cart_expiring   = $cookie[2];
			$this->_has_cookie      = true;
		}

		// Check if we requested to load a specific cart.
		if ( isset( $_REQUEST['cart_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			// Set requested cart key as customer ID in session.
			$this->_customer_id = (string) trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// If cart retrieved then update cart.
		if ( $cookie || $this->_customer_id ) {
			$this->_data = $this->get_cart_data();

			if ( ! $this->is_session_cookie_valid() ) {
				$this->destroy_session();
				$this->set_session_expiration();
			}

			// If the user logged in, update cart.
			if ( is_user_logged_in() && strval( get_current_user_id() ) !== $this->_customer_id ) {
				// Destroy old cookie.
				$this->set_customer_cart_cookie( false );

				// Update customer ID details.
				$guest_cart_id      = $this->_customer_id;
				$this->_customer_id = strval( get_current_user_id() );

				// Save cart data under customers ID number and remove old guest cart.
				$this->save_cart( $guest_cart_id );

				// Save new cookie for cart.
				$this->set_customer_cart_cookie( true );
			}

			// Update cart if its close to expiring.
			if ( time() > $this->_cart_expiring || empty( $this->_cart_expiring ) ) {
				$this->set_session_expiration();
				$this->update_cart_timestamp( $this->_customer_id, $this->_cart_expiration );
			}
		} else {
			// New guest customer.
			$this->set_session_expiration();
			$this->_customer_id = $this->generate_customer_id();
			$this->_data        = $this->get_cart_data();
		}
	} // END init_session_cookie()

	/**
	 * Checks if session cookie is expired, or belongs to a logged out user.
	 *
	 * @access private
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return bool Whether session cookie is valid.
	 */
	private function is_session_cookie_valid() {
		// If session is expired, session cookie is invalid.
		if ( time() > $this->_cart_expiration ) {
			return false;
		}

		// If user has logged out, session cookie is invalid.
		if ( ! is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) ) {
			return false;
		}

		// Session from a different user is not valid. (Although from a guest user will be valid)
		if ( is_user_logged_in() && ! $this->is_customer_guest( $this->_customer_id ) && strval( get_current_user_id() ) !== $this->_customer_id ) {
			return false;
		}

		return true;
	} // END is_session_cookie_valid()

	/**
	 * Setup cart without cookie.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param int $current_user_id Current user ID.
	 */
	public function init_session_without_cookie( $current_user_id = 0 ) {
		// Check if we requested to load a specific cart.
		$this->_cart_key = $this->get_requested_cart();

		// Customer is null until we say otherwise.
		$this->_customer_id = null;

		// If a cart was requested then update it if needed.
		if ( ! empty( $this->_cart_key ) ) {
			$this->_data = $this->get_cart_data();

			// Update cart if its close to expiring.
			if ( time() > $this->_cart_expiring || empty( $this->_cart_expiring ) ) {
				$this->set_cart_expiration();
				$this->update_cart_timestamp( $this->_customer_id, $this->_cart_expiration );
			}
		} else {
			// New guest customer.
			$this->set_cart_expiration();
			$this->_cart_key = $this->generate_key();
			$this->_data     = $this->get_cart_data();
		}
	} // END init_session_without_cookie()

	/**
	 * Get requested cart.
	 *
	 * Either returns the cart key from the URL or via header.
	 *
	 * @access public
	 *
	 * @since 4.0.0
	 *
	 * @return string Cart key.
	 */
	public function get_requested_cart() {
		$cart_key = ''; // Leave blank to start.

		// Are we requesting via url parameter?
		if ( isset( $_REQUEST['cart_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$cart_key = (string) trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Are we requesting via custom header?
		if ( ! empty( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) {  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$cart_key = (string) trim( sanitize_key( wp_unslash( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		$cart_key = apply_filters( 'cocart_get_requested_cart', $cart_key );

		return $cart_key;
	} // END get_requested_cart()

	/**
	 * Is Cookie support enabled?
	 *
	 * Determines if a cookie should manage the cart for customers.
	 *
	 * @access public
	 *
	 * @since      2.1.0 Introduced.
	 * @deprecated 4.0.0 No replacement.
	 *
	 * @return bool
	 */
	public function is_cookie_supported() {
		cocart_do_deprecated_action( 'cocart_cookie_supported', '4.0.0', null, sprintf( __( '%s is no longer used. No replacement.', 'cart-rest-api-for-woocommerce' ), __FUNCTION__ ) );

		return apply_filters( 'cocart_cookie_supported', true );
	} // END is_cookie_supported()

	/**
	 * Sets the cart cookie on-demand.
	 *
	 * Warning: Cookies will only be set if this is called before the headers are sent.
	 *
	 * @access public
	 *
	 * @param bool $set Should the cart cookie be set.
	 */
	public function set_customer_cart_cookie( $set = true ) {
		if ( $set ) {
			$to_hash           = $this->_customer_id . '|' . $this->_cart_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_customer_id . '||' . $this->_cart_expiration . '||' . $this->_cart_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

			// If no cookie exists then create a new.
			if ( ! isset( $_COOKIE[ $this->_cookie ] ) || $_COOKIE[ $this->_cookie ] !== $cookie_value ) {
				cocart_setcookie( $this->_cookie, $cookie_value, $this->_cart_expiration, $this->use_secure_cookie(), $this->use_httponly() );
			}
		} else {
			// If cookies exists, destroy it.
			if ( isset( $_COOKIE[ $this->_cookie ] ) ) {
				cocart_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), $this->use_httponly() );
				unset( $_COOKIE[ $this->_cookie ] );
			}
		}
	} // END set_customer_cart_cookie()

	/**
	 * Backwards compatibility function for setting cart cookie.
	 *
	 * Since the cookie name (as of WooCommerce 2.1) is prepended with wp, cache systems like batcache will not cache pages when set.
	 *
	 * @access public
	 *
	 * @param bool $set Should the cart cookie be set.
	 *
	 * @since 2.6.0 Introduced.
	 */
	public function set_customer_session_cookie( $set = true ) {
		$this->set_customer_cart_cookie( $set );
	} // END set_customer_session_cookie()

	/**
	 * Returns the cookie name.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_cookie_name() {
		return $this->_cookie;
	} // END get_cookie_name()

	/**
	 * Should the cart cookie be secure?
	 *
	 * @access protected
	 *
	 * @return bool
	 */
	protected function use_secure_cookie() {
		return apply_filters( 'cocart_cart_use_secure_cookie', wc_site_is_https() && is_ssl() );
	} // END use_secure_cookie()

	/**
	 * Set a cookie - wrapper for setcookie using WP constants.
	 *
	 * @access public
	 *
	 * @since      2.1.0 Introduced.
	 * @deprecated 4.0.0 Uses cocart_setcookie() instead.
	 * @version    4.0.0
	 *
	 * @param string  $name Name of the cookie being set.
	 * @param string  $value Value of the cookie.
	 * @param integer $expire Expiry of the cookie.
	 * @param bool    $secure Whether the cookie should be served only over https.
	 * @param bool    $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript. @since 2.7.2.
	 */
	public function cocart_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false ) {
		cocart_deprecated_function( 'CoCart\Session\Handler::cocart_setcookie', '4.0', 'cocart_setcookie' );

		if ( ! headers_sent() ) {
			/**
			 * samesite - Set to None by default and only available to those using PHP 7.3 or above.
			 *
			 * @since 2.9.1.
			 */
			if ( version_compare( PHP_VERSION, '7.3.0', '>=' ) ) {
				setcookie( $name, $value, apply_filters( 'cocart_set_cookie_options', array( 'expires' => $expire, 'secure' => $secure, 'path' => COOKIEPATH ? COOKIEPATH : '/', 'domain' => COOKIE_DOMAIN, 'httponly' => apply_filters( 'cocart_cookie_httponly', $httponly, $name, $value, $expire, $secure ), 'samesite' => apply_filters( 'cocart_cookie_samesite', 'Lax' ) ), $name, $value ) ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
			} else {
				setcookie( $name, $value, $expire, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, $secure, apply_filters( 'cocart_cookie_httponly', $httponly, $name, $value, $expire, $secure ) );
			}
		} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			headers_sent( $file, $line );
			trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
		}
	} // END cocart_setcookie()

	/**
	 * Return true if the current customer has an active cart.
	 *
	 * Either a cookie, a user ID or a cart key to retrieve values.
	 *
	 * @access public
	 *
	 * @return bool
	 */
	public function has_session() {
		// Check cookie first for native cart.
		if ( isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie ) {
			return true;
		}

		// Current user ID. If value is above zero then user is logged in.
		$current_user_id = strval( get_current_user_id() );
		if ( is_user_logged_in() || is_numeric( $current_user_id ) && $current_user_id > 0 ) {
			return true;
		}

		// If we are loading a session via REST API then identify cart key.
		if ( ! empty( $this->_customer_id ) ) {
			return true;
		}

		return false;
	} // END has_session()

	/**
	 * Set session expiration.
	 *
	 * PHP session expiration is set to 48 hours by default.
	 *
	 * @access public
	 */
	public function set_session_expiration() {
		$this->_cart_expiring   = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) ); // 47 Hours.
		$this->_cart_expiration = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) ); // 48 Hours.
	} // END set_session_expiration()

	/**
	 * Set cart expiration.
	 *
	 * This session expiration is used for the REST API.
	 *
	 * @access public
	 */
	public function set_cart_expiration() {
		$this->_cart_expiring   = time() + intval( apply_filters( 'cocart_cart_expiring', DAY_IN_SECONDS * 6 ) ); // 6 Days.
		$this->_cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', DAY_IN_SECONDS * 7 ) ); // 7 Days.
	} // END set_cart_expiration()

	/**
	 * Generate a unique customer ID for guests, or return user ID if logged in.
	 *
	 * @uses Handler::generate_key()
	 *
	 * @access public
	 *
	 * @since 2.6.0 Introduced.
	 * @since 4.0.0 Now uses `generate_key()` if customer ID is empty.
	 *
	 * @return string
	 */
	public function generate_customer_id() {
		$customer_id = '';

		$current_user_id = strval( get_current_user_id() );
		if ( is_numeric( $current_user_id ) && $current_user_id > 0 ) {
			$customer_id = $current_user_id;
		}

		if ( empty( $customer_id ) ) {
			$customer_id = $this->generate_key();
		}

		return $customer_id;
	} // END generate_customer_id()

	/**
	 * Generate a unique key.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string A unique key.
	 */
	public function generate_key() {
		require_once ABSPATH . 'wp-includes/class-phpass.php';

		$hasher      = new \PasswordHash( 8, false );
		$customer_id = apply_filters( 'cocart_generate_key', md5( $hasher->get_random_bytes( 32 ) ), $hasher );

		return $customer_id;
	} // END generate_key()

	/**
	 * Checks if this is an auto-generated customer ID.
	 *
	 * @access private
	 *
	 * @param string|int $customer_id Customer ID to check.
	 *
	 * @return bool Whether customer ID is randomly generated.
	 */
	private function is_customer_guest( $customer_id ) {
		$customer_id = strval( $customer_id );

		if ( empty( $customer_id ) ) {
			return true;
		}

		// Almost all random $customer_ids will have some letters in it, while all actual ids will be integers.
		if ( strval( (int) $customer_id ) !== $customer_id ) {
			return true;
		}

		// Performance hack to potentially save a DB query, when same user as $customer_id is logged in.
		if ( is_user_logged_in() && strval( get_current_user_id() ) === $customer_id ) {
			return false;
		} else {
			$customer = new WC_Customer( $customer_id );

			if ( 0 === $customer->get_id() ) {
				return true;
			}
		}

		return false;
	} // END is_customer_guest()

	/**
	 * Get session unique ID for requests if session is initialized or user ID if logged in.
	 * Introduced to help with unit tests in WooCommerce since version 5.3
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_customer_unique_id() {
		$customer_id = '';

		if ( $this->has_session() && $this->_customer_id ) {
			$customer_id = $this->_customer_id;
		} elseif ( is_user_logged_in() ) {
			$customer_id = (string) get_current_user_id();
		}

		return $customer_id;
	} // END get_customer_unique_id()

	/**
	 * Get the cart cookie, if set. Otherwise return false.
	 *
	 * Cart cookies without a cart key and customer ID are invalid.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.3
	 *
	 * @return bool|array
	 */
	public function get_session_cookie() {
		$cookie_value = isset( $_COOKIE[ $this->_cookie ] ) ? wp_unslash( $_COOKIE[ $this->_cookie ] ) : false; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
			return false;
		}

		list( $customer_id, $cart_expiration, $cart_expiring, $cookie_hash ) = explode( '||', $cookie_value );

		if ( empty( $customer_id ) ) {
			return false;
		}

		// Validate hash.
		$to_hash = $customer_id . '|' . $cart_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
			return false;
		}

		return array( $customer_id, $cart_expiration, $cart_expiring, $cookie_hash );
	} // END get_session_cookie()

	/**
	 * Get cart data.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_cart_data() {
		return $this->has_session() ? (array) $this->get_cart( $this->_customer_id, array() ) : array();
	} // END get_cart_data()

	/**
	 * Get session data.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->get_cart_data();
	}

	/**
	 * Gets a cache prefix. This is used in cart names so the entire
	 * cache can be invalidated with 1 function call.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @return string
	 */
	public function get_cache_prefix() {
		return \WC_Cache_Helper::get_cache_prefix( COCART_CART_CACHE_GROUP );
	} // END get_cache_prefix()

	/**
	 * Save cart data and delete previous cart data.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.7
	 *
	 * @param int $old_cart_key cart ID before user logs in.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function save_cart( $old_cart_key = 0 ) {
		if ( $this->has_session() ) {
			global $wpdb;

			/**
			 * Deprecated filter: `cocart_empty_cart_expiration` as it is no longer needed.
			 *
			 * @deprecated 2.7.2 No replacement.
			 */
			/* translators: %s: filter name */
			$message = sprintf( __( 'This filter "%s" is no longer required and has been deprecated.', 'cart-rest-api-for-woocommerce' ), 'cocart_empty_cart_expiration' );
			cocart_do_deprecated_action( 'cocart_empty_cart_expiration', '2.7.2', null, $message );

			/**
			 * Checks if data is still validated to create a cart or update a cart in session.
			 *
			 * @since   2.7.2 Introduced.
			 * @version 2.7.3
			 */
			$this->_data = $this->is_cart_data_valid( $this->_data, $this->_customer_id );

			if ( ! $this->_data || empty( $this->_data ) || is_null( $this->_data ) ) {
				return true;
			}

			/**
			 * Filter source of cart.
			 *
			 * @since 3.0.0
			 * @param string $cart_source
			 */
			$cart_source = apply_filters( 'cocart_cart_source', $this->_cart_source );

			/**
			 * Set the cart hash.
			 *
			 * @since 3.0.0
			 */
			$this->set_cart_hash();

			// Save or update cart data.
			$wpdb->query(
				$wpdb->prepare(
					"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_created`, `cart_expiry`, `cart_source`, `cart_hash`) VALUES (%s, %s, %d, %d, %s, %s)
 					ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`), `cart_hash` = VALUES(`cart_hash`)",
					$this->_customer_id,
					maybe_serialize( $this->_data ),
					time(),
					(int) $this->_cart_expiration,
					$cart_source,
					$this->_cart_hash
				)
			);

			wp_cache_set( $this->get_cache_prefix() . $this->_customer_id, $this->_data, COCART_CART_CACHE_GROUP, $this->_cart_expiration - time() );

			// Customer is now registered so we delete the previous cart as guest to prevent duplication.
			if ( get_current_user_id() !== $old_cart_key && ! is_object( get_user_by( 'id', $old_cart_key ) ) ) {
				$this->delete_cart( $old_cart_key );
			}
		}
	} // END save_cart()

	/**
	 * Backwards compatibility for other plugins to
	 * save data and delete guest session.
	 *
	 * @access public
	 *
	 * @since 3.0.13 Introduced.
	 *
	 * @param int $old_session_key session ID before user logs in.
	 */
	public function save_data( $old_session_key = 0 ) {
		$this->save_cart( $old_cart_key );
	} // END save_data()

	/**
	 * Destroy all cart data.
	 *
	 * @access public
	 */
	public function destroy_cart() {
		$this->delete_cart( $this->_customer_id );
		$this->forget_cart();
	} // END destroy_cart()

	/**
	 * Backwards compatibility for other plugins to
	 * destroy all session data.
	 *
	 * @access public
	 *
	 * @since 3.0.13 Introduced.
	 */
	public function destroy_session() {
		$this->destroy_cart();
	} // END destroy_session()

	/**
	 * Destroy cart cookie.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function destroy_cookie() {
		cocart_setcookie( $this->_cookie, '', time() - YEAR_IN_SECONDS, $this->use_secure_cookie(), $this->use_httponly() );
	} // END destroy_cookie()

	/**
	 * Forget all cart data without destroying it.
	 *
	 * @access public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 */
	public function forget_cart() {
		$this->destroy_cookie();

		// Empty cart.
		wc_empty_cart();

		$this->_data        = array();
		$this->_customer_id = $this->generate_key();
	} // END forget_cart()

	/**
	 * Backwards compatibility for other plugins to
	 * forget cart data without destroying it.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function forget_session() {
		$this->forget_cart();
	} // END forget_session()

	/**
	 * When a user is logged out, ensure they have a unique nonce by using the user ID.
	 *
	 * @access public
	 *
	 * @since      2.1.2 Introduced.
	 * @deprecated 4.0.0
	 * @version    4.0.0
	 *
	 * @param int $uid User ID.
	 *
	 * @return string
	 */
	public function nonce_user_logged_out( $uid ) {
		cocart_deprecated_function( 'CoCart\Session\Handler::nonce_user_logged_out', '4.0', 'CoCart\Session\Handler::maybe_update_nonce_user_logged_out' );

		return $this->has_session() && $this->_customer_id ? $this->_customer_id : $uid;
	} // END nonce_user_logged_out()

	/**
	 * When a user is logged out, ensure they have a unique nonce to manage cart and more using the customer/session ID.
	 * This filter runs everything `wp_verify_nonce()` and `wp_create_nonce()` gets called.
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param int    $uid    User ID.
	 * @param string $action The nonce action.
	 *
	 * @return int|string
	 */
	public function maybe_update_nonce_user_logged_out( $uid, $action ) {
		if ( \Automattic\WooCommerce\Utilities\StringUtil::starts_with( $action, 'woocommerce' ) ) {
			return $this->has_session() && $this->_customer_id ? $this->_customer_id : $uid;
		}

		return $uid;
	} // END maybe_update_nonce_user_logged_out()

	/**
	 * Cleanup cart data from the database and clear caches.
	 *
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		$wpdb->query( $wpdb->prepare( "DELETE FROM $this->_table WHERE cart_expiry < %d", time() ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// Invalidate cache group.
		if ( class_exists( '\WC_Cache_Helper' ) ) {
			\WC_Cache_Helper::invalidate_cache_group( COCART_CART_CACHE_GROUP );
		}
	} // END cleanup_sessions()

	/**
	 * Returns the cart.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 * @param mixed  $default  Default cart value.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 * @return string|array
	 */
	public function get_cart( $cart_key, $default = false ) {
		global $wpdb;

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $cart_key, COCART_CART_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_value FROM $this->_table WHERE cart_key = %s", $cart_key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			if ( is_null( $value ) ) {
				$value = $default;
			}

			$cache_duration = $this->_cart_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $cart_key, $value, COCART_CART_CACHE_GROUP, $cache_duration );
			}
		}

		return maybe_unserialize( $value );
	} // END get_cart()

	/**
	 * Returns the session.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param string $cart_key The cart key.
	 * @param mixed  $default  Default cart value.
	 *
	 * @return string|array
	 */
	public function get_session( $cart_key, $default = false ) {
		return $this->get_cart( $cart_key, $default );
	} // END get_session()

	/**
	 * Returns the timestamp the cart was created.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public function get_cart_created( $cart_key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_created FROM $this->_table WHERE cart_key = %s", $cart_key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $value;
	} // END get_cart_created()

	/**
	 * Returns the timestamp the cart expires.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.s
	 *
	 * @return string
	 */
	public function get_cart_expiration( $cart_key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_expiry FROM $this->_table WHERE cart_key = %s", $cart_key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $value;
	} // END get_cart_expiration()

	/**
	 * Returns the source of the cart.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string
	 */
	public function get_cart_source( $cart_key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT cart_source FROM $this->_table WHERE cart_key = %s", $cart_key ) ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		return $value;
	} // END get_cart_source()

	/**
	 * Create a blank new cart and returns cart key if successful.
	 *
	 * @access  public
	 *
	 * @since   2.1.0 Introduced.
	 * @version 3.0.0
	 *
	 * @param string $cart_key        The cart key passed to create the cart.
	 * @param array  $cart_value      The cart data.
	 * @param string $cart_expiration Timestamp of cart expiration.
	 * @param string $cart_source     Cart source.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return $cart_key
	 */
	public function create_new_cart( $cart_key = '', $cart_value = array(), $cart_expiration = '', $cart_source = '' ) {
		global $wpdb;

		if ( empty( $cart_key ) ) {
			$cart_key = self::generate_customer_id();
		}

		if ( empty( $cart_expiration ) ) {
			$cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiring', DAY_IN_SECONDS * 7 ) );
		}

		if ( empty( $cart_source ) ) {
			$cart_source = apply_filters( 'cocart_cart_source', $this->_cart_source );
		}

		$result = $wpdb->insert(
			$this->_table,
			array(
				'cart_key'     => $cart_key,
				'cart_value'   => maybe_serialize( $cart_value ),
				'cart_created' => time(),
				'cart_expiry'  => (int) $cart_expiration,
				'cart_source'  => $cart_source,
			),
			array( '%s', '%s', '%d', '%d', '%s' )
		);

		// Returns the cart key if cart successfully created.
		if ( $result ) {
			return $cart_key;
		}
	} // END create_new_cart()

	/**
	 * Update cart.
	 *
	 * @access public
	 *
	 * @param string $cart_key Cart to update.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function update_cart( $cart_key ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array(
				'cart_value'  => maybe_serialize( $this->_data ),
				'cart_expiry' => (int) $this->_cart_expiration,
			),
			array( 'cart_key' => $cart_key ),
			array( '%s', '%d' ),
			array( '%s' )
		);
	} // END update_cart()

	/**
	 * Delete the cart from the cache and database.
	 *
	 * @access public
	 *
	 * @param string $cart_key The cart key.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function delete_cart( $cart_key ) {
		global $wpdb;

		// Delete cache.
		wp_cache_delete( $this->get_cache_prefix() . $cart_key, COCART_CART_CACHE_GROUP );

		// Delete cart from database.
		$wpdb->delete( $this->_table, array( 'cart_key' => $cart_key ), array( '%s' ) );
	} // END delete_cart()

	/**
	 * Update the cart expiry timestamp.
	 *
	 * @access public
	 *
	 * @param string $cart_key  The cart key.
	 * @param int    $timestamp Timestamp to expire the cookie.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function update_cart_timestamp( $cart_key, $timestamp ) {
		global $wpdb;

		$wpdb->update(
			$this->_table,
			array( 'cart_expiry' => $timestamp ),
			array( 'cart_key' => $cart_key ),
			array( '%d' ),
			array( '%s' )
		);
	} // END update_cart_timestamp()

	/**
	 * Checks if data is still validated to create a cart or update a cart in session.
	 *
	 * @access protected
	 *
	 * @since   2.7.2 Introduced.
	 * @version 3.0.14
	 *
	 * @param array  $data     The cart data to validate.
	 * @param string $cart_key The cart key.
	 *
	 * @return array $data Returns the original cart data or a boolean value.
	 */
	protected function is_cart_data_valid( $data, $cart_key ) {
		if ( ! empty( $data ) && empty( $this->get_cart( $cart_key ) ) ) {
			// If the cart value is empty then the cart data is not valid.
			if ( ! isset( $data['cart'] ) || empty( maybe_unserialize( $data['cart'] ) ) ) {
				$data = false;
			}
		}

		$data = apply_filters( 'cocart_is_cart_data_valid', $data );

		return $data;
	} // END is_cart_data_valid()

	/**
	 * Whether the cookie is only accessible over HTTP.
	 * Returns true by default for the frontend and false by default via the REST API.
	 *
	 * @uses Authentication::is_rest_api_request()
	 *
	 * @access protected
	 *
	 * @since      2.7.2 Introduced.
	 * @deprecated 4.0.0 No longer used.
	 *
	 * @return boolean
	 */
	protected function use_httponly() {
		cocart_deprecated_function( 'CoCart\Session\Handler::use_httponly', '4.0' );

		$httponly = true;

		if ( Authentication::is_rest_api_request() ) {
			$httponly = false;
		}

		return $httponly;
	} // END use_httponly()

	/**
	 * Set the cart hash based on the carts contents and total.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.0.3
	 */
	public function set_cart_hash() {
		$cart_session = $this->get( 'cart' );
		$cart_totals  = $this->get( 'cart_totals' );

		$cart_total = isset( $cart_totals ) ? maybe_unserialize( $cart_totals ) : array( 'total' => 0 );
		$hash       = ! empty( $cart_session ) ? md5( wp_json_encode( $cart_session ) . $cart_total['total'] ) : '';

		$this->_cart_hash = $hash;
	} // END set_cart_hash()

	/**
	 * Get the session table name.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function get_table_name() {
		return $this->_table;
	} // END get_table_name()

} // END class
