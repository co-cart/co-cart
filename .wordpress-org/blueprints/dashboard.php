<?php
/**
 * Plugin Name: CoCart Playground
 * Description: Playground support for CoCart.
 * Author: CoCart Headless, LLC
 * Author URI: https://cocartapi.com
 * Version: 0.0.1
 * Text Domain: cocart-playground
 * Requires at least: 6.3
 * Tested up to: 6.8
 * Requires PHP: 7.4
 *
 * Copyright:   CoCart Headless, LLC
 * License:     GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package CoCart Playground
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays a welcome message on the dashboard for the WP Playground.
 */
function cocart_pg_welcome() {
	$screen    = get_current_screen();
	$screen_id = $screen ? $screen->id : '';

	if ( ! in_array( $screen_id, array( 'dashboard' ) ) ) {
		return;
	}
	?>
	<div id="welcome-panel" class="welcome-panel" style="background-color: #6032b0; width: 99%;">
		<div class="welcome-panel-content">
			<div class="welcome-panel-header">
				<h2>
					<svg width="48" height="48" xmlns="http://www.w3.org/2000/svg" fill="none" fillrule="evenodd" cliprule="evenodd" viewBox="0 0 16 16" aria-hidden="true" focusable="false" x="56" y="56" alignment-baseline="middle" style="position: relative; top: 8px;">
						<path stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 14h.01M11 14h.01"/>
						<path stroke="#FFFFFF" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.066 4.75H14.25l-1.566 5.088a2 2 0 0 1-1.911 1.412H6.55a2 2 0 0 1-1.99-1.79l-.623-5.92A2 2 0 0 0 1.95 1.75H1.75"/>
					</svg> <?php esc_html_e( 'Welcome to your CoCart Playground!', 'cocart-playground' ); ?></h2>
				<p>
					<a href="<?php echo esc_url( 'https://cocartapi.com/?ref=wpplayground' ); ?>" target="_blank">
						<?php esc_html_e( 'Learn more about CoCart.', 'cocart-playground' ); ?>
					</a>
				</p>
			</div>
			<div class="welcome-panel-column-container">
				<div class="welcome-panel-column">
					<svg width="48" height="48" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="#1E1E1E" aria-hidden="true" focusable="false">
						<path fill-rule="evenodd" clip-rule="evenodd" d="M73 39c-14.8-9.1-33.4-9.4-48.5-.9S0 62.6 0 80L0 432c0 17.4 9.4 33.4 24.5 41.9s33.7 8.1 48.5-.9L361 297c14.3-8.7 23-24.2 23-41s-8.7-32.2-23-41L73 39z"/>
					</svg>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'Base URL', 'cocart-playground' ); ?></h3>
						<p><?php esc_html_e( 'The base URL is where your WordPress is installed. All requests made to the CoCart API is via your WordPress site url.', 'cocart-playground' ); ?></p>
						<p><?php esc_html_e( 'Use the base URL below for this playground.', 'cocart-playground' ); ?></p>
						<code><?php echo get_rest_url(); ?></code>
					</div>
				</div>
				<div class="welcome-panel-column">
					<svg width="48" height="48" viewBox="0 0 19 18" fill="#1E1E1E" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
						<rect width="48" height="48" fill="none" transform="translate(0.5)"/>
						<path fill-rule="evenodd" clip-rule="evenodd" d="M13.75 2H5.25C3.733 2 2.5 3.233 2.5 4.75V13.25C2.5 14.767 3.733 16 5.25 16H13.75C15.267 16 16.5 14.767 16.5 13.25V4.75C16.5 3.233 15.267 2 13.75 2ZM6.78 12.78C6.634 12.926 6.442 13 6.25 13C6.058 13 5.866 12.927 5.72 12.78C5.427 12.487 5.427 12.012 5.72 11.719L7.69 9.749L5.72 7.779C5.427 7.486 5.427 7.011 5.72 6.718C6.013 6.425 6.488 6.425 6.781 6.718L9.281 9.218C9.574 9.511 9.574 9.986 9.281 10.279L6.781 12.779L6.78 12.78ZM12.75 13H10.25C9.836 13 9.5 12.664 9.5 12.25C9.5 11.836 9.836 11.5 10.25 11.5H12.75C13.164 11.5 13.5 11.836 13.5 12.25C13.5 12.664 13.164 13 12.75 13Z" fill="#1E1E1E"/>
					</svg>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'API Reference', 'cocart-playground' ); ?></h3>
						<p><?php esc_html_e( 'If you’re new to CoCart API, recommend checking out the API reference to get started.', 'cocart-playground' ); ?></p>
						<a href="<?php echo esc_url( 'https://cocartapi.com/docs/?ref=wpplayground' ); ?>" target="_blank"><?php esc_html_e( 'Learn about the API', 'cocart-playground' ); ?></a>
					</div>
				</div>
				<div class="welcome-panel-column">
					<svg width="48" height="48" id="Discord-Logo" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" viewBox="0 0 126.644 96">
						<defs><style>.cls-1{fill:#5865f2;}</style></defs>
						<path id="Discord-Symbol-Blurple" class="cls-1" d="M81.15,0c-1.2376,2.1973-2.3489,4.4704-3.3591,6.794-9.5975-1.4396-19.3718-1.4396-28.9945,0-.985-2.3236-2.1216-4.5967-3.3591-6.794-9.0166,1.5407-17.8059,4.2431-26.1405,8.0568C2.779,32.5304-1.6914,56.3725.5312,79.8863c9.6732,7.1476,20.5083,12.603,32.0505,16.0884,2.6014-3.4854,4.8998-7.1981,6.8698-11.0623-3.738-1.3891-7.3497-3.1318-10.8098-5.1523.9092-.6567,1.7932-1.3386,2.6519-1.9953,20.281,9.547,43.7696,9.547,64.0758,0,.8587.7072,1.7427,1.3891,2.6519,1.9953-3.4601,2.0457-7.0718,3.7632-10.835,5.1776,1.97,3.8642,4.2683,7.5769,6.8698,11.0623,11.5419-3.4854,22.3769-8.9156,32.0509-16.0631,2.626-27.2771-4.496-50.9172-18.817-71.8548C98.9811,4.2684,90.1918,1.5659,81.1752.0505l-.0252-.0505ZM42.2802,65.4144c-6.2383,0-11.4159-5.6575-11.4159-12.6535s4.9755-12.6788,11.3907-12.6788,11.5169,5.708,11.4159,12.6788c-.101,6.9708-5.026,12.6535-11.3907,12.6535ZM84.3576,65.4144c-6.2637,0-11.3907-5.6575-11.3907-12.6535s4.9755-12.6788,11.3907-12.6788,11.4917,5.708,11.3906,12.6788c-.101,6.9708-5.026,12.6535-11.3906,12.6535Z"/>
					</svg>
					<div class="welcome-panel-column-content">
						<h3><?php esc_html_e( 'Community Support', 'cocart-playground' ); ?></h3>
						<p><?php esc_html_e( 'Join the community on Discord to chat with us and fellow CoCart users.', 'cocart-playground' ); ?></p>
						<a href="<?php echo esc_url( 'https://cocartapi.com/community/?ref=wpplayground' ); ?>" target="_blank"><?php esc_html_e( 'Join Community', 'cocart-playground' ); ?></a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', 'cocart_pg_welcome' );

// Disable the setup wizard.
// This is to prevent the setup wizard from being displayed in the WP Playground.
add_filter( 'cocart_enable_setup_wizard', '__return_false' );
