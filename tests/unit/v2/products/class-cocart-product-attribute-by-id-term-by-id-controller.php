<?php
/**
 * Test CoCart Product Attribute By ID Term By ID Controller
 *
 * Tests for CoCart single attribute term by attribute ID and term ID endpoint.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attribute By ID Term By ID Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/attributes/{attribute_id}/terms/{id}.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attribute_By_Id_Term_By_Id_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting a term by attribute ID and term ID returns 200.
	 *
	 * @return void
	 */
	public function test_get_term_by_attribute_and_term_id_returns_200() {
		$slug         = 'fabric_' . time();
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Fabric',
			'slug'     => $slug,
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		// Register the taxonomy directly so wp_insert_term() can use it.
		$taxonomy = wc_attribute_taxonomy_name( $slug );
		if ( ! taxonomy_exists( $taxonomy ) ) {
			register_taxonomy( $taxonomy, array( 'product' ), array( 'label' => 'Fabric', 'hierarchical' => false ) );
		}
		$term    = wp_insert_term( 'Cotton', $taxonomy );
		$term_id = $term['term_id'];

		$response = $this->get_attribute_term( $attribute_id, $term_id );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test getting a non-existent term returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_term_returns_404() {
		$attribute_id = wc_create_attribute( array(
			'name'     => 'Style',
			'slug'     => 'style_' . time(),
			'type'     => 'select',
			'order_by' => 'menu_order',
		) );

		$response = $this->get_attribute_term( $attribute_id, 99999 );

		$this->assert_rest_response_status( 404, $response );
	}
}
