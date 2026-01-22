<?php
/**
 * REST API: CoCart_REST_Product_Brands_V2_Controller class.
 *
 * Handles requests to the products/brands endpoint.
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
 * Controller for returning products brands via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products/brands" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Product_Categories_V2_Controller
 */
class CoCart_REST_Product_Brands_V2_Controller extends CoCart_REST_Product_Categories_V2_Controller {

	/**
	 * Route namespace. - Remove once new route registry is completed.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'products/brands';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_brand';

	/**
	 * Version of route.
	 */
	protected $version = 'v2';

	/**
	 * Get version of route. - Remove once route abstract is created to extend from.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		return self::get_path_regex();
	}
}
