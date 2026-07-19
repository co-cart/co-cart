<?php
/**
 * CoCart Community setup.
 *
 * @author  Sébastien Dumont
 * @package CoCart
 * @since   2.6.0
 * @version 4.9.0
 * @license GPL-3.0
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
	public static $version = '4.9.1';

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
	public static $db_version = '4.3.23';

	/**
	 * Tested up to WordPress version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @var string
	 */
	public static $tested_up_to_wp = '6.9';

	/**
	 * Required WordPress version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.3.0 Introduced.
	 *
	 * @var string
	 */
	public static $required_wp = '6.7';

	/**
	 * Required WooCommerce version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @var string
	 */
	public static $required_woo = '9.0';

	/**
	 * Required PHP version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $required_php = '8.2';

	/**
	 * Cloning is forbidden.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cloning this object is forbidden.', 'cocart-core' ), '3.10.0' );
	} // END __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Unserializing instances of this class is forbidden.', 'cocart-core' ), '3.10.0' );
	} // END __wakeup()

	/**
	 * Namespace for the API.
	 *
	 * @var string
	 */
	private static $api_namespace = 'cocart';

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

		// Install CoCart upon activation.
		register_activation_hook( COCART_FILE, array( __CLASS__, 'install_cocart' ) );
		add_filter( 'wp_plugin_dependencies_slug', array( __CLASS__, 'convert_plugin_dependency_slug' ) );

		// Maybe disable access to WP?
		add_action( 'template_redirect', array( __CLASS__, 'maybe_disable_wp_access' ), -10 );

		// Setup CoCart Session Handler.
		add_filter( 'woocommerce_session_handler', array( __CLASS__, 'session_handler' ) );

		// Setup WooCommerce and CoCart.
		add_action( 'woocommerce_loaded', array( __CLASS__, 'cocart_tasks' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'woocommerce' ) );
		add_action( 'woocommerce_loaded', array( __CLASS__, 'background_updater' ) );

		// Load integrations (compatibility modules + third-party plugin support).
		add_action( 'init', array( __CLASS__, 'include_integrations' ), 5 );

		// Load translation files.
		add_action( 'init', array( __CLASS__, 'load_plugin_textdomain' ), 0 );

		// Load REST API.
		add_action( 'rest_api_init', array( __CLASS__, 'load_rest_api' ) );

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
	 * @version 4.9.0
	 */
	public static function setup_constants() {
		self::define( 'COCART_ABSPATH', dirname( COCART_FILE ) . '/' );
		self::define( 'COCART_PLUGIN_BASENAME', plugin_basename( COCART_FILE ) );
		self::define( 'COCART_VERSION', self::$version );
		self::define( 'COCART_DB_VERSION', self::$db_version );
		self::define( 'COCART_TESTED_WP', self::$tested_up_to_wp );
		self::define( 'COCART_REQUIRED_WP', self::$required_wp );
		self::define( 'COCART_REQUIRED_PHP', self::$required_php );
		self::define( 'COCART_REQUIRED_WOO', self::$required_woo );
		self::define( 'COCART_SLUG', 'cart-rest-api-for-woocommerce' );
		self::define( 'COCART_URL_PATH', untrailingslashit( plugins_url( '/', COCART_FILE ) ) );
		self::define( 'COCART_FILE_PATH', untrailingslashit( plugin_dir_path( COCART_FILE ) ) );
		self::define( 'COCART_CART_CACHE_GROUP', 'cocart_cart_id' );
		self::define( 'COCART_STORE_URL', 'https://cocartapi.com/' );
		self::define( 'COCART_BILLING_URL', 'https://cocartapi.com/billing/' );
		self::define( 'COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/' );
		self::define( 'COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce' );
		self::define( 'COCART_REVIEW_URL', 'https://testimonial.to/cocart' );
		self::define( 'COCART_SUGGEST_FEATURE', 'https://cocartapi.com/suggest-a-feature/' );
		self::define( 'COCART_COMMUNITY_URL', 'https://cocartapi.com/community/' );
		self::define( 'COCART_DOCUMENTATION_URL', 'https://docs.cocartapi.com/' );
		self::define( 'COCART_TRANSLATION_URL', 'https://translate.cocartapi.com/projects/cart-rest-api-for-woocommerce/' );
		self::define( 'COCART_REPO_URL', 'https://github.com/co-cart/co-cart' );
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
			define( $name, $value ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.VariableConstantNameFound
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
	 * Get the API namespace.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public static function get_api_namespace() {
		return self::$api_namespace;
	}

	/**
	 * Set the API namespace.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 5.0.0 Introduced.
	 */
	protected static function set_api_namespace() {
		self::$api_namespace = wp_cache_get( 'cocart_api_namespace', CoCart_Utilities_Cache_Helpers::get_cache_prefix( 'api_namespace' ) );

		if ( false === self::$api_namespace ) {
			/**
			 * CoCart can be white labeled by configuring the "COCART_API_NAMESPACE" constant in your `wp-config.php` file.
			 *
			 * @since 5.0.0 Introduced.
			 */
			self::$api_namespace = defined( 'COCART_API_NAMESPACE' ) ? constant( 'COCART_API_NAMESPACE' ) : 'cocart';

			wp_cache_add( 'cocart_api_namespace', self::$api_namespace, CoCart_Utilities_Cache_Helpers::get_cache_prefix( 'api_namespace' ), time() + DAY_IN_SECONDS );
		}

		// Revert back if white label add-on is not active. @todo Add detection of white label plugin.
		if ( 'cocart' !== self::$api_namespace ) {
			self::$api_namespace = 'cocart';
		}
	} // END set_api_namespace();

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

		// Important functions.
		include_once __DIR__ . '/cocart-background-functions.php';
		include_once __DIR__ . '/cocart-core-functions.php';
		include_once __DIR__ . '/cocart-deprecated-functions.php';
		include_once __DIR__ . '/cocart-formatting-functions.php';

		// Integration Registry — must load before compatibility and third-party modules.
		include_once __DIR__ . '/classes/class-cocart-integrations.php';

		// Core classes.
		require_once __DIR__ . '/classes/class-cocart-status.php';
		require_once __DIR__ . '/classes/class-cocart-helpers.php';
		require_once __DIR__ . '/classes/class-cocart-install.php';
		require_once __DIR__ . '/classes/class-cocart-logger.php';
		require_once __DIR__ . '/classes/class-cocart-session.php';
		require_once __DIR__ . '/classes/class-cocart-datetime.php';

		// REST API functions.
		include_once __DIR__ . '/cocart-rest-functions.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-authentication.php';

		// WP-CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once __DIR__ . '/classes/class-cocart-cli.php';
		}

		/**
		 * The plugin suggestions updater must load in all contexts so the
		 * scheduled action callback is registered when Action Scheduler
		 * processes the queue via WP-Cron or WP-CLI.
		 */
		if ( ! defined( 'COCART_WHITE_LABEL' ) || false === COCART_WHITE_LABEL ) {
			require_once __DIR__ . '/classes/admin/plugin-suggestions/class-cocart-admin-plugin-suggestions.php';
		}

		/**
		 * Load backend features only if COCART_WHITE_LABEL constant is
		 * NOT set or IS set to false in user's wp-config.php file.
		 */
		if (
			! defined( 'COCART_WHITE_LABEL' ) ||
			( false === COCART_WHITE_LABEL && is_admin() ) ||
			( defined( 'WP_CLI' ) && WP_CLI )
		) {
			require_once __DIR__ . '/classes/admin/class-cocart-admin.php';
		} else {
			require_once __DIR__ . '/classes/admin/class-cocart-wc-admin-system-status.php';
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
		require_once __DIR__ . '/classes/class-cocart-background-updater.php';
	} // END background_updater()

	/**
	 * Include all integrations (compatibility modules + third-party plugin support).
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.9.0 Introduced.
	 */
	public static function include_integrations(): void {
		CoCart_Integrations::load();
	} // END include_integrations()

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

		self::disable_legacy_version();

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
					esc_html__( '%1$s could not be activated. %2$s', 'cocart-core' ),
					'CoCart',
					CoCart_Helpers::get_environment_message() // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				)
			);
		}

		if ( CoCart_Helpers::is_cocart_plus_installed() && defined( 'COCART_PACKAGE_VERSION' ) && version_compare( COCART_VERSION, COCART_PACKAGE_VERSION, '>=' ) ) {
			self::deactivate_plugin();
			wp_die(
				sprintf(
					/* translators: %1$s: CoCart, %2$s: CoCart Plus */
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
					/* translators: %1$s: CoCart, %2$s: CoCart Pro */
					esc_html__( '%1$s is not required as it is already packaged within %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'CoCart Pro'
				)
			);
		}
	} // END activation_check()

	/**
	 * Disable the legacy version of CoCart core if found.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 5.0.0 Introduced.
	 */
	protected static function disable_legacy_version() {
		$plugin_to_deactivate = 'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php';

		if ( is_multisite() && is_network_admin() ) {
			$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins = array_keys( $active_plugins );
		} else {
			$active_plugins = (array) get_option( 'active_plugins', array() );
		}

		foreach ( $active_plugins as $plugin_basename ) {
			if ( $plugin_to_deactivate === $plugin_basename ) {
				set_transient( 'cocart_legacy_deactivated', '1', 1 * HOUR_IN_SECONDS );
				deactivate_plugins( $plugin_basename );
				return;
			}
		}
	} // END disable_legacy_version()

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
	 * @since 4.1.0  Moved REST API classes to load ONLY when the REST API is used.
	 * @since x.x.x  Moved abstracts to load ONLY when the REST API is used.
	 */
	public static function load_rest_api() {
		// Prevent CoCart running in the backend should the REST API server be called by another plugin.
		if ( is_admin() ) {
			return;
		}

		require_once __DIR__ . '/classes/class-cocart-data-exception.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-etag.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-cart-callbacks.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-cart-extension.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-response.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-cart-formatting.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-cart-validation.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-product-validation.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-rest-api.php';
		require_once __DIR__ . '/classes/rest-api/class-cocart-security.php';
	} // END load_rest_api()

	/**
	 * Returns true if we are making a REST API request for CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.2.0 Moved to main class.
	 * @since 4.9.0 Recognize requests made via the WordPress REST API batch endpoint.
	 *
	 * @return bool
	 */
	public static function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) && ! defined( 'WP_CLI' ) ) {
			return false;
		}

		$rest_prefix = trailingslashit( rest_get_url_prefix() );
		$request_uri = esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ); // phpcs:disable WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

		$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'cocart/' ) );

		// Requests sent via the WP REST API batch endpoint share the cart, session and
		// customer for every sub-request dispatched, so return true to accept them.
		if ( ! $is_rest_api_request ) {
			$is_rest_api_request = ( false !== strpos( $request_uri, $rest_prefix . 'batch/v1' ) );
		}

		/**
		 * Filters the REST API requested.
		 *
		 * @since 2.1.0 Introduced.
		 *
		 * @param bool $is_rest_api_request True if CoCart REST API is requested.
		 */
		return apply_filters( 'cocart_is_rest_api_request', $is_rest_api_request );
	} // END is_rest_api_request()

	/**
	 * Redirects to front-end site if set or simply dies with an error message.
	 *
	 * Only administrators will still have access to the WordPress site.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 5.0.0 Introduced.
	 */
	public static function maybe_disable_wp_access() {
		/**
		 * If request method is HEAD then the headless site is making a HEAD request to figure out redirects,
		 * so don't mess with redirects.
		 */
		if (
			isset( $_SERVER['REQUEST_METHOD'] ) &&
			'HEAD' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) )
		) {
			return;
		}

		// Check if WordPress is already doing a redirect.
		if ( isset( $_SERVER['HTTP_X_WP_REDIRECT_CHECK'] ) ) {
			return;
		}

		// Check if WordPress is accessing the customizer.
		if ( is_customize_preview() ) {
			return;
		}

		$cocart_settings = get_option( 'cocart_settings', array() );

		nocache_headers();

		$location = cocart_get_frontend_url( $cocart_settings );
		$disabled = cocart_is_wp_disabled_access( $cocart_settings );

		// WordPress is not disabled so exit early.
		if ( 'no' === $disabled ) {
			return;
		}

		// Check which pages are still accessible.
		$cart_id     = get_option( 'woocommerce_cart_page_id' );
		$checkout_id = get_option( 'woocommerce_checkout_page_id' );

		$current_page_id = get_the_ID();

		/**
		 * Filter controls which pages are accessible when WordPress is denied access.
		 *
		 * Both the cart and checkout pages are accessible by default.
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @return array Page ID's that are accessible.
		 */
		$accessible_pages = apply_filters( 'cocart_wp_accessible_page_ids', array( $cart_id, $checkout_id ) );

		if ( $current_page_id > 0 && in_array( $current_page_id, $accessible_pages ) ) {
			return;
		}

		// Check if user is not administrator.
		$current_user = get_userdata( get_current_user_id() );

		if ( ! empty( $current_user ) ) {
			$user_roles = $current_user->roles;

			if ( in_array( 'administrator', $user_roles, true ) ) {
				return;
			}
		}

		// Redirect if new location provided and disabled.
		if ( ! empty( $location ) && 'yes' === $disabled ) {
			header( 'X-Redirect-By: CoCart' );
			header( "Location: $location", true, 301 );
			exit;
		}

		// Return just error message if disabled only.
		$error = new \WP_Error( 'access_denied', __( "You don't have permission to access the site.", 'cocart-core' ), array( 'status' => 403 ) );

		wp_send_json( $error->get_error_message(), 403 );
		exit;
	} // END maybe_disable_wp_access()

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
		if ( class_exists( 'WC_Session_Handler' ) ) {
			require_once __DIR__ . '/classes/class-cocart-session-handler.php';
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
	 * @since 4.6.2 Moved the cart cache to load once WooCommerce has loaded instead of only during the REST API.
	 *
	 * @return void
	 */
	public static function woocommerce() {
		require_once __DIR__ . '/classes/class-cocart-woocommerce.php';
		require_once __DIR__ . '/classes/class-cocart-cart-cache.php';
	} // END woocommerce()

	/**
	 * Converts the CoCart slug to the correct slug for the current version.
	 * This ensures that when the plugin is installed in a different folder name,
	 * the correct slug is used so that dependent plugins can be installed/activated.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.6.4 Introduced.
	 *
	 * @param string $slug The plugin slug to convert.
	 *
	 * @return string
	 */
	public static function convert_plugin_dependency_slug( $slug ) {
		if ( 'cart-rest-api-for-woocommerce' === $slug ) {
			$slug = dirname( COCART_PLUGIN_BASENAME );
		}

		return $slug;
	} // END convert_plugin_dependency_slug()

	/**
	 * Load the plugin translations if any ready.
	 *
	 * Note: the first-loaded translation file takes priority over any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce-LOCALE.mo
	 *      - PLUGIN_DIR/languages/cart-rest-api-for-woocommerce-LOCALE.mo
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 */
	public static function load_plugin_textdomain() {
		$locale = determine_locale();

		unload_textdomain( COCART_SLUG );
		load_textdomain( COCART_SLUG, WP_LANG_DIR . '/' . COCART_SLUG . '/' . COCART_SLUG . '-' . $locale . '.mo' );
		load_textdomain( COCART_SLUG, plugin_dir_path( COCART_FILE ) . 'languages/' . COCART_SLUG . '-' . $locale . '.mo' );
	} // END load_plugin_textdomain()
} // END class
