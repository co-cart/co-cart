<?php
/**
 * Admin View: WooCommerce not installed or activated notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   2.0.0
 * @version 3.7.2
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3>
				<?php
				echo sprintf(
					/* translators: 1: CoCart, 2: WooCommerce */
					esc_html__( '%1$s requires %2$s to be installed and activated.', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'WooCommerce'
				);
				?>
			</h3>

			<p>
			<?php
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) && file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' ) ) :

				if ( current_user_can( 'activate_plugin', 'woocommerce/woocommerce.php' ) ) :

					echo sprintf( '<a href="%1$s" class="button button-primary" aria-label="%2$s">%2$s</a>', esc_url( wp_nonce_url( self_admin_url( 'plugins.php?action=activate&plugin=woocommerce/woocommerce.php&plugin_status=active' ), 'activate-plugin_woocommerce/woocommerce.php' ) ), esc_html__( 'Activate WooCommerce', 'cart-rest-api-for-woocommerce' ) );

				else :

					echo esc_html__( 'As you do not have permission to activate a plugin. Please ask a site administrator to activate WooCommerce for you.', 'cart-rest-api-for-woocommerce' );

				endif;

			else :

				if ( current_user_can( 'install_plugins' ) ) {
					$url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=woocommerce' ), 'install-plugin_woocommerce' );
				} else {
					$url = 'https://wordpress.org/plugins/woocommerce/';
				}

				echo '<a href="' . esc_url( $url ) . '" class="button button-primary" aria-label="' . esc_html__( 'Install WooCommerce', 'cart-rest-api-for-woocommerce' ) . '">' . esc_html__( 'Install WooCommerce', 'cart-rest-api-for-woocommerce' ) . '</a>';

			endif;
			?>
			</p>
		</div>
	</div>
</div>
