<?php
/**
 * Admin View: Getting Started.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin\Views
 * @since    1.2.0
 * @version  3.1.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_args = CoCart_Helpers::cocart_campaign(
	array(
		'utm_content' => 'getting-started',
	)
);
$store_url     = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL ) );
$addons_url    = admin_url( 'plugin-install.php?tab=cocart' );
$pro_url       = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL . 'pro/' ) );
?>
<div class="wrap cocart getting-started">

	<div class="container">
		<div class="content">
			<div class="logo">
				<a href="<?php echo $store_url; ?>" target="_blank">
					<img src="<?php echo COCART_URL_PATH . '/assets/images/brand/logo.jpg'; ?>" alt="CoCart Logo" />
				</a>
			</div>

			<h1><?php printf( __( 'Welcome to %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></h1>

			<p><?php printf( __( 'Thank you for choosing %1$s - the #1 REST API that handles the frontend of %2$s.', 'cart-rest-api-for-woocommerce' ), 'CoCart', 'WooCommerce' ); ?>

			<p><?php printf( __( '%s focuses on the front-end of the store helping you to manage shopping carts and allows developers to build a headless store in any framework of their choosing. No local storing required.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?>

			<p><?php printf( __( 'Now that you have %1$s installed, your ready to start developing. In the documentation you will find the API routes available along with action hooks and filters that allow you to customise %1$s to your needs.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

			<p><?php _e( 'There is also a knowledge base section that provides answers to most common questions should you find that you need help. This is best to be looked at first before contacting for support.', 'cart-rest-api-for-woocommerce' ); ?>

			<p><?php printf( __( 'If you do need support or simply want to talk to other developers about taking your WooCommerce store headless, come join the %s community.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?>

			<?php
			// Display warning notice if WooCommerce is not installed or the minimum required version.
			if ( ! defined( 'WC_VERSION' ) || CoCart_Helpers::is_not_wc_version_required() ) {
				echo '<p><strong>' . sprintf( __( 'It appears you either do not have %1$s installed or have the minimum required version to be compatible with %2$s. Please install or update your %1$s setup.', 'cart-rest-api-for-woocommerce' ), 'WooCommerce', 'CoCart' ) . '</strong></p>';
			}
			?>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', COCART_DOCUMENTATION_URL, esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?> 
				<?php printf( '<a class="button button-secondary button-large" href="%1$s" target="_blank">%2$s</a>', CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ), esc_html__( 'Join Community', 'cart-rest-api-for-woocommerce' ) ); ?>

				<?php if ( CoCart_Helpers::is_cocart_ps_active() ) { ?>
				<hr>

				<p><?php printf( __( 'Want to find compatible plugins or extensions for CoCart. Checkout our plugin suggestions that can help enhance your development and your customers shopping experience.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

				<p style="text-align: center;">
					<?php printf( '<a class="button button-secondary button-medium" href="%1$s">%2$s</a>', esc_url( $addons_url ), esc_html__( 'View Plugin Suggestions', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>
				<?php } ?>
			</p>
		</div>
	</div>

</div>
