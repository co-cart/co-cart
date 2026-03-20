<?php
/**
 * Test CoCart Calculate Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Calculate_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test calculating cart totals returns full cart response.
	 *
	 * @return void
	 */
	public function test_calculate_cart_totals() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 2 );

		$response = $this->calculate_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test calculating cart with return_totals returns only totals.
	 *
	 * @return void
	 */
	public function test_calculate_cart_with_return_totals() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->calculate_cart( array( 'return_totals' => true ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		// When return_totals=true, the response is the totals array directly.
		$this->assertIsArray( $data );
	}

	/**
	 * Test calculating empty cart returns 200.
	 *
	 * @return void
	 */
	public function test_calculate_empty_cart() {
		$this->clear_cart();

		$response = $this->calculate_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test calculating cart with multiple items.
	 *
	 * @return void
	 */
	public function test_calculate_cart_with_multiple_items() {
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		$this->add_item_to_cart( $product1->get_id(), 2 );
		$this->add_item_to_cart( $product2->get_id(), 1 );

		$response = $this->calculate_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test calculating cart with variable products.
	 *
	 * @return void
	 */
	public function test_calculate_cart_with_variable_products() {
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '30.00' );
		$variation1->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '35.00' );
		$variation2->set_attributes( array( 'pa_color' => 'Blue' ) );
		$variation2->save();

		$this->add_item_to_cart( $product->get_id(), 1, array( 'variation_id' => $variation1->get_id() ) );
		$this->add_item_to_cart( $product->get_id(), 1, array( 'variation_id' => $variation2->get_id() ) );

		$response = $this->calculate_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test calculating cart with sale products.
	 *
	 * @return void
	 */
	public function test_calculate_cart_with_sale_products() {
		$product = $this->create_product( array(
			'name'          => 'Sale Product',
			'regular_price' => '50.00',
			'sale_price'    => '30.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->calculate_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
	}
}
