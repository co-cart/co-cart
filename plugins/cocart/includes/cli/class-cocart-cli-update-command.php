<?php
/**
 * WP-CLI: CoCart Update command class file.
 *
 * @author  Sébastien Dumont
 * @package CoCart\CLI
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart\CLI;

use \WP_CLI;
use CoCart\Install;
use CoCart\Admin\Notices;

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
class Update {

	/**
	 * Registers the update command.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function register_commands() {
		\WP_CLI::add_command(
			'cocart update', // Command.
			array( __CLASS__, 'update' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Updates the CoCart database.', 'cart-rest-api-for-woocommerce' ),
			)
		);
	}

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

		include_once COCART_ABSPATH . 'includes/classes/class-cocart-install.php';
		include_once COCART_ABSPATH . 'includes/cocart-update-functions.php';

		$current_db_version = get_option( 'cocart_db_version' );
		$update_count       = 0;
		$callbacks          = Install::get_db_update_callbacks();
		$callbacks_to_run   = array();

		foreach ( $callbacks as $version => $update_callbacks ) {
			if ( version_compare( $current_db_version, $version, '<' ) ) {
				foreach ( $update_callbacks as $update_callback ) {
					$callbacks_to_run[] = $update_callback;
				}
			}
		}

		if ( empty( $callbacks_to_run ) ) {
			// Ensure DB version is set to the current CoCart version to match WP-Admin update routine.
			Install::update_db_version();

			\WP_CLI::success(
				/* translators: %s Database version number */
				sprintf(
					__( 'No updates required. Database version is %s', 'cart-rest-api-for-woocommerce' ),
					get_option( 'cocart_db_version' )
				)
			);

			return;
		}

		\WP_CLI::log(
			/* translators: 1: Number of database updates 2: List of update callbacks */
			sprintf(
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
			$update_count ++;
			$progress->tick();
		}

		Install::update_db_version();
		$progress->finish();

		Notices::remove_notice( 'update_db', true );

		\WP_CLI::success(
			/* translators: 1: Number of database updates performed 2: Database version number */
			sprintf(
				__( '%1$d update functions completed. Database version is %2$s', 'cart-rest-api-for-woocommerce' ),
				absint( $update_count ),
				get_option( 'cocart_db_version' )
			)
		);
	} // END update()

} // END class
