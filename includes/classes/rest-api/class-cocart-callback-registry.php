<?php
/**
 * Class: CoCart_Callback_Registry
 *
 * Manages the registration of callbacks.
 *
 * @package CoCart\Classes
 * @since   3.1.0 Introduced.
 * @version 4.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart Callback Registry.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_Callback_Registry {

	/**
	 * Registered Callbacks.
	 *
	 * @access protected
	 *
	 * @var array $registered_callbacks Registered callbacks.
	 */
	protected static $registered_callbacks = array();

	/**
	 * Singleton instance.
	 *
	 * @access private
	 *
	 * @var CoCart_Callback_Registry
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @access public
	 *
	 * @return CoCart_Callback_Registry
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize class.
	 *
	 * @access public
	 */
	public function __construct() {
		/**
		 * Hook: cocart_register_extension_callback.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @deprecated 5.0.0 No replacement.
		 *
		 * @param CoCart_Cart_Extension $cart_extension Instance of the CoCart_Cart_Extension class which exposes the CoCart_Cart_Extension::register() method.
		 */
		cocart_do_deprecated_action( 'cocart_register_extension_callback', '5.0.0', null );
	} // END __construct()

	/**
	 * Registers a callback.
	 *
	 * @access public
	 *
	 * @param string $callback An instance of the callback class.
	 *
	 * @return boolean True means registered successfully.
	 */
	public function register( $callback ) {
		$name = $callback->get_name();

		if ( $this->is_registered( $name ) ) {
			_doing_it_wrong(
				__METHOD__,
				esc_html(
					sprintf(
						/* translators: %s: Callback name. */
						__( '"%s" is already registered.', 'cocart-core' ),
						$name
					)
				),
				'3.1.0'
			);
			return false;
		}

		self::$registered_callbacks[ $name ] = $callback;

		return true;
	} // END register()

	/**
	 * Checks if a callback is already registered.
	 *
	 * @access public
	 *
	 * @param string $name Callback name.
	 *
	 * @return bool True if the callback is registered, false otherwise.
	 */
	public function is_registered( $name ) {
		return isset( self::$registered_callbacks[ $name ] );
	} // END is_registered()

	/**
	 * Retrieves all registered callbacks.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function get_all_registered_callbacks() {
		return self::$registered_callbacks;
	} // END get_all_registered_callbacks()
} // END class
