<?php
/**
 * Admin View: Notice - Setup Wizard
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.1.0
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
				<strong>
				<?php
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( 'Welcome to %s!', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
				</strong>
			</h3>
			<p>
				<?php
					echo sprintf(
						/* translators: %s: CoCart */
						esc_html__( 'To help prepare %s running smoothly, we would like to guide you with a setup wizard.', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					);
					?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cocart-setup' ) ); ?>" class="button button-primary cocart-button">
				<?php echo esc_html__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'setup_wizard', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'I will manually setup later.', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
