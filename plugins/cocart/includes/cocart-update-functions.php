<?php
/**
 * CoCart Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

use CoCart\Install;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update CoCart session database structure.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_update_400_db_structure() {
	global $wpdb;

	$source_exists = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->prefix}cocart_carts WHERE key_name = 'cart_user_id'" );

	if ( is_null( $source_exists ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_user_id` BIGINT UNSIGNED NOT NULL AFTER `cart_key`, ADD `cart_customer` BIGINT UNSIGNED NOT NULL AFTER `cart_user_id`" );
	}
}

/**
 * Update CoCart sessions for registered customers.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_update_400_db_sessions() {
	global $wpdb;

	$sessions = $wpdb->get_results(
		"SELECT cart_key FROM {$wpdb->prefix}cocart_carts"
	);

	foreach ( $sessions as $session ) {
		$cart_key = $session->cart_key;

		// If the cart key is a registered customer, set the `cart_user_id` and `cart_customer` with the `cart_key`.
		if ( get_userdata( $cart_key ) ) {
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE {$wpdb->prefix}cocart_carts
					SET cart_user_id = %d, cart_customer = %d
					WHERE cart_key = %s",
					$cart_key,
					$cart_key,
					$cart_key
				)
			);
		}
	}
}

/**
 * Update the status of the session upgrade.
 *
 * Sets a database version to compare a match.
 */
function cocart_update_400_session_upgraded() {
	update_option( 'cocart_session_upgraded', '4.0.0' );
} // END cocart_update_400_session_upgraded()

/**
 * Update CoCart session database structure.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_update_300_db_structure() {
	global $wpdb;

	$source_exists = $wpdb->get_row( "SHOW INDEX FROM {$wpdb->prefix}cocart_carts WHERE key_name = 'cart_created'" );

	if ( is_null( $source_exists ) ) {
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_created` BIGINT UNSIGNED NOT NULL AFTER `cart_value`" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_source` VARCHAR(200) NOT NULL AFTER `cart_expiry`" );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}cocart_carts ADD `cart_hash` VARCHAR(200) NOT NULL AFTER `cart_source`" );
	}
}

/**
 * Update database version to 3.0.0
 */
function cocart_update_300_db_version() {
	Install::update_db_version( '3.0.0' );
}
