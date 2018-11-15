<?php
/**
 * Admin View: WooCommerce not installed or activated notice.
 *
 * @since    2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

echo '<div class="notice notice-error">';

echo '<p>' . sprintf( __( '%1$s requires %2$sWooCommerce%3$s to be installed and activated.', 'cart-rest-api-for-woocommerce' ), esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>' ) . '</p>';

echo '<p>';

if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) :

	if ( current_user_can( 'activate_plugin', 'woocommerce/woocommerce.php' ) ) :

		echo '<a href="' . esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=active' ), 'activate-plugin_woocommerce/woocommerce.php' ) ) . '" class="button button-primary">' . esc_html__( 'Activate WooCommerce', 'cart-rest-api-for-woocommerce' ) . '</a>';

	else :

		echo esc_html__( 'As you do not have permission to activate a plugin. Please ask a site administrator to activate WooCommerce for you.', 'cart-rest-api-for-woocommerce' );

	endif;

else:

	if ( current_user_can( 'install_plugins' ) ) {
		$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
	} else {
		$url = 'https://wordpress.org/plugins/woocommerce/';
	}

	echo '<a href="' . esc_url( $url ) . '" class="button button-primary">' . esc_html__( 'Install WooCommerce', 'cart-rest-api-for-woocommerce' ) . '</a>';

endif;

if ( current_user_can( 'deactivate_plugin', 'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php' ) ) :

	echo ' <a href="' . esc_url( wp_nonce_url( 'plugins.php?action=deactivate&plugin=cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php&plugin_status=inactive', 'deactivate-plugin_cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php' ) ) . '" class="button button-secondary">' . esc_html__( 'Turn off CoCart plugin', 'cart-rest-api-for-woocommerce' ) . '</a>';

endif;

echo '</p>';

echo '</div>';
