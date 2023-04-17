<?php
/**
 * Abstract: CoCart\Abstracts\CoCart_REST_Controller.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Abstracts
 * @since   4.0.0 Introduced.
 */

use CoCart\Schemas\AbstractSchema;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class extends `WP_REST_Controller` in order to manage support and interacting with CoCart's REST API.
 *
 * It's required to follow "Controller Classes" guide before extending this class:
 * <https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/>
 *
 * NOTE THAT ONLY CODE RELEVANT FOR MOST ENDPOINTS SHOULD BE INCLUDED INTO THIS CLASS.
 * If necessary extend this class and create new abstract classes like `CoCart_REST_Terms_Controller`.
 *
 * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/
 */
abstract class CoCart_REST_Controller extends \WP_REST_Controller {

	/**
	 * Schema class instance.
	 *
	 * @access protected
	 *
	 * @var AbstractSchema
	 */
	protected $schema = null;

	/**
	 * Endpoint namespace.
	 *
	 * The endpoint namespace will always start with the lowest version for backwards compatibility.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v1';

	/**
	 * Route base.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Used to cache computed return fields.
	 *
	 * @access private
	 *
	 * @var null|array
	 */
	private $_fields = null;

	/**
	 * Used to verify if cached fields are for correct request object.
	 *
	 * @access private
	 *
	 * @var null|WP_REST_Request
	 */
	private $_request = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->schema = new AbstractSchema;
	}

	/**
	 * Get the namespace for this route.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	} // END get_namespace()

	/**
	 * Get the path of this REST route.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function get_path() {
		return $this->rest_base;
	} // END get_path()

	/**
	 * Get arguments for this REST route.
	 *
	 * @access public
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array();
	} // END get_args()

	/**
	 * Get item schema properties.
	 *
	 * Retrieves the item’s schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		return $this->schema->get_public_item_schema();
	} // END get_public_item_schema()

	/**
	 * Get normalized rest base.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return string
	 */
	protected function get_normalized_rest_base() {
		return preg_replace( '/\(.*\)\//i', '', $this->rest_base );
	} // END get_normalized_rest_base()

	/**
	 * Prepare objects query.
	 *
	 * @todo Needs finishing.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function prepare_objects_query( $request ) {
		$args = array(
			'offset'  => $request['offset'],
			'order'   => ! empty( $request['order'] ) ? strtoupper( $request['order'] ) : 'DESC',
			'orderby' => ! empty( $request['orderby'] ) ? strtolower( $request['orderby'] ) : '',
			'paged'   => $request['page'],
		);
	} // END prepare_objects_query()

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `fields=` request argument.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return string Fields to be included in the response.
	 */
	public function get_fields_for_response( $request ) {
		// From xdebug profiling, this method could take upto 25% off request time in index calls.
		// Cache it and make sure _fields was cached on current request object!
		if ( isset( $this->_fields ) && is_array( $this->_fields ) && $request === $this->_request ) {
			return $this->_fields;
		}

		$this->_request = $request;

		$schema     = $this->get_public_item_schema();
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		$fields = array_unique( array_keys( $properties ) );

		if ( ! isset( $request['fields'] ) ) {
			$this->_fields = $fields;
			return $fields;
		}

		$requested_fields = wp_parse_list( $request['fields'] );

		// Return all fields if no fields specified.
		if ( 0 === count( $requested_fields ) ) {
			$this->_fields = $fields;
			return $fields;
		}

		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		// Return the list of all requested fields which appear in the schema.
		$this->_fields = array_reduce(
			$requested_fields,
			static function( $response_fields, $field ) use ( $fields ) {
				if ( in_array( $field, $fields, true ) ) {
					$response_fields[] = $field;

					return $response_fields;
				}

				// Check for nested fields if $field is not a direct match.
				$nested_fields = explode( '.', $field );

				// A nested field is included so long as its top-level property
				// is present in the schema.
				if ( in_array( $nested_fields[0], $fields, true ) ) {
					$response_fields[] = $field;
				}

				return $response_fields;
			},
			array()
		);
		return $this->_fields;
	} // END get_fields_for_response()

	/**
	 * Returns either the default response of the API requested or a filtered response.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 3.1.0 Added two response headers; a timestamp and the version of CoCart.
	 * @since 3.3.0 Added new custom headers without the prefix `X-`
	 * @since 4.0.0 Removed old custom headers with the prefix `X-`
	 *
	 * @param \WP_REST_Request $request Request object.
	 * @param array            $data    The original data response of the API requested.
	 *
	 * @return \WP_REST_Response|\WP_Error Returned response.
	 */
	public function get_response( \WP_REST_Request $request, $data = array() ) {
		$response = null;

		$route    = $request->get_route();
		$route    = explode( '/', $route );
		$route    = $route[2];
		$endpoint = str_replace( '-', '_', $route );

		try {
			// Return response.
			$response = rest_ensure_response( $data );

			/**
			 * Filter decides if the responses return as default or
			 * use the modified response which is filtered by the rest base.
			 *
			 * @see cocart_{$endpoint}_response
			 *
			 * @since 3.0.0 Introduced.
			 */
			$default_response = apply_filters( 'cocart_return_default_response', true );

			if ( ! $default_response ) {
				/**
				 * Filter is to be used as a final straw for changing the response
				 * based on the endpoint.
				 *
				 * @since 3.0.0 Introduced.
				 */
				$response = apply_filters( 'cocart_{$endpoint}_response', $response );
			}

			/**
			 * Data can only return empty if:
			 *
			 * 1. Something seriously has gone wrong server side and no data could be provided.
			 * 2. The response returned nothing because the cart is empty.
			 * 3. The developer filtered the response incorrectly and returned nothing.
			 */
			$endpoints = array(
				'cart',
				'session',
				'cart/items',
				'cart/items/count',
			);

			foreach ( $endpoints as $route ) {
				if ( $route !== $endpoint && empty( $response ) ) {
					/* translators: %s: REST API URL */
					throw new CoCart_Data_Exception( 'cocart_response_returned_empty', sprintf( __( 'Request returned nothing for "%s"! Please seek assistance.', 'cart-rest-api-for-woocommerce' ), rest_url( $route ) ) );
				}
			}
		} catch ( \CoCart_Data_Exception $e ) {
			$response = self::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		} catch ( \Exception $e ) {
			$response = self::get_error_response( 'cocart_unknown_server_error', $e->getMessage(), 500 );
		}

		$response = is_wp_error( $response ) ? self::error_to_response( $response ) : $response;

		return self::add_response_headers( $response );
	} // END get_response()

	/**
	 * Converts an error to a response object. Based on \WP_REST_Server.
	 *
	 * @access protected
	 *
	 * @since 3.0.0 Introduced.
	 * @since 4.0.0 Added debug backtrace if WP_DEBUG is true.
	 *
	 * @param \WP_Error $error WP_Error instance.
	 *
	 * @return \WP_REST_Response List of associative arrays with code and message keys.
	 */
	protected function error_to_response( $error ) {
		$error_data = $error->get_error_data();
		$status     = isset( $error_data, $error_data['status'] ) ? $error_data['status'] : 500;
		$errors     = array();

		foreach ( (array) $error->errors as $code => $messages ) {
			foreach ( (array) $messages as $message ) {
				$errors[ $code ] = array(
					'code'    => $code,
					'message' => $message,
					'data'    => $error->get_error_data( $code ),
				);

				if ( function_exists( 'debug_backtrace' ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					$errors[ $code ]['trace'] = debug_backtrace();
				}
			}
		}

		$data = array_shift( $errors );

		if ( count( $errors ) ) {
			$data['additional_errors'] = $errors;
		}

		return new \WP_REST_Response( $data, $status );
	} // END error_to_response()

	/**
	 * Get route response when something went wrong.
	 *
	 * @access protected
	 *
	 * @param string $error_code String based error code.
	 * @param string $error_message User facing error message.
	 * @param int    $http_status_code HTTP status. Defaults to 500.
	 * @param array  $additional_data  Extra data (key value pairs) to expose in the error response.
	 *
	 * @return \WP_Error WP Error object.
	 */
	protected function get_error_response( $error_code, $error_message, $http_status_code = 500, $additional_data = array() ) {
		return new \WP_Error( $error_code, $error_message, array_merge( $additional_data, array( 'status' => $http_status_code ) ) );
	} // END get_error_response()

	/**
	 * Adds headers to a response object.
	 *
	 * @access protected
	 *
	 * @param \WP_REST_Response $response The response object.
	 *
	 * @return \WP_REST_Response
	 */
	protected function add_response_headers( \WP_REST_Response $response ) {
		// Add timestamp of response.
		$response->header( 'CoCart-Timestamp', time() );

		// Add version of CoCart.
		$response->header( 'CoCart-Version', COCART_VERSION );

		return $response;
	} // END add_response_headers()

	/**
	 * Retrieves the context param.
	 *
	 * Ensures consistent descriptions between endpoints, and populates enum from schema.
	 *
	 * @access public
	 *
	 * @param array $args Optional. Additional arguments for context parameter. Default empty array.
	 *
	 * @return array Context parameter details.
	 */
	public function get_context_param( $args = array() ) {
		$param_details = array(
			'description'       => __( 'Scope under which the request is made; determines fields present in response.', 'woo-gutenberg-products-block' ),
			'type'              => 'string',
			'sanitize_callback' => 'sanitize_key',
			'validate_callback' => 'rest_validate_request_arg',
		);

		$schema = $this->get_public_item_schema();

		if ( empty( $schema['properties'] ) ) {
			return array_merge( $param_details, $args );
		}

		$contexts = array();

		foreach ( $schema['properties'] as $attributes ) {
			if ( ! empty( $attributes['context'] ) ) {
				$contexts = array_merge( $contexts, $attributes['context'] );
			}
		}

		if ( ! empty( $contexts ) ) {
			$param_details['enum'] = array_unique( $contexts );
			rsort( $param_details['enum'] );
		}

		return array_merge( $param_details, $args );
	} // END get_context_param()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param mixed            $item Item to prepare.
	 * @param \WP_REST_Request $request Request object.
	 *
	 * @return array
	 */
	protected function prepare_links( $item, $request ) {
		return array();
	} // END prepare_links()

	/**
	 * Retrieves the query params for the collections.
	 *
	 * @access public
	 *
	 * @return array Query parameters for the collection.
	 */
	public function get_collection_params() {
		return array(
			'context' => $this->get_context_param(),
		);
	} // END get_collection_params()

} // END class
