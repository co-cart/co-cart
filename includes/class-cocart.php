<?php
/**
 * CoCart core setup.
 *
 * @author  Sébastien Dumont
 * @package CoCart
 * @since   2.6.0
 * @version 4.0.0
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
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $version = '4.0.1';

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
	public static $db_version = '3.0.0';

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
	 * @since 1.0.0 Introduced.
	 *
	 * @var string
	 */
	public static $required_woo = '4.3';

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
	 * Cloning is forbidden.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden.', 'cart-rest-api-for-woocommerce' ), '3.10.0' );
	} // END __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cart-rest-api-for-woocommerce' ), '3.10.0' );
	} // END __wakeup()

	/**
	 * Initiate CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
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

		/**
		 * Hook: Fires once CoCart has finished loading.
		 *
		 * @since 3.0.0 Introduced.
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
		self::define( 'COCART_STORE_URL', 'https://cocartapi.com/' );
		self::define( 'COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/' );
		self::define( 'COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce' );
		self::define( 'COCART_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/' );
		self::define( 'COCART_COMMUNITY_URL', 'https://cocartapi.com/community/' );
		self::define( 'COCART_DOCUMENTATION_URL', 'https://docs.cocart.xyz' );
		self::define( 'COCART_TRANSLATION_URL', 'https://translate.cocart.xyz/projects/cart-rest-api-for-woocommerce/' );
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
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.10.0 Introduced.
	 *
	 * @param string $file    The file we are getting the modified time from.
	 * @param string $version A version number, handy for plugins to make use of this method.
	 *
	 * @return string
	 */
	public static function get_file_version( $file, $version = '' ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}

		return $version ? $version : self::$version;
	} // END get_file_version()

	/**
	 * Includes required core files.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   1.0.0 Introduced.
	 * @version 3.11.0
	 *
	 * @return void
	 */
	public static function includes() {
		// Class autoloader.
		include_once __DIR__ . '/class-cocart-autoloader.php';

		// Polyfill Functions - Must be included before everything else.
		include_once __DIR__ . '/cocart-polyfill-functions.php';

		// Abstracts.
		include_once __DIR__ . '/abstracts/abstract-cocart-extension-callback.php';

		// Important functions.
		include_once __DIR__ . '/cocart-background-functions.php';
		include_once __DIR__ . '/cocart-formatting-functions.php';

		// Core classes.
		require_once __DIR__ . '/class-cocart-api.php';
		require_once __DIR__ . '/class-cocart-authentication.php';
		require_once __DIR__ . '/class-cocart-cart-cache.php';
		require_once __DIR__ . '/class-cocart-cart-callbacks.php';
		require_once __DIR__ . '/class-cocart-cart-extension.php';
		require_once __DIR__ . '/class-cocart-helpers.php';
		require_once __DIR__ . '/class-cocart-install.php';
		require_once __DIR__ . '/class-cocart-logger.php';
		require_once __DIR__ . '/class-cocart-response.php';
		require_once __DIR__ . '/class-cocart-cart-formatting.php';
		require_once __DIR__ . '/class-cocart-cart-validation.php';
		require_once __DIR__ . '/class-cocart-product-validation.php';
		require_once __DIR__ . '/class-cocart-session.php';

		// REST API functions.
		include_once __DIR__ . '/cocart-rest-functions.php';

		// WP-CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once __DIR__ . '/class-cocart-cli.php';
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
			require_once __DIR__ . '/admin/class-cocart-admin.php';
		} else {
			require_once __DIR__ . '/admin/class-cocart-wc-admin-system-status.php';
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
		require_once __DIR__ . '/class-cocart-background-updater.php';
	} // END background_updater()

	/**
	 * Include extension compatibility.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 */
	public static function include_extension_compatibility() {
		require_once __DIR__ . '/compatibility/class-cocart-compatibility.php';
	} // END include_extension_compatibility()

	/**
	 * Include third party support.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.8.1 Introduced.
	 */
	public static function include_third_party() {
		require_once __DIR__ . '/third-party/class-cocart-third-party.php';
	} // END include_third_party()

	/**
	 * Install CoCart upon activation.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.7.2
	 *
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
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 * @since 3.10.4 Added a check for CoCart Plus.
	 */
	public static function activation_check() {
		if ( ! CoCart_Helpers::is_environment_compatible() ) {
			self::deactivate_plugin();
			wp_die(
				sprintf(
					/* translators: %1$s: CoCart, %2$s: Environment message */
					esc_html__( '%1$s could not be activated. %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					CoCart_Helpers::get_environment_message() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				)
			);
		}

		if ( CoCart_Helpers::is_cocart_plus_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			wp_die(
				sprintf(
					/* translators: %1$s: CoCart Core, %2$s: CoCart Plus */
					esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'CoCart Plus'
				)
			);
		}

		if ( CoCart_Helpers::is_cocart_pro_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			wp_die(
				sprintf(
					/* translators: %1$s: CoCart Core, %2$s: CoCart Pro */
					esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'CoCart Pro'
				)
			);
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
	 * @since 2.6.0  Introduced.
	 * @since 3.10.0 Added security for added protection.
	 */
	public static function load_rest_api() {
		require_once __DIR__ . '/class-cocart-rest-api.php';
		require_once __DIR__ . '/class-cocart-security.php';
	} // END load_rest_api()

	/**
	 * Filters the session handler to replace with our own.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.2 Introduced.
	 *
	 * @param string $handler WooCommerce Session Handler.
	 *
	 * @return string $handler CoCart Session Handler.
	 */
	public static function session_handler( $handler ) {
		if ( class_exists( 'WC_Session' ) ) {
			include_once __DIR__ . '/abstracts/abstract-cocart-session.php';
			require_once __DIR__ . '/class-cocart-session-handler.php';
			$handler = 'CoCart_Session_Handler';
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
		include_once __DIR__ . '/cocart-task-functions.php';
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
		require_once __DIR__ . '/class-cocart-woocommerce.php';
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
