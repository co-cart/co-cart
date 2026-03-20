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
 * Admin access is simulated via wp_set_current_user() — WC API key header
 * auth is not processed by WP_REST_Server in unit tests.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_Delete_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected $admin_id;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->admin_id = $this->factory->user->create( array(
			'role' => 'administrator',
		) );

		// WooCommerce grants manage_woocommerce to admins at runtime, but this
		// may not happen in the test environment — grant it explicitly.
		$admin = get_user_by( 'id', $this->admin_id );
		$admin->add_cap( 'manage_woocommerce' );
	}

	/**
	 * Test that deleting a session requires authentication.
	 *
	 * Verifies that an unauthenticated DELETE request to the session
	 * endpoint returns a 401 Unauthorized response.
	 *
	 * @return void
	 */
	public function test_delete_session_requires_authentication() {
		$this->clear_authentication();

		$response = $this->rest_delete( '/cocart/v2/session/test_session_key' );

		$this->assert_rest_response_status( 401, $response );
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
		$this->authenticate_as( $this->admin_id );

		$response = $this->rest_delete( '/cocart/v2/session/nonexistent_session_key_xyz' );

		$this->assert_rest_response_status( 404, $response );
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
		// Add an item as guest to create a session.
		$product      = $this->create_product();
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$cart_key = $this->get_cart_key_from_response( $add_response );
		$this->assertNotEmpty( $cart_key );

		// save_data() requires CoCart::is_rest_api_request() to return true (it checks
		// $this->cart_key && is_rest_api_request()), but that flag is false between
		// requests. Insert directly into the DB to simulate a persisted session.
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'cocart_carts',
			array(
				'cart_key'     => $cart_key,
				'cart_value'   => maybe_serialize( array( 'cart' => maybe_serialize( WC()->session->get( 'cart' ) ) ) ),
				'cart_created' => time(),
				'cart_expiry'  => time() + DAY_IN_SECONDS,
				'cart_source'  => 'cocart',
				'cart_hash'    => '',
			)
		);

		// Delete as admin.
		$this->authenticate_as( $this->admin_id );
		$response = $this->rest_delete( '/cocart/v2/session/' . $cart_key );
		$this->assert_rest_response_status( 200, $response );

		// Verify session is gone.
		$this->authenticate_as( $this->admin_id );
		$get_response = $this->rest_request( 'GET', '/cocart/v2/session/' . $cart_key );
		$this->assert_rest_response_status( 404, $get_response );
	}
}
