<?php
/**
 * Handle data for the current customers session.
 *
 * @author   Sébastien Dumont
 * @category Abstracts
 * @package  CoCart\Admin\Abstracts
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Notices
 */
abstract class CoCart_Notices {

	/**
	 * Stores notices.
	 *
	 * @access public
	 * @static
	 * @var    array
	 */
	public static $notices = array();

	/**
	 * Constructor
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'wp_loaded', array( $this, 'hide_notices' ) );

		// If the current user has capabilities then add notices.
		if ( CoCart_Helpers::user_has_capabilities() ) {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}
	} // END __construct()

	/**
	 * Get notices
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function get_notices() {
		return self::$notices;
	} // END get_notices()

	/**
	 * Remove all notices.
	 *
	 * @access public
	 * @static
	 */
	public static function remove_all_notices() {
		self::$notices = array();
	} // END remove_all_notices()

	/**
	 * Show a notice.
	 *
	 * @access public
	 * @static
	 * @param  string $name Notice name.
	 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
	 */
	public static function add_notice( $name, $force_save = false ) {
		self::$notices = array_unique( array_merge( self::get_notices(), array( $name ) ) );

		if ( $force_save ) {
			// Adding early save to prevent more race conditions with notices.
			self::store_notices();
		}
	} // END add_notice()

	/**
	 * Remove a notice from being displayed.
	 *
	 * @access public
	 * @static
	 * @param  string $name Notice name.
	 * @param  bool   $force_save Force saving inside this method instead of at the 'shutdown'.
	 */
	public static function remove_notice( $name, $force_save = false ) {
		$notices = self::get_notices();

		// Check that the notice exists before attempting to remove it.
		if ( in_array( $name, $notices ) ) {
			self::$notices = array_diff( $notices, array( $name ) );

			delete_option( 'cocart_admin_notice_' . $name );

			if ( $force_save ) {
				// Adding early save to prevent more conditions with notices.
				self::store_notices();
			}
		}
	} // END remove_notice()

	/**
	 * See if a notice is being shown.
	 *
	 * @access public
	 * @param  string $name Notice name.
	 * @return boolean
	 */
	public function has_notice( $name ) {
		return in_array( $name, self::get_notices(), true );
	} // END has_notice()

	/**
	 * Hide a notice if the GET variable is set.
	 *
	 * @access public
	 */
	public function hide_notices() {
		if ( isset( $_GET['cocart-hide-notice'] ) && isset( $_GET['_cocart_notice_nonce'] ) ) {
			if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_cocart_notice_nonce'] ) ), 'cocart_hide_notices_nonce' ) ) {
				wp_die( esc_html__( 'Action failed. Please refresh the page and retry.', 'cart-rest-api-for-woocommerce' ) );
			}

			if ( ! CoCart_Helpers::user_has_capabilities() ) {
				wp_die( esc_html__( 'You don&#8217;t have permission to do this.', 'cart-rest-api-for-woocommerce' ) );
			}

			$hide_notice = sanitize_text_field( wp_unslash( $_GET['cocart-hide-notice'] ) );

			self::remove_notice( $hide_notice );

			update_user_meta( get_current_user_id(), 'dismissed_cocart_' . $hide_notice . '_notice', true );

			do_action( 'cocart_hide_' . $hide_notice . '_notice' );
		}
	} // END hide_notices()

	/**
	 * Add notices.
	 *
	 * @access public
	 */
	public function add_notices() {
		$notices = self::get_notices();

		if ( empty( $notices ) ) {
			return;
		}

		// Notice should only show on specific pages.
		if ( ! CoCart_Helpers::is_cocart_admin_page() ) {
			return;
		}

		foreach ( $notices as $notice ) {
			if ( ! empty( self::$core_notices[ $notice ] ) && apply_filters( 'cocart_show_admin_notice', true, $notice ) ) {
				add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, self::$core_notices[ $notice ] ) );
			} else {
				add_action( is_multisite() ? 'network_admin_notices' : 'admin_notices', array( $this, 'output_custom_notices' ) );
			}
		}
	} // END add_notices()

	/**
	 * Add a custom notice.
	 *
	 * @access public
	 * @static
	 * @param  string $name        Notice name.
	 * @param  string $notice_html Notice HTML.
	 */
	public function add_custom_notice( $name, $notice_html ) {
		self::add_notice( $name );
		update_option( 'cocart_admin_notice_' . $name, wp_kses_post( $notice_html ) );
	} // END add_custom_notice()

	/**
	 * Output any stored custom notices.
	 *
	 * @access public
	 * @return void
	 */
	public function output_custom_notices() {
		$notices = self::get_notices();

		if ( ! empty( $notices ) ) {
			foreach ( $notices as $notice ) {
				if ( empty( self::$core_notices[ $notice ] ) ) {
					$notice_html = get_site_option( 'cocart_admin_notice_' . $notice );

					if ( $notice_html ) {
						include COCART_ABSPATH . 'includes/admin/views/html-notice-custom.php';
					}
				}
			}
		}
	} // END output_custom_notices()

}
