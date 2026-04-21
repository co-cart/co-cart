<?php
/**
 * CoCart - Admin Menus.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Menus
 * @since   2.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Menus' ) ) {

	class CoCart_Admin_Menus {

		/**
		 * A list with the objects that handle submenu pages
		 *
		 * @access public
		 * @var    array
		 */
		public $submenu_pages = array();

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			// Add and remove main plugin page.
			add_action( 'admin_menu', array( $this, 'add_main_menu_page' ), 10 );
			add_action( 'admin_menu', array( $this, 'remove_main_menu_page' ), 11 );

			// Add submenu pages.
			add_action( 'admin_menu', array( $this, 'load_admin_submenu_pages' ), 9 );
		} // END __construct()

		/**
		 * Add CoCart to the menu.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.10.5
		 */
		public function add_main_menu_page() {
			add_menu_page(
				'CoCart',
				'CoCart',
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'cocart',
				function () {
					return '';
				},
				'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill-rule="oddeven" version="1.1" viewBox="0 0 16 16"><path transform="scale(.8)" d="m2.1875 1.25a0.93759 0.93759 0 1 0 0 1.875h0.25c0.80673 7.844e-4 1.4661 0.59617 1.5508 1.3984l0.14453 1.373a0.93759 0.93759 0 0 0 0.029297 0.2793l0.60547 5.748c0.18391 1.7418 1.6684 3.0771 3.4199 3.0762h5.2793c1.5049-4.6e-4 2.8408-0.98742 3.2832-2.4258l1.959-6.3613a0.93759 0.93759 0 0 0-0.89648-1.2129h-11.887l-0.070312-0.67383c-0.18371-1.7399-1.6664-3.0745-3.416-3.0762a0.93759 0.93759 0 0 0-0.0019531 0h-0.25zm3.9355 5.625h10.42l-1.584 5.1465c-0.20279 0.65927-0.80243 1.1033-1.4922 1.1035h-5.2793a0.93759 0.93759 0 0 0-0.0019531 0c-0.80767 4.26e-4 -1.4679-0.59524-1.5527-1.3984l-0.50977-4.8516zm1.377 9.375a1.2501 1.2501 0 1 0 0 2.5h0.011719a1.2501 1.2501 0 1 0 0-2.5h-0.011719zm6.25 0a1.2501 1.2501 0 1 0 0 2.5h0.011719a1.2501 1.2501 0 1 0 0-2.5h-0.011719z" style="color-rendering:auto;color:#000000;dominant-baseline:auto;fill-rule:nonzero;fill:#ffffff;font-feature-settings:normal;font-variant-alternates:normal;font-variant-caps:normal;font-variant-ligatures:normal;font-variant-numeric:normal;font-variant-position:normal;image-rendering:auto;isolation:auto;mix-blend-mode:normal;opacity:.94;shape-padding:0;shape-rendering:auto;solid-color:#000000;stroke:none;text-decoration-color:#000000;text-decoration-line:none;text-decoration-style:solid;text-indent:0;text-orientation:mixed;text-transform:none;white-space:normal"/></svg>' ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				80
			);
		} // END add_main_menu_page()

		/**
		 * Remove the main menu page as we will rely only on submenu pages.
		 *
		 * @access public
		 *
		 * @since 3.10.0 Introduced.
		 */
		public function remove_main_menu_page() {
			remove_submenu_page( 'cocart', 'cocart' );
		} // END remove_main_menu_page()

		/**
		 * Sets up all objects that handle submenu pages and adds them to the
		 * $submenu_pages property of the plugin.
		 *
		 * @access public
		 *
		 * @since 3.10.0 Introduced.
		 */
		public function load_admin_submenu_pages() {
			/**
			 * Hook to register submenu_pages class handlers
			 * The array element should be 'submenu_page_slug' => array( 'class_name' => array(), 'data' => array() )
			 *
			 * @since 3.10.0 Introduced.
			 *
			 * @param array $submenus Array of submenu pages.
			 */
			$submenu_pages = apply_filters( 'cocart_register_submenu_page', array() );

			if ( empty( $submenu_pages ) ) {
				return;
			}

			foreach ( $submenu_pages as $submenu_page_slug => $submenu_page ) {
				if ( empty( $submenu_page['data'] ) ) {
					continue;
				}

				if ( empty( $submenu_page['data']['page_title'] ) || empty( $submenu_page['data']['menu_title'] ) || empty( $submenu_page['data']['capability'] ) || empty( $submenu_page['data']['menu_slug'] ) ) {
					continue;
				}

				$this->submenu_pages[ $submenu_page['data']['menu_slug'] ] = new $submenu_page['class_name']( $submenu_page['data']['page_title'], $submenu_page['data']['menu_title'], $submenu_page['data']['capability'], $submenu_page['data']['menu_slug'] );

				if ( CoCart_Helpers::is_wc_version_gte( '4.0' ) && function_exists( 'wc_admin_connect_page' ) ) {
					if ( 'cocart-setup' !== $submenu_page['data']['menu_slug'] ) {
						wc_admin_connect_page(
							array(
								'id'        => $submenu_page['data']['menu_slug'],
								'screen_id' => 'cocart_page_' . $submenu_page['data']['menu_slug'],
								'title'     => array(
									'CoCart',
									preg_replace( '/\d/', '', wp_strip_all_tags( $submenu_page['data']['menu_title'], true ) ),
								),
								'path'      => add_query_arg(
									array(
										'page' => $submenu_page['data']['menu_slug'],
									),
									'admin.php'
								),
							)
						);
					}
				}
			}
		} // END load_admin_submenu_pages()
	} // END class

} // END if class exists

return new CoCart_Admin_Menus();
