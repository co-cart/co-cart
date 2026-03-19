<?php
/**
 * Test CoCart Session Items Controller
 *
 * Tests for CoCart session items API endpoint including authentication,
 * retrieving items, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Session Items Controller Class
 *
 * Tests the session items API endpoint which handles retrieving items in a
 * specific cart session via GET /cocart/v2/session/{session_key}/items.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test that getting session items requires authentication.
	 *
	 * Verifies that an unauthenticated GET request to the session items
	 * endpoint returns a 401 Unauthorized response.
	 *
	 * @return void
	 */
	public function test_get_session_items_requires_authentication() {
		$response = $this->rest_get( '/cocart/v2/session/test_session_key/items' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting session items returns items.
	 *
	 * Verifies that an authenticated admin can retrieve items from a
	 * session and the response contains the expected items.
	 *
	 * @return void
	 */
	public function test_get_session_items_returns_items() {
		// Create a session by adding a product to cart.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$cart_key = $this->get_cart_key_from_response( $add_response );
		$this->assertNotEmpty( $cart_key );

		// Create WC API key for authentication.
		$key_data = $this->create_wc_api_key();

		// Get session items.
		$response = $this->get_session_items_by_key( $cart_key, $key_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
	}

	/**
	 * Test getting session items for a session with no items.
	 *
	 * Verifies that retrieving items from a session with no items returns
	 * a 200 response with an empty array.
	 *
	 * @return void
	 */
	public function test_get_session_items_for_empty_session() {
		// Create an empty cart session.
		$create_response = $this->create_cart();
		$this->assert_rest_response_status( 200, $create_response );

		$data     = $create_response->get_data();
		$cart_key = $data['cart_key'];

		$key_data = $this->create_wc_api_key();

		$response = $this->get_session_items_by_key( $cart_key, $key_data );

		$this->assert_rest_response_status( 200, $response );

		$items = $response->get_data();
		$this->assertIsArray( $items );
		$this->assertEmpty( $items );
	}

	/**
	 * Test getting session items for a non-existent session returns 404.
	 *
	 * Verifies that attempting to retrieve items from a session that does
	 * not exist returns an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_session_items_nonexistent_session_returns_404() {
		$key_data = $this->create_wc_api_key();

		$response = $this->get_session_items_by_key( 'nonexistent_session_key_xyz', $key_data );

		$this->assert_rest_response_status( 404, $response );
	}
}
