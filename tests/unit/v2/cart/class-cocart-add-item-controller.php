<?php
/**
 * Test CoCart Add Item Controller
 *
 * Tests for CoCart add item API endpoints including adding single items
 * to cart, validation, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Add Item Controller Class
 *
 * Tests the add item API endpoints which handle adding products to the cart
 * including validation, quantity handling, and various product types.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Add_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test adding a simple product to cart.
	 *
	 * Verifies that a simple product can be successfully added to the cart
	 * with the correct quantity and price.
	 *
	 * @return void
	 */
	public function test_add_simple_product_to_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 2 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'item_key', $data );
		$this->assertArrayHasKey( 'product_id', $data );
		$this->assertArrayHasKey( 'quantity', $data );
		$this->assertEquals( $product->get_id(), $data['product_id'] );
		$this->assertEquals( 2, $data['quantity'] );
	}

	/**
	 * Test adding a variable product to cart.
	 *
	 * Verifies that a variable product can be successfully added to the cart
	 * with the correct variation attributes.
	 *
	 * @return void
	 */
	public function test_add_variable_product_to_cart() {
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

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'variation_id' => $variation->get_id() ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'item_key', $data );
		$this->assertEquals( $product->get_id(), $data['product_id'] );
		$this->assertEquals( $variation->get_id(), $data['variation_id'] );
	}

	/**
	 * Test adding product with custom data.
	 *
	 * Verifies that a product can be added to the cart with custom data
	 * and that the custom data is properly stored.
	 *
	 * @return void
	 */
	public function test_add_product_with_custom_data() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Custom Product',
			'regular_price' => '20.00',
		) );

		$custom_data = array(
			'custom_field' => 'custom_value',
			'notes'        => 'Special instructions',
		);

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'custom_data' => $custom_data ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'custom_data', $data );
		$this->assertEquals( $custom_data, $data['custom_data'] );
	}

	/**
	 * Test adding product with invalid product ID.
	 *
	 * Verifies that attempting to add a non-existent product returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_invalid_product_to_cart() {
		$response = $this->add_item_to_cart( 99999, 1 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with invalid quantity.
	 *
	 * Verifies that attempting to add a product with invalid quantity
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_product_with_invalid_quantity() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		// Test with zero quantity.
		$response = $this->add_item_to_cart( $product->get_id(), 0 );

		$this->assert_rest_response_status( 400, $response );

		// Test with negative quantity.
		$response = $this->add_item_to_cart( $product->get_id(), -1 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding out of stock product.
	 *
	 * Verifies that attempting to add an out of stock product returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_out_of_stock_product() {
		// Create out of stock product.
		$product = $this->create_product( array(
			'name'         => 'Out of Stock Product',
			'regular_price' => '25.00',
			'stock_status' => 'outofstock',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product exceeding stock quantity.
	 *
	 * Verifies that attempting to add more items than available in stock
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_product_exceeding_stock() {
		// Create product with limited stock.
		$product = $this->create_product( array(
			'name'           => 'Limited Stock Product',
			'regular_price'  => '25.00',
			'manage_stock'   => true,
			'stock_quantity' => 5,
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 10 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with invalid variation.
	 *
	 * Verifies that attempting to add a variable product with invalid
	 * variation ID returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_product_with_invalid_variation() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'variation_id' => 99999 ) );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with missing required attributes.
	 *
	 * Verifies that attempting to add a variable product without required
	 * variation attributes returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_variable_product_without_attributes() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$response = $this->add_item_to_cart( $product->get_id(), 1 );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with variation attributes.
	 *
	 * Verifies that a variable product can be added to the cart using
	 * variation attributes instead of variation ID.
	 *
	 * @return void
	 */
	public function test_add_product_with_variation_attributes() {
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

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'variation' => array( 'pa_color' => 'Red' ) ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( $product->get_id(), $data['product_id'] );
		$this->assertEquals( $variation->get_id(), $data['variation_id'] );
	}

	/**
	 * Test adding product with cart item data.
	 *
	 * Verifies that a product can be added to the cart with additional
	 * cart item data and that the data is properly stored.
	 *
	 * @return void
	 */
	public function test_add_product_with_cart_item_data() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$cart_item_data = array(
			'custom_option' => 'option_value',
			'gift_wrap'     => true,
		);

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'cart_item_data' => $cart_item_data ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart_item_data', $data );
		$this->assertEquals( $cart_item_data, $data['cart_item_data'] );
	}

	/**
	 * Test adding product with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the full cart data.
	 *
	 * @return void
	 */
	public function test_add_product_with_return_cart() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'return_cart' => true ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
	}

	/**
	 * Test adding product with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the cart items data.
	 *
	 * @return void
	 */
	public function test_add_product_with_return_cart_items() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'return_cart_items' => true ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
	}

	/**
	 * Test adding product with return cart totals parameter.
	 *
	 * Verifies that when return_cart_totals parameter is true, the response
	 * includes only the cart totals data.
	 *
	 * @return void
	 */
	public function test_add_product_with_return_cart_totals() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'return_cart_totals' => true ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
	}

	/**
	 * Test adding product with session parameter.
	 *
	 * Verifies that a product can be added to a specific cart session
	 * and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_add_product_with_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$session_key = 'test_session_' . time();

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'session' => $session_key ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
	}

	/**
	 * Test adding product with invalid session.
	 *
	 * Verifies that attempting to add a product with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_product_with_invalid_session() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'session' => '' ) );

		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test adding product with price override.
	 *
	 * Verifies that a product can be added to the cart with a custom price
	 * override and that the custom price is properly applied.
	 *
	 * @return void
	 */
	public function test_add_product_with_price_override() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'price' => '20.00' ) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '20.00', $data['price'] );
	}

	/**
	 * Test adding product with invalid price override.
	 *
	 * Verifies that attempting to add a product with an invalid price
	 * override returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_add_product_with_invalid_price_override() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$response = $this->add_item_to_cart( $product->get_id(), 1, array( 'price' => -10.00 ) );

		$this->assert_rest_response_status( 400, $response );
	}
} 