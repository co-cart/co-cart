<?php
/**
 * Test CoCart Update Cart Controller
 *
 * Tests for CoCart update cart API endpoint via PUT /cocart/v2/cart/update.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Update Cart Controller Class
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Update_Cart_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test updating the cart returns 200.
	 *
	 * @return void
	 */
	public function test_update_cart_returns_200() {
		$response = $this->update_cart();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test update cart response contains expected cart structure.
	 *
	 * @return void
	 */
	public function test_update_cart_returns_cart_structure() {
		$product = $this->create_product( array( 'regular_price' => '10.00' ) );
		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->update_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertArrayHasKey( 'totals', $data );
	}
}
