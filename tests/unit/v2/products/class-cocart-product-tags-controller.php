<?php
/**
 * Test CoCart Product Tags Controller
 *
 * Tests for CoCart product tags API endpoints including tag listing,
 * individual tag retrieval, and tag-related data.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Tags Controller Class
 *
 * Tests the product tags API endpoints which handle tag operations
 * like listing tags, retrieving individual tags, and accessing
 * tag metadata.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Tags_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product tags list.
	 *
	 * Verifies that the product tags endpoint returns a list of all
	 * available product tags with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_product_tags() {
		// Create test tags.
		$tag1 = wp_insert_term( 'Tag 1', 'product_tag' );
		$tag2 = wp_insert_term( 'Tag 2', 'product_tag' );

		$response = $this->get_product_tags();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['tags'] ) );
	}

	/**
	 * Test getting a single product tag.
	 *
	 * Verifies that individual product tags can be retrieved by ID
	 * and that the response contains the correct tag data.
	 *
	 * @return void
	 */
	public function test_get_single_product_tag() {
		// Create test tag.
		$tag = wp_insert_term( 'Test Tag', 'product_tag' );

		$response = $this->get_product_tag( $tag['term_id'] );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $tag['term_id'], $data['id'] );
		$this->assertEquals( 'Test Tag', $data['name'] );
		$this->assertEquals( 'test-tag', $data['slug'] );
	}

	/**
	 * Test getting non-existent product tag.
	 *
	 * Verifies that requesting a non-existent product tag returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_tag() {
		$response = $this->get_product_tag( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting product tags with pagination.
	 *
	 * Verifies that the product tags endpoint supports pagination
	 * parameters and returns the correct number of tags per page.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_pagination() {
		// Create multiple tags.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Tag {$i}", 'product_tag' );
		}

		$response = $this->get_product_tags( array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 2, $data['tags'] );
	}

	/**
	 * Test getting product tags with search.
	 *
	 * Verifies that the product tags endpoint supports search functionality
	 * and returns only tags matching the search term.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_search() {
		// Create test tags.
		$tag1 = wp_insert_term( 'Red Tag', 'product_tag' );
		$tag2 = wp_insert_term( 'Blue Tag', 'product_tag' );

		$response = $this->get_product_tags( array(
			'search' => 'Red',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 1, $data['tags'] );
		$this->assertEquals( 'Red Tag', $data['tags'][0]['name'] );
	}

	/**
	 * Test getting product tags with ordering.
	 *
	 * Verifies that the product tags endpoint supports different ordering
	 * options and returns tags in the correct order.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_ordering() {
		// Create test tags.
		$tag1 = wp_insert_term( 'A Tag', 'product_tag' );
		$tag2 = wp_insert_term( 'Z Tag', 'product_tag' );

		// Test ordering by name ascending.
		$response = $this->get_product_tags( array(
			'orderby' => 'name',
			'order'   => 'asc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		
		// Check that tags are ordered by name ascending.
		$tag_names = wp_list_pluck( $data['tags'], 'name' );
		$sorted_names = $tag_names;
		sort( $sorted_names );
		$this->assertEquals( $sorted_names, $tag_names );
	}

	/**
	 * Test getting product tags with hide_empty filter.
	 *
	 * Verifies that the product tags endpoint supports hide_empty filtering
	 * and returns only tags that have associated products.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_hide_empty_filter() {
		// Create test tags.
		$tag1 = wp_insert_term( 'Empty Tag', 'product_tag' );
		$tag2 = wp_insert_term( 'Used Tag', 'product_tag' );

		// Create product and assign it to the second tag.
		$product = $this->create_product( array( 'name' => 'Tagged Product' ) );
		wp_set_object_terms( $product->get_id(), $tag2['term_id'], 'product_tag' );

		$response = $this->get_product_tags( array(
			'hide_empty' => true,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		
		// Check that only tags with products are returned.
		foreach ( $data['tags'] as $tag ) {
			$this->assertGreaterThan( 0, $tag['count'] );
		}
	}

	/**
	 * Test getting product tags with include filter.
	 *
	 * Verifies that the product tags endpoint supports include filtering
	 * and returns only the specified tags.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_include_filter() {
		// Create test tags.
		$tag1 = wp_insert_term( 'Tag 1', 'product_tag' );
		$tag2 = wp_insert_term( 'Tag 2', 'product_tag' );
		$tag3 = wp_insert_term( 'Tag 3', 'product_tag' );

		$response = $this->get_product_tags( array(
			'include' => array( $tag1['term_id'], $tag2['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 2, $data['tags'] );
		
		$returned_ids = wp_list_pluck( $data['tags'], 'id' );
		$this->assertContains( $tag1['term_id'], $returned_ids );
		$this->assertContains( $tag2['term_id'], $returned_ids );
		$this->assertNotContains( $tag3['term_id'], $returned_ids );
	}

	/**
	 * Test getting product tags with exclude filter.
	 *
	 * Verifies that the product tags endpoint supports exclude filtering
	 * and returns all tags except the specified ones.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_exclude_filter() {
		// Create test tags.
		$tag1 = wp_insert_term( 'Tag 1', 'product_tag' );
		$tag2 = wp_insert_term( 'Tag 2', 'product_tag' );
		$tag3 = wp_insert_term( 'Tag 3', 'product_tag' );

		$response = $this->get_product_tags( array(
			'exclude' => array( $tag1['term_id'] ),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		
		$returned_ids = wp_list_pluck( $data['tags'], 'id' );
		$this->assertNotContains( $tag1['term_id'], $returned_ids );
		$this->assertContains( $tag2['term_id'], $returned_ids );
		$this->assertContains( $tag3['term_id'], $returned_ids );
	}

	/**
	 * Test getting product tags with number filter.
	 *
	 * Verifies that the product tags endpoint supports number filtering
	 * and returns the specified number of tags.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_number_filter() {
		// Create multiple tags.
		for ( $i = 1; $i <= 10; $i++ ) {
			wp_insert_term( "Tag {$i}", 'product_tag' );
		}

		$response = $this->get_product_tags( array(
			'number' => 5,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 5, $data['tags'] );
	}

	/**
	 * Test getting product tags with offset filter.
	 *
	 * Verifies that the product tags endpoint supports offset filtering
	 * and returns tags starting from the specified offset.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_offset_filter() {
		// Create multiple tags.
		for ( $i = 1; $i <= 5; $i++ ) {
			wp_insert_term( "Tag {$i}", 'product_tag' );
		}

		$response = $this->get_product_tags( array(
			'offset' => 2,
			'number' => 3,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'tags', $data );
		$this->assertCount( 3, $data['tags'] );
	}

	/**
	 * Test getting product tags with context parameter.
	 *
	 * Verifies that the product tags endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_context() {
		// Create test tag.
		$tag = wp_insert_term( 'Test Tag', 'product_tag' );

		// Test with view context (default).
		$response = $this->get_product_tags( array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_product_tags( array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_product_tags( array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting product tags with invalid parameters.
	 *
	 * Verifies that the product tags endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_product_tags_with_invalid_parameters() {
		// Test with invalid per_page value.
		$response = $this->get_product_tags( array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_product_tags( array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid order value.
		$response = $this->get_product_tags( array( 'order' => 'invalid' ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test product tag response structure.
	 *
	 * Verifies that the product tag response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_product_tag_response_structure() {
		// Create test tag.
		$tag = wp_insert_term( 'Test Tag', 'product_tag' );

		$response = $this->get_product_tag( $tag['term_id'] );
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'name', $data );
		$this->assertArrayHasKey( 'slug', $data );
		$this->assertArrayHasKey( 'description', $data );
		$this->assertArrayHasKey( 'count', $data );
		$this->assertArrayHasKey( 'link', $data );

		// Check data types.
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['name'] );
		$this->assertIsString( $data['slug'] );
		$this->assertIsString( $data['description'] );
		$this->assertIsInt( $data['count'] );
		$this->assertIsString( $data['link'] );
	}
} 