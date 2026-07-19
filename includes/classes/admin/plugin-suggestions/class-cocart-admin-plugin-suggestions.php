<?php
/**
 * Plugin suggestions updater.
 *
 * Uses WC_Queue to ensure plugin suggestions data is up to date and cached locally.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   3.5.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Suggestions Updater
 */
class CoCart_Admin_Plugin_Suggestions_Updater {

	/**
	 * Setup.
	 *
	 * The callback is registered unconditionally so scheduled actions can
	 * run in any context Action Scheduler processes the queue from,
	 * including WP-Cron and WP-CLI where admin_init never fires.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.9.1 Callback is no longer deferred to admin_init.
	 */
	public static function load() {
		add_action( 'cocart_update_plugin_suggestions', array( __CLASS__, 'run_update_plugin_suggestions' ) );
	} // END load()

	/**
	 * Runs the scheduled plugin suggestions update.
	 *
	 * Action callback wrapper as update_plugin_suggestions()
	 * returns data for direct callers.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.9.1 Introduced.
	 *
	 * @return void
	 */
	public static function run_update_plugin_suggestions() {
		self::update_plugin_suggestions();
	} // END run_update_plugin_suggestions()

	/**
	 * Fetches new plugin data, updates CoCart plugin suggestions.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array Returns plugin suggestions and timestamp.
	 */
	public static function update_plugin_suggestions() {
		$url     = 'https://suggestions.cocartapi.com/plugin/1.0/suggestions.json';
		$request = wp_safe_remote_get( $url );

		if ( is_wp_error( $request ) ) {
			self::retry();
			set_transient( 'cocart_plugin_suggestions', array(), DAY_IN_SECONDS );
			return array();
		}

		$body = wp_remote_retrieve_body( $request );
		if ( empty( $body ) ) {
			self::retry();
			set_transient( 'cocart_plugin_suggestions', array(), DAY_IN_SECONDS );
			return array();
		}

		$body = json_decode( $body, true );
		if ( empty( $body ) || ! is_array( $body ) ) {
			self::retry();
			set_transient( 'cocart_plugin_suggestions', array(), DAY_IN_SECONDS );
			return array();
		}

		$data = array(
			'suggestions' => $body,
			'updated'     => time(),
		);

		set_transient( 'cocart_plugin_suggestions', $data, WEEK_IN_SECONDS );

		return $data;
	} // END update_plugin_suggestions()

	/**
	 * Used when an error has occurred when fetching suggestions.
	 * Re-schedules the job earlier than the main weekly one.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function retry() {
		if ( ! method_exists( '\ActionScheduler', 'is_initialized' ) ) {
			return;
		}

		WC()->queue()->cancel_all( 'cocart_update_plugin_suggestions' );
		WC()->queue()->schedule_single( time() + DAY_IN_SECONDS, 'cocart_update_plugin_suggestions' );
	} // END retry()
} // END class

CoCart_Admin_Plugin_Suggestions_Updater::load();
