<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: CoCart is a <strong>REST API for WooCommerce</strong>. It focuses on <strong>the front-end</strong> of the store to manage the shopping cart allowing developers to build a headless store.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     2.4.0
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 5.2
 * Requires PHP: 7.0
 * WC requires at least: 4.0.0
 * WC tested up to: 4.3.1
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
		public static $version = '2.4.0';

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
		 * @version 2.3.0
		 */
		public function __construct() {
			// Setup Constants.
			$this->setup_constants();

			// Force WooCommerce to accept CoCart requests when authenticating.
			add_filter( 'woocommerce_rest_is_request_to_rest_api', array( $this, 'allow_cocart_requests_wc' ) );

			// Include required files.
			add_action( 'plugins_loaded', array( $this, 'includes' ) );

			// Includes WooCommerce tweaks.
			add_action( 'woocommerce_loaded', array( $this, 'woocommerce' ) );

			// Includes setup for CoCart, notices and admin pages.
			add_action( 'init', array( $this, 'admin_includes' ) );

			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Overrides the session handler used for the web.
			add_filter( 'woocommerce_session_handler', array( $this, 'cocart_session_handler' ) );

			// Loads cart from session.
			add_action( 'woocommerce_load_cart_from_session', array( $this, 'load_cart_from_session' ), 0 );
		} // END __construct()

		/**
		 * Setup Constants
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.3.0
		 */
		public function setup_constants() {
			$this->define('COCART_VERSION', self::$version);
			$this->define('COCART_FILE', __FILE__);
			$this->define('COCART_SLUG', 'cart-rest-api-for-woocommerce');

			$this->define('COCART_URL_PATH', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			$this->define('COCART_FILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

			$this->define('COCART_CART_CACHE_GROUP', 'cocart_cart_id');

			$this->define('COCART_STORE_URL', 'https://cocart.xyz/');
			$this->define('COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/');
			$this->define('COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce');
			$this->define('COCART_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/');
			$this->define('COCART_DOCUMENTATION_URL', 'https://docs.cocart.xyz');
			$this->define('COCART_TRANSLATION_URL', 'https://translate.cocart.xyz/projects/cart-rest-api-for-woocommerce/');

			$this->define('COCART_NEXT_VERSION', '3.0.0');
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
		 * @version 2.3.1
		 * @return  void
		 */
		public function includes() {
			include_once( COCART_FILE_PATH . '/includes/class-cocart-autoloader.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-helpers.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-logger.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-product-validation.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-session-handler.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-session.php' );
			include_once( COCART_FILE_PATH . '/includes/class-cocart-init.php' );
			require_once( COCART_FILE_PATH . '/includes/class-cocart-install.php' );
		} // END includes()

		/**
		 * Include WooCommerce tweaks.
		 *
		 * @access public
		 * @since  2.1.2
		 * @return void
		 */
		public function woocommerce() {
			include_once( COCART_FILE_PATH . '/includes/class-cocart-woocommerce.php' );
		} // END woocommerce()

		/**
		 * Include admin classes to handle all back-end functions.
		 *
		 * @access  public
		 * @since   1.2.2
		 * @version 2.3.1
		 * @return  void
		 */
		public function admin_includes() {
			if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
				include_once( COCART_FILE_PATH . '/includes/admin/class-cocart-admin.php' );
			}
		} // END admin_includes()

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
		 * Returns CoCart session handler class name.
		 *
		 * @access  public
		 * @since   2.1.2
		 * @version 2.3.0
		 * @param   string WooCommerce Session Handler
		 * @return  string
		 */
		public function cocart_session_handler( $handler ) {
			if ( ! class_exists('WC_Session') ) {
				return $handler;
			}

			if ( ! is_admin() || ! defined( 'DOING_AJAX' ) || ! defined( 'DOING_CRON' ) || ! CoCart_Helpers::is_rest_api_request() ) {
				$handler = 'CoCart_Session_Handler';
			}

			return $handler;
		} // END cocart_session_handler()

		/**
		 * Loads guest or specific carts into session.
		 *
		 * @access  public
		 * @since   2.1.0
		 * @version 2.1.2
		 */
		public function load_cart_from_session() {
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
		 * Translations should be added in the WordPress language directory:
		 *      - WP_LANG_DIR/plugins/cart-rest-api-for-woocommerce-LOCALE.mo
		 *
		 * @access public
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'cart-rest-api-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

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