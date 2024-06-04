<?php
/**
 * Class: CoCart_Cart_Callbacks
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all core cart callbacks.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_Cart_Callbacks {

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
	 * @param CoCart_Cart_Extension $callback Instance of the CoCart_Cart_Extension class.
	 */
	public function register_callback_update_cart( $callback ) {
		include_once __DIR__ . '/callbacks/update-cart.php';

		$callback->register( new CoCart_Cart_Update_Callback() );
	} // END register_callback_update_cart()

	/**
	 * Registers callback to update customer.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @param CoCart_Cart_Extension $callback Instance of the CoCart_Cart_Extension class.
	 */
	public function register_callback_update_customer( $callback ) {
		include_once __DIR__ . '/callbacks/update-customer.php';

		$callback->register( new CoCart_Update_Customer_Callback() );
	} // END register_callback_update_customer()
} // END class

return new CoCart_Cart_Callbacks();
