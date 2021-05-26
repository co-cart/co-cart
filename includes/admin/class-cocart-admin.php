<?php
/**
 * CoCart - Admin.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin
 * @since    1.2.0
 * @version  3.0.0
 * @license  GPL-2.0+
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
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'admin_redirects' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 */
		public function includes() {
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-assets.php';           // Admin Assets
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-menus.php';            // Admin Menus
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-notices.php';          // Plugin Notices
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-search.php';    // Plugin Search
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-notices.php';       // WooCommerce Admin Notices
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-system-status.php'; // WooCommerce System Status
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
					include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-action-links.php';         // Plugin Action Links
					include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-screen-update.php'; // Plugin Update
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

			if ( ! empty( $_GET['cocart-install-plugin-redirect'] ) ) {
				$plugin_slug = wc_clean( wp_unslash( $_GET['cocart-install-plugin-redirect'] ) );

				if ( current_user_can( 'install_plugins' ) && in_array( $plugin_slug, CoCart_Helpers::get_wporg_cocart_plugins(), true ) ) {
					$nonce = wp_create_nonce( 'install-plugin_' . $plugin_slug );
					$url   = self_admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug . '&_wpnonce=' . $nonce );
				} else {
					$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );
				}

				wp_safe_redirect( $url );
				exit;
			}
		}

	} // END class

} // END if class exists

return new CoCart_Admin();
