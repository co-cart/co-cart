<?php
/**
 * REST API:Cart Cache class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\RestApi
 * @since   3.1.0
 * @version 4.0.0
 */

namespace CoCart\RestApi;

use CoCart\Logger;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cart Cache for Cart REST API.
 *
 * This handles the cart data in cache before the totals are calculated.
 *
 * @since 3.1.0
 */
class CartCache {

	/**
	 * Contains an array of cart items cached.
	 *
	 * @access protected
	 * @var    array $_cart_contents_cached Stores cached cart items.
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
	 *
	 * @since 3.1.0 Introduced.
	 * @since 4.0.0 Added security check if the item is allowed for the price to be changed.
	 *
	 * @param array           $cart_item Before cart item modified.
	 * @param WP_REST_Request $request   Full details about the request.
	 *
	 * @return array $cart_item After cart item modified.
	 */
	public function set_new_price( $cart_item, $request ) {
		/**
		 * Check if we require a salt key to match before allowing to continue with request.
		 *
		 * @since 4.0.0 Introduced.
		 */
		if ( ! empty( self::maybe_cocart_require_salt() ) ) {
			$default = true;

			if ( $request->get_header( 'csaltk' ) !== self::maybe_cocart_require_salt() ) {
				Logger::log( __( 'An attempt was made to override the price of an item but the salt key did not match.', 'cart-rest-api-for-woocommerce' ), 'alert' );
			} else {
				$default = false;
			}

			if ( $default ) {
				return $cart_item;
			}
		}

		/**
		 * Check if we allow to change the price of the item.
		 *
		 * @since 4.0.0 Introduced.
		 *
		 * @param array $cart_item Cart item.
		 * @param array $request   Full details about the request.
		 */
		if ( ! self::is_allowed_to_override_price( $cart_item, $request ) ) {
			return $cart_item;
		}

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
	 * @uses WC()->session->set()
	 * @uses WC()->session->__unset()
	 *
	 * @access public
	 *
	 * @param array|string $cart_item_key Cart item key to remove from the cart cache.
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
	 *
	 * @param WC_Cart $cart Cart object.
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
	 * @uses WC()->session->get()
	 *
	 * @access public
	 *
	 * @return array Cart items cached.
	 */
	public function get_cart_contents_cached() {
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

		return isset( self::$_cart_contents_cached[ $item_key ] ) ? self::$_cart_contents_cached[ $item_key ] : null;
	} // END get_cached_item()

	/**
	 * Set a cart item to cache.
	 *
	 * @uses WC()->session->set()
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $item_key Key to item in cart.
	 * @param mixed  $value    Value to set.
	 */
	public static function set_cached_item( $item_key, $value ) {
		if ( self::get_cached_item( $item_key ) !== $value ) {
			self::$_cart_contents_cached[ sanitize_key( $item_key ) ] = $value;
		}

		WC()->session->set( 'cart_cached', maybe_serialize( self::$_cart_contents_cached ) );
	} // END set_cached_item()

	/**
	 * Clear cart cached.
	 *
	 * @uses WC()->session->__unset()
	 *
	 * @access public
	 */
	public function clear_cart_cached() {
		WC()->session->__unset( 'cart_cached' );
	} // END clear_cart_cached()

	/**
	 * Returns true if the cart item can be allowed to override the price.
	 *
	 * By default it will always allow overriding the price unless stated otherwise.
	 *
	 * @access protected
	 *
	 * @param array           $cart_item Cart item.
	 * @param WP_REST_Request $request   Full details about the request.
	 *
	 * @return bool True if the cart item can be allowed to override the price.
	 */
	protected function is_allowed_to_override_price( $cart_item, $request ) {
		return apply_filters( 'cocart_' . __FUNCTION__, true, $cart_item, $request );
	} // END is_allowed_to_override_price()

	/**
	 * Returns the salt key for CoCart if defined.
	 *
	 * Used to help prevent session hijacking.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return mixed The salt key for CoCart or false if not defined.
	 */
	protected function maybe_cocart_require_salt() {
		/**
		 * Check if the salt key is defined.
		 * Should be hashed already to remain secure.
		 */
		if ( defined( 'COCART_SALT_KEY' ) ) {
			return COCART_SALT_KEY;
		}

		$settings = get_option( 'cocart_settings', array() );

		$salt_key = ! empty( $settings['general']['salt_key'] ) ? $settings['general']['salt_key'] : '';

		if ( ! empty( $salt_key ) ) {
			return $salt_key;
		}

		return false;
	} // END maybe_cocart_require_salt()

} // END class

return new CartCache();
