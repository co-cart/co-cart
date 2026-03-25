<?php
/**
 * Test CoCart v1 Cart Controller
 *
 * @package CoCart\Tests\Unit\V1
 */

class Test_CoCart_V1_Cart_Controller extends CoCart_API_V1_Test_Case {

	public function test_get_cart_when_empty() {
		$response = $this->get_cart();

		$this->assert_rest_response_status( 200, $response );
		$this->assert_rest_response_content_type( 'application/json', $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertEmpty( $data['items'] );
		$this->assertArrayHasKey( 'item_count', $data );
		$this->assertEquals( 0, $data['item_count'] );
	}

	public function test_add_item_to_cart() {
		$product = $this->create_product();

		$response = $this->add_item_to_cart( $product->get_id(), 2 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 1, $data['items'] );

		$item = $data['items'][0];
		$this->assertEquals( $product->get_id(), $item['product_id'] );
		$this->assertEquals( 2, $item['quantity'] );
	}

	public function test_add_invalid_product_to_cart() {
		$response = $this->add_item_to_cart( 99999, 1 );

		$this->assert_rest_response_status( 400, $response );
		$this->assert_rest_response_error( 'cocart_product_not_found', $response );
	}

	public function test_remove_item_from_cart() {
		$product = $this->create_product();

		// Add item to cart.
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );

		// Remove item from cart.
		$response = $this->remove_item_from_cart( $item_key );

		$this->assert_rest_response_status( 200, $response );

		// Verify cart is empty.
		$this->assert_cart_is_empty();
	}

	public function test_update_item_quantity_in_cart() {
		$product = $this->create_product();

		// Add item to cart.
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );

		// Update item quantity.
		$response = $this->update_item_in_cart( $item_key, 3 );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( 1, $data['items'] );

		$item = $data['items'][0];
		$this->assertEquals( 3, $item['quantity'] );
	}

	public function test_clear_cart() {
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );

		// Add items to cart.
		$this->add_item_to_cart( $product1->get_id(), 1 );
		$this->add_item_to_cart( $product2->get_id(), 2 );

		// Verify cart has items.
		$this->assert_cart_has_items( 2 );

		// Clear cart.
		$response = $this->clear_cart();

		$this->assert_rest_response_status( 200, $response );

		// Verify cart is empty.
		$this->assert_cart_is_empty();
	}

	public function test_get_cart_totals() {
		$product = $this->create_product( array( 'regular_price' => '15.00' ) );

		// Add item to cart.
		$this->add_item_to_cart( $product->get_id(), 2 );

		// Get cart totals.
		$response = $this->get_cart_totals();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'total', $data );
		$this->assertEquals( '30.00', $data['total'] );
	}

	public function test_get_cart_count() {
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );

		// Add items to cart.
		$this->add_item_to_cart( $product1->get_id(), 1 );
		$this->add_item_to_cart( $product2->get_id(), 3 );

		// Get cart count.
		$response = $this->get_cart_count();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'count', $data );
		$this->assertEquals( 4, $data['count'] );
	}

	public function test_get_products() {
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );

		$response = $this->get_products();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'products', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['products'] ) );
	}

	public function test_get_single_product() {
		$product = $this->create_product( array( 'name' => 'Test Product' ) );

		$response = $this->get_product( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'product', $data );
		$this->assertEquals( $product->get_id(), $data['product']['id'] );
		$this->assertEquals( 'Test Product', $data['product']['name'] );
	}

	public function test_get_product_categories() {
		$response = $this->get_product_categories();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
	}

	public function test_get_product_attributes() {
		$response = $this->get_product_attributes();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
	}
} 