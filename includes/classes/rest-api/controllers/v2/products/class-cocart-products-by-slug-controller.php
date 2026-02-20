<?php
/**
 * REST API: CoCart_REST_Products_by_Slug_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for returning a single product by slug via the REST API (API v2).
 *
 * This REST API controller handles requests to return individual products by slugs
 * via /products/{slug} endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Products_V2_Controller
 */
class CoCart_REST_Products_by_Slug_V2_Controller extends CoCart_REST_Products_V2_Controller {

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/products/(?P<slug>[\S]+)';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			'args'        => array(
				'slug' => array(
					'description' => __( 'Slug of the product.', 'cocart-core' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'args'                => array(
					'context' => $this->get_context_param(
						array(
							'default' => 'view',
						)
					),
				),
				'permission_callback' => '__return_true',
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_item_schema' ),
		);
	} // END get_args()

	/**
	 * Get a single item.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_item( $request ) {
		try {
			$slug = sanitize_title( $request['slug'] );

			$object = CoCart_Utilities_Product_Helpers::get_product_by_slug( $slug );

			if ( ! $object ) {
				$object = CoCart_Utilities_Product_Helpers::get_product_variation_by_slug( $slug );
			}

			if ( ! $object || 0 === $object->get_id() ) {
				throw new CoCart_Data_Exception( 'cocart_product_invalid_slug', esc_html__( 'Invalid product slug.', 'cocart-core' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $object, $request );
			$response = rest_ensure_response( $data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_item()
	/**
	 * Retrieves the item's schema, conforming to JSON Schema.
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
