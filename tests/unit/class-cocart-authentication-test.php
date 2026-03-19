<?php
/**
 * Test CoCart Authentication
 *
 * Tests for CoCart authentication mechanisms including basic authentication
 * and WooCommerce API key authentication.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Authentication Class
 *
 * Tests various authentication methods used by CoCart API endpoints.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Authentication extends CoCart_API_V2_Test_Case {

	/**
	 * Test basic authentication with valid credentials.
	 *
	 * Verifies that users can authenticate using valid username and password
	 * credentials via Basic Authentication header.
	 *
	 * @return void
	 */
	public function test_basic_authentication_with_valid_credentials() {
		$user = $this->create_customer( array(
			'username' => 'testuser',
			'email'    => 'test@example.com',
		) );

		wp_set_password( 'password123', $user->get_id() );

		// Create basic auth header.
		$auth_header = 'Basic ' . base64_encode( 'testuser:password123' );

		$response = $this->cocart_v2_request( 'POST', 'login', array(), array(
			'Authorization' => $auth_header,
		) );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test basic authentication with invalid credentials.
	 *
	 * Verifies that authentication fails with invalid username and password
	 * credentials, returning a 401 Unauthorized status.
	 *
	 * @return void
	 */
	public function test_basic_authentication_with_invalid_credentials() {
		$auth_header = 'Basic ' . base64_encode( 'invaliduser:wrongpassword' );

		$response = $this->cocart_v2_request( 'POST', 'login', array(), array(
			'Authorization' => $auth_header,
		) );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test WooCommerce API key authentication.
	 *
	 * Verifies that WooCommerce API keys with appropriate permissions can
	 * authenticate to admin endpoints like the sessions API.
	 *
	 * @return void
	 */
	public function test_wc_api_key_authentication() {
		$key_data = $this->create_wc_api_key( array(
			'description' => 'Test API Key',
			'permissions' => 'read_write',
		) );

		// Test with admin endpoint that requires API key.
		$response = $this->get_sessions( $key_data );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test API key with insufficient permissions.
	 *
	 * Verifies that WooCommerce API keys with insufficient permissions
	 * (e.g., write-only) cannot access read-only endpoints like sessions.
	 *
	 * @return void
	 */
	public function test_api_key_insufficient_permissions() {
		$key_data = $this->create_wc_api_key( array(
			'description' => 'Limited Key',
			'permissions' => 'write', // Write-only should not have read access to sessions.
		) );

		$response = $this->get_sessions( $key_data );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test cart operations with authentication.
	 *
	 * Verifies that authenticated users can perform cart operations and
	 * that the response includes the user ID to confirm authentication.
	 *
	 * @return void
	 */
	public function test_cart_operations_with_authentication() {
		$user = $this->create_customer();
		$this->authenticate_as( $user->get_id() );

		$product = $this->create_product();

		// Add item to cart as authenticated user.
		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'user_id', $data );
		$this->assertEquals( $user->get_id(), $data['user_id'] );
	}

	/**
	 * Test authentication headers are properly sent.
	 *
	 * Verifies that the authentication helper method correctly formats
	 * the Authorization header for WooCommerce API key authentication.
	 *
	 * @return void
	 */
	public function test_authentication_headers() {
		$key_data = $this->create_wc_api_key();

		$headers = $this->authenticate_with_wc_api_key( $key_data );

		$this->assertArrayHasKey( 'Authorization', $headers );
		$this->assertStringStartsWith( 'Basic ', $headers['Authorization'] );
	}

	/**
	 * Test logout functionality.
	 *
	 * Verifies that authenticated users can successfully log out
	 * from the CoCart API.
	 *
	 * @return void
	 */
	public function test_logout() {
		$user = $this->create_customer();
		$this->authenticate_as( $user->get_id() );

		$response = $this->cocart_v2_request( 'POST', 'logout' );

		$this->assert_rest_response_status( 200, $response );
	}
}
