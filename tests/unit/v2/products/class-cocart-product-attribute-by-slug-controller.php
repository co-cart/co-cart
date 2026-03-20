<?php
/**
 * Test CoCart Product Attribute By Slug Controller
 *
 * Tests for CoCart product attribute by slug endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By Slug Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{slug}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Slug_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a product attribute by slug returns 200.
	 *
	 * @return void
	 */
	public function test_get_attribute_by_slug_returns_200() {
		$slug = 'pa_weight_' . time();
		wc_create_attribute( array(
			'name'     => 'Weight',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $slug );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent attribute slug returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_attribute_slug_returns_404() {
		$response = $this->rest_get( '/cocart/v2/products/attributes/pa_does_not_exist_xyz' );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test attribute by slug response contains expected keys.
	 *
	 * @return void
	 */
	public function test_attribute_by_slug_response_structure() {
		$slug = 'pa_length_' . time();
		wc_create_attribute( array(
			'name'     => 'Length',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $slug );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
	}
}
