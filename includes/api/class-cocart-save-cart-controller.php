<?php
/**
 * CoCart - Save cart controller
 *
 * Handles the request to save the cart by associating an email address with /save-cart endpoint.
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
 * REST API Save Cart controller class.
 *
 * @package CoCart/API
 */
class CoCart_Save_Cart_Controller extends CoCart_API_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'save-cart';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Count Items in Cart - cocart/v1/save-cart (POST)
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			'methods'  => WP_REST_Server::CREATABLE,
			'callback' => array( $this, 'save_cart' ),
			'args'     => array(
				'cart_key' => array(
					'description' => __( 'Unique identifier for the cart key.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
				'cart_email' => array(
					'description' => __( 'Email address to associate with the cart key.', 'cart-rest-api-for-woocommerce' ),
					'type'        => 'string',
				),
			),
		) );
	} // register_routes()

	/**
	 * Save email address to cart.
	 *
	 * @access public
	 * @static
	 * @param  array $data
	 * @global $wpdb
	 * @return string|WP_REST_Response
	 */
	public static function save_cart( $data = array() ) {
		global $wpdb;

		$cart_key   = ! empty( $data['cart_key'] ) ? $data['cart_key'] : '';
		$cart_email = ! empty( $data['cart_email'] ) ? $data['cart_email'] : '';

		if ( empty( $cart_key ) || empty( $cart_email ) ) {
			$message = __( 'Either the cart key or a valid email address is missing. Please check the parameters.', 'cart-rest-api-for-woocommerce' );

			CoCart_Logger::log( $message, 'error' );

			/**
			 * Filters message about product does not exist.
			 *
			 * @param string $message Message.
			 */
			$message = apply_filters( 'cocart_save_cart_parameters_missing_message', $message );

			return new WP_Error( 'cocart_save_cart_parameters_missing', $message, array( 'status' => 500 ) );
		}

		if ( isset( $cart_key ) ) {
			$cart_saved = CoCart::$session->is_cart_saved( $cart_key );

			if ( $cart_saved ) {
				// If cart exists then associate email address with it.
				$wpdb->query(
					$wpdb->prepare(
						"UPDATE {$wpdb->prefix}cocart_carts SET `cart_email` = '%s'
						WHERE `cart_key` = '%s',
						$cart_email,
						$cart_key
					)
				);
			}
		}

		if ( $return != 'numeric' && $count <= 0 ) {
			$message = __( 'Cart is already saved with another email address!', 'cart-rest-api-for-woocommerce' );

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
