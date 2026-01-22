<?php
/**
 * Admin View: Upgrade Warning Notice.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin\Views
 * @since    1.2.3
 * @version  4.3.18
 * @license  GPL-3.0
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
				esc_html__( 'What is next for %s?', 'cocart-core' ),
				'CoCart'
			);
			?>
			</h3>

			<p>
			<?php
			printf(
				/* translators: %1$s: CoCart, %2$s: CoCart's Next Version */
				esc_html__( 'Version %2$s of %1$s is almost ready for beta testing with many improvements including some broken changes.', 'cocart-core' ),
				'CoCart',
				esc_attr( COCART_NEXT_VERSION )
			);
			?>
			</p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" target="_blank" rel="noopener noreferrer" role="button">%2$s</a>', esc_url( 'https://github.com/co-cart/co-cart/blob/development/NEXT_CHANGELOG.md' ), esc_html__( "What's Coming Next?", 'cocart-core' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'upgrade_warning', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice.', 'cocart-core' ); ?>"><?php echo esc_html__( 'Remind me another time', 'cocart-core' ); ?></a>
		</div>
	</div>
</div>
