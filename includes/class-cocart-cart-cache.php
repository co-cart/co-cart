<?php
/**
 * Handles cart data in cache before totals are calculated.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.1.0
 * @version 3.7.6
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Cart_Cache
 */
class CoCart_Cart_Cache {

	/**
	 * Contains an array of cart items cached.
	 *
	 * @access protected
	 * @var    array $_cart_contents_cached - Stores cached cart items.
	 */
	protected static $_cart_contents_cached = array();

	/**
	 * Initiate calculate totals for cached items.
	 *
	 * @access public
	 */
	public function __construct() {
		add_filter( 'cocart_override_cart_item', array( $this, 'set_new_price' ), 1, 2 );
		add_action( 'cocart_item_removed', array( $this, 'remove_cached_item' ), 0, 1 );
		add_action( 'cocart_before_cart_emptied', array( $this, 'clear_cart_cached' ), 0 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_cached_item' ), 99, 1 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_cached_items' ), 99, 1 );
	}

	/**
	 * Add new price to item if one is requested.
	 *
	 * @access public
	 * @param  array           $cart_item - Before cart item modified.
	 * @param  WP_REST_Request $request - Full details about the request.
	 * @return array $cart_item - After cart item modified.
	 */
	public function set_new_price( $cart_item, $request ) {
		$price = isset( $request['price'] ) ? wc_clean( wp_unslash( $request['price'] ) ) : '';

		if ( ! empty( $price ) ) {
			$cart_item['price'] = $price;
		}

		return $cart_item;
	} // END set_new_price()

	/**
	 * Removes item from cache to prevent it from
	 * calculating wrong the next time it's added to the cart.
	 * Or clears all cached items when the cart is cleared.
	 *
	 * @access public
	 * @param  array|string $cart_item_key - Cart item key to remove from the cart cache.
	 */
	public function remove_cached_item( $cart_item_key ) {
		if ( is_array( $cart_item_key ) ) {
			$cart_item_key = $cart_item_key['key'];
		}

		if ( ! empty( $cart_item_key ) ) {
			// Remove item from cache.
			unset( self::$_cart_contents_cached[ $cart_item_key ] );

			// Update session.
			if ( ! empty( self::$_cart_contents_cached ) ) {
				WC()->session->set( 'cart_cached', maybe_serialize( self::$_cart_contents_cached ) );
			} else {
				WC()->session->__unset( 'cart_cached' );
			}
		} else {
			// Clear cache.
			self::clear_cart_cached();
		}
	} // END remove_cached_item()

	/**
	 * Calculate cached items.
	 *
	 * @access public
	 * @param  WC_Cart $cart Cart object.
	 */
	public function calculate_cached_items( $cart ) {
		$cart_contents_cached = $this->get_cart_contents_cached();

		// If cart contents is cached, proceed.
		if ( ! empty( $cart_contents_cached ) && is_array( $cart_contents_cached ) ) {
			foreach ( $cart->get_cart() as $key => $value ) {
				$product = $value['data']; // Get original product data.

				// If this item is cached then look up price difference before setting the new price.
				if ( isset( $cart_contents_cached[ $key ] ) ) {
					if ( isset( $cart_contents_cached[ $key ]['price'] ) && $product->get_price() !== $cart_contents_cached[ $key ]['price'] ) {
						$value['data']->set_price( $cart_contents_cached[ $key ]['price'] );
					}
				}
			}
		}
	} // END calculate_cached_items()

	/**
	 * Gets cart contents cached.
	 *
	 * @access public
	 * @return array of cart items
	 */
	public function get_cart_contents_cached() {
		return maybe_unserialize( WC()->session->get( 'cart_cached' ) );
	} // END get_cart_contents_cached()

	/**
	 * Get a cached cart item.
	 *
	 * @access public
	 * @static
	 * @param  string $item_key Item key to get.
	 * @return array Value of cart data.
	 */
	public static function get_cached_item( $item_key ) {
		$item_key = sanitize_key( $item_key );

		return isset( self::$_cart_contents_cached[ $item_key ] ) ? self::$_cart_contents_cached[ $item_key ] : null;
	} // END get_cached_item()

	/**
	 * Set a cart item to cache.
	 *
	 * @access  public
	 * @since   3.1.0 Introduced.
	 * @version 3.7.6
	 * @static
	 * @param   string $item_key Key to item in cart.
	 * @param   mixed  $value Value to set.
	 */
	public static function set_cached_item( $item_key, $value ) {
		self::$_cart_contents_cached = maybe_unserialize( WC()->session->get( 'cart_cached' ) );

		if ( self::get_cached_item( $item_key ) !== $value ) {
			self::$_cart_contents_cached[ sanitize_key( $item_key ) ] = $value;
		}

		if ( ! empty( self::$_cart_contents_cached ) ) {
			WC()->session->set( 'cart_cached', maybe_serialize( self::$_cart_contents_cached ) );
		}
	} // END set_cached_item()

	/**
	 * Clear cart cached.
	 *
	 * @access public
	 */
	public function clear_cart_cached() {
		WC()->session->__unset( 'cart_cached' );
	} // END clear_cart_cached()

} // END class

return new CoCart_Cart_Cache();
