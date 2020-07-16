<?php
/**
 * CoCart - Installation related functions and actions.
 *
 * @author   Sébastien Dumont
 * @category Classes
 * @package  CoCart/Classes/Install
 * @since    1.2.0
 * @version  2.1.0
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
		 * @access  public
		 * @since   1.2.0
		 * @version 2.1.0
		 */
		public function __construct() {
			// Checks version of CoCart and install/update if needed.
			add_action( 'init', array( $this, 'check_version' ), 5 );

			// Redirect to Getting Started page once activated.
			add_action( 'activated_plugin', array( $this, 'redirect_getting_started') );

			// Drop tables when MU blog is deleted.
			add_filter( 'wpmu_drop_tables', array( $this, 'wpmu_drop_tables' ) );
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
		 * @since   1.2.0
		 * @version 2.1.0
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

			// Creates cron jobs.
			self::create_cron_jobs();

			// Install database tables.
			self::create_tables();

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
		 * @version 2.1.0
		 * @param   string $plugin The activate plugin name.
		 */
		public static function redirect_getting_started( $plugin ) {
			// Prevent redirect if plugin name does not match or multiple plugins are being activated.
			if ( $plugin !== plugin_basename( COCART_FILE ) || isset( $_GET['activate-multi'] ) ) {
				return;
			}

			// If CoCart has already been installed before then don't redirect.
			if ( ! empty( get_option( 'cocart_version' ) ) || ! empty( get_site_option( 'cocart_install_date', time() ) ) ) {
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

			// If activated on a multi-site, don't redirect.
			if ( is_multisite() ) {
				return;
			}

			wp_safe_redirect( $getting_started );
			exit;
		} // END redirect_getting_started()

		/**
		 * Create cron jobs (clear them first).
		 *
		 * @access private
		 * @static
		 * @since  2.1.0
		 */
		private static function create_cron_jobs() {
			wp_clear_scheduled_hook( 'cocart_cleanup_carts' );

			wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'cocart_cleanup_carts' );
		} // END create_cron_jobs()

		/**
		 * Creates database tables which the plugin needs to function.
		 *
		 * @access private
		 * @static
		 * @since  2.1.0
		 * @global $wpdb
		 */
		private static function create_tables() {
			global $wpdb;

			$wpdb->hide_errors();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$collate = '';

			if ( $wpdb->has_cap( 'collation' ) ) {
				$collate = $wpdb->get_charset_collate();
			}

			// Queries
			$tables = 
				"CREATE TABLE {$wpdb->prefix}cocart_carts (
					cart_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
					cart_key char(42) NOT NULL,
					cart_value longtext NOT NULL,
					cart_expiry BIGINT UNSIGNED NOT NULL,
					PRIMARY KEY (cart_id),
					UNIQUE KEY cart_key (cart_key)
				) $collate;";

			// Execute
			dbDelta( $tables );
		} // END create_tables()

		/**
		 * Return a list of CoCart tables. Used to make sure all CoCart tables 
		 * are dropped when uninstalling the plugin in a single site 
		 * or multi site environment.
		 *
		 * @access public
		 * @static
		 * @since  2.1.0
		 * @global $wpdb
		 * @return array $tables.
		 */
		public static function get_tables() {
			global $wpdb;

			$tables = array(
				"{$wpdb->prefix}cocart_carts",
			);

			return $tables;
		} // END get_tables()

		/**
		 * Drop CoCart tables.
		 *
		 * @access public
		 * @static
		 * @since  2.1.0
		 * @global $wpdb
		 * @return void
		 */
		public static function drop_tables() {
			global $wpdb;

			$tables = self::get_tables();

			foreach ( $tables as $table ) {
				$wpdb->query( "DROP TABLE IF EXISTS {$table}" );
			}
		} // END drop_tables()

		/**
		 * Uninstall tables when MU blog is deleted.
		 *
		 * @access public
		 * @since  2.1.0
		 * @param  array $tables List of tables that will be deleted by WP.
		 * @return string[]
		 */
		public static function wpmu_drop_tables( $tables ) {
			return array_merge( $tables, self::get_tables() );
		} // END wpmu_drop_tables()

	} // END class.

} // END if class exists.

return new CoCart_Install();
