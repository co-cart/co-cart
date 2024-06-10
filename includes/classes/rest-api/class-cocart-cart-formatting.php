<?php
/**
 * Class: CoCart_Cart_Formatting
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0 Introduced.
 * @version 4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles cart response formatting.
 *
 * @since 3.0.0 Introduced.
 */
class CoCart_Cart_Formatting {

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		// Returns the cart contents without the cart item key as the parent array.
		add_filter( 'cocart_cart', array( $this, 'remove_items_parent_item_key' ), 0 );
		add_filter( 'cocart_cart', array( $this, 'remove_removed_items_parent_item_key' ), 0 );

		// Remove any empty cart item data objects.
		add_filter( 'cocart_cart_item_data', array( $this, 'clean_empty_cart_item_data' ), 0 );
	} // END __construct()

	/**
	 * Returns the cart items values without the cart item key as the parent array.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.11.0
	 *
	 * @param array $cart The cart data before modifying.
	 *
	 * @return array $cart The cart data after modifying.
	 */
	public function remove_items_parent_item_key( $cart ) {
		if ( isset( $cart['items'] ) ) {
			$cart['items'] = array_values( $cart['items'] );
		}

		return $cart;
	} // END remove_items_parent_item_key()

	/**
	 * Returns the removed cart items values without the cart item key as the parent array.
	 *
	 * @access public
	 *
	 * @since   3.0.0 Introduced.
	 * @version 3.11.0
	 *
	 * @param array $cart The cart data before modifying.
	 *
	 * @return array $cart The cart data after modifying.
	 */
	public function remove_removed_items_parent_item_key( $cart ) {
		if ( isset( $cart['removed_items'] ) ) {
			$cart['removed_items'] = array_values( $cart['removed_items'] );
		}

		return $cart;
	} // END remove_removed_items_parent_item_key()

	/**
	 * Remove any empty cart item data objects.
	 *
	 * @access public
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @param array $cart_item_data Cart item data before.
	 *
	 * @return array $cart_item_data Cart item data after.
	 */
	public function clean_empty_cart_item_data( $cart_item_data ) {
		foreach ( $cart_item_data as $item => $data ) {
			if ( empty( $data ) ) {
				unset( $cart_item_data[ $item ] );
			}
		}

		return $cart_item_data;
	} // END clean_empty_cart_item_data()
} // END class.

return new CoCart_Cart_Formatting();
