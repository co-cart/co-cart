<?php
/**
 * REST API: CoCart_REST_Product_Variations_V2_Controller class
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

class_alias( 'CoCart_REST_Product_Variations_V2_Controller', 'CoCart_Product_Variations_V2_Controller' );

/**
 * Controller for returning products via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products/variations" endpoint.
 *
 * @since 3.1.0 Introduced.
 *
 * @extends CoCart_REST_Product_Variations_Controller
 */
class CoCart_REST_Product_Variations_V2_Controller extends CoCart_REST_Product_Variations_Controller {

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
	 * Get the path of this rest route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public static function get_path_regex() {
		return '/products/(?P<product_id>[\d]+)/variations';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			'args'        => array(
				'product_id' => array(
					'description' => __( 'Unique identifier for the variable product.', 'cocart-core' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'args'                => $this->get_collection_params(),
				'permission_callback' => '__return_true',
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
	} // END get_version()

	/**
	 * Register the routes for product variations.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Get Variable Product Variations - cocart/v2/products/32/variations (GET).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

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
			return new \WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		// Validate the variation requested to see if it is not one of the variations for the variable product.
		if ( ! in_array( $variation->get_id(), $variation_ids ) ) {
			return new \WP_Error( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cocart-core' ), array( 'status' => 404 ) );
		}

		// Validation successful.
		return true;
	} // END validate_variation()

	/**
	 * Prepare a single variation output for response.
	 *
	 * @access public
	 *
	 * @param WC_Product_Variation $product The product object.
	 * @param WP_REST_Request      $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function prepare_object_for_response( $product, $request ) {
		$controller = $this->get_products_controller();
		$fields     = $controller->get_fields_for_response( $request );

		$data     = $controller->get_variation_product_data( $product, $fields );
		$data     = $controller->add_additional_fields_to_object( $data, $request );
		$data     = $controller->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );

		// Only prepare links if requested (WordPress 6.1+ optimization).
		if ( rest_is_field_included( '_links', $fields ) || rest_is_field_included( '_embedded', $fields ) ) {
			$response->add_links( $this->prepare_links( $product ) );
		}

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
	 * Get the products controller instance for delegating response building.
	 *
	 * @access protected
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return CoCart_REST_Products_V2_Controller
	 */
	protected function get_products_controller() {
		static $controller = null;

		if ( is_null( $controller ) ) {
			$controller = new CoCart_REST_Products_V2_Controller();
		}

		return $controller;
	} // END get_products_controller()

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
			'permalink' => cocart_get_permalink( get_permalink( $product->get_parent_id() ) ),
			'href'      => $links['parent_product']['href'],
		);

		unset( $links['parent_product'] );

		return $links;
	} // END prepare_links()
}
