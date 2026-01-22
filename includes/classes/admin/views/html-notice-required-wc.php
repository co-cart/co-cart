<?php
/**
 * Admin View: Required WooCommerce Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   2.0.0
 * @version 3.0.7
 * @license GPL-3.0
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
			<h3><?php echo esc_html__( 'Update Required!', 'cocart-core' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: 1: CoCart, 2: WooCommerce, 3: Required WooCommerce version */
					esc_html__( '%1$s requires at least %2$s v%3$s or higher.', 'cocart-core' ),
					'CoCart',
					'WooCommerce',
					esc_attr( CoCart::$required_woo )
				);
				?>
			</p>
		</div>

		<?php if ( current_user_can( 'update_plugins' ) ) { ?>
		<div class="cocart-action">
			<?php
			$upgrade_url = wp_nonce_url( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				add_query_arg(
					array(
						'action' => 'upgrade-plugin',
						'plugin' => 'woocommerce',
					),
					self_admin_url( 'update.php' )
				),
				'upgrade-plugin_woocommerce'
			);
			$upgrade_url = wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_wc', $upgrade_url ), 'cocart_hide-notices_nonce', '_cocart_notice_nonce' ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			?>
			<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary cocart-button" role="button"><?php echo esc_html__( 'Update WooCommerce', 'cocart-core' ); ?></a>
			<a class="no-thanks" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'check_wc', esc_url( CoCart_Helpers::cocart_get_current_admin_url() ) ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cocart-core' ); ?></a>
		</div>
		<?php } ?>
	</div>
</div>
