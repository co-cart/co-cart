<?php
/**
 * Display notices in the WordPress admin for CoCart.
 *
 * Forked the notice system from: https://github.com/woocommerce/woocommerce/blob/master/includes/admin/class-wc-admin-notices.php
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Notices
 * @since   1.2.0
 * @version 3.2.0
 * @license GPL-2.0+
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
		 * @access public
		 * @static
		 * @since  3.0.0
		 * @var    array
		 */
		public static $notices = array();

		/**
		 * Array of notices - name => callback.
		 *
		 * @access private
		 * @static
		 * @since  3.0.0
		 * @var    array
		 */
		private static $core_notices = array(
			'update_db'           => 'update_db_notice',
			'check_php'           => 'check_php_notice',
			'check_wp'            => 'check_wp_notice',
			'check_wc'            => 'check_woocommerce_notice',
			'plugin_review'       => 'plugin_review_notice',
			'check_beta'          => 'check_beta_notice',
			'base_tables_missing' => 'base_tables_missing_notice',
			'setup_wizard'        => 'setup_wizard_notice',
		);

		/**
		 * Constructor
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.1.0
		 */
		public function __construct() {
			self::$install_date = get_option( 'cocart_install_date', time() );
			self::$notices      = get_option( 'cocart_admin_notices', array() );

			add_action( 'switch_theme', array( $this, 'reset_admin_notices' ) );
			add_action( 'cocart_installed', array( $this, 'reset_admin_notices' ) );
			add_action( 'wp_loaded', array( $this, 'hide_notices' ) );
			add_action( 'wp_loaded', array( $this, 'timed_notices' ), 11 );

			add_action( 'shutdown', array( $this, 'store_notices' ) );

			// If the current user has capabilities then add notices.
			if ( CoCart_Helpers::user_has_capabilities() ) {
				add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
			}
		} // END __construct()

		/**
		 * Store notices to DB.
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 */
		public static function store_notices() {
			update_option( 'cocart_admin_notices', self::get_notices() );
		} // END store_notices()

		/**
		 * Get notices
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 * @return array
		 */
		public static function get_notices() {
			return self::$notices;
		} // END get_notices()

		/**
		 * Remove all notices.
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 */
		public static function remove_all_notices() {
			self::$notices = array();
		} // END remove_all_notices()

		/**
		 * Reset notices for when new version of CoCart is installed.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function reset_admin_notices() {
			self::add_notice( 'check_php' );
			self::add_notice( 'check_wp' );
			self::add_notice( 'check_wc' );
			self::add_notice( 'check_beta' );
		} // END reset_admin_notices()

		/**
		 * Show a notice.
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 * @param  string $name Notice name.
		 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
		 */
		public static function add_notice( $name, $force_save = false ) {
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
		 * @static
		 * @since  3.0.0
		 * @param  string $name Notice name.
		 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
		 */
		public static function remove_notice( $name, $force_save = false ) {
			$notices = self::get_notices();

			// Check that the notice exists before attempting to remove it.
			if ( in_array( $name, $notices ) ) {
				self::$notices = array_diff( $notices, array( $name ) );

				delete_option( 'cocart_admin_notice_' . $name );

				if ( $force_save ) {
					// Adding early save to prevent more conditions with notices.
					self::store_notices();
				}
			}
		} // END remove_notice()

		/**
		 * See if a notice is being shown.
		 *
		 * @access  public
		 * @static
		 * @since   3.0.0
		 * @version 3.1.0
		 * @param   string $name Notice name.
		 * @return  boolean
		 */
		public static function has_notice( $name ) {
			return in_array( $name, self::get_notices(), true );
		} // END has_notice()

		/**
		 * Hide a notice if the GET variable is set.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function hide_notices() {
			if ( isset( $_GET['cocart-hide-notice'] ) && isset( $_GET['_cocart_notice_nonce'] ) ) {
				if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_cocart_notice_nonce'] ) ), 'cocart_hide_notices_nonce' ) ) {
					wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cart-rest-api-for-woocommerce' ) );
				}

				if ( ! CoCart_Helpers::user_has_capabilities() ) {
					wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'cart-rest-api-for-woocommerce' ) );
				}

				$hide_notice = sanitize_text_field( wp_unslash( $_GET['cocart-hide-notice'] ) );

				self::remove_notice( $hide_notice );

				update_user_meta( get_current_user_id(), 'dismissed_cocart_' . $hide_notice . '_notice', true );

				do_action( 'cocart_hide_' . $hide_notice . '_notice' );

				wp_safe_redirect( remove_query_arg( array( 'cocart-hide-notice', '_cocart_notice_nonce' ), CoCart_Helpers::cocart_get_current_admin_url() ) );
				exit;
			}
		} // END hide_notices()

		/**
		 * Add notices.
		 *
		 * @access  public
		 * @since   3.0.0
		 * @version 3.0.17
		 */
		public function add_notices() {
			// Prevent notices from loading on the frontend.
			if ( ! is_admin() ) {
				return;
			}

			$notices = self::get_notices();

			if ( empty( $notices ) ) {
				return;
			}

			// Notice should only show on specific pages.
			if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
				return;
			}

			foreach ( $notices as $notice ) {
				if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'cocart_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( $this, self::$core_notices[ $notice ] ) );
				} else {
					add_action( 'admin_notices', array( $this, 'output_custom_notices' ) );
				}
			}
		} // END add_notices()

		/**
		 * Add a custom notice.
		 *
		 * @access public
		 * @static
		 * @since  3.0.0
		 * @param  string $name        Notice name.
		 * @param  string $notice_html Notice HTML.
		 */
		public static function add_custom_notice( $name, $notice_html ) {
			self::add_notice( $name );
			update_option( 'cocart_admin_notice_' . $name, wp_kses_post( $notice_html ) );
		} // END add_custom_notice()

		/**
		 * Output any stored custom notices.
		 *
		 * @access public
		 * @since  3.0.0
		 * @return void
		 */
		public function output_custom_notices() {
			$notices = self::get_notices();

			if ( ! empty( $notices ) ) {
				foreach ( $notices as $notice ) {
					if ( empty( self::$core_notices[ $notice ] ) ) {
						$notice_html = get_option( 'cocart_admin_notice_' . $notice );

						if ( $notice_html ) {
							include COCART_ABSPATH . 'includes/admin/views/html-notice-custom.php';
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
		 * @access  public
		 * @since   3.0.0
		 * @version 3.2.0
		 * @return  void
		 */
		public function timed_notices() {
			// Add review notice first. We will remove it after if already dismissed.
			self::add_notice( 'plugin_review' );

			// Was the plugin review notice dismissed?
			$hide_review_notice = get_user_meta( get_current_user_id(), 'dismissed_cocart_plugin_review_notice', true );

			// If review plugin notice dismissed, remove it.
			if ( $hide_review_notice ) {
				self::remove_notice( 'plugin_review' );
			}
		} // END timed_notices()

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

				if ( $next_scheduled_date || ! empty( $_GET['do_update_cocart'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
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
			} elseif ( version_compare( WC_VERSION, CoCart::$required_woo, '<' ) ) {
				/**
				 * Displays a warning message if minimum version of WooCommerce check fails and
				 * provides an update button if the user has admin capabilities to update the plugin.
				 */
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

		/**
		 * Displays setup wizard notice.
		 *
		 * Shows only for those new to CoCart or setup wizard has not be done.
		 *
		 * @access public
		 * @since  3.1.0
		 * @return void
		 */
		public function setup_wizard_notice() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-setup-wizard.php';
		} // END setup_wizard_notice()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
