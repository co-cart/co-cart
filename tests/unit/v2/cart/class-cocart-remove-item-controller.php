<?php
/**
 * Test CoCart Remove Item Controller
 *
 * Tests for CoCart remove item API endpoints including removing items
 * from cart, validation, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Remove Item Controller Class
 *
 * Tests the remove item API endpoints which handle removing products
 * from the cart including validation and error handling.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Remove_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test removing an item from cart.
	 *
	 * Verifies that an item can be successfully removed from the cart
	 * and that the cart is properly updated.
	 *
	 * @return void
	 */
	public function test_remove_item_from_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart first.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 2,
		) );

		$this->assert_rest_response_status( 200, $add_response );
		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove the item from cart.
		$response = $this->remove_item_from_cart( $item_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'removed', $data );
		$this->assertTrue( $data['removed'] );
		$this->assertArrayHasKey( 'item_key', $data );
		$this->assertEquals( $item_key, $data['item_key'] );
	}

	/**
	 * Test removing non-existent item from cart.
	 *
	 * Verifies that attempting to remove a non-existent item returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_remove_nonexistent_item_from_cart() {
		$response = $this->remove_item_from_cart( 'nonexistent_key' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing item from empty cart.
	 *
	 * Verifies that attempting to remove an item from an empty cart
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_remove_item_from_empty_cart() {
		// Ensure cart is empty.
		$this->clear_cart();

		$response = $this->remove_item_from_cart( 'some_key' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing item with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the updated cart data.
	 *
	 * @return void
	 */
	public function test_remove_item_with_return_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart first.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove item with return cart.
		$response = $this->remove_item_from_cart( $item_key, array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
		$this->assertCount( 0, $data['cart']['items'] );
	}

	/**
	 * Test removing item with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the updated cart items data.
	 *
	 * @return void
	 */
	public function test_remove_item_with_return_cart_items() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart first.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove item with return cart items.
		$response = $this->remove_item_from_cart( $item_key, array(
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
		$this->assertCount( 0, $data['items'] );
	}

	/**
	 * Test removing item with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the updated cart totals data.
	 *
	 * @return void
	 */
	public function test_remove_item_with_return_cart_totals() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart first.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove item with return cart totals.
		$response = $this->remove_item_from_cart( $item_key, array(
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
		$this->assertEquals( '0.00', $data['totals']['subtotal'] );
		$this->assertEquals( '0.00', $data['totals']['total'] );
	}

	/**
	 * Test removing item with session parameter.
	 *
	 * Verifies that an item can be removed from a specific cart session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_remove_item_with_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$session_key = 'test_session_' . time();

		// Add item to cart with session.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
			'session'    => $session_key,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove item from session.
		$response = $this->remove_item_from_cart( $item_key, array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
	}

	/**
	 * Test removing item with invalid session.
	 *
	 * Verifies that attempting to remove an item with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_remove_item_with_invalid_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Try to remove item with invalid session.
		$response = $this->remove_item_from_cart( $item_key, array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing multiple items from cart.
	 *
	 * Verifies that multiple items can be removed from the cart
	 * and that the cart is properly updated after each removal.
	 *
	 * @return void
	 */
	public function test_remove_multiple_items_from_cart() {
		// Create test products.
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		// Add items to cart.
		$add_response1 = $this->add_item_to_cart( array(
			'product_id' => $product1->get_id(),
			'quantity'   => 1,
		) );
		$add_response2 = $this->add_item_to_cart( array(
			'product_id' => $product2->get_id(),
			'quantity'   => 1,
		) );

		$item_key1 = $add_response1->get_data()['item_key'];
		$item_key2 = $add_response2->get_data()['item_key'];

		// Remove first item.
		$response1 = $this->remove_item_from_cart( $item_key1 );
		$this->assert_rest_response_status( 200, $response1 );

		// Remove second item.
		$response2 = $this->remove_item_from_cart( $item_key2 );
		$this->assert_rest_response_status( 200, $response2 );

		// Verify cart is empty.
		$cart_response = $this->get_cart();
		$cart_data = $cart_response->get_data();
		$this->assertCount( 0, $cart_data['items'] );
	}

	/**
	 * Test removing item and verifying cart totals.
	 *
	 * Verifies that removing an item properly updates the cart totals
	 * and that the totals are calculated correctly.
	 *
	 * @return void
	 */
	public function test_remove_item_and_verify_totals() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 2,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Verify initial cart total.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertEquals( '50.00', $initial_data['totals']['subtotal'] );

		// Remove item.
		$response = $this->remove_item_from_cart( $item_key, array(
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '0.00', $data['totals']['subtotal'] );
		$this->assertEquals( '0.00', $data['totals']['total'] );
	}

	/**
	 * Test removing item with custom data.
	 *
	 * Verifies that an item with custom data can be properly removed
	 * from the cart.
	 *
	 * @return void
	 */
	public function test_remove_item_with_custom_data() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart with custom data.
		$add_response = $this->add_item_to_cart( array(
			'product_id'  => $product->get_id(),
			'quantity'    => 1,
			'custom_data' => array( 'gift_wrap' => true ),
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove item.
		$response = $this->remove_item_from_cart( $item_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertTrue( $data['removed'] );
		$this->assertEquals( $item_key, $data['item_key'] );
	}

	/**
	 * Test removing variable product item.
	 *
	 * Verifies that a variable product item can be properly removed
	 * from the cart.
	 *
	 * @return void
	 */
	public function test_remove_variable_product_item() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variation.
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '30.00' );
		$variation->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation->save();

		// Add variation to cart.
		$add_response = $this->add_item_to_cart( array(
			'product_id'   => $product->get_id(),
			'variation_id' => $variation->get_id(),
			'quantity'     => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Remove variation from cart.
		$response = $this->remove_item_from_cart( $item_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertTrue( $data['removed'] );
		$this->assertEquals( $item_key, $data['item_key'] );
	}

	/**
	 * Test removing item with empty item key.
	 *
	 * Verifies that attempting to remove an item with an empty item key
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_remove_item_with_empty_key() {
		$response = $this->remove_item_from_cart( '' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test removing item with malformed item key.
	 *
	 * Verifies that attempting to remove an item with a malformed item key
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_remove_item_with_malformed_key() {
		$response = $this->remove_item_from_cart( 'invalid_key_format' );

		$this->assert_rest_response_status( 404, $response );
	}
} 