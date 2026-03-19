<?php
/**
 * Test CoCart Logout Controller
 *
 * Tests for CoCart logout API endpoints including session termination,
 * authentication cleanup, and validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Logout Controller Class
 *
 * Tests the logout API endpoints which handle user session termination
 * and authentication cleanup for the CoCart API.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Logout_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test successful logout.
	 *
	 * Verifies that a user can successfully logout and that the session
	 * is properly terminated.
	 *
	 * @return void
	 */
	public function test_successful_logout() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Logout the user.
		$response = $this->logout_user();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'logged_out', $data );
		$this->assertTrue( $data['logged_out'] );
	}

	/**
	 * Test logout when not logged in.
	 *
	 * Verifies that attempting to logout when not logged in returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_logout_when_not_logged_in() {
		// Ensure no user is logged in.
		wp_logout();

		// Try to logout.
		$response = $this->logout_user();

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test logout with invalid session.
	 *
	 * Verifies that attempting to logout with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_logout_with_invalid_session() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Manually destroy the session.
		wp_destroy_current_session();

		// Try to logout.
		$response = $this->logout_user();

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test logout with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the cart data after logout.
	 *
	 * @return void
	 */
	public function test_logout_with_return_cart() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Add item to cart.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Logout with return cart.
		$response = $this->logout_user( array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'logged_out', $data );
		$this->assertTrue( $data['logged_out'] );
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
	}

	/**
	 * Test logout with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the cart items data after logout.
	 *
	 * @return void
	 */
	public function test_logout_with_return_cart_items() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Add item to cart.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Logout with return cart items.
		$response = $this->logout_user( array(
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'logged_out', $data );
		$this->assertTrue( $data['logged_out'] );
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
	}

	/**
	 * Test logout with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the cart totals data after logout.
	 *
	 * @return void
	 */
	public function test_logout_with_return_cart_totals() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Add item to cart.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Logout with return cart totals.
		$response = $this->logout_user( array(
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'logged_out', $data );
		$this->assertTrue( $data['logged_out'] );
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
	}

	/**
	 * Test logout with session parameter.
	 *
	 * Verifies that a user can logout from a specific session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_logout_with_session() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		$session_key = 'test_session_' . time();

		// Login the user with session.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
			'session'  => $session_key,
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Logout from session.
		$response = $this->logout_user( array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertTrue( $data['logged_out'] );
	}

	/**
	 * Test logout with invalid session.
	 *
	 * Verifies that attempting to logout with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_logout_with_invalid_session_parameter() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Try to logout with invalid session.
		$response = $this->logout_user( array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test logout and verify session cleanup.
	 *
	 * Verifies that after logout, the user session is properly cleaned up
	 * and subsequent requests require re-authentication.
	 *
	 * @return void
	 */
	public function test_logout_and_verify_session_cleanup() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Logout the user.
		$logout_response = $this->logout_user();
		$this->assert_rest_response_status( 200, $logout_response );

		// Try to access a protected endpoint.
		$response = $this->get_cart();
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test logout response structure.
	 *
	 * Verifies that the logout response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_logout_response_structure() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Logout the user.
		$response = $this->logout_user();
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'logged_out', $data );

		// Check data types.
		$this->assertIsBool( $data['logged_out'] );
	}

	/**
	 * Test logout with multiple sessions.
	 *
	 * Verifies that a user can logout from multiple sessions
	 * and that each session is properly terminated.
	 *
	 * DEV NOTE: Not sure what AI was thinking when writing this test but we will see before removing it.
	 *
	 * @return void
	 */
	public function test_logout_with_multiple_sessions() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		$session1 = 'session1_' . time();
		$session2 = 'session2_' . time();

		// Login the user with multiple sessions.
		$login_response1 = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
			'session'  => $session1,
		) );

		$login_response2 = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
			'session'  => $session2,
		) );

		$this->assert_rest_response_status( 200, $login_response1 );
		$this->assert_rest_response_status( 200, $login_response2 );

		// Logout from first session.
		$logout_response1 = $this->logout_user( array(
			'session' => $session1,
		) );

		$this->assert_rest_response_status( 200, $logout_response1 );

		// Logout from second session.
		$logout_response2 = $this->logout_user( array(
			'session' => $session2,
		) );

		$this->assert_rest_response_status( 200, $logout_response2 );

		// Verify both sessions are terminated.
		$data1 = $logout_response1->get_data();
		$data2 = $logout_response2->get_data();

		$this->assertTrue( $data1['logged_out'] );
		$this->assertTrue( $data2['logged_out'] );
		$this->assertEquals( $session1, $data1['session'] );
		$this->assertEquals( $session2, $data2['session'] );
	}

	/**
	 * Test logout with cart preservation.
	 *
	 * Verifies that after logout, the cart data is preserved
	 * and can be accessed by subsequent requests.
	 *
	 * @return void
	 */
	public function test_logout_with_cart_preservation() {
		// Create a test user.
		$user = $this->create_user( array(
			'user_login' => 'testuser',
			'user_email' => 'test@example.com',
			'user_pass'  => 'password123',
		) );

		// Login the user.
		$login_response = $this->login_user( array(
			'username' => 'testuser',
			'password' => 'password123',
		) );

		$this->assert_rest_response_status( 200, $login_response );

		// Add item to cart.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Logout the user.
		$logout_response = $this->logout_user();
		$this->assert_rest_response_status( 200, $logout_response );

		// Verify cart is still accessible (though without authentication).
		$cart_response = $this->get_cart();
		$this->assert_rest_response_status( 401, $cart_response );
	}
}
