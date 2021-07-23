<?php
/**
 * CoCart Updates
 *
 * Functions for updating data, used by the background updater.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.0.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update CoCart session database structure.
 *
 * @global $wpdb
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
	CoCart_Install::update_db_version( '3.0.0' );
}
