<?php
/**
 * CoCart - Items controller
 *
 * Handles the request to view just the items in the cart with /cart/items endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - View Items controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Items_v2_Controller extends CoCart_Cart_V2_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/items';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Items - cocart/v2/cart/items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_items_schema' ),
			)
		);
	} // register_routes()

	/**
	 * Returns all items in the cart.
	 *
	 * @access public
	 * @return WP_REST_Response
	 */
	public function view_items() {
		$cart_contents = ! $this->get_cart_instance()->is_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

		$items = $this->get_items( $cart_contents );

		// Return message should the cart be empty.
		if ( empty( $cart_contents ) ) {
			$items = esc_html__( 'No items in the cart.', 'cart-rest-api-for-woocommerce' );
		}

		return CoCart_Response::get_response( $items, $this->namespace, $this->rest_base );
	} // END view_items()

	/**
	 * Get the query params.
	 *
	 * @access public
	 * @since  3.1.0
	 * @return array $params
	 */
	public function get_collection_params() { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		return parent::get_collection_params();
	} // END get_collection_params()

	/**
	 * Get the schema for returning cart items.
	 *
	 * @access public
	 * @since  3.1.0 Introduced.
	 * @return array
	 */
	public function get_public_items_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'CoCart - ' . __( 'Items in Cart', 'cart-rest-api-for-woocommerce' ),
			'type'       => 'object',
			'properties' => array(
				'[a-z0-9]' => array(
					'description' => __( 'The item container identified with the unique ID of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'item_key'       => array(
							'description' => __( 'Unique ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'id'             => array(
							'description' => __( 'Product ID or Variation ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'           => array(
							'description' => __( 'The name of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'title'          => array(
							'description' => __( 'The title of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'price'          => array(
							'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'quantity'       => array(
							'description' => __( 'The quantity of the item in the cart and minimum and maximum purchase capability.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'value'        => array(
									'description' => __( 'The quantity of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'min_purchase' => array(
									'description' => __( 'The minimum purchase amount required.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'max_purchase' => array(
									'description' => __( 'The maximum purchase amount allowed. If -1 the item has an unlimited purchase amount.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'totals'         => array(
							'description' => __( 'The totals of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'subtotal'     => array(
									'description' => __( 'The subtotal of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'subtotal_tax' => array(
									'description' => __( 'The subtotal tax of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'total'        => array(
									'description' => __( 'The total of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'total_tax'    => array(
									'description' => __( 'The total tax of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
							),
						),
						'slug'           => array(
							'description' => __( 'The product slug of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'meta'           => array(
							'description' => __( 'The meta data of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'product_type' => array(
									'description' => __( 'The product type of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'sku'          => array(
									'description' => __( 'The SKU of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'dimensions'   => array(
									'description' => __( 'The dimensions of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'properties'  => array(
										'length' => array(
											'description' => __( 'The length of the item in cart.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'width'  => array(
											'description' => __( 'The width of the item in cart.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'height' => array(
											'description' => __( 'The height of the item in cart.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'unit'   => array(
											'description' => __( 'The unit measurement of the item in cart.', 'cart-rest-api-for-woocommerce' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
								),
								'weight'       => array(
									'description' => __( 'The weight of the item in cart.', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'float',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'variation'    => array(
									'description' => __( 'The variation attributes of the item in cart (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'properties'  => array(),
								),
							),
						),
						'backorders'     => array(
							'description' => __( 'The price of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'cart_item_data' => array(
							'description' => __( 'Custom item data applied to the item in cart (if any).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(),
						),
						'featured_image' => array(
							'description' => __( 'The featured image of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
			),
		);
	} // END get_public_items_schema()

} // END class
