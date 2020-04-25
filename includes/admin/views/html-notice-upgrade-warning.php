<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @since    1.2.3
 * @version  2.1.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart
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
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Upgrade Warning!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo sprintf( __( 'Version %1$s%4$s%2$s of %3$s is coming soon and will provide support for guest customers among new filters for developers. I am in need of testers and your feedback.', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart', COCART_NEXT_VERSION ); ?></p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank">%2$s</a>', esc_url( 'https://cocart.xyz/contact/' ), esc_html__( 'Sign up to Test', 'cart-rest-api-for-woocommerce' ) ); ?>
			<span class="no-thanks"><a href="<?php echo esc_url( add_query_arg( 'hide_cocart_upgrade_notice', 'true' ) ); ?>" aria-label="<?php echo esc_html__( 'Hide this notice and ask me again in 2 weeks', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Ask me again in 2 weeks', 'cart-rest-api-for-woocommerce' ); ?></a> / <a href="<?php echo esc_url( add_query_arg( 'hide_forever_cocart_upgrade_notice', 'true' ) ); ?>" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a></span>
		</div>
	</div>
</div>
