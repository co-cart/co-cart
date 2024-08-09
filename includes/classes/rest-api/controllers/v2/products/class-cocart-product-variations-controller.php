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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Product_Variations_V2_Controller', 'CoCart_Product_Variations_V2_Controller' );

/**
 * CoCart REST API v2 - Product Variations controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Variations_Controller
 */
class CoCart_REST_Product_Variations_V2_Controller extends CoCart_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Register the routes for product variations.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Variable Product Variations - cocart/v2/products/32/variations (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'args'   => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cart-rest-api-for-woocommerce' ),
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

		// Get a single variation - cocart/v2/products/32/variations/148 (GET).
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
					'permission_callback' => array( $this, 'validate_variation' ),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Validate the variation exists and is part of the variable product.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|bool
	 */
	public function validate_variation( $request ) {
		$parent    = wc_get_product( (int) $request['product_id'] );
		$variation = wc_get_product( (int) $request['id'] );

		$variation_ids = $parent->get_children();

		// Validate the variation product exists.
		if ( ! $variation || 0 === $variation->get_id() ) {
			return new WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		// Validate the variation requested to see if it is not one of the variations for the variable product.
		if ( ! in_array( $variation->get_id(), $variation_ids ) ) {
			return new WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), array( 'status' => 404 ) );
		}

		// Validation successful.
		return true;
	} // END validate_variation()

	/**
	 * Prepare a single variation output for response.
	 *
	 * @access public
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function prepare_object_for_response( $product, $request ) {
		$controller = new CoCart_REST_Products_V2_Controller();

		$data     = $controller->get_variation_product_data( $product );
		$data     = $controller->add_additional_fields_to_object( $data, $request );
		$data     = $controller->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product ) );

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to product type being prepared for the response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  The product object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object_v2", $response, $product, $request );
	} // END prepare_object_for_response()

	/**
	 * Get a single item.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response The response, or an error.
	 */
	public function get_item( $request ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );

			$product_id = CoCart_Utilities_Cart_Helpers::validate_product_id( $product_id );

			// Return failed product ID validation if any.
			if ( is_wp_error( $product_id ) ) {
				return $product_id;
			}

			$product = wc_get_product( $product_id );

			if ( ! $product || 0 === $product->get_id() ) {
				throw new CoCart_Data_Exception( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $product, $request );
			$response = rest_ensure_response( $data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_item()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array $links Links for the given product.
	 */
	protected function prepare_links( $product ) {
		$links = parent::prepare_links( $product );

		$rest_base = str_replace( '(?P<product_id>[\d]+)', $product->get_parent_id(), $this->rest_base );

		$links['self']['href']       = rest_url( sprintf( '/%s/%s/%d', $this->namespace, $rest_base, $product->get_id() ) );
		$links['collection']['href'] = rest_url( sprintf( '/%s/%s', $this->namespace, $rest_base ) );

		// Rename link type and add permalink for the parent product.
		$links['up'] = array(
			'permalink' => get_permalink( $product->get_parent_id() ),
			'href'      => $links['parent_product']['href'],
		);

		unset( $links['parent_product'] );

		return $links;
	} // END prepare_links()
}
