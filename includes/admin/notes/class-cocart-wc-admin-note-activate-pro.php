<?php
/**
 * CoCart - WooCommerce Admin: Activate CoCart Pro.
 *
 * Adds a note to ask the client to activate CoCart Pro.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\WooCommerce Admin\Notes
 * @since   2.4.0
 * @version 3.2.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_WC_Admin_Activate_Pro_Note extends CoCart_WC_Admin_Notes {

	/**
	 * Name of the note for use in the database.
	 */
	const NOTE_NAME = 'cocart-wc-admin-activate-pro';

	/**
	 * Name of the plugin slug.
	 */
	const PLUGIN_SLUG = 'cocart-pro';

	/**
	 * Name of the plugin file.
	 */
	const PLUGIN_FILE = 'cocart-pro/cocart-pro.php';

	/**
	 * Constructor
	 */
	public function __construct() {
		self::activate_plugin();
		self::add_note( self::NOTE_NAME );
	}

	/**
	 * Add note.
	 *
	 * @access public
	 * @static
	 * @since   2.4.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
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

		// Check if CoCart Pro is installed. If not true then don't create note.
		$is_plugin_installed = Automattic\WooCommerce\Admin\PluginsHelper::is_plugin_installed( self::PLUGIN_FILE );

		if ( ! $is_plugin_installed ) {
			return;
		}

		// Check if CoCart Pro is activated. If true then don't create note.
		$pro_active = Automattic\WooCommerce\Admin\PluginsHelper::is_plugin_active( self::PLUGIN_FILE );

		if ( $pro_active ) {
			$data_store = \WC_Data_Store::load( 'admin-note' );

			// We already have this note? Then mark the note as actioned.
			$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );

			if ( ! empty( $note_ids ) ) {

				$note_id = array_pop( $note_ids );

				$note = Automattic\WooCommerce\Admin\Notes\Notes::get_note( $note_id );

				if ( Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED !== $note->get_status() ) {
					$note->set_status( Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED );
					$note->save();
				}
			}

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
	 * @since   2.4.0 Introduced.
	 * @since   3.2.0 Dropped support for WooCommerce less than version 4.8
	 * @version 3.2.0
	 * @return  array
	 */
	public static function get_note_args() {
		$status = Automattic\WooCommerce\Admin\Notes\Note::E_WC_ADMIN_NOTE_ACTIONED;

		$args = array(
			'title'   => sprintf(
				/* translators: %s: CoCart Pro */
				__( '%s is not Activated!', 'cart-rest-api-for-woocommerce' ),
				'CoCart Pro'
			),
			'content' => sprintf(
				/* translators: %s: CoCart Pro */
				__( 'You have %1$s installed but it\'s not activated yet. Activate %1$s to unlock the full cart experience and support for WooCommerce extensions like subscriptions now.', 'cart-rest-api-for-woocommerce' ),
				'CoCart Pro'
			),
			'name'    => self::NOTE_NAME,
			'actions' => array(
				array(
					'name'    => 'activate-cocart-pro',
					'label'   => sprintf(
						/* translators: %s: CoCart Pro */
						__( 'Activate %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart Pro'
					),
					'url'     => add_query_arg( array( 'action' => 'activate-cocart-pro' ), admin_url( 'plugins.php' ) ),
					'status'  => $status,
					'primary' => true,
				),
			),
		);

		return $args;
	} // END get_note_args()

	/**
	 * Activates CoCart Pro when note is actioned.
	 *
	 * @access public
	 */
	public function activate_plugin() {
		if ( ! isset( $_GET['action'] ) || 'activate-cocart-pro' !== $_GET['action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$admin_url = add_query_arg(
			array(
				'action'        => 'activate',
				'plugin'        => self::PLUGIN_FILE,
				'plugin_status' => 'active',
			),
			admin_url( 'plugins.php' )
		);

		$activate_url = add_query_arg( '_wpnonce', wp_create_nonce( 'activate-plugin_' . self::PLUGIN_FILE ), $admin_url );

		wp_safe_redirect( $activate_url );
		exit;
	} // END activate_plugin()

} // END class

return new CoCart_WC_Admin_Activate_Pro_Note();
