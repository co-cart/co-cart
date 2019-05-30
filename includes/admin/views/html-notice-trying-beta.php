<?php
/**
 * Admin View: Trying Beta Notice.
 *
 * @since    1.2.0
 * @version  1.2.2
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
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
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart WooCommerce REST-API Extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php echo esc_html__( 'Thanks for trying out this beta/pre-release!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'If you have any questions or any feedback at all, please let me know. Any little bit you\'re willing to share helps.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

		<div class="cocart-send-feedback">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-feedback-button" target="_blank">%2$s</a>', esc_url( COCART_STORE_URL . 'feedback/' ), esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_beta_notice', 'true' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'Ask me again in 7 days', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
