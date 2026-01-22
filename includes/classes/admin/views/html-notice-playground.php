<?php
/**
 * Admin View: WordPress Playground Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_args['utm_source']   = 'wordpress-admin'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_medium']   = 'wordpress-admin'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_campaign'] = 'admin-notice'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_content']  = 'plugin-link'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="notice notice-error cocart-notice is-dismissible" role="alert">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/icon-logo.png' ); ?>" alt="CoCart Logo" /><?php // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
		</div>

		<div class="cocart-notice-content">
			<p>
				<?php esc_html_e( 'If WordPress Playground is having any issues. You can request a demo site instead.', 'cocart-core' ); ?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( add_query_arg( $campaign_args, 'https://cocartapi.com/try-free-demo/' ) ); ?>" class="button button-primary cocart-button" role="button"><?php echo esc_html__( 'Request Demo', 'cocart-core' ); ?></a>
		</div>
	</div>
</div>
