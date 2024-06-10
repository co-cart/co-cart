<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin\Views
 * @since    1.2.3
 * @version  4.0.0
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
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3>
			<?php
			printf(
				/* translators: %s: CoCart */
				esc_html__( 'What is next for %s?', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			);
			?>
			</h3>

			<p>
			<?php
			printf(
				/* translators: %1$s: CoCart, %2$s: CoCart's Next Version */
				esc_html__( 'Version %2$s of %1$s is in development with many improvements and options including improved security. A newly added settings page with multiple options to quickly configure %1$s as you need and many more action hooks and filters for developers.', 'cart-rest-api-for-woocommerce' ),
				'CoCart',
				esc_attr( COCART_NEXT_VERSION )
			);
			?>
			</p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank">%2$s</a>', esc_url( 'https://github.com/co-cart/co-cart/blob/dev/NEXT_CHANGELOG.md' ), esc_html__( "What's Coming Next?", 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'upgrade_warning', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Remind me another time', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
