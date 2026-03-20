<?php
/**
 * Test CoCart Product Reviews Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Product_Reviews_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product reviews list.
	 *
	 * @return void
	 */
	public function test_get_product_reviews() {
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		$comment_id = wp_insert_comment( array(
			'comment_post_ID'  => $product->get_id(),
			'comment_author'   => 'Tester',
			'comment_content'  => 'Great product!',
			'comment_type'     => 'review',
			'comment_approved' => 1,
		) );
		add_comment_meta( $comment_id, 'rating', 5 );

		$response = $this->get_product_reviews();

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThanOrEqual( 1, count( $data ) );
	}

	/**
	 * Test getting a single product review.
	 *
	 * @return void
	 */
	public function test_get_single_product_review() {
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		$comment_id = wp_insert_comment( array(
			'comment_post_ID'  => $product->get_id(),
			'comment_author'   => 'Test Reviewer',
			'comment_content'  => 'Excellent!',
			'comment_type'     => 'review',
			'comment_approved' => 1,
		) );
		add_comment_meta( $comment_id, 'rating', 5 );

		$response = $this->get_product_review( $comment_id );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $comment_id, $data['id'] );
	}

	/**
	 * Test getting non-existent product review returns 404.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_review() {
		$response = $this->get_product_review( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test product review response structure.
	 *
	 * @return void
	 */
	public function test_product_review_response_structure() {
		$product = $this->create_product( array( 'name' => 'Review Product' ) );

		$comment_id = wp_insert_comment( array(
			'comment_post_ID'  => $product->get_id(),
			'comment_author'   => 'Structured Tester',
			'comment_content'  => 'Structured review',
			'comment_type'     => 'review',
			'comment_approved' => 1,
		) );
		add_comment_meta( $comment_id, 'rating', 4 );

		$response = $this->get_product_review( $comment_id );
		$data     = $response->get_data();

		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'reviewer', $data );
		$this->assertArrayHasKey( 'review', $data );
		$this->assertArrayHasKey( 'rating', $data );
		$this->assertIsInt( $data['id'] );
		$this->assertIsString( $data['reviewer'] );
	}
}
