<?php
/**
 * Class: CoCart\Logger.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.1.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart;

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
class Logger {

	/**
	 * Log Handler Interface.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var object $logger Log Handler Interface.
	 */
	public static $logger;

	/**
	 * Log issues or errors within CoCart.
	 *
	 * Uses "wc_get_logger()"
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.1.0 Introduced.
	 * @version 4.0.0
	 *
	 * @param string $message The message of the log.
	 * @param string $type    The type of log to record.
	 * @param string $plugin  The CoCart plugin being logged.
	 */
	public static function log( $message, $type, $plugin = 'cocart' ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'cocart_logging', true, $type, $plugin ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			if ( empty( self::$logger ) ) {
				self::$logger = wc_get_logger();
			}

			if ( 'cocart' === $plugin ) {
				$log_entry = "\n" . '====CoCart Version: ' . COCART_VERSION . '====' . "\n";
				$context   = array( 'source' => 'cocart' );
			} elseif ( 'cocart-pro' === $plugin ) {
				$log_entry = "\n" . '====CoCart Pro Version: ' . COCART_PRO_VERSION . '====' . "\n";
				$context   = array( 'source' => 'cocart-pro' );
			} else {
				/* translators: %1$s: Log entry name, %2$s: log entry version */
				$log_entry = "\n" . sprintf( esc_html__( '====%1$s Version: %2$s====', 'cart-rest-api-for-woocommerce' ), apply_filters( 'cocart_log_entry_name', '', $plugin ), apply_filters( 'cocart_log_entry_version', '', $plugin ) ) . "\n";
				$context   = array( 'source' => apply_filters( 'cocart_log_entry_source', '' ) );
			}

			$log_time = date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ), current_time( 'timestamp' ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested

			$log_entry .= '====Start Log ' . $log_time . '====' . "\n" . $message . "\n";
			$log_entry .= '====End Log====' . "\n\n";

			switch ( $type ) {
				// Interesting events.
				case 'info':
					self::$logger->info( $log_entry, $context );
					break;
				// Normal but significant events.
				case 'notice':
					self::$logger->notice( $log_entry, $context );
					break;
				// Exceptional occurrences that are not errors.
				case 'warning':
					self::$logger->warning( $log_entry, $context );
					break;
				// Runtime errors that do not require immediate.
				case 'error':
					self::$logger->error( $log_entry, $context );
					break;
				// Critical conditions.
				case 'critical':
					self::$logger->critical( $log_entry, $context );
					break;
				// Action must be taken immediately.
				case 'alert':
					self::$logger->alert( $log_entry, $context );
					break;
				// System is unusable.
				case 'emergency':
					self::$logger->emergency( $log_entry, $context );
					break;
				// Detailed debug information.
				case 'debug':
				default:
					self::$logger->debug( $log_entry, $context );
					break;
			}
		}
	} // END log()

} // END class
