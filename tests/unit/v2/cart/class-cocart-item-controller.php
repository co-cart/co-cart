<?php
/**
 * Test CoCart Item Controller
 *
 * Tests for CoCart single cart item API endpoint via GET /cocart/v2/cart/item.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Item Controller Class
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a single cart item returns 200.
	 *
	 * @return void
	 */
	public function test_get_cart_item_returns_200() {
		$product      = $this->create_product( array( 'regular_price' => '15.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$this->assertNotEmpty( $item_key );

		// Route is /cart/item/{item_key} — item_key is in the URL path.
		$response = $this->rest_get( '/cocart/v2/cart/item/' . $item_key );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent cart item returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_cart_item_returns_404() {
		$response = $this->rest_get( '/cocart/v2/cart/item/nonexistentitemkeyxyz' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test cart item response contains expected keys.
	 *
	 * @return void
	 */
	public function test_cart_item_response_structure() {
		$product      = $this->create_product( array( 'regular_price' => '15.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$item_key     = $this->get_item_key_from_response( $add_response );

		$response = $this->rest_get( '/cocart/v2/cart/item/' . $item_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'item_key', $data );
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'quantity', $data );
		$this->assertEquals( $item_key, $data['item_key'] );
	}
}
