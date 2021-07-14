<?php
/**
 * CoCart REST API Sessions controller.
 *
 * Returns a list of carts in session.
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
 * CoCart REST API Sessions v2 controller class.
 *
 * @package CoCart REST API/API
 * @extends CoCart_Cart_V2_Controller
 */
class CoCart_Sessions_V2_Controller extends CoCart_Cart_V2_Controller {

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
	protected $rest_base = 'sessions';

	/**
	 * Register the routes for index.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Sessions - cocart/v2/sessions (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_carts_in_session' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
				),
			// 'schema' => array( $this, 'get_item_schema' )
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 * @return WP_Error|boolean
	 */
	public function get_items_permissions_check() {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cart-rest-api-for-woocommerce' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns carts in session if any exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.0.7
	 * @return  WP_REST_Response Returns the carts in session from the database.
	 */
	public function get_carts_in_session() {
		try {
			global $wpdb;

			$results = $wpdb->get_results(
				"
				SELECT * 
				FROM {$wpdb->prefix}cocart_carts",
				ARRAY_A
			);

			if ( empty( $results ) ) {
				throw new CoCart_Data_Exception( 'cocart_no_carts_in_session', __( 'No carts in session!', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$sessions = array();

			foreach ( $results as $key => $cart ) {
				$cart_value = maybe_unserialize( $cart['cart_value'] );
				$customer   = maybe_unserialize( $cart_value['customer'] );

				$email      = ! empty( $customer['email'] ) ? $customer['email'] : '';
				$first_name = ! empty( $customer['first_name'] ) ? $customer['first_name'] : '';
				$last_name  = ! empty( $customer['last_name'] ) ? $customer['last_name'] : '';

				if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
					$name = $first_name . ' ' . $last_name;
				} else {
					$name = '';
				}

				$cart_source = $cart['cart_source'];

				$sessions[] = array(
					'cart_id'         => $cart['cart_id'],
					'cart_key'        => $cart['cart_key'],
					'customers_name'  => $name,
					'customers_email' => $email,
					'cart_created'    => gmdate( 'm/d/Y H:i:s', $cart['cart_created'] ),
					'cart_expiry'     => gmdate( 'm/d/Y H:i:s', $cart['cart_expiry'] ),
					'cart_source'     => $cart_source,
					'link'            => rest_url( sprintf( '/%s/%s', $this->namespace, 'session/' . $cart['cart_key'] ) ),
				);
			}

			return CoCart_Response::get_response( $sessions, $this->namespace, $this->rest_base );
		} catch ( \CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_carts_in_session()

} // END class
