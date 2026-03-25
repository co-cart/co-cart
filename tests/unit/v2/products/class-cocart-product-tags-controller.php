<?php
/**
 * Test CoCart Product Tags Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Product_Tags_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product tags list.
	 *
	 * @return void
	 */
	public function test_get_product_tags() {
		wp_insert_term( 'Tag One', 'product_tag' );
		wp_insert_term( 'Tag Two', 'product_tag' );

		$response = $this->get_product_tags();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThanOrEqual( 2, count( $data ) );
	}

	/**
	 * Test getting a single product tag.
	 *
	 * @return void
	 */
	public function test_get_single_product_tag() {
		$tag = wp_insert_term( 'Test Tag', 'product_tag' );

		$response = $this->get_product_tag( $tag['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $tag['term_id'], $data['id'] );
		$this->assertEquals( 'Test Tag', $data['name'] );
	}

	/**
	 * Test getting non-existent product tag returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_tag() {
		$response = $this->get_product_tag( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test product tag response structure.
	 *
	 * @return void
	 */
	public function test_product_tag_response_structure() {
		$tag = wp_insert_term( 'Structured Tag', 'product_tag' );

		$response = $this->get_product_tag( $tag['term_id'] );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'count', $data );
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
		$this->assertIsInt( $data['count'] );
	}
}
