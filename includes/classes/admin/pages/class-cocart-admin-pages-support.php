<?php
/**
 * Admin Page: Support page for CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Pages
 * @since   3.10.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_Admin_Support_Page extends CoCart_Submenu_Page {

	/**
	 * Helper init method that runs on parent __construct
	 *
	 * @access protected
	 */
	protected function init() {
		add_filter( 'cocart_register_submenu_page', array( $this, 'register_submenu_page' ), 10 );
	} // END init()

	/**
	 * Callback for the HTML output for the support page
	 *
	 * @access public
	 *
	 * @since 3.10.0 Introduced.
	 */
	public function output() {
		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_content' => 'support-page',
			)
		);
		$store_url     = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL ) );
		?>
		<div class="wrap cocart-wrapped" role="main">
			<div class="cocart-content">
			<?php
			include_once COCART_ABSPATH . 'includes/classes/admin/views/html-next-steps.php';
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
	 * @since 3.10.0 Introduced.
	 *
	 * @param array $submenu_pages Currently registered submenu pages.
	 *
	 * @return array $submenu_pages All registered submenu pages.
	 */
	public function register_submenu_page( $submenu_pages ) {
		if ( ! is_array( $submenu_pages ) ) {
			return $submenu_pages;
		}

		$submenu_pages['support'] = array(
			'class_name' => 'CoCart_Admin_Support_Page',
			'data'       => array(
				'page_title' => __( 'Support', 'cocart-core' ),
				'menu_title' => __( 'Support', 'cocart-core' ),
				'capability' => apply_filters( 'cocart_screen_capability', 'manage_options' ),
				'menu_slug'  => 'cocart-support',
			),
		);

		return $submenu_pages;
	} // END register_submenu_page()
} // END class

return new CoCart_Admin_Support_Page();
