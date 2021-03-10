<?php
/**
 * Admin View: Notice - Updated.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Views
 * @since    3.0.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info cocart-notice">
	<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'update', remove_query_arg( 'do_update_cocart' ) ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>

	<p><?php echo sprintf( esc_html__( '%s database update complete. Thank you for updating to the latest version!', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>
</div>
