<?php
/**
 * CoCart - Abstract Rest Terms Controller
 *
 * @deprecated 5.0.0 Use CoCart_REST_Taxonomy_Terms_Controller class instead.
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

if ( ! class_exists( 'CoCart_REST_Terms_V2_Controller' ) ) {

	/**
	 * CoCart REST API v2 - Terms controller class.
	 *
	 * @package CoCart Products/API
	 * @extends CoCart_REST_Taxonomy_Terms_Controller
	 */
	abstract class CoCart_REST_Terms_V2_Controller extends CoCart_REST_Taxonomy_Terms_Controller {

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
		 * The version of this controller's route.
		 *
		 * @deprecated 5.0.0 Version is registered in the REST API class instead.
		 */
		protected $version = 'v2';

		/**
		 * Get the version of this controller's route.
		 *
		 * @deprecated 5.0.0 Version is registered in the REST API class instead.
		 */
		public function get_version() {
			cocart_deprecated_function( __FUNCTION__, '5.0.0' );

			return $this->version;
		} // END get_version()

		/**
		 * Register the routes for terms.
		 *
		 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
		 *
		 * @access public
		 */
		public function register_routes() {
			cocart_deprecated_function( __FUNCTION__, '5.0.0' );

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base,
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_items' ),
						'permission_callback' => array( $this, 'get_items_permissions_check' ),
						'args'                => $this->get_collection_params(),
					),
					'allow_batch' => array( 'v1' => true ),
					'schema'      => array( $this, 'get_item_schema' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/' . $this->rest_base . '/(?P<id>[\d]+)',
				array(
					array(
						'methods'             => WP_REST_Server::READABLE,
						'callback'            => array( $this, 'get_item' ),
						'permission_callback' => array( $this, 'get_item_permissions_check' ),
						'args'                => array(
							'id'      => array(
								'description' => __( 'Unique identifier for the resource.', 'cocart-core' ),
								'type'        => 'integer',
							),
							'context' => $this->get_context_param( array( 'default' => 'view' ) ),
						),
					),
					'allow_batch' => array( 'v1' => true ),
					'schema'      => array( $this, 'get_item_schema' ),
				)
			);
		} // END register_routes()
	} // END class
}
