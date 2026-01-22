<?php
/**
 * REST API: CoCart_REST_Count_Items_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Cart\v2
 * @since   3.0.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Count_Items_V2_Controller', 'CoCart_Count_Items_V2_Controller' );

/**
 * Controller for counting items in the cart (API v2).
 *
 * This REST API controller handles the request to count the items
 * in the cart via "cocart/v2/cart/items/count" endpoint.
 *
 * @since 3.0.0 Introduced.
 *
 * @see CoCart_REST_Cart_V2_Controller
 */
class CoCart_REST_Count_Items_V2_Controller extends CoCart_REST_Cart_V2_Controller {

	/**
	 * Route base. - Replaced with `get_path()`
	 *
	 * @var string
	 */
	protected $rest_base = 'cart/items/count';

	/**
	 * Get the path of this rest route.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/cart/items/count';
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
				'callback'            => array( $this, 'get_cart_contents_count' ),
				'permission_callback' => '__return_true',
				'args'                => $this->get_collection_params(),
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_item_schema' ),
		);
	} // END get_args()

	/**
	 * Register routes.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Count Items in Cart - cocart/v2/cart/items/count (GET).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Get cart contents count.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @since 1.0.0 Introduced.
	 * @since 5.0.0 Return parameter now returns numeric as default not empty.
	 *
	 * @param WP_REST_Request $request       The request object.
	 * @param array           $cart_contents Cart contents to count items.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_cart_contents_count( $request, $cart_contents = array() ) {
		try {
			$return        = ! empty( $request['return'] ) ? $request['return'] : 'numeric';
			$removed_items = isset( $request['removed_items'] ) ? $request['removed_items'] : false;

			if ( empty( $cart_contents ) ) {
				// Return count for removed items in cart.
				if ( isset( $request['removed_items'] ) && is_bool( $request['removed_items'] ) && $request['removed_items'] ) {
					$count = $this->get_removed_cart_contents_count();
				} else {
					// Return count for items in cart.
					$count = $this->get_cart_instance()->get_cart_contents_count();
				}
			} else {
				// Counts all items from the quantity variable.
				$count = array_sum( wp_list_pluck( $cart_contents, 'quantity' ) );
			}

			if ( 'numeric' !== $return ) {
				if ( $count <= 0 ) {
					$message = __( 'No items in the cart.', 'cocart-core' );

					/**
					 * Filters message about no items in the cart.
					 *
					 * @since 2.1.0 Introduced.
					 *
					 * @param string $message Message.
					 */
					$message = apply_filters( 'cocart_no_items_in_cart_message', $message );

					throw new CoCart_Data_Exception( 'cocart_no_items_in_cart', $message, 404 );
				} else {
					$count = sprintf(
						/* Translators: %d = Number of items. */
						__( 'There are %d items in the cart.', 'cocart-core' ),
						$count
					);
				}
			}

			$response = rest_ensure_response( $count );
			$response = ( new CoCart_REST_Utilities_Cart_Response() )->add_headers( $response, $request );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
		}
	} // END get_cart_contents_count()

	/**
	 * Retrieves the item schema for returning the item count.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return array Public item schema data.
	 */
	public function get_public_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_cart_count_items',
			'type'       => 'object',
			'properties' => array(
				'removed_items' => array(
					'required'          => false,
					'default'           => false,
					'description'       => __( 'Returns count for removed items from the cart.', 'cocart-core' ),
					'type'              => 'boolean',
					'sanitize_callback' => 'rest_sanitize_boolean',
					'validate_callback' => 'rest_validate_request_arg',
				),
			),
		);

		return $schema;
	} // END get_public_item_schema()

	/**
	 * Get the query params for counting items.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 * @since 5.0.0 Return parameter is internal and returns if debug is enabled.
	 *
	 * @return array $params
	 */
	public function get_collection_params() {
		// Cart query parameters.
		$params = parent::get_collection_params();

		// Count Items parameters.
		$params['removed_items'] = array(
			'description'       => __( 'Set as true to count items removed from the cart.', 'cocart-core' ),
			'required'          => false,
			'default'           => false,
			'type'              => 'boolean',
			'sanitize_callback' => 'rest_sanitize_boolean',
			'validate_callback' => 'rest_validate_request_arg',
		);

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$params['return'] = array(
				'description'       => __( 'Internal parameter. No description.', 'cocart-core' ),
				'required'          => false,
				'default'           => 'numeric',
				'enum'              => array(
					'numeric',
					'string',
				),
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => 'rest_validate_request_arg',
			);
		}

		return $params;
	} // END get_collection_params()
} // END class
