<?php
/**
 * WP-CLI: CoCart Update command class file.
 *
 * @author  Sébastien Dumont
 * @package CoCart\CLI
 * @since   3.0.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Allows you to update CoCart via CLI.
 *
 * @version 4.0.0
 * @package CoCart\CLI
 */
class CoCart_CLI_Update_Command {

	/**
	 * Registers the update command.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function register_commands() {
		WP_CLI::add_command(
			'cocart update', // Command.
			array( __CLASS__, 'update' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Updates the CoCart database.', 'cart-rest-api-for-woocommerce' ),
			)
		);
	} // END register_commands()

	/**
	 * Runs all pending CoCart database updates.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function update() {
		global $wpdb;

		$wpdb->hide_errors();

		include_once COCART_ABSPATH . 'includes/class-cocart-install.php';
		include_once COCART_ABSPATH . 'includes/cocart-update-functions.php';

		$current_db_version = get_option( 'cocart_db_version' );
		$update_count       = 0;
		$callbacks          = CoCart_Install::get_db_update_callbacks();
		$callbacks_to_run   = array();

		foreach ( $callbacks as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$callbacks_to_run[] = $update_callback;
				}
			}
		}

		if ( empty( $callbacks_to_run ) ) {
			// Ensure DB version is set to the current WC version to match WP-Admin update routine.
			CoCart_Install::update_db_version();

			WP_CLI::success(
				sprintf(
					/* translators: %s Database version number */
					__( 'No updates required. Database version is %s', 'cart-rest-api-for-woocommerce' ),
					get_option( 'cocart_db_version' )
				)
			);

			return;
		}

		WP_CLI::log(
			sprintf(
				/* translators: 1: Number of database updates 2: List of update callbacks */
				__( 'Found %1$d updates (%2$s)', 'cart-rest-api-for-woocommerce' ),
				count( $callbacks_to_run ),
				implode( ', ', $callbacks_to_run )
			)
		);

		$progress = \WP_CLI\Utils\make_progress_bar(
			__( 'Updating database', 'cart-rest-api-for-woocommerce' ),
			count( $callbacks_to_run )
		);

		foreach ( $callbacks_to_run as $update_callback ) {
			call_user_func( $update_callback );
			$result = false;
			while ( $result ) {
				$result = (bool) call_user_func( $update_callback );
			}
			++$update_count;
			$progress->tick();
		}

		$progress->finish();

		include_once COCART_ABSPATH . 'includes/classes/admin/class-cocart-admin-notices.php'; // @todo Remove line in future.
		CoCart_Admin_Notices::remove_notice( 'update_db', true );

		WP_CLI::success(
			sprintf(
				/* translators: 1: Number of database updates performed 2: Database version number */
				__( '%1$d update functions completed. Database version is %2$s', 'cart-rest-api-for-woocommerce' ),
				absint( $update_count ),
				get_option( 'cocart_db_version' )
			)
		);
	} // END update()
} // END class
