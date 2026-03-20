<?php
/**
 * Test CoCart Totals Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Totals_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting cart totals returns flat totals array.
	 *
	 * The totals endpoint returns the WC totals array directly (no wrapper key).
	 * It returns 404 if the cart is empty or not calculated.
	 *
	 * @return void
	 */
	public function test_get_cart_totals() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 2 );

		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertArrayHasKey( 'total', $data );
	}

	/**
	 * Test getting totals for empty cart returns 404.
	 *
	 * @return void
	 */
	public function test_get_totals_for_empty_cart() {
		$this->clear_cart();

		$response = $this->get_cart_totals();

		// Totals controller returns 404 when cart has no items/totals.
		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test totals response structure.
	 *
	 * @return void
	 */
	public function test_get_totals_response_structure() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_totals();
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertArrayHasKey( 'total', $data );
		$this->assertArrayHasKey( 'shipping_total', $data );
		$this->assertArrayHasKey( 'discount_total', $data );
		$this->assertArrayHasKey( 'fee_total', $data );
	}

	/**
	 * Test getting totals with multiple items.
	 *
	 * @return void
	 */
	public function test_get_totals_with_multiple_items() {
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

		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertArrayHasKey( 'total', $data );
	}

	/**
	 * Test getting totals with sale products.
	 *
	 * @return void
	 */
	public function test_get_totals_with_sale_products() {
		$product = $this->create_product( array(
			'name'          => 'Sale Product',
			'regular_price' => '50.00',
			'sale_price'    => '30.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'subtotal', $data );
	}

	/**
	 * Test getting totals with variable products.
	 *
	 * @return void
	 */
	public function test_get_totals_with_variable_products() {
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

		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'subtotal', $data );
		$this->assertArrayHasKey( 'total', $data );
	}
}
