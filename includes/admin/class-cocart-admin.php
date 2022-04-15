<?php
/**
 * CoCart - Admin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0
 * @version 3.5.0
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
			add_action( 'current_screen', array( $this, 'conditional_includes' ) );
			add_action( 'admin_init', array( $this, 'admin_redirects' ) );

			// Install CoCart Plugins Action.
			add_action( 'update-custom_install-cocart-plugin', array( $this, 'install_cocart_plugin' ) );
			add_action( 'install_plugin_complete_actions', array( $this, 'install_plugin_complete_actions' ), 10, 3 );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.5.0
		 */
		public function includes() {
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-assets.php';             // Admin Assets.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-menus.php';              // Admin Menus.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-notices.php';            // Plugin Notices.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-suggestions.php'; // Plugin Suggestions.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-search.php';      // Plugin Search.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-notices.php';         // WooCommerce Admin Notices.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-system-status.php';   // WooCommerce System Status.

			// Setup Wizard.
			if ( ! empty( $_GET['page'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				switch ( $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					case 'cocart-setup':
						include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-setup-wizard.php';
						break;
				}
			}
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
					include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-action-links.php';         // Plugin Action Links.
					include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-screen-update.php'; // Plugin Update.
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

			if ( ! empty( $_GET['cocart-install-plugin-redirect'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$plugin_slug = wc_clean( wp_unslash( $_GET['cocart-install-plugin-redirect'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

				if ( current_user_can( 'install_plugins' ) && in_array( $plugin_slug, CoCart_Helpers::get_wporg_cocart_plugins(), true ) ) {
					$nonce = wp_create_nonce( 'install-cocart-plugin_' . $plugin_slug );
					$url   = self_admin_url( 'update.php?action=install-cocart-plugin&plugin=' . $plugin_slug . '&_wpnonce=' . $nonce );
				} else {
					$url = admin_url( 'plugin-install.php?tab=search&type=term&s=' . $plugin_slug );
				}

				wp_safe_redirect( $url );
				exit;
			}

			// Prevent any further admin redirects if CoCart database failed to create.
			if ( get_transient( '_cocart_db_creation_failed' ) ) {
				return;
			}

			// Setup wizard redirect.
			if ( get_transient( '_cocart_activation_redirect' ) && apply_filters( 'cocart_enable_setup_wizard', true ) ) {
				$do_redirect  = true;
				$current_page = isset( $_GET['page'] ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : false; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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

		/**
		 * Install CoCart Plugins.
		 *
		 * @access public
		 * @since  3.1.0
		 */
		public function install_cocart_plugin() {
			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_die( esc_html__( 'Sorry, you are not allowed to install plugins on this site.', 'cart-rest-api-for-woocommerce' ) );
			}

			include_once ABSPATH . 'wp-admin/includes/plugin-install.php'; // For plugins_api().

			$plugin = isset( $_REQUEST['plugin'] ) ? trim( sanitize_key( wp_unslash( $_REQUEST['plugin'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			check_admin_referer( 'install-cocart-plugin_' . $plugin );

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin,
					'fields' => array(
						'sections' => false,
					),
				)
			);

			$api = apply_filters( 'cocart_api_install_plugin', $api, $plugin );

			if ( is_wp_error( $api ) ) {
				wp_die( esc_url( $api ) );
			}

			$title = __( 'CoCart Plugin Installation', 'cart-rest-api-for-woocommerce' );

			require_once ABSPATH . 'wp-admin/admin-header.php';

			/* translators: %s: Plugin name and version. */
			$title = sprintf( __( 'Installing Plugin: %s', 'cart-rest-api-for-woocommerce' ), $api->name . ' ' . $api->version );
			$nonce = 'install-cocart-plugin_' . $plugin;
			$url   = 'update.php?action=install-cocart-plugin&plugin=' . rawurlencode( $plugin );
			$url  .= '&from=cocart';

			$type = 'web'; // Install plugin from Web.

			$upgrader = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
			$upgrader->install( $api->download_link, array( 'overwrite_package' => true ) );

			require_once ABSPATH . 'wp-admin/admin-footer.php';
		} // END install_cocart_plugin()

		/**
		 * Returns install plugin complete action link if plugin was related to CoCart.
		 *
		 * @access public
		 * @since  3.1.0
		 * @param array  $install_actions - Array of install actions.
		 * @param string $api - The API URL.
		 * @param string $plugin_file - Plugin file name.
		 */
		public function install_plugin_complete_actions( $install_actions, $api, $plugin_file ) {
			if ( strstr( $plugin_file, 'cocart-' ) ) {
				$install_actions['plugins_page'] = sprintf(
					/* translators: 1; Admin URL, 2: Link Text */
					'<a href="%1$s">%2$s</a>',
					self_admin_url( 'plugin-install.php?tab=cocart' ),
					sprintf(
						/* translators: %s: CoCart */
						__( 'Go to %s Plugin Installer', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					)
				);
			}

			return $install_actions;
		} // END install_plugin_complete_actions()

	} // END class

} // END if class exists

return new CoCart_Admin();
