<?php
/**
 * CoCart - WooCommerce Admin: Need Help?
 *
 * Adds a note to ask the client if they need help with CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\WooCommerce Admin\Notes
 * @since   2.3.0 Introduced.
 * @version 4.3.7
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Notes\Note;

class CoCart_WC_Admin_Need_Help_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-need-help';

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
	 *
	 * @static
	 *
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
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.3.0  Introduced.
	 * @since 3.2.0  Dropped support for WooCommerce less than version 4.8
	 * @since 3.10.4 Updated action buttons.
	 *
	 * @return array $args Note arguments.
	 */
	public static function get_note_args() {
		$status = Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_campaign' => 'wc-admin',
				'utm_content'  => 'wc-inbox',
			)
		);

		$args = array(
			'title'   => sprintf(
				/* translators: %s CoCart */
				__( 'Need help with %s?', 'cart-rest-api-for-woocommerce' ),
				'CoCart',
			),
			'content' => sprintf(
				/* translators: %s CoCart */
				__( 'You can ask for help by joining the %s community on Discord.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-community',
					'label'   => __( 'Join community', 'cart-rest-api-for-woocommerce' ),
					'url'     => esc_url( COCART_COMMUNITY_URL ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()
} // END class

return new CoCart_WC_Admin_Need_Help_Note();
