<?php
/**
 * WP-CLI: CoCart Version command class file.
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
 * Returns the version of CoCart via CLI.
 *
 * @version 4.0.0
 * @package CoCart\CLI
 */
class CoCart_CLI_Version_Command {

	/**
	 * Registers the version commands.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function register_commands() {
		WP_CLI::add_command(
			'cocart version', // Command.
			array( __CLASS__, 'version' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Returns the version of CoCart installed.', 'cart-rest-api-for-woocommerce' ),
			)
		);

		WP_CLI::add_command(
			'cocart db-version', // Command.
			array( __CLASS__, 'db_version' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Returns the database version of CoCart installed.', 'cart-rest-api-for-woocommerce' ),
			)
		);
	} // END register_commands()

	/**
	 * Returns the version of CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function version() {
		global $wpdb;

		$wpdb->hide_errors();

		$current_version = get_option( 'cocart_version' );

		WP_CLI::log(
			WP_CLI::colorize(
				'%y' . sprintf(
					/* translators: 2: Version of CoCart */
					__( '%1$s Version is %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					$current_version
				)
			)
		);
	} // END version()

	/**
	 * Returns the database version of CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function db_version() {
		global $wpdb;

		$wpdb->hide_errors();

		$db_version = get_option( 'cocart_db_version' );

		WP_CLI::log(
			WP_CLI::colorize(
				'%y' . sprintf(
					/* translators: 2: Database Version of CoCart */
					__( '%1$s Database Version is %2$s', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					$db_version
				)
			)
		);
	} // END db_version()
} // END class
