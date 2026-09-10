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
<div class="notice notice-info cocart-notice" role="alert">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/icon-logo.png' ); ?>" alt="CoCart Logo" /><?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
		</div>

		<div class="cocart-notice-content">
			<h3>
			<?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( 'Thank you for trying out v%s', 'cart-rest-api-for-woocommerce' ),
				esc_attr( strstr( COCART_VERSION, '-', true ) )
			);

			if ( CoCart_Helpers::is_cocart_beta() ) {
				printf(
					/* translators: %s: CoCart */
					esc_html__( ', a beta release of %s!', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
			}

			if ( CoCart_Helpers::is_cocart_rc() ) {
				printf(
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
				printf(
					/* translators: 1: Feedback URL, 2: CoCart, 3: Button Text */
					'<a href="%1$s" class="button button-primary cocart-button" target="_blank" rel="noopener noreferrer" role="button">%3$s</a>',
					esc_url( 'https://github.com/cocart-headless/cocart-community/issues/new?assignees=&labels=needs%3A+developer+feedback&&title=[v' . COCART_VERSION . ']:%20Title%20of%20the%20issue' ),
					'CoCart',
					esc_html__( 'Give Feedback', 'cart-rest-api-for-woocommerce' )
				);
				?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_beta', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
