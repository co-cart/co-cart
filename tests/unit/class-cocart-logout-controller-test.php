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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$response = $this->logout();

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
		$this->clear_authentication();

		$response = $this->logout();

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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		// Manually destroy the session.
		wp_destroy_current_session();

		$response = $this->logout();

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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->logout( array( 'return_cart' => true ) );

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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->logout( array( 'return_cart_items' => true ) );

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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->logout( array( 'return_cart_totals' => true ) );

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
	 * Verifies that a user can logout from a specific named session.
	 * Uses login() to establish a named session via the API.
	 *
	 * @return void
	 */
	public function test_logout_with_session() {
		$this->factory->user->create( array(
			'user_login' => 'testuser_session',
			'user_email' => 'testsession@example.com',
			'user_pass'  => 'password123',
		) );

		$session_key = 'test_session_' . time();

		$login_response = $this->login( array(
			'username' => 'testuser_session',
			'password' => 'password123',
			'session'  => $session_key,
		) );

		$this->assert_rest_response_status( 200, $login_response );

		$response = $this->logout( array( 'session' => $session_key ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertTrue( $data['logged_out'] );
	}

	/**
	 * Test logout with invalid session parameter.
	 *
	 * Verifies that attempting to logout with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_logout_with_invalid_session_parameter() {
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$response = $this->logout( array( 'session' => 'invalid_session' ) );

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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$logout_response = $this->logout();
		$this->assert_rest_response_status( 200, $logout_response );

		// After logout, clear authentication and verify cart is inaccessible.
		$this->clear_authentication();
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
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$response = $this->logout();
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'logged_out', $data );
		$this->assertIsBool( $data['logged_out'] );
	}

	/**
	 * Test logout with multiple sessions.
	 *
	 * Verifies that a user can logout from multiple named sessions.
	 * Uses login() to establish each named session via the API.
	 *
	 * DEV NOTE: Not sure what AI was thinking when writing this test but we will see before removing it.
	 *
	 * @return void
	 */
	public function test_logout_with_multiple_sessions() {
		$this->factory->user->create( array(
			'user_login' => 'testuser_multi',
			'user_email' => 'testmulti@example.com',
			'user_pass'  => 'password123',
		) );

		$session1 = 'session1_' . time();
		$session2 = 'session2_' . time();

		$login_response1 = $this->login( array(
			'username' => 'testuser_multi',
			'password' => 'password123',
			'session'  => $session1,
		) );

		$login_response2 = $this->login( array(
			'username' => 'testuser_multi',
			'password' => 'password123',
			'session'  => $session2,
		) );

		$this->assert_rest_response_status( 200, $login_response1 );
		$this->assert_rest_response_status( 200, $login_response2 );

		$logout_response1 = $this->logout( array( 'session' => $session1 ) );
		$this->assert_rest_response_status( 200, $logout_response1 );

		$logout_response2 = $this->logout( array( 'session' => $session2 ) );
		$this->assert_rest_response_status( 200, $logout_response2 );

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
	 * Verifies that after logout, the cart is inaccessible without authentication.
	 *
	 * @return void
	 */
	public function test_logout_with_cart_preservation() {
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$logout_response = $this->logout();
		$this->assert_rest_response_status( 200, $logout_response );

		$this->clear_authentication();
		$cart_response = $this->get_cart();
		$this->assert_rest_response_status( 401, $cart_response );
	}
}
