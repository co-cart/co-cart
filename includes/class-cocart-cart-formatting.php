<?php
/**
 * Handles cart response formatting.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Cart_Formatting' ) ) {

	class CoCart_Cart_Formatting {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			// Returns the cart contents without the cart item key as the parent array.
			add_filter( 'cocart_cart', array( $this, 'remove_items_parent_item_key' ), 0 );
			add_filter( 'cocart_cart', array( $this, 'remove_removed_items_parent_item_key' ), 0 );

			// Remove any empty cart item data objects.
			add_filter( 'cocart_cart_item_data', array( $this, 'clean_empty_cart_item_data' ), 0 );
		}

		/**
		 * Returns the cart contents without the cart item key as the parent array.
		 *
		 * @access  public
		 * @since   3.0.0
		 * @version 3.1.0
		 * @param   array $cart The cart data before modifying.
		 * @return  array $cart The cart data after modifying.
		 */
		public function remove_items_parent_item_key( $cart ) {
			if ( isset( $cart['items'] ) ) {
				$new_items = array();

				foreach ( $cart['items'] as $item_key => $cart_item ) {
					$new_items[] = $cart_item;
				}

				// Override items returned.
				$cart['items'] = $new_items;
			}

			return $cart;
		} // END remove_items_parent_item_key()

		/**
		 * Returns the removed cart contents without the cart item key as the parent array.
		 *
		 * @access  public
		 * @since   3.0.0
		 * @version 3.1.0
		 * @param   array $cart The cart data before modifying.
		 * @return  array $cart The cart data after modifying.
		 */
		public function remove_removed_items_parent_item_key( $cart ) {
			if ( isset( $cart['removed_items'] ) ) {
				$new_items = array();

				foreach ( $cart['removed_items'] as $item_key => $cart_item ) {
					$new_items[] = $cart_item;
				}

				// Override removed items returned.
				$cart['removed_items'] = $new_items;
			}

			return $cart;
		} // END remove_removed_items_parent_item_key()

		/**
		 * Remove any empty cart item data objects.
		 *
		 * @access public
		 * @param  array $cart_item_data Cart item data before.
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

} // END if class exists.

return new CoCart_Cart_Formatting();
