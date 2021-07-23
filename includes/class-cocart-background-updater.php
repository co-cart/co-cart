<?php
/**
 * Background Updater
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @version 3.0.5
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Background_Process', false ) ) {
	include_once WC_ABSPATH . '/abstracts/class-wc-background-process.php';
}

if ( ! class_exists( 'CoCart_Background_Updater' ) ) {

	/**
	 * CoCart_Background_Updater Class.
	 *
	 * @extends WC_Background_Process
	 */
	class CoCart_Background_Updater extends WC_Background_Process {

		/**
		 * Initiate new background process.
		 *
		 * @access public
		 */
		public function __construct() {
			// Uses unique prefix per blog so each blog has separate queue.
			$this->prefix = 'wp_' . get_current_blog_id();
			$this->action = 'cocart_updater';

			parent::__construct();
		}

		/**
		 * Dispatch updater.
		 *
		 * Updater will still run via cron job if this fails for any reason.
		 *
		 * @access public
		 */
		public function dispatch() {
			$dispatched = parent::dispatch();
			$logger     = new CoCart_Logger();

			if ( is_wp_error( $dispatched ) ) {
				$logger->error(
					/* translators: %s: dispatch error message */
					sprintf( __( 'Unable to dispatch CoCart updater: %s', 'cart-rest-api-for-woocommerce' ), $dispatched->get_error_message() ),
					array( 'source' => 'cocart_db_updates' )
				);
			}
		}

		/**
		 * Handle cron health check.
		 *
		 * Restart the background process if not already running
		 * and data exists in the queue.
		 *
		 * @access public
		 */
		public function handle_cron_healthcheck() {
			if ( $this->is_process_running() ) {
				// Background process already running.
				return;
			}

			if ( $this->is_queue_empty() ) {
				// No data to process.
				$this->clear_scheduled_event();
				return;
			}

			$this->handle();
		}

		/**
		 * Schedule fallback event.
		 *
		 * @access protected
		 */
		protected function schedule_event() {
			if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
				wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
			}
		}

		/**
		 * Is the updater running?
		 *
		 * @access public
		 * @return boolean
		 */
		public function is_updating() {
			return false === $this->is_queue_empty();
		}

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @access protected
		 * @param  string $callback Update callback function.
		 * @return string|false
		 */
		protected function task( $callback ) {
			wc_maybe_define_constant( 'COCART_UPDATING', true );

			$logger = new CoCart_Logger();

			include_once dirname( __FILE__ ) . '/cocart-update-functions.php';

			$result = false;

			if ( is_callable( $callback ) ) {
				/* translators: %s: callback function */
				$logger->info( sprintf( __( 'Running %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'cocart_db_updates' ) );
				$result = (bool) call_user_func( $callback, $this );

				if ( $result ) {
					/* translators: %s: callback function */
					$logger->info( sprintf( __( '%s callback needs to run again', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'cocart_db_updates' ) );
				} else {
					/* translators: %s: callback function */
					$logger->info( sprintf( __( 'Finished running %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'cocart_db_updates' ) );
				}
			} else {
				/* translators: %s: callback function */
				$logger->notice( sprintf( __( 'Could not find %s callback', 'cart-rest-api-for-woocommerce' ), $callback ), array( 'source' => 'cocart_db_updates' ) );
			}

			return $result ? $callback : false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @access protected
		 */
		protected function complete() {
			$logger = new CoCart_Logger();
			$logger->info( __( 'Data update complete', 'cart-rest-api-for-woocommerce' ), array( 'source' => 'cocart_db_updates' ) );
			CoCart_Install::update_db_version();
			parent::complete();
		}

		/**
		 * See if the batch limit has been exceeded.
		 *
		 * @access public
		 * @return bool
		 */
		public function is_memory_exceeded() {
			return $this->memory_exceeded();
		}

	} // END class.

} // END if class exists.

return new CoCart_Background_Updater();
