<?php
/**
 * CoCart Tasks
 *
 * Functions for running requested tasks.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.1.2 Introduced.
 * @version 4.0.0
 */

use CoCart\Logger;
use CoCart\Session\Handler;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cleans up carts from the database that have expired.
 *
 * @see Handler::cleanup_sessions() to cleanup sessions.
 * @uses Logger::log() to log failure.
 *
 * @since 3.1.2 Introduced
 */
function cocart_task_cleanup_carts() {
	if ( ! class_exists( 'CoCart\Session\Handler' ) ) {
		include COCART_ABSPATH . 'includes/abstracts/abstract-cocart-session.php';
		include COCART_ABSPATH . 'includes/classes/class-cocart-session-handler.php';
	}

	$session = new Handler();

	if ( is_callable( array( $session, 'cleanup_sessions' ) ) ) {
		$session->cleanup_sessions();
	} else {
		Logger::log( esc_html__( 'CoCart Task: Clean up carts failed.', 'cart-rest-api-for-woocommerce' ), 'error' );
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
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @return int $results The number of saved carts.
 */
function cocart_task_clear_carts( $return_results = false ) {
	global $wpdb;

	$wpdb->query( "TRUNCATE {$wpdb->prefix}cocart_carts" );

	/**
	 * Clear all persistent carts.
	 */
	$results = absint( $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key='_woocommerce_persistent_cart_" . get_current_blog_id() . "';" ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	wp_cache_flush();

	if ( $return_results ) {
		return $results;
	}
} // END cocart_task_clear_carts()
