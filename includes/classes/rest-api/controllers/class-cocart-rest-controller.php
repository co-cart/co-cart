<?php
/**
 * CoCart - REST Controller
 *
 * Shared base class for all controllers.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RESTAPI\Controllers
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core base controller for managing and interacting with REST API.
 */
abstract class CoCart_REST_Controller extends WP_REST_Controller {

	/**
	 * The namespace of this controller's route.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart';

	/**
	 * The base of this controller's route.
	 *
	 * @var string
	 */
	protected $rest_base;

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Get the path regex for this REST route.
	 *
	 * @return string Path regex.
	 */
	public function get_path_regex() {
		_doing_it_wrong(
			'CoCart_REST_Controller::get_path_regex',
			/* translators: %s: get_path_regex() */
			sprintf( __( "Method '%s' must be overridden.", 'cocart-core' ), __METHOD__ ),
			'5.0.0'
		);
	} // END get_path_regex()

	/**
	 * Get the path of this REST route.
	 *
	 * @return string
	 */
	public function get_path() {
		$path = self::get_path_regex();

		if ( ! empty( $path ) ) {
			$path = ltrim( $path, '/' );
		}

		return $path;
	} // END get_path()

	/**
	 * Get the version of this controller's route.
	 */
	public function get_version() {
		return $this->version;
	} // END get_version()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array Method arguments.
	 */
	public function get_args() {
		_doing_it_wrong(
			'CoCart_REST_Controller::get_args',
			/* translators: %s: get_args() */
			sprintf( __( "Method '%s' must be overridden.", 'cocart-core' ), __METHOD__ ),
			'5.0.0'
		);

		// Return empty array instead of null to prevent foreach errors.
		return array();
	} // END get_args()

	/**
	 * Constructor.
	 *
	 * Initializes the namespace and rest_base properties based on route's path.
	 */
	public function __construct() {
		$this->namespace = CoCart::get_api_namespace();

		$this->rest_base = '/' . $this->namespace . '/' . $this->get_version() . '/' . $this->get_path();
	} // END __construct()

	/**
	 * Check if the user has permission to access the route.
	 *
	 * If not overridden, it will allow access to the route by default. Override this method to implement custom permission checks.
	 *
	 * @access public
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function check_permission( $request ) {
		_doing_it_wrong(
			'CoCart_REST_Controller::check_permission',
			/* translators: %s: check_permission() */
			sprintf( __( "Method '%s' must be overridden.", 'cocart-core' ), __METHOD__ ),
			'5.0.0'
		);

		return true;
	} // END check_permission()

	/**
	 * Prepare links for the request.
	 *
	 * @access protected
	 *
	 * @param mixed            $item    Item to prepare.
	 * @param \WP_REST_Request $request Full details about the request.
	 *
	 * @return array
	 */
	protected function prepare_links( $item, $request ) {
		return array();
	} // END prepare_links()

	/**
	 * Build REST URL path using sprintf formatting.
	 *
	 * @access protected
	 *
	 * @param string $path_template Path template with placeholders (e.g., 'products/%d/variations/%d').
	 * @param array  $values        Values to replace placeholders.
	 *
	 * @return string The complete REST URL path with namespace and leading slash.
	 */
	protected function build_rest_path( $path_template, $values = array() ) {
		$path = empty( $values ) ? $path_template : sprintf( $path_template, ...$values );
		return sprintf( '/%s/%s/%s', $this->namespace, $this->get_version(), ltrim( $path, '/' ) );
	} // END build_rest_path()

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

	/**
	 * Get the item schema, conforming to JSON Schema.
	 *
	 * Provides a basic schema structure that child classes should override.
	 * This prevents WordPress's get_fields_for_response() from returning null.
	 *
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart',
			'type'       => 'object',
			'properties' => array(),
		);

		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()
} // END class
