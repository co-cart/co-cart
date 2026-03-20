<?php
/**
 * Test CoCart Clear Cart Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Clear_Cart_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test clearing cart with items.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_items() {
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

		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		// clear_cart returns the full cart response.
		$this->assertIsArray( $data );
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Test clearing empty cart returns 200.
	 *
	 * @return void
	 */
	public function test_clear_empty_cart() {
		$this->clear_cart();

		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Test clearing cart with multiple items empties cart.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_multiple_items() {
		$products = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$products[] = $this->create_product( array(
				'name'          => "Product {$i}",
				'regular_price' => ( 10 + $i ) . '.00',
			) );
		}

		foreach ( $products as $product ) {
			$this->add_item_to_cart( $product->get_id(), 1 );
		}

		$response = $this->clear_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Test clearing cart with variable products empties cart.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_variable_products() {
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
		$this->add_item_to_cart( $variation2->get_id(), 1 );

		$response = $this->clear_cart();
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEmpty( $data['items'] );
	}
}
