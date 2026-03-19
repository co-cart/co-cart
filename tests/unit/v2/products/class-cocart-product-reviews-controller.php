<?php
/**
 * Test CoCart Product Reviews Controller
 *
 * Tests for CoCart product reviews API endpoints including review
 * listing, individual review retrieval, and review-related data.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Reviews Controller Class
 *
 * Tests the product reviews API endpoints which handle review operations
 * like listing reviews, retrieving individual reviews, and accessing
 * review metadata.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Reviews_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product reviews list.
	 *
	 * Verifies that the product reviews endpoint returns a list of all
	 * available product reviews with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_product_reviews() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create test reviews.
		$review1 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'John Doe',
			'review'   => 'Great product!',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Jane Smith',
			'review'   => 'Good quality.',
			'rating'   => 4,
		) );

		$response = $this->get_product_reviews();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		$this->assertGreaterThanOrEqual( 2, count( $data['reviews'] ) );
	}

	/**
	 * Test getting a single product review.
	 *
	 * Verifies that individual product reviews can be retrieved by ID
	 * and that the response contains the correct review data.
	 *
	 * @return void
	 */
	public function test_get_single_product_review() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create test review.
		$review = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Test Reviewer',
			'review'   => 'Test review content',
			'rating'   => 5,
		) );

		$response = $this->get_product_review( $review->comment_ID );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $review->comment_ID, $data['id'] );
		$this->assertEquals( 'Test Reviewer', $data['reviewer'] );
		$this->assertEquals( 'Test review content', $data['review'] );
		$this->assertEquals( 5, $data['rating'] );
	}

	/**
	 * Test getting non-existent product review.
	 *
	 * Verifies that requesting a non-existent product review returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_review() {
		$response = $this->get_product_review( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting product reviews with pagination.
	 *
	 * Verifies that the product reviews endpoint supports pagination
	 * parameters and returns the correct number of reviews per page.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_pagination() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create multiple reviews.
		for ( $i = 1; $i <= 5; $i++ ) {
			$this->create_product_review( $product->get_id(), array(
				'reviewer' => "Reviewer {$i}",
				'review'   => "Review {$i}",
				'rating'   => 5,
			) );
		}

		$response = $this->get_product_reviews( array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		$this->assertCount( 2, $data['reviews'] );
	}

	/**
	 * Test getting product reviews with product filter.
	 *
	 * Verifies that the product reviews endpoint supports product filtering
	 * and returns only reviews for the specified product.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_product_filter() {
		// Create test products.
		$product1 = $this->create_product( array( 'name' => 'Product 1' ) );
		$product2 = $this->create_product( array( 'name' => 'Product 2' ) );

		// Create reviews for different products.
		$review1 = $this->create_product_review( $product1->get_id(), array(
			'reviewer' => 'Reviewer 1',
			'review'   => 'Review for Product 1',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product2->get_id(), array(
			'reviewer' => 'Reviewer 2',
			'review'   => 'Review for Product 2',
			'rating'   => 4,
		) );

		$response = $this->get_product_reviews( array(
			'product' => $product1->get_id(),
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		$this->assertCount( 1, $data['reviews'] );
		$this->assertEquals( 'Review for Product 1', $data['reviews'][0]['review'] );
	}

	/**
	 * Test getting product reviews with rating filter.
	 *
	 * Verifies that the product reviews endpoint supports rating filtering
	 * and returns only reviews with the specified rating.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_rating_filter() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create reviews with different ratings.
		$review1 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Reviewer 1',
			'review'   => '5-star review',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Reviewer 2',
			'review'   => '3-star review',
			'rating'   => 3,
		) );

		$response = $this->get_product_reviews( array(
			'rating' => 5,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		
		// Check that only 5-star reviews are returned.
		foreach ( $data['reviews'] as $review ) {
			$this->assertEquals( 5, $review['rating'] );
		}
	}

	/**
	 * Test getting product reviews with status filter.
	 *
	 * Verifies that the product reviews endpoint supports status filtering
	 * and returns only reviews with the specified status.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_status_filter() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create approved review.
		$approved_review = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Approved Reviewer',
			'review'   => 'Approved review',
			'rating'   => 5,
			'status'   => 'approve',
		) );

		// Create pending review.
		$pending_review = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Pending Reviewer',
			'review'   => 'Pending review',
			'rating'   => 4,
			'status'   => 'hold',
		) );

		$response = $this->get_product_reviews( array(
			'status' => 'approve',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		
		// Check that only approved reviews are returned.
		foreach ( $data['reviews'] as $review ) {
			$this->assertEquals( 'approved', $review['status'] );
		}
	}

	/**
	 * Test getting product reviews with ordering.
	 *
	 * Verifies that the product reviews endpoint supports different ordering
	 * options and returns reviews in the correct order.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_ordering() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create reviews with different dates.
		$review1 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'First Reviewer',
			'review'   => 'First review',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Second Reviewer',
			'review'   => 'Second review',
			'rating'   => 4,
		) );

		// Test ordering by date descending (newest first).
		$response = $this->get_product_reviews( array(
			'orderby' => 'date',
			'order'   => 'desc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		
		// Check that reviews are ordered by date descending.
		$review_dates = wp_list_pluck( $data['reviews'], 'date_created' );
		$sorted_dates = $review_dates;
		rsort( $sorted_dates );
		$this->assertEquals( $sorted_dates, $review_dates );
	}

	/**
	 * Test getting product reviews with search.
	 *
	 * Verifies that the product reviews endpoint supports search functionality
	 * and returns only reviews matching the search term.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_search() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create reviews with different content.
		$review1 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Reviewer 1',
			'review'   => 'This is an excellent product',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Reviewer 2',
			'review'   => 'This is a good product',
			'rating'   => 4,
		) );

		$response = $this->get_product_reviews( array(
			'search' => 'excellent',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		$this->assertCount( 1, $data['reviews'] );
		$this->assertEquals( 'This is an excellent product', $data['reviews'][0]['review'] );
	}

	/**
	 * Test getting product reviews with reviewer filter.
	 *
	 * Verifies that the product reviews endpoint supports reviewer filtering
	 * and returns only reviews from the specified reviewer.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_reviewer_filter() {
		// Create test product.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		// Create reviews from different reviewers.
		$review1 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'John Doe',
			'review'   => 'Review from John',
			'rating'   => 5,
		) );
		$review2 = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Jane Smith',
			'review'   => 'Review from Jane',
			'rating'   => 4,
		) );

		$response = $this->get_product_reviews( array(
			'reviewer' => 'John Doe',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'reviews', $data );
		$this->assertCount( 1, $data['reviews'] );
		$this->assertEquals( 'John Doe', $data['reviews'][0]['reviewer'] );
	}

	/**
	 * Test getting product reviews with context parameter.
	 *
	 * Verifies that the product reviews endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_context() {
		// Create test product and review.
		$product = $this->create_product( array( 'name' => 'Review Product' ) );
		$review = $this->create_product_review( $product->get_id(), array(
			'reviewer' => 'Test Reviewer',
			'review'   => 'Test review',
			'rating'   => 5,
		) );

		// Test with view context (default).
		$response = $this->get_product_reviews( array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_product_reviews( array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_product_reviews( array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting product reviews with invalid parameters.
	 *
	 * Verifies that the product reviews endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_product_reviews_with_invalid_parameters() {
		// Test with invalid per_page value.
		$response = $this->get_product_reviews( array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_product_reviews( array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid rating value.
		$response = $this->get_product_reviews( array( 'rating' => 6 ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Helper method to create a product review.
	 *
	 * Creates a test product review with the specified parameters.
	 *
	 * @param int   $product_id The product ID.
	 * @param array $args       Review arguments.
	 * @return WP_Comment The created review comment.
	 */
	private function create_product_review( $product_id, $args = array() ) {
		$defaults = array(
			'reviewer' => 'Test Reviewer',
			'review'   => 'Test review content',
			'rating'   => 5,
			'status'   => 'approve',
		);

		$args = wp_parse_args( $args, $defaults );

		$comment_data = array(
			'comment_post_ID'      => $product_id,
			'comment_author'       => $args['reviewer'],
			'comment_content'      => $args['review'],
			'comment_type'         => 'review',
			'comment_approved'     => $args['status'] === 'approve' ? 1 : 0,
			'user_id'              => 0,
			'comment_date'         => current_time( 'mysql' ),
			'comment_date_gmt'     => current_time( 'mysql', 1 ),
		);

		$comment_id = wp_insert_comment( $comment_data );

		// Add rating meta.
		if ( $args['rating'] ) {
			add_comment_meta( $comment_id, 'rating', $args['rating'] );
		}

		return get_comment( $comment_id );
	}
} 