<?php
/**
 * Test CoCart main class.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart::is_rest_api_request() detection.
 *
 * Covers both pretty-permalink requests (`/wp-json/cocart/...`) and plain
 * permalink requests (`?rest_route=/cocart/...`), the latter being the form
 * missed prior to 4.9.6, mirroring the same class of bug fixed upstream in
 * WooCommerce core's `is_rest_api_request()`.
 *
 * @see https://github.com/woocommerce/woocommerce/pull/66816
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart extends CoCart_Unit_Test_Case {

	/**
	 * Reset request globals after each test so they don't bleed into others.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		unset( $_SERVER['REQUEST_URI'], $_GET['rest_route'] );

		parent::tearDown();
	}

	/**
	 * A pretty-permalink CoCart request is detected as a REST API request.
	 *
	 * @return void
	 */
	public function test_detects_pretty_permalink_cocart_request() {
		$_SERVER['REQUEST_URI'] = '/' . rest_get_url_prefix() . '/cocart/v2/cart';
		unset( $_GET['rest_route'] );

		$this->assertTrue( CoCart::is_rest_api_request() );
	}

	/**
	 * A plain-permalink CoCart request (`?rest_route=`) is detected as a
	 * REST API request.
	 *
	 * @return void
	 */
	public function test_detects_plain_permalink_cocart_request() {
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=/cocart/v2/cart';
		$_GET['rest_route']     = '/cocart/v2/cart';

		$this->assertTrue( CoCart::is_rest_api_request() );
	}

	/**
	 * A plain-permalink request to the WP REST API batch endpoint carrying
	 * CoCart sub-requests is detected as a REST API request.
	 *
	 * @return void
	 */
	public function test_detects_plain_permalink_batch_request() {
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=/batch/v1';
		$_GET['rest_route']     = '/batch/v1';

		$this->assertTrue( CoCart::is_rest_api_request() );
	}

	/**
	 * A pretty-permalink request for a non-CoCart namespace is not detected
	 * as a REST API request.
	 *
	 * @return void
	 */
	public function test_ignores_pretty_permalink_non_cocart_request() {
		$_SERVER['REQUEST_URI'] = '/' . rest_get_url_prefix() . '/wc/v3/products';
		unset( $_GET['rest_route'] );

		$this->assertFalse( CoCart::is_rest_api_request() );
	}

	/**
	 * A plain-permalink request for a non-CoCart namespace is not detected
	 * as a REST API request.
	 *
	 * @return void
	 */
	public function test_ignores_plain_permalink_non_cocart_request() {
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=/wc/v3/products';
		$_GET['rest_route']     = '/wc/v3/products';

		$this->assertFalse( CoCart::is_rest_api_request() );
	}

	/**
	 * An empty `rest_route` parameter is not detected as a REST API request.
	 *
	 * @return void
	 */
	public function test_ignores_empty_rest_route_parameter() {
		$_SERVER['REQUEST_URI'] = '/index.php?rest_route=';
		$_GET['rest_route']     = '';

		$this->assertFalse( CoCart::is_rest_api_request() );
	}

	/**
	 * A request with no `REQUEST_URI` at all is not detected as a REST API
	 * request.
	 *
	 * @return void
	 */
	public function test_ignores_missing_request_uri() {
		unset( $_SERVER['REQUEST_URI'], $_GET['rest_route'] );

		$this->assertFalse( CoCart::is_rest_api_request() );
	}
}
