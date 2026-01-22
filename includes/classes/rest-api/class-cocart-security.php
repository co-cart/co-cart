<?php
/**
 * REST API: CoCart_Security
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.7.10 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Security
 *
 * Responsible for added protection.
 *
 * @since 3.7.10 Introduced.
 */
class CoCart_Security {

	/**
	 * Setup class.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		// Hide CoCart from WordPress REST API Index.
		add_filter( 'rest_index', array( $this, 'hide_from_rest_index' ) );

		// Hides CoCart named routes from index.
		add_filter( 'rest_namespace_index', array( $this, 'hide_routes_from_index' ), 0, 2 );
	} // END __construct()

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

			if ( isset( $response->data['routes'] ) ) {
				// Loop through each registered route.
				foreach ( $response->data['routes'] as $route => $endpoints ) {
					// Check if the current namespace matches any CoCart namespace.
					if ( ! empty( $route ) && strpos( $route, 'cocart' ) !== false ) {
						unset( $response->data['routes'][ $route ] );
					}
				}
			}

			if ( isset( $response->data['namespaces'] ) ) {
				// Loop through each registered namespace.
				foreach ( $response->data['namespaces'] as $key => $namespace ) {
					// Check if the current namespace matches any CoCart namespace.
					if ( ! empty( $namespace ) && strpos( $namespace, 'cocart' ) !== false ) {
						unset( $response->data['namespaces'][ $key ] );
					}
				}
			}
		}

		return $response;
	} // END hide_from_rest_index()

	/**
	 * This prevents the index of CoCart to expose all routes available.
	 *
	 * Returns an error message to confuse outsides.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param WP_REST_Response $response Response data.
	 * @param WP_REST_Request  $request  The request object.
	 *
	 * @return WP_Error
	 */
	public function hide_routes_from_index( $response, $request ) {
		$namespace = $request['namespace'];

		if ( preg_match( '/^' . CoCart::get_api_namespace() . '$/', $namespace ) ) {
			return new \WP_Error(
				'rest_invalid_namespace',
				__( 'The specified namespace could not be found.', 'cocart-core' ),
				array( 'status' => 404 )
			);
		}
	} // END hide_routes_from_index()

	/**
	 * Removes meta data that a plugin should NOT be outputting with Products API.
	 *
	 * @access public
	 *
	 * @since 4.3.9 Introduced.
	 *
	 * @deprecated 5.0.0
	 *
	 * @hooked: cocart_products_ignore_private_meta_keys - 1
	 *
	 * @param array      $ignored_meta_keys Ignored meta keys.
	 * @param WC_Product $product           The product object.
	 *
	 * @return array $ignored_meta_keys Ignored meta keys.
	 */
	public function remove_exposed_product_meta( $ignored_meta_keys, $product ) {
		$meta_data = $product->get_meta_data();

		foreach ( $meta_data as $meta ) {
			if ( 'wcwl_mailout_errors' == $meta->key ) {
				$ignored_meta_keys[] = $meta->key;
			}
		}

		return $ignored_meta_keys;
	} // END remove_exposed_product_meta()
} // END class

return new CoCart_Security();
