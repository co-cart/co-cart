<?php
/**
 * Admin View: Next Steps for a CoCart user.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tweets users can optionally send.
 *
 * @var array
 */
$tweets = array( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	'Cha ching. I just set up a headless store with @cocartapi!',
	'Someone give me high five, I just set up a headless store with @cocartapi!',
	'Want to build a fast headless store like mine? Checkout @cocartapi and decouple in days, not months.',
	'Build headless stores, without building an API. Checkout @cocartapi - Designed for @WooCommerce.',
);

$tweet = array_rand( $tweets ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$current_user = wp_get_current_user(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$user_email   = $current_user->user_email; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$campaign_args = CoCart_Helpers::cocart_campaign( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	array(
		'utm_source'  => 'CoCartCore',
		'utm_medium'  => 'plugin-admin',
		'utm_content' => 'next-steps',
	)
);

$docs_url  = 'https://cocart.dev/'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
$help_text = sprintf( // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
	/* translators: %1$s: link to docs */
	__( 'Visit CoCart.dev to access <a href="%1$s" target="_blank" rel="noopener noreferrer">developer resources</a>.', 'cart-rest-api-for-woocommerce' ),
	$docs_url
);
?>
<div class="cocart-newsletter">
	<p><?php esc_html_e( 'Get product updates, tutorials and more straight to your inbox.', 'cart-rest-api-for-woocommerce' ); ?></p>
	<form action="https://xyz.us1.list-manage.com/subscribe/post?u=48ead612ad85b23fe2239c6e3&amp;id=d462357844&amp;SIGNUPPAGE=plugin" method="post" target="_blank" rel="noopener noreferrer" novalidate>
		<div class="newsletter-form-container">
			<input
				class="newsletter-form-email"
				type="email"
				value="<?php echo esc_attr( $user_email ); ?>"
				name="EMAIL"
				placeholder="<?php esc_attr_e( 'Email address', 'cart-rest-api-for-woocommerce' ); ?>"
				required
			>
			<p class="cocart-actions step newsletter-form-button-container">
				<button
					type="submit"
					value="<?php esc_attr_e( 'Yes please!', 'cart-rest-api-for-woocommerce' ); ?>"
					name="subscribe"
					id="mc-embedded-subscribe"
					class="button button-primary newsletter-form-button"
				><?php esc_html_e( 'Yes please!', 'cart-rest-api-for-woocommerce' ); ?></button>
			</p>
		</div>
	</form>
</div>

<ul class="cocart-next-steps">
	<li class="cocart-next-step-item">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Next step', 'cart-rest-api-for-woocommerce' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Start Developing', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( "You're ready to develop your headless store.", 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<p class="cocart-actions step">
				<a class="button button-primary button-large" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_DOCUMENTATION_URL ) ) ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'View Documentation', 'cart-rest-api-for-woocommerce' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="cocart-next-step-item">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Need something else?', 'cart-rest-api-for-woocommerce' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Install Plugins', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( 'Checkout plugin suggestions by CoCart.', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<p class="cocart-actions step">
				<a class="button button-large" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=cocart' ) ); ?>" target="_blank">
					<?php esc_html_e( 'View Plugin Suggestions', 'cart-rest-api-for-woocommerce' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="cocart-additional-steps">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'You can also', 'cart-rest-api-for-woocommerce' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<p class="cocart-actions step">
				<a class="button" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ) ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Join Community', 'cart-rest-api-for-woocommerce' ); ?>
				</a>
				<?php
				// Only show upgrade option if neither CoCart Plus, Pro or above is found.
				if ( apply_filters( 'cocart_show_upgrade_action_link', true ) ) {
					?>
				<a class="button" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'pricing/' ) ) ) ); ?>" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Unlock more features', 'cart-rest-api-for-woocommerce' ); ?>
				</a><?php } ?>
				<a class="button" href="<?php echo esc_url( 'https://marketplace.visualstudio.com/items?itemName=sebastien-dumont.cocart-vscode' ); ?>" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Install VSCode Extension', 'cart-rest-api-for-woocommerce' ); ?>
				</a>
			</p>
		</div>
	</li>
</ul>

<p class="tweet-share">
	<a href="https://twitter.com/share" class="twitter-share-button" data-size="large" data-text="<?php echo esc_html( $tweets[ $tweet ] ); ?>" data-url="https://cocartapi.com/" data-hashtags="withcocart" data-related="WooCommerce" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
</p>

<p class="next-steps-help-text"><?php echo wp_kses_post( $help_text ); ?></p>
