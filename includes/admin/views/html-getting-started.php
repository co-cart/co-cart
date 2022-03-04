<?php
/**
 * Admin View: Getting Started.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   1.2.0
 * @version 3.1.0
 * @license GPL-2.0+
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
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank">
					<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/header-logo.png' ); ?>" alt="CoCart Logo" />
				</a>
			</div>

			<h1>
				<?php
				printf(
					/* translators: 1: CoCart */
					esc_html__( 'Welcome to %s.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</h1>

			<p>
				<?php
				printf(
					/* translators: 1: CoCart, 2: WooCommerce */
					esc_html__( 'Thank you for choosing %1$s - the #1 REST API that handles the frontend of %2$s.', 'cart-rest-api-for-woocommerce' ),
					'CoCart',
					'WooCommerce'
				);
				?>
			</p>

			<p>
				<?php
				printf(
					/* translators: 1: CoCart */
					esc_html__( '%s focuses on the front-end of the store helping you to manage shopping carts and allows developers to build a headless store in any framework of their choosing. No local storing required.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>

			<p>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'Now that you have %1$s installed, your ready to start developing your headless store.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>

			<p>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'In the documentation you will find the API routes available along with over 100+ action hooks and filters for developers to customise API responses or change how %1$s operates.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>

			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: Developers Hub link */
						__( 'There is also a <a href="%1$s" target="_blank">developers hub</a> where you can find all the resources you need to be productive with CoCart and keep track of everything that is happening with the plugin including development decisions and scoping of future versions.', 'cart-rest-api-for-woocommerce' ),
						'https://cocart.dev'
					)
				);
				?>
			</p>

			<p>
				<?php
				esc_html_e( 'It also provides answers to most common questions should you find that you need help. This is best place to look at first before contacting for support.', 'cart-rest-api-for-woocommerce' );
				?>
			</p>

			<p>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'If you do need support or simply want to talk to other developers about taking your WooCommerce store headless, come join the %s community.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>

			<p><?php esc_html_e( 'Thank you and enjoy!', 'cart-rest-api-for-woocommerce' ); ?></p>

			<p><?php esc_html_e( 'regards,', 'cart-rest-api-for-woocommerce' ); ?></p>

			<div class="founder-row">
				<div class="founder-image">
					<img src="<?php echo 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( 'mailme@sebastiendumont.com' ) ) ) . '?d=mp&s=60'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" width="60px" height="60px" alt="Photo of Founder" />
				</div>

				<div class="founder-details">
					<p>Sébastien Dumont<br>
					<?php
					echo sprintf(
						/* translators: %s: CoCart */
						esc_html__( 'Founder of %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					);
					?>
					</p>
				</div>
			</div>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', esc_url( COCART_DOCUMENTATION_URL ), esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?> 
				<?php printf( '<a class="button button-secondary button-large" href="%1$s" target="_blank">%2$s</a>', esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ) ), esc_html__( 'Join Community', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>

			<?php if ( CoCart_Helpers::is_cocart_ps_active() ) { ?>
			<hr>

			<p><?php printf( esc_html__( 'Want to find compatible plugins or extensions for CoCart. Checkout our plugin suggestions that can help enhance your development and your customers shopping experience.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-secondary button-medium" href="%1$s">%2$s</a>', esc_url( $addons_url ), esc_html__( 'View Plugin Suggestions', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>
			<?php } ?>
		</div>
	</div>

</div>
