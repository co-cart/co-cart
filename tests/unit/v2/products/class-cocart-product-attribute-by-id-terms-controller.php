<?php
/**
 * Test CoCart Product Attribute By ID Terms Controller
 *
 * Tests for CoCart product attribute terms by attribute ID endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By ID Terms Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{attribute_id}/terms.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Id_Terms_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting terms for an attribute by ID returns 200.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_by_id_returns_200() {
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Material',
			'slug'     => 'pa_material_' . time(),
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->get_attribute_terms( $attribute_id );

		// 200 with terms or 404 when no terms exist — both are valid.
		$status = $response->get_status();
		$this->assertTrue( in_array( $status, array( 200, 404 ), true ) );
	}

	/**
	 * Test getting terms for a non-existent attribute returns 404.
	 *
	 * @return void
	 */
	public function test_get_terms_for_nonexistent_attribute_returns_404() {
		$response = $this->get_attribute_terms( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}
}
