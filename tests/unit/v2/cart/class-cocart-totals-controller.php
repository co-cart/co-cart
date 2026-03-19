<?php
/**
 * Test CoCart Totals Controller
 *
 * Tests for CoCart totals API endpoints including cart totals retrieval,
 * different total types, and calculation validation.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Totals Controller Class
 *
 * Tests the totals API endpoints which handle retrieving cart totals
 * including subtotals, taxes, shipping, discounts, and fees.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Totals_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting cart totals.
	 *
	 * Verifies that cart totals can be successfully retrieved
	 * and that the response contains the correct totals data.
	 *
	 * @return void
	 */
	public function test_get_cart_totals() {
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

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
		$this->assertEquals( '50.00', $data['totals']['subtotal'] );
		$this->assertEquals( '50.00', $data['totals']['total'] );
	}

	/**
	 * Test getting totals for empty cart.
	 *
	 * Verifies that totals for an empty cart return zero values
	 * and proper response structure.
	 *
	 * @return void
	 */
	public function test_get_totals_for_empty_cart() {
		// Ensure cart is empty.
		$this->clear_cart();

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertEquals( '0.00', $data['totals']['subtotal'] );
		$this->assertEquals( '0.00', $data['totals']['total'] );
	}

	/**
	 * Test getting totals with tax.
	 *
	 * Verifies that cart totals are properly calculated with tax
	 * when tax is enabled.
	 *
	 * @return void
	 */
	public function test_get_totals_with_tax() {
		// Enable tax calculations.
		update_option( 'woocommerce_calc_taxes', 'yes' );

		// Create tax rate.
		$tax_rate = array(
			'tax_rate_country'  => 'US',
			'tax_rate_state'    => '',
			'tax_rate'          => '10.0000',
			'tax_rate_name'     => 'Tax',
			'tax_rate_priority' => '1',
			'tax_rate_compound' => '0',
			'tax_rate_shipping' => '1',
			'tax_rate_order'    => '1',
			'tax_rate_class'    => '',
		);
		WC_Tax::_insert_tax_rate( $tax_rate );

		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '100.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '100.00', $data['totals']['subtotal'] );
		$this->assertEquals( '110.00', $data['totals']['total'] );
		$this->assertArrayHasKey( 'total_tax', $data['totals'] );
		$this->assertEquals( '10.00', $data['totals']['total_tax'] );

		// Clean up.
		update_option( 'woocommerce_calc_taxes', 'no' );
	}

	/**
	 * Test getting totals with shipping.
	 *
	 * Verifies that cart totals are properly calculated with shipping
	 * when shipping is configured.
	 *
	 * @return void
	 */
	public function test_get_totals_with_shipping() {
		// Enable shipping calculations.
		update_option( 'woocommerce_enable_shipping_calc', 'yes' );

		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '50.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Set shipping address.
		$shipping_data = array(
			'country' => 'US',
			'state'   => 'CA',
			'city'    => 'Los Angeles',
			'postcode' => '90210',
		);

		// Get cart totals with shipping.
		$response = $this->get_cart_totals( array(
			'shipping' => $shipping_data,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'shipping_total', $data['totals'] );
		$this->assertArrayHasKey( 'shipping_tax', $data['totals'] );

		// Clean up.
		update_option( 'woocommerce_enable_shipping_calc', 'no' );
	}

	/**
	 * Test getting totals with fees.
	 *
	 * Verifies that cart totals are properly calculated with fees
	 * when fees are added to the cart.
	 *
	 * @return void
	 */
	public function test_get_totals_with_fees() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '100.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Add fee to cart.
		$fee_data = array(
			'name'   => 'Processing Fee',
			'amount' => '5.00',
		);

		// Get cart totals with fees.
		$response = $this->get_cart_totals( array(
			'fees' => array( $fee_data ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '100.00', $data['totals']['subtotal'] );
		$this->assertEquals( '105.00', $data['totals']['total'] );
		$this->assertArrayHasKey( 'fee_total', $data['totals'] );
		$this->assertEquals( '5.00', $data['totals']['fee_total'] );
	}

	/**
	 * Test getting totals with discounts.
	 *
	 * Verifies that cart totals are properly calculated with discounts
	 * when discount coupons are applied.
	 *
	 * @return void
	 */
	public function test_get_totals_with_discounts() {
		// Create test product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '100.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Create coupon.
		$coupon = new WC_Coupon();
		$coupon->set_code( 'TEST10' );
		$coupon->set_amount( 10 );
		$coupon->set_discount_type( 'percent' );
		$coupon->save();

		// Get cart totals with discount.
		$response = $this->get_cart_totals( array(
			'coupons' => array( 'TEST10' ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '100.00', $data['totals']['subtotal'] );
		$this->assertEquals( '90.00', $data['totals']['total'] );
		$this->assertArrayHasKey( 'discount_total', $data['totals'] );
		$this->assertEquals( '10.00', $data['totals']['discount_total'] );
	}

	/**
	 * Test getting totals with session parameter.
	 *
	 * Verifies that cart totals can be retrieved for a specific
	 * cart session and that the session is properly maintained.
	 *
	 * @return void
	 */
	public function test_get_totals_with_session() {
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

		// Get cart totals in session.
		$response = $this->get_cart_totals( array(
			'session' => $session_key,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'session', $data );
		$this->assertEquals( $session_key, $data['session'] );
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test getting totals with invalid session.
	 *
	 * Verifies that attempting to get totals with an invalid session
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_get_totals_with_invalid_session() {
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

		// Try to get totals with invalid session.
		$response = $this->get_cart_totals( array(
			'session' => 'invalid_session',
		) );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting totals with multiple items.
	 *
	 * Verifies that cart totals are properly calculated when multiple
	 * items with different prices are in the cart.
	 *
	 * @return void
	 */
	public function test_get_totals_with_multiple_items() {
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

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '80.00', $data['totals']['subtotal'] );
		$this->assertEquals( '80.00', $data['totals']['total'] );
	}

	/**
	 * Test getting totals with variable products.
	 *
	 * Verifies that cart totals are properly calculated when variable
	 * products with different variation prices are in the cart.
	 *
	 * @return void
	 */
	public function test_get_totals_with_variable_products() {
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

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '65.00', $data['totals']['subtotal'] );
		$this->assertEquals( '65.00', $data['totals']['total'] );
	}

	/**
	 * Test getting totals with on-sale products.
	 *
	 * Verifies that cart totals are properly calculated when products
	 * on sale are in the cart.
	 *
	 * @return void
	 */
	public function test_get_totals_with_sale_products() {
		// Create test product with sale price.
		$product = $this->create_product( array(
			'name'          => 'Sale Product',
			'regular_price' => '50.00',
			'sale_price'    => '30.00',
		) );

		// Add item to cart.
		$this->add_item_to_cart( array(
			'product_id' => $product->get_id(),
			'quantity'   => 1,
		) );

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertEquals( '30.00', $data['totals']['subtotal'] );
		$this->assertEquals( '30.00', $data['totals']['total'] );
	}

	/**
	 * Test getting totals with return cart parameter.
	 *
	 * Verifies that when return_cart parameter is true, the response
	 * includes the full cart data along with totals.
	 *
	 * @return void
	 */
	public function test_get_totals_with_return_cart() {
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

		// Get cart totals with return cart.
		$response = $this->get_cart_totals( array(
			'return_cart' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart', $data );
		$this->assertArrayHasKey( 'items', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data['cart'] );
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test getting totals with return cart items parameter.
	 *
	 * Verifies that when return_cart_items parameter is true, the response
	 * includes only the cart items data along with totals.
	 *
	 * @return void
	 */
	public function test_get_totals_with_return_cart_items() {
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

		// Get cart totals with return cart items.
		$response = $this->get_cart_totals( array(
			'return_cart_items' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test getting totals response structure.
	 *
	 * Verifies that the get totals response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_get_totals_response_structure() {
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

		// Get cart totals.
		$response = $this->get_cart_totals();
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'totals', $data );
		$this->assertArrayHasKey( 'subtotal', $data['totals'] );
		$this->assertArrayHasKey( 'total', $data['totals'] );
		$this->assertArrayHasKey( 'total_tax', $data['totals'] );
		$this->assertArrayHasKey( 'shipping_total', $data['totals'] );
		$this->assertArrayHasKey( 'shipping_tax', $data['totals'] );
		$this->assertArrayHasKey( 'discount_total', $data['totals'] );
		$this->assertArrayHasKey( 'fee_total', $data['totals'] );

		// Check data types.
		$this->assertIsString( $data['totals']['subtotal'] );
		$this->assertIsString( $data['totals']['total'] );
		$this->assertIsString( $data['totals']['total_tax'] );
		$this->assertIsString( $data['totals']['shipping_total'] );
		$this->assertIsString( $data['totals']['shipping_tax'] );
		$this->assertIsString( $data['totals']['discount_total'] );
		$this->assertIsString( $data['totals']['fee_total'] );
	}
} 