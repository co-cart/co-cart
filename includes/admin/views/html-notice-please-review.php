<?php
/**
 * Admin View: Plugin Review Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   1.2.0
 * @version 3.0.7
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$current_user = wp_get_current_user(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

$time = CoCart_Helpers::cocart_seconds_to_words( time() - self::$install_date );
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3>
				<?php
				printf(
					/* translators: 1: Display name of current user. 2: CoCart */
					esc_html__( 'Hi %1$s, are you enjoying %2$s?', 'cart-rest-api-for-woocommerce' ),
					esc_html( $current_user->display_name ),
					'CoCart'
				);
				?>
			</h3>
			<p>
				<?php
				printf(
					/* translators: 1: CoCart 2: Time since installed. */
					esc_html__( 'You have been using %1$s for %2$s now! Mind leaving a review and let me know know what you think of the plugin? I\'d really appreciate it!', 'cart-rest-api-for-woocommerce' ), 'CoCart', esc_html( $time )
				);
				?>
			</p>
		</div>

		<div class="cocart-action">
			<?php printf( '<a href="%1$s" class="button button-primary cocart-button" aria-label="' . esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">%2$s</a>', esc_url( COCART_REVIEW_URL . '?rate=5#new-post' ), esc_html__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) ); ?>
			<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'plugin_review', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>" class="no-thanks" aria-label="<?php echo esc_html__( 'Hide this notice forever.', 'cart-rest-api-for-woocommerce' ); ?>"><?php echo esc_html__( 'No thank you / I already have', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
