<?php
/**
 * Test Cart Permission Check
 *
 * Tests that the cart controller's check_permission() method correctly
 * prevents unauthenticated access to registered user carts while
 * allowing legitimate guest and authenticated requests.
 *
 * @package CoCart\Tests\Unit\V2\Cart
 */

class Test_CoCart_Cart_Permission extends CoCart_API_V2_Test_Case {

	/**
	 * Test that a guest with no cart_key can access the cart.
	 */
	public function test_guest_no_cart_key_returns_200() {
		$this->clear_authentication();

		$response = $this->get_cart();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that a guest with a non-user cart_key can access the cart.
	 */
	public function test_guest_with_random_cart_key_returns_200() {
		$this->clear_authentication();

		$response = $this->get_cart( array( 'cart_key' => 'abc123' ) );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that a guest passing a registered user's ID as cart_key is blocked.
	 */
	public function test_guest_with_registered_user_id_as_cart_key_returns_403() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );

		$this->clear_authentication();

		$response = $this->get_cart( array( 'cart_key' => (string) $user_id ) );

		$this->assert_rest_response_status( 403, $response );
		$this->assert_rest_response_error( 'cocart_must_authenticate_user', $response );
	}

	/**
	 * Test that a logged-in user with no cart_key can access the cart.
	 */
	public function test_logged_in_user_no_cart_key_returns_200() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );

		$this->authenticate_as( $user_id );

		$response = $this->get_cart();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that a logged-in user passing their own ID as cart_key is allowed.
	 *
	 * This is no longer blocked — the user would get their own cart regardless.
	 */
	public function test_logged_in_user_own_id_as_cart_key_returns_200() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );

		$this->authenticate_as( $user_id );

		$response = $this->get_cart( array( 'cart_key' => (string) $user_id ) );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that a logged-in user with a guest cart_key can access the cart.
	 */
	public function test_logged_in_user_with_guest_cart_key_returns_200() {
		$user_id = $this->factory->user->create( array( 'role' => 'customer' ) );

		$this->authenticate_as( $user_id );

		$response = $this->get_cart( array( 'cart_key' => 'guest_abc123' ) );

		$this->assert_rest_response_status( 200, $response );
	}
}
