<?php
/**
 * REST API: CoCart\RestApi\Callbacks\Callback.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart\RestApi\Callbacks;

use CoCart\RestApi\Callbacks\UpdateCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all core cart callbacks.
 *
 * @since 3.1.0 Introduced.
 */
class Callback {

	/**
	 * Register callbacks.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_action( 'cocart_register_extension_callback', array( $this, 'register_callback_update_cart' ) );
		add_action( 'cocart_register_extension_callback', array( $this, 'register_callback_update_customer' ) );
	} // END __construct()

	/**
	 * Registers callback to update cart.
	 *
	 * @access public
	 *
	 * @param CoCart\RestApi\CartExtension $callback Instance of the CoCart\RestApi\CartExtension class.
	 */
	public function register_callback_update_cart( $callback ) {
		include_once COCART_ABSPATH . 'includes/callbacks/update-cart.php';

		$callback->register( new UpdateCart() );
	} // END register_callback_update_cart()

	/**
	 * Registers callback to update customer.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param CoCart\RestApi\CartExtension $callback Instance of the CoCart\RestApi\CartExtension class.
	 */
	public function register_callback_update_customer( $callback ) {
		include_once COCART_ABSPATH . 'includes/callbacks/update-customer.php';

		$callback->register( new UpdateCustomer() );
	} // END register_callback_update_customer()

} // END class

return new Callback();
