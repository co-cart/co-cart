<?php
/**
 * CoCart REST Test Case
 *
 * Provides CoCart REST API-specific testing functionality.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * CoCart REST Test Case Class
 *
 * Base test case for CoCart REST API tests that provides functionality
 * for making REST requests and asserting responses.
 *
 * @package CoCart\Tests\Framework
 */
abstract class CoCart_REST_Test_Case extends CoCart_Unit_Test_Case {

	/**
	 * REST Server instance.
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

	/**
	 * Set up test environment.
	 *
	 * Initializes the WordPress REST server and registers all REST routes
	 * to enable testing of REST API endpoints.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Set up REST server.
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server();
		do_action( 'rest_api_init' );
	}

	/**
	 * Tear down test environment.
	 *
	 * Cleans up the REST server instance to prevent interference
	 * between tests.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up REST server.
		global $wp_rest_server;
		$wp_rest_server = null;

		parent::tearDown();
	}

	/**
	 * Make a REST API request.
	 *
	 * Creates and dispatches a REST request with the specified method,
	 * route, parameters, and headers.
	 *
	 * @param string $method  HTTP method (GET, POST, PUT, DELETE, PATCH).
	 * @param string $route   REST route (e.g., '/wp/v2/posts').
	 * @param array  $params  Request parameters to send with the request.
	 * @param array  $headers Request headers to include.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function rest_request( $method, $route, $params = array(), $headers = array() ) {
		$request = new WP_REST_Request( $method, $route );

		if ( ! empty( $params ) ) {
			foreach ( $params as $key => $value ) {
				$request->set_param( $key, $value );
			}
		}

		if ( ! empty( $headers ) ) {
			foreach ( $headers as $key => $value ) {
				$request->add_header( $key, $value );
			}
		}

		return $this->server->dispatch( $request );
	}

	/**
	 * Make a GET request to the REST API.
	 *
	 * Convenience method for making GET requests to REST endpoints.
	 *
	 * @param string $route   REST route to request.
	 * @param array  $params  Query parameters to include in the request.
	 * @param array  $headers Request headers to include.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function rest_get( $route, $params = array(), $headers = array() ) {
		return $this->rest_request( 'GET', $route, $params, $headers );
	}

	/**
	 * Make a POST request to the REST API.
	 *
	 * Convenience method for making POST requests to REST endpoints.
	 *
	 * @param string $route   REST route to request.
	 * @param array  $params  Request body parameters to send.
	 * @param array  $headers Request headers to include.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function rest_post( $route, $params = array(), $headers = array() ) {
		return $this->rest_request( 'POST', $route, $params, $headers );
	}

	/**
	 * Make a PUT request to the REST API.
	 *
	 * Convenience method for making PUT requests to REST endpoints.
	 *
	 * @param string $route   REST route to request.
	 * @param array  $params  Request body parameters to send.
	 * @param array  $headers Request headers to include.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function rest_put( $route, $params = array(), $headers = array() ) {
		return $this->rest_request( 'PUT', $route, $params, $headers );
	}

	/**
	 * Make a DELETE request to the REST API.
	 *
	 * Convenience method for making DELETE requests to REST endpoints.
	 *
	 * @param string $route   REST route to request.
	 * @param array  $params  Request parameters to send.
	 * @param array  $headers Request headers to include.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function rest_delete( $route, $params = array(), $headers = array() ) {
		return $this->rest_request( 'DELETE', $route, $params, $headers );
	}

	/**
	 * Assert that a REST response has the expected status code.
	 *
	 * Helper method to check if a REST response has the expected
	 * HTTP status code.
	 *
	 * @param int              $expected Expected HTTP status code.
	 * @param WP_REST_Response $response REST response object.
	 * @return void
	 */
	protected function assert_rest_response_status( $expected, $response ) {
		$this->assertEquals( $expected, $response->get_status() );
	}

	/**
	 * Assert that a REST response has the expected content type.
	 *
	 * Helper method to check if a REST response has the expected
	 * content type header.
	 *
	 * @param string           $expected Expected content type (e.g., 'application/json').
	 * @param WP_REST_Response $response REST response object.
	 * @return void
	 */
	protected function assert_rest_response_content_type( $expected, $response ) {
		$this->assertEquals( $expected, $response->get_headers()['content-type'] );
	}

	/**
	 * Assert that a REST response contains expected data.
	 *
	 * Helper method to check if a REST response contains specific
	 * key-value pairs in its data.
	 *
	 * @param array            $expected Expected data as key-value pairs.
	 * @param WP_REST_Response $response REST response object.
	 * @return void
	 */
	protected function assert_rest_response_contains( $expected, $response ) {
		$data = $response->get_data();
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $data );
			$this->assertEquals( $value, $data[ $key ] );
		}
	}

	/**
	 * Assert that a REST response has the expected error code.
	 *
	 * Helper method to check if a REST error response has the expected
	 * error code.
	 *
	 * @param string           $expected Expected error code.
	 * @param WP_REST_Response $response REST response object.
	 * @return void
	 */
	protected function assert_rest_response_error( $expected, $response ) {
		$data = $response->get_data();
		$this->assertArrayHasKey( 'code', $data );
		$this->assertEquals( $expected, $data['code'] );
	}

	/**
	 * Get the cart key from a response.
	 *
	 * Extracts the cart key from a cart response. Useful for subsequent
	 * operations that require the cart key.
	 *
	 * @param WP_REST_Response $response REST response object.
	 * @return string|null The cart key if found, null otherwise.
	 */
	protected function get_cart_key_from_response( $response ) {
		$data = $response->get_data();
		return isset( $data['cart_key'] ) ? $data['cart_key'] : null;
	}

	/**
	 * Set up authentication for REST requests.
	 *
	 * Sets the current user for REST requests, enabling testing of
	 * authenticated endpoints.
	 *
	 * @param int $user_id User ID to authenticate as.
	 * @return void
	 */
	protected function authenticate_as( $user_id ) {
		wp_set_current_user( $user_id );
	}

	/**
	 * Clear authentication.
	 *
	 * Removes the current user, effectively logging out for REST requests.
	 * Useful for testing unauthenticated endpoints.
	 *
	 * @return void
	 */
	protected function clear_authentication() {
		wp_set_current_user( 0 );
	}
}
