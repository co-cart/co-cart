<?php
/**
 * Test CoCart Product Attribute By Slug Term By Slug Controller
 *
 * Tests for CoCart single attribute term by attribute slug and term slug endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By Slug Term By Slug Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{attribute_slug}/terms/{term_slug}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Slug_Term_By_Slug_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a term by attribute slug and term slug returns 200.
	 *
	 * @return void
	 */
	public function test_get_term_by_attribute_slug_and_term_slug_returns_200() {
		$attr_slug    = 'pa_pattern_' . time();
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Pattern',
			'slug'     => $attr_slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		// Register the taxonomy so wp_insert_term() can use it.
		WC_Post_Types::register_taxonomies();
		$taxonomy  = wc_attribute_taxonomy_name_by_id( $attribute_id );
		$term      = wp_insert_term( 'Striped', $taxonomy );
		$term_slug = get_term( $term['term_id'], $taxonomy )->slug;

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $attr_slug . '/terms/' . $term_slug );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent term slug returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_term_slug_returns_404() {
		$attr_slug = 'pa_print_' . time();
		wc_create_attribute( array(
			'name'     => 'Print',
			'slug'     => $attr_slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $attr_slug . '/terms/does-not-exist-xyz' );

		$this->assert_rest_response_status( 404, $response );
	}
}
