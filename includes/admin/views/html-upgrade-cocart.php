<?php
/**
 * Admin View: Upgrade CoCart.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin\Views
 * @since    3.1.0
 * @license  GPL-2.0+
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
				<a href="<?php echo $store_url; ?>" target="_blank">
					<img src="<?php echo COCART_URL_PATH . '/assets/images/brand/logo.jpg'; ?>" alt="CoCart Logo" />
				</a>
			</div>

			<?php
			// Display warning notice if WooCommerce is not installed or the minimum required version.
			if ( ! defined( 'WC_VERSION' ) || CoCart_Helpers::is_not_wc_version_required() ) {
				echo '<p><strong>' . sprintf( __( 'It appears you either do not have %1$s installed or have the minimum required version to be compatible with %2$s. Please install or update your %1$s setup.', 'cart-rest-api-for-woocommerce' ), 'WooCommerce', 'CoCart' ) . '</strong></p>';
			}
			?>

			<h2 style="text-align: center;"><?php printf( __( 'More features in %s', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ); ?></h2>

			<h3><?php _e( 'Coupons', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Apply coupons, remove coupons, validate coupons should the cart have been left unattended for some time and get all applied coupons to the cart.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Cross Sells', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Return products based on the items in the cart to promote them to your customer.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Customer Details', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Return details of the customer in session. If they are a returning customer, you are ready to pre-fill your checkout form for them.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Fees', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Need to apply an additional fee to the cart? No problem. Add a fee, Get all fees applied, Remove all fees and calculate fees.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Payment Methods', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Return the payment methods available in your store for your customer to choose from if more than one.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Shipping Methods', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Once the shipping is calculated you can fetch the available shipping methods and set according to the customers selection.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Quantities', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Return each item in the cart with the quantity of that item the customer has added.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Removed Items', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'Access to items removed from the cart by the customer should you wish to allow the customer to re-add an item to the cart if they made a mistake.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Cart Weight', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php _e( 'If you are shipping a lot of heavy items, knowing how much a customers cart weighs is a no brainier.', 'cart-rest-api-for-woocommerce' ); ?></p>

			<h3><?php _e( 'Subscriptions', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p><?php printf( __( 'Integration with the official <a href="%s" target="_blank">WooCommerce Subscriptions</a> extension – see subscription details for any subscription product added to cart. Also extends support for Shipping Methods.', 'cart-rest-api-for-woocommerce' ), esc_url( 'https://woocommerce.com/products/woocommerce-subscriptions/' ) ); ?></p>

			<hr>

			<p class="price-tag"><sub>$</sub>69<sup>/yr</sup></p>

			<p><?php printf( __( 'Upgrade to %s and get plugin updates and support per year for 1 website. You may upgrade your licence at any time, just pay the difference in cost!', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ); ?></p>

			<p style="text-align: center;">
				<?php printf( '<a class="button upgrade button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( $pro_url ), esc_html__( 'Upgrade Now', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>
		</div>
	</div>

	<div class="container">
		<div class="content">
			<h2 style="text-align: center;"><?php _e( 'Testimonials', 'cart-rest-api-for-woocommerce' ); ?></h2>

			<video controls playsinline disablepictureinpicture poster="<?php echo COCART_URL_PATH . '/assets/images/testimonials/james-rowland.png'; ?>" style="width: 100%; height: 100%; max-height: 65vh;" preload="auto">
				<source src="https://cocart.xyz/wp-content/uploads/2021/06/james-video-testimonial.mp4" type="video/mp4">
			</video>

			<strong>James Rowland</strong> - CEO of PerfectCheckout.com <img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">

			<p>What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.</p>

			<strong>Joel Pierre</strong> – JPPdesigns Web design & Development <img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">

			<p>This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.</p>

			<p>Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.</p>

			<strong>Allan Pooley</strong> – Little and Big <img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">

			<p>Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.</p>

			<strong>MightyGroup</strong> – Rikard Kling <img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
			<img draggable="false" class="emoji" alt="⭐" src="<?php echo $star_svg; ?>" data-lazy-src="<?php echo $star_svg; ?>" data-was-processed="true">
		</div>
	</div>

</div>
