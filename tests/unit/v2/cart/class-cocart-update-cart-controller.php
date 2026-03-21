<?php
/**
 * Test CoCart Update Cart Controller
 *
 * Tests for CoCart update cart API endpoint via PUT /cocart/v2/cart/update.
 * The endpoint requires a registered namespace (callback). CoCart ships two:
 * "update-cart" (bulk quantity update) and "update-customer" (billing/shipping details).
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
	 * Test the update-cart callback updates item quantities and returns 200.
	 *
	 * @return void
	 */
	public function test_update_cart_bulk_quantities_returns_200() {
		$product      = $this->create_product( array( 'regular_price' => '10.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$item_key = $this->get_item_key_from_response( $add_response );
		$this->assertNotEmpty( $item_key );

		// Route accepts POST (WP_REST_Server::CREATABLE = 'POST'), not PUT.
		$response = $this->rest_request( 'POST', '/cocart/v2/cart/update', array(
			'namespace' => 'update-cart',
			'quantity'  => array( $item_key => '3' ),
		) );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test the update-cart callback response contains expected cart structure.
	 *
	 * @return void
	 */
	public function test_update_cart_returns_cart_structure() {
		$product      = $this->create_product( array( 'regular_price' => '10.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$item_key     = $this->get_item_key_from_response( $add_response );

		$response = $this->rest_request( 'POST', '/cocart/v2/cart/update', array(
			'namespace' => 'update-cart',
			'quantity'  => array( $item_key => '2' ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertArrayHasKey( 'totals', $data );
	}

	/**
	 * Test the update-customer callback returns 200 when updating billing details.
	 *
	 * @return void
	 */
	public function test_update_customer_callback_returns_200() {
		$product = $this->create_product( array( 'regular_price' => '10.00' ) );
		$this->add_item_to_cart( $product->get_id(), 1 );

		$response = $this->rest_request( 'POST', '/cocart/v2/cart/update', array(
			'namespace' => 'update-customer',
			'email'     => 'test@example.com',
			'country'   => 'US',
		) );

		$this->assert_rest_response_status( 200, $response );
	}
}
