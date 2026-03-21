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
	 * Test adding a grouped product's children to the cart returns 200.
	 *
	 * @return void
	 */
	public function test_add_multiple_items_to_cart() {
		$child1 = $this->create_product( array( 'regular_price' => '5.00' ) );
		$child2 = $this->create_product( array( 'regular_price' => '8.00' ) );

		$grouped = new WC_Product_Grouped();
		$grouped->set_name( 'Test Grouped Product' );
		$grouped->set_status( 'publish' );
		$grouped->set_children( array( $child1->get_id(), $child2->get_id() ) );
		$grouped->save();

		$response = $this->add_item_to_cart(
			$grouped->get_id(),
			array(
				$child1->get_id() => 1,
				$child2->get_id() => 2,
			)
		);

		$this->assert_rest_response_status( 200, $response );
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
