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
				'dashicons-cart',
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
								'id'        => 'cocart',
								'screen_id' => 'cocart_page_' . $submenu_page['data']['menu_slug'],
								'title'     => array(
									'CoCart',
									$submenu_page['data']['menu_title'],
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
