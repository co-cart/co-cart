<?php
/**
 * Test CoCart Clear Cart Controller
 *
 * Tests for CoCart clear cart API endpoints including clearing all items
 * from cart, session handling, and validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Clear Cart Controller Class
 *
 * Tests the clear cart API endpoints which handle removing all products
 * from the cart including session management and validation.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Clear_Cart_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test clearing cart with items.
	 *
	 * Verifies that all items can be successfully removed from the cart
	 * and that the cart is properly emptied.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_items() {
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
		$this->add_item_to_cart( array(
			'product_id' => $product1->get_id(),
			'quantity'   => 2,
		) );
		$this->add_item_to_cart( array(
			'product_id' => $product2->get_id(),
			'quantity'   => 1,
		) );

		// Verify cart has items.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertCount( 2, $initial_data['items'] );

		// Clear cart.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cleared', $data );
		$this->assertTrue( $data['cleared'] );
		$this->assertArrayHasKey( 'items_removed', $data );
		$this->assertEquals( 2, $data['items_removed'] );

		// Verify cart is empty.
		$final_cart = $this->get_cart();
		$final_data = $final_cart->get_data();
		$this->assertCount( 0, $final_data['items'] );
	}

	/**
	 * Test clearing empty cart.
	 *
	 * Verifies that clearing an empty cart returns a successful response
	 * and indicates no items were removed.
	 *
	 * @return void
	 */
	public function test_clear_empty_cart() {
		// Ensure cart is empty.
		$this->clear_cart();

		// Clear cart again.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cleared', $data );
		$this->assertTrue( $data['cleared'] );
		$this->assertArrayHasKey( 'items_removed', $data );
		$this->assertEquals( 0, $data['items_removed'] );
	}

	/**
	 * Test clearing cart with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the cleared cart data.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_return_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Clear cart with return cart.
		$response = $this->clear_cart( array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
		$this->assertCount( 0, $data['cart']['items'] );
		$this->assertEquals( '0.00', $data['cart']['totals']['subtotal'] );
	}

	/**
	 * Test clearing cart with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the cleared cart items data.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_return_cart_items() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Clear cart with return cart items.
		$response = $this->clear_cart( array(
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
		$this->assertCount( 0, $data['items'] );
	}

	/**
	 * Test clearing cart with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the cleared cart totals data.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_return_cart_totals() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Clear cart with return cart totals.
		$response = $this->clear_cart( array(
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
	 * Test clearing cart with session parameter.
	 *
	 * Verifies that a cart can be cleared in a specific session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$session_key = 'test_session_' . time();

		// Add item to cart with session.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
			'session'    => $session_key,
		) );

		// Clear cart in session.
		$response = $this->clear_cart( array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertTrue( $data['cleared'] );
	}

	/**
	 * Test clearing cart with invalid session.
	 *
	 * Verifies that attempting to clear a cart with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_invalid_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Try to clear cart with invalid session.
		$response = $this->clear_cart( array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test clearing cart with multiple items.
	 *
	 * Verifies that clearing a cart with multiple items properly
	 * removes all items and returns the correct count.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_multiple_items() {
		// Create test products.
		$products = array();
		for ( $i = 1; $i <= 5; $i++ ) {
			$products[] = $this->create_product( array(
				'name'          => "Product {$i}",
				'regular_price' => ( 10 + $i ) . '.00',
			) );
		}

		// Add multiple items to cart.
		foreach ( $products as $product ) {
			$this->add_item_to_cart( array(
				'product_id' => $product->get_id(),
				'quantity'   => 1,
			) );
		}

		// Verify cart has items.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertCount( 5, $initial_data['items'] );

		// Clear cart.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertTrue( $data['cleared'] );
		$this->assertEquals( 5, $data['items_removed'] );

		// Verify cart is empty.
		$final_cart = $this->get_cart();
		$final_data = $final_cart->get_data();
		$this->assertCount( 0, $final_data['items'] );
	}

	/**
	 * Test clearing cart with variable products.
	 *
	 * Verifies that clearing a cart with variable products properly
	 * removes all items including variations.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_variable_products() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variations.
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

		// Add variations to cart.
		$this->add_item_to_cart( array(
			'product_id'   => $product->get_id(),
			'variation_id' => $variation1->get_id(),
			'quantity'     => 1,
		) );
		$this->add_item_to_cart( array(
			'product_id'   => $product->get_id(),
			'variation_id' => $variation2->get_id(),
			'quantity'     => 1,
		) );

		// Verify cart has items.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertCount( 2, $initial_data['items'] );

		// Clear cart.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertTrue( $data['cleared'] );
		$this->assertEquals( 2, $data['items_removed'] );

		// Verify cart is empty.
		$final_cart = $this->get_cart();
		$final_data = $final_cart->get_data();
		$this->assertCount( 0, $final_data['items'] );
	}

	/**
	 * Test clearing cart and verifying totals.
	 *
	 * Verifies that clearing the cart properly resets all totals
	 * to zero.
	 *
	 * @return void
	 */
	public function test_clear_cart_and_verify_totals() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 2,
		) );

		// Verify initial cart total.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertEquals( '50.00', $initial_data['totals']['subtotal'] );

		// Clear cart.
		$response = $this->clear_cart( array(
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '0.00', $data['totals']['subtotal'] );
		$this->assertEquals( '0.00', $data['totals']['total'] );
	}

	/**
	 * Test clearing cart with custom data.
	 *
	 * Verifies that clearing a cart with items that have custom data
	 * properly removes all items and their associated data.
	 *
	 * @return void
	 */
	public function test_clear_cart_with_custom_data() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart with custom data.
		$this->add_item_to_cart( array(
			'product_id'  => $product->get_id(),
			'quantity'    => 1,
			'custom_data' => array( 'gift_wrap' => true ),
		) );

		// Clear cart.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertTrue( $data['cleared'] );
		$this->assertEquals( 1, $data['items_removed'] );

		// Verify cart is empty.
		$final_cart = $this->get_cart();
		$final_data = $final_cart->get_data();
		$this->assertCount( 0, $final_data['items'] );
	}

	/**
	 * Test clearing cart response structure.
	 *
	 * Verifies that the clear cart response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_clear_cart_response_structure() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Clear cart.
		$response = $this->clear_cart();
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'cleared', $data );
		$this->assertArrayHasKey( 'items_removed', $data );

		// Check data types.
		$this->assertIsBool( $data['cleared'] );
		$this->assertIsInt( $data['items_removed'] );
	}
} 