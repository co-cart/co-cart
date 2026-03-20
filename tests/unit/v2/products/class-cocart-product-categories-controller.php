<?php
/**
 * Test CoCart Product Categories Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Product_Categories_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product categories list.
	 *
	 * @return void
	 */
	public function test_get_product_categories() {
		wp_insert_term( 'Category One', 'product_cat' );
		wp_insert_term( 'Category Two', 'product_cat' );

		$response = $this->get_product_categories();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThanOrEqual( 2, count( $data ) );
	}

	/**
	 * Test getting a single product category.
	 *
	 * @return void
	 */
	public function test_get_single_product_category() {
		$category = wp_insert_term( 'Test Category', 'product_cat' );

		$response = $this->get_product_category( $category['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $category['term_id'], $data['id'] );
		$this->assertEquals( 'Test Category', $data['name'] );
	}

	/**
	 * Test getting non-existent product category returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_category() {
		$response = $this->get_product_category( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test product category response structure.
	 *
	 * @return void
	 */
	public function test_product_category_response_structure() {
		$category = wp_insert_term( 'Structured Category', 'product_cat' );

		$response = $this->get_product_category( $category['term_id'] );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
	}

	/**
	 * Test product category hierarchy via parent_id.
	 *
	 * @return void
	 */
	public function test_product_category_hierarchy() {
		$parent = wp_insert_term( 'Parent Cat', 'product_cat' );
		$child  = wp_insert_term( 'Child Cat', 'product_cat', array( 'parent' => $parent['term_id'] ) );

		$response = $this->get_product_category( $child['term_id'] );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'parent_id', $data );
		$this->assertEquals( $parent['term_id'], $data['parent_id'] );
	}
}
