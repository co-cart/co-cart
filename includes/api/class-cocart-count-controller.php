<?php
/**
 * CoCart - Count Items controller
 *
 * Handles the request to count the items in the cart with /count-items endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/API
 * @since    2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Count Items controller class.
 *
 * @package CoCart/API
 */
class CoCart_Count_Items_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'count-items';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Count Items in Cart - cocart/v1/count-items (GET)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::READABLE,
			'callback' => array( $this, 'get_cart_contents_count' ),
			'args'     => array(
				'return' => array(
					'default' => 'numeric'
				),
			),
		) );
	} // register_routes()

	/**
	 * Get cart contents count.
	 *
	 * @access  public
	 * @static
	 * @since   1.0.0
	 * @version 2.1.0
	 * @param   array $data
	 * @return  string|WP_REST_Response
	 */
	public static function get_cart_contents_count( $data = array() ) {
		$id = ! empty( $data['id'] ) ? $data['id'] : '';

		// If no id provided then count items in the current session.
		if ( empty( $id ) ) {
			$count = WC()->cart->get_cart_contents_count();
		}
		// Count items from a specific cart in session.
		else {
			$count = $this->get_cart_in_session( $data, 'cart_contents' );
		}

		$return = ! empty( $data['return'] ) ? $data['return'] : '';

		if ( $return != 'numeric' && $count <= 0 ) {
			$message = __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'notice' );

			/**
			 * Filters message about no items in the cart.
			 *
			 * @since 2.1.0
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

			return new WP_REST_Response( $message, 200 );
		}

		return $count;
	} // END get_cart_contents_count()

} // END class
