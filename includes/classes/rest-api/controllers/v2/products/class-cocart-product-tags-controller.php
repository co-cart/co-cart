<?php
/**
 * CoCart - Product Tags controller
 *
 * Handles requests to the products/tags endpoint.
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

class_alias( 'CoCart_REST_Product_Tags_V2_Controller', 'CoCart_Product_Tags_V2_Controller' );

/**
 * CoCart REST API v2 - Product Tags controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_REST_Taxonomy_Terms_Controller
 */
class CoCart_REST_Product_Tags_V2_Controller extends CoCart_REST_Taxonomy_Terms_Controller {

	/**
	 * Taxonomy.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_tag';

	/**
	 * Get the path regex for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string Path regex.
	 */
	public static function get_path_regex() {
		return '/products/tags';
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
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array Method arguments.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

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
	}
} // END class
