<?php
/**
 * Registers all core cart callbacks.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.1.0
 * @license GPL-2.0+
 */

namespace CoCart\CoCart\Callbacks;

use CoCart\CoCart\Callbacks\UpdateCart;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Cart Callbacks class.
 */
class Cart {

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
	 */
	public function register_callback_update_cart( $callback ) {
		include_once COCART_ABSPATH . 'includes/callbacks/update-cart.php';

		$callback->register( new UpdateCart() );
	} // END register_callback_update_cart()

} // END class

return new Cart();
