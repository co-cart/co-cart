<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: CoCart is a <strong>REST API for WooCommerce</strong>. It focuses on <strong>the front-end</strong> of the store to manage the shopping cart allowing developers to build a headless store.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     2.1.2-rc.1
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * WC requires at least: 3.6.0
 * WC tested up to: 4.1.0
 *
 * Copyright: © 2020 Sébastien Dumont, (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! class_exists( 'CoCart' ) ) {
	class CoCart {

		/**
		 * Plugin Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static $version = '2.1.2-rc.1';

		/**
		 * Required WooCommerce Version
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 * @version 2.1.0
		 */
		public static $required_woo = '3.6.0';

		/**
		 * API Session instance.
		 *
		 * @since 2.1.0
		 * @var   CoCart_API_Session
		 */
		public $session = null;

		/**
		 * @var CoCart - the single instance of the class.
		 *
		 * @access protected
		 * @static
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Main CoCart Instance.
		 *
		 * Ensures only one instance of CoCart is loaded or can be loaded.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 * @version 1.0.6
		 * @see     CoCart()
		 * @return  CoCart - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Cloning is forbidden.
		 *
		 * @access public
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this object is forbidden.', 'cart-rest-api-for-woocommerce' ), self::$version );
		} // END __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access public
		 * @return void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'cart-rest-api-for-woocommerce' ), self::$version );
		} // END __wakeup()

		/**
		 * Load the plugin.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.1.2
		 */
		public function __construct() {
			// Setup Constants.
			$this->setup_constants();

			// Include admin classes to handle all back-end functions.
			$this->admin_includes();

			// Force WooCommerce to accept CoCart requests when authenticating.
			add_filter( 'woocommerce_rest_is_request_to_rest_api', array( $this, 'allow_cocart_requests_wc' ) );

			// Include required files.
			add_action( 'plugins_loaded', array( $this, 'includes' ) );

			// Initialize session.
			add_action( 'plugins_loaded', array( $this, 'initialize_session' ), 12 );

			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Removes WooCommerce filter that validates the quantity value to be an integer.
			remove_filter( 'woocommerce_stock_amount', 'intval' );

			// Validates the quantity value to be a float.
			add_filter( 'woocommerce_stock_amount', 'floatval' );

			// Overrides the session handler used for the web.
			add_filter( 'woocommerce_session_handler', array( $this, 'cocart_session_handler' ), 99 );
		} // END __construct()

		/**
		 * Setup Constants
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.1.0
		 */
		public function setup_constants() {
			$this->define('COCART_VERSION', self::$version);
			$this->define('COCART_FILE', __FILE__);
			$this->define('COCART_SLUG', 'cart-rest-api-for-woocommerce');

			$this->define('COCART_URL_PATH', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			$this->define('COCART_FILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

			$this->define('COCART_WP_VERSION_REQUIRE', '5.0');

			$this->define('COCART_CART_CACHE_GROUP', 'cocart_cart_id');

			$this->define('COCART_STORE_URL', 'https://cocart.xyz/');
			$this->define('COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/');
			$this->define('COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce');
			$this->define('COCART_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/');
			$this->define('COCART_DOCUMENTATION_URL', 'https://docs.cocart.xyz');
			$this->define('COCART_TRANSLATION_URL', 'https://translate.cocart.xyz/projects/cart-rest-api-for-woocommerce/');

			$this->define('COCART_NEXT_VERSION', '2.1.0');
		} // END setup_constants()

		/**
		 * Define constant if not already set.
		 *
		 * @access private
		 * @since  1.2.0
		 * @param  string $name
		 * @param  string|bool $value
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		} // END define()

		/**
		 * Includes CoCart REST-API.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.1.0
		 * @return  void
		 */
		public function includes() {
			include_once( COCART_FILE_PATH . '/includes/class-cocart-autoloader.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-logger.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-product-validation.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-session-handler.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-session.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-init.php' );
		} // END includes()

		/**
		 * Include admin classes to handle all back-end functions.
		 *
		 * @access public
		 * @since  1.2.2
		 * @return void
		 */
		public function admin_includes() {
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				include_once( dirname( __FILE__ ) . '/includes/admin/class-cocart-admin.php' );
				require_once( dirname( __FILE__ ) . '/includes/class-cocart-install.php' ); // Install CoCart.
			}
		} // END admin_includes()

		/**
		 * Initialize CoCart API Session, if requesting the API.
		 *
		 * @access public
		 * @since  2.1.0
		 * @return void
		 */
		public function initialize_session() {
			if ( is_null( $this->session ) && class_exists( 'CoCart_API_Session' ) ) {
				$this->session = new CoCart_API_Session();
				$this->session->init();
			}
		} // END initialize_session()

		/**
		 * Load the plugin translations if any ready.
		 *
		 * Translations should be added in the WordPress language directory:
		 *      - WP_LANG_DIR/plugins/cart-rest-api-for-woocommerce-LOCALE.mo
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'cart-rest-api-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

		/**
		 * Force WooCommerce to accept CoCart API requests when authenticating.
		 *
		 * @access public
		 * @since  2.0.5
		 * @param  bool $request
		 * @return bool true|$request
		 */
		public function allow_cocart_requests_wc( $request ) {
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
		 * Returns true if we are making a REST API request.
		 *
		 * @access public
		 * @since  2.1.0
		 * @return bool
		 */
		public function is_rest_api_request() {
			if ( empty( $_SERVER['REQUEST_URI'] ) ) {
				return false;
			}

			$rest_prefix         = trailingslashit( rest_get_url_prefix() );
			$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix ) );

			return $is_rest_api_request;
		} // END is_rest_api_request()

		/**
		 * Returns CoCart session handler class name.
		 *
		 * @access public
		 * @since  2.1.2
		 * @return string
		 */
		public function cocart_session_handler() {
			return 'CoCart_Session_Handler';
		} // END cocart_session_handler()

	} // END class

} // END if class exists

/**
 * Returns the main instance of CoCart.
 *
 * @since  2.1.0
 * @return CoCart
 */
function CoCart() {
	return CoCart::instance();
}

CoCart();