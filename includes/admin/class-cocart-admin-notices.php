<?php
/**
 * CoCart - Display notices in the WordPress admin.
 *
 * @since    1.2.0
 * @version  1.2.3
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Notices
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
		 * @access public
		 */
		public function __construct() {
			self::$install_date = get_site_option( 'cocart_install_date', time() );

			// Check WordPress enviroment.
			add_action( 'admin_init', array( $this, 'check_wp' ), 12 );

			// Don't bug the user if they don't want to see any notices.
			add_action( 'admin_init', array( $this, 'dont_bug_me' ), 15 );

			// Display other admin notices when required. All are dismissable.
			add_action( 'admin_print_styles', array( $this, 'add_notices' ), 0 );
		} // END __construct()

		/**
		 * Checks that the WordPress version meets the plugin requirement.
		 *
		 * @access public
		 * @global string $wp_version
		 * @return bool
		 */
		public function check_wp() {
			global $wp_version;

			// If the current user can not install plugins then return nothing!
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			if ( ! version_compare( $wp_version, COCART_WP_VERSION_REQUIRE, '>=' ) ) {
				add_action( 'admin_notices', array( $this, 'requirement_wp_notice' ) );
				return false;
			}

			return true;
		} // END check_wp()

		/**
		 * Don't bug the user if they don't want to see any notices.
		 *
		 * @access  public
		 * @version 1.2.3
		 * @global  $current_user
		 */
		public function dont_bug_me() {
			global $current_user;

			$user_hidden_notice = false;

			// If the user is allowed to install plugins and requested to hide the review notice then hide it for that user.
			if ( ! empty( $_GET['hide_cocart_review_notice'] ) && current_user_can( 'install_plugins' ) ) {
				add_user_meta( $current_user->ID, 'cocart_hide_review_notice', '1', true );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dismiss upgrade notice then hide it 2 weeks.
			if ( ! empty( $_GET['hide_cocart_upgrade_notice'] ) && current_user_can( 'install_plugins' ) ) {
				set_transient( 'cocart_upgrade_notice_hidden', 'hidden', WEEK_IN_SECONDS * 2 );
				$user_hidden_notice = true;
			}

			// If the user is allowed to install plugins and requested to dimiss beta notice then hide it for 1 week.
			if ( ! empty( $_GET['hide_cocart_beta_notice'] ) && current_user_can( 'install_plugins' ) ) {
				set_transient( 'cocart_beta_notice_hidden', 'hidden', WEEK_IN_SECONDS );
				$user_hidden_notice = true;
			}

			// Did user hide a notice?
			if ( $user_hidden_notice ) {
				// Redirect to the plugins page.
				wp_safe_redirect( admin_url( 'plugins.php' ) );
				exit;
			}
		} // END dont_bug_me()

		/**
		 * Displays admin notices for the following:
		 *
		 * 1. Plugin review, shown after 7 days or more from the time the plugin was installed.
		 * 2. Testing a beta/pre-release version of the plugin.
		 * 3. Upgrade warning for a future release coming.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 1.2.3
		 * @global  $current_user
		 * @return  void|bool
		 */
		public function add_notices() {
			global $current_user;

			// If the current user can not install plugins then return nothing!
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			// Notices should only show on the main dashboard and on the plugins screen.
			if ( ! in_array( $screen_id, CoCart_Admin::cocart_get_admin_screens() ) ) {
				return false;
			}

			// Is admin review notice hidden?
			$hide_review_notice = get_user_meta( $current_user->ID, 'cocart_hide_review_notice', true );

			// Check if we need to display the review plugin notice.
			if ( empty( $hide_review_notice ) ) {
				// If it has been a week or more since activating the plugin then display the review notice.
				if ( ( intval( time() - self::$install_date ) ) > WEEK_IN_SECONDS ) {
					add_action( 'admin_notices', array( $this, 'plugin_review_notice' ) );
				}
			}

			// Is this version of CoCart a beta/pre-release?
			if ( CoCart_Admin::is_cocart_beta() && empty( get_transient( 'cocart_beta_notice_hidden' ) ) ) {
				add_action( 'admin_notices', array( $this, 'beta_notice' ) );
			}

			// Upgrade warning notice that will disappear once the new release is installed.
			$upgrade_version = '2.0.0';

			if ( version_compare( COCART_VERSION, $upgrade_version, '<' ) && empty( get_transient( 'cocart_upgrade_notice_hidden' ) ) ) {
				add_action( 'admin_notices', array( $this, 'upgrade_warning' ) );
			}
		} // END add_notices()

		/**
		 * Shows an upgrade warning notice if the installed version is less
		 * than the new release coming soon.
		 *
		 * @access public
		 * @since  1.2.3
		 */
		public function upgrade_warning() {
			include_once( dirname( __FILE__ ) . '/views/html-notice-upgrade-warning.php' );
		} // END upgrade_warning()

		/**
		 * Show the WordPress requirement notice.
		 *
		 * @access public
		 */
		public function requirement_wp_notice() {
			include( dirname( __FILE__ ) . '/views/html-notice-requirement-wp.php' );
		} // END requirement_wp_notice()

		/**
		 * Show the beta notice.
		 *
		 * @access public
		 */
		public function beta_notice() {
			include( dirname( __FILE__ ) . '/views/html-notice-trying-beta.php' );
		} // END beta_notice()

		/**
		 * Show the plugin review notice.
		 *
		 * @access public
		 */
		public function plugin_review_notice() {
			$install_date = self::$install_date;

			include( dirname( __FILE__ ) . '/views/html-notice-please-review.php' );
		} // END plugin_review_notice()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
