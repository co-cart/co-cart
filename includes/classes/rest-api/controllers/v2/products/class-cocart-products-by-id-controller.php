<?php
/**
 * REST API: CoCart_REST_Products_by_ID_V2_Controller class
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
 * Controller for returning an individual product via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products" endpoint.
 *
 * @since 5.0.0 Introduced.
 *
 * @extends CoCart_REST_Products_V2_Controller
 */
class CoCart_REST_Products_by_ID_V2_Controller extends CoCart_REST_Products_V2_Controller {

	/**
	 * Route namespace. - Remove once new route registry is completed.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Schema.
	 *
	 * @var array
	 */
	protected $schema = array();

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
		return '/products/(?P<id>[\w-]+)';
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
					'id'      => array(
						'description' => __( 'Unique identifier for the product.', 'cocart-core' ),
						'type'        => 'string',
					),
					'context' => $this->get_context_param(
						array(
							'default' => 'view',
						)
					),
				),
				'permission_callback' => '__return_true',
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

	/**
	 * Get a single item.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
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

			if ( ! $product || 0 === $product->get_id() || 'publish' !== $product->get_status() ) {
				throw new CoCart_Data_Exception( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cocart-core' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $product, $request );
			$response = rest_ensure_response( $data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_item()
} // END class
