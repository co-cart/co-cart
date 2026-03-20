<?php
/**
 * Test CoCart Remove Item Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Remove_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test removing an item from cart.
	 *
	 * @return void
	 */
	public function test_remove_item_from_cart() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 2 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$this->assertNotNull( $item_key );

		$response = $this->remove_item_from_cart( $item_key );
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		// Cart is returned — items should now be empty.
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Test removing non-existent item from cart returns 404.
	 *
	 * @return void
	 */
	public function test_remove_nonexistent_item_from_cart() {
		$response = $this->remove_item_from_cart( 'nonexistent_key_that_does_not_exist' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing item from empty cart returns 404.
	 *
	 * @return void
	 */
	public function test_remove_item_from_empty_cart() {
		$this->clear_cart();

		$response = $this->remove_item_from_cart( 'some_key' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing multiple items from cart.
	 *
	 * @return void
	 */
	public function test_remove_multiple_items_from_cart() {
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		$add_response1 = $this->add_item_to_cart( $product1->get_id(), 1 );
		$this->add_item_to_cart( $product2->get_id(), 1 );

		// After both adds, get the cart and extract both keys.
		$cart_response = $this->get_cart();
		$cart_items    = array_values( $cart_response->get_data()['items'] );
		$item_key1     = $cart_items[0]['item_key'];
		$item_key2     = $cart_items[1]['item_key'];

		$response1 = $this->remove_item_from_cart( $item_key1 );
		$this->assert_rest_response_status( 200, $response1 );

		$response2 = $this->remove_item_from_cart( $item_key2 );
		$this->assert_rest_response_status( 200, $response2 );

		// Verify cart is empty.
		$cart_response = $this->get_cart();
		$cart_data     = $cart_response->get_data();
		$this->assertEmpty( $cart_data['items'] );
	}

	/**
	 * Test removing item and verifying cart is empty.
	 *
	 * @return void
	 */
	public function test_remove_item_and_verify_cart_empty() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$item_key     = $this->get_item_key_from_response( $add_response );

		$response = $this->remove_item_from_cart( $item_key );
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Test removing item with malformed item key returns 404.
	 *
	 * @return void
	 */
	public function test_remove_item_with_malformed_key() {
		$response = $this->remove_item_from_cart( 'invalid_key_format' );

		$this->assert_rest_response_status( 404, $response );
	}
}
