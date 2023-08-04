<?php
/**
 * CoCart Uninstall
 *
 * Uninstalling CoCart deletes tables and options.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Uninstaller
 * @since   2.1.0
 * @version 4.0.0
 * @license GPL-2.0+
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

// Clear clean up carts schedule.
wp_clear_scheduled_hook( 'cocart_cleanup_carts' );

// Reschedule WooCommerce cleanup sessions event.
wp_schedule_event( time() + ( 6 * HOUR_IN_SECONDS ), 'twicedaily', 'woocommerce_cleanup_sessions' );

/**
 * Only remove ALL plugin data and database table if COCART_REMOVE_ALL_DATA constant is
 * set to true in user's wp-config.php. This is to prevent data loss when deleting the
 * plugin from the backend and to ensure only the site owner can perform this action.
 */
if ( defined( 'COCART_REMOVE_ALL_DATA' ) && true === COCART_REMOVE_ALL_DATA ) {
	// Drop Tables.
	require_once dirname( __FILE__ ) . '/includes/classes/class-cocart-install.php';
	CoCart\Install::drop_tables();

	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cocart\_%';" );

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'cocart\_%';" );

	// Delete sitemeta. Multi-site only!
	if ( is_multisite() ) {
		$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key LIKE 'cocart\_%';" );
	}

	require_once dirname( __FILE__ ) . '/includes/classes/class-cocart-helpers.php';

	// Delete WooCommerce Admin Notes.
	if ( CoCart\Help::is_wc_admin_enabled() ) {
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-activate-pro' );
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-do-with-products' );
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-help-improve' );
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-need-help' );
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-thanks-install' );
		\Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-upgrade-pro' );
	}

	// Clear any cached data that has been removed.
	wp_cache_flush();
}
