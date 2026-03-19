<?php
/**
 * Test CoCart Sessions Controller
 *
 * Tests for CoCart sessions API endpoints that require admin authentication
 * via WooCommerce API keys.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Sessions Controller Class
 *
 * Tests the sessions API endpoints which allow administrators to view
 * and manage active cart sessions.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Sessions_Controller extends CoCart_API_Test_Case {

	/**
	 * Test getting sessions without authentication.
	 *
	 * Verifies that the sessions API endpoint requires authentication
	 * and returns a 401 Unauthorized status when accessed without
	 * proper credentials.
	 *
	 * @return void
	 */
	public function test_get_sessions_without_authentication() {
		$response = $this->cocart_v2_request( 'GET', 'sessions' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting sessions with WooCommerce API key.
	 *
	 * Verifies that authenticated requests with appropriate API key
	 * permissions can successfully retrieve all active cart sessions.
	 *
	 * @return void
	 */
	public function test_get_sessions_with_api_key() {
		// Create API key.
		$key_data = $this->create_wc_api_key( array(
			'description' => 'Test Sessions API Key',
			'permissions' => 'read_write',
		) );

		// Make authenticated request.
		$response = $this->get_sessions( $key_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'sessions', $data );
	}

	/**
	 * Test getting specific session.
	 *
	 * Verifies that authenticated requests can retrieve a specific
	 * cart session by its cart key.
	 *
	 * @return void
	 */
	public function test_get_specific_session() {
		// Create API key.
		$key_data = $this->create_wc_api_key();

		// Create a cart first to get a session.
		$product = $this->create_product();
		$cart_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $cart_response );

		$cart_data = $cart_response->get_data();
		$cart_key = $cart_data['cart_key'];

		// Get the specific session.
		$response = $this->get_session( $cart_key, $key_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart_key', $data );
		$this->assertEquals( $cart_key, $data['cart_key'] );
	}

	/**
	 * Test getting non-existent session.
	 *
	 * Verifies that requesting a non-existent cart session returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_session() {
		$key_data = $this->create_wc_api_key();

		$response = $this->get_session( 'nonexistent_key', $key_data );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test sessions API with different permissions.
	 *
	 * Verifies that the sessions API properly enforces permission
	 * requirements - read-only keys can access sessions, but
	 * write-only keys cannot.
	 *
	 * @return void
	 */
	public function test_sessions_api_permissions() {
		// Test with read-only permissions.
		$read_key = $this->create_wc_api_key( array(
			'description' => 'Read Only Key',
			'permissions' => 'read',
		) );

		$response = $this->get_sessions( $read_key );
		$this->assert_rest_response_status( 200, $response );

		// Test with write-only permissions (should fail).
		$write_key = $this->create_wc_api_key( array(
			'description' => 'Write Only Key',
			'permissions' => 'write',
		) );

		$response = $this->get_sessions( $write_key );
		$this->assert_rest_response_status( 401, $response );
	}
}
