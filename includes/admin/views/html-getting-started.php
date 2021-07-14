<?php
/**
 * Admin View: Getting Started.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\Admin\Views
 * @since    1.2.0
 * @version  3.0.7
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
				<a href="<?php echo esc_url( $store_url ); ?>" target="_blank">
					<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/logo.jpg' ); ?>" alt="CoCart Logo" />
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
					/* translators: 1: CoCart */
					esc_html__( 'Now that you have %1$s installed, your ready to start developing. In the documentation you will find the API routes available along with action hooks and filters that allow you to customise %1$s to your needs.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>

			<p>
				<?php
				esc_html_e( 'There is also a knowledge base section that provides answers to most common questions should you find that you need help. This is best to be looked at first before contacting for support.', 'cart-rest-api-for-woocommerce' );
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

			<?php
			// Display warning notice if WooCommerce is not installed or the minimum required version.
			if ( ! defined( 'WC_VERSION' ) || CoCart_Helpers::is_not_wc_version_required() ) {
				echo '<p><strong>' . sprintf(
					/* translators: 1: WooCommerce, 2: CoCart */
					esc_html__( 'It appears you either do not have %1$s installed or have the minimum required version to be compatible with %2$s. Please install or update your %1$s setup.', 'cart-rest-api-for-woocommerce' ),
					'WooCommerce',
					'CoCart'
				) . '</strong></p>';
			}
			?>

			<p style="text-align: center;">
				<?php printf( '<a class="button button-primary button-large" href="%1$s" target="_blank">%2$s</a>', esc_url( COCART_DOCUMENTATION_URL ), esc_html__( 'View Documentation', 'cart-rest-api-for-woocommerce' ) ); ?> 
				<?php printf( '<a class="button button-secondary button-large" href="%1$s" target="_blank">%2$s</a>', CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ), esc_html__( 'Join Community', 'cart-rest-api-for-woocommerce' ) ); ?>
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

	<?php
	// Display extra content if CoCart Pro is NOT activated.
	if ( ! CoCart_Helpers::is_cocart_pro_activated() ) {
		?>
		<div class="container">
			<div class="content">
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
					/* translators: %s URL to WC Subscriptions product page. */
					__( 'Integration with the official <a href="%s" target="_blank">WooCommerce Subscriptions</a> extension – see subscription details for any subscription product added to cart. Also extends support for Shipping Methods.', 'cart-rest-api-for-woocommerce' ),
					esc_url( 'https://woocommerce.com/products/woocommerce-subscriptions/' )
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
