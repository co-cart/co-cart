<?php
/**
 * REST API: CoCart_REST_Batch_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI
 * @since   4.0.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Controller for submitting multiple requests at once.
 *
 * This REST API controller is a helpful for performance optimization
 * when a large number of write operations need to be made
 * via "cocart/batch" endpoint.
 *
 * @since 4.0.0 Introduced.
 */
class CoCart_REST_Batch_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'batch';

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		// Batch requests - cocart/batch (POST).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'get_response' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'validation' => array(
							'type'    => 'string',
							'enum'    => array( 'require-all-validate', 'normal' ),
							'default' => 'normal',
						),
						'requests'   => array(
							'required' => true,
							'type'     => 'array',
							'maxItems' => 25,
							'items'    => array(
								'type'       => 'object',
								'properties' => array(
									'method'  => array(
										'type'    => 'string',
										'enum'    => array( 'POST', 'PUT', 'PATCH', 'DELETE' ),
										'default' => 'POST',
									),
									'path'    => array(
										'type'     => 'string',
										'required' => true,
									),
									'body'    => array(
										'type'       => 'object',
										'properties' => array(),
										'additionalProperties' => true,
									),
									'headers' => array(
										'type'       => 'object',
										'properties' => array(),
										'additionalProperties' => array(
											'type'  => array( 'string', 'array' ),
											'items' => array(
												'type' => 'string',
											),
										),
									),
								),
							),
						),
					),
				),
			),
		);
	} // register_routes()

	/**
	 * Get the route response.
	 *
	 * @see WP_REST_Server::serve_batch_request_v1
	 * https://developer.wordpress.org/reference/classes/wp_rest_server/serve_batch_request_v1/
	 *
	 * @throws CoCart_Data_Exception On error.
	 *
	 * @param WP_REST_Request $request Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function get_response( \WP_REST_Request $request ) {
		try {
			foreach ( $request['requests'] as $args ) {
				if ( ! stristr( $args['path'], 'cocart/v2' ) ) {
					throw new \CoCart_Data_Exception( 'cocart_rest_invalid_path', __( 'Invalid path provided.', 'cart-rest-api-for-woocommerce' ), 400 );
				}
			}

			$response = rest_get_server()->serve_batch_request_v1( $request );
		} catch ( \CoCart_Data_Exception $error ) {
			$response = CoCart_Response::get_error_response( $error->getErrorCode(), $error->getMessage(), $error->getCode(), $error->getAdditionalData() );
		} catch ( \Exception $error ) {
			$response = \CoCart_Response::get_error_response( 'cocart_rest_unknown_server_error', $error->getMessage(), 500 );
		}

		if ( is_wp_error( $response ) ) {
			$response = \CoCart_Response::error_to_response( $response );
		}

		$response->header( 'CoCart-Timestamp', time() );

		return $response;
	} // END get_response()

} // END class
