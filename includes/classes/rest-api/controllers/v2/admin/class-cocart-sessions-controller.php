<?php
/**
 * REST API: CoCart_REST_Sessions_V2_Controller class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Sessions\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Sessions_V2_Controller', 'CoCart_Sessions_V2_Controller' );

/**
 * Returns a list of carts in session.
 *
 * This REST API controller handles the request to get sessions
 * via "cocart/v2/sessions" endpoint.
 *
 * @since 4.0.0 Introduced.
 * @extends CoCart_REST_Controller
 */
class CoCart_REST_Sessions_V2_Controller extends CoCart_REST_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/sessions';
	}

	/**
	 * Get method arguments for this REST route.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_carts_in_session' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_object_schema' ),
		);
	} // END get_args()

	/**
	 * Route base.
	 *
	 * @deprecated 5.0.0 Replaced with `get_path()` instead.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $rest_base = 'sessions';

	/**
	 * Register the routes for index.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced
	 * @since 3.1.0 Added schema information.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Get Sessions - cocart/v2/sessions (GET).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Check whether a given request has permission to read site data.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! wc_rest_check_manager_permissions( 'settings', 'read' ) ) {
			return new \WP_Error( 'cocart_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'cocart-core' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	} // END get_items_permissions_check()

	/**
	 * Returns carts in session if any exists.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return WP_REST_Response Returns the carts in session from the database.
	 */
	public function get_carts_in_session() {
		try {
			global $wpdb;

			$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				"
				SELECT * 
				FROM {$wpdb->prefix}cocart_carts",
				ARRAY_A
			);

			if ( empty( $results ) ) {
				throw new CoCart_Data_Exception( 'cocart_no_carts_in_session', __( 'No carts in session!', 'cocart-core' ), 404 );
			}

			// Contains the results of sessions.
			$sessions = array();

			foreach ( $results as $key => $cart ) {
				$cart_value = maybe_unserialize( $cart['cart_value'] );
				$customer   = isset( $cart_value['customer'] ) ? maybe_unserialize( $cart_value['customer'] ) : array();

				$email      = ! empty( $customer['email'] ) ? $customer['email'] : '';
				$first_name = ! empty( $customer['first_name'] ) ? $customer['first_name'] : '';
				$last_name  = ! empty( $customer['last_name'] ) ? ' ' . $customer['last_name'] : '';

				if ( ! empty( $first_name ) || ! empty( $last_name ) ) {
					$name = $first_name . $last_name;
				} else {
					$name = '';
				}

				$sessions[] = array(
					'cart_id'         => $cart['cart_id'],
					'cart_key'        => $cart['cart_key'],
					'customers_name'  => $name,
					'customers_email' => $email,
					'cart_created'    => gmdate( 'm/d/Y H:i:s', $cart['cart_created'] ),
					'cart_expiry'     => gmdate( 'm/d/Y H:i:s', $cart['cart_expiry'] ),
					'cart_source'     => $cart['cart_source'],
					'link'            => rest_url( sprintf( '/%s/%s', $this->namespace, 'session/' . $cart['cart_key'] ) ),
				);
			}

			return CoCart_Response::get_response( $sessions, $this->namespace, $this->rest_base );
		} catch ( \CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_carts_in_session()

	/**
	 * Get the schema for returning the sessions.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced
	 *
	 * @return array
	 */
	public function get_public_object_schema() {
		return array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_sessions_v2',
			'type'       => 'object',
			'properties' => array(
				'cart_id'         => array(
					'description' => __( 'Unique identifier for the session.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_key'        => array(
					'description' => __( 'Unique identifier for the customers cart.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'customers_name'  => array(
					'description' => __( 'The name of the customer provided.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'customers_email' => array(
					'description' => __( 'The email the customer provided.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_created'    => array(
					'description' => __( 'The date and time the cart was created, in the site\'s timezone.', 'cocart-core' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_expiry'     => array(
					'description' => __( 'The date and time the cart will expire, in the site\'s timezone.', 'cocart-core' ),
					'type'        => 'string',
					'format'      => 'date-time',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'cart_source'     => array(
					'description' => __( 'Identifies the source of how the cart was made, native or headless.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'link'            => array(
					'description' => __( 'URL to the individual cart in session.', 'cocart-core' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);
	} // END get_public_object_schema()
} // END class
