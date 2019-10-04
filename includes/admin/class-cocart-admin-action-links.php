<?php
/**
 * CoCart - Admin Action Links.
 *
 * Adds links to CoCart on the plugins page.
 *
 * @since    1.2.0
 * @version  2.0.6
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Action_Links' ) ) {

	class CoCart_Admin_Action_Links {

		/**
		 * Constructor
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'plugin_action_links_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_action_links' ) );
			add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta'), 10, 3 );
		} // END __construct()

		/**
		 * Plugin action links.
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.0.1
		 * @param   array $links An array of plugin links.
		 * @return  array $links
		 */
		public function plugin_action_links( $links ) {
			if ( current_user_can( 'manage_options' ) ) {
				$action_links = array(
					'getting-started' => '<a href="' . add_query_arg( array( 'page' => 'cocart', 'section' => 'getting-started' ), admin_url( 'admin.php' ) ) . '" aria-label="' . sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '">' . esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				return array_merge( $action_links, $links );
			}

			return $links;
		} // END plugin_action_links()

		/**
		 * Plugin row meta links
		 *
		 * @access  public
		 * @since   2.0.0
		 * @version 2.0.4
		 * @param   array  $metadata An array of the plugin's metadata.
		 * @param   string $file     Path to the plugin file.
		 * @param   array  $data     Plugin Information
		 * @return  array  $metadata
		 */
		public function plugin_row_meta( $metadata, $file, $data ) {
			if ( $file == plugin_basename( COCART_FILE ) ) {
				$metadata[ 1 ] = sprintf( __( 'Developed By %s', 'cart-rest-api-for-woocommerce' ), '<a href="' . $data[ 'AuthorURI' ] . '" aria-label="' . esc_attr__( 'View the developers site', 'cart-rest-api-for-woocommerce' ) . '">' . $data[ 'Author' ] . '</a>' );

				$campaign_args = array(
					'utm_medium'   => 'co-cart-lite',
					'utm_source'   => 'plugins-page',
					'utm_campaign' => 'plugins-row',
					'utm_content'  => 'go-pro',
				);

				$row_meta = array(
					'docs' => '<a href="' . apply_filters( 'cocart_docs_url', esc_url( COCART_DOCUMENTATION_URL ) ) . '" aria-label="' . sprintf( esc_attr__( 'View %s documentation', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'translate' => '<a href="' . esc_url( COCART_TRANSLATION_URL ) . '" aria-label="' . sprintf( esc_attr__( 'Translate %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Translate', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'community' => '<a href="' . esc_url( COCART_SUPPORT_URL ) . '" aria-label="' . esc_attr__( 'Get support from the community', 'cart-rest-api-for-woocommerce' ). '" target="_blank">' . esc_attr__( 'Community Support', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'review' => '<a href="' . esc_url( COCART_REVIEW_URL ) . '" aria-label="' . sprintf( esc_attr__( 'Review %s on WordPress.org', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				// Checks if CoCart Pro has been installed.
				if ( ! CoCart_Admin::is_cocart_pro_installed() ) {
					$store_url = add_query_arg( $campaign_args, COCART_STORE_URL . 'pricing-lite/' );

					$row_meta['upgrade'] = sprintf( '<a href="%1$s" aria-label="' . sprintf( esc_attr__( 'Upgrade to %s', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ) . '" target="_blank" style="color: #c00; font-weight: 700;">%2$s</a>', esc_url( $store_url ), esc_attr__( 'Upgrade to Pro', 'cart-rest-api-for-woocommerce' ) );
				}

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return new CoCart_Admin_Action_Links();
