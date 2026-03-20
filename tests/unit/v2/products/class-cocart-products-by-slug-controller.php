<?php
/**
 * Test CoCart Products By Slug Controller
 *
 * Tests for CoCart products by slug API endpoint including retrieving products
 * by slug, response structure, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Products By Slug Controller Class
 *
 * Tests the products by slug API endpoint which handles retrieving a single
 * product by its slug via GET /cocart/v2/products/{slug}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Products_By_Slug_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a product by slug returns 200.
	 *
	 * Verifies that a valid published product can be retrieved by slug
	 * with a successful response.
	 *
	 * @return void
	 */
	public function test_get_product_by_slug_returns_200() {
		$product = $this->create_product( array(
			'name'          => 'Slug Test Product',
			'regular_price' => '20.00',
		) );

		// Re-fetch from DB to get the auto-generated slug.
		$product = wc_get_product( $product->get_id() );

		$response = $this->get_product_by_slug( $product->get_slug() );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a product by slug returns correct data.
	 *
	 * Verifies that the response slug and ID match the created product.
	 *
	 * @return void
	 */
	public function test_get_product_by_slug_returns_correct_data() {
		$product = $this->create_product( array(
			'name'          => 'Slug Match Product',
			'regular_price' => '20.00',
		) );

		// Re-fetch from DB to get the auto-generated slug.
		$product = wc_get_product( $product->get_id() );

		$response = $this->get_product_by_slug( $product->get_slug() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( $product->get_id(), $data['id'] );
		$this->assertEquals( $product->get_slug(), $data['slug'] );
	}

	/**
	 * Test getting a product by invalid slug returns 404.
	 *
	 * Verifies that requesting a non-existent product slug returns
	 * an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_product_by_invalid_slug_returns_404() {
		$response = $this->get_product_by_slug( 'this-slug-does-not-exist-xyz' );

		$this->assert_rest_response_status( 404, $response );
	}
}
