<?php
/**
 * Admin View: Notice - Updated.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.0.0
 * @version 3.0.7
 * @license GPL-2.0+
 */

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
			<h3><?php esc_html_e( 'Database updated', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
				<?php
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( '%s database update complete. Thank you for updating to the latest version!', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>
		</div>

		<div class="cocart-action">
			<a class="button button-primary cocart-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'update_db', remove_query_arg( 'do_update_cocart', CoCart_Helpers::cocart_get_current_admin_url() ) ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
