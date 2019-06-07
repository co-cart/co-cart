<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @since    1.2.3
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
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart a WooCommerce API extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Upgrade Warning!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo sprintf( __( 'Version %1$s2.0.0%2$s of %3$s is coming soon and will provide a new and improved API. Before it is released, I require your help to test it out and provide your feedback!', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart' ); ?></p>
		</div>

		<div class="cocart-send-feedback">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-feedback-button" target="_blank">%2$s</a>', esc_url( 'https://cocart.xyz/cocart-v2-preview/' ), esc_html__( 'Learn More', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_upgrade_notice', 'true' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'Ask me again in 2 weeks', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
