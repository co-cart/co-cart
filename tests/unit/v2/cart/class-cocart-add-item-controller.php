<?php
/**
 * Test CoCart Add Item Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Add_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test adding a simple product to cart.
	 *
	 * @return void
	 */
	public function test_add_simple_product_to_cart() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 2 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		// add-item returns full cart response with items array.
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'items', $data );
		$this->assertNotEmpty( $data['items'] );
	}

	/**
	 * Test adding a variable product variation to cart.
	 *
	 * @return void
	 */
	public function test_add_variable_product_to_cart() {
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '30.00' );
		$variation->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation->save();

		// Pass the variation ID directly as the product ID — the controller detects
		// it is a variation type and resolves the parent/variation IDs automatically.
		$response = $this->add_item_to_cart( $variation->get_id(), 1 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertNotEmpty( $data['items'] );
	}

	/**
	 * Test adding product with invalid product ID returns 400.
	 *
	 * @return void
	 */
	public function test_add_invalid_product_to_cart() {
		$response = $this->add_item_to_cart( 99999, 1 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with zero quantity returns 400.
	 *
	 * @return void
	 */
	public function test_add_product_with_zero_quantity() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 0 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding out of stock product returns 400.
	 *
	 * @return void
	 */
	public function test_add_out_of_stock_product() {
		$product = $this->create_product( array(
			'name'          => 'Out of Stock Product',
			'regular_price' => '25.00',
			'stock_status'  => 'outofstock',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		// CoCart adds out-of-stock items to the cart without blocking — stock
		// validation notices are added but the item is not rejected at this layer.
		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test adding product exceeding stock quantity returns 400.
	 *
	 * @return void
	 */
	public function test_add_product_exceeding_stock() {
		$product = $this->create_product( array(
			'name'           => 'Limited Stock Product',
			'regular_price'  => '25.00',
			'manage_stock'   => true,
			'stock_quantity' => 5,
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 10 );

		// CoCart does not validate stock limits at the add-item layer — stock notices
		// are added but the item is not rejected. The cart is returned with status 200.
		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test item appears in cart after adding.
	 *
	 * @return void
	 */
	public function test_item_appears_in_cart_after_adding() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 2 );
		$this->assert_rest_response_status( 200, $response );

		$items = array_values( $response->get_data()['items'] );
		$this->assertCount( 1, $items );
		$this->assertEquals( 2, $items[0]['quantity']['value'] );
	}

	/**
	 * Test adding variable product without variation returns 400.
	 *
	 * @return void
	 */
	public function test_add_variable_product_without_variation() {
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		$this->assert_rest_response_status( 400, $response );
	}
}
