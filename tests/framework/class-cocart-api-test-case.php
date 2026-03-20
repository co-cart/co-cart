<?php
/**
 * CoCart API Test Case
 *
 * Provides CoCart API-specific testing functionality.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * CoCart API Test Case Class
 *
 * Base test case for CoCart API testing that provides common functionality
 * shared between API versions, including WooCommerce API key authentication
 * and admin endpoint support.
 *
 * @package CoCart\Tests\Framework
 */
abstract class CoCart_API_Test_Case extends CoCart_REST_Test_Case {

	/**
	 * Set up test environment.
	 *
	 * Ensures CoCart is loaded and available for testing.
	 * This method is called before each test method.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure CoCart is loaded.
		if ( ! class_exists( 'CoCart' ) ) {
			$this->markTestSkipped( 'CoCart is not available.' );
		}
	}

	/**
	 * Create WooCommerce API key for testing.
	 *
	 * Creates a test API key with specified permissions that can be used
	 * for authenticating admin endpoint requests like the sessions API.
	 *
	 * @since 1.0.0
	 * @param array $args {
	 *     Optional. API key configuration arguments.
	 *
	 *     @type string $description Description for the API key. Default 'Test API Key'.
	 *     @type int    $user        User ID to associate with the key. Default 1.
	 *     @type string $permissions Key permissions. Accepts 'read', 'write', 'read_write'. Default 'read_write'.
	 * }
	 * @return array {
	 *     API key data containing authentication credentials.
	 *
	 *     @type int    $key_id          The key ID.
	 *     @type int    $user_id         The user ID associated with the key.
	 *     @type string $description     The key description.
	 *     @type string $permissions     The key permissions.
	 *     @type string $consumer_key    The consumer key for authentication.
	 *     @type string $consumer_secret The consumer secret for authentication.
	 *     @type string $truncated_key   The truncated key for display.
	 * }
	 */
	protected function create_wc_api_key( $args = array() ) {
		$defaults = array(
			'description' => 'Test API Key',
			'user'        => 1,
			'permissions' => 'read_write',
		);

		$args = wp_parse_args( $args, $defaults );

		// Create API key using WooCommerce functions.
		$consumer_key    = 'ck_' . wc_rand_hash();
		$consumer_secret = 'cs_' . wc_rand_hash();

		$key_data = array(
			'key_id'          => 1,
			'user_id'         => $args['user'],
			'description'     => $args['description'],
			'permissions'     => $args['permissions'],
			'consumer_key'    => $consumer_key,
			'consumer_secret' => $consumer_secret,
			'truncated_key'   => substr( $consumer_key, -7 ),
		);

		// Store the key data for testing purposes.
		update_option( 'woocommerce_api_keys', array( $key_data ) );

		return $key_data;
	}

	/**
	 * Authenticate with WooCommerce API key.
	 *
	 * Creates the proper Authorization header for WooCommerce API key
	 * authentication using Basic Auth format.
	 *
	 * @since 1.0.0
	 * @param array $key_data {
	 *     API key data from create_wc_api_key().
	 *
	 *     @type string $consumer_key    The consumer key.
	 *     @type string $consumer_secret The consumer secret.
	 * }
	 * @return array {
	 *     Headers array for authentication.
	 *
	 *     @type string $Authorization Basic Auth header with encoded credentials.
	 * }
	 */
	protected function authenticate_with_wc_api_key( $key_data ) {
		$headers = array(
			'Authorization' => 'Basic ' . base64_encode( $key_data['consumer_key'] . ':' . $key_data['consumer_secret'] ),
		);

		return $headers;
	}

	/**
	 * Make authenticated request to admin endpoints.
	 *
	 * Creates a WooCommerce API key if not provided and makes an authenticated
	 * request to admin endpoints that require API key authentication.
	 *
	 * @since 1.0.0
	 * @param string $method   HTTP method (GET, POST, PUT, DELETE).
	 * @param string $endpoint API endpoint path.
	 * @param array  $params   Request parameters to send.
	 * @param array  $key_data Optional. API key data. If not provided, creates a new key.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function authenticated_admin_request( $method, $endpoint, $params = array(), $key_data = null ) {
		if ( ! $key_data ) {
			$key_data = $this->create_wc_api_key();
		}

		$headers = $this->authenticate_with_wc_api_key( $key_data );

		return $this->rest_request( $method, $endpoint, $params, $headers );
	}

	/**
	 * Get sessions via CoCart API (requires admin authentication).
	 *
	 * Retrieves all active cart sessions. This endpoint requires WooCommerce
	 * API key authentication with read permissions.
	 *
	 * @since 1.0.0
	 * @param array $key_data Optional. API key data. If not provided, creates a new key.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_sessions( $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', '/cocart/v2/sessions', array(), $key_data );
	}

	/**
	 * Get session via CoCart API (requires admin authentication).
	 *
	 * Retrieves a specific cart session by cart key. This endpoint requires
	 * WooCommerce API key authentication with read permissions.
	 *
	 * @since 1.0.0
	 * @param string $cart_key The cart key to retrieve.
	 * @param array  $key_data Optional. API key data. If not provided, creates a new key.
	 * @return WP_REST_Response The REST API response object.
	 */
	protected function get_session( $cart_key, $key_data = null ) {
		return $this->authenticated_admin_request( 'GET', '/cocart/v2/session/' . $cart_key, array(), $key_data );
	}

	/**
	 * Assert that cart is empty from response.
	 *
	 * Helper method to assert that a cart response indicates an empty cart.
	 * Checks for 200 status and empty items array.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Response $response The REST API response object.
	 * @return void
	 */
	protected function assert_cart_is_empty_from_response( $response ) {
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertEmpty( $data['items'] );
	}

	/**
	 * Assert that cart contains expected number of items from response.
	 *
	 * Helper method to assert that a cart response contains the expected
	 * number of items. Checks for 200 status and correct item count.
	 *
	 * @since 1.0.0
	 * @param int              $expected Expected number of items in the cart.
	 * @param WP_REST_Response $response The REST API response object.
	 * @return void
	 */
	protected function assert_cart_has_items_from_response( $expected, $response ) {
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );
		$this->assertCount( $expected, $data['items'] );
	}

	/**
	 * Assert that cart contains a specific product from response.
	 *
	 * Helper method to assert that a cart response contains a specific
	 * product by ID. Checks for 200 status and product presence.
	 *
	 * @since 1.0.0
	 * @param int              $product_id The product ID to check for.
	 * @param WP_REST_Response $response   The REST API response object.
	 * @return void
	 */
	protected function assert_cart_contains_product_from_response( $product_id, $response ) {
		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'items', $data );

		$found = false;
		foreach ( $data['items'] as $item ) {
			if ( isset( $item['id'] ) && $item['id'] === $product_id ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, "Cart does not contain product with ID {$product_id}" );
	}

	/**
	 * Get cart key from response.
	 *
	 * Extracts the cart key from a cart response. Useful for subsequent
	 * operations that require the cart key.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Response $response The REST API response object.
	 * @return string|null The cart key if found, null otherwise.
	 */
	protected function get_cart_key_from_response( $response ) {
		$data = $response->get_data();
		return isset( $data['cart_key'] ) ? $data['cart_key'] : null;
	}

	/**
	 * Get item key from response.
	 *
	 * Extracts the item key from a cart response. Useful for subsequent
	 * operations like updating or removing items.
	 *
	 * @since 1.0.0
	 * @param WP_REST_Response $response The REST API response object.
	 * @param int              $index    The index of the item (default 0).
	 * @return string|null The item key if found, null otherwise.
	 */
	protected function get_item_key_from_response( $response, $index = 0 ) {
		$data  = $response->get_data();
		$items = isset( $data['items'] ) ? array_values( $data['items'] ) : array();
		if ( isset( $items[ $index ]['item_key'] ) ) {
			return $items[ $index ]['item_key'];
		}
		return null;
	}
}
