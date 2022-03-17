<?php
/**
 * CoCart - WooCommerce Admin: Thanks for Installing
 *
 * Adds a note for the client thanking them for installing the plugin.
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
	 * @since   2.3.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
	 * @param   string $note_name Note name.
	 * @param   string $seconds   How many seconds since CoCart was installed before the notice is shown.
	 * @param   string $source    Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments return then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		$data_store = Automattic\WooCommerce\Admin\Notes\Notes::load_data_store();

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
	 * @access  public
	 * @static
	 * @since   2.3.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
	 * @return  array
	 */
	public static function get_note_args() {
		$status = Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_UNACTIONED;

		$campaign_args = CoCart_Helpers::cocart_campaign(
			array(
				'utm_campaign' => 'wc-admin',
				'utm_content'  => 'wc-inbox',
			)
		);

		$args = array(
			'title'   => sprintf(
				/* translators: %s: CoCart */
				esc_attr__( 'Thank you for installing %s!', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			),
			'content' => __( 'Now you are ready to start developing your headless store. Visit the documentation site to learn how to access the API, view examples and find many action hooks and filters and more.', 'cart-rest-api-for-woocommerce' ),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-view-documentation',
					'label'   => __( 'View Documentation', 'cart-rest-api-for-woocommerce' ),
					'url'     => CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( 'https://docs.cocart.xyz' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Thanks_Install_Note();
