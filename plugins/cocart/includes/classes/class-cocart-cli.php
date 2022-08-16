<?php
/**
 * Enables CoCart, via the WP-CLI command line.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @version 4.0.0
 */

namespace CoCart;

use \WP_CLI;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CLI class.
 */
class CLI {

	/**
	 * Registers CLI commands where needed for CoCart.
	 *
	 * @access public
	 *
	 * @uses WP_CLI::add_command()
	 */
	public function __construct() {
		\WP_CLI::add_command( 'cocart', 'CoCart\CLI\Status' );

		$this->hooks();
	} // END __construct()

	/**
	 * Sets up and hooks WP CLI to CoCart CLI code.
	 *
	 * @uses WP_CLI::add_hook()
	 *
	 * @access private
	 */
	private function hooks() {
		\WP_CLI::add_hook( 'after_wp_load', 'CoCart\CLI\Version::register_commands' );
		\WP_CLI::add_hook( 'after_wp_load', 'CoCart\CLI\Update::register_commands' );
	}

} // END class

new CLI();
