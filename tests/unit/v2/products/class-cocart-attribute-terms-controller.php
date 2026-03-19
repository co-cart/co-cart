<?php
/**
 * Test CoCart Attribute Terms Controller
 *
 * Tests for CoCart attribute terms API endpoints including terms listing,
 * individual term retrieval, and term hierarchy.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Attribute Terms Controller Class
 *
 * Tests the attribute terms API endpoints which handle term operations
 * like listing terms, retrieving individual terms, and accessing
 * term hierarchy and metadata.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Attribute_Terms_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting attribute terms list.
	 *
	 * Verifies that the attribute terms endpoint returns a list of all
	 * available terms for a specific attribute with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test terms.
		$term1 = wp_insert_term( 'Red', 'pa_color' );
		$term2 = wp_insert_term( 'Blue', 'pa_color' );

		$response = $this->get_attribute_terms( $attribute );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['terms'] ) );
	}

	/**
	 * Test getting a single attribute term.
	 *
	 * Verifies that individual attribute terms can be retrieved by ID
	 * and that the response contains the correct term data.
	 *
	 * @return void
	 */
	public function test_get_single_attribute_term() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test term.
		$term = wp_insert_term( 'Test Term', 'pa_color' );

		$response = $this->get_attribute_term( $attribute, $term['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $term['term_id'], $data['id'] );
		$this->assertEquals( 'Test Term', $data['name'] );
		$this->assertEquals( 'test-term', $data['slug'] );
	}

	/**
	 * Test getting non-existent attribute term.
	 *
	 * Verifies that requesting a non-existent attribute term returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_attribute_term() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_attribute_term( $attribute, 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting attribute terms with pagination.
	 *
	 * Verifies that the attribute terms endpoint supports pagination
	 * parameters and returns the correct number of terms per page.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_pagination() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create multiple terms.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Term {$i}", 'pa_color' );
		}

		$response = $this->get_attribute_terms( $attribute, array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 2, $data['terms'] );
	}

	/**
	 * Test getting attribute terms with search.
	 *
	 * Verifies that the attribute terms endpoint supports search functionality
	 * and returns only terms matching the search term.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_search() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test terms.
		$term1 = wp_insert_term( 'Red Color', 'pa_color' );
		$term2 = wp_insert_term( 'Blue Color', 'pa_color' );

		$response = $this->get_attribute_terms( $attribute, array(
			'search' => 'Red',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 1, $data['terms'] );
		$this->assertEquals( 'Red Color', $data['terms'][0]['name'] );
	}

	/**
	 * Test getting attribute terms with ordering.
	 *
	 * Verifies that the attribute terms endpoint supports different ordering
	 * options and returns terms in the correct order.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_ordering() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test terms.
		$term1 = wp_insert_term( 'A Term', 'pa_color' );
		$term2 = wp_insert_term( 'Z Term', 'pa_color' );

		// Test ordering by name ascending.
		$response = $this->get_attribute_terms( $attribute, array(
			'orderby' => 'name',
			'order'   => 'asc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		
		// Check that terms are ordered by name ascending.
		$term_names = wp_list_pluck( $data['terms'], 'name' );
		$sorted_names = $term_names;
		sort( $sorted_names );
		$this->assertEquals( $sorted_names, $term_names );
	}

	/**
	 * Test getting attribute terms with include filter.
	 *
	 * Verifies that the attribute terms endpoint supports include filtering
	 * and returns only the specified terms.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_include_filter() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test terms.
		$term1 = wp_insert_term( 'Term 1', 'pa_color' );
		$term2 = wp_insert_term( 'Term 2', 'pa_color' );
		$term3 = wp_insert_term( 'Term 3', 'pa_color' );

		$response = $this->get_attribute_terms( $attribute, array(
			'include' => array( $term1['term_id'], $term2['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 2, $data['terms'] );
		
		$returned_ids = wp_list_pluck( $data['terms'], 'id' );
		$this->assertContains( $term1['term_id'], $returned_ids );
		$this->assertContains( $term2['term_id'], $returned_ids );
		$this->assertNotContains( $term3['term_id'], $returned_ids );
	}

	/**
	 * Test getting attribute terms with exclude filter.
	 *
	 * Verifies that the attribute terms endpoint supports exclude filtering
	 * and returns all terms except the specified ones.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_exclude_filter() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test terms.
		$term1 = wp_insert_term( 'Term 1', 'pa_color' );
		$term2 = wp_insert_term( 'Term 2', 'pa_color' );
		$term3 = wp_insert_term( 'Term 3', 'pa_color' );

		$response = $this->get_attribute_terms( $attribute, array(
			'exclude' => array( $term1['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		
		$returned_ids = wp_list_pluck( $data['terms'], 'id' );
		$this->assertNotContains( $term1['term_id'], $returned_ids );
		$this->assertContains( $term2['term_id'], $returned_ids );
		$this->assertContains( $term3['term_id'], $returned_ids );
	}

	/**
	 * Test getting attribute terms with number filter.
	 *
	 * Verifies that the attribute terms endpoint supports number filtering
	 * and returns the specified number of terms.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_number_filter() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create multiple terms.
		for ( $i = 1; $i <= 10; $i++ ) {
			wp_insert_term( "Term {$i}", 'pa_color' );
		}

		$response = $this->get_attribute_terms( $attribute, array(
			'number' => 5,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 5, $data['terms'] );
	}

	/**
	 * Test getting attribute terms with offset filter.
	 *
	 * Verifies that the attribute terms endpoint supports offset filtering
	 * and returns terms starting from the specified offset.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_offset_filter() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create multiple terms.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Term {$i}", 'pa_color' );
		}

		$response = $this->get_attribute_terms( $attribute, array(
			'offset' => 2,
			'number' => 3,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 3, $data['terms'] );
	}

	/**
	 * Test getting attribute terms with context parameter.
	 *
	 * Verifies that the attribute terms endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_context() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test term.
		$term = wp_insert_term( 'Test Term', 'pa_color' );

		// Test with view context (default).
		$response = $this->get_attribute_terms( $attribute, array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_attribute_terms( $attribute, array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_attribute_terms( $attribute, array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting attribute terms with invalid parameters.
	 *
	 * Verifies that the attribute terms endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_attribute_terms_with_invalid_parameters() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Test with invalid per_page value.
		$response = $this->get_attribute_terms( $attribute, array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_attribute_terms( $attribute, array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid order value.
		$response = $this->get_attribute_terms( $attribute, array( 'order' => 'invalid' ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test attribute term response structure.
	 *
	 * Verifies that the attribute term response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_attribute_term_response_structure() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test term.
		$term = wp_insert_term( 'Test Term', 'pa_color' );

		$response = $this->get_attribute_term( $attribute, $term['term_id'] );
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'link', $data );
		$this->assertArrayHasKey( 'parent', $data );

		// Check data types.
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
		$this->assertIsString( $data['slug'] );
		$this->assertIsString( $data['description'] );
		$this->assertIsInt( $data['count'] );
		$this->assertIsString( $data['link'] );
		$this->assertIsInt( $data['parent'] );
	}

	/**
	 * Test attribute term hierarchy.
	 *
	 * Verifies that attribute terms properly handle parent-child
	 * relationships and hierarchy structure.
	 *
	 * @return void
	 */
	public function test_attribute_term_hierarchy() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create parent term.
		$parent_term = wp_insert_term( 'Parent Term', 'pa_color' );

		// Create child term.
		$child_term = wp_insert_term( 'Child Term', 'pa_color', array(
			'parent' => $parent_term['term_id'],
		) );

		// Get child term.
		$response = $this->get_attribute_term( $attribute, $child_term['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( $parent_term['term_id'], $data['parent'] );

		// Get parent term.
		$response = $this->get_attribute_term( $attribute, $parent_term['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( 0, $data['parent'] );
	}

	/**
	 * Test attribute term with products.
	 *
	 * Verifies that attribute terms properly show product counts
	 * when products are assigned to them.
	 *
	 * @return void
	 */
	public function test_attribute_term_with_products() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Color',
			'slug'         => 'color',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		// Create test term.
		$term = wp_insert_term( 'Test Term', 'pa_color' );

		// Create products and assign to term.
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );
		wp_set_object_terms( $product1->get_id(), $term['term_id'], 'pa_color' );
		wp_set_object_terms( $product2->get_id(), $term['term_id'], 'pa_color' );

		// Get term.
		$response = $this->get_attribute_term( $attribute, $term['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( 2, $data['count'] );
	}

	/**
	 * Test getting terms for non-existent attribute.
	 *
	 * Verifies that requesting terms for a non-existent attribute returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_terms_for_nonexistent_attribute() {
		$response = $this->get_attribute_terms( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting terms for attribute with no terms.
	 *
	 * Verifies that requesting terms for an attribute with no terms returns
	 * an empty array.
	 *
	 * @return void
	 */
	public function test_get_terms_for_attribute_with_no_terms() {
		// Create test attribute.
		$attribute = wc_create_attribute( array(
			'name'         => 'Empty Attribute',
			'slug'         => 'empty-attribute',
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );

		$response = $this->get_attribute_terms( $attribute );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'terms', $data );
		$this->assertCount( 0, $data['terms'] );
	}
} 