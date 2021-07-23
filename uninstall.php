<?php
/**
 * CoCart Uninstall
 *
 * Uninstalling CoCart deletes tables and options.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Uninstaller
 * @since   2.1.0
 * @version 2.8.3
 * @license GPL-2.0+
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

wp_clear_scheduled_hook( 'cocart_cleanup_carts' );

/**
 * Only remove ALL plugin data and database table if COCART_REMOVE_ALL_DATA constant is
 * set to true in user's wp-config.php. This is to prevent data loss when deleting the
 * plugin from the backend and to ensure only the site owner can perform this action.
 */
if ( defined( 'COCART_REMOVE_ALL_DATA' ) && true === COCART_REMOVE_ALL_DATA ) {
	// Drop Tables.
	require_once dirname( __FILE__ ) . '/includes/class-cocart-install.php';
	CoCart_Install::drop_tables();

	// Delete options.
	$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cocart\_%';" );

	// Delete usermeta.
	$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'cocart\_%';" );

	// Delete sitemeta. Multi-site only!
	if ( is_multisite() ) {
		$wpdb->query( "DELETE FROM $wpdb->sitemeta WHERE meta_key LIKE 'cocart\_%';" );
	}

	require_once dirname( __FILE__ ) . '/includes/class-cocart-helpers.php';

	// Delete WooCommerce Admin Notes.
	if (
		class_exists( 'Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes' ) ||
		class_exists( 'Automattic\WooCommerce\Admin\Notes\Notes' )
	) {

		if ( CoCart_Helpers::is_wc_version_gte_4_8() ) {
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-activate-pro' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-do-with-products' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-help-improve' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-need-help' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-thanks-install' );
			Automattic\WooCommerce\Admin\Notes\Notes::delete_notes_with_name( 'cocart-wc-admin-upgrade-pro' );
		} else {
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-activate-pro' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-do-with-products' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-help-improve' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-need-help' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-thanks-install' );
			Automattic\WooCommerce\Admin\Notes\WC_Admin_Notes::delete_notes_with_name( 'cocart-wc-admin-upgrade-pro' );
		}
	}

	// Clear any cached data that has been removed.
	wp_cache_flush();
}
