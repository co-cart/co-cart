<?php
/**
 * CoCart REST API Store controller.
 *
 * Returns store details and all routes.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\v2
 * @since    3.0.0
 * @version  3.0.7
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API Store v2 controller class.
 *
 * @package CoCart REST API/API
 */
class CoCart_Store_V2_Controller extends CoCart_API_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'store';

	/**
	 * Register the routes for index.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Cart - cocart/v2 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_store' ),
				'permission_callback' => '__return_true',
			)
		);
	} // register_routes()

	/**
	 * Retrieves the store index.
	 *
	 * This endpoint describes the general store details.
	 *
	 * @access public
	 * @param  WP_REST_Request $request - Full details about the request.
	 * @return WP_REST_Response The API root index data.
	 */
	public function get_store( $request ) {
		// General store data.
		$available = array(
			'version'         => COCART_VERSION,
			'title'           => get_option( 'blogname' ),
			'description'     => get_option( 'blogdescription' ),
			'home_url'        => home_url(),
			'language'        => get_bloginfo( 'language' ),
			'gmt_offset'      => get_option( 'gmt_offset' ),
			'timezone_string' => get_option( 'timezone_string' ),
			'store_address'   => $this->get_store_address(),
			'routes'          => $this->get_routes(),
		);

		$response = new WP_REST_Response( $available );

		$response->add_link( 'help', 'https://docs.cocart.xyz/' );

		/**
		 * Filters the API store index data.
		 *
		 * This contains the data describing the API. This includes information
		 * about the store, routes available on the API, and a small amount
		 * of data about the site.
		 *
		 * @param WP_REST_Response $response Response data.
		 */
		return apply_filters( 'cocart_store_index', $response );
	} // END get_store()

	/**
	 * Returns the store address.
	 *
	 * @access public
	 * @return array
	 */
	public function get_store_address() {
		return apply_filters(
			'cocart_store_address',
			array(
				'address'   => get_option( 'woocommerce_store_address' ),
				'address_2' => get_option( 'woocommerce_store_address_2' ),
				'city'      => get_option( 'woocommerce_store_city' ),
				'country'   => get_option( 'woocommerce_default_country' ),
				'postcode'  => get_option( 'woocommerce_store_postcode' ),
			)
		);
	} // END get_store_address()

	/**
	 * Returns the list of all public CoCart API routes.
	 *
	 * @access public
	 * @return array
	 */
	public function get_routes() {
		$prefix = trailingslashit( home_url() . '/' . rest_get_url_prefix() . '/cocart/v2/' );

		return apply_filters(
			'cocart_routes',
			array(
				'cart'             => $prefix . 'cart',
				'cart-add-item'    => $prefix . 'cart/add-item',
				'cart-add-items'   => $prefix . 'cart/add-items',
				'cart-item'        => $prefix . 'cart/item',
				'cart-items'       => $prefix . 'cart/items',
				'cart-items-count' => $prefix . 'cart/items/count',
				'cart-calculate'   => $prefix . 'cart/calculate',
				'cart-clear'       => $prefix . 'cart/clear',
				'cart-totals'      => $prefix . 'cart/totals',
				'login'            => $prefix . 'login',
				'logout'           => $prefix . 'logout',
			)
		);
	} // END get_routes()

} // END class
