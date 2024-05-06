<?php
/**
 * REST API: CoCart_Count_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v1
 * @since   2.1.0 Introduced.
 * @version 3.13.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Counts the items in the cart. (API v1)
 *
 * Handles the request to count the items in the cart with /count-items endpoint.
 *
 * @since 2.1.0 Introduced.
 *
 * @see CoCart_API_Controller
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
	 *
	 * @since   2.1.0 Introduced.
	 * @version 2.7.2
	 */
	public function register_routes() {
		// Count Items in Cart - cocart/v1/count-items (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_cart_contents_count' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'return' => array(
						'default' => 'numeric',
						'type'    => 'string',
					),
				),
			)
		);
	} // END register_routes()

	/**
	 * Count items.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param array           $cart_contents Cart contents.
	 *
	 * @return string|WP_REST_Response Response data.
	 */
	public static function count_items( $request = array(), $cart_contents = array() ) {
		if ( empty( $cart_contents ) ) {
			$count = WC()->cart->get_cart_contents_count();
		} else {
			// Counts all items from the quantity variable.
			$count = array_sum( wp_list_pluck( $cart_contents, 'quantity' ) );
		}

		return $count;
	} // END count_items()

	/**
	 * Get cart contents count.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 1.0.0 Introduced.
	 *
	 * @see CoCart_Logger::log()
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param array           $cart_contents Cart contents.
	 *
	 * @return string|WP_REST_Response Response data.
	 */
	public static function get_cart_contents_count( $request = array(), $cart_contents = array() ) {
		$return = ! empty( $request['return'] ) ? $request['return'] : '';
		$count  = self::count_items( $request, $cart_contents );

		if ( 'numeric' !== $return && $count <= 0 ) {
			$message = __( 'There are no items in the cart!', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'notice' );

			/**
			 * Filters message about no items in the cart.
			 *
			 * @since 2.1.0 Introduced.
			 *
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

			return new WP_REST_Response( $message, 200 );
		}

		return $count;
	} // END get_cart_contents_count()
} // END class
