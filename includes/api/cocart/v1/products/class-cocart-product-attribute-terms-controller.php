<?php
/**
 * CoCart - Product Attribute Terms controller
 *
 * Handles requests to the products/attributes/<attributes_id> endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v1
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Attribute Terms controller class.
 *
 * @package CoCart/API
 * @extends CoCart_REST_Terms_Controller
 */
class CoCart_Product_Attribute_Terms_Controller extends CoCart_REST_Terms_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/attributes/(?P<attribute_id>[\d]+)/terms';

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
				'args'   => array(
					'attribute_id' => array(
						'description' => __( 'Unique identifier for the attribute of the terms.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			array(
				'args'   => array(
					'id'           => array(
						'description' => __( 'Unique identifier for the attribute term.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
					'attribute_id' => array(
						'description' => __( 'Unique identifier for the attribute of the terms.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'integer',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => array( $this, 'get_item_permissions_check' ),
					'args'                => array(
						'context' => $this->get_context_param( array( 'default' => 'view' ) ),
					),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Prepare a single product attribute term output for response.
	 *
	 * @access public
	 * @param  WP_Term         $item Term object.
	 * @param  WP_REST_Request $request
	 * @return WP_REST_Response $response
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get term order.
		$menu_order = get_term_meta( $item->term_id, 'order_' . $this->taxonomy, true );

		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => $item->name,
			'slug'        => $item->slug,
			'description' => $item->description,
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $item->count,
		);

		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, 'view' );

		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $item, $request ) );

		/**
		 * Filter a term item returned from the API.
		 *
		 * Allows modification of the term data right before it is returned.
		 *
		 * @param WP_REST_Response  $response  The response object.
		 * @param object            $item      The original term object.
		 * @param WP_REST_Request   $request   Request used to generate the response.
		 */
		return apply_filters( "cocart_prepare_{$this->taxonomy}", $response, $item, $request );
	}

	/**
	 * Get the Attribute Term's schema, conforming to JSON Schema.
	 *
	 * @access public
	 * @return array
	 */
	public function get_item_schema() {
		$schema = array(
			'schema'     => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'product_attribute_term',
			'type'       => 'object',
			'properties' => array(
				'id'          => array(
					'description' => __( 'Unique identifier for the attribute term.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'        => array(
					'description' => __( 'Term name.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'slug'        => array(
					'description' => __( 'An alphanumeric identifier for the attribute term unique to its type.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_title',
					),
				),
				'description' => array(
					'description' => __( 'HTML description of the attribute term.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'arg_options' => array(
						'sanitize_callback' => 'wp_filter_post_kses',
					),
				),
				'menu_order'  => array(
					'description' => __( 'Menu order, used to custom sort the attribute term.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
				),
				'count'       => array(
					'description' => __( 'Number of published products for the attribute term.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

}
