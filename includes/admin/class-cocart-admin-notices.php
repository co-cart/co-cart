<?php
/**
 * Display notices in the WordPress admin for CoCart.
 *
 * Forked the notice system from: https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-notices.php
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Notices
 * @since    1.2.0
 * @version  3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Notices' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_Admin_Notices' ) ) {

	class CoCart_Admin_Notices extends CoCart_Notices {

		/**
		 * Activation date.
		 *
		 * @access public
		 * @static
		 * @var    string
		 */
		public static $install_date;

		/**
		 * Constructor
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 */
		public function __construct() {
			self::$install_date = get_site_option( 'cocart_install_date', time() );

			add_action( 'switch_theme', array( $this, 'reset_admin_notices' ) );
			add_action( 'cocart_installed', array( $this, 'reset_admin_notices' ) );
			add_action( 'init', array( $this, 'timed_notices' ) );
			self::add_custom_notice( 'test', 'This is a custom notice!' );
		} // END __construct()

		/**
		 * Reset notices for when new version of CoCart is installed.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function reset_admin_notices() {
			self::add_notice( 'upgrade_warning' );
			self::add_notice( 'check_php' );
			self::add_notice( 'check_wp' );
			self::add_notice( 'check_wc' );
			self::add_notice( 'check_beta' );
		} // END reset_admin_notices()

		/**
		 * Notice about base tables missing.
		 *
		 * @access public
		 * @since  3.0.0
		 * @return void
		 */
		public function base_tables_missing_notice() {
			$notice_dismissed = apply_filters(
				'cocart_hide_base_tables_missing_nag',
				get_user_meta( get_current_user_id(), 'dismissed_cocart_base_tables_missing_notice', true )
			);

			if ( $notice_dismissed ) {
				self::remove_notice( 'base_tables_missing' );
			}

			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-base-table-missing.php';
		} // END base_tables_missing_notice()

		/**
		 * Shows a notice asking the user for a review of CoCart.
		 *
		 * @access public
		 * @since  3.0.0
		 * @return void
		 */
		public function timed_notices() {
			// Was the plugin review notice dismissed?
			$hide_review_notice = get_user_meta( get_current_user_id(), 'dismissed_cocart_plugin_review_notice', true );

			// Check if we need to display the review plugin notice.
			if ( empty( $hide_review_notice ) ) {
				self::add_notice( 'plugin_review' );
			}
		} // END timed_notices()

		/**
		 * Shows an upgrade warning notice if the installed version is less
		 * than the new release coming soon.
		 *
		 * @access  public
		 * @since   1.2.3
		 * @version 3.0.0
		 * @return  void
		 */
		public function upgrade_warning_notice() {
			if ( ! CoCart_Helpers::is_cocart_pre_release() && version_compare( strstr( COCART_VERSION, '-', true ), COCART_NEXT_VERSION, '<' ) ) {
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-upgrade-warning.php';
			}
		} // END upgrade_warning_notice()

		/**
		 * If we need to update the database, include a message with the DB update button.
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 */
		public static function update_db_notice() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( CoCart_Install::needs_db_update() ) {
				$next_scheduled_date = WC()->queue()->get_next( 'cocart_run_update_callback', null, 'cocart-db-updates' );

				if ( $next_scheduled_date || ! empty( $_GET['do_update_cocart'] ) ) { // WPCS: input var ok, CSRF ok.
					include COCART_ABSPATH . 'includes/admin/views/html-notice-updating.php';
				} else {
					include COCART_ABSPATH . 'includes/admin/views/html-notice-update.php';
				}
			} else {
				include COCART_ABSPATH . 'includes/admin/views/html-notice-updated.php';
			}
		} // END update_db_notice()

		/**
		 * Checks the environment on loading WordPress, just in case the environment changes after activation.
		 *
		 * Shows the PHP requirement notice if minimum requirement does not meet.
		 *
		 * @access  public
		 * @since   2.6.0
		 * @version 3.0.0
		 * @return  void
		 */
		public function check_php_notice() {
			if ( ! CoCart_Helpers::is_environment_compatible() && is_plugin_active( plugin_basename( COCART_FILE ) ) ) {
				CoCart::deactivate_plugin();

				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-requirement-php.php';
			}
		} // END check_php_notice()

		/**
		 * Checks that the WordPress version meets the plugin requirement before deciding
		 * to deactivate the plugin and show the WordPress requirement notice if it doesn't meet.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 * @return  void
		 */
		public function check_wp_notice() {
			if ( ! CoCart_Helpers::is_wp_version_gte( CoCart::$required_wp ) ) {
				CoCart::deactivate_plugin();
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-requirement-wp.php';
			}
		} // END check_wp_notice()

		/**
		 * Check WooCommerce Dependency.
		 *
		 * @access public
		 * @since  3.0.0
		 * @return void
		 */
		public function check_woocommerce_notice() {
			if ( ! defined( 'WC_VERSION' ) ) {
				// Deactivate plugin.
				CoCart::deactivate_plugin();

				// WooCommerce is Not Installed or Activated Notice.
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-wc-not-installed.php';
			}
			/**
			 * Displays a warning message if minimum version of WooCommerce check fails and
			 * provides an update button if the user has admin capabilities to update the plugin.
			 */
			elseif ( version_compare( WC_VERSION, CoCart::$required_woo, '<' ) ) {
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-required-wc.php';
			}
		} // END check_woocommerce_notice()

		/**
		 * Displays notice if user is testing pre-release version of the plugin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 * @return  void
		 */
		public function check_beta_notice() {
			// Is this version of CoCart a pre-release?
			if ( CoCart_Helpers::is_cocart_pre_release() ) {
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-trying-beta.php';
			}
		} // END check_beta_notice()

		/**
		 * Displays plugin review notice.
		 *
		 * Shown after 2 weeks or more from the time the plugin was installed.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 * @return  void
		 */
		public function plugin_review_notice() {
			// If it has been 2 weeks or more since activating the plugin then display the review notice.
			if ( ( intval( time() - self::$install_date ) ) > WEEK_IN_SECONDS * 2 ) {
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-please-review.php';
			}
		} // END plugin_review_notice()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
