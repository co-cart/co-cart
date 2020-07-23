<?php
/**
 * CoCart - WooCommerce Admin: Thanks for Installing
 *
 * Adds a note for the client thanking them for installing the plugin.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/WooCommerce Admin/Notes
 * @since    2.3.0
 * @version  2.4.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_WC_Admin_Thanks_Install_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-thanks-install';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME );
	}

	/**
	 * Add note.
	 *
	 * @access  public
	 * @static
	 * @since   2.3.0
	 * @version 2.4.0
	 * @param   $note_name  Note name.
	 * @param   $seconds    How many seconds since CoCart was installed before the notice is shown.
	 * @param   $source     Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		$data_store = \WC_Data_Store::load( 'admin-note' );

		// We already have this note? Then don't create it again.
		$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );
		if ( ! empty( $note_ids ) ) {
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
			'title'   => sprintf( __( 'Thank you for installing %s!', 'cart-rest-api-for-woocommerce' ), 'CoCart' ),
			'content' => __( 'Now you are ready to start developing your headless store. Visit the documentation site for examples, action hooks and filters and more.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-view-documentation',
					'label'   => __( 'View Documentation', 'cart-rest-api-for-woocommerce' ),
					'url'     => 'https://docs.cocart.xyz/?utm_source=inbox',
					'status'  => Automattic\WooCommerce\Admin\Notes\WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED,
					'primary' => true
				)
			)
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Thanks_Install_Note();