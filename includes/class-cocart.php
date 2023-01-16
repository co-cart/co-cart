<?php
/**
 * CoCart core setup.
 *
 * @author  Sébastien Dumont
 * @package CoCart
 * @since   2.6.0
 * @version 3.1.0
 * @license GPL-2.0+
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
	 * @var string
	 */
	public static $version = '3.7.11';

	/**
	 * CoCart Database Schema version.
	 *
	 * @access public
	 * @static
	 * @since  3.0.0 started with version string 3.0.0
	 * @var    string
	 */
	public static $db_version = '3.0.0';

	/**
	 * Required WordPress Version
	 *
	 * @access public
	 * @static
	 * @since  2.3.0
	 * @var    string
	 */
	public static $required_wp = '5.6';

	/**
	 * Required WooCommerce Version
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.1.0
	 * @var     string
	 */
	public static $required_woo = '4.3';

	/**
	 * Required PHP Version
	 *
	 * @access public
	 * @static
	 * @since  2.6.0
	 * @var    string
	 */
	public static $required_php = '7.3';

	/**
	 * Initiate CoCart.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 3.1.2
	 */
	public static function init() {
		self::setup_constants();
		self::includes();
		self::include_extension_compatibility();
		self::include_third_party();

		// Install CoCart upon activation.
		register_activation_hook( COCART_FILE, array( __CLASS__, 'install_cocart' ) );

		// Setup CoCart Session Handler.
		add_filter( 'woocommerce_session_handler', array( __CLASS__, 'session_handler' ) );

		// Setup WooCommerce and CoCart.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'cocart_tasks' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'woocommerce' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'background_updater' ) );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Load REST API.
		add_action( 'init', array( __CLASS__, 'load_rest_api' ) );

		// Initialize action.
		do_action( 'cocart_init' );
	} // END init()

	/**
	 * Setup Constants
	 *
	 * @access  public
	 * @static
	 * @since   1.2.0
	 * @version 3.0.0
	 */
	public static function setup_constants() {
		self::define( 'COCART_ABSPATH', dirname( COCART_FILE ) . '/' );
		self::define( 'COCART_PLUGIN_BASENAME', plugin_basename( COCART_FILE ) );
		self::define( 'COCART_VERSION', self::$version );
		self::define( 'COCART_DB_VERSION', self::$db_version );
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
	 * @static
	 * @since  1.2.0
	 * @param  string      $name Name of constant.
	 * @param  string|bool $value Value of constant.
	 */
	private static function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	} // END define()

	/**
	 * Return the name of the package.
	 *
	 * @access public
	 * @static
	 * @since  3.0.8
	 * @return string
	 */
	public static function get_name() {
		return 'CoCart';
	} // END get_name()

	/**
	 * Return the version of the package.
	 *
	 * @access public
	 * @static
	 * @since  3.0.8
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	} // END get_version()

	/**
	 * Return the path to the package.
	 *
	 * @access public
	 * @static
	 * @since  3.0.8
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	} // END get_path()

	/**
	 * Includes required core files.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 3.7.0
	 * @return  void
	 */
	public static function includes() {
		// Class autoloader.
		include_once COCART_ABSPATH . 'includes/class-cocart-autoloader.php';

		// Abstracts.
		include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-extension-callback.php';

		// Important functions.
		include_once COCART_ABSPATH . 'includes/cocart-background-functions.php';
		include_once COCART_ABSPATH . 'includes/cocart-formatting-functions.php';

		// Core classes.
		include_once COCART_ABSPATH . 'includes/class-cocart-api.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-authentication.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-cart-cache.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-cart-callbacks.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-cart-extension.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-helpers.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-install.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-logger.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-response.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-cart-formatting.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-cart-validation.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-product-validation.php';
		include_once COCART_ABSPATH . 'includes/class-cocart-session.php';

		// REST API functions.
		include_once COCART_ABSPATH . 'includes/cocart-rest-functions.php';

		// WP-CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			include_once COCART_ABSPATH . 'includes/class-cocart-cli.php';
		}

		/**
		 * Load backend features only if COCART_WHITE_LABEL constant is
		 * NOT set or IS set to false in user's wp-config.php file.
		 */
		if (
			! defined( 'COCART_WHITE_LABEL' ) ||
			false === COCART_WHITE_LABEL && is_admin() ||
			( defined( 'WP_CLI' ) && WP_CLI )
		) {
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin.php';
		} else {
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-system-status.php';
		}
	} // END includes()

	/**
	 * CoCart Background Updater.
	 *
	 * Called using the "woocommerce_loaded" hook to allow the use of
	 * WooCommerce constants.
	 *
	 * @access public
	 * @static
	 * @since  3.0.0
	 * @return void
	 */
	public static function background_updater() {
		include_once COCART_ABSPATH . 'includes/class-cocart-background-updater.php';
	} // END background_updater()

	/**
	 * Include extension compatibility.
	 *
	 * @access public
	 * @static
	 * @since  3.0.0
	 */
	public static function include_extension_compatibility() {
		include_once COCART_ABSPATH . 'includes/compatibility/class-cocart-compatibility.php';
	} // END include_extension_compatibility()

	/**
	 * Include third party support.
	 *
	 * @access public
	 * @static
	 * @since  2.8.1
	 */
	public static function include_third_party() {
		include_once COCART_ABSPATH . 'includes/third-party/class-cocart-third-party.php';
	} // END include_third_party()

	/**
	 * Install CoCart upon activation.
	 *
	 * @access  public
	 * @static
	 * @since   3.0.0
	 * @version 3.7.2
	 * @param bool $skip_check Whether to skip the activation check. Default is false.
	 */
	public static function install_cocart( $skip_check = false ) {
		if ( $skip_check ) {
			self::activation_check();
		}

		CoCart_Install::install();
	} // END install_cocart()

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
			/* translators: %1$s: CoCart, %2$s: Environment message */
			wp_die( sprintf( esc_html__( '%1$s could not be activated. %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart', CoCart_Helpers::get_environment_message() ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( CoCart_Helpers::is_cocart_pro_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			/* translators: %1$s: CoCart Lite, %2$s: CoCart Pro */
			wp_die( sprintf( esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart Lite', 'CoCart Pro' ) );
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

		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			unset( $_GET['activate'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
	} // END deactivate_plugin()

	/**
	 * Load REST API.
	 *
	 * @access  public
	 * @static
	 * @since   2.6.0
	 * @version 3.0.0
	 */
	public static function load_rest_api() {
		include_once COCART_ABSPATH . 'includes/class-cocart-rest-api.php';
	} // END load_rest_api()

	/**
	 * Filters the session handler to replace with our own.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.2
	 * @version 3.1.0
	 * @param   string $handler WooCommerce Session Handler.
	 * @return  string $handler CoCart Session Handler.
	 */
	public static function session_handler( $handler ) {
		if ( class_exists( 'WC_Session' ) ) {
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
			include_once COCART_ABSPATH . 'includes/class-cocart-session-handler.php';
			$handler = 'CoCart_Session_Handler';
		}

		return $handler;
	} // END session_handler()

	/**
	 * Includes CoCart tasks.
	 *
	 * @access public
	 * @static
	 * @since  3.1.2 Introduced.
	 * @return void
	 */
	public static function cocart_tasks() {
		include_once COCART_ABSPATH . 'includes/cocart-task-functions.php';
	} // END cocart_tasks()

	/**
	 * Includes WooCommerce tweaks.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.2
	 * @version 3.0.0
	 * @return  void
	 */
	public static function woocommerce() {
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
