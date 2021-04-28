<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API variations controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Products_V2_Controller
 */
class CoCart_Product_Variations_V2_Controller extends CoCart_Products_V2_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/(?P<product_id>[\d]+)/variations';

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = 'product_variation';

	/**
	 * Register the routes for product variations.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v2/products/32/variations (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cocart-products' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get Products - cocart/v2/products/32/variations/148 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'product_id' => array(
							'description' => __( 'Unique identifier for the variable product.', 'cocart-products' ),
							'type'        => 'integer',
						),
						'id'         => array(
							'description' => __( 'Unique identifier for the variation.', 'cocart-products' ),
							'type'        => 'integer',
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

}
