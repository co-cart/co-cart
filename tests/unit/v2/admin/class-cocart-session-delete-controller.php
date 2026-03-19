<?php
/**
 * Test CoCart Session Delete Controller
 *
 * Tests for CoCart session delete API endpoint including authentication,
 * successful deletion, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Session Delete Controller Class
 *
 * Tests the session delete API endpoint which handles deleting a specific
 * cart session via DELETE /cocart/v2/session/{session_key}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_Delete_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test that deleting a session requires authentication.
	 *
	 * Verifies that an unauthenticated DELETE request to the session
	 * endpoint returns a 401 Unauthorized response.
	 *
	 * @return void
	 */
	public function test_delete_session_requires_authentication() {
		$response = $this->rest_delete( '/cocart/v2/session/test_session_key' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test deleting a session successfully.
	 *
	 * Verifies that an authenticated admin can delete a session and
	 * the session is no longer retrievable afterward.
	 *
	 * @return void
	 */
	public function test_delete_session_successfully() {
		// Create a session by adding a product to cart as guest.
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '20.00',
		) );

		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$cart_key = $this->get_cart_key_from_response( $add_response );
		$this->assertNotEmpty( $cart_key );

		// Create WC API key for authentication.
		$key_data = $this->create_wc_api_key();

		// Delete the session.
		$response = $this->delete_session_by_key( $cart_key, $key_data );

		$this->assert_rest_response_status( 200, $response );

		// Verify the session is gone.
		$get_response = $this->get_session_by_key( $cart_key, $key_data );
		$this->assert_rest_response_status( 404, $get_response );
	}

	/**
	 * Test deleting a non-existent session returns 404.
	 *
	 * Verifies that attempting to delete a session that does not exist
	 * returns an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_delete_nonexistent_session_returns_404() {
		$key_data = $this->create_wc_api_key();

		$response = $this->delete_session_by_key( 'nonexistent_session_key_xyz', $key_data );

		$this->assert_rest_response_status( 404, $response );
	}
}
