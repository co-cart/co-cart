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

if ( ! class_exists( 'CoCart_Admin_Notices' ) ) {

	class CoCart_Admin_Notices {

		/**
		 * Activation date.
		 *
		 * @access public
		 * @static
		 * @var    string
		 */
		public static $install_date;

		/**
		 * Stores notices.
		 *
		 * @access private
		 * @static
		 * @since  3.0.0
		 * @var    array
		 */
		private static $notices = array();

		/**
		 * Array of notices - name => callback.
		 *
		 * @access private
		 * @static
		 * @since  3.0.0
		 * @var    array
		 */
		private static $core_notices = array(
			'update'              => 'update_notice',
			'check_php'           => 'check_php_notice',
			'check_wp'            => 'check_wp_notice',
			'check_wc'            => 'check_woocommerce_notice',
			'plugin_review'       => 'plugin_review_notice',
			'check_beta'          => 'check_beta_notice',
			'upgrade_warning'     => 'upgrade_warning_notice',
			'base_tables_missing' => 'base_tables_missing_notice',
		);

		/**
		 * Constructor
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 */
		public function __construct() {
			self::$install_date = get_site_option( 'cocart_install_date', time() );
			self::$notices = get_site_option( 'cocart_admin_notices', array() );

			add_action( 'cocart_installed', array( $this, 'reset_admin_notices' ) );

			// Don't bug the user if they don't want to see any notices.
			add_action( 'admin_init', array( $this, 'dont_bug_me' ), 15 );

			// If the current user has capabilities then add notices.
			if ( CoCart_Helpers::user_has_capabilities() ) {
				add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
			}
		} // END __construct()

		/**
		 * Store notices to DB.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function store_notices() {
			update_site_option( 'cocart_admin_notices', self::get_notices() );
		} // END store_notices()

		/**
		 * Get notices
		 *
		 * @access public
		 * @since  3.0.0
		 * @return array
		 */
		public function get_notices() {
			return self::$notices;
		} // END get_notices()

		/**
		 * Remove all notices.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function remove_all_notices() {
			self::$notices = array();
		} // END remove_all_notices()

		/**
		 * Reset notices for when new version of CoCart is installed.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function reset_admin_notices() {
			self::add_notice( 'check_php_notice' );
			self::add_notice( 'check_wp_notice' );
			self::add_notice( 'check_woocommerce_notice' );
		}

		/**
		 * Show a notice.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  string $name Notice name.
		 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
		 */
		public function add_notice( $name, $force_save = false ) {
			self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );

			if ( $force_save ) {
				// Adding early save to prevent more race conditions with notices.
				self::store_notices();
			}
		} // END add_notice()

		/**
		 * Remove a notice from being displayed.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  string $name Notice name.
		 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
		 */
		public function remove_notice( $name, $force_save = false ) {
			self::$notices = array_diff( self::get_notices(), array( $name ) );

			delete_option( 'cocart_admin_notice_' . $name );

			if ( $force_save ) {
				// Adding early save to prevent more conditions with notices.
				self::store_notices();
			}
		} // END remove_notice()

		/**
		 * See if a notice is being shown.
		 *
		 * @access public
		 * @since  3.0.0
		 * @param  string $name Notice name.
		 * @return boolean
		 */
		public function has_notice( $name ) {
			return in_array( $name, self::get_notices(), true );
		} // END has_notice()

		/**
		 * Add notices.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function add_notices() {
			$notices = self::get_notices();

			if ( empty( $notices ) ) {
				return;
			}

			// Notice should only show on a CoCart page.
			if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
				return;
			}

			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'cocart_show_admin_notice', true, $notice ) ) {
					add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, self::$core_notices[ $notice ] ) );
				} else {
					add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, 'output_custom_notices' ) );
				}
			}
		} // END add_notices()

		/**
		 * Output any stored custom notices.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function output_custom_notices() {
			$notices = self::get_notices();

			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( self::$core_notices[ $notice ] ) ) {
						$notice_html = get_site_option( 'cocart_admin_notice_' . $notice );

						if ( $notice_html ) {
							include dirname( __FILE__ ) . '/views/html-notice-custom.php';
						}
					}
				}
			}
		} // END output_custom_notices()

		/**
		 * Notice about base tables missing.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function base_tables_missing_notice() {
			$notice_dismissed = apply_filters(
				'cocart_hide_base_tables_missing_nag',
				get_user_meta( get_current_user_id(), 'dismissed_base_tables_missing_notice', true )
			);

			if ( $notice_dismissed ) {
				self::remove_notice( 'base_tables_missing' );
			}

			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-base-table-missing.php';
		} // END base_tables_missing_notice()

		/**
		 * Don't bug the user if they don't want to see any notices.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 */
		public function dont_bug_me() {
			$user_hidden_notice = false;

			// If the user is allowed to install plugins and requested to hide the review notice then hide it for that user.
			if ( ! empty( $_GET['hide_cocart_review_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				add_user_meta( get_current_user_id(), 'cocart_hide_review_notice', '1', true );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dismiss upgrade notice then hide it 2 weeks.
			if ( ! empty( $_GET['hide_cocart_upgrade_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				set_transient( 'cocart_upgrade_notice_hidden', 'hidden', apply_filters( 'cocart_upgrade_notice_expiration', WEEK_IN_SECONDS * 2 ) );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dismiss upgrade notice forever.
			if ( ! empty( $_GET['hide_forever_cocart_upgrade_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				set_transient( 'cocart_upgrade_notice_hidden', 'hidden' );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dismiss beta notice then hide it for 1 week.
			if ( ! empty( $_GET['hide_cocart_beta_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				set_transient( 'cocart_beta_notice_hidden', 'hidden', apply_filters( 'cocart_beta_notice_expiration', WEEK_IN_SECONDS ) );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dismiss beta notice forever.
			if ( ! empty( $_GET['hide_forever_cocart_beta_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				set_transient( 'cocart_beta_notice_hidden', 'hidden' );
				$user_hidden_notice = true;
			}

			// Did user hide a notice?
			if ( $user_hidden_notice ) {
				// Redirects back to current admin URL.
				wp_safe_redirect( CoCart_Helpers::cocart_get_current_admin_url() );
				exit;
			}
		} // END dont_bug_me()

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
			// Upgrade warning notice will disappear once the new release is installed.
			$upgrade_notice = get_transient( 'cocart_upgrade_notice_hidden' );
			$next_version   = get_transient( 'cocart_next_version' );

			// If the next version is higher than the previous upgrade version then clear transient to show upgrade notice again.
			if ( ! empty( $upgrade_notice ) && version_compare( COCART_NEXT_VERSION, $next_version, '>' ) ) {
				delete_transient( 'cocart_upgrade_notice_hidden' );
			}

			if ( ! CoCart_Helpers::is_cocart_pre_release() && version_compare( strstr( COCART_VERSION, '-', true ), COCART_NEXT_VERSION, '<' ) && empty( get_transient( 'cocart_upgrade_notice_hidden' ) ) ) {
				include_once COCART_ABSPATH . 'includes/admin/views/html-notice-upgrade-warning.php';
				set_transient( 'cocart_next_version', COCART_NEXT_VERSION );
			}

		} // END upgrade_warning_notice()

		/**
		 * If we need to update the database, include a message with the DB update button.
		 *
		 * @access public
		 * @static
		 */
		public static function update_notice() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( CoCart_Install::needs_db_update() ) {
				$next_scheduled_date = WC()->queue()->get_next( 'cocart_run_update_callback', null, 'cocart-db-updates' );

				if ( $next_scheduled_date || ! empty( $_GET['do_update_cocart'] ) ) { // WPCS: input var ok, CSRF ok.
					include dirname( __FILE__ ) . '/views/html-notice-updating.php';
				} else {
					include dirname( __FILE__ ) . '/views/html-notice-update.php';
				}
			} else {
				include dirname( __FILE__ ) . '/views/html-notice-updated.php';
			}
		} // END update_notice()

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
			if ( CoCart_Helpers::is_cocart_pre_release() && empty( get_transient( 'cocart_beta_notice_hidden' ) ) ) {
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
			$install_date = self::$install_date;

			// Is admin review notice hidden?
			$hide_review_notice = get_user_meta( get_current_user_id(), 'cocart_hide_review_notice', true );

			// Check if we need to display the review plugin notice.
			if ( empty( $hide_review_notice ) ) {
				// If it has been 2 weeks or more since activating the plugin then display the review notice.
				if ( ( intval( time() - $install_date ) ) > WEEK_IN_SECONDS * 2 ) {
					include_once COCART_ABSPATH . 'includes/admin/views/html-notice-please-review.php';
				}
			}
		} // END plugin_review_notice()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
