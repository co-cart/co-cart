<?php
/**
 * CoCart Uninstall
 *
 * Uninstalling CoCart deletes tables and options.
 *
 * @package CoCart\Uninstaller
 * @since   2.1.0
 * @version 2.1.1
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb, $wp_version;

// Drop Tables.
require_once( dirname( __FILE__ ) . '/includes/class-cocart-install.php' );

CoCart_Install::drop_tables();

// Delete options.
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'cocart\_%';" );

// Delete usermeta.
$wpdb->query( "DELETE FROM $wpdb->usermeta WHERE meta_key LIKE 'cocart\_%';" );

// Clear any cached data that has been removed.
wp_cache_flush();
