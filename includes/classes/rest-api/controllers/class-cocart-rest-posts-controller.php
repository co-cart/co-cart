<?php
/**
 * Abstract: CoCart_REST_Posts_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   4.?.? Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Posts Controller Class.
 *
 * @since   4.?.? Introduced.
 * @extends CoCart_REST_Controller
 */
abstract class CoCart_REST_Posts_Controller extends CoCart_REST_Controller {

	/**
	 * Post type.
	 *
	 * @var string
	 */
	protected $post_type = '';

	/**
	 * Controls visibility on frontend.
	 *
	 * @var string
	 */
	protected $public = false;

	/**
	 * Get post types.
	 *
	 * @access protected
	 *
	 * @return array
	 */
	protected function get_post_types() {
		return array( $this->post_type );
	} // END get_post_types()

	/**
	 * Prepare objects query.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		return new WP_Error(
			'invalid-method',
			sprintf(
				/* translators: %s: Class method name. */
				__( "Method '%s' not implemented. Must be overridden in subclass.", 'cart-rest-api-for-woocommerce' ),
				__METHOD__
			),
			array( 'status' => 405 )
		);
	} // END prepare_objects_query()

	/**
	 * Get objects.
	 *
	 * @access protected
	 *
	 * @param array $query_args Query args.
	 *
	 * @return array
	 */
	protected function get_objects( $query_args ) {
		return new WP_Error(
			'invalid-method',
			sprintf(
				/* translators: %s: Class method name. */
				__( "Method '%s' not implemented. Must be overridden in subclass.", 'cart-rest-api-for-woocommerce' ),
				__METHOD__
			),
			array( 'status' => 405 )
		);
	} // END get_objects()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$links = array(
			'self'       => array(
				'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $post->ID ) ),
			),
			'collection' => array(
				'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
			),
		);

		if ( $this->public ) {
			$links['self']['permalink'] = get_permalink( $post->ID );
		}

		return $links;
	} // END prepare_links()

	/**
	 * Get the query params for collections of resource.
	 *
	 * @access public
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		$params['after']   = array(
			'description'       => __( 'Limit response to resources published after a given ISO8601 compliant date.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['before']  = array(
			'description'       => __( 'Limit response to resources published before a given ISO8601 compliant date.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'format'            => 'date-time',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['exclude'] = array(
			'description'       => __( 'Ensure result set excludes specific IDs.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['include'] = array(
			'description'       => __( 'Limit result set to specific ids.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'array',
			'items'             => array(
				'type' => 'integer',
			),
			'default'           => array(),
			'sanitize_callback' => 'wp_parse_id_list',
		);
		$params['offset']  = array(
			'description'       => __( 'Offset the result set by a specific number of items.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']   = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'default'           => 'DESC',
			'enum'              => array( 'ASC', 'DESC' ),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby'] = array(
			'description'       => __( 'Sort collection by object attribute.', 'cart-rest-api-for-woocommerce' ),
			'type'              => 'string',
			'default'           => 'date',
			'enum'              => array(
				'date',
				'id',
				'include',
				'title',
				'slug',
				'modified',
			),
			'sanitize_callback' => 'sanitize_text_field',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$post_type_obj = get_post_type_object( $this->post_type );

		if ( isset( $post_type_obj->hierarchical ) && $post_type_obj->hierarchical ) {
			$params['parent']         = array(
				'description'       => __( 'Limit result set to those of particular parent IDs.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
			$params['parent_exclude'] = array(
				'description'       => __( 'Limit result set to all items except those of a particular parent ID.', 'cart-rest-api-for-woocommerce' ),
				'type'              => 'array',
				'items'             => array(
					'type' => 'integer',
				),
				'sanitize_callback' => 'wp_parse_id_list',
				'default'           => array(),
			);
		}

		return $params;
	} // END get_collection_params()
} // END class
