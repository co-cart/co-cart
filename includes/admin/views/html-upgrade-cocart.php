<?php
/**
 * Admin View: Upgrade CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_args = CoCart_Helpers::cocart_campaign(
	array(
		'utm_content' => 'upgrade-cocart',
	)
);
$store_url     = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL ) );
$addons_url    = admin_url( 'plugin-install.php?tab=cocart' );
$pro_url       = CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, COCART_STORE_URL . 'pro/' ) );
$star_svg      = COCART_URL_PATH . '/assets/images/star-filled.svg';
?>
<div class="wrap cocart upgrade-cocart">

	<div class="container">
		<div class="content">
			<div class="logo">
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank">
					<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/header-logo.png' ); ?>" alt="CoCart Logo" />
				</a>
			</div>

			<h2 style="text-align: center;">
			<?php
			printf(
				/* translators: %s: CoCart Pro */
				esc_html__( 'More features in %s', 'cart-rest-api-for-woocommerce' ),
				'CoCart Pro'
			);
			?>
			</h2>

			<h3><?php esc_html_e( 'Coupons', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Apply coupons, remove coupons, validate coupons should the cart have been left unattended for some time and get all applied coupons to the cart.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Cross Sells', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Return products based on the items in the cart to promote them to your customer.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Customer Details', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Return details of the customer in session. If they are a returning customer, you are ready to pre-fill your checkout form for them.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Fees', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Need to apply an additional fee to the cart? No problem. Add a fee, Get all fees applied, Remove all fees and calculate fees.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Payment Methods', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Return the payment methods available in your store for your customer to choose from if more than one.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Shipping Methods', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Once the shipping is calculated you can fetch the available shipping methods and set according to the customers selection.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Quantities', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Return each item in the cart with the quantity of that item the customer has added.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Removed Items', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'Access to items removed from the cart by the customer should you wish to allow the customer to re-add an item to the cart if they made a mistake.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Cart Weight', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php esc_html_e( 'If you are shipping a lot of heavy items, knowing how much a customers cart weighs is a no brainier.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php esc_html_e( 'Subscriptions', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
			<?php
			printf(
				/* translators: 1: Start of link, 2: WooCommerce Subscriptions, 3: Close of link */
				__( 'Integration with the official %1$s%2$s%3$s extension – see subscription details for any subscription product added to cart. Also extends support for Shipping Methods.', 'cart-rest-api-for-woocommerce' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'<a href="' . esc_url( 'https://woocommerce.com/products/woocommerce-subscriptions/' ) . '" target="_blank">',
				'WooCommerce Subscriptions',
				'</a>'
			);
			?>
			</p>

			<hr>

			<p class="price-tag"><sub>$</sub>69<sup>/yr</sup></p>

			<p>
			<?php
			printf(
				/* translators: %s: CoCart Pro */
				esc_html__( 'Upgrade to %s and get plugin updates and support per year for 1 website. You may upgrade your licence at any time, just pay the difference in cost!', 'cart-rest-api-for-woocommerce' ),
				'CoCart Pro'
			);
			?>
			</p>

			<p style="text-align: center;">
				<?php printf( '<a class="button upgrade button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( $pro_url ), esc_html__( 'Upgrade Now', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>
		</div>
	</div>

	<div class="container">
		<div class="content">
			<h2 style="text-align: center;"><?php esc_html_e( 'Testimonials', 'cart-rest-api-for-woocommerce' ); ?></h2>

			<video controls playsinline disablepictureinpicture poster="<?php echo esc_url( COCART_URL_PATH . '/assets/images/testimonials/james-rowland.png' ); ?>" style="width: 100%; height: 100%; max-height: 65vh;" preload="auto">
				<source src="https://cocart.xyz/wp-content/uploads/2021/06/james-video-testimonial.mp4" type="video/mp4">
			</video>

			<p><strong>James Rowland</strong> - CEO of PerfectCheckout.com <img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true"></p>

			<hr>

			<p>What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.</p>

			<p><strong>Joel Pierre</strong> – JPPdesigns Web design & Development <img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true"></p>

			<hr>

			<p>This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.</p>

			<p>Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.</p>

			<p><strong>Allan Pooley</strong> – Little and Big <img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true"></p>

			<hr>

			<p>Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.</p>

			<p><strong>MightyGroup</strong> – Rikard Kling <img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo esc_url( $star_svg ); ?>" data-lazy-src="<?php echo esc_url( $star_svg ); ?>" data-was-processed="true"></p>
		</div>
	</div>

</div>
