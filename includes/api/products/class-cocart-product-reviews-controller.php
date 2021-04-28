<?php
/**
 * CoCart - Products controller
 *
 * Handles requests to the /products/reviews/ endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Reviews controller class.
 *
 * @package CoCart/API
 * @extends CoCart_Product_Reviews_V2_Controller
 */
class CoCart_Product_Reviews_V2_Controller extends CoCart_Product_Reviews_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Register the routes for product reviews.
	 *
	 * @access public
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cocart-products' ),
						'type'        => 'integer',
					),
					'id'         => array(
						'description' => __( 'Unique identifier for the variation.', 'cocart-products' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'create_item_permissions_check' ),
					'args'                => array_merge(
						$this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
						array(
							'product_id'     => array(
								'required'    => true,
								'description' => __( 'Unique identifier for the product.', 'cocart-products' ),
								'type'        => 'integer',
							),
							'review'         => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Review content.', 'cocart-products' ),
							),
							'reviewer'       => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Name of the reviewer.', 'cocart-products' ),
							),
							'reviewer_email' => array(
								'required'    => true,
								'type'        => 'string',
								'description' => __( 'Email of the reviewer.', 'cocart-products' ),
							),
						)
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the review.', 'cocart-products' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'check_review_exists' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

}
