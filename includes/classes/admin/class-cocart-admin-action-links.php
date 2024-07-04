<?php
/**
 * Adds links for CoCart on the plugins page.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0
 * @version 3.10.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Action_Links' ) ) {

	class CoCart_Admin_Action_Links {

		/**
		 * Stores the campaign arguments.
		 *
		 * @access public
		 *
		 * @var array
		 */
		public $campaign_args = array();

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			$this->campaign_args['utm_source']  = 'CoCartCore';
			$this->campaign_args['utm_medium']  = 'plugin-admin';
			$this->campaign_args['utm_content'] = 'action-links';

			add_filter( 'plugin_action_links_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 3 );
		} // END __construct()

		/**
		 * Plugin action links.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.10.0
		 * @param   array $links An array of plugin links.
		 * @return  array $links
		 */
		public function plugin_action_links( $links ) {
			if ( version_compare( get_option( 'cocart_version' ), COCART_VERSION, '<' ) ) {
				return $links;
			}

			$page = admin_url( 'admin.php' );

			if ( apply_filters( 'cocart_enable_setup_wizard', true ) && current_user_can( 'manage_options' ) ) {
				$action_links['setup-wizard'] = '<a href="' . add_query_arg(
					array(
						'page' => 'cocart-setup',
					),
					$page
				) . '" aria-label="' . esc_attr__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ) . '" title="' . esc_attr__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ) . '">' . esc_attr__( 'Setup Wizard', 'cart-rest-api-for-woocommerce' ) . '</a>';
			}

			$action_links['support'] = '<a href="' . add_query_arg(
				array(
					'page' => 'cocart-support',
				),
				$page
			) . '" aria-label="' . sprintf(
				/* translators: %s: CoCart */
				esc_attr__( 'Support for %s', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			) . '" title="' . sprintf(
				/* translators: %s: CoCart */
				esc_attr__( 'Support for %s', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			) . '">' . esc_attr__( 'Support', 'cart-rest-api-for-woocommerce' ) . '</a>';

			// Only show upgrade option if neither CoCart Plus, Pro or above is found.
			if ( apply_filters( 'cocart_show_upgrade_action_link', true ) ) {
				$store_url = CoCart_Helpers::build_shortlink( add_query_arg( $this->campaign_args, COCART_STORE_URL . 'pricing/' ) );

				$action_links['upgrade'] = sprintf(
					'<a href="%1$s" aria-label="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Upgrade %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" target="_blank" style="color: #6032b0; font-weight: 600;">%2$s</a>',
					esc_url( $store_url ),
					sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Upgrade %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					)
				);
			}

			$links = array_merge( $action_links, $links );

			return $links;
		} // END plugin_action_links()

		/**
		 * Plugin row meta links
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 3.10.0
		 * @param   array  $metadata An array of the plugin's metadata.
		 * @param   string $file     Path to the plugin file.
		 * @return  array  $metadata
		 */
		public function plugin_row_meta( $metadata, $file ) {
			if ( version_compare( get_option( 'cocart_version' ), COCART_VERSION, '<' ) ) {
				return $metadata;
			}

			if ( plugin_basename( COCART_FILE ) === $file ) {
				$row_meta = array(
					'community' => '<a href="' . esc_url( COCART_COMMUNITY_URL ) . '" title="' . sprintf(
						/* translators: %1$s: CoCart, %2$s :Discord */
						esc_attr__( 'Join %1$s Community on %2$s', 'cart-rest-api-for-woocommerce' ),
						'CoCart',
						'Discord'
					) . '" aria-label="' . sprintf(
						/* translators: %1$s: CoCart, %2$s :Discord */
						esc_attr__( 'Join %1$s Community on %2$s', 'cart-rest-api-for-woocommerce' ),
						'CoCart',
						'Discord'
					) . '" target="_blank">' . esc_attr__( 'Join Community', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'docs'      => '<a href="' . CoCart_Helpers::build_shortlink( add_query_arg( $this->campaign_args, esc_url( COCART_DOCUMENTATION_URL ) ) ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'View %s Documentation', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" aria-label="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'View %s Documentation', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" target="_blank">' . esc_attr__( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'translate' => '<a href="' . CoCart_Helpers::build_shortlink( add_query_arg( $this->campaign_args, esc_url( COCART_TRANSLATION_URL ) ) ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Translate %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" aria-label="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Translate %s', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" target="_blank">' . esc_attr__( 'Translate', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'review'    => '<a href="' . esc_url( COCART_REVIEW_URL ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Review %s on WordPress.org', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" aria-label="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Review %s on WordPress.org', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					) . '" target="_blank">' . esc_attr__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()
	} // END class

} // END if class exists

return new CoCart_Admin_Action_Links();
