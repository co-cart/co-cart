<?php
/**
 * Test CoCart Count Items Controller
 *
 * Tests for CoCart count items API endpoints including counting items
 * in cart, different count formats, and validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Count Items Controller Class
 *
 * Tests the count items API endpoints which handle counting products
 * in the cart including different return formats and validation.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Count_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test counting items in cart.
	 *
	 * Verifies that items in the cart can be successfully counted
	 * and that the response contains the correct count.
	 *
	 * @return void
	 */
	public function test_count_items_in_cart() {
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

		// Count items in cart.
		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertEquals( 2, $data['count'] );
	}

	/**
	 * Test counting items in empty cart.
	 *
	 * Verifies that counting items in an empty cart returns zero
	 * and proper response structure.
	 *
	 * @return void
	 */
	public function test_count_items_in_empty_cart() {
		// Ensure cart is empty.
		$this->clear_cart();

		// Count items in cart.
		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertEquals( 0, $data['count'] );
	}

	/**
	 * Test counting items with numeric return format.
	 *
	 * Verifies that items can be counted with numeric return format
	 * and that the response contains the correct numeric value.
	 *
	 * @return void
	 */
	public function test_count_items_with_numeric_return() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 3,
		) );

		// Count items with numeric return.
		$response = $this->count_items_in_cart( array(
			'return' => 'numeric',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertEquals( 3, $data['count'] );
		$this->assertIsInt( $data['count'] );
	}

	/**
	 * Test counting items with string return format.
	 *
	 * Verifies that items can be counted with string return format
	 * and that the response contains the correct string value.
	 *
	 * @return void
	 */
	public function test_count_items_with_string_return() {
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

		// Count items with string return.
		$response = $this->count_items_in_cart( array(
			'return' => 'string',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertEquals( '1', $data['count'] );
		$this->assertIsString( $data['count'] );
	}

	/**
	 * Test counting items with session parameter.
	 *
	 * Verifies that items can be counted in a specific cart session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_count_items_with_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$session_key = 'test_session_' . time();

		// Add item to cart with session.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 2,
			'session'    => $session_key,
		) );

		// Count items in session.
		$response = $this->count_items_in_cart( array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertEquals( 2, $data['count'] );
	}

	/**
	 * Test counting items with invalid session.
	 *
	 * Verifies that attempting to count items with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_count_items_with_invalid_session() {
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

		// Try to count items with invalid session.
		$response = $this->count_items_in_cart( array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test counting multiple items in cart.
	 *
	 * Verifies that multiple items with different quantities are
	 * properly counted in the cart.
	 *
	 * @return void
	 */
	public function test_count_multiple_items_in_cart() {
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
			'quantity'   => 3,
		) );

		// Count items in cart.
		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 5, $data['count'] );
	}

	/**
	 * Test counting items with variable products.
	 *
	 * Verifies that variable products are properly counted in the cart.
	 *
	 * @return void
	 */
	public function test_count_items_with_variable_products() {
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
			'quantity'     => 2,
		) );

		// Count items in cart.
		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 3, $data['count'] );
	}

	/**
	 * Test counting items after adding and removing.
	 *
	 * Verifies that the count is properly updated when items are
	 * added and removed from the cart.
	 *
	 * @return void
	 */
	public function test_count_items_after_add_and_remove() {
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

		// Count items after adding.
		$count_response1 = $this->count_items_in_cart();
		$count_data1 = $count_response1->get_data();
		$this->assertEquals( 2, $count_data1['count'] );

		// Remove item from cart.
		$item_key = $add_response->get_data()['item_key'];
		$this->remove_item_from_cart( $item_key );

		// Count items after removing.
		$count_response2 = $this->count_items_in_cart();
		$count_data2 = $count_response2->get_data();
		$this->assertEquals( 0, $count_data2['count'] );
	}

	/**
	 * Test counting items with custom data.
	 *
	 * Verifies that items with custom data are properly counted
	 * in the cart.
	 *
	 * @return void
	 */
	public function test_count_items_with_custom_data() {
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

		// Count items in cart.
		$response = $this->count_items_in_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( 1, $data['count'] );
	}

	/**
	 * Test counting items with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the full cart data along with the count.
	 *
	 * @return void
	 */
	public function test_count_items_with_return_cart() {
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

		// Count items with return cart.
		$response = $this->count_items_in_cart( array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
	}

	/**
	 * Test counting items with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the cart items data along with the count.
	 *
	 * @return void
	 */
	public function test_count_items_with_return_cart_items() {
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

		// Count items with return cart items.
		$response = $this->count_items_in_cart( array(
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
	}

	/**
	 * Test counting items with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the cart totals data along with the count.
	 *
	 * @return void
	 */
	public function test_count_items_with_return_cart_totals() {
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

		// Count items with return cart totals.
		$response = $this->count_items_in_cart( array(
			'return_cart_totals' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
	}

	/**
	 * Test counting items with invalid return format.
	 *
	 * Verifies that attempting to count items with an invalid return format
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_count_items_with_invalid_return_format() {
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

		// Try to count items with invalid return format.
		$response = $this->count_items_in_cart( array(
			'return' => 'invalid_format',
		) );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test counting items response structure.
	 *
	 * Verifies that the count items response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_count_items_response_structure() {
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

		// Count items in cart.
		$response = $this->count_items_in_cart();
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'count', $data );

		// Check data types.
		$this->assertIsInt( $data['count'] );
	}
} 