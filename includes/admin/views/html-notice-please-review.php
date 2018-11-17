<?php
/**
 * Admin View: Plugin Review Notice.
 *
 * @since    2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user();

$time = cocart_seconds_to_words( time() - $install_date );
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="https://ps.w.org/cart-rest-api-for-woocommerce/assets/icon-256x256.jpg" alt="<?php echo esc_attr__( 'CoCart a WooCommerce API extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php echo esc_html__( 'Are you enjoying CoCart?', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php printf( esc_html__( 'You have been using %1$s for %2$s now! Mind leaving a quick review and let me know know what you think of the plugin? I\'d really appreciate it!', 'cocart' ), esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ), esc_html( $time ) ); ?></p>
		</div>

		<div class="cocart-review-now">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-review-button" aria-label="' . esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%2$s</a>', esc_url( 'https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews?rate=5#new-post' ), esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_review_notice', 'true' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide Review Notice', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'No thank you / I already have', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
