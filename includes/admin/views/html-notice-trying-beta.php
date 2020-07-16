<?php
/**
 * Admin View: Trying Beta Notice.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @since    1.2.0
 * @version  2.3.0
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
			<h3>
			<?php
			if ( CoCart_Helpers::is_cocart_beta( COCART_VERSION ) ) {
				echo sprintf( esc_html__( 'Thank you for trying out beta release v%s of CoCart!', 'cart-rest-api-for-woocommerce' ), str_replace( '-beta', '', COCART_VERSION ) );
			}

			if ( CoCart_Helpers::is_cocart_rc( COCART_VERSION ) ) {
				echo sprintf( esc_html__( 'Thank you for trying out release candidate v%s of CoCart!', 'cart-rest-api-for-woocommerce' ), str_replace( '-rc', '', COCART_VERSION ) );
			}
			?>
			</h3>
			<p><?php echo esc_html__( 'If you have any questions or any feedback at all, please let me know. Any little bit you\'re willing to share helps the development of CoCart.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" aria-label="' . esc_html__( 'Give Feedback for %2$s', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%3$s</a>', esc_url( COCART_STORE_URL . 'feedback/' ), 'CoCart', esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' ) ); ?>
			<span class="no-thanks"><a href="<?php echo esc_url( add_query_arg( 'hide_cocart_beta_notice', 'true' ) ); ?>" aria-label="<?php echo esc_html__( 'Hide this notice and ask me again for feedback in 7 days', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Ask me again in 7 days', 'cart-rest-api-for-woocommerce' ); ?></a> / <a href="<?php echo esc_url( add_query_arg( 'hide_forever_cocart_beta_notice', 'true' ) ); ?>" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a></span>
		</div>
	</div>
</div>
