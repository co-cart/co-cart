<?php
/**
 * Test CoCart Authentication Injection Hooks
 *
 * Tests for the authentication injection hooks introduced in v4.8.0,
 * covering all four filter/action extension points in the login controller.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Authentication Injection Class
 *
 * Covers the hook-based extension points documented in the authentication
 * injection tutorial: cocart_login_permission_callback,
 * cocart_login_permission_granted, cocart_login_secure_auth_methods,
 * and cocart_login_query_parameters.
 *
 * @since   4.9.0 Introduced.
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Authentication_Injection extends CoCart_API_V2_Test_Case {

	/**
	 * Reset the controller's static request-deduplication cache between tests
	 * so each test gets a clean slate.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$ref = new ReflectionProperty( 'CoCart_REST_Login_V2_Controller', 'processed_requests' );
		$ref->setAccessible( true );
		$ref->setValue( null, array() );
	}

	/**
	 * Remove any filters/actions registered during a test.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		remove_all_filters( 'cocart_login_permission_callback' );
		remove_all_filters( 'cocart_login_secure_auth_methods' );
		remove_all_filters( 'cocart_login_query_parameters' );
		remove_all_actions( 'cocart_login_permission_granted' );

		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// cocart_login_permission_callback
	// -------------------------------------------------------------------------

	/**
	 * Test that a filter on cocart_login_permission_callback can deny access.
	 *
	 * Returning a WP_Error from the filter should cause the login endpoint
	 * to respond with an authorization error (401 or 403).
	 *
	 * @return void
	 */
	public function test_permission_callback_filter_can_deny_access() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		add_filter(
			'cocart_login_permission_callback',
			function () {
				return new WP_Error(
					'cocart_custom_auth_denied',
					'Custom authentication check failed.',
					array( 'status' => 403 )
				);
			}
		);

		$response = $this->cocart_v2_request( 'POST', 'login' );

		$this->assertTrue(
			in_array( $response->get_status(), array( 401, 403 ), true ),
			'Expected 401 or 403 when permission callback filter returns WP_Error.'
		);
	}

	/**
	 * Test that login succeeds by default when no filter alters permission.
	 *
	 * Without any injected filter, an authenticated user should receive a 200
	 * response from the login endpoint.
	 *
	 * @return void
	 */
	public function test_permission_callback_filter_allows_access_by_default() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		$response = $this->cocart_v2_request( 'POST', 'login' );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that the permission callback filter is skipped for JWT auth.
	 *
	 * When the current auth method is "jwt" it is treated as a secure method,
	 * so cocart_login_permission_callback must never be applied. A filter that
	 * would otherwise deny access should be ignored.
	 *
	 * We simulate JWT by mocking the Authorization header via the
	 * cocart_auth_header filter so get_current_auth_method() detects "jwt".
	 *
	 * @return void
	 */
	public function test_permission_callback_filter_skipped_for_jwt_auth() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		// Simulate a JWT Bearer token in the Authorization header.
		add_filter(
			'cocart_auth_header',
			function () {
				return 'Bearer fake.jwt.token';
			}
		);

		// This filter would deny access — but it should be bypassed for JWT.
		add_filter(
			'cocart_login_permission_callback',
			function () {
				return new WP_Error( 'should_not_run', 'This should be skipped.', array( 'status' => 403 ) );
			}
		);

		$response = $this->cocart_v2_request( 'POST', 'login' );

		remove_all_filters( 'cocart_auth_header' );

		$this->assert_rest_response_status( 200, $response );
	}

	// -------------------------------------------------------------------------
	// cocart_login_permission_granted
	// -------------------------------------------------------------------------

	/**
	 * Test that cocart_login_permission_granted fires on a successful login.
	 *
	 * Hooks an action callback that sets a flag; verifies the flag is set
	 * after the login request completes.
	 *
	 * @return void
	 */
	public function test_permission_granted_action_fires_on_success() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		$action_fired = false;

		add_action(
			'cocart_login_permission_granted',
			function () use ( &$action_fired ) {
				$action_fired = true;
			}
		);

		$response = $this->cocart_v2_request( 'POST', 'login' );

		$this->assert_rest_response_status( 200, $response );
		$this->assertTrue( $action_fired, 'cocart_login_permission_granted action should have fired.' );
	}

	/**
	 * Test that cocart_login_permission_granted does NOT fire when access is denied.
	 *
	 * When the permission callback filter returns WP_Error, the action that
	 * signals a successful grant must not be triggered.
	 *
	 * @return void
	 */
	public function test_permission_granted_action_does_not_fire_on_denied() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		add_filter(
			'cocart_login_permission_callback',
			function () {
				return new WP_Error( 'denied', 'Denied.', array( 'status' => 403 ) );
			}
		);

		$action_fired = false;

		add_action(
			'cocart_login_permission_granted',
			function () use ( &$action_fired ) {
				$action_fired = true;
			}
		);

		$this->cocart_v2_request( 'POST', 'login' );

		$this->assertFalse( $action_fired, 'cocart_login_permission_granted must not fire when access is denied.' );
	}

	// -------------------------------------------------------------------------
	// cocart_login_secure_auth_methods
	// -------------------------------------------------------------------------

	/**
	 * Test that adding a custom method to cocart_login_secure_auth_methods
	 * causes the permission callback filter to be skipped for that method.
	 *
	 * We add "basic_auth" to the secure list and set a permission_callback
	 * filter that would deny access. Since basic_auth requests now bypass the
	 * filter, the login should still succeed.
	 *
	 * @return void
	 */
	public function test_secure_auth_methods_filter_adds_custom_method() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );

		// Simulate a Basic Auth header so the controller detects "basic_auth".
		add_filter(
			'cocart_auth_header',
			function () {
				return 'Basic ' . base64_encode( 'ck_test:cs_test' );
			}
		);

		// Elevate basic_auth to a secure method — filter should be skipped.
		add_filter(
			'cocart_login_secure_auth_methods',
			function ( $methods ) {
				$methods[] = 'basic_auth';
				return $methods;
			}
		);

		// This filter would deny access if it ran.
		add_filter(
			'cocart_login_permission_callback',
			function () {
				return new WP_Error( 'should_be_skipped', 'Should not run.', array( 'status' => 403 ) );
			}
		);

		$response = $this->cocart_v2_request( 'POST', 'login' );

		remove_all_filters( 'cocart_auth_header' );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that the default secure auth methods include "jwt" and "api_key".
	 *
	 * Captures the value passed through the filter to assert the defaults.
	 *
	 * @return void
	 */
	public function test_secure_auth_methods_filter_default_values() {
		$captured = null;

		add_filter(
			'cocart_login_secure_auth_methods',
			function ( $methods ) use ( &$captured ) {
				$captured = $methods;
				return $methods;
			}
		);

		// Trigger the filter by running a login (user must be authenticated).
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$this->authenticate_as( $user_id );
		$this->cocart_v2_request( 'POST', 'login' );

		$this->assertNotNull( $captured, 'cocart_login_secure_auth_methods filter should have been applied.' );
		$this->assertContains( 'jwt', $captured, 'Default secure methods should include "jwt".' );
		$this->assertContains( 'api_key', $captured, 'Default secure methods should include "api_key".' );
	}

	// -------------------------------------------------------------------------
	// cocart_login_query_parameters
	// -------------------------------------------------------------------------

	/**
	 * Test that cocart_login_query_parameters filter adds a custom parameter.
	 *
	 * Instantiates the login controller and calls get_collection_params()
	 * directly to assert the custom parameter appears in the merged result.
	 *
	 * Note: the tutorial refers to this hook as "cocart_login_collection_params"
	 * but the actual hook name in the source is "cocart_login_query_parameters".
	 *
	 * @return void
	 */
	public function test_query_parameters_filter_adds_custom_param() {
		add_filter(
			'cocart_login_query_parameters',
			function ( $params ) {
				$params['device_id'] = array(
					'description'       => 'Unique identifier for the requesting device.',
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
					'validate_callback' => 'rest_validate_request_arg',
				);
				return $params;
			}
		);

		$controller = new CoCart_REST_Login_V2_Controller();
		$params     = $controller->get_collection_params();

		$this->assertArrayHasKey( 'device_id', $params, 'Custom parameter "device_id" should be present after filter.' );
		$this->assertEquals( 'string', $params['device_id']['type'] );
	}
}
