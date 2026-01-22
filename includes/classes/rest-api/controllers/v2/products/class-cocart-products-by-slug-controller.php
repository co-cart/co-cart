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
		return '/products/(?P<slug>[\S]+)';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			'args'        => array(
				'slug' => array(
					'description' => __( 'Slug of the resource.', 'cocart-core' ),
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
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

	/**
	 * Get a single item.
	 *
	 * @throws RouteException On error.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	protected function get_route_response( WP_REST_Request $request ) {
		$slug = sanitize_title( $request['slug'] );

		$object = CoCart_Utilities_Product_Helpers::get_product_by_slug( $slug );

		if ( ! $object ) {
			$object = CoCart_Utilities_Product_Helpers::get_product_variation_by_slug( $slug );
		}

		if ( ! $object || 0 === $object->get_id() ) {
			throw new RouteException( 'woocommerce_rest_product_invalid_slug', __( 'Invalid product slug.', 'cocart-core' ), 404 );
		}

		return rest_ensure_response( $this->schema->get_item_response( $object ) );
	}
} // END class
