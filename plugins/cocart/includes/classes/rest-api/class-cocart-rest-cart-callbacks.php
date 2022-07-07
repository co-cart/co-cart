<?php
/**
 * Registers all core cart callbacks.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   3.1.0
 * @version 4.0.0
 */

namespace CoCart\RestApi\Callbacks;

use CoCart\RestApi\Callbacks\UpdateCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Cart Callbacks class.
 */
class Callback {

	/**
	 * Register callbacks.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'cocart_register_extension_callback', array( $this, 'register_callback_update_cart' ) );
	} // END __construct()

	/**
	 * Registers callback to update cart.
	 *
	 * @access public
	 * @param CoCart_Cart_Extension $callback Instance of the CoCart_Cart_Extension class.
	 */
	public function register_callback_update_cart( $callback ) {
		include_once COCART_ABSPATH . 'includes/callbacks/update-cart.php';

		$callback->register( new UpdateCart() );
	} // END register_callback_update_cart()

} // END class

return new Callback();