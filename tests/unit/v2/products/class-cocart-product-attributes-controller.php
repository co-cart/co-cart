<?php
/**
 * Test CoCart Product Attributes Controller
 *
 * Tests for CoCart product attributes API endpoints including attribute
 * listing, individual attribute retrieval, and attribute terms.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Attributes Controller Class
 *
 * Tests the product attributes API endpoints which handle attribute operations
 * like listing attributes, retrieving individual attributes, and accessing
 * attribute terms and metadata.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Attributes_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product attributes list.
	 *
	 * Verifies that the product attributes endpoint returns a list of all
	 * available product attributes with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_product_attributes() {
		// Create test attributes.
		$attribute1 = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute2 = wc_create_attribute( array(
			'name'         => 'Size',
			'slug'         => 'size',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attributes();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['attributes'] ) );
	}

	/**
	 * Test getting a single product attribute.
	 *
	 * Verifies that individual product attributes can be retrieved by ID
	 * and that the response contains the correct attribute data.
	 *
	 * @return void
	 */
	public function test_get_single_product_attribute() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Test Attribute',
			'slug'         => 'test-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attribute( $attribute );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $attribute, $data['id'] );
		$this->assertEquals( 'Test Attribute', $data['name'] );
		$this->assertEquals( 'test-attribute', $data['slug'] );
	}

	/**
	 * Test getting non-existent product attribute.
	 *
	 * Verifies that requesting a non-existent product attribute returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_attribute() {
		$response = $this->get_product_attribute( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting product attributes with pagination.
	 *
	 * Verifies that the product attributes endpoint supports pagination
	 * parameters and returns the correct number of attributes per page.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_pagination() {
		// Create multiple attributes.
		for ( $i = 1; $i <= 5; $i++ ) {
			wc_create_attribute( array(
				'name'         => "Attribute {$i}",
				'slug'         => "attribute-{$i}",
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			) );
		}

		$response = $this->get_product_attributes( array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertCount( 2, $data['attributes'] );
	}

	/**
	 * Test getting product attributes with search.
	 *
	 * Verifies that the product attributes endpoint supports search functionality
	 * and returns only attributes matching the search term.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_search() {
		// Create test attributes.
		$attribute1 = wc_create_attribute( array(
			'name'         => 'Red Color',
			'slug'         => 'red-color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute2 = wc_create_attribute( array(
			'name'         => 'Blue Color',
			'slug'         => 'blue-color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attributes( array(
			'search' => 'Red',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertCount( 1, $data['attributes'] );
		$this->assertEquals( 'Red Color', $data['attributes'][0]['name'] );
	}

	/**
	 * Test getting product attributes with ordering.
	 *
	 * Verifies that the product attributes endpoint supports different ordering
	 * options and returns attributes in the correct order.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_ordering() {
		// Create test attributes.
		$attribute1 = wc_create_attribute( array(
			'name'         => 'A Attribute',
			'slug'         => 'a-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute2 = wc_create_attribute( array(
			'name'         => 'Z Attribute',
			'slug'         => 'z-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Test ordering by name ascending.
		$response = $this->get_product_attributes( array(
			'orderby' => 'name',
			'order'   => 'asc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		
		// Check that attributes are ordered by name ascending.
		$attribute_names = wp_list_pluck( $data['attributes'], 'name' );
		$sorted_names = $attribute_names;
		sort( $sorted_names );
		$this->assertEquals( $sorted_names, $attribute_names );
	}

	/**
	 * Test getting product attributes with include filter.
	 *
	 * Verifies that the product attributes endpoint supports include filtering
	 * and returns only the specified attributes.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_include_filter() {
		// Create test attributes.
		$attribute1 = wc_create_attribute( array(
			'name'         => 'Attribute 1',
			'slug'         => 'attribute-1',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute2 = wc_create_attribute( array(
			'name'         => 'Attribute 2',
			'slug'         => 'attribute-2',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute3 = wc_create_attribute( array(
			'name'         => 'Attribute 3',
			'slug'         => 'attribute-3',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attributes( array(
			'include' => array( $attribute1, $attribute2 ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertCount( 2, $data['attributes'] );
		
		$returned_ids = wp_list_pluck( $data['attributes'], 'id' );
		$this->assertContains( $attribute1, $returned_ids );
		$this->assertContains( $attribute2, $returned_ids );
		$this->assertNotContains( $attribute3, $returned_ids );
	}

	/**
	 * Test getting product attributes with exclude filter.
	 *
	 * Verifies that the product attributes endpoint supports exclude filtering
	 * and returns all attributes except the specified ones.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_exclude_filter() {
		// Create test attributes.
		$attribute1 = wc_create_attribute( array(
			'name'         => 'Attribute 1',
			'slug'         => 'attribute-1',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute2 = wc_create_attribute( array(
			'name'         => 'Attribute 2',
			'slug'         => 'attribute-2',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		$attribute3 = wc_create_attribute( array(
			'name'         => 'Attribute 3',
			'slug'         => 'attribute-3',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attributes( array(
			'exclude' => array( $attribute1 ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		
		$returned_ids = wp_list_pluck( $data['attributes'], 'id' );
		$this->assertNotContains( $attribute1, $returned_ids );
		$this->assertContains( $attribute2, $returned_ids );
		$this->assertContains( $attribute3, $returned_ids );
	}

	/**
	 * Test getting product attributes with number filter.
	 *
	 * Verifies that the product attributes endpoint supports number filtering
	 * and returns the specified number of attributes.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_number_filter() {
		// Create multiple attributes.
		for ( $i = 1; $i <= 10; $i++ ) {
			wc_create_attribute( array(
				'name'         => "Attribute {$i}",
				'slug'         => "attribute-{$i}",
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			) );
		}

		$response = $this->get_product_attributes( array(
			'number' => 5,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertCount( 5, $data['attributes'] );
	}

	/**
	 * Test getting product attributes with offset filter.
	 *
	 * Verifies that the product attributes endpoint supports offset filtering
	 * and returns attributes starting from the specified offset.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_offset_filter() {
		// Create multiple attributes.
		for ( $i = 1; $i <= 5; $i++ ) {
			wc_create_attribute( array(
				'name'         => "Attribute {$i}",
				'slug'         => "attribute-{$i}",
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			) );
		}

		$response = $this->get_product_attributes( array(
			'offset' => 2,
			'number' => 3,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertCount( 3, $data['attributes'] );
	}

	/**
	 * Test getting product attributes with context parameter.
	 *
	 * Verifies that the product attributes endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_context() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Test Attribute',
			'slug'         => 'test-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Test with view context (default).
		$response = $this->get_product_attributes( array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_product_attributes( array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_product_attributes( array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting product attributes with invalid parameters.
	 *
	 * Verifies that the product attributes endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_product_attributes_with_invalid_parameters() {
		// Test with invalid per_page value.
		$response = $this->get_product_attributes( array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_product_attributes( array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid order value.
		$response = $this->get_product_attributes( array( 'order' => 'invalid' ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test product attribute response structure.
	 *
	 * Verifies that the product attribute response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_product_attribute_response_structure() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Test Attribute',
			'slug'         => 'test-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_product_attribute( $attribute );
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'type', $data );
		$this->assertArrayHasKey( 'order_by', $data );
		$this->assertArrayHasKey( 'has_archives', $data );

		// Check data types.
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
		$this->assertIsString( $data['slug'] );
		$this->assertIsString( $data['type'] );
		$this->assertIsString( $data['order_by'] );
		$this->assertIsBool( $data['has_archives'] );
	}

	/**
	 * Test product attribute with terms.
	 *
	 * Verifies that product attributes properly show terms when
	 * terms are created for the attribute.
	 *
	 * @return void
	 */
	public function test_product_attribute_with_terms() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Test Attribute',
			'slug'         => 'test-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create terms for the attribute.
		$term1 = wp_insert_term( 'Term 1', 'pa_test-attribute' );
		$term2 = wp_insert_term( 'Term 2', 'pa_test-attribute' );

		// Get attribute.
		$response = $this->get_product_attribute( $attribute );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 2, $data['terms'] );
	}

	/**
	 * Test product attribute types.
	 *
	 * Verifies that different attribute types are properly handled
	 * and returned correctly.
	 *
	 * @return void
	 */
	public function test_product_attribute_types() {
		// Create attributes with different types.
		$select_attribute = wc_create_attribute( array(
			'name'         => 'Select Attribute',
			'slug'         => 'select-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$text_attribute = wc_create_attribute( array(
			'name'         => 'Text Attribute',
			'slug'         => 'text-attribute',
			'type'         => 'text',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Get select attribute.
		$response = $this->get_product_attribute( $select_attribute );
		$data = $response->get_data();
		$this->assertEquals( 'select', $data['type'] );

		// Get text attribute.
		$response = $this->get_product_attribute( $text_attribute );
		$data = $response->get_data();
		$this->assertEquals( 'text', $data['type'] );
	}

	/**
	 * Test product attribute order by options.
	 *
	 * Verifies that different order_by options are properly handled
	 * and returned correctly.
	 *
	 * @return void
	 */
	public function test_product_attribute_order_by_options() {
		// Create attributes with different order_by options.
		$menu_order_attribute = wc_create_attribute( array(
			'name'         => 'Menu Order Attribute',
			'slug'         => 'menu-order-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$name_attribute = wc_create_attribute( array(
			'name'         => 'Name Attribute',
			'slug'         => 'name-attribute',
			'type'         => 'select',
			'order_by'     => 'name',
			'has_archives' => false,
		) );

		// Get menu order attribute.
		$response = $this->get_product_attribute( $menu_order_attribute );
		$data = $response->get_data();
		$this->assertEquals( 'menu_order', $data['order_by'] );

		// Get name attribute.
		$response = $this->get_product_attribute( $name_attribute );
		$data = $response->get_data();
		$this->assertEquals( 'name', $data['order_by'] );
	}
} 