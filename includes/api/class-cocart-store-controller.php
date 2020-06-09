<?php
/**
 * CoCart REST API Store controller.
 *
 * Returns store details and all routes.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    3.0.0
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
		// Get Cart - cocart/v2 (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_store' ),
		) );
	} // register_routes()

	/**
	 * Retrieves the store index.
	 *
	 * This endpoint describes the general store details.
	 *
	 * @access public
	 * @param  array $request
	 * @return WP_REST_Response The API root index data.
	 */
	public function get_store( $request ) {
		// General store data.
		$available = array(
			'name'            => get_option( 'blogname' ),
			'description'     => get_option( 'blogdescription' ),
			'home_url'        => home_url(),
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
		return apply_filters( 'cocart_store_address', array(
			'address'   => get_option( 'woocommerce_store_address' ),
			'address_2' => get_option( 'woocommerce_store_address_2' ),
			'city'      => get_option( 'woocommerce_store_city' ),
			'country'   => get_option( 'woocommerce_default_country' ),
			'postcode'  => get_option( 'woocommerce_store_postcode' )
		) );
	} // END get_store_address()

	/**
	 * Returns the list of all CoCart API routes.
	 *
	 * @access public
	 * @return array
	 */
	public function get_routes() {
		$prefix = trailingslashit( home_url() . '/' .rest_get_url_prefix() . '/cocart/v2/' );

		return apply_filters( 'cocart_routes', array(
			'cart'         =>  $prefix . 'cart',
			'add-item'     =>  $prefix . 'cart/add-item',
			'clear'        =>  $prefix . 'cart/clear',
			'calculate'    =>  $prefix . 'cart/calculate',
			'count-items'  =>  $prefix . 'cart/items/count',
			'update-item'  =>  $prefix . 'cart/update-item',
			'remove-item'  =>  $prefix . 'cart/remove-item',
			'restore-item' =>  $prefix . 'cart/restore-item',
			'logout'       =>  $prefix . 'cart/logout',
			'totals'       =>  $prefix . 'cart/totals',
		) );
	} // END get_routes()

} // END class
