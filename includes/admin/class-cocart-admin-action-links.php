<?php
/**
 * Adds links for CoCart on the plugins page.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin
 * @since    1.2.0
 * @version  2.3.0
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
					'getting-started' => '<a href="' . add_query_arg( array( 'page' => 'cocart', 'section' => 'getting-started' ), admin_url( 'admin.php' ) ) . '" aria-label="' . sprintf( esc_attr__( 'Getting Started with %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" style="color: #9b6cc6; font-weight: 600;">' . esc_attr__( 'Getting Started', 'cart-rest-api-for-woocommerce' ) . '</a>',
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
		 * @version 2.3.0
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
					'docs'      => '<a href="' . esc_url( COCART_DOCUMENTATION_URL ) . '" aria-label="' . sprintf( esc_attr__( 'View %s documentation', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Documentation', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'translate' => '<a href="' . esc_url( COCART_TRANSLATION_URL ) . '" aria-label="' . sprintf( esc_attr__( 'Translate %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Translate', 'cart-rest-api-for-woocommerce' ) . '</a>',
					'review'    => '<a href="' . esc_url( COCART_REVIEW_URL ) . '" aria-label="' . sprintf( esc_attr__( 'Review %s on WordPress.org', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank">' . esc_attr__( 'Leave a Review', 'cart-rest-api-for-woocommerce' ) . '</a>',
				);

				// Only show donate option if CoCart Pro is not installed.
				if ( ! CoCart_Helpers::is_cocart_pro_installed() ) {
					$donate = array(
						'donate'   => '<a href="' . esc_url( 'https://www.buymeacoffee.com/sebastien' ) . '" aria-label="' . sprintf( esc_attr__( 'Make a donation for %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank" style="color: #399141; font-weight: 600;">' . esc_attr__( 'Donate', 'cart-rest-api-for-woocommerce' ) . '</a>',
						'priority' => '<a href="' . esc_url( 'https://cocart.xyz/product/14-day-priority-support/' ) . '" aria-label="' . sprintf( esc_attr__( 'Order priority support for %s', 'cart-rest-api-for-woocommerce' ), 'CoCart' ) . '" target="_blank" style="color: #9b6cc6; font-weight: 600;">' . esc_attr__( 'Priority Support', 'cart-rest-api-for-woocommerce' ) . '</a>'
					);

					$row_meta = array_merge( $donate, $row_meta );
				}

				// Only show upgrade option if CoCart Pro is not installed.
				if ( ! CoCart_Helpers::is_cocart_pro_installed() ) {
					$store_url = add_query_arg( $campaign_args, COCART_STORE_URL . 'pro/' );

					$row_meta['upgrade'] = sprintf( '<a href="%1$s" aria-label="' . sprintf( esc_attr__( 'Upgrade to %s', 'cart-rest-api-for-woocommerce' ), 'CoCart Pro' ) . '" target="_blank" style="color: #c00; font-weight: 600;">%2$s</a>', esc_url( $store_url ), esc_attr__( 'Upgrade to Pro', 'cart-rest-api-for-woocommerce' ) );
				}

				$metadata = array_merge( $metadata, $row_meta );
			}

			return $metadata;
		} // END plugin_row_meta()

	} // END class

} // END if class exists

return new CoCart_Admin_Action_Links();
