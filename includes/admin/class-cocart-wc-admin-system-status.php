<?php
/**
 * CoCart - WooCommerce System Status.
 *
 * Adds additional related information to the WooCommerce System Status.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/WooCommerce System Status
 * @since    2.1.0
 * @version  2.4.0
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
					'tooltip' => sprintf( esc_html__( 'This section shows any information about %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ),
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
		 * Gets the system status data to return.
		 *
		 * @access public
		 * @return array $data
		 */
		public function get_system_status_data() {
			$data = array();

			$data['cocart_version'] = array(
				'name'      => _x( 'Version', 'label that indicates the version of the plugin', 'cart-rest-api-for-woocommerce' ),
				'label'     => esc_html__( 'Version', 'cart-rest-api-for-woocommerce' ),
				'note'      => COCART_VERSION,
				'mark'      => '',
				'mark_icon' => '',
			);

			$data['cocart_db_version'] = array(
				'name'      => _x( 'Database Version', 'label that indicates the database version of the plugin', 'cart-rest-api-for-woocommerce' ),
				'label'     => esc_html__( 'Database Version', 'cart-rest-api-for-woocommerce' ),
				'note'      => get_option( 'cocart_version', null ),
				'tip'       => sprintf( esc_html__( 'The version of %s reported by the database. This should be the same as the version of the plugin.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ),
				'mark'      => '',
				'mark_icon' => '',
			);

			$data['cocart_install_date'] = array(
				'name'      => _x( 'Install Date', 'label that indicates the install date of the plugin', 'cart-rest-api-for-woocommerce' ),
				'label'     => esc_html__( 'Install Date', 'cart-rest-api-for-woocommerce' ),
				'note'      => date( get_option( 'date_format' ), get_site_option( 'cocart_install_date', time() ) ),
				'mark'      => '',
				'mark_icon' => '',
			);

			$data['cocart_carts_in_session'] = array(
				'name'      => _x( 'Carts in Session', 'label that indicates the number of carts in session', 'cart-rest-api-for-woocommerce' ),
				'label'     => esc_html__( 'Carts in Session', 'cart-rest-api-for-woocommerce' ),
				'note'      => $this->carts_in_session(),
				'mark'      => '',
				'mark_icon' => '',
			);

			$data['cocart_carts_expired'] = array(
				'name'      => _x( 'Carts Expired', 'label that indicates the number of carts expired', 'cart-rest-api-for-woocommerce' ),
				'label'     => esc_html__( 'Carts Expired', 'cart-rest-api-for-woocommerce' ),
				'note'      => $this->count_carts_expired(),
				'mark'      => '',
				'mark_icon' => '',
			);

			return $data;
		} // END get_system_status_data()

		/**
		 * Counts how many carts are currently in session.
		 *
		 * @access public
		 * @param  string - Session table to count.
		 * @global $wpdb
		 * @return int - Number of carts in session.
		 */
		public function carts_in_session( $session = '' ) {
			global $wpdb;

			if ( empty( $session ) ) {
				$results = $wpdb->get_results( "
					SELECT COUNT(cart_id) as count 
					FROM {$wpdb->prefix}cocart_carts
				", ARRAY_A );
			} else {
				$results = $wpdb->get_results( "
				SELECT COUNT(session_id) as count 
				FROM {$wpdb->prefix}woocommerce_sessions
				", ARRAY_A );
			}

			return $results[0]['count'];
		} // END carts_in_session()

		/**
		 * Counts how many carts have expired.
		 *
		 * @access public
		 * @global $wpdb
		 * @return int - Number of carts expired.
		 */
		public function count_carts_expired() {
			global $wpdb;

			$results = $wpdb->get_results( $wpdb->prepare( "
				SELECT COUNT(cart_id) as count
				FROM {$wpdb->prefix}cocart_carts 
				WHERE cart_expiry < %d", time()
			), ARRAY_A );

			return $results[0]['count'];
		} // END count_carts_expired()

		/**
		 * Adds debug buttons under the tools section of WooCommerce System Status.
		 *
		 * @access  public
		 * @since   2.1.0
		 * @version 2.4.0
		 * @param   array $tools - All tools before adding ours.
		 * @return  array $tools - All tools after adding ours.
		 */
		public function debug_button( $tools ) {
			$tools['cocart_clear_carts'] = array(
				'name'   => esc_html__( 'Clear cart sessions', 'cart-rest-api-for-woocommerce' ),
				'button' => esc_html__( 'Clear all', 'cart-rest-api-for-woocommerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					esc_html__( 'Note:', 'cart-rest-api-for-woocommerce' ),
					esc_html__( 'This will clear all carts in session handled by CoCart and saved carts.', 'cart-rest-api-for-woocommerce' )
				),
				'callback' => array( $this, 'debug_clear_carts' ),
			);

			$tools['cocart_cleanup_carts'] = array(
				'name'   => esc_html__( 'Clear expired carts', 'cart-rest-api-for-woocommerce' ),
				'button' => esc_html__( 'Clear expired', 'cart-rest-api-for-woocommerce' ),
				'desc'   => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					esc_html__( 'Note:', 'cart-rest-api-for-woocommerce' ),
					sprintf(
						esc_html__( 'This will clear all expired carts %s stored in the database.', 'cart-rest-api-for-woocommerce' ),
						'<strong>' . esc_html__( 'only', 'cart-rest-api-for-woocommerce' ) . '</strong>'
					)
				),
				'callback' => array( $this, 'debug_clear_expired_carts' ),
			);

			$carts_to_sync = $this->carts_in_session( 'woocommerce' );

			// Only show synchronize carts option if required.
			if ( $carts_to_sync > 0 ) {
				$tools['cocart_sync_carts'] = array(
					'name'   => esc_html__( 'Synchronize carts', 'cart-rest-api-for-woocommerce' ),
					'button' => sprintf( esc_html__( 'Synchronize (%d) cart/s', 'cart-rest-api-for-woocommerce' ), $carts_to_sync ),
					'desc'   => sprintf(
						'<strong class="red">%1$s</strong> %2$s',
						esc_html__( 'Note:', 'cart-rest-api-for-woocommerce' ),
						esc_html__( 'This will copy any existing carts from WooCommerce\'s session table to CoCart\'s session table in the database. If cart already exists for a customer then it will not sync for that customer.', 'cart-rest-api-for-woocommerce' )
					),
					'callback' => array( $this, 'synchronize_carts' ),
				);
			} else {
				// Remove option to clear WooCommerce's session table if empty.
				unset( $tools['clear_sessions'] );
			}

			return $tools;
		} // END debug_button

		/**
		 * Runs the debug callback for clearing all carts.
		 *
		 * @access  public
		 * @since   2.1.0
		 * @version 2.1.2
		 */
		public function debug_clear_carts() {
			$results = CoCart_API_Session::clear_carts();

			echo '<div class="updated inline"><p>' . sprintf( esc_html__( 'All active carts have been cleared and %s saved carts.', 'cart-rest-api-for-woocommerce' ), absint( $results ) ) . '</p></div>';
		} // END debug_clear_carts()

		/**
		 * Runs the debug callback for clearing expired carts ONLY.
		 *
		 * @access public
		 */
		public function debug_clear_expired_carts() {
			CoCart_API_Session::cleanup_carts();

			echo '<div class="updated inline"><p>' . esc_html__( 'All expired carts have now been cleared from the database.', 'cart-rest-api-for-woocommerce' ) . '</p></div>';
		} // END debug_clear_expired_carts()

		/**
		 * Synchronizes the carts from one session table to the other.
		 * Any cart that already exists for the customer will not sync.
		 *
		 * @access public
		 * @since  2.1.2
		 */
		public function synchronize_carts() {
			global $wpdb;

			$wpdb->query(
				"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`)
				SELECT t1.session_key, t1.session_value, t1.session_expiry
				FROM {$wpdb->prefix}woocommerce_sessions t1
				WHERE NOT EXISTS(SELECT cart_key FROM {$wpdb->prefix}cocart_carts t2 WHERE t2.cart_key = t1.session_key) ");

			echo '<div class="updated inline"><p>' . esc_html__( 'Carts are now synchronized.', 'cart-rest-api-for-woocommerce' ) . '</p></div>';
		} // END resync_carts()

	} // END class

} // END if class

return new CoCart_Admin_WC_System_Status();