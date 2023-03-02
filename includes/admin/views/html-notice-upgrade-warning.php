<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Views
 * @since    1.2.3
 * @version  3.0.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo COCART_URL_PATH . '/assets/images/brand/logo.jpg'; ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Bigger, better and more awesome', 'cart-rest-api-for-woocommerce' ); ?></h3>

			<p><?php echo sprintf( __( 'Just thought you might like to know that a new version of %3$s %1$s%4$s%2$s is coming soon with many improvements, options, a new settings page and more action and filter hooks for developers.', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart', COCART_NEXT_VERSION ); ?></p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank">%2$s</a>', esc_url( 'https://github.com/co-cart/co-cart/blob/dev/NEXT_CHANGELOG.md' ), esc_html__( "What's Coming Next?", 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'upgrade_warning', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t show me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>