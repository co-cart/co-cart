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
	 * @access public
	 *
	 * @static
	 */
	public static function load() {
		add_action( 'admin_init', array( __CLASS__, 'init' ) );
	} // END load()

	/**
	 * Schedule events and hook appropriate actions.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function init() {
		if ( ! defined( 'REST_REQUEST' ) ) {
			add_action( 'cocart_update_plugin_suggestions', array( __CLASS__, 'update_plugin_suggestions' ) );
		}
	} // END init()

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
		$data = get_option(
			'cocart_plugin_suggestions',
			array(
				'suggestions' => array(),
				'updated'     => time(),
			)
		);

		$data['updated'] = time();

		$url     = 'https://suggestions.cocartapi.com/plugin/1.0/suggestions.json';
		$request = wp_safe_remote_get( $url );

		if ( is_wp_error( $request ) ) {
			self::retry();
			return update_option( 'cocart_plugin_suggestions', $data, false );
		}

		$body = wp_remote_retrieve_body( $request );
		if ( empty( $body ) ) {
			self::retry();
			return update_option( 'cocart_plugin_suggestions', $data, false );
		}

		$body = json_decode( $body, true );
		if ( empty( $body ) || ! is_array( $body ) ) {
			self::retry();
			return update_option( 'cocart_plugin_suggestions', $data, false );
		}

		$data['suggestions'] = $body;

		update_option( 'cocart_plugin_suggestions', $data, false );

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

$suggestions = new CoCart_Admin_Plugin_Suggestions_Updater(); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$suggestions::load();
