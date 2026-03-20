<?php
/**
 * Test CoCart Attribute Terms Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Attribute_Terms_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Attribute ID used across tests.
	 *
	 * @var int
	 */
	protected $attribute_id;

	/**
	 * Set up test environment.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		$this->attribute_id = wc_create_attribute( array(
			'name'         => 'Test Size',
			'slug'         => 'test_size_' . time(),
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
	}

	/**
	 * Test getting attribute terms list.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms() {
		$attribute = wc_get_attribute( $this->attribute_id );
		wp_insert_term( 'Small', $attribute->slug );
		wp_insert_term( 'Large', $attribute->slug );

		$response = $this->get_attribute_terms( $this->attribute_id );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThanOrEqual( 2, count( $data ) );
	}

	/**
	 * Test getting a single attribute term.
	 *
	 * @return void
	 */
	public function test_get_single_attribute_term() {
		$attribute = wc_get_attribute( $this->attribute_id );
		$term      = wp_insert_term( 'Medium', $attribute->slug );

		$response = $this->get_attribute_term( $this->attribute_id, $term['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $term['term_id'], $data['id'] );
		$this->assertEquals( 'Medium', $data['name'] );
	}

	/**
	 * Test getting non-existent attribute term returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_attribute_term() {
		$response = $this->get_attribute_term( $this->attribute_id, 99999 );

		$this->assert_rest_response_status( 404, $response );
	}
}
