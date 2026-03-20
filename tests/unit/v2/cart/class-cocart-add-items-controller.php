<?php
/**
 * Test CoCart Add Items Controller
 *
 * Tests for CoCart add multiple items API endpoint via POST /cocart/v2/cart/add-items.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Add Items Controller Class
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Add_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test adding multiple items to the cart.
	 *
	 * @return void
	 */
	public function test_add_multiple_items_to_cart() {
		$product1 = $this->create_product( array( 'regular_price' => '10.00' ) );
		$product2 = $this->create_product( array( 'regular_price' => '20.00' ) );

		$response = $this->add_items_to_cart( array(
			array( 'id' => (string) $product1->get_id(), 'quantity' => '1' ),
			array( 'id' => (string) $product2->get_id(), 'quantity' => '2' ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 2, $data['items'] );
	}

	/**
	 * Test adding items with an invalid product ID returns an error.
	 *
	 * @return void
	 */
	public function test_add_items_with_invalid_product_returns_error() {
		$response = $this->add_items_to_cart( array(
			array( 'id' => '999999', 'quantity' => '1' ),
		) );

		// CoCart returns 404 when a product does not exist.
		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test adding an empty items array returns an error.
	 *
	 * @return void
	 */
	public function test_add_items_with_empty_array_returns_error() {
		$response = $this->add_items_to_cart( array() );

		// Empty items should return a 400 error.
		$status = $response->get_status();
		$this->assertGreaterThanOrEqual( 400, $status );
	}
}
