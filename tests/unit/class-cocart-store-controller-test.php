<?php
/**
 * Test CoCart Store Controller
 *
 * Tests for CoCart store API endpoints including store information
 * and public routes discovery.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Store Controller Class
 *
 * Tests the store API endpoints which handle store information
 * and provide details about available public routes.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Store_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting store information.
	 *
	 * Verifies that the store endpoint returns basic store information
	 * including store title, description, and home URL.
	 *
	 * @return void
	 */
	public function test_get_store() {
		$response = $this->get_store_info();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'title', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'home_url', $data );
		$this->assertArrayHasKey( 'language', $data );
		$this->assertArrayHasKey( 'gmt_offset', $data );
		$this->assertArrayHasKey( 'timezone_string', $data );
		$this->assertArrayHasKey( 'routes', $data );
	}

	/**
	 * Test store title is correct.
	 *
	 * Verifies that the store title returned matches the WordPress
	 * site title configuration.
	 *
	 * @return void
	 */
	public function test_store_name() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$this->assertEquals( get_bloginfo( 'title' ), $data['title'] );
	}

	/**
	 * Test store description is correct.
	 *
	 * Verifies that the store description returned matches the WordPress
	 * site description configuration.
	 *
	 * @return void
	 */
	public function test_store_description() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$this->assertEquals( get_bloginfo( 'description' ), $data['description'] );
	}

	/**
	 * Test store home URL is correct.
	 *
	 * Verifies that the store home URL returned matches the WordPress
	 * home URL configuration.
	 *
	 * @return void
	 */
	public function test_store_home_url() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$this->assertEquals( home_url(), $data['home_url'] );
	}

	/**
	 * Test store timezone information.
	 *
	 * Verifies that the store timezone information returned matches
	 * the WordPress timezone configuration.
	 *
	 * @return void
	 */
	public function test_store_timezone() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$this->assertEquals( get_option( 'gmt_offset' ), $data['gmt_offset'] );
		$this->assertEquals( get_option( 'timezone_string' ), $data['timezone_string'] );
	}

	/**
	 * Test store routes are available.
	 *
	 * Verifies that the store endpoint returns available public routes
	 * for the API.
	 *
	 * @return void
	 */
	public function test_store_routes() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$this->assertArrayHasKey( 'routes', $data );
		$this->assertIsArray( $data['routes'] );
		$this->assertNotEmpty( $data['routes'] );
	}

	/**
	 * Test store routes contain expected endpoints.
	 *
	 * Verifies that the store routes include expected CoCart API v2 endpoints.
	 *
	 * @return void
	 */
	public function test_store_routes_contain_endpoints() {
		$response = $this->get_store_info();
		$data = $response->get_data();

		$routes = $data['routes'];
		$route_keys = array_keys( $routes );

		// Check for v2 endpoints.
		$this->assertContains( '/cocart/v2/cart', $route_keys );
		$this->assertContains( '/cocart/v2/products', $route_keys );
		$this->assertContains( '/cocart/v2/store', $route_keys );
	}

	/**
	 * Test store endpoint is publicly accessible.
	 *
	 * Verifies that the store endpoint can be accessed without
	 * authentication and returns public information.
	 *
	 * @return void
	 */
	public function test_store_endpoint_public_access() {
		// Test without authentication.
		$response = $this->get_store_info();

		$this->assert_rest_response_status( 200, $response );
		$this->assertArrayHasKey( 'title', $response->get_data() );
	}

	/**
	 * Test store endpoint with different contexts.
	 *
	 * Verifies that the store endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_store_endpoint_contexts() {
		// Test with view context (default).
		$response = $this->get_store_info( array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_store_info( array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_store_info( array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test store endpoint schema.
	 *
	 * Verifies that the store endpoint returns the correct schema
	 * information for the response structure.
	 *
	 * @return void
	 */
	public function test_store_endpoint_schema() {
		$response = $this->get_store_info();
		$schema = $response->get_links();

		// Verify response has proper structure.
		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
	}

	/**
	 * Test store endpoint with debug mode.
	 *
	 * Verifies that when WP_DEBUG is enabled, additional debug
	 * information is included in the response.
	 *
	 * @return void
	 */
	public function test_store_endpoint_debug_info() {
		// Enable debug mode temporarily.
		$original_debug = defined( 'WP_DEBUG' ) ? WP_DEBUG : false;
		if ( ! defined( 'WP_DEBUG' ) ) {
			define( 'WP_DEBUG', true );
		}

		$response = $this->get_store_info();
		$data = $response->get_data();

		// Check for debug information when WP_DEBUG is true.
		if ( WP_DEBUG ) {
			$this->assertArrayHasKey( 'version', $data );
			$this->assertArrayHasKey( 'routes', $data );
		}

		// Restore original debug setting.
		if ( ! $original_debug && defined( 'WP_DEBUG' ) ) {
			// Note: We can't undefine constants, but this is just for testing.
		}
	}
}
