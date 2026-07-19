<?php
/**
 * Handles support for Brands taxonomy.
 *
 * Was previously a WooCommerce extension, now part of WooCommerce core.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Compatibility\Modules
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( CoCart_Helpers::is_wc_version_lt( '9.4' ) ) {
	return;
}

if ( ! class_exists( 'CoCart_WC_Brands_Feature' ) ) {

	class CoCart_WC_Brands_Feature {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_filter( 'cocart_prepare_objects_query', array( $this, 'filter_products_by_brand' ), 10, 2 );
			// add_filter( 'rest_product_collection_params', array( $this, 'product_collection_params' ), 10, 2 ); // Not yet working. need to find correct filter.
		}

		/**
		 * Filters products by taxonomy brand name.
		 *
		 * @access public
		 *
		 * @param array           $args    Request args.
		 * @param WP_REST_Request $request Request data.
		 *
		 * @return array Request args.
		 */
		public function filter_products_by_brand( $args, $request ) {
			if ( ! empty( $request['brand'] ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_brand',
					'field'    => 'name',
					'terms'    => $request['brand'],
				);
			}

			return $args;
		} // END filter_products_by_brand()

		/**
		 * Documents additional query params for collections of products.
		 *
		 * @param array        $params JSON Schema-formatted collection parameters.
		 * @param WP_Post_Type $post_type   Post type object.
		 *
		 * @return array JSON Schema-formatted collection parameters.
		 */
		public function product_collection_params( $params, $post_type ) {
			$params['brand'] = array(
				'description'       => __( 'Limit result set to products assigned a specific brand name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'wp_parse_list',
				'validate_callback' => 'rest_validate_request_arg',
			);

			return $params;
		} // END product_collection_params()
	} // END class.

} // END if class exists.

return new CoCart_WC_Brands_Feature();
