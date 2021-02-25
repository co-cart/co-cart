<?php
/**
 * Admin View: Required WooCommerce Notice.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Views
 * @since    2.0.0
 * @version  3.0.0
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
			<h3><?php echo esc_html__( 'Update Required!', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php echo sprintf( __( '%1$s requires at least %2$s v%3$s or higher.', 'cart-rest-api-for-woocommerce' ), 'CoCart', 'WooCommerce', CoCart::$required_woo ); ?></p>
		</div>

		<?php if ( current_user_can( 'update_plugins' ) ) { ?>
		<div class="cocart-action">
			<?php
			$upgrade_url = wp_nonce_url(
				add_query_arg(
					array(
						'action' => 'upgrade-plugin',
						'plugin' => 'woocommerce',
					),
					self_admin_url( 'update.php' )
				),
				'upgrade-plugin_woocommerce'
			);
			$upgrade_url = wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_wc', $upgrade_url ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' );
			?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary cocart-button" aria-label="<?php echo esc_html__( 'Update WooCommerce', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo sprintf( esc_html__( 'Update %s', 'cart-rest-api-for-woocommerce' ), 'WooCommerce' ); ?></a>
			<a class="no-thanks" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_wc', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php _e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
