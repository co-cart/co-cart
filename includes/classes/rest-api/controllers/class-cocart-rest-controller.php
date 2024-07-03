<?php
/**
 * Abstract: CoCart_REST_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   4.?.? Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Rest Controller Class.
 *
 * This class extend `WP_REST_Controller`. It's required to follow "Controller Classes" guide
 * before extending this class: <https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/>
 *
 * NOTE THAT ONLY CODE RELEVANT FOR MOST ENDPOINTS SHOULD BE INCLUDED INTO THIS CLASS.
 *
 * @since   4.?.? Introduced.
 * @extends WP_REST_Controller
 * @see     https://developer.wordpress.org/rest-api/extending-the-rest-api/controller-classes/
 */
abstract class CoCart_REST_Controller extends WP_REST_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart';

	/**
	 * Endpoint version.
	 *
	 * @var string
	 */
	protected $version = 'v1';

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = '';

	/**
	 * Used to cache computed return fields.
	 *
	 * @var null|array
	 */
	private $fields = null;

	/**
	 * Used to verify if cached fields are for correct request object.
	 *
	 * @var null|WP_REST_Request
	 */
	private $request = null;

	/**
	 * Constructor.
	 *
	 * @since 4.?.?
	 */
	public function __construct() {
		$this->namespace = $this->namespace . '/' . $this->version;
	}

	/**
	 * Adds the values from additional fields to a data object.
	 *
	 * @access protected
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @param array           $prepared Prepared response array.
	 * @param WP_REST_Request $request  Full details about the request.
	 *
	 * @return array Modified data object with additional fields.
	 */
	protected function add_additional_fields_to_object( $prepared, $request ) {
		$additional_fields = $this->get_additional_fields();

		$requested_fields = $this->get_fields_for_response( $request );

		$excluded_fields = $this->get_excluded_fields_for_response( $request );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['get_callback'] ) {
				continue;
			}

			if ( ! cocart_is_field_included( $field_name, $requested_fields, $excluded_fields ) ) {
				continue;
			}

			$prepared[ $field_name ] = call_user_func( $field_options['get_callback'], $prepared, $field_name, $request, $this->get_object_type() );
		}

		return $prepared;
	} // END add_additional_fields_to_object()

	/**
	 * Add the schema from additional fields to an schema array.
	 *
	 * The type of object is inferred from the passed schema.
	 *
	 * @access protected
	 *
	 * @param array $schema Schema array.
	 *
	 * @return array
	 */
	protected function add_additional_fields_schema( $schema ) {
		if ( empty( $schema['title'] ) ) {
			return $schema;
		}

		/**
		 * Can't use $this->get_object_type otherwise we cause an inf loop.
		 */
		$object_type = $schema['title'];

		$additional_fields = $this->get_additional_fields( $object_type );

		foreach ( $additional_fields as $field_name => $field_options ) {
			if ( ! $field_options['schema'] ) {
				continue;
			}

			$schema['properties'][ $field_name ] = $field_options['schema'];
		}

		$schema['properties'] = apply_filters( 'cocart_' . $object_type . '_schema', $schema['properties'] );

		return $schema;
	} // END add_additional_fields_schema()

	/**
	 * Get normalized rest base.
	 *
	 * @access protected
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @return string
	 */
	protected function get_normalized_rest_base() {
		return preg_replace( '/\(.*\)\//i', '', $this->rest_base );
	} // END get_normalized_rest_base()

	/**
	 * Add meta query.
	 *
	 * @access protected
	 *
	 * @since 3.4.1 Introduced. (Was suppose to be introduced in 3.1.0 but forgot to commit the function until 3.4.1 🤦‍♂️)
	 *
	 * @param array $args       Query args.
	 * @param array $meta_query Meta query.
	 *
	 * @return array
	 */
	protected function add_meta_query( $args, $meta_query ) {
		if ( empty( $args['meta_query'] ) ) {
			$args['meta_query'] = array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query, WPCS: slow query ok.
		}

		$args['meta_query'][] = $meta_query;

		return $args['meta_query'];
	} // END add_meta_query()

	/**
	 * Gets an array of fields to be included on the response.
	 *
	 * Included fields are based on item schema and `_fields=` request argument.
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return array Fields to be included in the response.
	 */
	public function get_fields_for_response( $request ) {
		// From xdebug profiling, this method could take upto 25% of request time in index calls.
		// Cache it and make sure _fields was cached on current request object!
		// TODO: Submit this caching behavior in core.
		if ( isset( $this->fields ) && is_array( $this->fields ) && $request === $this->request ) {
			return $this->fields;
		}

		$this->request = $request;

		// Get default properties from schema.
		$schema     = $this->get_item_schema();
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		// Override properties if any different.
		$properties = $this->get_properties_for_fields( $properties, $request );

		$additional_fields = $this->get_additional_fields();

		if ( ! empty( $additional_fields ) ) {
			foreach ( $additional_fields as $field_name => $field_options ) {
				/**
				 * For back-compat, include any field with an empty schema
				 * because it won't be present in $this->get_item_schema().
				 */
				if ( is_null( $field_options['schema'] ) ) {
					$properties[ $field_name ] = $field_options;
				}
			}

			// Exclude fields that specify a different context than the request context.
			$context = $request['context'];
			if ( $context ) {
				foreach ( $properties as $name => $options ) {
					if ( ! empty( $options['context'] ) && ! in_array( $context, $options['context'], true ) ) {
						unset( $properties[ $name ] );
					}
				}
			}
		}

		$fields = array_unique( array_keys( $properties ) );

		if ( ! isset( $request['_fields'] ) ) {
			$this->fields = $fields;
			return $fields;
		}

		$requested_fields = wp_parse_list( $request['_fields'] );

		if ( 0 === count( $requested_fields ) ) {
			$this->fields = $fields;
			return $fields;
		}

		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		// Always persist 'id', because it can be needed for add_additional_fields_to_object().
		if ( in_array( 'id', $fields, true ) ) {
			$requested_fields[] = 'id';
		}

		// Always persist 'parent_id' if variations is included without parent product, because it can be needed for add_additional_fields_to_object().
		if ( in_array( 'parent_id', $fields, true ) && $request['include_variations'] ) {
			$requested_fields[] = 'parent_id';
		}

		// Return the list of all requested fields which appear in the schema.
		$this->fields = array_reduce(
			$requested_fields,
			function ( $response_fields, $field ) use ( $fields ) {
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

		return $this->fields;
	} // END get_fields_for_response()

	/**
	 * Gets an array of fields to be excluded on the response.
	 *
	 * Excluded fields are based on item schema and `_exclude_fields=` request argument.
	 *
	 * @access public
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @TODO: Submit this function in core.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return string Fields to be excluded in the response.
	 */
	public function get_excluded_fields_for_response( $request ) {
		// Get default properties from schema.
		$schema     = $this->get_item_schema();
		$properties = isset( $schema['properties'] ) ? $schema['properties'] : array();

		// Override properties if any different.
		$properties = $this->get_properties_for_fields( $properties, $request );

		$fields = array_unique( array_keys( $properties ) );

		if ( empty( $request['_exclude_fields'] ) ) {
			return array();
		}

		$requested_fields = wp_parse_list( $request['_exclude_fields'] );

		// Return all fields if no fields specified.
		if ( 0 === count( $requested_fields ) ) {
			return $fields;
		}

		// Trim off outside whitespace from the comma delimited list.
		$requested_fields = array_map( 'trim', $requested_fields );

		// Return the list of all requested fields which appear in the schema.
		return array_reduce(
			$requested_fields,
			static function ( $response_fields, $field ) use ( $fields ) {
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
	} // END get_excluded_fields_for_response()

	/**
	 * Retrieves the item's schema for display / public consumption purposes.
	 *
	 * Should only be overridden if the endpoint is returning results paginated
	 * and the items are returned as children.
	 *
	 * @access public
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_items_schema() {
		return array();
	} // END get_public_items_schema()

	/**
	 * Should only be overridden in the controller to return new properties.
	 *
	 * @access public
	 *
	 * @since 4.?.? Introduced.
	 *
	 * @param array           $properties Original properties.
	 * @param WP_REST_Request $request    The request object.
	 *
	 * @return array $properties
	 */
	public function get_properties_for_fields( $properties, $request ) {
		return $properties;
	} // END get_properties_for_fields()
} // END class
