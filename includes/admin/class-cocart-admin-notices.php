<?php
/**
 * Display notices in the WordPress admin for CoCart.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Notices
 * @since    1.2.0
 * @version  2.6.2
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
		 * Constructor
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.6.0
		 */
		public function __construct() {
			self::$install_date = get_site_option( 'cocart_install_date', time() );

			// Check PHP environment.
			add_action( 'admin_init', array( $this, 'check_php' ), 12 );

			// Check WordPress environment.
			add_action( 'admin_init', array( $this, 'check_wp' ), 12 );

			// Check WooCommerce dependency.
			add_action( 'admin_init', array( $this, 'check_woocommerce_dependency' ), 12 );

			// Don't bug the user if they don't want to see any notices.
			add_action( 'admin_init', array( $this, 'dont_bug_me' ), 15 );

			// Display other admin notices when required. All are dismissible.
			add_action( 'admin_print_styles', array( $this, 'add_review_notice' ), 0 );
			add_action( 'admin_print_styles', array( $this, 'add_pre_release_notice' ), 0 );
			add_action( 'admin_print_styles', array( $this, 'add_upgrade_warning_notice' ), 0 );
		} // END __construct()

		/**
		 * Checks the environment on loading WordPress, just in case the environment changes after activation.
		 *
		 * @access  public
		 * @since   2.6.0
		 * @version 2.6.2
		 * @return  bool
		 */
		public function check_php() {
			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			if ( ! CoCart_Helpers::is_environment_compatible() && is_plugin_active( plugin_basename( COCART_FILE ) ) ) {
				CoCart::deactivate_plugin();
				add_action( 'admin_notices', array( $this, 'requirement_php_notice' ) );
				return false;
			}

			return true;
		} // END check_php()

		/**
		 * Checks that the WordPress version meets the plugin requirement.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.3.0
		 * @return  bool
		 */
		public function check_wp() {
			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			if ( ! CoCart_Helpers::is_wp_version_gte( CoCart::$required_wp ) ) {
				CoCart::deactivate_plugin();
				add_action( 'admin_notices', array( $this, 'requirement_wp_notice' ) );
				return false;
			}

			return true;
		} // END check_wp()

		/**
		 * Check WooCommerce Dependency.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.3.0
		 */
		public function check_woocommerce_dependency() {
			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			if ( ! defined( 'WC_VERSION' ) ) {
				CoCart::deactivate_plugin();
				add_action( 'admin_notices', array( $this, 'woocommerce_not_installed' ) );
				return false;
			}

			else if ( version_compare( WC_VERSION, CoCart::$required_woo, '<' ) ) {
				add_action( 'admin_notices', array( $this, 'required_wc_version_failed' ) );
				return false;
			}
		} // END check_woocommerce_dependency()

		/**
		 * Don't bug the user if they don't want to see any notices.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.6.0
		 * @global  $current_user
		 */
		public function dont_bug_me() {
			global $current_user;

			$user_hidden_notice = false;

			// If the user is allowed to install plugins and requested to hide the review notice then hide it for that user.
			if ( ! empty( $_GET['hide_cocart_review_notice'] ) && CoCart_Helpers::user_has_capabilities() ) {
				add_user_meta( $current_user->ID, 'cocart_hide_review_notice', '1', true );
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
		 * Displays plugin review notice.
		 * 
		 * Shown after 2 weeks or more from the time the plugin was installed.
		 * 
		 * @access public
		 * @since  2.3.0
		 * @global $current_user
		 * @return void|bool
		 */
		public function add_review_notice() {
			global $current_user;

			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			// Notice should only show on a CoCart page.
			if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
				return false;
			}

			// Is admin review notice hidden?
			$hide_review_notice = get_user_meta( $current_user->ID, 'cocart_hide_review_notice', true );

			// Check if we need to display the review plugin notice.
			if ( empty( $hide_review_notice ) ) {
				// If it has been 2 weeks or more since activating the plugin then display the review notice.
				if ( ( intval( time() - self::$install_date ) ) > WEEK_IN_SECONDS * 2 ) {
					add_action( 'admin_notices', array( $this, 'plugin_review_notice' ) );
				}
			}
		} // END add_review_notice()

		/**
		 * Displays notice if user is testing pre-release version of the plugin.
		 * 
		 * @access public
		 * @since  2.3.0
		 * @global $current_user
		 * @return void|bool
		 */
		public function add_pre_release_notice() {
			global $current_user;

			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			// Notice should only show on a CoCart page.
			if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
				return false;
			}

			// Is this version of CoCart a pre-release?
			if ( CoCart_Helpers::is_cocart_pre_release() && empty( get_transient( 'cocart_beta_notice_hidden' ) ) ) {
				add_action( 'admin_notices', array( $this, 'beta_notice' ) );
			}
		} // END add_pre_release_notice()

		/**
		 * Displays notice with an upgrade warning when a future release is coming.
		 *
		 * @access public
		 * @since  2.3.0
		 * @global $current_user
		 * @return void|bool
		 */
		public function add_upgrade_warning_notice() {
			global $current_user;

			// If the current user can not install plugins then return nothing!
			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				return false;
			}

			// Notice should only show on a CoCart page.
			if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
				return false;
			}

			// Upgrade warning notice will disappear once the new release is installed.
			$upgrade_notice = get_transient( 'cocart_upgrade_notice_hidden' );
			$next_version   = get_transient( 'cocart_next_version' );

			// If the next version is higher than the previous upgrade version then clear transient to show upgrade notice again.
			if ( ! empty( $upgrade_notice ) && version_compare( COCART_NEXT_VERSION, $next_version, '>' ) ) {
				delete_transient( 'cocart_upgrade_notice_hidden' );
			}

			if ( ! CoCart_Helpers::is_cocart_pre_release() && version_compare( strstr( COCART_VERSION, '-', true ), COCART_NEXT_VERSION, '<' ) && empty( get_transient( 'cocart_upgrade_notice_hidden' ) ) ) {
				add_action( 'admin_notices', array( $this, 'upgrade_warning' ) );
				set_transient( 'cocart_next_version', COCART_NEXT_VERSION );
			}
		} // END add_upgrade_warning_notice()

		/**
		 * Shows an upgrade warning notice if the installed version is less
		 * than the new release coming soon.
		 *
		 * @access  public
		 * @since   1.2.3
		 * @version 2.6.0
		 */
		public function upgrade_warning() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-upgrade-warning.php';
		} // END upgrade_warning()

		/**
		 * Show the PHP requirement notice.
		 *
		 * @access public
		 * @since  2.6.0
		 */
		public function requirement_php_notice() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-requirement-php.php';
		} // END requirement_php_notice()

		/**
		 * Show the WordPress requirement notice.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.6.0
		 * @return  void
		 */
		public function requirement_wp_notice() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-requirement-wp.php';
		} // END requirement_wp_notice()

		/**
		 * WooCommerce is Not Installed or Activated Notice.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.6.0
		 * @return  void
		 */
		public function woocommerce_not_installed() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-wc-not-installed.php';
		} // END woocommerce_not_installed()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails and
		 * provide an update button if the user has admin capabilities to update plugins.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.6.0
		 * @return  void
		 */
		public function required_wc_version_failed() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-required-wc.php';
		} // END required_wc_version_failed()

		/**
		 * Show the beta notice.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.6.0
		 * @return  void
		 */
		public function beta_notice() {
			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-trying-beta.php';
		} // END beta_notice()

		/**
		 * Show the plugin review notice.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.6.0
		 * @return  void
		 */
		public function plugin_review_notice() {
			$install_date = self::$install_date;

			include_once COCART_ABSPATH . 'includes/admin/views/html-notice-please-review.php';
		} // END plugin_review_notice()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
