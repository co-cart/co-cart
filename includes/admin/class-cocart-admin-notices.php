<?php
/**
 * CoCart - Display notices in the WordPress admin.
 *
 * @since    1.2.0
 * @version  2.0.6
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

			// Check WooCommerce dependency.
			add_action( 'plugins_loaded', array( $this, 'check_woocommerce_dependency' ) );

			// Don't bug the user if they don't want to see any notices.
			add_action( 'admin_init', array( $this, 'dont_bug_me' ), 15 );

			// Display other admin notices when required. All are dismissable.
			add_action( 'admin_print_styles', array( $this, 'add_notices' ), 0 );

			// Add after_plugin_row... action for CoCart.
			add_action( 'after_plugin_row_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_row' ), 11, 2 );
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
		 * Check WooCommerce Dependency.
		 *
		 * @access public
		 * @since  2.0.0
		 */
		public function check_woocommerce_dependency() {
			// If the current user can not install plugins then return nothing!
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			if ( ! defined( 'WC_VERSION' ) ) {
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
		 * @version 2.0.6
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
			$upgrade_version = COCART_NEXT_VERSION;

			if ( ! CoCart_Admin::is_cocart_beta() && version_compare( COCART_VERSION, $upgrade_version, '<' ) && empty( get_transient( 'cocart_upgrade_notice_hidden' ) ) ) {
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
			include_once( COCART_FILE_PATH . '/includes/admin/views/html-notice-upgrade-warning.php' );
		} // END upgrade_warning()

		/**
		 * Show the WordPress requirement notice.
		 *
		 * @access public
		 */
		public function requirement_wp_notice() {
			include( COCART_FILE_PATH . '/includes/admin/views/html-notice-requirement-wp.php' );
		} // END requirement_wp_notice()

		/**
		 * WooCommerce is Not Installed or Activated Notice.
		 *
		 * @access public
		 * @since  2.0.0
		 * @return void
		 */
		public function woocommerce_not_installed() {
			include_once( COCART_FILE_PATH . '/includes/admin/views/html-notice-wc-not-installed.php' );
		} // END woocommerce_not_installed()

		/**
		 * Display a warning message if minimum version of WooCommerce check fails and
		 * provide an update button if the user has admin capabilities to update plugins.
		 *
		 * @access public
		 * @since  2.0.0
		 * @return void
		 */
		public function required_wc_version_failed() {
			include_once( COCART_FILE_PATH . '/includes/admin/views/html-notice-required-wc.php' );
		} // END required_wc_version_failed()

		/**
		 * Show the beta notice.
		 *
		 * @access public
		 */
		public function beta_notice() {
			include( COCART_FILE_PATH . '/includes/admin/views/html-notice-trying-beta.php' );
		} // END beta_notice()

		/**
		 * Show the plugin review notice.
		 *
		 * @access public
		 */
		public function plugin_review_notice() {
			$install_date = self::$install_date;

			include( COCART_FILE_PATH . '/includes/admin/views/html-notice-please-review.php' );
		} // END plugin_review_notice()

		/**
		 * Displays a notice under the plugin row for CoCart.
		 *
		 * @access  public
		 * @since   2.0.3
		 * @version 2.0.6
		 * @param   string $file        Plugin basename.
		 * @param   array  $plugin_data Plugin information.
		 * @return  false|void
		 */
		public function plugin_row( $file, $plugin_data ) {
			?>
			<style>
			.mobile p::before {
				color: #8564d2;
				content: "\f470";
				display: inline-block;
				font: normal 20px/1 'dashicons';
				-webkit-font-smoothing: antialiased;
				-moz-osx-font-smoothing: grayscale;
				vertical-align: top;
			}
			</style>
			<?php
			$plugins_allowedtags = array(
				'a'       => array(
					'href'  => array(),
					'title' => array(),
				),
				'abbr'    => array( 'title' => array() ),
				'acronym' => array( 'title' => array() ),
				'code'    => array(),
				'em'      => array(),
				'strong'  => array(),
			);

			$plugin_name = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

			$version_mentioned = COCART_NEXT_VERSION;
			$details_url       = esc_url( COCART_NEXT_VERSION_DETAILS );

			if ( is_network_admin() || ! is_multisite() ) {
				if ( is_network_admin() ) {
					$active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
				} else {
					$active_class = is_plugin_active( $file ) ? ' active' : '';
				}

				$notice_type = 'notice-warning';

				// Only show the plugin notice if this version of CoCart is not a beta or is lower than the version mentioned in the notice.
				if ( CoCart_Admin::is_cocart_beta() || version_compare( COCART_VERSION, $version_mentioned, '>' ) ) {
					return;
				}

				echo '<tr class="plugin-update-tr' . $active_class . ' cocart-row-notice" id="' . esc_attr( 'cart-rest-api-for-woocommerce-update' ) . '" data-slug="cart-rest-api-for-woocommerce" data-plugin="' . esc_attr( $file ) . '"><td colspan="3" class="plugin-update colspanchange"><div class="mobile notice inline ' . $notice_type . ' notice-alt"><p>';

				/* translators: 1: plugin name, 2: version mentioned, 3: details URL */
				printf(
					__( 'Are you a Mobile app developer? In preparation for <strong>%1$s v%2$s</strong>, support for storing cart data will be introduced to make it easier to access specific carts in session and your feedback is needed. <a href="%3$s" target="_blank">Read this article for more details.</a>', 'cart-rest-api-for-woocommerce' ),
					$plugin_name,
					$version_mentioned,
					$details_url
				);

				echo '</p></div></td></tr>';
			}
		} // END plugin_row()

	} // END class.

} // END if class exists.

return new CoCart_Admin_Notices();
