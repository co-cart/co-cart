<?php
/**
 * Test CoCart Product Brands Controller
 *
 * Tests for CoCart product brands API endpoint including listing brands,
 * response structure, and data integrity.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Brands Controller Class
 *
 * Tests the product brands API endpoint which handles retrieving product
 * brands via GET /cocart/v2/products/brands.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Brands_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Set up test.
	 *
	 * Skip all tests in this class if the product_brand taxonomy does not exist,
	 * as it requires a third-party plugin or CoCart Pro.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		if ( ! taxonomy_exists( 'product_brand' ) ) {
			$this->markTestSkipped( 'product_brand taxonomy is not registered. Skipping brand tests.' );
		}
	}

	/**
	 * Test getting brands returns 200.
	 *
	 * Verifies that the product brands endpoint is reachable
	 * and returns a successful response.
	 *
	 * @return void
	 */
	public function test_get_brands_returns_200() {
		$response = $this->get_product_brands();

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting brands returns an array.
	 *
	 * Verifies that the product brands response is an array.
	 *
	 * @return void
	 */
	public function test_get_brands_returns_array() {
		$response = $this->get_product_brands();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test getting brands with a created brand.
	 *
	 * Verifies that a newly created brand appears in the brands list.
	 *
	 * @return void
	 */
	public function test_get_brands_with_created_brand() {
		$term = wp_insert_term( 'Test Brand', 'product_brand' );
		$this->assertNotWPError( $term );

		$response = $this->get_product_brands();

		$this->assert_rest_response_status( 200, $response );

		$data    = $response->get_data();
		$term_ids = array_column( $data, 'id' );
		$this->assertContains( $term['term_id'], $term_ids );

		wp_delete_term( $term['term_id'], 'product_brand' );
	}

	/**
	 * Test brand response structure.
	 *
	 * Verifies that each brand in the response has the expected fields.
	 *
	 * @return void
	 */
	public function test_get_brand_response_structure() {
		$term = wp_insert_term( 'Structure Brand', 'product_brand' );
		$this->assertNotWPError( $term );

		$response = $this->get_product_brands();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertNotEmpty( $data );

		$brand = reset( $data );
		$this->assertArrayHasKey( 'id', $brand );
		$this->assertArrayHasKey( 'name', $brand );
		$this->assertArrayHasKey( 'slug', $brand );
		$this->assertArrayHasKey( 'description', $brand );
		$this->assertArrayHasKey( 'count', $brand );

		wp_delete_term( $term['term_id'], 'product_brand' );
	}
}
