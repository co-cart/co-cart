<?php
/**
 * CoCart - Item controller
 *
 * Handles the request to view a single item in the cart with /cart/item endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\v2
 * @since   3.0.0
 * @version 3.0.17
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API View individual item controller class.
 *
 * @package CoCart\API
 * @extends CoCart_Item_Controller
 */
class CoCart_Item_v2_Controller extends CoCart_Item_Controller {

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
	protected $rest_base = 'cart/item';

	/**
	 * Register routes.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Item - cocart/v2/cart/item/6364d3f0f495b6ab9dcf8d3b5c6e0b01 (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<item_key>[\w]+)',
			array(
				'args' => $this->get_collection_params(),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'view_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	} // register_routes()

	/**
	 * View Item in Cart.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.0.4
	 * @param   array $request
	 * @return  WP_REST_Response
	 */
	public function view_item( $request = array() ) {
		try {
			$item_key = ! isset( $request['item_key'] ) ? '' : sanitize_text_field( wp_unslash( wc_clean( $request['item_key'] ) ) );

			$controller = new CoCart_Cart_V2_Controller();

			$cart_contents = ! $controller->get_cart_instance()->is_empty() ? array_filter( $controller->get_cart_instance()->get_cart() ) : array();

			$item = $controller->get_items( $cart_contents );

			$item = isset( $item[ $item_key ] ) ? $item[ $item_key ] : false;

			// If item is not found, throw exception error.
			if ( ! $item ) {
				throw new CoCart_Data_Exception( 'cocart_item_not_found', __( 'Item specified was not found in cart.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			return CoCart_Response::get_response( $item, $this->namespace, $this->rest_base );
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END view_item()

	/**
	 * Get the query params for item.
	 *
	 * @access  public
	 * @since   3.0.0
	 * @version 3.0.17
	 * @return  array $params
	 */
	public function get_collection_params() {
		$controller = new CoCart_Cart_V2_Controller();

		$params = array_merge(
			$controller->get_collection_params(),
			array(
				'item_key' => array(
					'description'       => __( 'Unique identifier for the item in the cart.', 'cart-rest-api-for-woocommerce' ),
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				),
			)
		);

		return $params;
	} // END get_collection_params()

} // END class
