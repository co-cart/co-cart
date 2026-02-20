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
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

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
	public function get_path_regex() {
		return '/products/tags';
	} // END get_path_regex()

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
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Get the tag schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		if ( $this->schema ) {
			return $this->add_additional_fields_schema( $this->schema );
		}

		$this->schema = parent::get_item_schema();

		return $this->add_additional_fields_schema( $this->schema );
	} // END get_item_schema()
} // END class
