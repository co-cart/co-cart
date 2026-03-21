<?php
/**
 * Test CoCart Product Attribute By Slug Term By ID Controller
 *
 * Tests for CoCart single attribute term by attribute slug and term ID endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By Slug Term By ID Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{attribute_slug}/terms/{id}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Slug_Term_By_Id_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a term by attribute slug and term ID returns 200.
	 *
	 * @return void
	 */
	public function test_get_term_by_slug_and_term_id_returns_200() {
		$slug         = 'texture_' . time();
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Texture',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		// Register the taxonomy directly so wp_insert_term() can use it.
		$taxonomy = wc_attribute_taxonomy_name( $slug );
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array( 'label' => 'Texture', 'hierarchical' => false ) );
		}
		$term    = wp_insert_term( 'Smooth', $taxonomy );
		$term_id = $term['term_id'];

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $slug . '/terms/' . $term_id );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent term ID returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_term_id_returns_404() {
		$slug = 'grain_' . time();
		wc_create_attribute( array(
			'name'     => 'Grain',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->rest_get( '/cocart/v2/products/attributes/' . $slug . '/terms/99999' );

		$this->assert_rest_response_status( 404, $response );
	}
}
