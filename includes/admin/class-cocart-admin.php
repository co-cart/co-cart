<?php
/**
 * CoCart - Admin.
 *
 * @since    1.2.0
 * @version  2.0.1
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin' ) ) {

	class CoCart_Admin {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			// Include classes.
			self::includes();

			// Add admin page.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access public
		 */
		public function includes() {
			include( dirname( __FILE__ ) . '/class-cocart-admin-action-links.php' ); // Action Links
			include( dirname( __FILE__ ) . '/class-cocart-admin-assets.php' );       // Admin Assets
			include( dirname( __FILE__ ) . '/class-cocart-admin-notices.php' );      // Plugin Notices
		} // END includes()

		/**
		 * Add CoCart to the menu.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.0.1
		 */
		public function admin_menu() {
			$section = isset( $_GET['section'] ) ? trim( $_GET['section'] ) : 'getting-started';

			switch( $section ) {
				case 'getting-started':
					$title = sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' );
					break;
				default:
					$title = apply_filters( 'cocart_page_title_' . strtolower( str_replace( '-', '_', $section ) ), 'CoCart' );
					break;
			}

			add_menu_page(
				$title,
				'CoCart',
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'cocart',
				array( $this, 'cocart_page' ),
				'dashicons-cart'
			);
		} // END admin_menu()

		/**
		 * CoCart Page
		 *
		 * @access public
		 * @since  2.0.1
		 */
		public function cocart_page() {
			$section = isset( $_GET['section'] ) ? trim( $_GET['section'] ) : 'getting-started';

			switch( $section ) {
				case 'getting-started':
					$this->getting_started_content();
					break;

				default:
					do_action( 'cocart_page_section_' . strtolower( str_replace( '-', '_', $section ) ) );
					break;
			}
		} // END cocart_page()

		/**
		 * Getting Started content.
		 *
		 * @access public
		 */
		public function getting_started_content() {
			include_once( dirname( __FILE__ ) . '/views/html-getting-started.php' );
		} // END getting_started_content()

		/**
		 * Checks if CoCart Pro is installed.
		 *
		 * @access public
		 * @static
		 */
		public static function is_cocart_pro_installed() {
			$active_plugins = (array) get_option( 'active_plugins', array() );

			if ( is_multisite() ) {
				$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
			}
	
			return in_array( 'cocart-pro/cocart-pro.php', $active_plugins ) || array_key_exists( 'cocart-pro/cocart-pro.php', $active_plugins );
		} // END is_cocart_pro_installed()

		/**
		 * These are the only screens CoCart will focus 
		 * on displaying notices or equeue scripts/styles.
		 *
		 * @access  public
		 * @static
		 * @since   2.0.0
		 * @version 2.0.1
		 * @return  array
		 */
		public static function cocart_get_admin_screens() {
			return array(
				'dashboard',
				'plugins',
				'toplevel_page_cocart'
			);
		} // END cocart_get_admin_screens()

		/**
		 * Returns true if CoCart is a beta/pre-release.
		 *
		 * @access public
		 * @static
		 * @return boolean
		 */
		public static function is_cocart_beta() {
			if ( 
				strpos( COCART_VERSION, 'beta' ) ||
				strpos( COCART_VERSION, 'rc' )
			) {
				return true;
			}

			return false;
		} // END is_cocart_beta()

		/**
		 * Seconds to words.
		 *
		 * Forked from: https://github.com/thatplugincompany/login-designer/blob/master/includes/admin/class-login-designer-feedback.php
		 *
		 * @access public
		 * @static
		 * @param  string $seconds Seconds in time.
		 * @return string
		 */
		public static function cocart_seconds_to_words( $seconds ) {
			// Get the years.
			$years = ( intval( $seconds ) / YEAR_IN_SECONDS ) % 100;
			if ( $years > 1 ) {
				/* translators: Number of years */
				return sprintf( __( '%s years', 'cart-rest-api-for-woocommerce' ), $years );
			} elseif ( $years > 0 ) {
				return __( 'a year', 'cart-rest-api-for-woocommerce' );
			}

			// Get the weeks.
			$weeks = ( intval( $seconds ) / WEEK_IN_SECONDS ) % 52;
			if ( $weeks > 1 ) {
				/* translators: Number of weeks */
				return sprintf( __( '%s weeks', 'cart-rest-api-for-woocommerce' ), $weeks );
			} elseif ( $weeks > 0 ) {
				return __( 'a week', 'cart-rest-api-for-woocommerce' );
			}

			// Get the days.
			$days = ( intval( $seconds ) / DAY_IN_SECONDS ) % 7;
			if ( $days > 1 ) {
				/* translators: Number of days */
				return sprintf( __( '%s days', 'cart-rest-api-for-woocommerce' ), $days );
			} elseif ( $days > 0 ) {
				return __( 'a day', 'cart-rest-api-for-woocommerce' );
			}

			// Get the hours.
			$hours = ( intval( $seconds ) / HOUR_IN_SECONDS ) % 24;
			if ( $hours > 1 ) {
				/* translators: Number of hours */
				return sprintf( __( '%s hours', 'cart-rest-api-for-woocommerce' ), $hours );
			} elseif ( $hours > 0 ) {
				return __( 'an hour', 'cart-rest-api-for-woocommerce' );
			}

			// Get the minutes.
			$minutes = ( intval( $seconds ) / MINUTE_IN_SECONDS ) % 60;
			if ( $minutes > 1 ) {
				/* translators: Number of minutes */
				return sprintf( __( '%s minutes', 'cart-rest-api-for-woocommerce' ), $minutes );
			} elseif ( $minutes > 0 ) {
				return __( 'a minute', 'cart-rest-api-for-woocommerce' );
			}

			// Get the seconds.
			$seconds = intval( $seconds ) % 60;
			if ( $seconds > 1 ) {
				/* translators: Number of seconds */
				return sprintf( __( '%s seconds', 'cart-rest-api-for-woocommerce' ), $seconds );
			} elseif ( $seconds > 0 ) {
				return __( 'a second', 'cart-rest-api-for-woocommerce' );
			}
		} // END cocart_seconds_to_words()

	} // END class

} // END if class exists

return new CoCart_Admin();
