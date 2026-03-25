<?php
/**
 * Test CoCart Count Items Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Count_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test counting items in cart returns scalar integer.
	 *
	 * The count endpoint returns a scalar integer directly, not {count: N}.
	 *
	 * @return void
	 */
	public function test_count_items_in_cart() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 2 );

		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 2, $data );
	}

	/**
	 * Test counting items in empty cart returns 0.
	 *
	 * @return void
	 */
	public function test_count_items_in_empty_cart() {
		$this->clear_cart();

		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 0, $data );
	}

	/**
	 * Test counting multiple items from different products.
	 *
	 * @return void
	 */
	public function test_count_multiple_items_in_cart() {
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		$this->add_item_to_cart( $product1->get_id(), 2 );
		$this->add_item_to_cart( $product2->get_id(), 3 );

		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );
		$this->assertEquals( 5, $response->get_data() );
	}

	/**
	 * Test counting items with variable products.
	 *
	 * @return void
	 */
	public function test_count_items_with_variable_products() {
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

		$this->add_item_to_cart( $variation1->get_id(), 1 );
		$this->add_item_to_cart( $variation2->get_id(), 2 );

		$response = $this->count_items_in_cart();
		$this->assert_rest_response_status( 200, $response );
		$this->assertEquals( 3, $response->get_data() );
	}

	/**
	 * Test count updates after removing item.
	 *
	 * @return void
	 */
	public function test_count_items_after_add_and_remove() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 2 );
		$this->assertEquals( 2, $this->count_items_in_cart()->get_data() );

		$item_key = $this->get_item_key_from_response( $add_response );
		$this->remove_item_from_cart( $item_key );

		$this->assertEquals( 0, $this->count_items_in_cart()->get_data() );
	}
}
