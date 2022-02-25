<?php
/**
 * Enables CoCart, via the command line.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Should WP-CLI not exist, just return to prevent the plugin from crashing.
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_CLI' ) ) {

	/**
	 * CLI class.
	 */
	class CoCart_CLI {

		/**
		 * Load required files and hooks to make the CLI work.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->includes();
			$this->hooks();
		}

		/**
		 * Load command files.
		 *
		 * @access private
		 */
		private function includes() {
			require_once COCART_ABSPATH . 'includes/cli/class-cocart-cli-update-command.php';
			require_once COCART_ABSPATH . 'includes/cli/class-cocart-cli-version-command.php';
		}

		/**
		 * Sets up and hooks WP CLI to CoCart CLI code.
		 *
		 * @access private
		 */
		private function hooks() {
			WP_CLI::add_hook( 'after_wp_load', 'CoCart_CLI_Version_Command::register_commands' );
			WP_CLI::add_hook( 'after_wp_load', 'CoCart_CLI_Update_Command::register_commands' );
		}

	} // END class

} // END if class exists

new CoCart_CLI();
