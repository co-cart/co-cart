<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: A REST-API for WooCommerce that enables the ability to add, view, update and delete items from the cart.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     1.0.8
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 4.9.8
 * Tested up to: 5.1
 * WC requires at least: 3.0.0
 * WC tested up to: 3.5.4
 *
 * Copyright: © 2019 Sébastien Dumont, (mailme@sebastiendumont.com)
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

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
		public static $version = '1.0.8';

		/**
		 * Required WooCommerce Version
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 */
		public static $required_woo = '3.0.0';

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
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {
			// Check WooCommerce dependency.
			add_action( 'plugins_loaded', array( $this, 'check_woocommerce_dependency' ) );

			// Load translation files.
			add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

			// Add link to documentation and support.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );

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
		 * Check WooCommerce dependency before activation.
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function check_woocommerce_dependency() {
			// Check we're running the required version of WooCommerce.
			if ( ! defined( 'WC_VERSION' ) || version_compare( WC_VERSION, self::$required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'admin_notice' ) );
				return false;
			}
		} // END check_woocommerce_dependency()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function admin_notice() {
			echo '<div class="error"><p>' . sprintf( __( '%1$s requires at least %2$s v%3$s or higher.', 'cart-rest-api-for-woocommerce' ), 'Cart REST API for WooCommerce', 'WooCommerce', self::$required_woo ) . '</p></div>';
		} // END admin_notice()

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

		/**
		 * Includes Cart REST API for WooCommerce Admin.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {
			include_once( $this->plugin_path() . '/includes/class-wc-cart-rest-api-init.php' );
		} // END includes()

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 * @version 1.0.8
		 * @param   mixed $links
		 * @param   mixed $file
		 * @return  array
		 */
		public static function plugin_row_meta( $links, $file ) {
			if ( $file == plugin_basename( __FILE__ ) ) {
				$row_meta = array(
					'docs'    => '<a href="' . esc_url( 'https://co-cart.github.io/co-cart-docs/' ) . '" target="_blank" aria-label="' . esc_attr( __( 'View CoCart Documentation', 'cart-rest-api-for-woocommerce' ) ) . '">' . __( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'support' => '<a href="' . esc_url( 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/' ) . '" target="_blank" aria-label="' . esc_attr( __( 'Create a support ticket for CoCart', 'cart-rest-api-for-woocommerce' ) ) . '">' . __( 'Support', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'review'  => '<a href="' . esc_url( 'https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#reviews' ) . '" target="_blank" aria-label="' . esc_attr( __( 'Review CoCart on WordPress.org', 'cart-rest-api-for-woocommerce' ) ) . '">' . __( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return WC_Cart_Endpoint_REST_API::instance();
