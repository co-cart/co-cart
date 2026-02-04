<?php
/**
 * CoCart - Product Attribute Terms controller
 *
 * Handles requests to the products/attributes/<attributes_id> endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
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
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public static function get_path_regex() {
		return '/products/attributes/(?P<attribute_id>[\d]+)/terms';
	} // END get_path_regex()

	/**
	 * Get the path of this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path() {
		return self::get_path_regex();
	} // END get_path()

	/**
	 * Route namespace.
	 *
	 * @deprecated 5.0.0 Use $this->namespace from the REST API class instead.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Version of route.
	 *
	 * @deprecated 5.0.0 Version is registered in the REST API class instead.
	 */
	protected $version = 'v2';

	/**
	 * Get version of route.
	 *
	 * @deprecated 5.0.0 Version is registered in the REST API class instead.
	 */
	public function get_version() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		return $this->version;
	} // END get_version()
} // END class
