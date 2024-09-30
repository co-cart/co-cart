<?php
/**
 * Admin View: Plugin Review Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   1.2.0
 * @version 4.2.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
?>
<div class="notice cocart-notice">
	<div class="cocart-notice-inner cocart-step cocart-review-step-1">
		<div class="cocart-notice-content">
			<p>
				<?php
				printf(
					/* translators: 1: Display name of current user. 2: CoCart */
					esc_html__( 'Hi %1$s, are you enjoying %2$s so far?', 'cart-rest-api-for-woocommerce' ),
					esc_html( $current_user->display_name ),
					'CoCart'
				);
				?>
			</p>
		</div>

		<div class="cocart-action review-actions">
			<button class="button-primary cocart-review-switch-step" data-step="3" aria-label="<?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Yes', 'cart-rest-api-for-woocommerce' ); ?></button>
			<button class="button cocart-review-switch-step" data-step="2" aria-label="<?php echo esc_html__( 'Not Really', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'Not Really', 'cart-rest-api-for-woocommerce' ); ?></button>
		</div>
	</div>

	<div class="cocart-notice-inner cocart-step cocart-review-step-2" style="display:none;">
		<div class="cocart-notice-content">
			<p>
				<?php
				printf(
					/* translators: %s: CoCart */
					esc_html__( 'We\'re sorry to hear you aren\'t enjoying %s. We would love a chance to improve. Could you take a minute and let us know what we can do better?', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</p>
		</div>

		<div class="cocart-action review-actions">
			<a href="<?php echo esc_url( 'https://cocartapi.com/suggest-a-feature/' ); ?>" class="button button-primary cocart-notice-dismiss" target="_blank"><?php esc_html_e( 'Give Feedback', 'cart-rest-api-for-woocommerce' ); ?></a>
			<button class="button cocart-notice-dismiss"><?php esc_html_e( 'No thanks', 'cart-rest-api-for-woocommerce' ); ?></button>
		</div>
	</div>

	<div class="cocart-notice-inner cocart-step cocart-review-step-3" style="display:none;">
		<div class="cocart-notice-content">
			<p><?php esc_html_e( 'That\'s awesome! Could you please do me a BIG favor and give it a 5-star rating on WordPress to help us spread the word and boost our motivation?', 'cart-rest-api-for-woocommerce' ); ?></p>
			<p>
				<strong>
					<?php
					printf(
						wp_kses(
							/* translators: 1: Founders name, 2: Company name */
							__( '%1$s<br>Founder of %2$s', 'cart-rest-api-for-woocommerce' ),
							array( 'br' => array() )
						),
						'Sébastien Dumont',
						'CoCart Headless, LLC'
					);
					?>
				</strong>
			</p>
		</div>

		<div class="cocart-action review-actions">
			<a href="https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/?filter=5#new-post" class="button button-primary cocart-notice-dismiss" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Ok, you deserve it!', 'cart-rest-api-for-woocommerce' ); ?></a>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'plugin_review', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'No thank you', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
<script type="text/javascript">
	document.addEventListener( 'DOMContentLoaded', function() {
		var steps = document.querySelectorAll( '.cocart-review-switch-step' );
		steps.forEach( function(step) {
			step.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				var target = this.getAttribute( 'data-step' );
				if ( target ) {
					var notice = this.closest( '.cocart-notice' );
					var review_step = notice.querySelector( '.cocart-review-step-' + target );
					if ( review_step ) {
						var thisStep = this.closest( '.cocart-step' );
						eddFadeOut( thisStep );
						eddFadeIn( review_step );
					}
				}
			} )
		} )

		function eddFadeIn( element ) {
			var op = 0;
			element.style.opacity = op;
			element.style.display = 'table';
			var timer = setInterval( function () {
				if ( op >= 1 ) {
					clearInterval( timer );
				}
				element.style.opacity = op;
				element.style.filter = 'alpha(opacity=' + op * 100 + ')';
				op = op + 0.1;
			}, 80 );
		}

		function eddFadeOut( element ) {
			var op = 1;
			var timer = setInterval( function () {
				if ( op <= 0 ) {
					element.style.display = 'none';
					clearInterval( timer );
				}
				element.style.opacity = op;
				element.style.filter = 'alpha(opacity=' + op * 100 + ')';
				op = op - 0.1;
			}, 80 );
		}
	} );
</script>
