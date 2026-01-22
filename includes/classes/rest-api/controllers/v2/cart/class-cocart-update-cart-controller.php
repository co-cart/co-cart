<?php
/**
 * REST API: CoCart_REST_Update_Cart_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Update_Cart_V2_Controller', 'CoCart_Update_Cart_V2_Controller' );

/**
 * Controller for updating the cart via a registered callback (API v2).
 *
 * This REST API controller handles the request to update the cart
 * via "cocart/v2/cart/update" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Update_Cart_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/update';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/update';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_cart' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
		);
	} // END get_args()

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Allowed route to be requested in a batch request.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Update Cart - cocart/v2/cart/update (POST).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return boolean
	 */
	public function get_permissions_check( $request ) {
		$namespace = wc_clean( sanitize_text_field( wp_unslash( $request['namespace'] ) ) );

		$callback_methods = CoCart_Callback_Registry::get_all_registered_callbacks();

		try {
			if ( ! is_string( $namespace ) ) {
				throw new CoCart_Data_Exception(
					'cocart_update_cart_namespace_error',
					sprintf(
						/* translators: %s: Available namespaces */
						__( 'You must provide a namespace when extending the cart endpoint. Available namespaces: (%s)', 'cocart-core' ),
						implode( ', ', array_keys( $callback_methods ) )
					),
					404
				);
			}

			if ( ! array_key_exists( $namespace, $callback_methods ) ) {
				throw new CoCart_Data_Exception(
					'cocart_update_cart_no_namespace_error',
					sprintf(
						/* translators: %s: Namespace */
						__( 'There is no such namespace registered: %s.', 'cocart-core' ),
						$namespace
					),
					404
				);
			}

			if ( ! is_callable( array( $callback_methods[ $namespace ], 'callback' ) ) ) {
				throw new CoCart_Data_Exception(
					'cocart_update_cart_invalid_callback_error',
					sprintf(
						/* translators: %s: Namespace */
						__( 'There is no valid callback registered for: %s.', 'cocart-core' ),
						$namespace
					),
					400
				);
			}
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}

		return true;
	} // END get_permissions_check()

	/**
	 * Updates the cart via requested namespace and returns the updated cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response The returned response.
	 */
	public function update_cart( $request ) {
		try {
			$callback = $callback_methods[ wc_clean( sanitize_text_field( wp_unslash( $request['namespace'] ) ) ) ]->callback( $request, $this );

			// Return callback error response if failed to update cart.
			if ( is_wp_error( $callback ) ) {
				return $callback;
			}

			// Returns updated cart if callback was successful.
			$cart = $this->get_cart( $request );

			$response = rest_ensure_response( $cart );
			$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END update_cart()

	/**
	 * Get the query params for updating cart.
	 *
	 * @access public
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Add to cart query parameters.
		$params += array(
			'namespace' => array(
				'description' => __( 'Namespace used to ensure the data in the request is routed appropriately.', 'cocart-core' ),
				'type'        => 'string',
			),
			'data'      => array(
				'description' => __( 'Additional data to pass.', 'cocart-core' ),
				'type'        => 'object',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
