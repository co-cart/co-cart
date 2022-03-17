<?php
/**
 * CoCart - WooCommerce Admin: Upgrade to CoCart Pro.
 *
 * Adds a note to ask the client if they are ready to upgrade to CoCart Pro.
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

class CoCart_WC_Admin_Upgrade_Pro_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-upgrade-pro';

	/**
	 * Name of the plugin file.
	 */
	const PLUGIN_FILE = 'cocart-pro/cocart-pro.php';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::add_note( self::NOTE_NAME, 30 * DAY_IN_SECONDS );
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

		// If no arguments returned then we cant create a note.
		if ( is_array( $args ) && empty( $args ) ) {
			return;
		}

		// Check if CoCart Pro is installed. If true then don't create note.
		$is_plugin_installed = Automattic\WooCommerce\Admin\PluginsHelper::is_plugin_installed( self::PLUGIN_FILE );

		if ( $is_plugin_installed ) {
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( $note_name );

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
			'title'   => __( 'Ready to take your headless store to the next level?', 'cart-rest-api-for-woocommerce' ),
			'content' => sprintf(
				/* translators: %s: CoCart Pro. */
				esc_attr__( 'Upgrade to %s and unlock more cart features and supported WooCommerce extensions.', 'cart-rest-api-for-woocommerce' ),
				'CoCart Pro'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'cocart-pro-learn-more',
					'label'   => __( 'Learn more', 'cart-rest-api-for-woocommerce' ),
					'url'     => CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'pro/' ) ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

} // END class

return new CoCart_WC_Admin_Upgrade_Pro_Note();
