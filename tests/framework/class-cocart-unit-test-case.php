<?php
/**
 * CoCart Unit Test Case
 *
 * Provides CoCart-specific testing functionality.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * CoCart Unit Test Case Class
 *
 * Base test case for CoCart unit tests that provides common functionality
 * for creating test data and making assertions.
 *
 * @package CoCart\Tests\Framework
 */
abstract class CoCart_Unit_Test_Case extends WP_UnitTestCase {

	/**
	 * Set up test environment.
	 *
	 * Ensures WooCommerce is loaded and clears any existing cart
	 * before each test to ensure a clean state.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure WooCommerce is loaded.
		if ( ! class_exists( 'WooCommerce' ) ) {
			$this->markTestSkipped( 'WooCommerce is not available.' );
		}

		// Clear any existing cart directly — do not use $this->clear_cart() here
		// because subclasses override it to make REST requests, and $this->server
		// is not yet initialized at this point in the set_up() chain.
		if ( function_exists( 'wc_empty_cart' ) ) {
			wc_empty_cart();
		}
	}

	/**
	 * Tear down test environment.
	 *
	 * Clears the cart after each test to ensure no leftover items
	 * affect subsequent tests.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		// Clear cart directly for the same reason as in set_up().
		if ( function_exists( 'wc_empty_cart' ) ) {
			wc_empty_cart();
		}

		parent::tearDown();
	}

	/**
	 * Clear the cart.
	 *
	 * Removes all items from the WooCommerce cart to ensure
	 * a clean state for testing.
	 *
	 * @return void
	 */
	protected function clear_cart() {
		if ( function_exists( 'wc_empty_cart' ) ) {
			wc_empty_cart();
		}
	}

	/**
	 * Create a test product.
	 *
	 * Creates a simple WooCommerce product for testing purposes.
	 * The product is automatically saved to the database.
	 *
	 * @param array $args {
	 *     Optional. Product configuration arguments.
	 *
	 *     @type string $name          Product name. Default 'Test Product'.
	 *     @type string $type          Product type. Default 'simple'.
	 *     @type string $regular_price Regular price. Default '10.00'.
	 *     @type string $price         Current price. Default '10.00'.
	 *     @type string $status        Product status. Default 'publish'.
	 * }
	 * @return WC_Product_Simple The created product object.
	 */
	protected function create_product( $args = array() ) {
		$defaults = array(
			'name'           => 'Test Product',
			'type'           => 'simple',
			'regular_price'  => '10.00',
			'price'          => '10.00',
			'sale_price'     => null,
			'status'         => 'publish',
			'stock_status'   => 'instock',
			'manage_stock'   => false,
			'stock_quantity' => null,
		);

		$args = wp_parse_args( $args, $defaults );

		$product = new WC_Product_Simple();
		$product->set_name( $args['name'] );
		$product->set_regular_price( $args['regular_price'] );
		if ( null !== $args['sale_price'] ) {
			$product->set_sale_price( $args['sale_price'] );
			$product->set_price( $args['sale_price'] );
		} else {
			$product->set_price( $args['price'] );
		}
		$product->set_status( $args['status'] );
		$product->set_stock_status( $args['stock_status'] );
		$product->set_manage_stock( $args['manage_stock'] );
		if ( null !== $args['stock_quantity'] ) {
			$product->set_stock_quantity( $args['stock_quantity'] );
		}
		$product->save();

		return $product;
	}

	/**
	 * Create a test customer.
	 *
	 * Creates a WooCommerce customer for testing purposes.
	 * The customer is automatically saved to the database.
	 *
	 * @param array $args {
	 *     Optional. Customer configuration arguments.
	 *
	 *     @type string $email      Customer email address. Default 'test@example.com'.
	 *     @type string $first_name Customer first name. Default 'Test'.
	 *     @type string $last_name  Customer last name. Default 'Customer'.
	 *     @type string $username   Customer username. Default 'testcustomer'.
	 * }
	 * @return WC_Customer The created customer object.
	 */
	protected function create_customer( $args = array() ) {
		$defaults = array(
			'email'      => 'test@example.com',
			'first_name' => 'Test',
			'last_name'  => 'Customer',
			'username'   => 'testcustomer',
		);

		$args = wp_parse_args( $args, $defaults );

		$customer = new WC_Customer();
		$customer->set_email( $args['email'] );
		$customer->set_first_name( $args['first_name'] );
		$customer->set_last_name( $args['last_name'] );
		$customer->set_username( $args['username'] );
		$customer->save();

		return $customer;
	}

	/**
	 * Create a test order.
	 *
	 * Creates a WooCommerce order for testing purposes.
	 * The order is automatically saved to the database.
	 *
	 * @param array $args {
	 *     Optional. Order configuration arguments.
	 *
	 *     @type string $status Order status. Default 'pending'.
	 * }
	 * @return WC_Order The created order object.
	 */
	protected function create_order( $args = array() ) {
		$defaults = array(
			'status' => 'pending',
		);

		$args = wp_parse_args( $args, $defaults );

		$order = wc_create_order( $args );
		return $order;
	}

	/**
	 * Assert that a response has the expected status code.
	 *
	 * Helper method to check if a response object has the expected
	 * HTTP status code.
	 *
	 * @param int    $expected Expected HTTP status code.
	 * @param object $response Response object with get_status() method.
	 * @return void
	 */
	protected function assert_response_status( $expected, $response ) {
		$this->assertEquals( $expected, $response->get_status() );
	}

	/**
	 * Assert that a response has the expected content type.
	 *
	 * Helper method to check if a response object has the expected
	 * content type header.
	 *
	 * @param string $expected Expected content type (e.g., 'application/json').
	 * @param object $response Response object with get_headers() method.
	 * @return void
	 */
	protected function assert_response_content_type( $expected, $response ) {
		$this->assertEquals( $expected, $response->get_headers()['content-type'] );
	}

	/**
	 * Assert that a response contains expected data.
	 *
	 * Helper method to check if a response object contains specific
	 * key-value pairs in its data.
	 *
	 * @param array  $expected Expected data as key-value pairs.
	 * @param object $response Response object with get_data() method.
	 * @return void
	 */
	protected function assert_response_contains( $expected, $response ) {
		$data = $response->get_data();
		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $data );
			$this->assertEquals( $value, $data[ $key ] );
		}
	}
}
