<?php
/**
 * REST API: CoCart_REST_Item_v2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\v2
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for viewing an individual item in the cart (API v2).
 *
 * This REST API controller handles the request to view a single item
 * in the cart via "cocart/v2/cart/item" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_v2_Controller
 */
class CoCart_REST_Item_v2_Controller extends CoCart_REST_Cart_v2_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Get Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_item' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	} // register_routes()

	/**
	 * View Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.7.8
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function view_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '' : wc_clean( wp_unslash( sanitize_text_field( $request['item_key'] ) ) );

			$cart_contents = ! $this->get_cart_instance()->is_empty() ? array_filter( $this->get_cart_instance()->get_cart() ) : array();

			$item = $this->get_items( $cart_contents );

			$item = isset( $item[ $item_key ] ) ? $item[ $item_key ] : false;

			// If item is not found, throw exception error.
			if ( ! $item ) {
				throw new CoCart_Data_Exception( 'cocart_item_not_found', __( 'Item specified was not found in cart.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $item, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END view_item()

	/**
	 * Get the query params for item.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'item_key' => array(
				'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			),
		);

		return $params;
	} // END get_collection_params()

	/**
	 * Get the schema for returning a cart item.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_cart_item',
			'type'       => 'object',
			'properties' => array(
				'item_key'       => array(
					'description' => __( 'Unique ID of the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'id'             => array(
					'description' => __( 'Product ID or Variation ID of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'name'           => array(
					'description' => __( 'The name of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'title'          => array(
					'description' => __( 'The title of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'price'          => array(
					'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'quantity'       => array(
					'description' => __( 'The quantity of the item and minimum and maximum purchase capability.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => array(
						'value'        => array(
							'description' => __( 'The quantity of the item.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'float',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'min_purchase' => array(
							'description' => __( 'The minimum purchase amount required.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'max_purchase' => array(
							'description' => __( 'The maximum purchase amount allowed. If -1 the item has an unlimited purchase amount.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'totals'         => array(
					'description' => __( 'The totals of the item.', 'cart-rest-api-for-woocommerce' ),
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
					'description' => __( 'The product slug of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'meta'           => array(
					'description' => __( 'The meta data of the item.', 'cart-rest-api-for-woocommerce' ),
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
					'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
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
					'description' => __( 'The featured image of the item.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);
	} // END get_public_item_schema()

} // END class
