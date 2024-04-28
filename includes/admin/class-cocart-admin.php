<?php
/**
 * CoCart - Admin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0
 * @version 3.13.0
 * @license GPL-2.0+
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
			add_action( 'init', array( $this, 'includes' ) );

			// Admin screens.
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.10.0
		 */
		public function includes() {
			include_once __DIR__ . '/abstract/abstract-class-submenu-page.php';  // Admin Abstracts.
			require_once __DIR__ . '/class-cocart-admin-assets.php';             // Admin Assets.
			require_once __DIR__ . '/class-cocart-admin-footer.php';             // Admin Footer.
			require_once __DIR__ . '/class-cocart-admin-help-tab.php';           // Admin Help Tab.
			require_once __DIR__ . '/class-cocart-admin-menus.php';              // Admin Menus.
			require_once __DIR__ . '/class-cocart-admin-notices.php';            // Plugin Notices.
			require_once __DIR__ . '/class-cocart-admin-plugin-suggestions.php'; // Plugin Suggestions.
			require_once __DIR__ . '/class-cocart-admin-plugin-search.php';      // Plugin Search.
			include_once __DIR__ . '/class-cocart-wc-admin-notices.php';         // WooCommerce Admin Notices.
			include_once __DIR__ . '/class-cocart-wc-admin-system-status.php';   // WooCommerce System Status.

			// Pages.
			require_once __DIR__ . '/pages/class-cocart-admin-pages-support.php'; // Support.
			require_once __DIR__ . '/class-cocart-admin-setup-wizard.php';        // Setup Wizard.
		} // END includes()

		/**
		 * Include admin files conditionally.
		 *
		 * @access public
		 * @since  3.0.0
		 */
		public function conditional_includes() {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			switch ( $screen->id ) {
				case 'plugins':
					require_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-action-links.php';         // Plugin Action Links.
					require_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-screen-update.php'; // Plugin Update.
					break;
			}
		} // END conditional_includes()

		/**
		 * Handle redirects to setup/welcome page after install and updates.
		 *
		 * For setup wizard, transient must be present, the user must have access rights, and we must ignore the network/bulk plugin updaters.
		 *
		 * @access public
		 * @since  3.1.0
		 */
		public function admin_redirects() {
			// If WooCommerce does not exists then do nothing as we require functions from WooCommerce to function!
			if ( ! class_exists( 'WooCommerce' ) ) {
				return;
			}

			// Prevent any further admin redirects if CoCart database failed to create.
			if ( get_transient( '_cocart_db_creation_failed' ) ) {
				return;
			}

			// Setup wizard redirect.
			if ( get_transient( '_cocart_activation_redirect' ) && apply_filters( 'cocart_enable_setup_wizard', true ) ) {
				$do_redirect  = true;
				$current_page = isset( $_GET['page'] ) ? wc_clean( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				// On these pages, or during these events, postpone the redirect.
				if ( wp_doing_ajax() || is_network_admin() || ! current_user_can( 'manage_options' ) ) {
					$do_redirect = false;
				}

				// On these pages, or during these events, disable the redirect.
				if ( 'cocart-setup' === $current_page || ! CoCart_Admin_Notices::has_notice( 'setup_wizard' ) || apply_filters( 'cocart_prevent_automatic_wizard_redirect', false ) || isset( $_GET['activate-multi'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					delete_transient( '_cocart_activation_redirect' );
					$do_redirect = false;
				}

				if ( $do_redirect ) {
					delete_transient( '_cocart_activation_redirect' );
					wp_safe_redirect( admin_url( 'admin.php?page=cocart-setup' ) );
					exit;
				}
			}
		} // END admin_redirects()
	} // END class

} // END if class exists

return new CoCart_Admin();
