<?php
/**
 * CoCart - Admin.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
 * @since    1.2.0
 * @version  2.3.0
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
			add_action( 'admin_init', array( $this, 'includes' ) );

			// Add admin page.
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		} // END __construct()

		/**
		 * Include any classes we need within admin.
		 *
		 * @access  public
		 * @since   1.2.0
		 * @version 2.3.0
		 */
		public function includes() {
			include_once( dirname( __FILE__ ) . '/class-cocart-admin-action-links.php' );         // Action Links
			include_once( dirname( __FILE__ ) . '/class-cocart-admin-assets.php' );               // Admin Assets
			include_once( dirname( __FILE__ ) . '/class-cocart-admin-plugin-screen-update.php' ); // Plugin Screen Update
			include_once( dirname( __FILE__ ) . '/class-cocart-admin-notices.php' );              // Plugin Notices
			include_once( dirname( __FILE__ ) . '/class-cocart-wc-admin-notices.php' );           // WooCommerce Admin Notices
			include_once( dirname( __FILE__ ) . '/class-cocart-wc-admin-system-status.php' );     // WooCommerce System Status
		} // END includes()

		/**
		 * Add CoCart to the menu and register WooCommerce admin bar.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.3.0
		 */
		public function admin_menu() {
			$section = isset( $_GET['section'] ) ? trim( $_GET['section'] ) : 'getting-started';

			switch( $section ) {
				case 'getting-started':
					$title      = sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' );
					$breadcrumb = esc_attr( 'Getting Started', 'cart-rest-api-for-woocommerce' );
					break;
				default:
					$title      = apply_filters( 'cocart_page_title_' . strtolower( str_replace( '-', '_', $section ) ), 'CoCart' );
					$breadcrumb = apply_filters( 'cocart_page_wc_bar_breadcrumb_' . strtolower( str_replace( '-', '_', $section ) ), '' );
					break;
			}

			// Add CoCart page.
			add_menu_page(
				$title,
				'CoCart',
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'cocart',
				array( $this, 'cocart_page' ),
				'dashicons-cart'
			);

			// Register WooCommerce Admin Bar.
			if ( CoCart_Helpers::is_wc_version_gte( '4.0' ) && function_exists( 'wc_admin_connect_page' ) ) {
				wc_admin_connect_page(
					array(
						'id'        => 'cocart-getting-started',
						'screen_id' => 'toplevel_page_cocart',
						'title'     => array(
							esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ),
							$breadcrumb,
						),
						'path'      => add_query_arg( array(
							'page'    => 'cocart',
							'section' => $section
						), 'admin.php' ),
					)
				);
			}
		} // END admin_menu()

		/**
		 * CoCart Page
		 *
		 * @access public
		 * @since  2.0.1
		 */
		public function cocart_page() {
			$section = isset( $_GET['section'] ) ? trim( $_GET['section'] ) : 'getting-started';

			switch( $section ) {
				case 'getting-started':
					$this->getting_started_content();
					break;

				default:
					do_action( 'cocart_page_section_' . strtolower( str_replace( '-', '_', $section ) ) );
					break;
			}
		} // END cocart_page()

		/**
		 * Getting Started content.
		 *
		 * @access public
		 */
		public function getting_started_content() {
			include_once( dirname( __FILE__ ) . '/views/html-getting-started.php' );
		} // END getting_started_content()

	} // END class

} // END if class exists

return new CoCart_Admin();
