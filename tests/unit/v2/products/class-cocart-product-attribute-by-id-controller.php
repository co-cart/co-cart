<?php
/**
 * Test CoCart Product Attribute By ID Controller
 *
 * Tests for CoCart product attribute by ID endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By ID Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{id}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Id_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a product attribute by ID returns 200.
	 *
	 * @return void
	 */
	public function test_get_attribute_by_id_returns_200() {
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Size',
			'slug'     => 'pa_size_' . time(),
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->get_product_attribute( $attribute_id );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent attribute by ID returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_attribute_by_id_returns_404() {
		$response = $this->get_product_attribute( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test attribute by ID response structure.
	 *
	 * @return void
	 */
	public function test_attribute_by_id_response_structure() {
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Color',
			'slug'     => 'pa_color_' . time(),
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->get_product_attribute( $attribute_id );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertEquals( $attribute_id, $data['id'] );
	}
}
