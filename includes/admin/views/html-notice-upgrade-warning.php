<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin\Views
 * @since    1.2.3
 * @version  3.0.7
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
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Thank you for getting me this far!', 'cart-rest-api-for-woocommerce' ); ?></h3>

			<?php
			$campaign_args = array(
				'utm_medium'   => 'cocart-lite',
				'utm_source'   => 'plugins-page',
				'utm_campaign' => 'plugins-row',
				'utm_content'  => 'go-pro',
			);
			$store_url     = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL . 'pro/' ) );
			?>

			<p>
				<?php
				echo sprintf(
					/* translators: Nothing to translate. */
					__( 'Version %1$s%5$s%2$s of %3$s will be coming in the future and will provide a %1$sNEW and improved REST API%2$s plus %1$sNEW filters for developers%2$s. As this is a free plugin, ❤️ %6$sdonations%8$s or a 🛒 %7$spurchase of %4$s %8$s helps maintenance and support of these new improvements. If you like using %3$s and are able to contribute in either way, it would be greatly appreciated. 🙂 Thank you.', 'cart-rest-api-for-woocommerce' ),
					'<strong>',
					'</strong>',
					'CoCart',
					'CoCart Pro',
					esc_attr( COCART_NEXT_VERSION ),
					'<a href="https://www.buymeacoffee.com/sebastien" target="_blank">',
					'<a href="' . esc_url( $store_url ) . '" target="_blank">',
					'</a>'
				);
				?>
			</p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank">%2$s</a>', esc_url( COCART_STORE_URL . 'contact/' ), esc_html__( 'Sign Up to Test', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'upgrade_warning', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Don\'t ask me again', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
