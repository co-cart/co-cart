<?php
/**
 * Class: CoCart_Logger
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.9.0
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
	 * Default plugin sources.
	 *
	 * @var array
	 */
	private const DEFAULT_SOURCES = array(
		'cart-rest-api-for-woocommerce' => array(
			'name'    => 'CoCart Community',
			'version' => 'COCART_VERSION',
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
	 * @since 4.0.0 Added plugin version and name to the log entry.
	 * @since 4.4.0 Deprecated log entry filters in favor of automatic plugin information detection and source determination.
	 * @since 4.9.0 Added optional caller source info to log entries for improved debugging context.
	 *
	 * @uses wc_get_logger()
	 *
	 * @param string $message The message of the log.
	 * @param string $type    The type of log to record.
	 * @param string $plugin  The CoCart plugin being logged.
	 *
	 * @return void
	 */
	public static function log( $message, $type = 'debug', $plugin = 'cart-rest-api-for-woocommerce' ) {
		if ( ! class_exists( 'WC_Logger' ) || ! self::should_log( $type ) ) {
			return;
		}

		self::initialize_logger();

		/**
		 * Filter whether to include caller source info in log entries.
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param bool $include Whether to include caller info. Default true.
		 */
		$caller_info = apply_filters( 'cocart_log_include_caller_info', true ) ? self::get_caller_info() : '';

		$context   = array( 'source' => self::get_source( $plugin ) );
		$log_entry = self::format_log_entry( $message, $plugin, $caller_info );

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
	 * Get all registered plugin sources.
	 *
	 * Merges default sources with any registered via the
	 * 'cocart_log_sources' filter.
	 *
	 * External plugins can register themselves:
	 *
	 *     add_filter( 'cocart_log_sources', function( $sources ) {
	 *         $sources['my-addon'] = array(
	 *             'name'    => 'My Addon',
	 *             'version' => 'MY_ADDON_VERSION',
	 *         );
	 *         return $sources;
	 *     } );
	 *
	 * The 'version' value can be either a constant name (string) or a
	 * direct version number. If it matches a defined constant, the
	 * constant's value will be used.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return array Registered plugin sources.
	 */
	private static function get_sources() {
		/**
		 * Filter to register additional plugin sources for logging.
		 *
		 * Sources returned by this filter are merged with the defaults.
		 * Default sources cannot be overridden.
		 *
		 * @since 4.9.0 Introduced.
		 *
		 * @param array $sources Additional plugin sources to register.
		 */
		$additional = apply_filters( 'cocart_log_sources', array() );

		return array_merge( $additional, self::DEFAULT_SOURCES );
	} // END get_sources()

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
		$sources = self::get_sources();

		return isset( $sources[ $plugin ] ) ? $plugin : basename( $plugin, '.php' );
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
	 * Get caller information from the debug backtrace.
	 *
	 * Walks the stack to skip internal classes (CoCart_Logger, CoCart_Data_Exception)
	 * and returns the real caller's class, method, file, and line number.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 4.9.0 Introduced.
	 *
	 * @return string Caller info string, e.g. "ClassName::method() in relative/path/file.php:123".
	 */
	private static function get_caller_info() {
		$trace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 5 ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace

		// Classes whose frames should be skipped to find the real caller.
		$skip_classes = array( 'CoCart_Logger', 'CoCart_Data_Exception' );

		$caller_frame = null;
		$call_site    = null;

		$trace_count = count( $trace );
		for ( $i = 1; $i < $trace_count; $i++ ) {
			$class = isset( $trace[ $i ]['class'] ) ? $trace[ $i ]['class'] : '';

			if ( ! in_array( $class, $skip_classes, true ) ) {
				$caller_frame = $trace[ $i ];
				$call_site    = isset( $trace[ $i - 1 ] ) ? $trace[ $i - 1 ] : $trace[ $i ];
				break;
			}
		}

		if ( ! $caller_frame ) {
			return 'unknown';
		}

		$parts = array();

		// Build class::method() or function() identifier.
		if ( ! empty( $caller_frame['class'] ) ) {
			$parts[] = $caller_frame['class'] . $caller_frame['type'] . $caller_frame['function'] . '()';
		} elseif ( ! empty( $caller_frame['function'] ) ) {
			$parts[] = $caller_frame['function'] . '()';
		}

		// Add file and line from the call site frame.
		if ( ! empty( $call_site['file'] ) ) {
			$file = $call_site['file'];

			// Normalize separators for cross-platform comparison.
			$file       = wp_normalize_path( $file );
			$plugin_dir = defined( 'WP_PLUGIN_DIR' ) ? wp_normalize_path( WP_PLUGIN_DIR ) : '';

			// Make the path relative to the plugins directory for readability.
			if ( $plugin_dir && strpos( $file, $plugin_dir ) === 0 ) {
				$file = ltrim( substr( $file, strlen( $plugin_dir ) ), '/' );
			}

			$line    = isset( $call_site['line'] ) ? $call_site['line'] : '?';
			$parts[] = $file . ':' . $line;
		}

		return implode( ' in ', $parts );
	} // END get_caller_info()

	/**
	 * Format the log entry with timestamp and version information.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 * @since 4.0.0 Added plugin version and name to the log entry.
	 * @since 4.9.0 Added optional caller source info to log entries for improved debugging context.
	 *
	 * @param string $message     The log message.
	 * @param string $plugin      The plugin being logged.
	 * @param string $caller_info Optional. Caller source info string.
	 *
	 * @return string The formatted log entry.
	 */
	private static function format_log_entry( $message, $plugin, $caller_info = '' ) {
		$log_time = date_i18n(
			sprintf( '%s @ %s', get_option( 'date_format' ), get_option( 'time_format' ) ),
			time()
		);

		$sources = self::get_sources();

		if ( isset( $sources[ $plugin ] ) ) {
			$plugin_info = $sources[ $plugin ];
			$version     = defined( $plugin_info['version'] ) ? constant( $plugin_info['version'] ) : $plugin_info['version'];
		} else {
			$plugin_info = self::get_plugin_details( $plugin );
			$version     = $plugin_info['version'];
		}

		$version_header = sprintf( '====%s Version: %s====', $plugin_info['name'], $version );

		return "\n{$version_header}\n" .
				"====Start Log {$log_time}====\n" .
				( $caller_info ? "Source: {$caller_info}\n" : '' ) .
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

	/**
	 * Mark hooks as deprecated to ensure backward compatibility
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 4.4.0 Introduced.
	 *
	 * @deprecated 4.9.0 Deprecated in favor of automatic plugin information detection and source determination.
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
} // END class.
