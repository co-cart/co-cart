<?php
/**
 * Admin View: Trying Beta Notice.
 *
 * @since    1.2.0
 * @version  2.0.0
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
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart, a WooCommerce REST-API extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php echo esc_html__( 'Thanks for trying out this beta/pre-release!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo esc_html__( 'If you have any questions or any feedback at all, please let me know. Any little bit you\'re willing to share helps.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" aria-label="' . esc_html__( 'Give Feedback for %2$s', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%3$s</a>', esc_url( COCART_STORE_URL . 'feedback/' ), 'CoCart', esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_beta_notice', 'true' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice and ask me again for feedback in 7 days', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Ask me again in 7 days', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
