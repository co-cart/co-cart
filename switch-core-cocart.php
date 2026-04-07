<?php
/**
 * Disables the community version of CoCart if found. This is to prevent conflicts between the two plugins since they share the same core files.
 *
 * @package CoCart
 *
 * @since 5.0.0 Introduced.
 */

/**
 * Disable the community version of CoCart if found.
 *
 * @since 5.0.0 Introduced.
 */
function disable_cocart_community_version() {
	$plugin_to_deactivate = 'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php';

	if ( is_multisite() && is_network_admin() ) {
		$active_plugins = (array) get_site_option( 'active_sitewide_plugins', array() );
		$active_plugins = array_keys( $active_plugins );
	} else {
		$active_plugins = (array) get_option( 'active_plugins', array() );
	}

	foreach ( $active_plugins as $plugin_basename ) {
		if ( $plugin_to_deactivate === $plugin_basename ) {
			set_transient( 'cocart_legacy_deactivated', '1', 1 * HOUR_IN_SECONDS );
			deactivate_plugins( $plugin_basename );
			return;
		}
	}
} // END disable_cocart_community_version()
