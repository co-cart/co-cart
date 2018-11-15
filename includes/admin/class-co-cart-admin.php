<?php
/**
 * CoCart - Admin.
 *
 * @since    2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
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
		 * @access  public
		 */
		public function __construct() {
			// Include classes.
			add_action( 'admin_init', array( $this, 'includes' ), 10 );

			// Register and enqueue styles.
			//add_action( 'admin_enqueue_scripts', array( $this, 'admin_styles' ), 10 );

			// Filters
			add_filter( 'plugin_action_links_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta'), 10, 3 );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access public
		 */
		public function includes() {
			include( dirname( __FILE__ ) . '/class-co-cart-admin-notices.php' ); // Plugin Notices
		} // END includes()

		/**
		 * Register and enqueue stylesheets.
		 *
		 * @access public
		 * @global $wp_scripts
		 */
		public function admin_styles() {
			global $wp_scripts;

			$screen    = get_current_screen();
			$screen_id = $screen ? $screen->id : '';

			wp_register_style( 'cocart-admin', COCART_SLUG . '_admin', '/assets/css/admin/cocart' . COCART_SCRIPT_MODE . '.css' );
			wp_enqueue_style( 'cocart-admin' );
		} // END admin_styles()

		/**
		 * Plugin action links.
		 *
		 * @access public
		 * @param  array $links
		 * @return array $links
		 */
		public function plugin_action_links( $links ) {
			$plugin_action_links = array();

			if ( current_user_can( 'manage_options' ) ) {
				// Checks if CoCart Pro has been installed.
				if ( ! CoCart_Rest_API::is_cocart_pro_installed() ) {
					$plugin_action_links['go-pro'] = '<a href="' . esc_url( 'https://cocart.xyz/pro/?utm_source=plugin&utm_medium=link&utm_campaign=plugins-page' ) . '" target="_blank" style="color:green; font-weight:bold;">' . __( 'Signup for CoCart Pro', 'cart-rest-api-for-woocommerce' ) . '</a>';
				}

				//$plugin_action_links['settings'] = '<a href="' . admin_url( 'options-general.php?page=cocart-settings' ) . '">' . __( 'Settings', 'cart-rest-api-for-woocommerce' ) . '</a>';

				return array_merge( $plugin_action_links, $links );
			}

			return $links;
		} // END plugin_action_links()

		/**
		 * Plugin row meta links
		 *
		 * @access  public
		 * @since   1.0.0
		 * @version 2.0.0
		 * @param   array  $links Plugin Row Meta
		 * @param   string $file  Plugin Base file
		 * @param   array  $data  Plugin Information
		 * @return  array  $links
		 */
		public function plugin_row_meta( $links, $file, $data ) {
			if ( $file == plugin_basename( COCART_FILE ) ) {
				$links[ 1 ] = sprintf( __( 'Developed By %s', 'cart-rest-api-for-woocommerce' ), '<a href="' . $data[ 'AuthorURI' ] . '">' . $data[ 'Author' ] . '</a>' );

				$row_meta = array(
					'documentation' => '<a href="' . esc_url( 'https://co-cart.github.io/co-cart-docs/' ) . '" target="_blank">' . __( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'support'       => '<a href="' . esc_url( 'https://cocart.xyz/support/?utm_source=plugin&utm_medium=link&utm_campaign=plugins-page' ) . '" target="_blank">' . __( 'Support', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				$links = array_merge( $links, $row_meta );
			}

			return $links;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return new CoCart_Admin();
