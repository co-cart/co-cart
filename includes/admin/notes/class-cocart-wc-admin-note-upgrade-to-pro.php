<?php
/**
 * CoCart - WooCommerce Admin: Upgrade to CoCart Pro.
 *
 * Adds a note to ask the client if they are ready to upgrade to CoCart Pro.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/WooCommerce Admin/Notes
 * @since    2.3.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_WC_Admin_Upgrade_Pro_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-upgrade-pro';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME, 30 * DAY_IN_SECONDS );
	}

	/**
	 * Add note.
	 *
	 * @access public
	 * @static
	 * @param $note_name  Note name.
	 * @param $seconds    How many seconds since CoCart was installed before the notice is shown.
	 * @param $source     Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Prevent note being created if CoCart Pro is installed.
		if ( CoCart_Helpers::is_cocart_pro_installed() ) {
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( $note_name );
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function get_note_args() {
		$args = array(
			'title'   => __( 'Ready to take your headless store to the next level?', 'cart-rest-api-for-woocommerce' ),
			'content' => sprintf( __( 'Upgrade to %s and unlock more cart features and supported WooCommerce extensions.', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-pro-learn-more',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => 'https://cocart.xyz/pro/?utm_source=inbox',
					'status'  => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED,
					'primary' => true
				)
			)
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Upgrade_Pro_Note();