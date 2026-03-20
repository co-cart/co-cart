<?php
/**
 * Enables CoCart, via the command line.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
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
	 *
	 * Registered as a composite WP-CLI command so each public method
	 * becomes a subcommand automatically (e.g. `wp cocart status`,
	 * `wp cocart version`, etc.).
	 */
	class CoCart_CLI {

		/**
		 * Load required files and register CLI commands.
		 *
		 * @access public
		 */
		public function __construct() {
			$this->includes();

			// Register all commands on the cli_init hook — the correct
			// hook for plugins to register WP-CLI commands. Using a closure
			// keeps register_commands() private while still allowing the
			// hook to call it.
			add_action( 'cli_init', function () {
				$this->register_commands();
			} );
		}

		/**
		 * Load command files.
		 *
		 * @access private
		 */
		private function includes() {
			require_once __DIR__ . '/cli/class-cocart-cli-status-command.php';
			require_once __DIR__ . '/cli/class-cocart-cli-update-command.php';
			require_once __DIR__ . '/cli/class-cocart-cli-version-command.php';
			require_once __DIR__ . '/cli/class-cocart-cli-sessions-command.php';
		}

		/**
		 * Register all WP-CLI commands.
		 *
		 * Registering `'cocart'` with the class name string makes it a
		 * composite command — WP-CLI discovers every public method as a
		 * subcommand automatically.
		 *
		 * This prevents the "can't have subcommands" error that occurs when a callable
		 * (leaf command) is registered as the parent.
		 *
		 * This function is protected so WP-CLI does not expose it as a subcommand.
		 *
		 * @access protected
		 */
		protected function register_commands() {
			// Register `cocart` as a composite command — WP-CLI discovers
			// each public method as a subcommand automatically.
			WP_CLI::add_command( 'cocart', __CLASS__ );

			// Register `cocart sessions` as its own composite class so that
			// `wp cocart sessions list` and `wp cocart sessions exists` work
			// as subcommands. A method on `CoCart_CLI` cannot itself have
			// subcommands (it would be a leaf), so the sessions namespace
			// must be its own class-based composite.
			WP_CLI::add_command( 'cocart sessions', 'CoCart_CLI_Sessions_Command' );
		}

		// ----------------------------------------------------------------
		// Subcommands — each public method maps to `wp cocart <method>`.
		// WP-CLI converts underscores to hyphens, so `db_version` becomes
		// `wp cocart db-version`.
		// ----------------------------------------------------------------

		/**
		 * Returns all statuses for CoCart.
		 *
		 * ## OPTIONS
		 *
		 * [--format=<format>]
		 * : Render output in a particular format.
		 * ---
		 * default: table
		 * options:
		 *   - table
		 *   - json
		 *   - csv
		 *   - yaml
		 *
		 * ## EXAMPLES
		 *
		 *     # List all statuses in table format.
		 *     wp cocart status
		 *
		 *     # List all statuses in CSV format.
		 *     wp cocart status --format=csv
		 *
		 * @when after_wp_load
		 *
		 * @access public
		 *
		 * @param array $args       WP-CLI positional arguments.
		 * @param array $assoc_args WP-CLI associative arguments.
		 */
		public function status( array $args, array $assoc_args ) {
			CoCart_CLI_Status_Command::status( $args, $assoc_args );
		}

		/**
		 * Returns the version of CoCart installed.
		 *
		 * ## EXAMPLES
		 *
		 *     wp cocart version
		 *
		 * @when after_wp_load
		 *
		 * @access public
		 *
		 * @param array $args       WP-CLI positional arguments.
		 * @param array $assoc_args WP-CLI associative arguments.
		 */
		public function version( array $args, array $assoc_args ) {
			CoCart_CLI_Version_Command::version();
		}

		/**
		 * Returns the database version of CoCart installed.
		 *
		 * ## EXAMPLES
		 *
		 *     wp cocart db-version
		 *
		 * @subcommand db-version
		 *
		 * @when after_wp_load
		 *
		 * @access public
		 *
		 * @param array $args       WP-CLI positional arguments.
		 * @param array $assoc_args WP-CLI associative arguments.
		 */
		public function db_version( array $args, array $assoc_args ) {
			CoCart_CLI_Version_Command::db_version();
		}

		/**
		 * Updates the CoCart database.
		 *
		 * ## EXAMPLES
		 *
		 *     wp cocart update
		 *
		 * @when after_wp_load
		 *
		 * @access public
		 *
		 * @param array $args       WP-CLI positional arguments.
		 * @param array $assoc_args WP-CLI associative arguments.
		 */
		public function update( array $args, array $assoc_args ) {
			CoCart_CLI_Update_Command::update();
		}
	} // END class

} // END if class exists

new CoCart_CLI();
