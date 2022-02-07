<?php
/**
 * CoCart - Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Categories controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Categories_Controller
 */
class CoCart_Product_Categories_V2_Controller extends CoCart_Product_Categories_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Prepare a single product category output for response.
	 *
	 * @access public
	 * @param  WP_Term         $item    Term object.
	 * @param  WP_REST_Request $request Request instance.
	 * @return WP_REST_Response
	 */
	public function prepare_item_for_response( $item, $request ) {
		// Get category display type.
		$display_type = get_term_meta( $item->term_id, 'display_type', true );

		// Get category order.
		$menu_order = get_term_meta( $item->term_id, 'order', true );

		$data = array(
			'id'          => (int) $item->term_id,
			'name'        => $item->name,
			'slug'        => $item->slug,
			'parent'      => (int) $item->parent,
			'description' => $item->description,
			'display'     => $display_type ? $display_type : 'default',
			'image'       => null,
			'menu_order'  => (int) $menu_order,
			'count'       => (int) $item->count,
		);

		// Get category image.
		$image_id = get_term_meta( $item->term_id, 'thumbnail_id', true );

		$thumbnail_id = ! empty( $image_id ) ? $image_id : get_option( 'woocommerce_placeholder_image', 0 );
		$thumbnail_id = apply_filters( 'cocart_products_category_thumbnail', $thumbnail_id );

		if ( $image_id ) {
			$attachment = get_post( $image_id );

			$thumbnail_src = wp_get_attachment_image_src( $thumbnail_id, apply_filters( 'cocart_products_category_thumbnail_size', 'woocommerce_thumbnail' ) );
			$thumbnail_src = ! empty( $thumbnail_src[0] ) ? $thumbnail_src[0] : '';
			$thumbnail_src = apply_filters( 'cocart_products_category_thumbnail_src', $thumbnail_src );

			$data['image'] = array(
				'id'   => (int) $image_id,
				'src'  => esc_url( $thumbnail_src ),
				'name' => get_the_title( $attachment ),
				'alt'  => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
			);
		}

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

}
