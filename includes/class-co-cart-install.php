<?php
/**
 * CoCart - Installation related functions and actions.
 *
 * @since    2.0.0
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart/Classes/Install
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Install' ) ) {

	class CoCart_Install {

		/**
		 * Plugin version.
		 *
		 * @access private
		 * @static
		 * @var    string
		 */
		private static $current_version;

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'init', array( __CLASS__, 'add_rewrite_endpoint' ), 0 );
			add_action( 'init', array( __CLASS__, 'check_version' ), 5 );

			// Get plugin version.
			self::$current_version = get_option( 'cocart_version' );
		} // END __construct()

		/**
		 * Check plugin version and run the updater if necessary.
		 *
		 * This check is done on all requests and runs if the versions do not match.
		 *
		 * @access public
		 * @static
		 */
		public static function check_version() {
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( self::$current_version, COCART_VERSION, '<' ) ) {
				self::install();
				do_action( 'cocart_updated' );
			}
		} // END check_version()

		/**
		 * Install CoCart.
		 *
		 * @access public
		 * @static
		 */
		public static function install() {
			if ( ! is_blog_installed() ) {
				return;
			}

			// Check if we are not already running this routine.
			if ( 'yes' === get_transient( 'cocart_installing' ) ) {
				return;
			}

			// If we made it till here nothing is running yet, lets set the transient now for five minutes.
			set_transient( 'cocart_installing', 'yes', MINUTE_IN_SECONDS * 5 );
			if ( ! defined( 'COCART_INSTALLING' ) ) {
				define( 'COCART_INSTALLING', true );
			}

			// Set activation date.
			self::set_install_date();

			// Update plugin version.
			self::update_version();

			// Refresh rewrite rules.
			self::flush_rewrite_rules();

			delete_transient( 'cocart_installing' );

			do_action( 'cocart_installed' );
		} // END install()

		/**
		 * Update plugin version to current.
		 *
		 * @access private
		 * @static
		 */
		private static function update_version() {
			update_option( 'cocart_version', COCART_VERSION );
		} // END update_version()

		/**
		 * Set the time the plugin was installed.
		 *
		 * @access public
		 * @static
		 */
		public static function set_install_date() {
			$install_date = get_site_option( 'cocart_install_date' );

			add_site_option( 'cocart_install_date', time() );
		} // END set_install_date()

		/**
		 * Add rewrite endpoint for CoCart.
		 *
		 * @access public
		 * @static
		 */
		public static function add_rewrite_endpoint() {
			add_rewrite_endpoint( 'cocart', EP_ALL );
		} // END add_rewrite_endpoint()

		/**
		 * Flush rewrite rules.
		 *
		 * @access public
		 * @static
		 */
		public static function flush_rewrite_rules() {
			flush_rewrite_rules();
		} // END flush_rewrite_rules()

	} // END class.

} // END if class exists.

return new CoCart_Install();
