<?php
/**
 * Admin View: Getting Started.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @since    1.2.0
 * @version  2.3.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$campaign_args = array(
	'utm_medium'   => 'co-cart-lite',
	'utm_source'   => 'WordPress',
	'utm_campaign' => 'liteplugin',
	'utm_content'  => 'getting-started'
);
$store_url  = add_query_arg( $campaign_args, COCART_STORE_URL );
$addons_url = add_query_arg( $campaign_args, COCART_STORE_URL . 'add-ons/' );
$pro_url    = add_query_arg( $campaign_args, COCART_STORE_URL . 'pro/' );
?>
<div class="wrap cocart getting-started">

	<div class="container">
		<div class="content">
			<div class="logo">
				<a href="<?php echo $store_url; ?>" target="_blank">
					<img src="<?php echo COCART_URL_PATH . '/assets/images/logo.jpg'; ?>" alt="CoCart Logo" />
				</a>
			</div>

			<h1><?php printf( __( 'Welcome to %s.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></h1>

			<p><?php printf( __( 'Thank you for choosing %1$s - the #1 REST API that handles the frontend of %2$s.', 'cart-rest-api-for-woocommerce' ), 'CoCart', 'WooCommerce' ); ?>

			<p><?php printf( __( '%s saves you time and money to develop a REST API. No local storing and zero configuration required. Powerful and developer friendly for any modern framework of your choosing ready to build your headless store.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?>

			<p><?php printf( __( 'Now that you have %s installed, your ready to start developing.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

			<?php
			// Display warning notice if WooCommerce is not installed or the minimum required version.
			if ( ! defined( 'WC_VERSION' ) || CoCart_Helpers::is_not_wc_version_required() ) {
				echo '<p><strong>' . sprintf( __( 'It appears you either do not have %1$s installed or have the minimum required version to be compatible with %2$s. Please install or update your %1$s setup.', 'cart-rest-api-for-woocommerce' ), 'WooCommerce', 'CoCart' ) . '</strong></p>';
			}
			?>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', COCART_DOCUMENTATION_URL, esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?>
			</p>
		</div>
	</div>

	<?php
	// Display extra content if CoCart Pro is NOT installed.
	if ( ! CoCart_Helpers::is_cocart_pro_installed() ) {
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
				<p><?php printf( __( 'Integration with the official <a href="%s" target="_blank">WooCommerce Subscriptions</a> extension – see subscription details for any subscription product added to cart. Also extends support for Shipping Methods.', 'cart-rest-api-for-woocommerce' ), 'https://woocommerce.com/products/woocommerce-subscriptions/' ); ?></p>

				<hr>

				<p><?php printf( __( 'There are many add-ons also available to extend %s to enhance your development and your customers shopping experience.', 'cart-rest-api-for-woocommerce' ), 'CoCart' ); ?></p>

				<p style="text-align: center;">
					<?php printf( '<a class="button button-secondary button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( $addons_url ), esc_html__( 'See all Add-ons', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>

				<p class="price-tag"><sub>$</sub>159<sup>/yr</sup></p>

				<p><?php printf( __( 'Upgrade to %s and get plugin updates and support per year up to 10 websites. Access to all pro add-ons plus all future pro add-ons.', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ); ?></p>

				<p style="text-align: center;">
					<?php printf( '<a class="button upgrade button-medium" href="%1$s" target="_blank">%2$s</a>', esc_url( $pro_url ), esc_html__( 'Upgrade Now', 'cart-rest-api-for-woocommerce' ) ); ?>
				</p>
			</div>
		</div>

		<div class="container">
			<div class="content">
				<h2 style="text-align: center;"><?php _e( 'Testimonials', 'cart-rest-api-for-woocommerce' ); ?></h2>

				<p>What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.</p>

				<strong>Joel Pierre</strong> – JPPdesigns Web design & Development <img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">

				<p>This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.</p>

				<p>Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.</p>

				<strong>Allan Pooley</strong> – Little and Big <img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">

				<p>Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.</p>

				<strong>MightyGroup</strong> – Rikard Kling <img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
				<img draggable="false" class="emoji" alt="⭐" src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-lazy-src="https://s.w.org/images/core/emoji/12.0.0-1/svg/2b50.svg" data-was-processed="true">
			</div>
		</div>
	<?php } ?>

</div>
