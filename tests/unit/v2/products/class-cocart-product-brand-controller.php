<?php
/**
 * Test CoCart Product Brand Controller
 *
 * Tests for CoCart single product brand by ID endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Brand Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/brands/{id}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Brand_Controller extends CoCart_API_V2_Test_Case {

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

		// Ensure product_brand taxonomy is registered — WC_Brands::init_taxonomy() is hooked
		// to woocommerce_register_taxonomy which may not fire during REST test setUp().
		if ( ! taxonomy_exists( 'product_brand' ) ) {
			if ( class_exists( 'WC_Brands' ) ) {
				WC_Brands::init_taxonomy();
			} else {
				$this->markTestSkipped( 'product_brand taxonomy is not registered. Skipping brand tests.' );
			}
		}
	}

	/**
	 * Test getting a single brand by ID returns 200.
	 *
	 * @return void
	 */
	public function test_get_brand_by_id_returns_200() {
		$term = wp_insert_term( 'Solo Brand', 'product_brand' );
		$this->assertNotWPError( $term );

		$response = $this->rest_get( '/cocart/v2/products/brands/' . $term['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		wp_delete_term( $term['term_id'], 'product_brand' );
	}

	/**
	 * Test getting a non-existent brand ID returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_brand_returns_404() {
		$response = $this->rest_get( '/cocart/v2/products/brands/99999' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test single brand response contains expected keys.
	 *
	 * @return void
	 */
	public function test_brand_response_structure() {
		$term = wp_insert_term( 'Structured Brand', 'product_brand' );
		$this->assertNotWPError( $term );

		$response = $this->rest_get( '/cocart/v2/products/brands/' . $term['term_id'] );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertEquals( $term['term_id'], $data['id'] );

		wp_delete_term( $term['term_id'], 'product_brand' );
	}
}
