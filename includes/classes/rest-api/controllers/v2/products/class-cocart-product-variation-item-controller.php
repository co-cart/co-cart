<?php
/**
 * REST API: CoCart_REST_Product_Variation_Item_V2_Controller class
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
 * Controller for returning a single product variation item via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products/{product_id}/variations/{id}" endpoint.
 *
 * @since 3.1.0 Introduced.
 *
 * @extends CoCart_REST_Product_Variations_V2_Controller
 */
class CoCart_REST_Product_Variation_Item_V2_Controller extends CoCart_REST_Product_Variations_V2_Controller {

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
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public static function get_path_regex() {
		return '/products/(?P<product_id>[\d]+)/variations/(?P<id>[\d]+)';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'args'                => array(
					'product_id' => array(
						'description' => __( 'Unique identifier for the variable product.', 'cocart-core' ),
						'type'        => 'integer',
					),
					'id'         => array(
						'description' => __( 'Unique identifier for the variation.', 'cocart-core' ),
						'type'        => 'integer',
					),
				),
				'permission_callback' => array( $this, 'validate_variation' ),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	}

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
				throw new CoCart_Data_Exception( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cocart-core' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $product, $request );
			$response = rest_ensure_response( $data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_item()
}
