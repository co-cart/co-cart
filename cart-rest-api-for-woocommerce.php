<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, update and delete items from the cart.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     1.2.3
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * WC requires at least: 3.0.0
 * WC tested up to: 3.6.4
 *
 * Copyright: © 2019 Sébastien Dumont, (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! class_exists( 'CoCart' ) ) {
	class CoCart {

		/**
		 * @var CoCart - the single instance of the class.
		 *
		 * @access protected
		 * @static
		 * @since 1.0.0
		 */
		protected static $_instance = null;

		/**
		 * Plugin Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static $version = '1.2.3';

		/**
		 * Required WooCommerce Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static $required_woo = '3.0.0';

		/**
		 * Main CoCart Instance.
		 *
		 * Ensures only one instance of CoCart is loaded or can be loaded.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 * @version 1.2.0
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
		 * @since  1.0.0
		 * @return void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cloning this object is forbidden.', 'cart-rest-api-for-woocommerce' ), self::$version );
		} // END __clone()

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access public
		 * @since  1.0.0
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
		 * @version 1.2.2
		 */
		public function __construct() {
			$this->setup_constants();
			$this->admin_includes();

			// Initialize plugin.
			add_action( 'plugins_loaded', array( $this, 'initialize_plugin' ) );

			// Include required files.
			add_action( 'init', array( $this, 'includes' ) );

			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		} // END __construct()

		/**
		 * Setup Constants
		 *
		 * @access private
		 * @since  1.2.0
		 */
		private function setup_constants() {
			$this->define('COCART_VERSION', self::$version);
			$this->define('COCART_FILE', __FILE__);
			$this->define('COCART_SLUG', 'cart-rest-api-for-woocommerce');

			$this->define('COCART_URL_PATH', untrailingslashit( plugins_url( '/', __FILE__ ) ) );
			$this->define('COCART_FILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

			$this->define('COCART_WP_VERSION_REQUIRE', '4.4');

			$this->define('COCART_STORE_URL', 'https://cocart.xyz/');
			$this->define('COCART_PLUGIN_URL', 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/');
			$this->define('COCART_SUPPORT_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce');
			$this->define('COCART_REVIEW_URL', 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/');
			$this->define('COCART_DOCUMENTATION_URL', 'https://co-cart.github.io/co-cart-docs/');
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
		 * Initialize plugin
		 *
		 * @access public
		 */
		public function initialize_plugin() {
			// If the current user can not install plugins then return nothing!
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			// Check we're running the required version of WooCommerce.
			if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::$required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'requirement_wc_notice' ) );
				return false;
			}
		} // END initialize_plugin()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 1.2.2
		 * @return  void
		 */
		public function requirement_wc_notice() {
			include( dirname( __FILE__ ) . '/admin/views/html-notice-requirement-wc.php' );
		} // END requirement_wc_notice()

		/**
		 * Includes Cart REST-API for WooCommerce.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 1.2.2
		 * @return  void
		 */
		public function includes() {
			include_once( dirname( __FILE__ ) . '/includes/class-wc-cart-rest-api-init.php' );
		} // END includes()

		/**
		 * Include admin class to handle all back-end functions.
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
		 * Make the plugin translation ready.
		 *
		 * Translations should be added in the WordPress language directory:
		 *      - WP_LANG_DIR/plugins/cart-rest-api-for-woocommerce-LOCALE.mo
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'cart-rest-api-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END load_plugin_textdomain()

	} // END class

} // END if class exists

return CoCart::instance();
