<?php
/**
 * Class: CoCart_Session_Handler.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles session data for the cart.
 *
 * Our session handler extends "WC_Session_Handler" class and accommodates the
 * required support for handling a customers cart session via the REST API
 * for a true headless experience.
 *
 * @since 2.1.0 Introduced.
 */
class CoCart_Session_Handler extends WC_Session_Handler {

	/**
	 * Stores cart in use.
	 *
	 * @access protected
	 *
	 * @var string Cart key identifier.
	 */
	protected $cart_key = '';

	/**
	 * Stores cart expiry.
	 *
	 * @access protected
	 *
	 * @var string Cart due to expire timestamp.
	 */
	protected $cart_expiring;

	/**
	 * Stores cart due to expire timestamp.
	 *
	 * @access protected
	 *
	 * @var string Cart expiration timestamp.
	 */
	protected $cart_expiration;

	/**
	 * Stores cart source.
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @var string Cart source.
	 */
	protected $cart_source;

	/**
	 * Stores cart hash.
	 *
	 * @access protected
	 *
	 * @var string $cart_hash Cart hash.
	 */
	protected $cart_hash;

	/**
	 * Constructor for the session class.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		parent::__construct();

		// Override table used for sessions.
		$this->_table = $GLOBALS['wpdb']->prefix . 'cocart_carts';
	}

	/**
	 * Init hooks and cart data.
	 *
	 * @uses CoCart::is_rest_api_request()
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.2.0 Rest requests don't require the use of cookies.
	 */
	public function init() {
		// Load the session based on native or decoupled request.
		if ( CoCart::is_rest_api_request() ) {
			$this->cart_source = 'cocart';

			$this->init_session_cocart();
			$this->set_cart_hash();

			add_action( 'shutdown', array( $this, 'save_data' ), 20 );
			add_action( 'wp_logout', array( $this, 'destroy_cart' ) );
		} else {
			$this->cart_source = 'woocommerce';
			parent::init();
		}
	} // END init()

	/**
	 * Get requested cart.
	 *
	 * Returns the cart key requested from parameters, via header, or WP-CLI command.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return string Cart key.
	 */
	public function get_requested_cart() {
		// Are we requesting via url parameter?
		if ( isset( $_REQUEST['cart_key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->cart_key = (string) trim( sanitize_key( wp_unslash( $_REQUEST['cart_key'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Are we requesting via custom header? - Old method
		if ( ! empty( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->cart_key = (string) trim( sanitize_key( wp_unslash( $_SERVER['HTTP_COCART_API_CART_KEY'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		// Are we requesting via custom header?
		if ( ! empty( $_SERVER['HTTP_CART_KEY'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$this->cart_key = (string) trim( sanitize_key( wp_unslash( $_SERVER['HTTP_CART_KEY'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}

		/**
		 * Filter allows the cart key to be overridden.
		 *
		 * Developer Note: Really only here so I don't have to create
		 * a new session handler to control the cart requested.
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @ignore Function ignored when parsed into Code Reference.
		 */
		$this->cart_key = apply_filters( 'cocart_requested_cart_key', $this->cart_key );

		return $this->cart_key;
	} // END get_requested_cart()

	/**
	 * Setup cart session.
	 *
	 * Cart session is decoupled without the use of a cookie.
	 *
	 * Supports customers guest and registered. It also allows
	 * administrators to create a cart session and associate a
	 * registered customer.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 */
	public function init_session_cocart() {
		// Current user ID. If user is NOT logged in then the customer is a guest.
		$current_user_id = 0;

		if ( is_user_logged_in() ) {
			$current_user_id = strval( get_current_user_id() );
		}

		$this->cart_key = $this->get_requested_cart();

		// Get cart session requested.
		if ( ! empty( $this->cart_key ) ) {
			// Get cart.
			$this->_data = $this->get_session_data();

			// If the user logs in, and there is a requested cart that is not a customer, then update session configuration.
			if ( is_user_logged_in() && ! empty( $this->_customer_id ) && ! $this->is_user_customer( $this->cart_key ) && $current_user_id !== $this->cart_key ) {
				$guest_session_id = $this->cart_key;
				$this->cart_key   = $current_user_id;
				$this->save_data( $guest_session_id );
			}

			// Update cart if its close to expiring.
			if ( $this->is_session_expiring() ) {
				$this->set_cart_expiration();
				$this->update_cart_timestamp( $this->cart_key, $this->cart_expiration );
			}
		} else {
			// New cart session created or authenticated user.
			$this->set_cart_expiration();
			$this->cart_key = 0 === $current_user_id ? $this->generate_key() : $current_user_id;
			$this->_data    = $this->get_session_data();
		}
	} // END init_session_cocart()

	/**
	 * Detect if the user is a customer.
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @param int $user_id The user ID.
	 *
	 * @return bool Returns true if user is a customer, otherwise false.
	 */
	public function is_user_customer( $user_id ) {
		if ( ! is_numeric( $user_id ) || 0 === $user_id ) {
			return false;
		}

		$current_user = get_userdata( $user_id );

		if ( ! empty( $current_user ) ) {
			$user_roles = $current_user->roles;

			if ( in_array( 'customer', $user_roles, true ) ) {
				return true;
			}
		}

		return false;
	} // END is_user_customer()

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
		// If we are loading a session via REST API then identify cart key.
		if ( ! empty( $this->cart_key ) && CoCart::is_rest_api_request() ) {
			return true;
		}

		if ( parent::has_session() ) {
			return true;
		}

		return false;
	} // END has_session()

	/**
	 * Checks if the session is expiring.
	 *
	 * @access private
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @return bool Whether session is expiring.
	 */
	private function is_session_expiring() {
		return time() > $this->cart_expiration || empty( $this->cart_expiring );
	} // END is_session_expiring()

	/**
	 * Set cart expiration.
	 *
	 * This session expiration is used for the REST API.
	 *
	 * For logged in users sessions renew daily and expire in a week. This is to keep carts persistent for logged in users.
	 * For guests, sessions expire in 48 hours.
	 *
	 * @access public
	 */
	public function set_cart_expiration() {
		$expiring_seconds   = DAY_IN_SECONDS;
		$expiration_seconds = 2 * DAY_IN_SECONDS;

		// Set expiration time for logged in users.
		if ( is_user_logged_in() ) {
			$expiration_seconds = WEEK_IN_SECONDS;
		}

		/**
		 * Filter allows you to change the amount of time before the cart starts to expire.
		 *
		 * @since 2.1.0 Introduced.
		 * @since 4.4.0 Added the parameter if user is logged in.
		 *
		 * @param int  $expiring_seconds  The expiration time in seconds.
		 * @param bool $is_user_logged_in Whether the user is logged in or not.
		 */
		$this->cart_expiring = time() + intval( apply_filters( 'cocart_cart_expiring', $expiring_seconds, is_user_logged_in() ) );

		/**
		 * Filter allows you to change the amount of time before the cart does expire.
		 *
		 * @since 2.1.0 Introduced.
		 * @since 4.4.0 Added the parameter if user is logged in.
		 *
		 * @param int  $expiration_seconds The expiration time in seconds.
		 * @param bool $is_user_logged_in  Whether the user is logged in or not.
		 */
		$this->cart_expiration = time() + intval( apply_filters( 'cocart_cart_expiration', $expiration_seconds, is_user_logged_in() ) );
	} // END set_cart_expiration()

	/**
	 * Generate a unique key.
	 *
	 * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
	 *
	 * @access public
	 *
	 * @since 4.2.0 Introduced.
	 *
	 * @return string A unique key.
	 */
	public function generate_key() {
		require_once ABSPATH . 'wp-includes/class-phpass.php';

		$hasher = new \PasswordHash( 8, false );
		/**
		 * Filter allows you to change the generated key.
		 *
		 * @since 4.2.0 Introduced.
		 *
		 * @param PasswordHash $hasher PHPass object.
		 */
		$generated_key = 't_' . apply_filters( 'cocart_generate_key', substr( md5( $hasher->get_random_bytes( 32 ) ), 2 ), $hasher );

		return $generated_key;
	} // END generate_key()

	/**
	 * Get session data.
	 *
	 * @return array
	 */
	public function get_session_data() {
		return $this->has_session() ? (array) $this->get_session( $this->cart_key, array() ) : array();
	}

	/**
	 * Gets a cache prefix.
	 *
	 * This is used in cart names so the entire cache can be invalidated with 1 function call.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cache_prefix() {
		return CoCart_Utilities_Cache_Helpers::get_cache_prefix( COCART_CART_CACHE_GROUP );
	} // END get_cache_prefix()

	/**
	 * Save data and delete guest session.
	 *
	 * @access public
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @param string|mixed $old_cart_key Optional cart key prior to user log-in. If $old_cart_key is not tied
	 *                                   to a user, the session will be deleted with the assumption that it was migrated
	 *                                   to the current session being saved.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function save_data( $old_cart_key = '' ) {
		if ( $this->has_session() ) {
			global $wpdb;

			// Check the data exists before continuing.
			if ( ! $this->_data || empty( $this->_data ) || is_null( $this->_data ) ) {
				return true;
			}

			// Check the source to determine cart expiration to utilize.
			if ( 'cocart' === $this->cart_source ) {
				$cart_expiration = (int) $this->cart_expiration;
			} else {
				$cart_expiration = (int) $this->_session_expiration;
			}

			/**
			 * Filter source of cart.
			 *
			 * @since 3.0.0 Introduced.
			 *
			 * @param string $cart_source
			 */
			$cart_source = apply_filters( 'cocart_cart_source', $this->cart_source );

			/**
			 * Set the cart hash.
			 *
			 * @since 3.0.0 Introduced.
			 */
			$this->set_cart_hash();

			// Save or update cart data.
			$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					'INSERT INTO %i (`cart_key`, `cart_value`, `cart_created`, `cart_expiry`, `cart_source`, `cart_hash`) VALUES (%s, %s, %d, %d, %s, %s)
 					ON DUPLICATE KEY UPDATE `cart_value` = VALUES(`cart_value`), `cart_expiry` = VALUES(`cart_expiry`), `cart_hash` = VALUES(`cart_hash`)',
					$this->_table,
					$this->cart_key,
					maybe_serialize( $this->_data ),
					time(),
					$cart_expiration,
					$cart_source,
					$this->cart_hash
				)
			);

			wp_cache_set( $this->get_cache_prefix() . $this->cart_key, $this->_data, COCART_CART_CACHE_GROUP, $cart_expiration - time() );

			/**
			 * Hook: Fires after session data is saved.
			 *
			 * @since 4.2.0 Introduced.
			 *
			 * @param int    $cart_key        Cart key identifier.
			 * @param array  $data            Cart data.
			 * @param int    $cart_expiration Cart expiration.
			 * @param string $cart_source     Cart source.
			 */
			do_action( 'cocart_after_session_saved_data', $this->cart_key, $this->_data, $cart_expiration, $cart_source );

			$this->_dirty = false;

			/**
			 * Ideally, the removal of guest session data migrated to a logged-in user would occur within
			 * parent::init_session_cookie() upon user login detection initially occurs. However, since some third-party
			 * extensions override this method, relocating this logic could break backward compatibility.
			 */
			if ( ! empty( $old_cart_key ) && $this->_customer_id !== $old_cart_key && ! is_object( get_user_by( 'id', $old_cart_key ) ) ) {
				$this->delete_cart( $old_cart_key );
			}
		}
	} // END save_data()

	/**
	 * Destroy all cart data.
	 *
	 * @access public
	 */
	public function destroy_cart() {
		$this->delete_cart( $this->cart_key );
		$this->forget_session();
	} // END destroy_cart()

	/**
	 * Overrides destroy session function so we use the
	 * correct column from our session table.
	 *
	 * @access public
	 *
	 * @since 3.0.13 Introduced.
	 */
	public function destroy_session() {
		$this->destroy_cart();
	} // END destroy_session()

	/**
	 * Cleanup cart data from the database and clear caches.
	 *
	 * @access public
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function cleanup_sessions() {
		global $wpdb;

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				'DELETE FROM %i WHERE cart_expiry < %d', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$this->_table,
				time()
			)
		);

		// Invalidate cache group.
		if ( class_exists( 'CoCart_Utilities_Cache_Helpers' ) ) {
			CoCart_Utilities_Cache_Helpers::invalidate_cache_group( COCART_CART_CACHE_GROUP );
		}
	} // END cleanup_sessions()

	/**
	 * Returns the session.
	 *
	 * @access public
	 *
	 * @param string $cart_key      The customer ID or cart key.
	 * @param mixed  $default_value Default cart value.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return string|array
	 */
	public function get_session( $cart_key, $default_value = false ) {
		global $wpdb;

		// There will be no sessions retrieved while WordPress setup is due.
		if ( defined( 'WP_SETUP_CONFIG' ) ) {
			return false;
		}

		// Try to get it from the cache, it will return false if not present or if object cache not in use.
		$value = wp_cache_get( $this->get_cache_prefix() . $cart_key, COCART_CART_CACHE_GROUP );

		if ( false === $value ) {
			$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
				$wpdb->prepare(
					'SELECT cart_value FROM %i WHERE cart_key = %s', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$this->_table,
					$cart_key
				)
			);

			if ( is_null( $value ) ) {
				$value = $default_value;
			}

			$cache_duration = $this->cart_expiration - time();
			if ( 0 < $cache_duration ) {
				wp_cache_add( $this->get_cache_prefix() . $cart_key, $value, COCART_CART_CACHE_GROUP, $cache_duration );
			}
		}

		return maybe_unserialize( $value );
	} // END get_session()

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

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->_table,
			array(
				'cart_value'  => maybe_serialize( $this->_data ),
				'cart_expiry' => (int) $this->cart_expiration,
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
		$wpdb->delete( $this->_table, array( 'cart_key' => $cart_key ), array( '%s' ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
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

		$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$this->_table,
			array( 'cart_expiry' => $timestamp ),
			array( 'cart_key' => $cart_key ),
			array( '%d' ),
			array( '%s' )
		);
	} // END update_cart_timestamp()

	/**
	 * Set the cart hash based on the carts contents and total.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 */
	public function set_cart_hash() {
		$cart_session = $this->get( 'cart' );
		$cart_totals  = $this->get( 'cart_totals' );

		$cart_total = ! empty( $cart_totals ) ? maybe_unserialize( $cart_totals ) : array( 'total' => 0 );
		$hash       = ! empty( $cart_session ) ? md5( wp_json_encode( $cart_session ) . $cart_total['total'] ) : '';

		$this->cart_hash = $hash;
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

	/**
	 * Get customer ID.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_key() {
		return $this->cart_key;
	} // END get_cart_key()

	/**
	 * Set customer ID.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param string $cart_key Customer ID.
	 */
	public function set_cart_key( $cart_key ) {
		$this->cart_key = $cart_key;
	} // END set_cart_key()

	/**
	 * Get cart hash
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_hash() {
		return $this->cart_hash;
	} // END get_cart_hash()

	/**
	 * Get cart is expiring.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_cart_is_expiring() {
		return $this->cart_expiring;
	} // END get_cart_is_expiring()

	/**
	 * Get carts expiration.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @return string
	 */
	public function get_carts_expiration() {
		return $this->cart_expiration;
	} // END get_carts_expiration()
} // END class
