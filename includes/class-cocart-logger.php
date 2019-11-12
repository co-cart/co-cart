<?php
/**
 * CoCart REST API logger
 *
 * Handles logging errors.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Logger
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API logger class.
 *
 * @package CoCart REST API/Logger
 */
class CoCart_Logger {

	public static $logger;

	/**
	 * Log issues or errors within CoCart.
	 *
	 * @access public
	 * @static
	 * @param string $message - The message of the log.
	 * @param string $type    - The type of log to record.
	 * @param string $plugin  - The CoCart plugin being logged.
	 */
	public static function log( $message, $type, $plugin = 'cocart-lite' ) {
		if ( apply_filters( 'cocart_logging', true ) ) {

			if ( empty( self::$logger ) ) {
				self::$logger = wc_get_logger();
			}

			$context = array( 'source' => apply_filters( 'cocart_log_source', 'cocart-lite' ) );

			if ( $plugin == 'cocart-lite' ) {
				$log_entry = "\n" . '====CoCart Lite Version: ' . COCART_VERSION . '====' . "\n";
			} else if ( $plugin == 'cocart-pro' ) {
				$log_entry = "\n" . '====CoCart Pro Version: ' . COCART_PRO_VERSION . '====' . "\n";
			}

			$log_time = date_i18n( get_option( 'date_format' ) . ' g:ia', current_time( 'timestamp' ) );

			$log_entry .= '====Start Log ' . $log_time . '====' . "\n" . $message . "\n";
			$log_entry .= '====End Log====' . "\n\n";

			switch( $type ) {
				// Interesting events.
				case 'info':
					self::$logger->info( $message, $context );
					break;
				// Normal but significant events.
				case 'notice':
					self::$logger->notice( $message, $context );
					break;
				// Exceptional occurrences that are not errors.
				case 'warning':
					self::$logger->warning( $message, $context );
					break;
				// Runtime errors that do not require immediate.
				case 'error':
					self::$logger->error( $message, $context );
					break;
				// Critical conditions.
				case 'critical':
					self::$logger->critical( $message, $context );
					break;
				// Action must be taken immediately.
				case 'alert':
					self::$logger->alert( $message, $context );
					break;
				// System is unusable.
				case 'emergency':
					self::$logger->emergency( $message, $context );
					break;
				// Detailed debug information.
				case 'debug':
				default:
					self::$logger->debug( $message, $context );
					break;
			}
		}
	} // END log()

} // END class
