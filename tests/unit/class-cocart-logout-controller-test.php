<?php
/**
 * Test CoCart Logout Controller
 *
 * Tests for CoCart logout API endpoints including session termination
 * and authentication cleanup.
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
	 * Test successful logout for an authenticated user.
	 *
	 * The logout endpoint (v2) extends the v1 controller which calls
	 * wp_logout() and returns `true` with status 200.
	 *
	 * @return void
	 */
	public function test_successful_logout() {
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$response = $this->logout();

		$this->assert_rest_response_status( 200, $response );
		$this->assertTrue( $response->get_data() );
	}

	/**
	 * Test logout is accessible without authentication.
	 *
	 * The logout endpoint uses __return_true as its permission callback,
	 * so unauthenticated requests also succeed.
	 *
	 * @return void
	 */
	public function test_logout_accessible_without_authentication() {
		$this->clear_authentication();

		$response = $this->logout();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test logout response is boolean true.
	 *
	 * Verifies the logout response structure — the endpoint returns
	 * the boolean value `true` wrapped in a 200 response.
	 *
	 * @return void
	 */
	public function test_logout_response_structure() {
		$user_id = $this->factory->user->create();
		$this->authenticate_as( $user_id );

		$response = $this->logout();

		$this->assert_rest_response_status( 200, $response );
		$this->assertIsBool( $response->get_data() );
		$this->assertTrue( $response->get_data() );
	}
}
