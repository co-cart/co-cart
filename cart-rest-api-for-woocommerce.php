<?php
/*
 * Plugin Name: WooCommerce Cart REST API
 * Plugin URI:  https://sebastiendumont.com
 * Version:     1.0.0-Beta
 * Description: Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, update and delete items from the cart.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 *
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.2
 * WC requires at least: 3.0.0
 * WC tested up to: 3.2.6
 *
 * Copyright: © 2018 Sébastien Dumont
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! class_exists( 'WC_Dependencies' ) ) {
	require_once( 'woo-dependencies/woo-dependencies.php' );
}

// Quit right now if WooCommerce is not active
if ( ! is_woocommerce_active() ) {
	return;
}

if ( ! class_exists( 'WC_Cart_Endpoint_REST_API' ) ) {
	class WC_Cart_Endpoint_REST_API {

		/**
		 * @var WC_Cart_Endpoint_REST_API - the single instance of the class.
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
		public static $version = '1.0.0';

		/**
		 * Required WooCommerce Version
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public $required_woo = '3.0.0';

		/**
		 * Main WC_Cart_Endpoint_REST_API Instance.
		 *
		 * Ensures only one instance of WC_Cart_Endpoint_REST_API is loaded or can be loaded.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @see    WC_Cart_Endpoint_REST_API()
		 * @return WC_Cart_Endpoint_REST_API - Main instance
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
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'cart-rest-api-for-woocommerce' ), '1.0.0' );
		}

		/**
		 * Unserializing instances of this class is forbidden.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Foul!', 'cart-rest-api-for-woocommerce' ), '1.0.0' );
		}

		/**
		 * Load the plugin.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {
			add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );
			add_action( 'init', array( $this, 'init_plugin' ) );

			// Include required files
			add_action( 'woocommerce_loaded', array( $this, 'includes' ) );
		}

		/**
		 * Get the Plugin Path.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return string
		 */
		public static function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		} // END plugin_path()

		/**
		 * Check requirements on activation.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function load_plugin() {
			// Check we're running the required version of WooCommerce.
			if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, $this->required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'wc_cart_rest_api_admin_notice' ) );
				return false;
			}
		} // END load_plugin()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function wc_cart_rest_api_admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires at least %2$s v%3$s in order to function. Please upgrade %2$s.', 'cart-rest-api-for-woocommerce' ), 'WooCommerce Cart REST API', 'WooCommerce', $this->required_woo ) . '</p></div>';
		} // END wc_cart_rest_api_admin_notice()

		/**
		 * Initialize the plugin if ready.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function init_plugin() {
			// Load text domain.
			load_plugin_textdomain( 'cart-rest-api-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		} // END init_plugin()

		/**
		 * Includes WooCommerce Cart REST API Admin.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {
			include_once( $this->plugin_path() .'/includes/class-wc-cart-rest-api-init.php' );
		} // END include()

	} // END class

} // END if class exists

return WC_Cart_Endpoint_REST_API::instance();
