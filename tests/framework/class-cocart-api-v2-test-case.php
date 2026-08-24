<?php
/**
 * CoCart API v2 Test Case
 *
 * Provides CoCart API v2-specific testing functionality.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * CoCart API v2 Test Case Class
 *
 * Provides helpers and assertions for testing CoCart API v2 endpoints.
 *
 * @package CoCart\Tests\Framework
 */
abstract class CoCart_API_V2_Test_Case extends CoCart_API_Test_Case {

	/**
	 * Set up test environment.
	 *
	 * Ensures CoCart v2 API is available before running tests.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure CoCart v2 API is available.
		if ( ! class_exists( 'CoCart' ) ) {
			$this->markTestSkipped( 'CoCart is not available.' );
		}

		// Prevent wp_logout() from calling setcookie(), which fails in PHPUnit
		// because headers have already been sent by the test bootstrap.
		add_filter( 'send_auth_cookies', '__return_false' );
	}

	/**
	 * Make a request to the CoCart v2 API.
	 *
	 * @param string $method   HTTP method (GET, POST, PUT, DELETE).
	 * @param string $endpoint API endpoint (relative to /cocart/v2/).
	 * @param array  $params   Request parameters.
	 * @param array  $headers  Request headers.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function cocart_v2_request( $method, $endpoint, $params = array(), $headers = array() ) {
		$route = '/cocart/v2/' . ltrim( $endpoint, '/' );
		return $this->rest_request( $method, $route, $params, $headers );
	}

	/**
	 * Get cart via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'cart', $params );
	}

	/**
	 * Add item to cart via CoCart v2 API.
	 *
	 * @param int   $product_id Product ID to add.
	 * @param int   $quantity   Quantity to add.
	 * @param array $params     Optional. Additional parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function add_item_to_cart( $product_id, $quantity = 1, $params = array() ) {
		$request_params = array_merge( array(
			'id'       => (string) $product_id,
			'quantity' => (string) $quantity,
		), $params );

		return $this->cocart_v2_request( 'POST', 'cart/add-item', $request_params );
	}

	/**
	 * Add multiple items to cart via CoCart v2 API.
	 *
	 * @param array $items  Array of items to add (each item is an array of product data).
	 * @param array $params Optional. Additional parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function add_items_to_cart( $items, $params = array() ) {
		$request_params = array_merge( array(
			'items' => $items,
		), $params );

		return $this->cocart_v2_request( 'POST', 'cart/add-items', $request_params );
	}

	/**
	 * Remove item from cart via CoCart v2 API.
	 *
	 * @param string $item_key Item key to remove.
	 * @param array  $params   Optional. Additional parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function remove_item_from_cart( $item_key, $params = array() ) {
		return $this->rest_request( 'DELETE', '/cocart/v2/cart/item/' . $item_key, $params );
	}

	/**
	 * Update item in cart via CoCart v2 API.
	 *
	 * @param string $item_key Item key to update.
	 * @param int    $quantity New quantity.
	 * @param array  $params   Optional. Additional parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function update_item_in_cart( $item_key, $quantity, $params = array() ) {
		$request_params = array_merge( array( 'quantity' => (string) $quantity ), $params );
		return $this->rest_request( 'POST', '/cocart/v2/cart/item/' . $item_key, $request_params );
	}

	/**
	 * Clear cart via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function clear_cart( $params = array() ) {
		return $this->cocart_v2_request( 'POST', 'cart/clear', $params );
	}

	/**
	 * Get cart totals via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_totals( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'cart/totals', $params );
	}

	/**
	 * Get cart count via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_count( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'cart/items/count', $params );
	}

	/**
	 * Count items in cart via CoCart v2 API.
	 *
	 * Alias for get_cart_count().
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function count_items_in_cart( $params = array() ) {
		return $this->get_cart_count( $params );
	}

	/**
	 * Calculate cart via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function calculate_cart( $params = array() ) {
		return $this->cocart_v2_request( 'POST', 'cart/calculate', $params );
	}

	/**
	 * Update cart via CoCart v2 API.
	 *
	 * The `cart/update` route registers its handler as CREATABLE (POST),
	 * not PUT.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function update_cart( $params = array() ) {
		return $this->cocart_v2_request( 'POST', 'cart/update', $params );
	}

	/**
	 * Get cart item via CoCart v2 API.
	 *
	 * @param string $item_key Item key to retrieve.
	 * @param array  $params   Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_item( $item_key, $params = array() ) {
		$request_params = array_merge( array(
			'item_key' => $item_key,
		), $params );

		return $this->cocart_v2_request( 'GET', 'cart/item', $request_params );
	}

	/**
	 * Get cart items via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_items( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'cart/items', $params );
	}

	/**
	 * Restore item via CoCart v2 API.
	 *
	 * @param string $item_key Item key to restore.
	 * @param array  $params   Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function restore_item( $item_key, $params = array() ) {
		return $this->cocart_v2_request( 'PUT', 'cart/item/' . $item_key, $params );
	}

	/**
	 * Login via CoCart v2 API.
	 *
	 * @param array $params  Login parameters (username, password, etc).
	 * @param array $headers Optional. Request headers (e.g., Authorization).
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function login( $params = array(), $headers = array() ) {
		return $this->cocart_v2_request( 'POST', 'login', $params, $headers );
	}

	/**
	 * Logout via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function logout( $params = array() ) {
		return $this->cocart_v2_request( 'POST', 'logout', $params );
	}

	/**
	 * Get current user session via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_a_single_session( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'session', $params );
	}

	/**
	 * Get products via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_products( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products', $params );
	}

	/**
	 * Get single product via CoCart v2 API.
	 *
	 * Accepts either a numeric product ID or a product slug — the community
	 * `products/(?P<id>[\w-]+)` route resolves both through the same handler.
	 *
	 * @param int|string $product_id Product ID or slug to retrieve.
	 * @param array      $params     Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product( $product_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/' . $product_id, $params );
	}

	/**
	 * Get product categories via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_categories( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/categories', $params );
	}

	/**
	 * Get product attributes via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_attributes( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/attributes', $params );
	}

	/**
	 * Get product variations via CoCart v2 API.
	 *
	 * @param int   $product_id Product ID to get variations for.
	 * @param array $params     Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_variations( $product_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/' . $product_id . '/variations', $params );
	}

	/**
	 * Get store info via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_store_info( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'store', $params );
	}

	/**
	 * Make authenticated request to admin endpoints (v2).
	 *
	 * @param string $method   HTTP method.
	 * @param string $endpoint API endpoint (relative to /cocart/v2/).
	 * @param array  $params   Request parameters.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function authenticated_admin_request( $method, $endpoint, $params = array(), $key_data = null ) {
		if ( ! $key_data ) {
			$key_data = $this->create_wc_api_key();
		}

		$headers = $this->authenticate_with_wc_api_key( $key_data );

		return $this->cocart_v2_request( $method, $endpoint, $params, $headers );
	}

	/**
	 * Get sessions via CoCart v2 API (requires admin authentication).
	 *
	 * @param array $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_sessions( $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', 'sessions', array(), $key_data );
	}

	/**
	 * Get specific session via CoCart v2 API (requires admin authentication).
	 *
	 * @param string $cart_key Cart key to retrieve.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_session_by_key( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', 'session/' . $cart_key, array(), $key_data );
	}

	/**
	 * Delete a specific session via CoCart v2 API (requires admin authentication).
	 *
	 * @param string $cart_key Cart key of the session to delete.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function delete_session_by_key( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'DELETE', 'session/' . $cart_key, array(), $key_data );
	}

	/**
	 * Get items in a specific session via CoCart v2 API (requires admin authentication).
	 *
	 * @param string $cart_key Cart key of the session.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_session_items_by_key( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', 'session/' . $cart_key . '/items', array(), $key_data );
	}

	/**
	 * Get a single product tag via CoCart v2 API.
	 *
	 * @param int   $tag_id Tag ID.
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_tag( $tag_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/tags/' . $tag_id, $params );
	}

	/**
	 * Get product tags via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_tags( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/tags', $params );
	}

	/**
	 * Get a single product category via CoCart v2 API.
	 *
	 * @param int   $cat_id Category ID.
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_category( $cat_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/categories/' . $cat_id, $params );
	}

	/**
	 * Get product reviews via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_reviews( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/reviews', $params );
	}

	/**
	 * Get a single product review via CoCart v2 API.
	 *
	 * @param int   $review_id Review ID.
	 * @param array $params    Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_review( $review_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/reviews/' . $review_id, $params );
	}

	/**
	 * Get a single product attribute via CoCart v2 API.
	 *
	 * @param int   $attribute_id Attribute ID.
	 * @param array $params       Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_attribute( $attribute_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/attributes/' . $attribute_id, $params );
	}

	/**
	 * Get attribute terms via CoCart v2 API.
	 *
	 * @param int   $attribute_id Attribute ID.
	 * @param array $params       Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_attribute_terms( $attribute_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/attributes/' . $attribute_id . '/terms', $params );
	}

	/**
	 * Get a single attribute term via CoCart v2 API.
	 *
	 * @param int   $attribute_id Attribute ID.
	 * @param int   $term_id      Term ID.
	 * @param array $params       Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_attribute_term( $attribute_id, $term_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/attributes/' . $attribute_id . '/terms/' . $term_id, $params );
	}

	/**
	 * Get a single product variation via CoCart v2 API.
	 *
	 * @param int   $product_id   Parent product ID.
	 * @param int   $variation_id Variation ID.
	 * @param array $params       Optional. Request parameters.
	 * @return WP_REST_Response
	 */
	protected function get_product_variation( $product_id, $variation_id, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/' . $product_id . '/variations/' . $variation_id, $params );
	}

	/**
	 * Assert that cart is empty (v2).
	 *
	 * Asserts that the cart is empty by checking the response.
	 *
	 * @return void
	 */
	protected function assert_cart_is_empty() {
		$response = $this->get_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Assert that cart contains expected number of items (v2).
	 *
	 * @param int $expected Expected number of items.
	 * @return void
	 */
	protected function assert_cart_has_items( $expected ) {
		$response = $this->get_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( $expected, $data['items'] );
	}

	/**
	 * Assert that cart contains a specific product (v2).
	 *
	 * @param int $product_id Product ID to check for.
	 * @return void
	 */
	protected function assert_cart_contains_product( $product_id ) {
		$response = $this->get_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );

		$found = false;
		foreach ( $data['items'] as $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $product_id ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, "Cart does not contain product with ID {$product_id}" );
	}

	/**
	 * Assert that cart contains a specific item key (v2).
	 *
	 * @param string $item_key Item key to check for.
	 * @return void
	 */
	protected function assert_cart_contains_item_key( $item_key ) {
		$response = $this->get_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );

		$found = false;
		foreach ( $data['items'] as $item ) {
			if ( isset( $item['item_key'] ) && $item['item_key'] === $item_key ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, "Cart does not contain item with key {$item_key}" );
	}

	/**
	 * Get cart key from response (v2).
	 *
	 * @param WP_REST_Response $response The REST API response object.
	 * @return string|null The cart key if found, null otherwise.
	 */
	protected function get_cart_key_from_response( $response ) {
		$data = $response->get_data();
		return isset( $data['cart_key'] ) ? $data['cart_key'] : null;
	}

	/**
	 * Get item key from response (v2).
	 *
	 * @param WP_REST_Response $response The REST API response object.
	 * @param int              $index    The index of the item (default 0).
	 * @return string|null The item key if found, null otherwise.
	 */
	protected function get_item_key_from_response( $response, $index = 0 ) {
		$data = $response->get_data();
		if ( ! empty( $data['items'] ) && is_array( $data['items'] ) ) {
			$items = array_values( $data['items'] );
			if ( isset( $items[ $index ]['item_key'] ) ) {
				return $items[ $index ]['item_key'];
			}
		}
		// add_item response with return_item=true returns item_key at top level.
		if ( isset( $data['item_key'] ) ) {
			return $data['item_key'];
		}
		return null;
	}
}
