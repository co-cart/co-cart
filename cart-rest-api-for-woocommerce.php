<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, update and delete items from the cart.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     2.0.0-beta.1
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.8
 * WC requires at least: 3.2.0
 * WC tested up to: 3.5.1
 *
 * Copyright: © 2018 Sébastien Dumont, (mailme@sebastiendumont.com)
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
		public static $version = '2.0.0-beta.1';

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
		 * @version 2.0.0
		 */
		public function __construct() {
			// Check WooCommerce dependency.
			add_action( 'plugins_loaded', array( $this, 'check_woocommerce_dependency' ) );

			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Setup Constants.
			$this->setup_constants();
		} // END __construct()

		/*-----------------------------------------------------------------------------------*/
		/*  Helper Functions                                                                 */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Setup Constants
		 *
		 * @since  2.0.0
		 * @access public
		 */
		public function setup_constants() {
			$this->define( 'COCART_VERSION', self::$version );
			$this->define( 'COCART_FILE', __FILE__ );
			$this->define( 'COCART_SLUG', 'cart-rest-api-for-woocommerce' );

			$this->define( 'COCART_URL_PATH', untrailingslashit( plugins_url('/', __FILE__) ) );
			$this->define( 'COCART_FILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

			$this->define( 'COCART_WP_VERSION_REQUIRE', '4.4' );
		} // END setup_constants()

		/**
		 * Define constant if not already set.
		 *
		 * @param  string $name
		 * @param  string|bool $value
		 * @access private
		 * @since  2.0.0
		 */
		private function define( $name, $value ) {
			if ( ! defined( $name ) ) {
				define( $name, $value );
			}
		} // END define()

		/**
		 * Check WooCommerce dependency before activation.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 */
		public function check_woocommerce_dependency() {
			// Check if WooCommerce is installed.
			if ( ! defined( 'WC_VERSION' ) ) {
				add_action( 'admin_notices', array( $this, 'woocommerce_not_installed' ) );
				return false;
			}
			// Check we're running the required version of WooCommerce.
			else if ( version_compare( WC_VERSION, self::$required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'required_wc_version_failed' ) );
				return false;
			}

			$this->woocommerce_is_active();
		} // END check_woocommerce_dependency()

		/**
		 * Include the rest of the plugin if WooCommerce is active.
		 *
		 * @access public
		 */
		public function woocommerce_is_active() {
			// Include required files
			$this->includes();
		} // END woocommerce_is_active()

		/**
		 * WooCommerce is Not Installed or Activated Notice.
		 *
		 * @access public
		 * @return void
		 */
		public function woocommerce_not_installed() {
			include_once( COCART_FILE_PATH . '/includes/admin/views/html-notice-wc-not-installed.php' );
		} // END woocommerce_not_installed()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails and
		 * provide an update button if the user has admin capabilities to update plugins.
		 *
		 * @access public
		 * @return void
		 */
		public function required_wc_version_failed() {
			include_once( COCART_FILE_PATH . '/includes/admin/views/html-notice-required-wc.php' );
		} // END required_wc_version_failed()

		/*-----------------------------------------------------------------------------------*/
		/*  Localization                                                                     */
		/*-----------------------------------------------------------------------------------*/

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

		/*-----------------------------------------------------------------------------------*/
		/*  Load Files                                                                       */
		/*-----------------------------------------------------------------------------------*/

		/**
		 * Includes the required core files used for admin and the CoCart API.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 * @return  void
		 */
		public function includes() {
			include_once( COCART_FILE_PATH . '/includes/class-co-cart-init.php' );

			if ( is_admin() ) {
				include_once( COCART_FILE_PATH . '/includes/admin/class-co-cart-admin.php' );
			}

			include_once( COCART_FILE_PATH . '/includes/class-co-cart-install.php' );
		} // END includes()

	} // END class

} // END if class exists

return CoCart::instance();
