<?php
/**
 * Class: CoCart_Cart_Cache
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.1.0 Introduced.
 * @version 4.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Cache for Cart REST API.
 *
 * This handles the cart data in cache before the totals are calculated.
 *
 * @since 3.1.0 Introduced.
 */
class CoCart_Cart_Cache {

	/**
	 * Contains an array of cart items cached.
	 *
	 * @access protected
	 *
	 * @var array $cart_contents_cached Stores cached cart items.
	 */
	protected static $cart_contents_cached = array();

	/**
	 * Initiate calculate totals for cached items.
	 *
	 * @access public
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 */
	public function __construct() {
		add_action( 'cocart_after_item_added_to_cart', array( $this, 'set_new_price' ), 1, 2 );
		add_action( 'cocart_after_items_added_to_cart', array( $this, 'set_new_price' ), 1, 2 );
		add_action( 'cocart_item_removed', array( $this, 'remove_cached_item' ), 0, 1 );
		add_action( 'cocart_before_cart_emptied', array( $this, 'clear_cart_cached' ), 0 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'remove_cached_item' ), 99, 1 );
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_cached_items' ), 99, 1 );
		add_filter( 'cocart_cart_item_price', array( $this, 'maybe_return_cached_price' ), 99, 3 );
	} // END __construct()

	/**
	 * Add new price to item if one is requested.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.1.0 Check if the requested product allows the price to be changed.
	 *
	 * @hook: cocart_after_item_added_to_cart
	 * @hook: cocart_after_items_added_to_cart
	 *
	 * @param array           $cart_item Before cart item modified.
	 * @param WP_REST_Request $request   The request object.
	 *
	 * @return array $cart_item After cart item modified.
	 */
	public function set_new_price( $cart_item, $request ) {
		/**
		 * Check if the requested product allows the price to be changed.
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param array           $cart_item Cart item.
		 * @param WP_REST_Request $request   The request object.
		 */
		if ( ! $this->does_product_allow_price_change( $cart_item, $request ) ) {
			return $cart_item;
		}

		$price = isset( $request['price'] ) ? wc_clean( wp_unslash( $request['price'] ) ) : '';

		if ( ! empty( $price ) ) {
			$cart_item['price'] = $price;
		}

		self::set_cached_item( $cart_item['key'], $cart_item );

		return $cart_item;
	} // END set_new_price()

	/**
	 * Removes item from cache to prevent it from calculating
	 * wrong the next time it's added to the cart.
	 *
	 * Or clears all cached items when the cart is cleared.
	 * Uses "WC()->session->set()" and "WC()->session->__unset()"
	 *
	 * @access public
	 *
	 * @param array|string $cart_item_key Cart item key to remove from the cart cache.
	 */
	public function remove_cached_item( $cart_item_key ) {
		// Should the session not be initialized, fail safely to prevent fatal error.
		if ( is_null( WC()->session ) ) {
			return;
		}

		if ( is_array( $cart_item_key ) ) {
			$cart_item_key = $cart_item_key['key'];
		}

		if ( ! empty( $cart_item_key ) ) {
			// Remove item from cache.
			unset( self::$cart_contents_cached[ $cart_item_key ] );

			// Update session.
			if ( ! empty( self::$cart_contents_cached ) ) {
				WC()->session->set( 'cart_cached', maybe_serialize( self::$cart_contents_cached ) );
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
	 *
	 * @param WC_Cart $cart The cart object.
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
	 * Returns the cached price if different,
	 * otherwise simply returns the original value.
	 *
	 * @access public
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @param string|int $price     Product price.
	 * @param array      $cart_item Cart item data.
	 * @param string     $item_key  Item key.
	 *
	 * @return string|int $price Product price
	 */
	public function maybe_return_cached_price( $price, $cart_item, $item_key ) {
		$cart_contents_cached = $this->get_cart_contents_cached();

		// If cart contents is cached, proceed.
		if ( ! empty( $cart_contents_cached ) && is_array( $cart_contents_cached ) ) {
			$product = $cart_item['data']; // Get original product data.

			// If this item is cached then return the new price.
			if ( ! empty( $cart_contents_cached[ $item_key ]['price'] ) ) {
				$price = cocart_prepare_money_response( $cart_contents_cached[ $item_key ]['price'], wc_get_price_decimals() );
			}
		}

		return $price;
	} // END maybe_return_cached_price()

	/**
	 * Gets cart contents cached.
	 *
	 * @access public
	 *
	 * @uses WC()->session->get()
	 *
	 * @return array Cart items cached.
	 */
	public function get_cart_contents_cached() {
		// Should the session not be initialized, fail safely to prevent fatal error.
		if ( is_null( WC()->session ) ) {
			return;
		}

		return maybe_unserialize( WC()->session->get( 'cart_cached' ) );
	} // END get_cart_contents_cached()

	/**
	 * Get a cached cart item.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $item_key Item key to get.
	 *
	 * @return array Value of cart data.
	 */
	public static function get_cached_item( $item_key ) {
		$item_key = sanitize_key( $item_key );

		return isset( self::$cart_contents_cached[ $item_key ] ) ? self::$cart_contents_cached[ $item_key ] : null;
	} // END get_cached_item()

	/**
	 * Set a cart item to cache.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @version 3.7.6
	 *
	 * @uses WC()->session->set()
	 *
	 * @param string $item_key Key to item in cart.
	 * @param mixed  $value    Value to set.
	 */
	public static function set_cached_item( $item_key, $value ) {
		self::$cart_contents_cached = maybe_unserialize( WC()->session->get( 'cart_cached' ) );

		if ( self::get_cached_item( $item_key ) !== $value ) {
			self::$cart_contents_cached[ sanitize_key( $item_key ) ] = $value;
		}

		if ( ! empty( self::$cart_contents_cached ) ) {
			WC()->session->set( 'cart_cached', maybe_serialize( self::$cart_contents_cached ) );
		}
	} // END set_cached_item()

	/**
	 * Clear cart cached.
	 *
	 * @access public
	 *
	 * @uses WC()->session->__unset()
	 */
	public function clear_cart_cached() {
		// Should the session not be initialized, fail safely to prevent fatal error.
		if ( is_null( WC()->session ) ) {
			return;
		}

		WC()->session->__unset( 'cart_cached' );
	} // END clear_cart_cached()

	/**
	 * Returns true if the cart item can be allowed to override the price.
	 *
	 * By default it will always allow overriding the price unless stated otherwise.
	 *
	 * @access protected
	 *
	 * @since 4.1.0 Introduced.
	 *
	 * @param array           $cart_item Cart item.
	 * @param WP_REST_Request $request   The request object.
	 *
	 * @return bool True if the cart item can be allowed to override the price.
	 */
	protected function does_product_allow_price_change( $cart_item, $request ) {
		/**
		 * Filter which products that can be allowed to override the price if not all.
		 *
		 * @since 4.1.0 Introduced.
		 *
		 * @param bool
		 * @param array           $cart_item Cart item.
		 * @param WP_REST_Request $request   The request object.
		 */
		return apply_filters( 'cocart_does_product_allow_price_change', true, $cart_item, $request );
	} // END does_product_allow_price_change()
} // END class

return new CoCart_Cart_Cache();
