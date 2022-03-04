<?php
/**
 * Registers all core cart callbacks.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Cart Callbacks class.
 */
class CoCart_Cart_Callbacks {

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
		include_once dirname( __FILE__ ) . '/callbacks/update-cart.php';

		$callback->register( new CoCart_Cart_Update_Callback() );
	} // END register_callback_update_cart()

} // END class

return new CoCart_Cart_Callbacks();
