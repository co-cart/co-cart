<?php
/**
 * Admin View: Getting Started.
 *
 * @since    1.2.0
 * @version  2.0.11
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
					<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="CoCart Logo" />
				</a>
			</div>

			<h1><?php printf( __( 'Welcome to %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></h1>

			<p><?php printf( __( 'Thank you for choosing %s - the one and only REST API for WooCommerce in the market that handles the cart.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?>

			<p><?php printf( __( '%s makes it easy to control and manage the shopping cart in any framework of your choosing. Powerful and developer friendly, ready to build your headless store the way you want.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', COCART_DOCUMENTATION_URL, esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>
		</div>
	</div>

	<?php
	// Display extra content if CoCart Pro is NOT installed.
	if ( ! CoCart_Admin::is_cocart_pro_installed() ) {
	?>
		<div class="container">
			<div class="content">
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
				<p><?php printf( __( 'Integration with the official <a href="%s">WooCommerce Subscriptions</a> extension – see subscription details for any subscription product added to cart. Also extends support for Shipping Methods.', 'cart-rest-api-for-woocommerce' ), 'https://woocommerce.com/products/woocommerce-subscriptions/' ); ?></p>

				<hr>

				<p><?php printf( __( 'There are many add-ons also available to extend %s to enhance your development and your customers shopping experience.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

				<p style="text-align: center;">
					<?php printf( '<a class="button button-secondary button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://cocart.xyz/add-ons/?utm_source=WordPress&utm_medium=link&utm_campaign=liteplugin&utm_content=getting-started' ), esc_html__( 'See all Add-ons', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>

				<p class="price-tag"><sub>$</sub>159<sup>/yr</sup></p>

				<p><?php printf( __( 'Upgrade to %s and get plugin updates and support per year up to 10 websites. Access to all pro add-ons plus all future pro add-ons.', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ); ?></p>

				<p style="text-align: center;">
					<?php printf( '<a class="button upgrade button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( 'https://cocart.xyz/pro/?utm_source=WordPress&utm_medium=link&utm_campaign=liteplugin&utm_content=getting-started' ), esc_html__( 'Upgrade Now', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>
			</div>
		</div>

		<div class="container">
			<div class="content">
				<h2 style="text-align: center;"><?php _e( 'Testimonials', 'cart-rest-api-for-woocommerce' ); ?></h2>

				<p>This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.</p>

				<p>Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.</p>

				<strong>Allan Pooley</strong> - <img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">

				<p>Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.</p>

				<strong>MightyGroup</strong> - <img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
			</div>
		</div>
	<?php } ?>

</div>
