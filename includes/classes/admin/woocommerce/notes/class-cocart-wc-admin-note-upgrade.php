<?php
/**
 * CoCart - WooCommerce Admin: Upgrade CoCart.
 *
 * Adds a note to ask the client if they are ready to upgrade CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\WooCommerce Admin\Notes
 * @since   3.10.4 Introduced.
 * @version 4.3.7
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Admin\Notes\Note;
use Automattic\WooCommerce\Admin\Notes\Notes;
use Automattic\WooCommerce\Admin\PluginsHelper;

class CoCart_WC_Admin_Upgrade extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-upgrade';

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
	 *
	 * @static
	 *
	 * @since 2.3.0 Introduced.
	 * @since 3.2.0 Dropped support for WooCommerce less than version 4.8
	 *
	 * @param string $note_name Note name.
	 * @param string $seconds   How many seconds since CoCart was installed before the notice is shown.
	 * @param string $source    Source of the note.
	 */
	public static function add_note( $note_name = '', $seconds = '', $source = 'cocart' ) {
		parent::add_note( $note_name, $seconds, $source );

		$args = self::get_note_args();

		// If no arguments returned then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Check if CoCart Plus or Pro is installed. If either true then don't create note.
		$is_plus_installed = PluginsHelper::is_plugin_installed( 'cocart-plus/cocart-plus.php' );
		$is_pro_installed  = PluginsHelper::is_plugin_installed( 'cocart-pro/cocart-pro.php' );

		if ( $is_plus_installed || $is_pro_installed ) {
			Notes::delete_notes_with_name( $note_name );

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
	 * @since 2.3.0 Introduced.
	 * @since 3.2.0 Dropped support for WooCommerce less than version 4.8
	 *
	 * @return array
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
			'title'   => __( 'Ready to take your headless store to the next level?', 'cart-rest-api-for-woocommerce' ),
			'content' => sprintf(
				/* translators: %s: CoCart. */
				esc_attr__( 'Upgrade %s and unlock more cart features and supported WooCommerce extensions.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-learn-more',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'pricing/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()
} // END class

return new CoCart_WC_Admin_Upgrade();
