<?php
/**
 * Admin View: Custom Notices
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
	<a class="woocommerce-message-close notice-dismiss" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', $notice ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
	<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
</div>
