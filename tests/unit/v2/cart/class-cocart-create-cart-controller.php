<?php
/**
 * Test CoCart Create Cart Controller
 *
 * Tests for CoCart create cart API endpoint including guest cart creation,
 * response structure, and access control.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Create Cart Controller Class
 *
 * Tests the create cart API endpoint which handles creating a new empty
 * guest cart via POST /cocart/v2/cart.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Create_Cart_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test creating a cart as a guest returns 200.
	 *
	 * Verifies that a guest user can create a new empty cart
	 * and receives a successful response.
	 *
	 * @return void
	 */
	public function test_create_cart_as_guest_returns_200() {
		$response = $this->create_cart();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test create cart response has cart key.
	 *
	 * Verifies that the response from creating a cart includes
	 * a non-empty cart key.
	 *
	 * @return void
	 */
	public function test_create_cart_response_has_cart_key() {
		$response = $this->create_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'cart_key', $data );
		$this->assertNotEmpty( $data['cart_key'] );
	}

	/**
	 * Test create cart response has message.
	 *
	 * Verifies that the response from creating a cart includes
	 * a message field.
	 *
	 * @return void
	 */
	public function test_create_cart_response_has_message() {
		$response = $this->create_cart();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'message', $data );
	}

	/**
	 * Test that newly created cart keys are unique.
	 *
	 * Verifies that two consecutive cart creation requests produce
	 * different cart keys.
	 *
	 * @return void
	 */
	public function test_create_cart_keys_are_unique() {
		$response1 = $this->create_cart();
		$response2 = $this->create_cart();

		$this->assert_rest_response_status( 200, $response1 );
		$this->assert_rest_response_status( 200, $response2 );

		$data1 = $response1->get_data();
		$data2 = $response2->get_data();

		$this->assertNotEquals( $data1['cart_key'], $data2['cart_key'] );
	}
}
