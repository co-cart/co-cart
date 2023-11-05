<?php
/**
 * Admin View: Required WooCommerce Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error cocart-notice is-dismissible">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<p>
				<?php
				echo sprintf(
					/* translators: 1: CoCart, 2: InstaWP */
					esc_html__( 'WordPress Playground is not compatible with the %1$s plugin. Recommend creating a sandbox site with %2$s instead.', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'InstaWP'
				);
				?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( 'https://app.instawp.io/onboard?launch_slug=true&plugins=cart-rest-api-for-woocommerce' ); ?>" class="button button-primary cocart-button" aria-label="<?php echo esc_html__( 'Setup Sandbox', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Setup Sandbox', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
