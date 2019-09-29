<?php
/**
 * Admin View: Getting Started.
 *
 * @since    1.2.0
 * @version  2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap cocart getting-started">

	<div class="container">

		<div class="content">
			<div class="logo">
				<a href="<?php echo COCART_STORE_URL; ?>" target="_blank">
					<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="<?php echo esc_attr__( 'CoCart, a WooCommerce REST-API extension', 'cart-rest-api-for-woocommerce' ); ?>" />
				</a>
			</div>

			<h1><?php printf( __( 'Getting Started with %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></h1>

			<?php
			// Display message depending on the version of CoCart installed.
			if ( CoCart_Admin::is_cocart_pro_installed() ) {
			?>
				<p><strong><?php printf( __( 'Thanks for purchasing %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ); ?></strong></p>

				<p><?php printf( esc_html__( 'You\'ve just added more power to %1$s. %2$s to learn the additional endpoints now available to you.', 'cart-rest-api-for-woocommerce' ), 'CoCart', esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?></p>
			<?php } else { ?>
				<p><strong><?php printf( __( 'Thanks for choosing %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></strong></p>

				<p><?php printf( esc_html__( 'You\'ve just added more control for the %2$s REST-API. %1$s gives you the final piece to the %2$s REST-API to enable it\'s full potential allowing you to build a store entirely via the REST-API.', 'cart-rest-api-for-woocommerce' ), 'CoCart', 'WooCommerce' ); ?></p>
			<?php } ?>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', apply_filters( 'cocart_getting_started_doc_url', COCART_DOCUMENTATION_URL ), esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>

			<hr>

			<p><?php printf(
				/* translators: 1: Opening <a> tag to the CoCart Twitter account, 2: Opening <a> tag to the CoCart Instagram account, 3: Opening <a> tag to the Auto Load Next Post contact page, 4: Opening <a> tag to the CoCart newsletter, 5: Closing </a> tag, 6: Plugin Name */
				esc_html__( 'If you have any questions or feedback, let me know on %1$sTwitter%5$s, %2$sInstagram%5$s or via the %3$sFeedback page%5$s. Also, %4$ssubscribe to my newsletter%5$s if you want to stay up to date with what\'s new and upcoming in %6$s.', 'cart-rest-api-for-woocommerce' ), '<a href="https://twitter.com/cart_co" target="_blank">', '<a href="https://www.instagram.com/co_cart/" target="_blank">', '<a href="https://cocart.xyz/feedback/" target="_blank">', '<a href="http://eepurl.com/dKIYXE" target="_blank">', '</a>', 'CoCart'
			);
			?></p>

			<p><?php echo esc_html__( 'Enjoy!', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>

	</div>

</div>
