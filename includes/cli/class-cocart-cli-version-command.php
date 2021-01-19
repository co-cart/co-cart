<?php
/**
 * Returns the version of CoCart via CLI.
 *
 * @author   Sébastien Dumont
 * @category CLI
 * @package  CoCart\CLI
 * @since    2.7.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_CLI_Version_Command {

	/**
	 * Registers the version commands.
	 */
	public function __construct() {
		WP_CLI::add_command( 'cocart version', array( 'CoCart_CLI_Version_Command', 'version' ) );
		WP_CLI::add_command( 'cocart db-version', array( 'CoCart_CLI_Version_Command', 'db_version' ) );
	}

	/**
	 * Returns the version of CoCart.
	 *
	 * @access public
	 * @static
	 * @global $wpdb
	 */
	public static function version() {
		global $wpdb;

		$wpdb->hide_errors();

		$current_version = get_option( 'cocart_version' );

		/* translators: 2: Version of CoCart */
		WP_CLI::log(
			WP_CLI::colorize(
				'%y' . sprintf( __( '%s Version is %s', 'cart-rest-api-for-woocommerce' ), 'CoCart', $current_version )
			)
		);
	} // END version()

	/**
	 * Returns the database version of CoCart.
	 *
	 * @access public
	 * @static
	 * @global $wpdb
	 */
	public static function db_version() {
		global $wpdb;

		$wpdb->hide_errors();

		$db_version = get_option( 'cocart_db_version' );

		/* translators: 2: Database Version of CoCart */
		WP_CLI::log(
			WP_CLI::colorize(
				'%y' . sprintf( __( '%s Database Version is %s', 'cart-rest-api-for-woocommerce' ), 'CoCart', $db_version )
			)
		);
	} // END db_version()

} // END class

new CoCart_CLI_Version_Command();