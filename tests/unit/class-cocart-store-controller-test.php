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
	 * Test getting store information returns 200.
	 *
	 * @return void
	 */
	public function test_get_store() {
		$response = $this->get_store_info();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test store response always contains core fields.
	 *
	 * These fields are always present regardless of WP_DEBUG setting.
	 *
	 * @return void
	 */
	public function test_store_contains_core_fields() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'title', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'home_url', $data );
		$this->assertArrayHasKey( 'language', $data );
		$this->assertArrayHasKey( 'gmt_offset', $data );
		$this->assertArrayHasKey( 'timezone_string', $data );
		$this->assertArrayHasKey( 'store_address', $data );
	}

	/**
	 * Test store title matches WordPress site title.
	 *
	 * @return void
	 */
	public function test_store_title() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertEquals( get_option( 'blogname' ), $data['title'] );
	}

	/**
	 * Test store description matches WordPress site description.
	 *
	 * @return void
	 */
	public function test_store_description() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertEquals( get_option( 'blogdescription' ), $data['description'] );
	}

	/**
	 * Test store home URL matches WordPress home URL.
	 *
	 * @return void
	 */
	public function test_store_home_url() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertEquals( home_url(), $data['home_url'] );
	}

	/**
	 * Test store timezone information.
	 *
	 * @return void
	 */
	public function test_store_timezone() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertEquals( get_option( 'gmt_offset' ), $data['gmt_offset'] );
		$this->assertEquals( wp_timezone_string(), $data['timezone_string'] );
	}

	/**
	 * Test store address is present and is an array.
	 *
	 * @return void
	 */
	public function test_store_address() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		$this->assertIsArray( $data['store_address'] );
		$this->assertArrayHasKey( 'address', $data['store_address'] );
		$this->assertArrayHasKey( 'city', $data['store_address'] );
		$this->assertArrayHasKey( 'country', $data['store_address'] );
		$this->assertArrayHasKey( 'postcode', $data['store_address'] );
	}

	/**
	 * Test store endpoint is publicly accessible without authentication.
	 *
	 * The store endpoint uses __return_true as its permission callback.
	 *
	 * @return void
	 */
	public function test_store_endpoint_is_public() {
		$this->clear_authentication();

		$response = $this->get_store_info();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test routes and version only appear when WP_DEBUG is enabled.
	 *
	 * @return void
	 */
	public function test_debug_fields_conditional() {
		$response = $this->get_store_info();
		$data     = $response->get_data();

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$this->assertArrayHasKey( 'routes', $data );
			$this->assertArrayHasKey( 'version', $data );
		} else {
			$this->assertArrayNotHasKey( 'routes', $data );
			$this->assertArrayNotHasKey( 'version', $data );
		}
	}
}
