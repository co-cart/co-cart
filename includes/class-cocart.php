<?php
/**
 * CoCart core setup.
 *
 * @author   Sébastien Dumont
 * @category Package
 * @since    2.6.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main CoCart class.
 *
 * @class CoCart
 */
final class CoCart {

	/**
	 * Plugin Version
	 *
	 * @access public
	 * @static
	 */
	public static $version = '2.6.0-beta.1';

	/**
	 * Required WordPress Version
	 *
	 * @access public
	 * @static
	 * @since  2.3.0
	 */
	public static $required_wp = '5.2';

	/**
	 * Required WooCommerce Version
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.1.0
	 */
	public static $required_woo = '4.0.0';

	/**
	 * Initiate CoCart.
	 *
	 * @access public
	 */
	public static function init() {
		self::setup_constants();
		self::includes();

		// Setup WooCommerce.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'woocommerce' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Includes setup for CoCart, notices and admin pages.
		add_action( 'init', array( __CLASS__, 'admin_includes' ) );

		// Load REST API.
		add_action( 'init', array( __CLASS__, 'load_rest_api' ) );

		// Force WooCommerce to accept CoCart requests when authenticating.
		//add_filter( 'woocommerce_rest_is_request_to_rest_api', array( __CLASS__, 'allow_cocart_requests_wc' ) );

		// Loads cart from session.
		//add_action( 'woocommerce_load_cart_from_session', array( __CLASS__, 'load_cart_from_session' ), 0 );

		// Init action.
		do_action( 'cocart_init' );
	} // END init()

	/**
	 * Setup Constants
	 *
	 * @access  public
	 * @since   1.2.0
	 * @version 2.6.0
	 */
	public static function setup_constants() {
		self::define( 'COCART_ABSPATH', dirname( COCART_FILE ) . '/' );
		self::define( 'COCART_PLUGIN_BASENAME', plugin_basename( COCART_FILE ) );
		self::define( 'COCART_VERSION', self::$version );
		self::define( 'COCART_SLUG', 'cart-rest-api-for-woocommerce' );
		self::define( 'COCART_URL_PATH', untrailingslashit( plugins_url( '/', COCART_FILE ) ) );
		self::define( 'COCART_FILE_PATH', untrailingslashit( plugin_dir_path( COCART_FILE ) ) );
		self::define( 'COCART_CART_CACHE_GROUP', 'cocart_cart_id' );
		self::define( 'COCART_STORE_URL', 'https://cocart.xyz/' );
		self::define( 'COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/' );
		self::define( 'COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce' );
		self::define( 'COCART_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/' );
		self::define( 'COCART_DOCUMENTATION_URL', 'https://docs.cocart.xyz' );
		self::define( 'COCART_TRANSLATION_URL', 'https://translate.cocart.xyz/projects/cart-rest-api-for-woocommerce/' );
		self::define( 'COCART_NEXT_VERSION', '3.0.0' );
	} // END setup_constants()

	/**
	 * Define constant if not already set.
	 *
	 * @access private
	 * @since  1.2.0
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	} // END define()

	/**
	 * Includes required core files.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @version 2.6.0
	 * @return  void
	 */
	public static function includes() {
		include_once COCART_ABSPATH . '/includes/class-cocart-autoloader.php';
		include_once COCART_ABSPATH . '/includes/class-cocart-helpers.php';
		include_once COCART_ABSPATH . '/includes/class-cocart-logger.php';
		include_once COCART_ABSPATH . '/includes/class-cocart-product-validation.php';
		include_once COCART_ABSPATH . '/includes/class-cocart-session.php';
		require_once COCART_ABSPATH . '/includes/class-cocart-install.php';
	} // END includes()

	/**
	 * Load REST API.
	 *
	 * @access public
	 * @static
	 * @since  2.6.0
	 */
	public static function load_rest_api() {
		include_once COCART_ABSPATH . '/includes/class-cocart-init.php';
	} // END load_rest_api()

	/**
	 * Include WooCommerce tweaks and new session handler.
	 *
	 * @access  public
	 * @since   2.1.2
	 * @version 2.6.0
	 * @return  void
	 */
	public static function woocommerce() {
		include_once COCART_ABSPATH . '/includes/class-cocart-woocommerce.php';
		include_once COCART_ABSPATH . '/includes/class-cocart-session-handler.php';
	} // END woocommerce()

	/**
	 * Include admin classes to handle all back-end functions.
	 *
	 * @access  public
	 * @since   1.2.2
	 * @version 2.3.1
	 * @return  void
	 */
	public static function admin_includes() {
		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			include_once COCART_ABSPATH . '/includes/admin/class-cocart-admin.php';
		}
	} // END admin_includes()

	/**
	 * Force WooCommerce to accept CoCart API requests when authenticating.
	 *
	 * @access  public
	 * @static
	 * @since   2.0.5
	 * @version 2.6.0
	 * @param   bool $request
	 * @return  bool true|$request
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
	 * Loads guest or specific carts into session.
	 *
	 * @access  public
	 * @since   2.1.0
	 * @version 2.1.2
	 */
	public static function load_cart_from_session() {
		if ( ! WC()->session instanceof CoCart_Session_Handler ) {
			return;
		}

		$customer_id = strval( get_current_user_id() );

		// Load cart for guest or specific cart.
		if ( is_numeric( $customer_id ) && $customer_id < 1 ) {
			$cookie = WC()->session->get_session_cookie();

			// If cookie exists then return customer ID from it.
			if ( $cookie ) {
				$customer_id = $cookie[0];
			}

			// Check if we requested to load a specific cart.
			if ( isset( $_REQUEST['cart_key'] ) || isset( $_REQUEST['id'] ) ) {
				$cart_id = isset( $_REQUEST['cart_key'] ) ? $_REQUEST['cart_key'] : $_REQUEST['id'];

				// Set customer ID in session.
				$customer_id = $cart_id;
			}

			// Get cart for customer.
			$cart = WC()->session->get_cart( $customer_id );

			// Set cart for customer if not empty.
			if ( ! empty( $cart ) ) {
				WC()->session->set( 'cart', maybe_unserialize( $cart[ 'cart' ] ) );
				WC()->session->set( 'cart_totals', maybe_unserialize( $cart[ 'cart_totals' ] ) );
				WC()->session->set( 'applied_coupons', maybe_unserialize( $cart[ 'applied_coupons' ] ) );
				WC()->session->set( 'coupon_discount_totals', maybe_unserialize( $cart[ 'coupon_discount_totals' ] ) );
				WC()->session->set( 'coupon_discount_tax_totals', maybe_unserialize( $cart[ 'coupon_discount_tax_totals' ] ) );
				WC()->session->set( 'removed_cart_contents', maybe_unserialize( $cart[ 'removed_cart_contents' ] ) );

				if ( ! empty( $cart['cart_fees'] ) ) {
					WC()->session->set( 'cart_fees', maybe_unserialize( $cart[ 'cart_fees' ] ) );
				}
			}
		}
	} // END load_cart_from_session()

	/**
	 * Load the plugin translations if any ready.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce-LOCALE.mo
	 *      - WP_LANG_DIR/plugins/cart-rest-api-for-woocommerce-LOCALE.mo
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.6.0
	 */
	public static function load_plugin_textdomain() {
		if ( function_exists( 'determine_locale' ) ) {
			$locale = determine_locale();
		} else {
			$locale = is_admin() ? get_user_locale() : get_locale();
		}

		$locale = apply_filters( 'plugin_locale', $locale, COCART_SLUG );

		unload_textdomain( COCART_SLUG );
		load_textdomain( COCART_SLUG, WP_LANG_DIR . '/cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce-' . $locale . '.mo' );
		load_plugin_textdomain( COCART_SLUG, false, plugin_basename( dirname( COCART_FILE ) ) . '/languages' );
	} // END load_plugin_textdomain()

} // END class
