<?php
/**
 * Test CoCart Update Item Controller
 *
 * Tests for CoCart update item API endpoint via PUT /cocart/v2/cart/item/{item_key}.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Update Item Controller Class
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Update_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test updating item quantity.
	 *
	 * @return void
	 */
	public function test_update_item_quantity() {
		$product      = $this->create_product( array( 'regular_price' => '25.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$this->assertNotEmpty( $item_key );

		$response = $this->update_item_in_cart( $item_key, 3 );
		$this->assert_rest_response_status( 200, $response );

		$data  = $response->get_data();
		$items = array_values( $data['items'] );
		$this->assertEquals( 3, $items[0]['quantity']['value'] );
	}

	/**
	 * Test updating non-existent item returns 404.
	 *
	 * @return void
	 */
	public function test_update_nonexistent_item() {
		$response = $this->update_item_in_cart( 'nonexistent_key_xyz', 2 );
		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test updating item with zero quantity returns error.
	 *
	 * @return void
	 */
	public function test_update_item_with_zero_quantity() {
		$product      = $this->create_product( array( 'regular_price' => '25.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$response = $this->update_item_in_cart( $item_key, 0 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test updating item exceeding stock returns error.
	 *
	 * @return void
	 */
	public function test_update_item_exceeding_stock() {
		$product = $this->create_product( array(
			'regular_price'  => '25.00',
			'manage_stock'   => true,
			'stock_quantity' => 3,
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$response = $this->update_item_in_cart( $item_key, 10 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test updating item with return_status returns message and quantity.
	 *
	 * @return void
	 */
	public function test_update_item_with_return_status() {
		$product      = $this->create_product( array( 'regular_price' => '25.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$response = $this->update_item_in_cart( $item_key, 2, array( 'return_status' => true ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'message', $data );
		$this->assertArrayHasKey( 'quantity', $data );
		$this->assertEquals( 2, $data['quantity'] );
	}
}
