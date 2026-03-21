<?php
/**
 * Test CoCart Update Cart Controller
 *
 * Tests for CoCart update cart API endpoint via POST /cocart/v2/cart/update.
 * The endpoint requires a registered namespace (callback). CoCart ships two:
 * "update-cart" (bulk quantity update) and "update-customer" (billing/shipping details).
 *
 * @package CoCart\\Tests\\Unit
 */

/**
 * Test CoCart Update Cart Controller Class
 *
 * @package CoCart\\Tests\\Unit
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

		$response = $this->rest_request(
			'POST',
			'/cocart/v2/cart/update',
			array(
				'namespace' => 'update-cart',
				'quantity'  => array( $item_key => '3' ),
			)
		);

		// Emit diagnostic info on failure before asserting.
		if ( 200 !== $response->get_status() ) {
			$data = $response->get_data();
			$this->fail(
				sprintf(
					'Expected 200, got %d. Error: %s',
					$response->get_status(),
					is_array( $data ) && isset( $data['message'] ) ? $data['message'] : wp_json_encode( $data )
				)
			);
		}

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test the update-customer callback returns 200 when updating billing details.
	 *
	 * @return void
	 */
	public function test_update_customer_callback_returns_200() {
		$product      = $this->create_product( array( 'regular_price' => '10.00' ) );
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$response = $this->rest_request(
			'POST',
			'/cocart/v2/cart/update',
			array(
				'namespace'  => 'update-customer',
				'first_name' => 'John',
				'last_name'  => 'Doe',
				'email'      => 'john.doe@example.com',
			)
		);

		// Emit diagnostic info on failure before asserting.
		if ( 200 !== $response->get_status() ) {
			$data = $response->get_data();
			$this->fail(
				sprintf(
					'Expected 200, got %d. Error: %s',
					$response->get_status(),
					is_array( $data ) && isset( $data['message'] ) ? $data['message'] : wp_json_encode( $data )
				)
			);
		}

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that an unregistered namespace returns 404.
	 *
	 * @return void
	 */
	public function test_unknown_namespace_returns_404() {
		$response = $this->rest_request(
			'POST',
			'/cocart/v2/cart/update',
			array(
				'namespace' => 'does-not-exist',
			)
		);

		$this->assert_rest_response_status( 404, $response );
	}
}
