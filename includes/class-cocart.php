<?php
/**
 * CoCart core setup.
 *
 * @author   Sébastien Dumont
 * @category Package
 * @since    2.6.0
 * @version  2.6.2
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
	public static $version = '2.7.0-rc.2';

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
	 * Required PHP Version
	 *
	 * @access public
	 * @static
	 * @since  2.6.0
	 */
	public static $required_php = '7.0';

	/**
	 * Initiate CoCart.
	 *
	 * @access public
	 * @static
	 */
	public static function init() {
		self::setup_constants();
		self::includes();

		// Environment checking when activating.
		register_activation_hook( COCART_FILE, array( __CLASS__, 'activation_check' ) );

		// Setup WooCommerce.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'woocommerce' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Load REST API.
		add_action( 'init', array( __CLASS__, 'load_rest_api' ) );

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
	 * @param  string      $name
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
		include_once COCART_ABSPATH . 'includes/class-cocart-autoloader.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-helpers.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-logger.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-product-validation.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-session.php';
		require_once COCART_ABSPATH . 'includes/class-cocart-install.php';
	} // END includes()

	/**
	 * Checks the server environment and other factors and deactivates the plugin if necessary.
	 *
	 * @access  public
	 * @static
	 * @since   2.6.0
	 * @version 2.6.2
	 */
	public static function activation_check() {
		if ( ! CoCart_Helpers::is_environment_compatible() ) {
			self::deactivate_plugin();
			wp_die( sprintf( __( '%1$s could not be activated. %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart', CoCart_Helpers::get_environment_message() ) );
		}

		if ( CoCart_Helpers::is_cocart_pro_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			wp_die( sprintf( __( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart Lite', 'CoCart Pro' ) );
		}
	} // END activation_check()

	/**
	 * Deactivates the plugin if the environment is not ready.
	 *
	 * @access public
	 * @static
	 * @since  2.6.0
	 */
	public static function deactivate_plugin() {
		deactivate_plugins( plugin_basename( COCART_FILE ) );

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	} // END deactivate_plugin()

	/**
	 * Load REST API.
	 *
	 * @access public
	 * @static
	 * @since  2.6.0
	 */
	public static function load_rest_api() {
		include_once COCART_ABSPATH . 'includes/class-cocart-authentication.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-init.php';
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
		include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-woocommerce.php';
	} // END woocommerce()

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
