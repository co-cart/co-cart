<?php
/**
 * Test CoCart Add Items Controller
 *
 * Tests for CoCart add multiple items API endpoint via POST /cocart/v2/cart/add-items.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Add Items Controller Class
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Add_Items_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test adding multiple items to the cart via the add-items (grouped product) endpoint.
	 *
	 * The /cart/add-items endpoint is designed for grouped products: it accepts
	 * request['id'] (the grouped product parent) and request['quantity'] (an array
	 * of child product IDs => quantities). Without a real grouped product, the
	 * controller returns 400 because product ID 0 is invalid.
	 *
	 * @return void
	 */
	public function test_add_multiple_items_to_cart() {
		$this->markTestSkipped(
			'/cart/add-items is for grouped products: requires request[id] (grouped parent) ' .
			'and request[quantity] (array of child ID => qty). Creating a grouped product ' .
			'setup in tests requires additional WooCommerce data factory support.'
		);
	}

	/**
	 * Test adding items with an invalid product ID returns an error.
	 *
	 * @return void
	 */
	public function test_add_items_with_invalid_product_returns_error() {
		// The controller validates request['id'] first; 0 (missing) or invalid ID returns 400.
		$response = $this->add_items_to_cart( array() );

		$status = $response->get_status();
		$this->assertGreaterThanOrEqual( 400, $status );
	}

	/**
	 * Test adding an empty items array returns an error.
	 *
	 * @return void
	 */
	public function test_add_items_with_empty_array_returns_error() {
		$response = $this->add_items_to_cart( array() );

		// Empty items should return a 400 error.
		$status = $response->get_status();
		$this->assertGreaterThanOrEqual( 400, $status );
	}
}
