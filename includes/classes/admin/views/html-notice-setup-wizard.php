<?php
/**
 * Admin View: Notice - Setup Wizard
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.1.0
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
			<h3>
				<strong>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'Welcome to %s!', 'cocart-core' ),
					'CoCart'
				);
				?>
				</strong>
			</h3>
			<p>
				<?php
					printf(
						/* translators: %s: CoCart */
						esc_html__( 'To help prepare %s running smoothly, we would like to guide you with a setup wizard.', 'cocart-core' ),
						'CoCart'
					);
					?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=cocart-setup' ) ); ?>" class="button button-primary cocart-button" role="button">
				<?php echo esc_html__( 'Setup Wizard', 'cocart-core' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'setup_wizard', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks"><?php echo esc_html__( 'I will manually setup later.', 'cocart-core' ); ?></a>
		</div>
	</div>
</div>
