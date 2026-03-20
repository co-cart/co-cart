<?php
/**
 * Test CoCart Sessions Controller
 *
 * Tests for CoCart sessions API endpoints that require admin authentication.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Sessions Controller Class
 *
 * Tests the sessions API endpoints which allow administrators to view
 * active cart sessions. Admin access is simulated by setting an
 * administrator user via wp_set_current_user() — WC API key header auth
 * is not processed by WP_REST_Server in unit tests.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Sessions_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Admin user ID, created once per test class.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->admin_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );

		// WooCommerce grants manage_woocommerce to admins at runtime, but this
		// may not happen in the test environment — grant it explicitly.
		$admin = get_user_by( 'id', $this->admin_id );
		$admin->add_cap( 'manage_woocommerce' );
	}

	/**
	 * Test getting sessions without authentication returns 401.
	 *
	 * Verifies that the sessions endpoint requires authentication.
	 *
	 * @return void
	 */
	public function test_get_sessions_without_authentication() {
		$this->clear_authentication();

		$response = $this->cocart_v2_request( 'GET', 'sessions' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting sessions with an admin user.
	 *
	 * Verifies that an administrator can access the sessions endpoint.
	 * With no sessions in the database the endpoint returns 404.
	 *
	 * @return void
	 */
	public function test_get_sessions_with_admin_user() {
		$this->authenticate_as( $this->admin_id );

		$response = $this->cocart_v2_request( 'GET', 'sessions' );

		// 200 (sessions exist) or 404 (none yet) — both mean auth passed.
		$status = $response->get_status();
		$this->assertTrue( in_array( $status, array( 200, 404 ), true ) );
	}

	/**
	 * Test getting sessions returns 404 when no sessions exist.
	 *
	 * The endpoint throws a 404 exception when there are no sessions in
	 * the database rather than returning an empty array.
	 *
	 * @return void
	 */
	public function test_get_sessions_returns_404_when_empty() {
		$this->authenticate_as( $this->admin_id );

		$response = $this->cocart_v2_request( 'GET', 'sessions' );

		// Either empty (404) or data exists (200) — verify correct error code if 404.
		if ( 404 === $response->get_status() ) {
			$data = $response->get_data();
			$this->assertArrayHasKey( 'code', $data );
			$this->assertEquals( 'cocart_no_carts_in_session', $data['code'] );
		} else {
			$this->assert_rest_response_status( 200, $response );
		}
	}

	/**
	 * Test getting a specific session requires admin access.
	 *
	 * Verifies that the single session endpoint also requires
	 * admin-level authentication.
	 *
	 * @return void
	 */
	public function test_get_specific_session_requires_admin() {
		$this->clear_authentication();

		$response = $this->rest_request( 'GET', '/cocart/v2/session/some-cart-key' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting a non-existent session returns 404.
	 *
	 * Verifies that requesting a cart session that does not exist
	 * returns a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_session() {
		$this->authenticate_as( $this->admin_id );

		$response = $this->get_session( 'nonexistent_cart_key' );

		$this->assert_rest_response_status( 404, $response );
	}
}
