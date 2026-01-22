<?php
/**
 * Class: CoCart_Logger
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.4.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart logger.
 *
 * Handles logging errors.
 *
 * @since 2.1.0 Introduced.
 */
class CoCart_Logger {

	/**
	 * Log Handler Interface.
	 *
	 * @var WC_Logger|null
	 */
	private static $logger = null;

	/**
	 * Valid log types.
	 *
	 * @var array
	 */
	private const VALID_LOG_TYPES = array(
		'debug',
		'info',
		'notice',
		'warning',
		'error',
		'critical',
		'alert',
		'emergency',
	);

	/**
	 * Plugin sources.
	 *
	 * @var array
	 */
	private const SOURCES = array(
		'cocart-core' => array(
			'name'    => 'CoCart Core',
			'version' => 'COCART_VERSION',
		),
		'cocart-plus' => array(
			'name'    => 'CoCart Plus',
			'version' => 'COCART_PLUS_VERSION',
		),
		'cocart-pro'  => array(
			'name'    => 'CoCart Pro',
			'version' => 'COCART_PRO_VERSION',
		),
	);

	/**
	 * Log issues or errors within CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @uses wc_get_logger()
	 *
	 * @param string $message The message of the log.
	 * @param string $type    The type of log to record.
	 * @param string $plugin  The CoCart plugin being logged.
	 *
	 * @return void
	 */
	public static function log( $message, $type = 'debug', $plugin = 'cocart-core' ) {
		if ( ! class_exists( 'WC_Logger' ) || ! self::should_log( $type ) ) {
			return;
		}

		self::initialize_logger();

		$context   = array( 'source' => self::get_source( $plugin ) );
		$log_entry = self::format_log_entry( $message, $plugin );

		self::write_log( $log_entry, $type, $context );
	} // END log()

	/**
	 * Initialize the logger if not already done.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @uses wc_get_logger()
	 *
	 * @return void
	 */
	private static function initialize_logger() {
		if ( empty( self::$logger ) ) {
			self::$logger = wc_get_logger();
		}
	} // END initialize_logger()

	/**
	 * Check if logging should proceed.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @param string $type The type of log.
	 *
	 * @return bool True if logging should proceed, false otherwise.
	 */
	private static function should_log( $type ) {
		/**
		 * Filter to enable or disable logging.
		 *
		 * @since 2.1.0 Introduced.
		 *
		 * @param bool   $enable Whether to enable logging.
		 * @param string $type   The type of log.
		 */
		return apply_filters( 'cocart_logging', true, $type )
			&& defined( 'WP_DEBUG' )
			&& WP_DEBUG
			&& in_array( $type, self::VALID_LOG_TYPES, true );
	} // END should_log()

	/**
	 * Get the source context for logging.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @param string $plugin The plugin being logged.
	 *
	 * @return string The source context for logging.
	 */
	private static function get_source( $plugin ) {
		return isset( self::SOURCES[ $plugin ] ) ? $plugin : basename( $plugin, '.php' );
	} // END get_source()

	/**
	 * Get plugin details for unknown plugins
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @param string $plugin Plugin slug.
	 *
	 * @return array Plugin details
	 */
	private static function get_plugin_details( $plugin ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// Try to find the plugin file.
		$plugin_dir  = WP_PLUGIN_DIR;
		$plugin_file = $plugin_dir . '/' . $plugin . '/' . $plugin . '.php';

		if ( ! file_exists( $plugin_file ) ) {
			$plugin_file = $plugin_dir . '/' . $plugin . '/index.php';
		}

		if ( file_exists( $plugin_file ) ) {
			$plugin_data = get_plugin_data( $plugin_file );

			return array(
				'name'    => $plugin_data['Name'],
				'version' => $plugin_data['Version'],
			);
		}

		return array(
			'name'    => $plugin,
			'version' => 'unknown',
		);
	} // END get_plugin_details()

	/**
	 * Mark hooks as deprecated to ensure backward compatibility
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @return void
	 */
	private static function deprecated_hooks() {
		cocart_do_deprecated_filter(
			'cocart_log_entry_name',
			'4.4.0',
			null,
			'Plugin information is now automatically detected using get_plugin_data()',
			array( 'cocart' )
		);

		cocart_do_deprecated_filter(
			'cocart_log_entry_version',
			'4.4.0',
			null,
			'Plugin information is now automatically detected using get_plugin_data()',
			array( 'cocart' )
		);

		cocart_do_deprecated_filter(
			'cocart_log_entry_source',
			'4.4.0',
			null,
			'Plugin source is now automatically determined'
		);
	} // END deprecated_hooks()

	/**
	 * Format the log entry with timestamp and version information.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Added plugin version and name to the log entry.
	 *
	 * @param string $message The log message.
	 * @param string $plugin  The plugin being logged.
	 *
	 * @return string The formatted log entry.
	 */
	private static function format_log_entry( $message, $plugin ) {
		self::deprecated_hooks();

		$log_time = date_i18n(
			sprintf( '%s @ %s', get_option( 'date_format' ), get_option( 'time_format' ) ),
			current_time( 'timestamp' )
		);

		if ( isset( self::SOURCES[ $plugin ] ) ) {
			$plugin_info = self::SOURCES[ $plugin ];
			$version     = constant( $plugin_info['version'] );
		} else {
			$plugin_info = self::get_plugin_details( $plugin );
			$version     = $plugin_info['version'];
		}

		$version_header = sprintf( '====%s Version: %s====', $plugin_info['name'], $version );

		return "\n{$version_header}\n" .
				"====Start Log {$log_time}====\n" .
				"{$message}\n" .
				"====End Log====\n\n";
	} // END format_log_entry()

	/**
	 * Write the log entry using the appropriate log level.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @param string $log_entry The log entry to write.
	 * @param string $type      The type of log to record.
	 * @param array  $context   The context for the log entry.
	 *
	 * @return void
	 */
	private static function write_log( $log_entry, $type, $context ) {
		self::$logger->{$type}( $log_entry, $context );
	} // END write_log()
} // END class.
