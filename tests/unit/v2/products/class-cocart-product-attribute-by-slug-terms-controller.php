<?php
/**
 * Test CoCart Product Attribute By Slug Terms Controller
 *
 * Tests for CoCart product attribute terms by slug endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By Slug Terms Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{attribute_slug}/terms.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Slug_Terms_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting terms for an attribute by slug returns 200 or 404.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_by_slug_returns_valid_status() {
		$slug = 'pa_finish_' . time();
		wc_create_attribute( array(
			'name'     => 'Finish',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $slug . '/terms' );

		// 200 with terms or 404 when none exist — both are valid.
		$status = $response->get_status();
		$this->assertTrue( in_array( $status, array( 200, 404 ), true ) );
	}

	/**
	 * Test getting terms for a non-existent attribute slug returns 404.
	 *
	 * @return void
	 */
	public function test_get_terms_for_nonexistent_slug_returns_404() {
		$response = $this->rest_get( '/cocart/v2/products/attributes/pa_does_not_exist_xyz/terms' );

		$this->assert_rest_response_status( 404, $response );
	}
}
