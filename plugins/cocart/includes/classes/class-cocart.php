<?php
/**
 * CoCart core setup.
 *
 * @author  Sébastien Dumont
 * @package CoCart
 * @since   2.6.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart;

use CoCart\Help;
use CoCart\Install;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main CoCart class.
 *
 * @class CoCart\Core
 */
final class Core {

	/**
	 * Plugin Version
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $version = '4.0.0-alpha.4';

	/**
	 * CoCart Database Schema version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @var string
	 */
	public static $db_version = '4.0.0';

	/**
	 * Required WordPress Version
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.3.0 Introduced.
	 *
	 * @var string
	 */
	public static $required_wp = '5.6';

	/**
	 * Required WooCommerce Version
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $required_woo = '6.9';

	/**
	 * Required PHP Version
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $required_php = '7.4';

	/**
	 * Initiate CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.1.2
	 */
	public static function init() {
		self::setup_constants();
		self::includes();

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

		/**
		 * Fires once CoCart has finished loading.
		 *
		 * @since 1.0.0 Introduced.
		 */
		do_action( 'cocart_init' );
	} // END init()

	/**
	 * Setup Constants
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.2.0 Introduced.
	 * @version 4.0.0
	 */
	public static function setup_constants() {
		self::define( 'COCART_ABSPATH', dirname( COCART_FILE ) . '/' );
		self::define( 'COCART_PLUGIN_BASENAME', plugin_basename( COCART_FILE ) );
		self::define( 'COCART_VERSION', self::$version );
		self::define( 'COCART_DB_VERSION', self::$db_version );
		self::define( 'COCART_SLUG', 'cart-rest-api-for-woocommerce' );
		self::define( 'COCART_CART_CACHE_GROUP', 'cocart_cart_id' );
		self::define( 'COCART_NEXT_VERSION', '5.0.0' );
	} // END setup_constants()

	/**
	 * Define constant if not already set.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 1.2.0 Introduced.
	 *
	 * @param string      $name Name of constant.
	 * @param string|bool $value Value of constant.
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
	 *
	 * @static
	 *
	 * @since 3.0.8 Introduced.
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'CoCart';
	} // END get_name()

	/**
	 * Return the version of the package.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.8 Introduced.
	 *
	 * @return string
	 */
	public static function get_version() {
		return self::$version;
	} // END get_version()

	/**
	 * Return the path to the package.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.8 Introduced.
	 *
	 * @return string
	 */
	public static function get_path() {
		return dirname( __DIR__ );
	} // END get_path()

	/**
	 * Includes required core files.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.0.0 Introduced.
	 * @version 4.0.0
	 *
	 * @return void
	 */
	public static function includes() {
		// Class autoloader.
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-autoloader.php';

		// Abstracts.
		include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-extension-callback.php';

		// Important functions.
		include_once COCART_ABSPATH . 'includes/cocart-background-functions.php';
		include_once COCART_ABSPATH . 'includes/cocart-core-functions.php';
		include_once COCART_ABSPATH . 'includes/cocart-deprecated-functions.php';
		include_once COCART_ABSPATH . 'includes/cocart-formatting-functions.php';

		// Core classes.
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-authentication.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-status.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-helpers.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-install.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-logger.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-session.php';

		// REST API functions.
		include_once COCART_ABSPATH . 'includes/cocart-rest-functions.php';

		// WP-CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			include_once COCART_ABSPATH . 'includes/classes/class-cocart-cli.php';
		}

		/**
		 * Load backend features only if COCART_WHITE_LABEL constant is
		 * NOT set or IS set to false in user's wp-config.php file.
		 */
		if ( Help::is_white_labelled() && is_admin() ) {
			include_once COCART_ABSPATH . 'includes/classes/admin/class-cocart-wc-admin-system-status.php';
		}
	} // END includes()

	/**
	 * CoCart Background Updater.
	 *
	 * Called using the "woocommerce_loaded" hook to allow the use of
	 * WooCommerce constants.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return void
	 */
	public static function background_updater() {
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-background-updater.php';
	} // END background_updater()

	/**
	 * Install CoCart upon activation.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.7.0 Added skip check parameter. Default is false.
	 *
	 * @param bool $skip_check Whether to skip the activation check. Default is false.
	 */
	public static function install_cocart( $skip_check = false ) {
		if ( $skip_check ) {
			self::activation_check();
		}

		Install::install();
	} // END install_cocart()

	/**
	 * Checks the server environment and other factors and deactivates the plugin if necessary.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 */
	public static function activation_check() {
		if ( ! Help::is_environment_compatible( self::$required_php ) ) {
			self::deactivate_plugin();
			/* translators: %1$s: CoCart, %2$s: Environment message */
			wp_die( sprintf( esc_html__( '%1$s could not be activated. %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart', Help::get_environment_message( self::$required_php ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		if ( Help::is_cocart_pro_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			/* translators: %1$s: CoCart, %2$s: CoCart Pro */
			wp_die( sprintf( esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ), 'CoCart', 'CoCart Pro' ) );
		}
	} // END activation_check()

	/**
	 * Deactivates the plugin if the environment is not ready.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
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
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 */
	public static function load_rest_api() {
		// Abstracts.
		include_once COCART_ABSPATH . 'includes/classes/rest-api/schemas/abstract-cocart-schema.php';
		include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-rest-controller.php';
		include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-cart-rest-controller.php';

		// CoCart REST API.
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-cart-cache.php';
		// include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-cart-callbacks.php';
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-cart-extension.php';
		//include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-response.php';
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-cart-formatting.php';
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-cart-validation.php';
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-rest-product-validation.php';
		include_once COCART_ABSPATH . 'includes/classes/rest-api/class-cocart-server.php';
	} // END load_rest_api()

	/**
	 * Filters the session handler to replace with our own.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.2 Introduced.
	 * @since 4.0.0 Added session upgrade check to determine the type of session handler to use.
	 *
	 * @param string $handler WooCommerce Session Handler.
	 *
	 * @return string $handler CoCart Session Handler.
	 */
	public static function session_handler( $handler ) {
		if ( class_exists( 'WC_Session' ) ) {
			include_once COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';

			$current_db_version = get_option( 'cocart_db_version', null );
			$session_upgraded   = get_option( 'cocart_session_upgraded', '' );

			if ( version_compare( $current_db_version, COCART_DB_VERSION, '==' ) && $session_upgraded === COCART_DB_VERSION ) {
				include_once COCART_ABSPATH . 'includes/classes/class-cocart-session-handler.php';
			} else {
				include_once COCART_ABSPATH . 'includes/classes/legacy/class-cocart-session-handler.php';
			}

			$handler = '\CoCart\Session\Handler';
		}

		return $handler;
	} // END session_handler()

	/**
	 * Includes CoCart tasks.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.2 Introduced.
	 *
	 * @return void
	 */
	public static function cocart_tasks() {
		include_once COCART_ABSPATH . 'includes/cocart-task-functions.php';
	} // END cocart_tasks()

	/**
	 * Includes WooCommerce tweaks.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @return void
	 */
	public static function woocommerce() {
		include_once COCART_ABSPATH . 'includes/utilities/class-cocart-utilities-rate-limits.php';
		include_once COCART_ABSPATH . 'includes/classes/class-cocart-woocommerce.php';
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
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.0.0 Introduced.
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
