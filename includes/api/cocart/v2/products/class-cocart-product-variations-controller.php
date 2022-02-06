<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0
 * @license GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

/**
 * CoCart REST API v2 - Product Variations controller class.
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
	/*public function register_routes() {
		// Get Products - cocart/v1/products/32/variations/148 (GET)
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'product_id' => array(
							'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
						'id'         => array(
							'description' => __( 'Unique identifier for the variation.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
						),
					),
					'permission_callback' => array( $this, 'validate_variation_request' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}*/

	/**
	 * Prepare a single variation output for response.
	 *
	 * @access public
	 * @param  WC_Product      $product Product instance.
	 * @param  WP_REST_Request $request - Full details about the request.
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		$data     = $this->get_variation_product_data( $product );
		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to product type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data          $object   Object data.
		 * @param WP_REST_Request  $request - Full details about the request.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object", $response, $product, $request );
	}

}
