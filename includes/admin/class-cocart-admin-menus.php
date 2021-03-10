<?php
/**
 * CoCart - Admin Menus.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Menus
 * @since    2.0.0
 * @version  3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Menus' ) ) {

	class CoCart_Admin_Menus {

		/**
		 * Constructor
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.0.0
		 */
		public function __construct() {
			add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'admin_menu' ) );
		} // END __construct()

		/**
		 * Add CoCart to the menu and register WooCommerce admin bar.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.0.0
		 */
		public function admin_menu() {
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( $_GET['section'] );

			switch ( $section ) {
				case 'getting-started':
					$title      = sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' );
					$breadcrumb = esc_attr( 'Getting Started', 'cart-rest-api-for-woocommerce' );
					break;
				default:
					$title      = apply_filters( 'cocart_page_title_' . strtolower( str_replace( '-', '_', $section ) ), 'CoCart' );
					$breadcrumb = apply_filters( 'cocart_page_wc_bar_breadcrumb_' . strtolower( str_replace( '-', '_', $section ) ), '' );
					break;
			}

			$page = admin_url( 'admin.php' );

			if ( is_multisite() ) {
				$page = network_admin_url( 'admin.php' );
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
						'path'      => add_query_arg(
							array(
								'page'    => 'cocart',
								'section' => $section,
							),
							$page
						),
					)
				);
			}

			// Adds CoCart page to the new WooCommerce Navigation Menu.
			if ( class_exists( '\Automattic\WooCommerce\Admin\Features\Navigation\Menu' ) ) {
				Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_item(
					array(
						'id'         => 'cocart',
						'title'      => 'CoCart',
						'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
						'url'        => 'cocart',
					)
				);
			}
		} // END admin_menu()

		/**
		 * CoCart Page
		 *
		 * @access  public
		 * @static
		 * @since   2.0.1
		 * @version 2.6.0
		 */
		public static function cocart_page() {
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( $_GET['section'] );

			switch ( $section ) {
				case 'getting-started':
					self::getting_started_content();
					break;

				default:
					do_action( 'cocart_page_section_' . strtolower( str_replace( '-', '_', $section ) ) );
					break;
			}
		} // END cocart_page()

		/**
		 * Getting Started content.
		 *
		 * @access  public
		 * @static
		 * @since   2.0.0
		 * @version 2.6.0
		 */
		public static function getting_started_content() {
			include_once dirname( __FILE__ ) . '/views/html-getting-started.php';
		} // END getting_started_content()

	} // END class

} // END if class exists

return new CoCart_Admin_Menus();
