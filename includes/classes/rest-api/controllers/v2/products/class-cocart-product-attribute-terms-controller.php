<?php
/**
 * CoCart - Product Attribute Terms controller
 *
 * Handles requests to the products/attributes/<attributes_id> endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Product_Attribute_Terms_V2_Controller', 'CoCart_Product_Attribute_Terms_V2_Controller' );

/**
 * CoCart REST API v2 - Product Attribute Terms controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Attribute_Terms_Controller
 */
class CoCart_REST_Product_Attribute_Terms_V2_Controller extends CoCart_Product_Attribute_Terms_Controller {

	/**
	 * Route namespace. - Remove once new route registry is completed.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

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
}
