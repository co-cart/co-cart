<?php
/**
 * Test CoCart Session Items Controller
 *
 * Tests for CoCart session items API endpoint including authentication,
 * retrieving items, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Session Items Controller Class
 *
 * Tests the session items API endpoint which handles retrieving items in a
 * specific cart session via GET /cocart/v2/session/{session_key}/items.
 *
 * Admin access is simulated via wp_set_current_user() — WC API key header
 * auth is not processed by WP_REST_Server in unit tests.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_Items_Controller extends CoCart_API_V2_Test_Case {

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

		// Grant manage_woocommerce explicitly — WooCommerce adds this at runtime
		// but it may not be available in the test environment.
		$admin = get_user_by( 'id', $this->admin_id );
		$admin->add_cap( 'manage_woocommerce' );
	}

	/**
	 * Test that getting session items requires authentication.
	 *
	 * Verifies that an unauthenticated GET request to the session items
	 * endpoint returns a 401 Unauthorized response.
	 *
	 * @return void
	 */
	public function test_get_session_items_requires_authentication() {
		$this->clear_authentication();

		$response = $this->rest_get( '/cocart/v2/session/test_session_key/items' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting session items for a non-existent session returns 404.
	 *
	 * Verifies that attempting to retrieve items from a session that does
	 * not exist returns an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_session_items_nonexistent_session_returns_404() {
		$this->authenticate_as( $this->admin_id );

		$response = $this->rest_get( '/cocart/v2/session/nonexistent_session_key_xyz/items' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting session items returns items.
	 *
	 * Verifies that an authenticated admin can retrieve items from a
	 * session that has items.
	 *
	 * @return void
	 */
	public function test_get_session_items_returns_items() {
		// Add an item as guest to create a session with items.
		$product      = $this->create_product();
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$cart_key = $this->get_cart_key_from_response( $add_response );
		$this->assertNotEmpty( $cart_key );

		// Get session items as admin.
		$this->authenticate_as( $this->admin_id );
		$response = $this->rest_get( '/cocart/v2/session/' . $cart_key . '/items' );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
	}
}
