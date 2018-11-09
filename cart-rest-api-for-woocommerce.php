<?php
/*
 * Plugin Name: CoCart
 * Plugin URI:  https://cocart.xyz
 * Description: Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, update and delete items from the cart.
 * Author:      Sébastien Dumont
 * Author URI:  https://sebastiendumont.com
 * Version:     1.0.6
 * Text Domain: cart-rest-api-for-woocommerce
 * Domain Path: /languages/
 *
 * Requires at least: 4.4
 * Tested up to: 4.9.8
 * WC requires at least: 3.2.0
 * WC tested up to: 3.5
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
		public static $version = '1.0.6';

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

			// Add links to documentation and support.
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		} // END __construct()

		/*-----------------------------------------------------------------------------------*/
		/*  Helper Functions                                                                 */
		/*-----------------------------------------------------------------------------------*/

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
		 * Run action hooks if WooCommerce is active.
		 *
		 * @access public
		 */
		public function woocommerce_is_active() {
			// Include required files
			add_action( 'woocommerce_loaded', array( $this, 'includes' ) );
		} // END woocommerce_is_active()

		/**
		 * WooCommerce is Not Installed or Activated Notice.
		 *
		 * @access public
		 * @return void
		 */
		public function woocommerce_not_installed() {
			include_once( $this->plugin_path() . '/includes/admin/views/html-notice-wc-not-installed.php' );
		} // END woocommerce_not_installed()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails and
		 * provide an update button if the user has admin capabilities to update plugins.
		 *
		 * @access public
		 * @return void
		 */
		public function required_wc_version_failed() {
			//$wc_required = self::$required_woo;

			include_once( $this->plugin_path() . '/includes/admin/views/html-notice-required-wc.php' );
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
		 * Includes CoCart API.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function includes() {
			include_once( $this->plugin_path() . '/includes/class-co-cart-init.php' );
		} // END includes()

		/**
		 * Show row meta on the plugin screen.
		 *
		 * @access public
		 * @static
		 * @param  mixed $links
		 * @param  mixed $file
		 * @return array
		 */
		public static function plugin_row_meta( $links, $file ) {
			if ( $file == plugin_basename( __FILE__ ) ) {
				$row_meta = array(
					'docs'    => '<a href="https://co-cart.github.io/co-cart-docs/" target="_blank">' . __( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'support' => '<a href="https://co-cart.github.io/co-cart-docs/#support" target="_blank">' . __( 'Support', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return CoCart::instance();
