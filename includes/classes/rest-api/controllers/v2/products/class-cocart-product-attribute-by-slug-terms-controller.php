<?php
/**
 * CoCart - Product Attribute Terms by Slug controller
 *
 * Handles requests to the products/attributes/{attribute_slug}/terms endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Attribute Terms by Slug controller class.
 *
 * Provides slug-based access to terms of a product attribute.
 * The attribute_slug is the clean form without the 'pa_' prefix (e.g. 'color', not 'pa_color').
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Product_Attribute_Terms_V2_Controller
 */
class CoCart_REST_Product_Attribute_Terms_By_Slug_V2_Controller extends CoCart_REST_Product_Attribute_Terms_V2_Controller {

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		return '/products/attributes/(?P<attribute_slug>[\w-]+)/terms';
	} // END get_path_regex()

	/**
	 * Resolve the taxonomy name from the attribute slug in the request.
	 *
	 * Accepts both clean slugs ('color') and full taxonomy names ('pa_color').
	 *
	 * @access protected
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return string Taxonomy name (e.g. 'pa_color'), or empty string if not found.
	 */
	protected function get_taxonomy( $request ) {
		if ( '' !== $this->taxonomy ) {
			return $this->taxonomy;
		}

		if ( ! empty( $request['attribute_slug'] ) ) {
			$id = wc_attribute_taxonomy_id_by_name( sanitize_title( $request['attribute_slug'] ) );
			if ( $id ) {
				$this->taxonomy = wc_attribute_taxonomy_name_by_id( $id );
			}
		}

		return $this->taxonomy;
	} // END get_taxonomy()
} // END class
