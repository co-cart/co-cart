<?php
/**
 * Adds a help tab to any CoCart page providing useful
 * and helpful information for the users.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   3.10.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Help_Tab' ) ) {

	class CoCart_Admin_Help_Tab {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'add_help_tabs' ), 50 );
		} // END __construct()

		/**
		 * Adds help tabs to the plugin pages.
		 *
		 * @access public
		 */
		public function add_help_tabs() {
			if ( isset( $_GET['page'] ) && strpos( trim( sanitize_key( wp_unslash( $_GET['page'] ) ) ), 'cocart' ) === 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$screen = get_current_screen();

				$campaign_args = CoCart_Helpers::cocart_campaign(
					array(
						'utm_source'   => 'CoCartCore',
						'utm_medium'   => 'plugin-admin',
						'utm_campaign' => 'help-tab',
						'utm_content'  => 'help-tab',
					)
				);

				$screen->add_help_tab(
					array(
						'id'      => 'cocart_support_tab',
						'title'   => esc_html__( 'Help & Support', 'cart-rest-api-for-woocommerce' ),
						'content' =>
							'<h2>' . esc_html__( 'Help & Support', 'cart-rest-api-for-woocommerce' ) . '</h2>' .

							'<p>' . sprintf(
								/* translators: %s CoCart */
								__( 'We are fanatical about support, and want you to get the best out of our REST API with %s. If you run into any difficulties, there are several places you can find help:', 'cart-rest-api-for-woocommerce' ),
								'CoCart'
							) . '</p>' .

							'<ul>' .
							'<li>' . sprintf(
								/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing */
								__( '%1$sDocumentation%2$s. Our extensive documentation contains the API reference and some examples.', 'cart-rest-api-for-woocommerce' ),
								'<a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_DOCUMENTATION_URL ) ) ) ) . '" aria-label="' . esc_attr__( 'View API Reference', 'cart-rest-api-for-woocommerce' ) . '" title="' . esc_attr__( 'View API Reference', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">',
								'</a>'
							) . '</li>' .
							'<li>' . sprintf(
								/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing, %3$s: Discord, %4$s: CoCart */
								__( '%1$sDiscussions%2$s. We have an active and friendly community on our %3$s server who may be able to help you figure out the how-tos of %4$s.', 'cart-rest-api-for-woocommerce' ),
								'<a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL . 'community/' ) ) ) ) . '" aria-label="' . esc_attr__( 'View API Reference', 'cart-rest-api-for-woocommerce' ) . '" title="' . esc_attr__( 'View API Reference', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">',
								'</a>',
								'Discord',
								'CoCart'
							) . '</li>' .
							'<li>' . sprintf(
								/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing, %3$s: CoCart */
								__( '%1$sTranslate%2$s. %3$s is in need of translations. Is the plugin not translated in your language or do you spot errors with the current translations? Helping out is easy! Head over to the project on WordPress.org and click %1$sTranslate %3$s%2$s.', 'cart-rest-api-for-woocommerce' ),
								'<a href="' . esc_url( COCART_TRANSLATION_URL ) . '" aria-label="' . esc_html__( 'Help translate', 'cart-rest-api-for-woocommerce' ) . '" title="' . esc_html__( 'Help translate', 'cart-rest-api-for-woocommerce' ) . ' "target="_blank">',
								'</a>',
								'CoCart'
							) . '</li>' .
							'<li>' . sprintf(
								/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing */
								__( '%1$sCommunity Forum%2$s. If you prefer, you can request for help on the WordPress Forum.', 'cart-rest-api-for-woocommerce' ),
								'<a href="' . esc_url( COCART_SUPPORT_URL ) . '" aria-label="' . esc_html__( 'Community Forum', 'cart-rest-api-for-woocommerce' ) . '" title="' . esc_html__( 'Community Forum', 'cart-rest-api-for-woocommerce' ) . '" target="_blank">',
								'</a>'
							) . '</li>' .
							'<li>' . sprintf(
								/* translators: %1$s: Link to report issues, %2$s: Link to contribute instructions, %3$s: Hyperlink closing */
								__( '%2$sFound a bug?%4$s If you find a bug within %1$s core, please create a bug report on the %2$sGithub repository%4$s. Ensure you read the %3$scontribution guide%4$s prior to submitting your report. To help solve your issue as fast as possible, please be as descriptive as possible by filling in the template provided as requested.', 'cart-rest-api-for-woocommerce' ),
								'CoCart',
								'<a href="https://github.com/co-cart/co-cart/issues" target="_blank">',
								'<a href="https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md" target="_blank">',
								'</a>'
							) . '</li>' .
							'</ul>',
					)
				);

				// Show only if we are not on the Setup Wizard page.
				if ( strpos( trim( sanitize_key( wp_unslash( $_GET['page'] ) ) ), 'cocart-setup' ) !== 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$screen->add_help_tab(
						array(
							'id'      => 'cocart_wizard_tab',
							'title'   => esc_html__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ),
							'content' =>
								'<h2>' . esc_html__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ) . '</h2>' .

								'<p>' . esc_html__( 'Need to access the setup wizard again? Press on the button below.', 'cart-rest-api-for-woocommerce' ) . '</p>' .

								'<p><a href="' . add_query_arg(
									array(
										'page' => 'cocart-setup',
									),
									admin_url( 'admin.php' )
								) . '" class="button button-primary" aria-label="' . sprintf(
									/* translators: %s CoCart */
									esc_attr__( 'View %s setup wizard', 'cart-rest-api-for-woocommerce' ),
									'CoCart'
								) . '">' . esc_html__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ) . '</a></p>',
						)
					);
				}

				$screen->set_help_sidebar(
					'<p><strong>' . esc_html__( 'Information', 'cart-rest-api-for-woocommerce' ) . '</strong></p>' .

					'<p><span class="dashicons dashicons-admin-plugins"></span> ' . esc_html__( 'Version', 'cart-rest-api-for-woocommerce' ) . ' ' . COCART_VERSION . '</p>' .

					'<p><span class="dashicons dashicons-admin-home"></span> <a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_STORE_URL ) ) ) ) . '" target="_blank">' . esc_html__( 'Visit website', 'cart-rest-api-for-woocommerce' ) . '</a></p>' .

					'<p><span class="dashicons dashicons-wordpress"></span> <a href="' . esc_url( COCART_PLUGIN_URL ) . '" target="_blank">' . esc_html__( 'View details', 'cart-rest-api-for-woocommerce' ) . '</a></p>' .

					'<p><span class="dashicons dashicons-external"></span> <a href="https://github.com/co-cart/co-cart/" target="_blank">' . esc_html__( 'GitHub', 'cart-rest-api-for-woocommerce' ) . '</a></p>'
				);
			}
		} // END add_help_tabs()
	} // END class.

} // END if class exists.

return new CoCart_Admin_Help_Tab();
