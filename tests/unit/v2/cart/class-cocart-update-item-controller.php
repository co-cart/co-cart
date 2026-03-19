<?php
/**
 * Test CoCart Update Item Controller
 *
 * Tests for CoCart update item API endpoints including updating item
 * quantities, custom data, and validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Update Item Controller Class
 *
 * Tests the update item API endpoints which handle updating products
 * in the cart including quantity changes and custom data updates.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Update_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test updating item quantity.
	 *
	 * Verifies that an item's quantity can be successfully updated
	 * and that the cart totals are properly recalculated.
	 *
	 * @return void
	 */
	public function test_update_item_quantity() {
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

		$this->assert_rest_response_status( 200, $add_response );
		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Update item quantity.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 3,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'item_key', $data );
		$this->assertArrayHasKey( 'quantity', $data );
		$this->assertEquals( $item_key, $data['item_key'] );
		$this->assertEquals( 3, $data['quantity'] );
	}

	/**
	 * Test updating item with custom data.
	 *
	 * Verifies that an item's custom data can be successfully updated
	 * and that the custom data is properly stored.
	 *
	 * DEV NOTE: While this would be cool to do, currently WooCommerce does not support updating items in the cart once they are added other than the quantity.
	 *
	 * @return void
	 */
	public function test_update_item_custom_data() {
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

		// Update item with custom data.
		$custom_data = array(
			'gift_wrap' => true,
			'notes'     => 'Updated notes',
		);

		$response = $this->update_item_in_cart( $item_key, array(
			'custom_data' => $custom_data,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'custom_data', $data );
		$this->assertEquals( $custom_data, $data['custom_data'] );
	}

	/**
	 * Test updating item with both quantity and custom data.
	 *
	 * Verifies that an item can be updated with both quantity and
	 * custom data simultaneously.
	 *
	 * @return void
	 */
	public function test_update_item_quantity_and_custom_data() {
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

		// Update item with both quantity and custom data.
		$custom_data = array(
			'gift_wrap' => true,
			'notes'     => 'Updated notes',
		);

		$response = $this->update_item_in_cart( $item_key, array(
			'quantity'    => 2,
			'custom_data' => $custom_data,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 2, $data['quantity'] );
		$this->assertEquals( $custom_data, $data['custom_data'] );
	}

	/**
	 * Test updating non-existent item.
	 *
	 * Verifies that attempting to update a non-existent item returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_update_nonexistent_item() {
		$response = $this->update_item_in_cart( 'nonexistent_key', array(
			'quantity' => 2,
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test updating item with invalid quantity.
	 *
	 * Verifies that attempting to update an item with invalid quantity
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_update_item_with_invalid_quantity() {
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

		// Test with zero quantity.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 0,
		) );

		$this->assert_rest_response_status( 400, $response );

		// Test with negative quantity.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => -1,
		) );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test updating item exceeding stock quantity.
	 *
	 * Verifies that attempting to update an item to exceed available stock
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_update_item_exceeding_stock() {
		// Create product with limited stock.
		$product = $this->create_product( array(
			'name'           => 'Limited Stock Product',
			'regular_price'  => '25.00',
			'manage_stock'   => true,
			'stock_quantity' => 5,
		) );

		// Add item to cart first.
		$add_response = $this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		$add_data = $add_response->get_data();
		$item_key = $add_data['item_key'];

		// Try to update to exceed stock.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 10,
		) );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test updating item with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the updated cart data.
	 *
	 * @return void
	 */
	public function test_update_item_with_return_cart() {
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

		// Update item with return cart.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity'    => 2,
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
		$this->assertCount( 1, $data['cart']['items'] );
		$this->assertEquals( 2, $data['cart']['items'][0]['quantity'] );
	}

	/**
	 * Test updating item with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the updated cart items data.
	 *
	 * @return void
	 */
	public function test_update_item_with_return_cart_items() {
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

		// Update item with return cart items.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity'          => 2,
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
		$this->assertCount( 1, $data['items'] );
		$this->assertEquals( 2, $data['items'][0]['quantity'] );
	}

	/**
	 * Test updating item with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the updated cart totals data.
	 *
	 * @return void
	 */
	public function test_update_item_with_return_cart_totals() {
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

		// Update item with return cart totals.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity'           => 2,
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
		$this->assertEquals( '50.00', $data['totals']['subtotal'] );
		$this->assertEquals( '50.00', $data['totals']['total'] );
	}

	/**
	 * Test updating item with session parameter.
	 *
	 * Verifies that an item can be updated in a specific cart session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_update_item_with_session() {
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

		// Update item in session.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 2,
			'session'  => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertEquals( 2, $data['quantity'] );
	}

	/**
	 * Test updating item with invalid session.
	 *
	 * Verifies that attempting to update an item with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_update_item_with_invalid_session() {
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

		// Try to update item with invalid session.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 2,
			'session'  => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test updating variable product item.
	 *
	 * Verifies that a variable product item can be properly updated
	 * in the cart.
	 *
	 * @return void
	 */
	public function test_update_variable_product_item() {
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

		// Update variation in cart.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity' => 2,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 2, $data['quantity'] );
		$this->assertEquals( $variation->get_id(), $data['variation_id'] );
	}

	/**
	 * Test updating item with empty parameters.
	 *
	 * Verifies that attempting to update an item with empty parameters
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_update_item_with_empty_parameters() {
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

		// Try to update with empty parameters.
		$response = $this->update_item_in_cart( $item_key, array() );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test updating item and verifying cart totals.
	 *
	 * Verifies that updating an item properly updates the cart totals
	 * and that the totals are calculated correctly.
	 *
	 * @return void
	 */
	public function test_update_item_and_verify_totals() {
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

		// Verify initial cart total.
		$initial_cart = $this->get_cart();
		$initial_data = $initial_cart->get_data();
		$this->assertEquals( '25.00', $initial_data['totals']['subtotal'] );

		// Update item quantity.
		$response = $this->update_item_in_cart( $item_key, array(
			'quantity'           => 3,
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '75.00', $data['totals']['subtotal'] );
		$this->assertEquals( '75.00', $data['totals']['total'] );
	}
} 