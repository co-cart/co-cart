<?php
/**
 * CoCart Background
 *
 * Functions for running in the background.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.1.0 Introduced.
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
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return void
 */
function cocart_transfer_sessions() {
	global $wpdb;

	$wpdb->query(
		"INSERT INTO {$wpdb->prefix}cocart_carts (`cart_key`, `cart_value`, `cart_expiry`)
		SELECT t1.session_key, t1.session_value, t1.session_expiry
		FROM {$wpdb->prefix}woocommerce_sessions t1
		WHERE NOT EXISTS(SELECT cart_key FROM {$wpdb->prefix}cocart_carts t2 WHERE t2.cart_key = t1.session_key) "
	);
} // END cocart_transfer_sessions()
