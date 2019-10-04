<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @since    1.2.3
 * @version  2.0.6
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
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart, a WooCommerce REST-API extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Upgrade Warning!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo sprintf( __( 'Version %1$s%4$s%2$s of %3$s is coming soon and will provide support for guest customers and a new endpoint to retrieve products without authentication. Before it is released, I require your help to test it out and provide your feedback! Documentation is also available for these features.', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart', COCART_NEXT_VERSION ); ?></p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank">%2$s</a>', esc_url( COCART_NEXT_VERSION_DETAILS ), esc_html__( 'Learn More', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_upgrade_notice', 'true' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'Ask me again in 2 weeks', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
