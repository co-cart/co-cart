<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API variations controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Variations_Controller
 */
class CoCart_Product_Variations_V2_Controller extends CoCart_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

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

	/**
	 * Get variation product data.
	 *
	 * @access protected
	 * @param  WC_Variation_Product $product Product instance.
	 * @return array
	 */
	protected function get_variation_product_data( $product ) {
		$controller = new CoCart_Cart_V2_Controller();

		$type         = $product->get_type();
		$rating_count = $product->get_rating_count( 'view' );
		$average      = $product->get_average_rating( 'view' );

		$data = array(
			'id'          => $product->get_id(),
			'parent_id'   => $product->get_parent_id( 'view' ),
			'name'        => $product->get_name( 'view' ),
			'slug'        => $product->get_slug( 'view' ),
			'permalink'   => $product->get_permalink(),
			'sku'         => $product->get_sku( 'view' ),
			'description' => $product->get_description( 'view' ),
			'dates'       => array(
				'created'      => wc_rest_prepare_date_response( $product->get_date_created( 'view' ), false ),
				'created_gmt'  => wc_rest_prepare_date_response( $product->get_date_created( 'view' ) ),
				'modified'     => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ), false ),
				'modified_gmt' => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ) ),
			),
			'prices'      => array(
				'price'         => $controller->prepare_money_response( $product->get_price( 'view' ), wc_get_price_decimals() ),
				'regular_price' => $controller->prepare_money_response( $product->get_regular_price( 'view' ), wc_get_price_decimals() ),
				'sale_price'    => $product->get_sale_price( 'view' ) ? $controller->prepare_money_response( $product->get_sale_price( 'view' ), wc_get_price_decimals() ) : '',
				'price_range'   => '',
				'on_sale'       => $product->is_on_sale( 'view' ),
				'date_on_sale'  => array(
					'from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ), false ),
					'from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ) ),
					'to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ), false ),
					'to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ) ),
				),
				'currency'      => $controller->get_store_currency(),
			),
			'conditions'  => array(
				'virtual'           => $product->is_virtual(),
				'downloadable'      => $product->is_downloadable(),
				'is_purchasable'    => $product->is_purchasable(),
				'is_in_stock'       => $product->is_in_stock(),
				'sold_individually' => $product->is_sold_individually(),
				'shipping_required' => $product->needs_shipping(),
			),
			'images'      => $this->get_images( $product ),
			'categories'  => $this->get_taxonomy_terms( $product ),
			'tags'        => $this->get_taxonomy_terms( $product, 'tag' ),
			'attributes'  => $this->get_attributes( $product ),
			'stock'       => array(
				'manage_stock'       => $product->managing_stock(),
				'stock_quantity'     => $product->get_stock_quantity( 'view' ),
				'stock_status'       => $product->get_stock_status( 'view' ),
				'backorders'         => $product->get_backorders( 'view' ),
				'backorders_allowed' => $product->backorders_allowed(),
				'backordered'        => $product->is_on_backorder(),
				'low_stock_amount'   => $product->get_low_stock_amount( 'view' ),
			),
			'weight'      => array(
				'value' => $product->get_weight( 'view' ),
				'unit'  => get_option( 'woocommerce_weight_unit' ),
			),
			'dimensions'  => array(
				'length' => $product->get_length( 'view' ),
				'width'  => $product->get_width( 'view' ),
				'height' => $product->get_height( 'view' ),
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			),
			'total_sales' => $product->get_total_sales( 'view' ),
			'meta_data'   => $product->get_meta_data(),
		);

		return $data;
	} // END get_variation_product_data()

}
