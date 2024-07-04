<?php
/**
 * Admin View: WordPress Playground Notice.
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

$campaign_args['utm_source']   = 'wordpress-admin'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_medium']   = 'wordpress-admin'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_campaign'] = 'admin-notice'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$campaign_args['utm_content']  = 'plugin-link'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="notice notice-error cocart-notice is-dismissible">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<p>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'WordPress Playground is not compatible with the %s plugin. Recommend requesting a demo instead.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( add_query_arg( $campaign_args, 'https://cocartapi.com/try-free-demo/' ) ); ?>" class="button button-primary cocart-button" aria-label="<?php echo esc_html__( 'Request Demo', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Request Demo', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
