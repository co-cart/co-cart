<?php
/**
 * CoCart - WooCommerce Admin: Help Improve CoCart.
 *
 * Adds a note to ask the client to help improve CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\WooCommerce Admin\Notes
 * @since   2.3.0
 * @version 3.2.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_WC_Admin_Help_Improve_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-help-improve';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME, 8 * DAY_IN_SECONDS );
	}

	/**
	 * Add note.
	 *
	 * @access public
	 * @static
	 * @param string $note_name Note name.
	 * @param string $seconds   How many seconds since CoCart was installed before the notice is shown.
	 * @param string $source    Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Otherwise, create new note.
		self::create_new_note( $args );
	} // END add_note()

	/**
	 * Get note arguments.
	 *
	 * @access  public
	 * @static
	 * @since   2.3.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
	 * @return array
	 */
	public static function get_note_args() {
		$status = Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$args = array(
			'title'   => __( 'Help improve CoCart', 'cart-rest-api-for-woocommerce' ),
			'content' => __( 'I\'d love your input to shape the future of CoCart together. Feel free to share any feedback, ideas or suggestions that you have.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'   => 'cocart-share-feedback',
					'label'  => __( 'Share feedback', 'cart-rest-api-for-woocommerce' ),
					'url'    => 'https://github.com/co-cart/co-cart/issues/new?assignees=&labels=priority%3Alow%2C+enhancement&template=enhancement.md&title=ISBAT+...',
					'status' => $status,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Help_Improve_Note();
