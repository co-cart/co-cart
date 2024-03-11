<?php
/**
 * CoCart Security
 *
 * Responsible for added protection.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.7.10 Introduced.
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CoCart_Security {

	/**
	 * Setup class.
	 *
	 * @access public
	 */
	public function __construct() {
		add_filter( 'rest_index', array( $this, 'hide_from_rest_index' ) );
	}

	/**
	 * Hide any CoCart namespace and routes from showing in the WordPress REST API Index.
	 *
	 * @access public
	 *
	 * @param WP_REST_Response $response Response data.
	 *
	 * @return object $response Altered response.
	 */
	public function hide_from_rest_index( $response ) {
		// Check if WP_DEBUG is not defined or is false.
		if ( ! defined( 'WP_DEBUG' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG !== true ) ) {

			// Loop through each registered route.
			foreach ( $response->data['routes'] as $route => $endpoints ) {
				// Check if the current namespace matches any CoCart namespace.
				if ( ! empty( $route ) && strpos( $route, 'cocart' ) !== false ) {
					unset( $response->data['routes'][ $route ] );
				}
			}

			// Loop through each registered namespace.
			foreach ( $response->data['namespaces'] as $key => $namespace ) {
				// Check if the current namespace matches any CoCart namespace.
				if ( ! empty( $namespace ) && strpos( $namespace, 'cocart' ) !== false ) {
					unset( $response->data['namespaces'][ $key ] );
				}
			}
		}

		return $response;
	} // END hide_from_rest_index()
} // END class

return new CoCart_Security();
