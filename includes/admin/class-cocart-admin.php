<?php
/**
 * CoCart - Admin.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin
 * @since    1.2.0
 * @version  3.0.7
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
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 3.0.0
		 */
		public function includes() {
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-assets.php';           // Admin Assets.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-menus.php';            // Admin Menus.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-notices.php';          // Plugin Notices.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-admin-plugin-search.php';    // Plugin Search.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-notices.php';       // WooCommerce Admin Notices.
			include_once COCART_ABSPATH . 'includes/admin/class-cocart-wc-admin-system-status.php'; // WooCommerce System Status.
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

	} // END class

} // END if class exists

return new CoCart_Admin();
