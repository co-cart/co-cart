<?php
/**
 * Admin View: Plugin Review Notice.
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

$current_user = wp_get_current_user();

$time = CoCart_Admin::cocart_seconds_to_words( time() - $install_date );
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart WooCommerce REST-API Extension', 'cart-rest-api-for-woocommerce' ); ?>" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php printf( esc_html__( 'Hi %1$s, are you enjoying %2$s?', 'cart-rest-api-for-woocommerce' ), $current_user->display_name, esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ) ); ?></h3>
			<p><?php printf( esc_html__( 'You have been using %1$s for %2$s now! Mind leaving a review and let me know know what you think of the plugin? I\'d really appreciate it!', 'cart-rest-api-for-woocommerce' ), esc_html__( 'CoCart', 'cart-rest-api-for-woocommerce' ), esc_html( $time ) ); ?></p>
		</div>

		<div class="cocart-review-now">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-review-button" target="_blank">%2$s</a>', esc_url( COCART_REVIEW_URL . '?rate=5#new-post' ), esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( add_query_arg( 'hide_cocart_review_notice', 'true' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'No thank you / I already have', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
