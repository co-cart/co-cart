<?php
/**
 * Adds links for CoCart on the plugins page.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   1.2.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
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

			add_filter( 'plugin_action_links_' . plugin_basename( 'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php' ), array( $this, 'disable_action_links' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		} // END __construct()

		/**
		 * Disable action links for CoCart core legacy version.
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param array $links An array of plugin links.
		 *
		 * @return array $links An array of plugin links.
		 */
		public function disable_action_links( $links ) {
			unset( $links['activate'] );

			return $links;
		} // END disable_action_links()

		/**
		 * Plugin action links.
		 *
		 * @access public
		 *
		 * @since   2.0.0 Introduced.
		 * @version 4.5.0
		 *
		 * @param array $links An array of plugin links.
		 *
		 * @return array $links
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
				) . '" title="' . esc_attr__( 'Setup Wizard', 'cocart-core' ) . '">' . esc_attr__( 'Setup Wizard', 'cocart-core' ) . '</a>';
			}

			$action_links['support'] = '<a href="' . add_query_arg(
				array(
					'page' => 'cocart-support',
				),
				$page
			) . '" title="' . sprintf(
				/* translators: %s: CoCart */
				esc_attr__( 'Support for %s', 'cocart-core' ),
				'CoCart'
			) . '">' . esc_attr__( 'Support', 'cocart-core' ) . '</a>';

			// Only show upgrade option if neither CoCart Plus, Pro or above is found.
			if ( apply_filters( 'cocart_show_upgrade_action_link', true ) ) {
				$store_url = CoCart_Helpers::build_shortlink( add_query_arg( $this->campaign_args, COCART_STORE_URL . 'why-upgrade/' ) );

				$action_links['upgrade'] = sprintf(
					'<a href="%1$s" title="%2$s" target="_blank" rel="noopener noreferrer" style="color: #6032b0; font-weight: 600;">%2$s</a>',
					esc_url( $store_url ),
					sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Upgrade %s', 'cocart-core' ),
						'CoCart'
					)
				);
			}

			$links = array_merge( $links, $action_links );

			return $links;
		} // END plugin_action_links()

		/**
		 * Plugin row meta links
		 *
		 * @access public
		 *
		 * @since   2.0.0 Introduced.
		 * @version 4.5.0
		 *
		 * @param array  $metadata An array of the plugin's metadata.
		 * @param string $file     Path to the plugin file.
		 *
		 * @return array $metadata An array of the plugin's metadata.
		 */
		public function plugin_row_meta( $metadata, $file ) {
			if ( version_compare( get_option( 'cocart_version' ), COCART_VERSION, '<' ) ) {
				return $metadata;
			}

			if ( plugin_basename( COCART_FILE ) === $file ) {
				$row_meta = array(
					'community' => '<a href="' . esc_url( COCART_COMMUNITY_URL ) . '" title="' . sprintf(
						/* translators: %1$s: CoCart, %2$s :Discord */
						esc_attr__( 'Join %1$s Community on %2$s', 'cocart-core' ),
						'CoCart',
						'Discord'
					) . '" target="_blank" rel="noopener noreferrer">' . esc_attr__( 'Join Community', 'cocart-core' ) . '</a>',
					'docs'      => '<a href="' . esc_url( COCART_DOCUMENTATION_URL ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'View %s Documentation', 'cocart-core' ),
						'CoCart'
					) . '" target="_blank" rel="noopener noreferrer">' . esc_attr__( 'Documentation', 'cocart-core' ) . '</a>',
					'translate' => '<a href="' . CoCart_Helpers::build_shortlink( add_query_arg( $this->campaign_args, esc_url( COCART_TRANSLATION_URL ) ) ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Translate %s', 'cocart-core' ),
						'CoCart'
					) . '" target="_blank" rel="noopener noreferrer">' . esc_attr__( 'Translate', 'cocart-core' ) . '</a>',
					'review'    => '<a href="' . esc_url( COCART_REVIEW_URL ) . '" title="' . sprintf(
						/* translators: %s: CoCart */
						esc_attr__( 'Submit a review for %s', 'cocart-core' ),
						'CoCart'
					) . '" target="_blank" rel="noopener noreferrer">' . esc_attr__( 'Leave a Review', 'cocart-core' ) . '</a>',
				);

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()
	} // END class

} // END if class exists

return new CoCart_Admin_Action_Links();
