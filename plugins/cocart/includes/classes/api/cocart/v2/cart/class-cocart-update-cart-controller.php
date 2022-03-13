<?php
/**
 * CoCart - Update Cart controller.
 *
 * Handles the request to update the cart with /cart/update endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Update Cart controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Cart_v2_Controller
 */
class CoCart_Update_Cart_v2_Controller extends CoCart_Cart_V2_Controller {

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
	protected $rest_base = 'cart/update';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Update Cart - cocart/v2/cart/update (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_cart' ),
					'permission_callback' => array( $this, 'get_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
			)
		);
	} // register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return boolean
	 */
	public function get_permissions_check( $request ) {
		$namespace = wc_clean( wp_unslash( $request['namespace'] ) );

		$extension_class  = new CoCart_Cart_Extension();
		$callback_methods = $extension_class->get_all_registered_callbacks();

		try {
			if ( ! is_string( $namespace ) ) {
				throw new CoCart_Data_Exception( 'cocart_update_cart_namespace_error', sprintf(
					/* translators: %s: Available namespaces */
					__( 'You must provide a namespace when extending the cart endpoint. Available namespaces: (%s)', 'cart-rest-api-for-woocommerce' ),
					implode( ', ', array_keys( $callback_methods ) )
				), 404 );
			}

			if ( ! array_key_exists( $namespace, $callback_methods ) ) {
				throw new CoCart_Data_Exception( 'cocart_update_cart_no_namespace_error', sprintf(
					/* translators: %s: Namespace */
					__( 'There is no such namespace registered: %s.', 'cart-rest-api-for-woocommerce' ),
					$namespace
				), 404 );
			}

			if ( ! is_callable( array( $callback_methods[ $namespace ], 'callback' ) ) ) {
				throw new CoCart_Data_Exception( 'cocart_update_cart_invalid_callback_error', sprintf(
					/* translators: %s: Namespace */
					__( 'There is no valid callback registered for: %s.', 'cart-rest-api-for-woocommerce' ),
					$namespace
				), 400 );
			}
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}

		return true;
	} // END get_permissions_check()

	/**
	 * Updates the cart via requested namespace and returns the updated cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function update_cart( $request ) {
		try {
			$namespace = wc_clean( wp_unslash( $request['namespace'] ) );
			$callback  = null;

			$extension_class  = new CoCart_Cart_Extension();
			$callback_methods = $extension_class->get_all_registered_callbacks();

			$update_cart = $callback_methods[ $namespace ]->callback( $request );

			// Proceed with requested callback.
			if ( is_callable( array( $callback_methods[ $namespace ], 'callback' ) ) ) {
				$callback = $update_cart;
			}

			// Return callback error response if failed to update cart.
			if ( is_wp_error( $callback ) ) {
				return $callback;
			}

			// Returns updated cart if callback was successful.
			$cart = $this->get_cart_contents( $request );

			return CoCart_Response::get_response( $cart, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END update_cart()

	/**
	 * Get the query params for updating cart.
	 *
	 * @access public
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'namespace' => array(
				'description' => __( 'Namespace used to ensure the data in the request is routed appropriately.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
			),
			'data'      => array(
				'description' => __( 'Additional data to pass.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
			),
		);

		return $params;
	} // END get_collection_params()

} // END class
