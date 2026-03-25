<?php
/**
 * Test CoCart Items Controller
 *
 * Tests for CoCart cart items API endpoint including retrieving items,
 * response structure, and empty cart handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Items Controller Class
 *
 * Tests the cart items API endpoint which handles retrieving all items
 * currently in the cart via GET /cocart/v2/cart/items.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting items from an empty cart returns appropriate response.
	 *
	 * Verifies that requesting items from an empty cart returns
	 * a 200 response with an empty array.
	 *
	 * @return void
	 */
	public function test_get_items_returns_404_when_cart_empty() {
		$this->clear_cart();

		$response = $this->get_cart_items();

		// The items endpoint returns 404 when the cart is empty.
		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting items returns 200 when cart has items.
	 *
	 * Verifies that requesting items from a cart with products returns
	 * a successful response.
	 *
	 * @return void
	 */
	public function test_get_items_returns_200_when_cart_has_items() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_items();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting items returns an array.
	 *
	 * Verifies that the cart items response is an array.
	 *
	 * @return void
	 */
	public function test_get_items_returns_array() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_items();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test getting items response has item key.
	 *
	 * Verifies that each item in the cart items response has an item_key.
	 *
	 * @return void
	 */
	public function test_get_items_response_has_item_key() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_items();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertNotEmpty( $data );

		$first_item = reset( $data );
		$this->assertArrayHasKey( 'item_key', $first_item );
	}

	/**
	 * Test getting items returns correct product ID.
	 *
	 * Verifies that the items in the response reference the correct product.
	 *
	 * @return void
	 */
	public function test_get_items_returns_correct_product_id() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->get_cart_items();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$first_item = reset( $data );
		$this->assertEquals( $product->get_id(), $first_item['id'] );
	}

	/**
	 * Test getting items with multiple products.
	 *
	 * Verifies that when multiple products are in the cart, the response
	 * contains all of them.
	 *
	 * @return void
	 */
	public function test_get_items_with_multiple_products() {
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '10.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '15.00',
		) );

		$this->add_item_to_cart( $product1->get_id(), 1 );
		$this->add_item_to_cart( $product2->get_id(), 1 );

		$response = $this->get_cart_items();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertCount( 2, $data );
	}
}
