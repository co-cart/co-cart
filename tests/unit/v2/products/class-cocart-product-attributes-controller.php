<?php
/**
 * Test CoCart Product Attributes Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Product_Attributes_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product attributes list.
	 *
	 * @return void
	 */
	public function test_get_product_attributes() {
		$attribute_id = wc_create_attribute( array(
			'name'         => 'Test Color',
			'slug'         => 'test_color_' . time(),
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$this->assertGreaterThan( 0, $attribute_id );

		$response = $this->get_product_attributes();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
	}

	/**
	 * Test getting a single product attribute.
	 *
	 * @return void
	 */
	public function test_get_single_product_attribute() {
		$attribute_id = wc_create_attribute( array(
			'name'         => 'Single Attr',
			'slug'         => 'single_attr_' . time(),
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attribute( $attribute_id );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $attribute_id, $data['id'] );
	}

	/**
	 * Test getting non-existent product attribute returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_attribute() {
		$response = $this->get_product_attribute( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test product attribute response structure.
	 *
	 * @return void
	 */
	public function test_product_attribute_response_structure() {
		$attribute_id = wc_create_attribute( array(
			'name'         => 'Structure Attr',
			'slug'         => 'structure_attr_' . time(),
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attribute( $attribute_id );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
	}
}
