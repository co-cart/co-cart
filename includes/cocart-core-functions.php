<?php
/**
 * CoCart Core Functions.
 *
 * Functions for the core plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.2.0 Introduced.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Define a constant if it is not already defined.
 *
 * @since 4.2.0 Introduced.
 *
 * @param string $name  Constant name.
 * @param mixed  $value Value.
 */
function cocart_maybe_define_constant( $name, $value ) {
	if ( ! defined( $name ) ) {
		define( $name, $value );
	}
} // END cocart_maybe_define_constant()

/**
 * Returns the timestamp the cart was created or expired.
 *
 * @since 4.2.0 Introduced.
 *
 * @param string $cart_key The cart key.
 * @param string $type     The type of timestamp.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return string The timestamp the cart was created or expired.
 */
function cocart_get_timestamp( $cart_key, $timestamp_type = 'created' ) {
	global $wpdb;

	if ( 'created' === $timestamp_type ) {
		$value = 'cart_created';
	} elseif ( 'expired' === $timestamp_type ) {
		$value = 'cart_expiry';
	}

	$result = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare( "SELECT $value FROM {$wpdb->prefix}cocart_carts WHERE cart_key = %s", $cart_key ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	);

	return $result;
} // END cocart_get_timestamp()

/**
 * Returns the source of the cart.
 *
 * @since 4.2.0 Introduced.
 *
 * @param string $cart_key The cart key.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return string
 */
function cocart_get_source( $cart_key ) {
	global $wpdb;

	$value = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare( "SELECT cart_source FROM {$wpdb->prefix}cocart_carts WHERE cart_key = %s", $cart_key ) // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	);

	return $value;
} // END cocart_get_source()

/**
 * Checks if the session table exists before returning results.
 * Helps prevents any fatal errors or crashes should debug mode be enabled.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return boolean Returns true or false if the session table exists.
 */
function cocart_maybe_show_results() {
	global $wpdb;

	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}cocart_carts';" ) ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return true;
	}

	return false;
} // END cocart_maybe_show_results()

/**
 * Counts how many carts are currently in session.
 *
 * @since 4.2.0 Introduced.
 *
 * @param string $session Session table to count.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts in session.
 */
function cocart_carts_in_session( $session = '' ) {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return __( 'Missing session table.', 'cart-rest-api-for-woocommerce' );
	}

	if ( empty( $session ) ) {
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"
			SELECT COUNT(cart_id) as count 
			FROM {$wpdb->prefix}cocart_carts",
			ARRAY_A
		);
	} else {
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"
			SELECT COUNT(session_id) as count 
			FROM {$wpdb->prefix}woocommerce_sessions",
			ARRAY_A
		);
	}

	return $results[0]['count'];
} // END cocart_carts_in_session()

/**
 * Counts how many carts are going to expire within the next 6 hours.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts expiring.
 */
function cocart_count_carts_expiring() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return 0;
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_expiry BETWEEN %d AND %d",
			time(),
			( HOUR_IN_SECONDS * 6 ) + time()
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_count_carts_expiring()

/**
 * Counts how many carts are active.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts active.
 */
function cocart_count_carts_active() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return 0;
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_expiry > %d",
			time()
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_count_carts_active()

/**
 * Counts how many carts have expired.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts expired.
 */
function cocart_count_carts_expired() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return 0;
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_expiry < %d",
			time()
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_count_carts_expired()

/**
 * Counts how many carts were created via the web.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts created via the web.
 */
function cocart_carts_source_web() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return __( 'Missing session table.', 'cart-rest-api-for-woocommerce' );
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_source=%s",
			'woocommerce'
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_carts_source_web()

/**
 * Counts how many carts were created via CoCart API.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts created via CoCart API.
 */
function cocart_carts_source_headless() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return __( 'Missing session table.', 'cart-rest-api-for-woocommerce' );
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_source=%s",
			'cocart'
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_carts_source_headless()

/**
 * Counts how many carts were the source is other or unknown.
 *
 * @since 4.2.0 Introduced.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int Number of carts created via other or unknown.
 */
function cocart_carts_source_other() {
	global $wpdb;

	if ( ! cocart_maybe_show_results() ) {
		return __( 'Missing session table.', 'cart-rest-api-for-woocommerce' );
	}

	$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"
			SELECT COUNT(cart_id) as count
			FROM {$wpdb->prefix}cocart_carts 
			WHERE cart_source!=%s AND cart_source!=%s",
			'cocart',
			'woocommerce'
		),
		ARRAY_A
	);

	return $results[0]['count'];
} // END cocart_carts_source_other()

/**
 * Wrapper for nocache_headers which also disables page caching.
 *
 * @since 4.2.0 Introduced.
 */
function cocart_nocache_headers() {
	cocart_maybe_define_constant( 'DONOTCACHEPAGE', true );
	cocart_maybe_define_constant( 'DONOTCACHEOBJECT', true );
	cocart_maybe_define_constant( 'DONOTCACHEDB', true );
	nocache_headers();
} // END cocart_nocache_headers()
