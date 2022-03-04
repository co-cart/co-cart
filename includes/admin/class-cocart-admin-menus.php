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
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'cocart_page_title_upgrade', function() {
				return __( 'Upgrade CoCart', 'cart-rest-api-for-woocommerce' );
			} );
			add_action( 'cocart_page_wc_bar_breadcrumb_upgrade', function() {
				return __( 'Upgrade CoCart', 'cart-rest-api-for-woocommerce' );
			} );
			add_action( 'cocart_page_section_upgrade', array( $this, 'upgrade_cocart_content' ) );
			add_filter( 'parent_file', array( $this, 'highlight_submenu_upgrade' ) );
		} // END __construct()

		/**
		 * Add CoCart to the menu and register WooCommerce admin bar.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.1.0
		 */
		public function admin_menu() {
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			switch ( $section ) {
				case 'getting-started':
					/* translators: %s: CoCart */
					$title      = sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' );
					$breadcrumb = esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' );
					break;
				default:
					$title      = apply_filters( 'cocart_page_title_' . strtolower( str_replace( '-', '_', $section ) ), 'CoCart' );
					$breadcrumb = apply_filters( 'cocart_page_wc_bar_breadcrumb_' . strtolower( str_replace( '-', '_', $section ) ), '' );
					break;
			}

			$page = admin_url( 'admin.php' );

			// Add CoCart page.
			add_menu_page(
				$title,
				'CoCart',
				apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'cocart',
				array( $this, 'cocart_page' ),
				'dashicons-cart'
			);

			// Add Setup Wizard as sub-menu.
			if ( apply_filters( 'cocart_enable_setup_wizard', true ) ) {
				add_submenu_page(
					'cocart',
					'',
					esc_attr__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ),
					apply_filters( 'cocart_screen_capability', 'manage_options' ),
					admin_url( 'admin.php?page=cocart-setup' )
				);
			}

			// If CoCart Pro is not active then add sub-menu to upgrade.
			if ( ! CoCart_Helpers::is_cocart_pro_activated() ) {
				add_submenu_page(
					'cocart',
					'',
					esc_attr__( 'Upgrade', 'cart-rest-api-for-woocommerce' ),
					apply_filters( 'cocart_screen_capability', 'manage_options' ),
					'upgrade-cocart',
					array( $this, 'redirect_upgrade' )
				);
			}

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

			/**
			 * Moves CoCart menu to the new WooCommerce Navigation Menu if it exists.
			 *
			 * @since 3.0.0
			 */
			if ( class_exists( '\Automattic\WooCommerce\Admin\Features\Navigation\Menu' ) && apply_filters( 'cocart_wc_navigation', true ) ) {
				// Add Category.
				Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_category(
					array(
						'id'     => 'cocart-category',
						'title'  => 'CoCart',
						'parent' => 'woocommerce',
					)
				);

				// Add Page.
				Automattic\WooCommerce\Admin\Features\Navigation\Menu::add_plugin_item(
					array(
						'id'         => 'cocart',
						'title'      => esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' ),
						'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
						'url'        => 'cocart',
						'parent'     => 'cocart-category',
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
			$section = ! isset( $_GET['section'] ) ? 'getting-started' : trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

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

		/**
		 * Upgrade CoCart content.
		 *
		 * @access public
		 * @static
		 * @since  3.1.0
		 */
		public static function upgrade_cocart_content() {
			include_once dirname( __FILE__ ) . '/views/html-upgrade-cocart.php';
		} // END upgrade_cocart_content()

		/**
		 * Redirects to upgrade section of CoCart Page.
		 *
		 * @access public
		 * @since  3.1.0
		 */
		public function redirect_upgrade() {
			wp_safe_redirect( admin_url( 'admin.php?page=cocart&section=upgrade' ) );
			exit;
		} // END redirect_upgrade()

		/**
		 * Sets the sub-menu active if viewing upgrade section.
		 *
		 * @access public
		 * @since  3.1.0
		 * @param  string $parent_file The parent file.
		 * @return mixed
		 */
		public function highlight_submenu_upgrade( $parent_file ) {
			global $plugin_page;

			$section = ! isset( $_GET['section'] ) ? '' : trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

			if ( 'cocart' === $plugin_page && 'upgrade' === $section ) {
				$plugin_page = 'upgrade-cocart'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			}

			return $parent_file;
		} // END highlight_submenu_upgrade()

	} // END class

} // END if class exists

return new CoCart_Admin_Menus();
