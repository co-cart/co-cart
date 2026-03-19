<?php
/**
 * Test CoCart Product Categories Controller
 *
 * Tests for CoCart product categories API endpoints including category
 * listing, individual category retrieval, and category hierarchy.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Categories Controller Class
 *
 * Tests the product categories API endpoints which handle category operations
 * like listing categories, retrieving individual categories, and accessing
 * category hierarchy and metadata.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Categories_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product categories list.
	 *
	 * Verifies that the product categories endpoint returns a list of all
	 * available product categories with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_product_categories() {
		// Create test categories.
		$category1 = wp_insert_term( 'Category 1', 'product_cat' );
		$category2 = wp_insert_term( 'Category 2', 'product_cat' );

		$response = $this->get_product_categories();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['categories'] ) );
	}

	/**
	 * Test getting a single product category.
	 *
	 * Verifies that individual product categories can be retrieved by ID
	 * and that the response contains the correct category data.
	 *
	 * @return void
	 */
	public function test_get_single_product_category() {
		// Create test category.
		$category = wp_insert_term( 'Test Category', 'product_cat' );

		$response = $this->get_product_category( $category['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $category['term_id'], $data['id'] );
		$this->assertEquals( 'Test Category', $data['name'] );
		$this->assertEquals( 'test-category', $data['slug'] );
	}

	/**
	 * Test getting non-existent product category.
	 *
	 * Verifies that requesting a non-existent product category returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_category() {
		$response = $this->get_product_category( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting product categories with pagination.
	 *
	 * Verifies that the product categories endpoint supports pagination
	 * parameters and returns the correct number of categories per page.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_pagination() {
		// Create multiple categories.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Category {$i}", 'product_cat' );
		}

		$response = $this->get_product_categories( array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 2, $data['categories'] );
	}

	/**
	 * Test getting product categories with search.
	 *
	 * Verifies that the product categories endpoint supports search functionality
	 * and returns only categories matching the search term.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_search() {
		// Create test categories.
		$category1 = wp_insert_term( 'Red Category', 'product_cat' );
		$category2 = wp_insert_term( 'Blue Category', 'product_cat' );

		$response = $this->get_product_categories( array(
			'search' => 'Red',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 1, $data['categories'] );
		$this->assertEquals( 'Red Category', $data['categories'][0]['name'] );
	}

	/**
	 * Test getting product categories with ordering.
	 *
	 * Verifies that the product categories endpoint supports different ordering
	 * options and returns categories in the correct order.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_ordering() {
		// Create test categories.
		$category1 = wp_insert_term( 'A Category', 'product_cat' );
		$category2 = wp_insert_term( 'Z Category', 'product_cat' );

		// Test ordering by name ascending.
		$response = $this->get_product_categories( array(
			'orderby' => 'name',
			'order'   => 'asc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		
		// Check that categories are ordered by name ascending.
		$category_names = wp_list_pluck( $data['categories'], 'name' );
		$sorted_names = $category_names;
		sort( $sorted_names );
		$this->assertEquals( $sorted_names, $category_names );
	}

	/**
	 * Test getting product categories with hide_empty filter.
	 *
	 * Verifies that the product categories endpoint supports hide_empty filtering
	 * and returns only categories that have associated products.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_hide_empty_filter() {
		// Create test categories.
		$category1 = wp_insert_term( 'Empty Category', 'product_cat' );
		$category2 = wp_insert_term( 'Used Category', 'product_cat' );

		// Create product and assign it to the second category.
		$product = $this->create_product( array( 'name' => 'Categorized Product' ) );
		wp_set_object_terms( $product->get_id(), $category2['term_id'], 'product_cat' );

		$response = $this->get_product_categories( array(
			'hide_empty' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		
		// Check that only categories with products are returned.
		foreach ( $data['categories'] as $category ) {
			$this->assertGreaterThan( 0, $category['count'] );
		}
	}

	/**
	 * Test getting product categories with include filter.
	 *
	 * Verifies that the product categories endpoint supports include filtering
	 * and returns only the specified categories.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_include_filter() {
		// Create test categories.
		$category1 = wp_insert_term( 'Category 1', 'product_cat' );
		$category2 = wp_insert_term( 'Category 2', 'product_cat' );
		$category3 = wp_insert_term( 'Category 3', 'product_cat' );

		$response = $this->get_product_categories( array(
			'include' => array( $category1['term_id'], $category2['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 2, $data['categories'] );
		
		$returned_ids = wp_list_pluck( $data['categories'], 'id' );
		$this->assertContains( $category1['term_id'], $returned_ids );
		$this->assertContains( $category2['term_id'], $returned_ids );
		$this->assertNotContains( $category3['term_id'], $returned_ids );
	}

	/**
	 * Test getting product categories with exclude filter.
	 *
	 * Verifies that the product categories endpoint supports exclude filtering
	 * and returns all categories except the specified ones.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_exclude_filter() {
		// Create test categories.
		$category1 = wp_insert_term( 'Category 1', 'product_cat' );
		$category2 = wp_insert_term( 'Category 2', 'product_cat' );
		$category3 = wp_insert_term( 'Category 3', 'product_cat' );

		$response = $this->get_product_categories( array(
			'exclude' => array( $category1['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		
		$returned_ids = wp_list_pluck( $data['categories'], 'id' );
		$this->assertNotContains( $category1['term_id'], $returned_ids );
		$this->assertContains( $category2['term_id'], $returned_ids );
		$this->assertContains( $category3['term_id'], $returned_ids );
	}

	/**
	 * Test getting product categories with parent filter.
	 *
	 * Verifies that the product categories endpoint supports parent filtering
	 * and returns only child categories of the specified parent.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_parent_filter() {
		// Create parent category.
		$parent_category = wp_insert_term( 'Parent Category', 'product_cat' );

		// Create child categories.
		$child_category1 = wp_insert_term( 'Child Category 1', 'product_cat', array(
			'parent' => $parent_category['term_id'],
		) );
		$child_category2 = wp_insert_term( 'Child Category 2', 'product_cat', array(
			'parent' => $parent_category['term_id'],
		) );

		$response = $this->get_product_categories( array(
			'parent' => $parent_category['term_id'],
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 2, $data['categories'] );
		
		// Check that all returned categories have the correct parent.
		foreach ( $data['categories'] as $category ) {
			$this->assertEquals( $parent_category['term_id'], $category['parent'] );
		}
	}

	/**
	 * Test getting product categories with number filter.
	 *
	 * Verifies that the product categories endpoint supports number filtering
	 * and returns the specified number of categories.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_number_filter() {
		// Create multiple categories.
		for ( $i = 1; $i <= 10; $i++ ) {
			wp_insert_term( "Category {$i}", 'product_cat' );
		}

		$response = $this->get_product_categories( array(
			'number' => 5,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 5, $data['categories'] );
	}

	/**
	 * Test getting product categories with offset filter.
	 *
	 * Verifies that the product categories endpoint supports offset filtering
	 * and returns categories starting from the specified offset.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_offset_filter() {
		// Create multiple categories.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Category {$i}", 'product_cat' );
		}

		$response = $this->get_product_categories( array(
			'offset' => 2,
			'number' => 3,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'categories', $data );
		$this->assertCount( 3, $data['categories'] );
	}

	/**
	 * Test getting product categories with context parameter.
	 *
	 * Verifies that the product categories endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_context() {
		// Create test category.
		$category = wp_insert_term( 'Test Category', 'product_cat' );

		// Test with view context (default).
		$response = $this->get_product_categories( array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_product_categories( array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_product_categories( array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting product categories with invalid parameters.
	 *
	 * Verifies that the product categories endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_product_categories_with_invalid_parameters() {
		// Test with invalid per_page value.
		$response = $this->get_product_categories( array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_product_categories( array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid order value.
		$response = $this->get_product_categories( array( 'order' => 'invalid' ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test product category response structure.
	 *
	 * Verifies that the product category response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_product_category_response_structure() {
		// Create test category.
		$category = wp_insert_term( 'Test Category', 'product_cat' );

		$response = $this->get_product_category( $category['term_id'] );
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
	 * Test product category hierarchy.
	 *
	 * Verifies that product categories properly handle parent-child
	 * relationships and hierarchy structure.
	 *
	 * @return void
	 */
	public function test_product_category_hierarchy() {
		// Create parent category.
		$parent_category = wp_insert_term( 'Parent Category', 'product_cat' );

		// Create child category.
		$child_category = wp_insert_term( 'Child Category', 'product_cat', array(
			'parent' => $parent_category['term_id'],
		) );

		// Get child category.
		$response = $this->get_product_category( $child_category['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( $parent_category['term_id'], $data['parent'] );

		// Get parent category.
		$response = $this->get_product_category( $parent_category['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( 0, $data['parent'] );
	}

	/**
	 * Test product category with products.
	 *
	 * Verifies that product categories properly show product counts
	 * when products are assigned to them.
	 *
	 * @return void
	 */
	public function test_product_category_with_products() {
		// Create test category.
		$category = wp_insert_term( 'Test Category', 'product_cat' );

		// Create products and assign to category.
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );
		wp_set_object_terms( $product1->get_id(), $category['term_id'], 'product_cat' );
		wp_set_object_terms( $product2->get_id(), $category['term_id'], 'product_cat' );

		// Get category.
		$response = $this->get_product_category( $category['term_id'] );
		$data = $response->get_data();

		$this->assertEquals( 2, $data['count'] );
	}
} 