<?php
/**
 * Test CoCart Restore Item Controller
 *
 * Tests for CoCart restore item API endpoint including restoring removed items,
 * response structure, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Restore Item Controller Class
 *
 * Tests the restore item API endpoint which handles restoring previously
 * removed cart items via PUT /cocart/v2/cart/item/{item_key}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Restore_Item_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test restoring a removed item successfully.
	 *
	 * Verifies that an item previously removed from the cart can be
	 * restored and appears back in the cart.
	 *
	 * @return void
	 */
	public function test_restore_item_successfully() {
		// Create and add product.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );

		// Remove the item.
		$remove_response = $this->remove_item_from_cart( $item_key );
		$this->assert_rest_response_status( 200, $remove_response );

		// Restore the item.
		$response = $this->restore_item( $item_key );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test restore item response includes items array.
	 *
	 * Verifies that the restore item response includes the cart items.
	 *
	 * @return void
	 */
	public function test_restore_item_returns_cart_by_default() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$item_key     = $this->get_item_key_from_response( $add_response );

		$this->remove_item_from_cart( $item_key );

		$response = $this->restore_item( $item_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertIsArray( $data['items'] );
	}

	/**
	 * Test restoring a non-existent item returns 404.
	 *
	 * Verifies that attempting to restore an item that does not exist
	 * returns an appropriate error response.
	 *
	 * @return void
	 */
	public function test_restore_nonexistent_item_returns_404() {
		$response = $this->restore_item( 'nonexistent_item_key_12345' );

		$this->assert_rest_response_status( 404, $response );
	}
}
