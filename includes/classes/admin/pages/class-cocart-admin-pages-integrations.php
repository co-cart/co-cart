<?php
/**
 * Admin Page: Integrations page for CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Pages
 * @since   4.9.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_Admin_Integrations_Page extends CoCart_Submenu_Page {

	/**
	 * Helper init method that runs on parent __construct
	 *
	 * @access protected
	 */
	protected function init() {
		add_filter( 'cocart_register_submenu_page', array( $this, 'register_submenu_page' ), 12 );
		add_action( 'wp_ajax_cocart_toggle_integration', array( $this, 'ajax_toggle_integration' ) );
		add_action( 'wp_ajax_cocart_search_integrations', array( $this, 'ajax_search_integrations' ) );
	} // END init()

	/**
	 * Callback for the HTML output for the integrations page.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function output() {
		?>
		<div class="wrap cocart-wrapped cocart-wrapped--wide" role="main">
			<div class="cocart-content">
			<?php
			include_once COCART_ABSPATH . 'includes/classes/admin/views/html-integrations.php';
			?>
			</div>
		</div>
		<div class="clear"></div>
		<?php
	} // END output()

	/**
	 * Register the admin submenu page.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @param array $submenu_pages Currently registered submenu pages.
	 *
	 * @return array $submenu_pages All registered submenu pages.
	 */
	public function register_submenu_page( $submenu_pages ) {
		if ( ! is_array( $submenu_pages ) ) {
			return $submenu_pages;
		}

		/**
		 * Filter the capability required to access CoCart admin screens.
		 *
		 * @since 2.0.0 Introduced.
		 *
		 * @param string $capability Required capability.
		 */
		$screen_capability = apply_filters( 'cocart_screen_capability', 'manage_options' );

		$submenu_pages['integrations'] = array(
			'class_name' => 'CoCart_Admin_Integrations_Page',
			'data'       => array(
				'page_title' => __( 'Integrations', 'cart-rest-api-for-woocommerce' ),
				'menu_title' => __( 'Integrations', 'cart-rest-api-for-woocommerce' ),
				'capability' => $screen_capability,
				'menu_slug'  => 'cocart-integrations',
			),
		);

		return $submenu_pages;
	} // END register_submenu_page()

	/**
	 * AJAX handler: toggle a single integration on or off.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function ajax_toggle_integration() {
		check_ajax_referer( 'cocart_integrations_nonce', 'nonce' );

		/**
		 * Filter the capability required to access CoCart admin screens.
		 *
		 * @since 2.0.0 Introduced.
		 *
		 * @param string $capability Required capability.
		 */
		if ( ! current_user_can( apply_filters( 'cocart_screen_capability', 'manage_options' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		$slug   = isset( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';
		$enable = isset( $_POST['enabled'] ) && 'true' === $_POST['enabled'];

		if ( empty( $slug ) || ! array_key_exists( $slug, CoCart_Integrations::get_all() ) ) {
			wp_send_json_error( array( 'message' => __( 'Unknown integration.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		if ( $enable ) {
			CoCart_Integrations::enable( $slug );
		} else {
			CoCart_Integrations::disable( $slug );
		}

		wp_send_json_success( array( 'enabled' => CoCart_Integrations::is_enabled( $slug ) ) );
	} // END ajax_toggle_integration()

	/**
	 * AJAX handler: search integrations by name or description.
	 *
	 * Returns an HTML fragment of matching integration cards to replace the cards container.
	 *
	 * @access public
	 *
	 * @since 4.9.0 Introduced.
	 */
	public function ajax_search_integrations() {
		check_ajax_referer( 'cocart_integrations_nonce', 'nonce' );

		/**
		 * Filter the capability required to access CoCart admin screens.
		 *
		 * @since 2.0.0 Introduced.
		 *
		 * @param string $capability Required capability.
		 */
		if ( ! current_user_can( apply_filters( 'cocart_screen_capability', 'manage_options' ) ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'cart-rest-api-for-woocommerce' ) ) );
		}

		$query        = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$integrations = CoCart_Integrations::get_all();

		if ( '' !== $query ) {
			$integrations = array_filter(
				$integrations,
				function ( $args ) use ( $query ) {
					return false !== stripos( $args['name'], $query ) ||
							false !== stripos( $args['description'], $query );
				}
			);
		}

		ob_start();

		if ( empty( $integrations ) ) {
			echo '<p class="cocart-integrations-no-results">' . esc_html__( 'No integrations found.', 'cart-rest-api-for-woocommerce' ) . '</p>';
		} else {
			foreach ( $integrations as $slug => $args ) {
				$enabled   = CoCart_Integrations::is_enabled( $slug );
				$available = CoCart_Integrations::can_be_enabled( $slug );
				include COCART_ABSPATH . 'includes/classes/admin/views/html-integrations-card.php';
			}
		}

		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	} // END ajax_search_integrations()
} // END class

return new CoCart_Admin_Integrations_Page();
