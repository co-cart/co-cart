<?php
/**
 * CoCart Background
 *
 * Functions for running in the background.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.1.0
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transfer sessions from WooCommerce table to CoCart table.
 *
 * @since 3.1.0 Introduced.
 *
 * @global $wpdb WordPress Database Object.
 *
 * @return void
 */
function cocart_transfer_sessions() {
	global $wpdb;

	$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`, `cart_source`)
		SELECT t1.session_key, t1.session_value, t1.session_expiry, 'woocommerce'
		FROM {$wpdb->prefix}woocommerce_sessions t1
		WHERE NOT EXISTS(SELECT cart_key FROM {$wpdb->prefix}cocart_carts t2 WHERE t2.cart_key = t1.session_key) "
	);
} // END cocart_transfer_sessions()

/**
 * Get or set transient value requested.
 *
 * @since 5.0.0 Introduced.
 *
 * @param string $key            The key to generate the transient name.
 * @param mixed  $value          The value to set for the transient (not used in 'get' method).
 * @param string $method         The method to determine which transient to get.
 * @param int    $cache_duration The duration in seconds for the transient to persist (used in 'set' method).
 *
 * @return mixed The transient value or false if not found.
 */
function cocart_transient( $key = '', $value = '', $method = 'get', $cache_duration = HOUR_IN_SECONDS ) {
	$transient_name = '_[cocart_' . $key . ']_';

	if ( 'set' === $method ) {
		set_transient( $transient_name, $value, $cache_duration );
	} else {
		return get_transient( $transient_name );
	}
} // END cocart_transient()

/**
 * Delete transient value requested.
 *
 * @since 5.0.0 Introduced.
 *
 * @param string $key The key to generate the transient name.
 *
 * @return mixed The transient value or false if not found.
 */
function cocart_delete_transient( $key = '' ) {
	$transient_name = '_[cocart_' . $key . ']_';

	delete_transient( $transient_name );
} // END cocart_delete_transient()

/**
 * Get or set site transient value requested.
 *
 * @since 5.0.0 Introduced.
 *
 * @param string $key            The key to generate the transient name.
 * @param mixed  $value          The value to set for the transient (not used in 'get' method).
 * @param string $method         The method to determine which transient to get.
 * @param int    $cache_duration The duration in seconds for the transient to persist (used in 'set' method).
 *
 * @return mixed The transient value or false if not found.
 */
function cocart_site_transient( $key = '', $value = '', $method = 'get', $cache_duration = HOUR_IN_SECONDS ) {
	$transient_name = '_[cocart_' . $key . ']_';

	if ( 'set' === $method ) {
		set_site_transient( $transient_name, $value, $cache_duration );
	} else {
		return get_site_transient( $transient_name );
	}
} // END cocart_site_transient()

/**
 * Delete site transient value requested.
 *
 * @since 5.0.0 Introduced.
 *
 * @param string $key The key to generate the transient name.
 *
 * @return mixed The transient value or false if not found.
 */
function cocart_delete_site_transient( $key = '' ) {
	$transient_name = '_[cocart_' . $key . ']_';

	delete_site_transient( $transient_name );
} // END cocart_delete_site_transient()
