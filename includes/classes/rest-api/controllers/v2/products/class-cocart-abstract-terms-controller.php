<?php
/**
 * CoCart - Abstract Rest Terms Controller
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
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
	 * @extends CoCart_REST_Terms_Controller
	 */
	abstract class CoCart_REST_Terms_V2_Controller extends CoCart_REST_Terms_Controller {

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

		/**
		 * Get the path of this REST route.
		 *
		 * @return string
		 */
		public function get_path() {
			return self::get_path_regex();
		}

		/**
		 * Register the routes for terms.
		 *
		 * @access public
		 */
		public function register_routes() {
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
					'schema'      => array( $this, 'get_public_item_schema' ),
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
					'schema'      => array( $this, 'get_public_item_schema' ),
				)
			);
		}
	}
}
