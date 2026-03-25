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
	 * Get the path of this rest route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/update';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
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
	 * Route base.
	 *
	 * @deprecated 5.0.0 Replaced with `get_path()` instead.
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/update';

	/**
	 * Register routes.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
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

		$extension_class  = new CoCart_Cart_Extension();
		$callback_methods = $extension_class->get_all_registered_callbacks();

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
			$namespace = wc_clean( sanitize_text_field( wp_unslash( $request['namespace'] ) ) );
			$callback  = null;

			$extension_class  = new CoCart_Cart_Extension();
			$callback_methods = $extension_class->get_all_registered_callbacks();

			$update_cart = $callback_methods[ $namespace ]->callback( $request, $this );

			// Proceed with requested callback.
			if ( is_callable( array( $callback_methods[ $namespace ], 'callback' ) ) ) {
				$callback = $update_cart;
			}

			// Return callback error response if failed to update cart.
			if ( is_wp_error( $callback ) ) {
				return $callback;
			}

			// Returns updated cart if callback was successful.
			$cart = $this->get_items( $request );

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
			'namespace'    => array(
				'description'       => __( 'Namespace used to ensure the data in the request is routed appropriately.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'data'         => array(
				'description' => __( 'Additional data to pass.', 'cocart-core' ),
				'type'        => 'object',
			),
			// Billing fields used by the update-customer callback.
			'first_name'   => array(
				'description'       => __( 'Customers billing first name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'last_name'    => array(
				'description'       => __( 'Customers billing last name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'company'      => array(
				'description'       => __( 'Customers billing company name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'address_1'    => array(
				'description'       => __( 'Customers billing address line 1.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'address_2'    => array(
				'description'       => __( 'Customers billing address line 2.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'city'         => array(
				'description'       => __( 'Customers billing city.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'state'        => array(
				'description'       => __( 'Customers billing state.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'postcode'     => array(
				'description'       => __( 'Customers billing postcode or zip code.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'country'      => array(
				'description'       => __( 'Customers billing country code (ISO 3166-1 alpha-2).', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			'email'        => array(
				'description'       => __( 'Customers billing email address.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_email',
			),
			'phone'        => array(
				'description'       => __( 'Customers billing phone number.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			// Shipping fields (prefixed with s_) used by the update-customer callback.
			's_first_name' => array(
				'description'       => __( 'Customers shipping first name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_last_name'  => array(
				'description'       => __( 'Customers shipping last name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_company'    => array(
				'description'       => __( 'Customers shipping company name.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_address_1'  => array(
				'description'       => __( 'Customers shipping address line 1.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_address_2'  => array(
				'description'       => __( 'Customers shipping address line 2.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_city'       => array(
				'description'       => __( 'Customers shipping city.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_state'      => array(
				'description'       => __( 'Customers shipping state.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_postcode'   => array(
				'description'       => __( 'Customers shipping postcode or zip code.', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
			's_country'    => array(
				'description'       => __( 'Customers shipping country code (ISO 3166-1 alpha-2).', 'cocart-core' ),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
			),
		);

		return $params;
	} // END get_collection_params()
} // END class
