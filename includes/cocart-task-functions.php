<?php
/**
 * CoCart Tasks
 *
 * Functions for running requested tasks.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.1.2 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up carts from the database that have expired.
 *
 * @since 3.1.2 Introduced
 */
function cocart_task_cleanup_carts() {
	if ( ! class_exists( 'CoCart_Session_Handler' ) ) {
		include COCART_ABSPATH . 'includes/classes/class-cocart-session-handler.php';
	}

	$session = new CoCart_Session_Handler();

	if ( is_callable( array( $session, 'cleanup_sessions' ) ) ) {
		$session->cleanup_sessions();
	} else {
		CoCart_Logger::log( esc_html__( 'CoCart Task: Clean up carts failed.', 'cart-rest-api-for-woocommerce' ), 'error' );
	}
}
add_action( 'cocart_cleanup_carts', 'cocart_task_cleanup_carts' );

/**
 * Clears all carts from the database.
 *
 * @todo Create WP-CLI command for this function in the future.
 *
 * @since 3.1.2 Introduced.
 *
 * @param bool $return_results Return total of persistent carts cleared.
 *
 * @global $wpdb
 *
 * @return int|void The number of saved carts.
 */
function cocart_task_clear_carts( $return_results = false ) {
	global $wpdb;

	$wpdb->query( "TRUNCATE {$wpdb->prefix}cocart_carts" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

	/**
	 * Clear all persistent carts.
	 */
	$results = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->prepare(
			"DELETE FROM {$wpdb->usermeta} WHERE meta_key = %s",
			'_woocommerce_persistent_cart_' . get_current_blog_id()
		)
	);

	wp_cache_flush();

	if ( $return_results ) {
		return absint( $results );
	}
} // END cocart_task_clear_carts()
