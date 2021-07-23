<?php
/**
 * Admin View: Custom Notices
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.0.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-content">
			<?php echo wp_kses_post( wpautop( $notice_html ) ); ?>
		</div>

		<div class="cocart-action">
			<a class="button button-primary cocart-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', $notice, CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
