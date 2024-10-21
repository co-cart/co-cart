<?php
/**
 * Replaces the admin footer text.
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

if ( ! class_exists( 'CoCart_Admin_Footer' ) ) {

	class CoCart_Admin_Footer {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), 15, 1 );
			add_filter( 'update_footer', array( $this, 'update_footer' ), 15 );
		} // END __construct()

		/**
		 * Filters the admin footer text by placing a simple thank you to those who
		 * like CoCart and review the plugin when viewing any CoCart admin page.
		 *
		 * @access public
		 *
		 * @param string $text Original footer text.
		 *
		 * @return string $text Filtered footer text.
		 */
		public function admin_footer_text( $text ) {
			if ( isset( $_GET['page'] ) && strpos( trim( sanitize_key( wp_unslash( $_GET['page'] ) ) ), 'cocart' ) === 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$text = sprintf(
					wp_kses(
						/* translators: $1$s - CoCart; $2$s - testimonial link. */
						__( 'We’d love to hear what you have to say. <a href="%1$s" target="_blank" rel="noopener noreferrer">Share your thoughts</a> and help others discover %2$s. Thank you!', 'cart-rest-api-for-woocommerce' ),
						array(
							'a' => array(
								'href'   => array(),
								'target' => array(),
								'rel'    => array(),
							),
							'p' => array(
								'class' => array(),
							),
						)
					),
					COCART_REVIEW_URL,
					'CoCart'
				);
			}

			return $text;
		} // END admin_footer_text()

		/**
		 * Filters the update footer by placing the version of the plugin
		 * when viewing CoCart settings page.
		 *
		 * @access public
		 *
		 * @param string $text WordPress version.
		 *
		 * @return string $text CoCart Version.
		 */
		public function update_footer( $text ) {
			if ( isset( $_GET['page'] ) && strpos( trim( sanitize_key( wp_unslash( $_GET['page'] ) ) ), 'cocart' ) === 0 ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				$campaign_args = CoCart_Helpers::cocart_campaign(
					array(
						'utm_source'   => 'CoCartCore',
						'utm_medium'   => 'plugin-admin',
						'utm_campaign' => 'footer',
						'utm_content'  => 'footer',
					)
				);

				$docs = sprintf(
					/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing */
					__( '%1$sDocumentation%2$s', 'cart-rest-api-for-woocommerce' ),
					'<a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_DOCUMENTATION_URL ) ) ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);

				$community = sprintf(
					/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing */
					__( '%1$sCommunity%2$s', 'cart-rest-api-for-woocommerce' ),
					'<a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( COCART_COMMUNITY_URL ) ) ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);

				$changelog = sprintf(
					/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing */
					__( '%1$sChangelog%2$s', 'cart-rest-api-for-woocommerce' ),
					'<a href="' . esc_url( CoCart_Helpers::build_shortlink( add_query_arg( $campaign_args, esc_url( 'https://cocart.dev/changelog/' ) ) ) ) . '" target="_blank" rel="noopener noreferrer">',
					'</a>'
				);

				/* translators: %s: CoCart */
				$version = sprintf( __( '%s Version', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . ' ' . esc_attr( COCART_VERSION );

				return $docs . ' | ' . $community . ' | ' . $changelog . ' | ' . $version;
			}

			return $text;
		} // END update_footer()
	} // END class

} // END if class exists

return new CoCart_Admin_Footer();
