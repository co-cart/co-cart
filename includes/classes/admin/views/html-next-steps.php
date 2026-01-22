<?php
/**
 * Admin View: Next Steps for a CoCart user.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.10.0 Introduced.
 * @license GPL-3.0
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
?>
<div class="cocart-newsletter">
	<p><?php esc_html_e( 'Get product updates, tutorials and more straight to your inbox.', 'cocart-core' ); ?></p>
	<form action="https://xyz.us1.list-manage.com/subscribe/post?u=48ead612ad85b23fe2239c6e3&amp;id=d462357844&amp;SIGNUPPAGE=plugin" method="post" target="_blank" rel="noopener noreferrer" novalidate>
		<div class="newsletter-form-container">
			<label for="newsletter-email" class="screen-reader-text"><?php esc_html_e( 'Email address', 'cocart-core' ); ?></label>
			<input
				class="newsletter-form-email"
				type="email"
				value="<?php echo esc_attr( $user_email ); ?>"
				name="EMAIL"
				placeholder="<?php esc_attr_e( 'Email address', 'cocart-core' ); ?>"
				required
			>
			<p class="cocart-actions step newsletter-form-button-container">
				<button
					type="submit"
					value="<?php esc_attr_e( 'Yes please!', 'cocart-core' ); ?>"
					name="subscribe"
					id="mc-embedded-subscribe"
					class="button button-primary cocart-button newsletter-form-button"
				><?php esc_html_e( 'Yes please!', 'cocart-core' ); ?></button>
			</p>
		</div>
	</form>
</div>

<ul class="cocart-next-steps">
	<li class="cocart-next-step-item">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Next step', 'cocart-core' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Start Developing', 'cocart-core' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( "You're ready to develop your headless store.", 'cocart-core' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<p class="cocart-actions step">
				<a class="button button-primary button-large cocart-button" href="<?php echo esc_url( COCART_DOCUMENTATION_URL ); ?>" target="_blank" rel="noopener noreferrer" role="button">
					<?php esc_html_e( 'View Documentation', 'cocart-core' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="cocart-next-step-item">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'Need something else?', 'cocart-core' ); ?></p>
			<h3 class="next-step-description"><?php esc_html_e( 'Install Plugins', 'cocart-core' ); ?></h3>
			<p class="next-step-extra-info"><?php esc_html_e( 'Checkout plugin suggestions by CoCart.', 'cocart-core' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<p class="cocart-actions step">
				<a class="button button-large cocart-button" href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=cocart' ) ); ?>" target="_blank" role="button">
					<?php esc_html_e( 'View Plugin Suggestions', 'cocart-core' ); ?>
				</a>
			</p>
		</div>
	</li>
	<li class="cocart-additional-steps">
		<div class="cocart-next-step-description">
			<p class="next-step-heading"><?php esc_html_e( 'You can also', 'cocart-core' ); ?></p>
		</div>
		<div class="cocart-next-step-action">
			<div class="column-container">
				<div class="column">
					<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 640 512" aria-hidden="true" focusable="false">
						<path d="M0 128C0 92.7 28.7 64 64 64l192 0 48 0 16 0 256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64l-256 0-16 0-48 0L64 448c-35.3 0-64-28.7-64-64L0 128zm320 0l0 256 256 0 0-256-256 0zM178.3 175.9c-3.2-7.2-10.4-11.9-18.3-11.9s-15.1 4.7-18.3 11.9l-64 144c-4.5 10.1 .1 21.9 10.2 26.4s21.9-.1 26.4-10.2l8.9-20.1 73.6 0 8.9 20.1c4.5 10.1 16.3 14.6 26.4 10.2s14.6-16.3 10.2-26.4l-64-144zM160 233.2L179 276l-38 0 19-42.8zM448 164c11 0 20 9 20 20l0 4 44 0 16 0c11 0 20 9 20 20s-9 20-20 20l-2 0-1.6 4.5c-8.9 24.4-22.4 46.6-39.6 65.4c.9 .6 1.8 1.1 2.7 1.6l18.9 11.3c9.5 5.7 12.5 18 6.9 27.4s-18 12.5-27.4 6.9l-18.9-11.3c-4.5-2.7-8.8-5.5-13.1-8.5c-10.6 7.5-21.9 14-34 19.4l-3.6 1.6c-10.1 4.5-21.9-.1-26.4-10.2s.1-21.9 10.2-26.4l3.6-1.6c6.4-2.9 12.6-6.1 18.5-9.8l-12.2-12.2c-7.8-7.8-7.8-20.5 0-28.3s20.5-7.8 28.3 0l14.6 14.6 .5 .5c12.4-13.1 22.5-28.3 29.8-45L448 228l-72 0c-11 0-20-9-20-20s9-20 20-20l52 0 0-4c0-11 9-20 20-20z"/>
					</svg>
					<h3><?php esc_html_e( 'Translate', 'cocart-core' ); ?></h3>
					<p>
					<?php
					printf(
						/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing, %3$s: CoCart */
						esc_html__( 'Is %3$s not translated in your language or do you spot errors with the current translations? Helping out is easy!', 'cocart-core' ),
						'<a href="' . esc_url( COCART_TRANSLATION_URL ) . '" title="' . esc_html__( 'Help translate', 'cocart-core' ) . ' "target="_blank" rel="noopener noreferrer">',
						'</a>',
						'CoCart'
					);
					?>
					</p>
					<a class="button cocart-button-alt" href="<?php echo esc_url( COCART_TRANSLATION_URL ); ?>" target="_blank"><?php esc_html_e( 'Help translate', 'cocart-core' ); ?></a>
				</div>
				<div class="column">
					<svg width="48" height="48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="#5865f2" viewBox="0 0 126.644 96" style="color: #5865f2">
						<path stroke="currentColor" d="M81.15,0c-1.2376,2.1973-2.3489,4.4704-3.3591,6.794-9.5975-1.4396-19.3718-1.4396-28.9945,0-.985-2.3236-2.1216-4.5967-3.3591-6.794-9.0166,1.5407-17.8059,4.2431-26.1405,8.0568C2.779,32.5304-1.6914,56.3725.5312,79.8863c9.6732,7.1476,20.5083,12.603,32.0505,16.0884,2.6014-3.4854,4.8998-7.1981,6.8698-11.0623-3.738-1.3891-7.3497-3.1318-10.8098-5.1523.9092-.6567,1.7932-1.3386,2.6519-1.9953,20.281,9.547,43.7696,9.547,64.0758,0,.8587.7072,1.7427,1.3891,2.6519,1.9953-3.4601,2.0457-7.0718,3.7632-10.835,5.1776,1.97,3.8642,4.2683,7.5769,6.8698,11.0623,11.5419-3.4854,22.3769-8.9156,32.0509-16.0631,2.626-27.2771-4.496-50.9172-18.817-71.8548C98.9811,4.2684,90.1918,1.5659,81.1752.0505l-.0252-.0505ZM42.2802,65.4144c-6.2383,0-11.4159-5.6575-11.4159-12.6535s4.9755-12.6788,11.3907-12.6788,11.5169,5.708,11.4159,12.6788c-.101,6.9708-5.026,12.6535-11.3907,12.6535ZM84.3576,65.4144c-6.2637,0-11.3907-5.6575-11.3907-12.6535s4.9755-12.6788,11.3907-12.6788,11.4917,5.708,11.3906,12.6788c-.101,6.9708-5.026,12.6535-11.3906,12.6535Z"/>
					</svg>
					<h3><?php esc_html_e( 'Community Support', 'cocart-core' ); ?></h3>
					<p>
					<?php
					printf(
						/* translators: %s: CoCart */
						esc_html__( 'Join the community on Discord to chat with us and fellow %s users. Share the store your building and your stack.', 'cocart-core' ),
						'CoCart'
					);
					?>
					</p>
					<a class="button cocart-button-alt" href="<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ) ); ?>" target="_blank" role="button"><?php esc_html_e( 'Join Community', 'cocart-core' ); ?></a>
				</div>
				<?php
				// Only show upgrade option if neither CoCart Plus, Pro or above is found.
				if ( apply_filters( 'cocart_show_upgrade_action_link', true ) ) {
					?>
				<div class="column wide has-background">
					<svg width="48" height="48" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" fill="none" viewBox="0 0 16 16" x="56" y="56" style="color: #fff" alignment-baseline="middle">
						<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h.01M11 14h.01"/><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.066 4.75H14.25l-1.566 5.088a2 2 0 0 1-1.911 1.412H6.55a2 2 0 0 1-1.99-1.79l-.623-5.92A2 2 0 0 0 1.95 1.75H1.75"/>
					</svg>
					<h3><?php esc_html_e( 'Ready to Upgrade?', 'cocart-core' ); ?></h3>
					<p><?php esc_html_e( 'Fully unlock the cart API for coupons, shipping, fees, rate limiting, improved batch request support and more.', 'cocart-core' ); ?></p>

					<?php
					// Get the timestamp for when CoCart was installed.
					$install_date = get_option( 'cocart_install_date', time() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

					// Check if a week has passed since install date.
					$time_elapsed = time() - $install_date; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$week_passed  = $time_elapsed > WEEK_IN_SECONDS; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

					$start_time = get_option( md5( 'cocart_upgrade_timer_start' ) ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					if ( $week_passed && ! $start_time ) {
						$start_time = time(); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
						update_option( md5( 'cocart_upgrade_timer_start' ), $start_time );
					}

					// Calculate time left only if a week has passed.
					$time_left = ! $week_passed ? -1 : ( $start_time ? max( 0, ( $start_time + 600 ) - time() ) : 600 ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound, 600 seconds = 10 minutes.
					?>

					<div id="countdown-timer" data-start="<?php echo esc_attr( $time_left ); ?>">
						<span id="minutes"></span>:<span id="seconds"></span> <?php echo esc_html_e( 'left to upgrade with a 20% discount!', 'cocart-core' ); ?>
					</div>

					<a class="button button-large cocart-button-alt" id="upgrade-button" href="<?php echo 0 === $time_left ? esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'pricing/' ) ) ) ) : esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'why-upgrade/' ) ) ) ); ?>" target="_blank" rel="noopener noreferrer" role="button">
					<?php
					echo 0 === $time_left ?
					esc_html__( 'View Pricing', 'cocart-core' ) :
					esc_html__( 'Upgrade Now', 'cocart-core' );
					?>
					</a>
					<script type="text/javascript">
						document.addEventListener('DOMContentLoaded', function() {
						const timerDiv = document.getElementById('countdown-timer');
						const upgradeButton = document.getElementById('upgrade-button');
						let timeLeft = parseInt(timerDiv.dataset.start);

						function updateTimer() {
							if (timeLeft <= 0) {
								timerDiv.innerHTML = 'Offer expired!';
								timerDiv.style.opacity = '0';
								setTimeout(() => {
									timerDiv.style.display = 'none';
								}, 500);
								upgradeButton.classList.add('expired');
								upgradeButton.textContent = '<?php esc_html_e( 'View Pricing', 'cocart-core' ); ?>';
								upgradeButton.href = '<?php echo esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'pricing/' ) ) ) ); ?>';
								return;
							}

							const minutes = Math.floor(timeLeft / 60);
							const seconds = timeLeft % 60;

							document.getElementById('minutes').textContent = minutes.toString().padStart(2, '0');
							document.getElementById('seconds').textContent = seconds.toString().padStart(2, '0');

							timeLeft--;
							setTimeout(updateTimer, 1000);
						}

						updateTimer();
					});
					</script>
				</div>
				<?php } ?>
			</div>
		</div>
	</li>
</ul>

<p class="tweet-share">
	<?php esc_html_e( 'Share your experience on X/Twitter', 'cocart-core' ); ?>: 
	<a href="https://twitter.com/intent/tweet?text=<?php echo esc_html( $tweets[ $tweet ] ); ?>" target="_blank" class="twitter-share-button" data-size="large" data-text="<?php echo esc_html( $tweets[ $tweet ] ); ?>" data-url="https://cocartapi.com/" data-hashtags="withcocart" data-related="CoCart API" data-show-count="false">Tweet</a><script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script><?php // phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
</p>
