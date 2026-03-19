<?php
/**
 * Test CoCart Products Controller v2
 *
 * Tests for CoCart v2 products API endpoints including listing products,
 * retrieving single products, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Products Controller V2 Class
 *
 * Tests the v2 products API endpoints which handle retrieving products
 * including listing, single product lookup, and validation.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Products_Controller_V2 extends CoCart_API_V2_Test_Case {

	/**
	 * Test listing products.
	 *
	 * Verifies that the products endpoint returns a 200 response
	 * with an array of products.
	 *
	 * @return void
	 */
	public function test_get_products() {
		$response = $this->get_products();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test listing products returns created products.
	 *
	 * Verifies that a newly created product appears in the products list.
	 *
	 * @return void
	 */
	public function test_get_products_contains_created_product() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '15.00',
		) );

		$response = $this->get_products();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );

		$product_ids = array_column( $data, 'id' );
		$this->assertContains( $product->get_id(), $product_ids );
	}

	/**
	 * Test retrieving a single product.
	 *
	 * Verifies that a single product can be retrieved by ID with
	 * the correct data returned.
	 *
	 * @return void
	 */
	public function test_get_single_product() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '15.00',
		) );

		$response = $this->get_product( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( $product->get_id(), $data['id'] );
		$this->assertEquals( 'Test Product', $data['name'] );
	}

	/**
	 * Test error handling for invalid product ID.
	 *
	 * Verifies that retrieving a product with a non-existent ID
	 * returns an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_invalid_product() {
		$response = $this->get_product( 999999 );

		$this->assert_rest_response_status( 404, $response );
	}
}
