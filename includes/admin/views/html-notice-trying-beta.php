<?php
/**
 * Admin View: Trying Beta Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   1.2.0
 * @version 3.0.7
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3>
			<?php
			echo sprintf(
				/* translators: %s: CoCart */
				esc_html__( 'Thank you for trying out v%s', 'cart-rest-api-for-woocommerce' ),
				esc_attr( strstr( COCART_VERSION, '-', true ) )
			);

			if ( CoCart_Helpers::is_cocart_beta() ) {
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( ', a beta release of %s!', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
			}

			if ( CoCart_Helpers::is_cocart_rc() ) {
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( ', a release candidate of %s!', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
			}
			?>
			</h3>
			<p><?php echo esc_html__( 'If you have any questions or any feedback at all, please let me know. Any little bit you\'re willing to share helps the development of CoCart.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

		<div class="cocart-action">
			<?php
				/* translators: 1: Feedback URL, 2: CoCart, 3: Button Text */
				printf( '<a href="%1$s" class="button button-primary cocart-button" aria-label="' . esc_html__( 'Give Feedback for %2$s', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%3$s</a>',
					esc_url( COCART_STORE_URL . 'feedback/?wpf674_3=CoCart v' . COCART_VERSION ),
					'CoCart',
					esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' )
				);
				?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_beta', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
