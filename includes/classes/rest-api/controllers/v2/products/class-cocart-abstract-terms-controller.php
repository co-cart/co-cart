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
		 * The version of this controller's route.
		 *
		 * @var string
		 */
		protected $version = 'v2';

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
