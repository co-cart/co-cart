<?php
/**
 * Test CoCart Session Controller
 *
 * Tests for CoCart single session API endpoint including authentication,
 * retrieving a session, and error handling.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Session Controller Class
 *
 * Tests the single session API endpoint which handles retrieving a specific
 * cart session via GET /cocart/v2/session/{session_key}.
 *
 * Admin access is simulated via wp_set_current_user() — WC API key header
 * auth is not processed by WP_REST_Server in unit tests.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_Controller extends CoCart_API_V2_Test_Case {

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
	 * Test that getting a session requires authentication.
	 *
	 * Verifies that an unauthenticated GET request to the session endpoint
	 * returns a 401 Unauthorized response.
	 *
	 * @return void
	 */
	public function test_get_session_requires_authentication() {
		$this->clear_authentication();

		$response = $this->rest_get( '/cocart/v2/session/test_session_key' );

		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting a non-existent session returns 404.
	 *
	 * Verifies that requesting a session that does not exist returns
	 * an appropriate 404 error response.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_session_returns_404() {
		$this->authenticate_as( $this->admin_id );

		$response = $this->rest_get( '/cocart/v2/session/nonexistent_session_key_xyz' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting an existing session returns 200.
	 *
	 * Verifies that an authenticated admin can retrieve a session that exists
	 * in the database.
	 *
	 * @return void
	 */
	public function test_get_existing_session_returns_200() {
		// Add an item as guest to create a session.
		$product      = $this->create_product();
		$add_response = $this->add_item_to_cart( $product->get_id(), 1 );
		$this->assert_rest_response_status( 200, $add_response );

		$cart_key = $this->get_cart_key_from_response( $add_response );
		$this->assertNotEmpty( $cart_key );

		// save_data() no-ops outside a REST request context — insert directly.
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

		$this->authenticate_as( $this->admin_id );
		$response = $this->rest_get( '/cocart/v2/session/' . $cart_key );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}
}
