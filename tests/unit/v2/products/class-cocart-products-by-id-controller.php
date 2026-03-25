<?php
/**
 * Test CoCart Products By ID Controller
 *
 * Tests for CoCart products by ID API endpoint including retrieving products
 * by ID, response structure, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Products By ID Controller Class
 *
 * Tests the products by ID API endpoint which handles retrieving a single
 * product by its ID via GET /cocart/v2/products/{id}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Products_By_ID_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a product by ID returns 200.
	 *
	 * Verifies that a valid published product can be retrieved by ID
	 * with a successful response.
	 *
	 * @return void
	 */
	public function test_get_product_by_id_returns_200() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->get_product( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a product by ID returns correct data.
	 *
	 * Verifies that the response ID and name match the created product.
	 *
	 * @return void
	 */
	public function test_get_product_by_id_returns_correct_data() {
		$product = $this->create_product( array(
			'name'          => 'My Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->get_product( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( $product->get_id(), $data['id'] );
		$this->assertEquals( 'My Test Product', $data['name'] );
	}

	/**
	 * Test getting a product by invalid ID returns 404.
	 *
	 * Verifies that requesting a non-existent product ID returns
	 * an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_product_by_invalid_id_returns_404() {
		$response = $this->get_product( 999999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting a product by invalid ID returns correct error code.
	 *
	 * Verifies that the error response contains the expected error code.
	 *
	 * @return void
	 */
	public function test_get_product_by_invalid_id_error_code() {
		$response = $this->get_product( 999999 );

		$this->assert_rest_response_error( 'cocart_product_invalid_id', $response );
	}

	/**
	 * Test getting an unpublished product by ID returns 404.
	 *
	 * Verifies that a draft product is not publicly accessible
	 * and returns an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_unpublished_product_by_id_returns_404() {
		$product = $this->create_product( array(
			'name'          => 'Draft Product',
			'regular_price' => '25.00',
			'status'        => 'draft',
		) );

		$response = $this->get_product( $product->get_id() );

		$this->assert_rest_response_status( 404, $response );
	}
}
