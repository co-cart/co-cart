<?php
/**
 * CoCart - Admin Assets.
 *
 * @since    1.2.0
 * @version  2.0.3
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
 * @license  GPL-2.0+
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
		 * @access  public
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 10 );

			// Adds admin body classes.
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );
		} // END __construct()

		/**
		 * Registers and enqueues Stylesheets.
		 *
		 * @access public
		 */
		public function admin_styles() {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';
			$suffix    = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

			if ( in_array( $screen_id, CoCart_Admin::cocart_get_admin_screens() ) ) {
				wp_register_style( COCART_SLUG . '_admin', COCART_URL_PATH . '/assets/css/admin/cocart' . $suffix . '.css' );
				wp_enqueue_style( COCART_SLUG . '_admin' );
			}
		} // END admin_styles()

		/**
		 * Adds admin body class for CoCart page.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.0.3
		 * @param   string $classes
		 * @return  string $classes
		 */
		public function admin_body_class( $classes ) {
			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			if ( $screen_id == 'toplevel_page_cocart' ) {
				$classes = ' cocart ';
			}

			return $classes;
		} // END admin_body_class()

	} // END class

} // END if class exists

return new CoCart_Admin_Assets();
