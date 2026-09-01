<?php
/**
 * Test CoCart Session Handler `cart_key` ownership checks (GHSA-p7mf-cpmj-c859).
 *
 * Regression coverage for the Broken Access Control vulnerability reported
 * against `CoCart_Session_Handler::init_session_cocart()`: an authenticated
 * user of any role could take over another user's cart by supplying that
 * user's numeric WordPress user ID as the `cart_key` request parameter.
 *
 * Also covers the two legitimate cart_key behaviors that must keep working
 * once the ownership check is in place: guest-to-logged-in cart transfer on
 * login, and privileged (shop manager) lookup of a customer's cart by ID.
 *
 * @see https://github.com/co-cart/co-cart/security/advisories/GHSA-p7mf-cpmj-c859
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Session Handler cart_key ownership Class.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Session_IDOR extends CoCart_API_V2_Test_Case {

	/**
	 * Simulate the start of a brand new, independent HTTP request to the
	 * CoCart REST API.
	 *
	 * A single PHPUnit test process reuses global state (`WC()->session`,
	 * `WC()->customer`, `WC()->cart`) across multiple `cocart_v2_request()`
	 * calls, where production gets a fresh PHP process — and therefore fresh
	 * globals — per request. Three things are faked here to match that:
	 *
	 * 1. `CoCart_Session_Handler::get_requested_cart()` reads `cart_key` from
	 *    `$_REQUEST`/`$_SERVER`, not from the `WP_REST_Request` object our
	 *    test helpers build, because in production those superglobals are
	 *    already populated before WordPress ever constructs the request.
	 * 2. `CoCart_Session_Handler::init()` only runs the cart_key-aware
	 *    `init_session_cocart()` branch when `CoCart::is_rest_api_request()`
	 *    is true, which inspects `$_SERVER['REQUEST_URI']`.
	 * 3. `WC()->cart` and `WC()->customer` are forced fresh so the response
	 *    reflects the new session's data, not whatever the previous
	 *    simulated request left sitting in memory. `WC_Cart::get_cart()`
	 *    only auto-loads from session on its very first-ever call per
	 *    process (guarded by `did_action( 'woocommerce_load_cart_from_session' )`,
	 *    which never resets) so later simulated requests need that load
	 *    forced explicitly via the cart's session sub-object.
	 *
	 * @param string|null $cart_key Value to simulate as the incoming
	 *                              `cart_key` request parameter, or null for
	 *                              none.
	 *
	 * @return void
	 */
	protected function simulate_new_request( $cart_key = null ) {
		$_SERVER['REQUEST_URI'] = '/' . rest_get_url_prefix() . '/cocart/v2/cart';

		if ( null !== $cart_key ) {
			$_REQUEST['cart_key'] = $cart_key;
			$_GET['cart_key']     = $cart_key;
		} else {
			unset( $_REQUEST['cart_key'], $_GET['cart_key'] );
		}

		if ( ! class_exists( 'CoCart_Session_Handler' ) ) {
			require_once COCART_FILE_PATH . '/includes/classes/class-cocart-session-handler.php';
		}

		WC()->session = new CoCart_Session_Handler();
		WC()->session->init();

		WC()->customer = new WC_Customer( get_current_user_id(), true );

		WC()->cart = new WC_Cart();

		$session_property = new ReflectionProperty( 'WC_Cart', 'session' );
		$session_property->setAccessible( true );
		$session_property->getValue( WC()->cart )->get_cart_from_session();
	}

	/**
	 * Force the current simulated request's deferred session write.
	 *
	 * `CoCart_Session_Handler::save_data()` normally runs on the 'shutdown'
	 * action, which only fires once at real PHP process end — in production
	 * that's still "before this request's response is fully done", since
	 * each request is its own process, but across simulated requests in one
	 * PHPUnit process it would never run until the entire suite finishes.
	 * Forcing it here makes the next simulated request read genuinely
	 * persisted data, exactly like a real subsequent request would.
	 *
	 * @return void
	 */
	protected function end_request() {
		WC()->session->save_data();
	}

	/**
	 * A Subscriber (a `cocart_user_customer_roles` role) must not be able to
	 * read another user's cart via `cart_key`, nor have it copied into their
	 * own cart_key as a side effect.
	 *
	 * @return void
	 */
	public function test_subscriber_cannot_read_or_hijack_victims_cart() {
		$victim_id   = $this->factory->user->create( array( 'role' => 'customer' ) );
		$attacker_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );

		$victim_product = $this->create_product( array( 'name' => 'Victim Only Product' ) );

		// Victim adds a product to their own cart, then their request ends.
		$this->authenticate_as( $victim_id );
		$this->simulate_new_request();
		$response = $this->add_item_to_cart( $victim_product->get_id() );
		$this->assert_rest_response_status( 200, $response );
		$this->end_request();

		// Attacker requests the victim's cart directly by supplying the
		// victim's numeric user ID as cart_key.
		$this->authenticate_as( $attacker_id );
		$this->simulate_new_request( (string) $victim_id );
		$response = $this->cocart_v2_request( 'GET', 'cart', array( 'cart_key' => (string) $victim_id ) );

		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (string) $attacker_id, $data['cart_key'], 'Attacker should be resolved to their own cart, not the victim\'s.' );
		$this->assertEmpty( $data['items'], 'Attacker must not be able to read the victim\'s cart contents via cart_key.' );
		$this->end_request();

		// Follow-up request with no cart_key: the attacker's own cart must
		// still be empty — the victim's cart must not have been copied in.
		$this->authenticate_as( $attacker_id );
		$this->simulate_new_request();
		$response = $this->cocart_v2_request( 'GET', 'cart' );
		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (string) $attacker_id, $data['cart_key'] );
		$this->assertEmpty( $data['items'], 'Victim\'s cart contents must not have been persisted into the attacker\'s own cart_key.' );
	}

	/**
	 * A Contributor (a role NOT in `cocart_user_customer_roles`, and not
	 * privileged) must not be able to add an item directly to another user's
	 * cart via `cart_key` — the write must land on their own cart instead.
	 *
	 * @return void
	 */
	public function test_contributor_cannot_write_directly_to_victims_cart() {
		$victim_id   = $this->factory->user->create( array( 'role' => 'customer' ) );
		$attacker_id = $this->factory->user->create( array( 'role' => 'contributor' ) );

		$victim_product   = $this->create_product( array( 'name' => 'Victim Existing Product' ) );
		$attacker_product = $this->create_product( array( 'name' => 'Attacker Injected Product' ) );

		// Victim adds a product to their own cart, then their request ends.
		$this->authenticate_as( $victim_id );
		$this->simulate_new_request();
		$response = $this->add_item_to_cart( $victim_product->get_id() );
		$this->assert_rest_response_status( 200, $response );
		$this->end_request();

		// Attacker (contributor role) tries to add an item directly to the
		// victim's cart by supplying the victim's user ID as cart_key.
		$this->authenticate_as( $attacker_id );
		$this->simulate_new_request( (string) $victim_id );
		$response = $this->add_item_to_cart(
			$attacker_product->get_id(),
			1,
			array( 'cart_key' => (string) $victim_id )
		);

		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		// The write must have landed on the attacker's own cart, not the victim's.
		$this->assertEquals( (string) $attacker_id, $data['cart_key'], 'Expected the write to be redirected to the attacker\'s own cart_key, not the victim\'s.' );
		$this->end_request();

		// The victim's cart, read independently, must be unaffected.
		$this->authenticate_as( $victim_id );
		$this->simulate_new_request();
		$response = $this->cocart_v2_request( 'GET', 'cart' );
		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		$this->assertCount( 1, $data['items'] );

		$found = false;
		foreach ( $data['items'] as $item ) {
			if ( (int) $item['id'] === $attacker_product->get_id() ) {
				$found = true;
				break;
			}
		}
		$this->assertFalse( $found, 'Attacker-injected product must not appear in the victim\'s real cart.' );
	}

	/**
	 * Guest-to-logged-in cart transfer on login must keep working: a guest
	 * cart token is never a registered user's ID, so it must always be
	 * allowed through and merged into the now-logged-in user's account.
	 *
	 * @return void
	 */
	public function test_guest_cart_still_transfers_to_logged_in_user() {
		$customer_id = $this->factory->user->create( array( 'role' => 'customer' ) );
		$product     = $this->create_product( array( 'name' => 'Guest Cart Product' ) );

		// Guest adds a product to their cart and gets a generated guest token.
		$this->clear_authentication();
		$this->simulate_new_request();
		$response = $this->add_item_to_cart( $product->get_id() );
		$this->assert_rest_response_status( 200, $response );
		$guest_cart_key = $this->get_cart_key_from_response( $response );
		$this->assertStringStartsWith( 't_', $guest_cart_key );
		$this->end_request();

		// The same browser/client logs in and continues using the guest
		// cart_key it already had — this must merge into their account.
		$this->authenticate_as( $customer_id );
		$this->simulate_new_request( $guest_cart_key );
		$response = $this->cocart_v2_request( 'GET', 'cart', array( 'cart_key' => $guest_cart_key ) );
		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (string) $customer_id, $data['cart_key'], 'Guest cart must transfer to the logged-in user\'s own cart_key.' );
		$this->assertCount( 1, $data['items'] );

		$found = false;
		foreach ( $data['items'] as $item ) {
			if ( (int) $item['id'] === $product->get_id() ) {
				$found = true;
				break;
			}
		}
		$this->assertTrue( $found, 'Guest cart contents must be present after merging into the logged-in user\'s cart.' );
	}

	/**
	 * A shop manager must still be able to open a specific customer's cart
	 * by their user ID (documented admin/POS-style use of cart_key).
	 *
	 * @return void
	 */
	public function test_shop_manager_can_still_access_customer_cart_by_id() {
		$customer_id     = $this->factory->user->create( array( 'role' => 'customer' ) );
		$shop_manager_id = $this->factory->user->create( array( 'role' => 'shop_manager' ) );

		// WooCommerce grants `manage_woocommerce` to the shop_manager role via
		// WC_Install::create_roles() — but only by calling WP_Roles::add_cap(),
		// which updates the raw roles array (and the DB option) without
		// touching the already-cached WP_Role object that WP_User::get_role_caps()
		// actually reads capabilities from. On a from-scratch database (as CI
		// uses, unlike a long-lived local dev DB that already has the option
		// fully populated from an earlier install) that leaves the cached
		// shop_manager WP_Role permanently missing manage_woocommerce for the
		// rest of the test process. Grant it via WP_Role::add_cap() instead,
		// which updates the cached object directly.
		wp_roles()->get_role( 'shop_manager' )->add_cap( 'manage_woocommerce' );

		$product = $this->create_product( array( 'name' => 'Customer Cart Product' ) );

		// Customer adds a product to their own cart, then their request ends.
		$this->authenticate_as( $customer_id );
		$this->simulate_new_request();
		$response = $this->add_item_to_cart( $product->get_id() );
		$this->assert_rest_response_status( 200, $response );
		$this->end_request();

		// Shop manager looks up the customer's cart directly by user ID.
		$this->authenticate_as( $shop_manager_id );
		$this->simulate_new_request( (string) $customer_id );
		$response = $this->cocart_v2_request( 'GET', 'cart', array( 'cart_key' => (string) $customer_id ) );

		$this->assert_rest_response_status( 200, $response );
		$data = $response->get_data();

		$this->assertEquals( (string) $customer_id, $data['cart_key'], 'Shop manager must still be able to view the customer\'s cart by ID.' );
		$this->assertCount( 1, $data['items'] );
	}
}
