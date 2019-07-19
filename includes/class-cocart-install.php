<?php
/**
 * CoCart - Installation related functions and actions.
 *
 * @since    1.2.0
 * @version  2.0.2
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
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			// Checks version of CoCart and install/update if needed.
			add_action( 'init', array( $this, 'check_version' ), 5 );

			// Redirect to Getting Started page once activated.
			add_action( 'activated_plugin', array( $this, 'redirect_getting_started') );
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
			if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( get_option( 'cocart_version' ), COCART_VERSION, '<' ) && current_user_can( 'install_plugins' ) ) {
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
			add_site_option( 'cocart_install_date', time() );
		} // END set_install_date()

		/**
		 * Redirects to the Getting Started page upon plugin activation.
		 *
		 * @access  public
		 * @static
		 * @since   1.2.0
		 * @version 2.0.2
		 * @param   string $plugin The activate plugin name.
		 */
		public static function redirect_getting_started( $plugin ) {
			// Prevent redirect if plugin name does not match.
			if ( $plugin !== plugin_basename( COCART_FILE ) ) {
				return;
			}

			$getting_started = add_query_arg( array( 
				'page'    => 'cocart', 
				'section' => 'getting-started'
			), admin_url( 'admin.php' ) );

			/**
			 * Should CoCart be installed via WP-CLI,
			 * display a link to the Getting Started page.
			 */
			if ( defined( 'WP_CLI' ) && WP_CLI ) {
				WP_CLI::log(
					WP_CLI::colorize(
						'%y' . sprintf( '🎉 %1$s %2$s', __( 'Get started with %3$s here:', 'cart-rest-api-for-woocommerce' ), $getting_started, esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ) ) . '%n'
					)
				);
				return;
			}

			// If activated on a Multisite, don't redirect.
			if ( is_multisite() ) {
				return;
			}

			wp_safe_redirect( $getting_started );
			exit;
		} // END redirect_getting_started()
	} // END class.

} // END if class exists.

return new CoCart_Install();
