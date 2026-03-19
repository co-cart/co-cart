<?php
/**
 * Test CoCart Batch Operations Controller
 *
 * Tests for CoCart batch operations API endpoints including batch cart
 * operations, multiple item processing, and validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Batch Operations Controller Class
 *
 * Tests the batch operations API endpoints which handle multiple cart
 * operations in a single request including adding, updating, and removing
 * multiple items simultaneously.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Batch_Operations_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test batch add items to cart.
	 *
	 * Verifies that multiple items can be added to the cart in a single
	 * batch operation and that all items are properly added.
	 *
	 * @return void
	 */
	public function test_batch_add_items_to_cart() {
		// Create test products.
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		// Batch add items to cart.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product1->get_id(),
					'quantity'   => 2,
				),
				array(
					'product_id' => $product2->get_id(),
					'quantity'   => 1,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'add', $data['results'] );
		$this->assertCount( 2, $data['results']['add'] );

		// Verify items were added to cart.
		$cart_response = $this->get_cart();
		$cart_data = $cart_response->get_data();
		$this->assertCount( 2, $cart_data['items'] );
	}

	/**
	 * Test batch update items in cart.
	 *
	 * Verifies that multiple items can be updated in the cart in a single
	 * batch operation and that all updates are properly applied.
	 *
	 * @return void
	 */
	public function test_batch_update_items_in_cart() {
		// Create test products.
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		// Add items to cart first.
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

		// Batch update items in cart.
		$batch_data = array(
			'update' => array(
				array(
					'item_key' => $item_key1,
					'quantity' => 3,
				),
				array(
					'item_key' => $item_key2,
					'quantity' => 2,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'update', $data['results'] );
		$this->assertCount( 2, $data['results']['update'] );

		// Verify items were updated in cart.
		$cart_response = $this->get_cart();
		$cart_data = $cart_response->get_data();
		$this->assertEquals( 3, $cart_data['items'][0]['quantity'] );
		$this->assertEquals( 2, $cart_data['items'][1]['quantity'] );
	}

	/**
	 * Test batch remove items from cart.
	 *
	 * Verifies that multiple items can be removed from the cart in a single
	 * batch operation and that all items are properly removed.
	 *
	 * @return void
	 */
	public function test_batch_remove_items_from_cart() {
		// Create test products.
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		// Add items to cart first.
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

		// Batch remove items from cart.
		$batch_data = array(
			'remove' => array(
				array( 'item_key' => $item_key1 ),
				array( 'item_key' => $item_key2 ),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'remove', $data['results'] );
		$this->assertCount( 2, $data['results']['remove'] );

		// Verify items were removed from cart.
		$cart_response = $this->get_cart();
		$cart_data = $cart_response->get_data();
		$this->assertCount( 0, $cart_data['items'] );
	}

	/**
	 * Test mixed batch operations.
	 *
	 * Verifies that different types of operations (add, update, remove)
	 * can be performed in a single batch request.
	 *
	 * @return void
	 */
	public function test_mixed_batch_operations() {
		// Create test products.
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );
		$product3 = $this->create_product( array(
			'name'          => 'Product 3',
			'regular_price' => '35.00',
		) );

		// Add initial items to cart.
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

		// Perform mixed batch operations.
		$batch_data = array(
			'add'    => array(
				array(
					'product_id' => $product3->get_id(),
					'quantity'   => 2,
				),
			),
			'update' => array(
				array(
					'item_key' => $item_key1,
					'quantity' => 3,
				),
			),
			'remove' => array(
				array( 'item_key' => $item_key2 ),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'add', $data['results'] );
		$this->assertArrayHasKey( 'update', $data['results'] );
		$this->assertArrayHasKey( 'remove', $data['results'] );

		// Verify mixed operations were applied.
		$cart_response = $this->get_cart();
		$cart_data = $cart_response->get_data();
		$this->assertCount( 2, $cart_data['items'] );
		$this->assertEquals( 3, $cart_data['items'][0]['quantity'] );
		$this->assertEquals( 2, $cart_data['items'][1]['quantity'] );
	}

	/**
	 * Test batch operations with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the updated cart data after batch operations.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_return_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Batch add item to cart with return cart.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => 2,
				),
			),
		);

		$response = $this->batch_operations( $batch_data, array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
		$this->assertCount( 1, $data['cart']['items'] );
	}

	/**
	 * Test batch operations with session parameter.
	 *
	 * Verifies that batch operations can be performed on a specific
	 * cart session and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$session_key = 'test_session_' . time();

		// Batch add item to cart with session.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => 1,
					'session'    => $session_key,
				),
			),
		);

		$response = $this->batch_operations( $batch_data, array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
	}

	/**
	 * Test batch operations with invalid session.
	 *
	 * Verifies that attempting to perform batch operations with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_invalid_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Batch add item to cart.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => 1,
				),
			),
		);

		// Try to perform batch operations with invalid session.
		$response = $this->batch_operations( $batch_data, array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test batch operations with invalid item keys.
	 *
	 * Verifies that attempting to update or remove items with invalid item keys
	 * returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_invalid_item_keys() {
		// Batch update with invalid item key.
		$batch_data = array(
			'update' => array(
				array(
					'item_key' => 'invalid_key',
					'quantity' => 2,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );

		// Batch remove with invalid item key.
		$batch_data = array(
			'remove' => array(
				array( 'item_key' => 'invalid_key' ),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test batch operations with invalid product IDs.
	 *
	 * Verifies that attempting to add items with invalid product IDs
	 * returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_invalid_product_ids() {
		// Batch add with invalid product ID.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => 99999,
					'quantity'   => 1,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test batch operations with invalid quantities.
	 *
	 * Verifies that attempting to add or update items with invalid quantities
	 * returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_invalid_quantities() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Batch add with zero quantity.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => 0,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );

		// Batch add with negative quantity.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => -1,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test batch operations with empty operations.
	 *
	 * Verifies that attempting to perform batch operations with empty
	 * operation arrays returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_empty_operations() {
		// Empty batch operations.
		$batch_data = array();

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );

		// Batch operations with empty arrays.
		$batch_data = array(
			'add'    => array(),
			'update' => array(),
			'remove' => array(),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test batch operations response structure.
	 *
	 * Verifies that the batch operations response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_batch_operations_response_structure() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Batch add item to cart.
		$batch_data = array(
			'add' => array(
				array(
					'product_id' => $product->get_id(),
					'quantity'   => 1,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'add', $data['results'] );

		// Check data types.
		$this->assertIsArray( $data['results'] );
		$this->assertIsArray( $data['results']['add'] );
	}

	/**
	 * Test batch operations with variable products.
	 *
	 * Verifies that batch operations work correctly with variable products
	 * and their variations.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_variable_products() {
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

		// Batch add variation to cart.
		$batch_data = array(
			'add' => array(
				array(
					'product_id'   => $product->get_id(),
					'variation_id' => $variation->get_id(),
					'quantity'     => 1,
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'add', $data['results'] );
		$this->assertCount( 1, $data['results']['add'] );
	}

	/**
	 * Test batch operations with custom data.
	 *
	 * Verifies that batch operations work correctly with items that have
	 * custom data.
	 *
	 * @return void
	 */
	public function test_batch_operations_with_custom_data() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Batch add item to cart with custom data.
		$batch_data = array(
			'add' => array(
				array(
					'product_id'  => $product->get_id(),
					'quantity'    => 1,
					'custom_data' => array( 'gift_wrap' => true ),
				),
			),
		);

		$response = $this->batch_operations( $batch_data );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'results', $data );
		$this->assertArrayHasKey( 'add', $data['results'] );
		$this->assertCount( 1, $data['results']['add'] );
	}
} 