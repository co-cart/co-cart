<?php
/**
 * CoCart API v1 Test Case
 *
 * Provides CoCart API v1-specific testing functionality.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * CoCart API v1 Test Case Class
 *
 * Provides helpers and assertions for testing CoCart API v1 endpoints.
 *
 * @package CoCart\Tests\Framework
 */
abstract class CoCart_API_V1_Test_Case extends CoCart_API_Test_Case {

	/**
	 * Set up test environment.
	 *
	 * Ensures CoCart v1 API is available before running tests.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * Make a request to the CoCart v1 API.
	 *
	 * @param string $method   HTTP method (GET, POST, PUT, DELETE).
	 * @param string $endpoint API endpoint (relative to /cocart/v1/).
	 * @param array  $params   Request parameters.
	 * @param array  $headers  Request headers.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function cocart_v1_request( $method, $endpoint, $params = array(), $headers = array() ) {
		$route = '/cocart/v1/' . ltrim( $endpoint, '/' );
		return $this->rest_request( $method, $route, $params, $headers );
	}

	/**
	 * Get cart via CoCart v1 API.
	 *
	 * The v1 "get cart" route is registered as `get-cart`, not `cart`.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'get-cart', $params );
	}

	/**
	 * Add item to cart via CoCart v1 API.
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

		return $this->cocart_v1_request( 'POST', 'add-item', $request_params );
	}

	/**
	 * Remove item from cart via CoCart v1 API.
	 *
	 * @param string $item_key Item key to remove.
	 * @param array  $params   Optional. Additional parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function remove_item_from_cart( $item_key, $params = array() ) {
		$request_params = array_merge( array(
			'item_key' => $item_key,
		), $params );

		return $this->cocart_v1_request( 'DELETE', 'item', $request_params );
	}

	/**
	 * Update item in cart via CoCart v1 API.
	 *
	 * The v1 `item` route registers its update handler as CREATABLE (POST),
	 * not PUT.
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

		return $this->cocart_v1_request( 'POST', 'item', $request_params );
	}

	/**
	 * Clear cart via CoCart v1 API.
	 *
	 * The v1 `clear-cart` route registers its handler as CREATABLE (POST),
	 * not DELETE.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function clear_cart( $params = array() ) {
		return $this->cocart_v1_request( 'POST', 'clear-cart', $params );
	}

	/**
	 * Get cart totals via CoCart v1 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_totals( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'totals', $params );
	}

	/**
	 * Get cart count via CoCart v1 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_cart_count( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'count-items', $params );
	}

	/**
	 * Get products via CoCart v1 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_products( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'products', $params );
	}

	/**
	 * Get single product via CoCart v1 API.
	 *
	 * @param int   $product_id Product ID to retrieve.
	 * @param array $params     Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product( $product_id, $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'products/' . $product_id, $params );
	}

	/**
	 * Get product categories via CoCart v1 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_categories( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'product-categories', $params );
	}

	/**
	 * Get product attributes via CoCart v1 API.
	 *
	 * @param array $params Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_attributes( $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'product-attributes', $params );
	}

	/**
	 * Get product variations via CoCart v1 API.
	 *
	 * @param int   $product_id Product ID to get variations for.
	 * @param array $params     Optional. Request parameters.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_product_variations( $product_id, $params = array() ) {
		return $this->cocart_v1_request( 'GET', 'products/' . $product_id . '/variations', $params );
	}

	/**
	 * Assert that cart is empty (v1).
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
	 * Assert that cart contains expected number of items (v1).
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
	 * Assert that cart contains a specific product (v1).
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
	 * Get cart key from response (v1).
	 *
	 * @param WP_REST_Response $response The REST API response object.
	 * @return string|null The cart key if found, null otherwise.
	 */
	protected function get_cart_key_from_response( $response ) {
		$data = $response->get_data();
		return isset( $data['cart_key'] ) ? $data['cart_key'] : null;
	}

	/**
	 * Get item key from response (v1).
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
}
