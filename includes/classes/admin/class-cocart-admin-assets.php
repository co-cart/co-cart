<?php
/**
 * Manages CoCart dashboard assets.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0
 * @version 3.0.17
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Assets' ) ) {

	class CoCart_Admin_Assets {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			// Registers and enqueue Stylesheets.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ) );

			// Registers and enqueue Scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );

			// Adds admin body classes.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		} // END __construct()

		/**
		 * Registers and enqueues scripts for CoCart admin pages.
		 *
		 * @access public
		 *
		 * @since 4.9.0 Introduced.
		 */
		public function admin_scripts() {
			$suffix    = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( strpos( $screen_id, 'cocart-settings' ) !== false ) {
				$script_path = 'assets/js/admin/cocart-admin-settings' . $suffix . '.js';

				wp_register_script(
					'cocart-admin-settings',
					COCART_URL_PATH . '/' . $script_path,
					array( 'jquery' ),
					CoCart::get_file_version( COCART_ABSPATH . $script_path ),
					true
				);
				wp_enqueue_script( 'cocart-admin-settings' );
			}
		} // END admin_scripts()

		/**
		 * Registers and enqueue Stylesheets.
		 *
		 * @access public
		 *
		 * @since 1.2.0 Introduced.
		 * @since 3.10.0 Use of $hook_suffix parameter was added instead of using the current screen.
		 * @since 3.10.1 Deprecated $hook_suffix parameter already and use `is_cocart_admin_page` helper function instead of $hook_suffix.
		 */
		public function admin_styles() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			if ( CoCart_Helpers::is_cocart_admin_page() ) {
				$style_path = 'assets/css/admin/cocart.css';

				wp_register_style( 'cocart-admin', COCART_URL_PATH . '/' . $style_path, array(), CoCart::get_file_version( COCART_ABSPATH . $style_path ) );
				wp_enqueue_style( 'cocart-admin' );
				wp_style_add_data( 'cocart-admin', 'rtl', 'replace' );
				if ( $suffix ) {
					wp_style_add_data( 'cocart-admin', 'suffix', '.min' );
				}
			}
			if ( $suffix ) {
				wp_style_add_data( 'cocart-admin', 'suffix', '.min' );
			}
		} // END admin_styles()

		/**
		 * Adds admin body class for CoCart page.
		 *
		 * @access public
		 *
		 * @since 1.2.0 Introduced.
		 * @since 4.0.0 Merged other body classes.
		 * @since 4.9.0 Added WC and CoCart versions.
		 *
		 * @param string $classes Current classes.
		 *
		 * @return string New classes.
		 */
		public function admin_body_class( $classes ) {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( defined( 'WC_VERSION' ) ) {
				$classes .= ' wc-version-' . WC_VERSION . ' ';

				if ( CoCart_Helpers::is_wc_version_gte( '10.9' ) ) {
					$classes .= ' cocart-wc-version-gte-10_9 ';
				}
			}

			if ( defined( 'COCART_VERSION' ) ) {
				$classes .= ' cocart-version-' . COCART_VERSION . ' ';
			}

			// Add special body class for plugin install page.
			if ( 'plugin-install' === $screen_id || 'plugin-install-network' === $screen_id ) {
				if ( isset( $_GET['tab'] ) && 'cocart' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					return $classes .= ' cocart-plugin-install ';
				}
			}

			// Add body class for CoCart page.
			if ( 'toplevel_page_cocart' === $screen_id || 'toplevel_page_cocart-network' === $screen_id ) {
				$classes .= ' cocart ';
			}

			// Return current classes including CoCart page style.
			if ( isset( $_GET['page'] ) && strpos( trim( sanitize_key( wp_unslash( $_GET['page'] ) ) ), 'cocart' ) === 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return $classes .= ' cocart-pagestyles ';
			}

			return $classes;
		} // END admin_body_class()
	} // END class

} // END if class exists

return new CoCart_Admin_Assets();
