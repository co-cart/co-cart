<?php
/**
 * Class: CoCart\CLI.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart;

use \WP_CLI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables CoCart, via the WP-CLI command line.
 *
 * @since 3.0.0 Introduced.
 */
class CLI {

	/**
	 * Registers CLI commands where needed for CoCart.
	 *
	 * Uses "WP_CLI::add_command()"
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		\WP_CLI::add_command( 'cocart', 'CoCart\CLI\Status' );

		$this->hooks();
	} // END __construct()

	/**
	 * Sets up and hooks WP CLI to CoCart CLI code.
	 *
	 * Uses "WP_CLI::add_hook()"
	 *
	 * @access private
	 */
	private function hooks() {
		\WP_CLI::add_hook( 'after_wp_load', 'CoCart\CLI\Version::register_commands' );
		\WP_CLI::add_hook( 'after_wp_load', 'CoCart\CLI\Update::register_commands' );
	}

} // END class

new CLI();
