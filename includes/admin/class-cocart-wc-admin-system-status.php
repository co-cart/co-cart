<?php
/**
 * CoCart - System Status.
 *
 * Adds additional related information to the WooCommerce System Status.
 *
 * @since    2.1.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/System Status
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_WC_System_Status' ) ) {
	class CoCart_Admin_WC_System_Status {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'woocommerce_system_status_report', array( $this, 'render_system_status_items' ) );

			add_filter( 'woocommerce_debug_tools', array( $this, 'debug_button' ) );
		} // END __construct()

		/**
		 * Renders the CoCart information in the WC status page.
		 *
		 * @access public
		 * @static
		 */
		public static function render_system_status_items() {
			$data = $this->get_system_status_data();

			$system_status_sections = apply_filters( 'cocart_system_status_sections', array(
				array(
					'title'   => 'CoCart',
					'tooltip' => sprintf( __( 'This section shows any information about %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ),
					'data'    => apply_filters( 'cocart_system_status_data', $data ),
				),
			) );

			foreach ( $system_status_sections as $section ) {
				$section_title   = $section['title'];
				$section_tooltip = $section['tooltip'];
				$debug_data      = $section['data'];

				include( dirname( __FILE__ ) . '/views/html-wc-system-status.php' );
			}
		} // END render_system_status_items()

		/**
		 * Get's the system status data to return.
		 *
		 * @access private
		 * @return array $data
		 */
		private function get_system_status_data() {
			$data = array();

			$data['cocart_version'] = array(
				'name'      => _x( 'Version', 'label that indicates the version of the plugin', 'cart-rest-api-for-woocommerce' ),
				'label'     => __( 'Version', 'cart-rest-api-for-woocommerce' ),
				'data'      => COCART_VERSION,
				//'note'      => '',
				//'mark'      => '',
				//'mark_icon' => '',
				//'success'   => ''
			);

			return $data;
		} // END get_system_status_data()

		/**
		 * Adds a debug button under the tools section of WooCommerce System Status.
		 *
		 * @access public
		 * @param  array $tools - All tools before adding ours.
		 * @return array $tools - All tools after adding ours.
		 */
		public function debug_button( $tools ) {
			$tools['cocart_clear_carts'] = array(
				'name'		=> __( 'Clear cart sessions', 'cart-rest-api-for-woocommerce' ),
				'button'	=> __( 'Clear', 'cart-rest-api-for-woocommerce' ),
				'desc'		=> sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					__( 'Note:', 'cart-rest-api-for-woocommerce' ),
					sprintf( 
						__( 'This will clear all carts stored in the database but will %s clear cookies stored on customers devices.', 'cart-rest-api-for-woocommerce' ),
						'<strong>' . __( 'NOT', 'cart-rest-api-for-woocommerce' ) . '</strong>'
					)
				),
				'callback'	=> array( $this, 'debug_clear_carts' ),
			);

			return $tools;
		} // END debug_button

		/**
		 * Runs the debug callback for clearing carts.
		 *
		 * @access public
		 */
		public function debug_clear_carts() {
			CoCart_API_Session::clear_carts();

			echo '<div class="updated inline"><p>' . __( 'All carts have now been cleared from the database.', 'cart-rest-api-for-woocommerce' ) . '</p></div>';
		} // END debug_clear_cart()

	} // END class

} // END if class

return new CoCart_Admin_WC_System_Status();