<?php
/**
 * CoCart - Cart Extension.
 *
 * Allows developers to extend CoCart by allowing to update the cart via custom callback.
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
 * CoCart Cart Extension class.
 */
class CoCart_Cart_Extension {

	/**
	 * Registered Callbacks.
	 *
	 * @access protected
	 * @var    array $registered_callbacks - Registered callbacks.
	 */
	protected $registered_callbacks = array();

	/**
	 * Setup class.
	 *
	 * @access public
	 */
	public function __construct() {
		$this->init();
	} // END __construct()

	/**
	 * Initialize Callbacks.
	 *
	 * @access protected
	 */
	protected function init() {
		/**
		 * Hook: cocart_register_extension_callback.
		 *
		 * @param CoCart_Cart_Extension $this Instance of the CoCart_Cart_Extension class which exposes the CoCart_Cart_Extension::register() method.
		 */
		do_action( 'cocart_register_extension_callback', $this );
	} // END init()

	/**
	 * Registers a callback.
	 *
	 * @access public
	 * @param  string $callback An instance of the callback class.
	 * @return boolean True means registered successfully.
	 */
	public function register( $callback ) {
		$name = $callback->get_name();

		if ( $this->is_registered( $name ) ) {
			/* translators: %s: Callback name. */
			_doing_it_wrong( __METHOD__, esc_html( sprintf( __( '"%s" is already registered.', 'cart-rest-api-for-woocommerce' ), $name ) ) );
			return false;
		}

		$this->registered_callbacks[ $name ] = $callback;

		return true;
	} // END register()

	/**
	 * Checks if a callback is already registered.
	 *
	 * @access public
	 * @param  string $name Callback name.
	 * @return bool   True if the callback is registered, false otherwise.
	 */
	public function is_registered( $name ) {
		return isset( $this->registered_callbacks[ $name ] );
	} // END is_registered()

	/**
	 * Retrieves all registered callbacks.
	 *
	 * @access public
	 * @return array
	 */
	public function get_all_registered_callbacks() {
		return $this->registered_callbacks;
	} // END get_all_registered_callbacks()

} // END class

return new CoCart_Cart_Extension();
