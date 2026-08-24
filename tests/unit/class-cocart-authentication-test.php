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
	 * Test login endpoint returns user data for an authenticated user.
	 *
	 * The login endpoint requires the user to already be authenticated (via
	 * wp_set_current_user). In a real HTTP context the Authorization header
	 * drives that; in unit tests we simulate it directly.
	 *
	 * @return void
	 */
	public function test_login_endpoint_returns_user_data() {
		$user_id = $this->factory->user->create( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'role'       => 'customer',
		) );

		$this->authenticate_as( $user_id );

		$response = $this->cocart_v2_request( 'POST', 'login' );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'user_id', $data );
		$this->assertEquals( (string) $user_id, $data['user_id'] );
	}

	/**
	 * Test login endpoint returns 401 when no user is authenticated.
	 *
	 * The login endpoint permission callback returns 401 when no user
	 * is currently logged in (user ID = 0).
	 *
	 * @return void
	 */
	public function test_login_endpoint_requires_authentication() {
		$this->clear_authentication();

		$response = $this->cocart_v2_request( 'POST', 'login' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test authentication headers helper formats Basic Auth correctly.
	 *
	 * Verifies that the authenticate_with_wc_api_key() helper method
	 * correctly formats the Authorization header.
	 *
	 * @return void
	 */
	public function test_authentication_headers_helper() {
		$key_data = $this->create_wc_api_key();

		$headers = $this->authenticate_with_wc_api_key( $key_data );

		$this->assertArrayHasKey( 'Authorization', $headers );
		$this->assertStringStartsWith( 'Basic ', $headers['Authorization'] );

		// Verify the encoded value decodes to key:secret format.
		$encoded     = substr( $headers['Authorization'], 6 );
		$decoded     = base64_decode( $encoded );
		$parts       = explode( ':', $decoded );
		$this->assertCount( 2, $parts );
		$this->assertEquals( $key_data['consumer_key'], $parts[0] );
		$this->assertEquals( $key_data['consumer_secret'], $parts[1] );
	}

	/**
	 * Test sessions endpoint requires admin-level access.
	 *
	 * Verifies that the sessions endpoint returns 401 when no user is
	 * authenticated (permission check fails without a WC manager user).
	 *
	 * @return void
	 */
	public function test_sessions_endpoint_requires_admin() {
		$this->clear_authentication();

		$response = $this->rest_request( 'GET', '/cocart/v2/sessions' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test sessions endpoint accessible with admin user.
	 *
	 * Verifies that a WP admin user can access the sessions endpoint.
	 * WooCommerce grants manage_woocommerce capability to shop managers.
	 *
	 * @return void
	 */
	public function test_sessions_endpoint_with_admin_user() {
		$admin_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );
		$admin = get_user_by( 'id', $admin_id );
		$admin->add_cap( 'manage_woocommerce' );
		$this->authenticate_as( $admin_id );

		$response = $this->rest_request( 'GET', '/cocart/v2/sessions' );

		// 200 (sessions exist) or 404 (no sessions yet) — both mean auth passed.
		$status = $response->get_status();
		$this->assertTrue( in_array( $status, array( 200, 404 ), true ) );
	}

	/**
	 * Test cart operations work for authenticated users.
	 *
	 * Verifies that authenticated users can add items to cart.
	 *
	 * @return void
	 */
	public function test_cart_operations_with_authentication() {
		$user_id = $this->factory->user->create( array(
			'role' => 'customer',
		) );
		$this->authenticate_as( $user_id );

		$product  = $this->create_product();
		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test logout endpoint is accessible to all users.
	 *
	 * The logout endpoint uses __return_true as its permission callback,
	 * so any request (authenticated or not) should succeed.
	 *
	 * @return void
	 */
	public function test_logout_endpoint_always_accessible() {
		$this->clear_authentication();

		$response = $this->cocart_v2_request( 'POST', 'logout' );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test logout endpoint works for authenticated users.
	 *
	 * Verifies that authenticated users can successfully call the logout
	 * endpoint.
	 *
	 * @return void
	 */
	public function test_logout_with_authenticated_user() {
		$user_id = $this->factory->user->create( array(
			'role' => 'customer',
		) );
		$this->authenticate_as( $user_id );

		$response = $this->cocart_v2_request( 'POST', 'logout' );

		$this->assert_rest_response_status( 200, $response );
	}
}
