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
			'id'       => $product_id,
			'quantity' => $quantity,
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
		$request_params = array_merge( array(
			'item_key' => $item_key,
		), $params );

		return $this->cocart_v2_request( 'DELETE', 'cart/remove-item', $request_params );
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
		$request_params = array_merge( array(
			'item_key' => $item_key,
			'quantity' => $quantity,
		), $params );

		return $this->cocart_v2_request( 'PUT', 'cart/update-item', $request_params );
	}

	/**
	 * Clear cart via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function clear_cart( $params = array() ) {
		return $this->cocart_v2_request( 'DELETE', 'cart/clear', $params );
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
		return $this->cocart_v2_request( 'GET', 'cart/items-count', $params );
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
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function update_cart( $params = array() ) {
		return $this->cocart_v2_request( 'PUT', 'cart/update', $params );
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
	 * @param int   $product_id Product ID to retrieve.
	 * @param array $params     Optional. Request parameters.
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
		return $this->cocart_v2_request( 'GET', 'product-categories', $params );
	}

	/**
	 * Get product attributes via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_attributes( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'product-attributes', $params );
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
			if ( isset( $item['product_id'] ) && $item['product_id'] === $product_id ) {
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
		if ( isset( $data['items'][ $index ]['item_key'] ) ) {
			return $data['items'][ $index ]['item_key'];
		}
		return null;
	}

	/**
	 * Create empty guest cart via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function create_cart( $params = array() ) {
		return $this->cocart_v2_request( 'POST', 'cart', $params );
	}

	/**
	 * Get product brands via CoCart v2 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_brands( $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/brands', $params );
	}

	/**
	 * Get single product by slug via CoCart v2 API.
	 *
	 * @param string $slug   Product slug to retrieve.
	 * @param array  $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_by_slug( $slug, $params = array() ) {
		return $this->cocart_v2_request( 'GET', 'products/' . $slug, $params );
	}

	/**
	 * Delete a specific session via CoCart v2 API (requires admin authentication).
	 *
	 * @param string $cart_key Cart key of the session to delete.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function delete_session_by_key( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'DELETE', '/cocart/v2/session/' . $cart_key, array(), $key_data );
	}

	/**
	 * Get items in a specific session via CoCart v2 API (requires admin authentication).
	 *
	 * @param string $cart_key Cart key of the session.
	 * @param array  $key_data Optional. API key data for authentication.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_session_items_by_key( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', '/cocart/v2/session/' . $cart_key . '/items', array(), $key_data );
	}
}
