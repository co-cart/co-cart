<?php
/**
 * Test CoCart Product Reviews Mine Controller
 *
 * Tests for CoCart product reviews/mine endpoint (reviews by logged-in user).
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Reviews Mine Controller Class
 *
 * Tests the endpoint GET /cocart/v2/products/reviews/mine.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Reviews_Mine_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Customer user ID.
	 *
	 * @var int
	 */
	private $customer_id;

	/**
	 * Set up test.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$this->customer_id = $this->factory->user->create( array( 'role' => 'customer' ) );
	}

	/**
	 * Tear down test.
	 *
	 * @return void
	 */
	public function tear_down() {
		wp_delete_user( $this->customer_id );
		wp_set_current_user( 0 );

		parent::tear_down();
	}

	/**
	 * Test that reviews/mine returns 403 when not logged in.
	 *
	 * @return void
	 */
	public function test_get_my_reviews_requires_login() {
		wp_set_current_user( 0 );

		$response = $this->rest_get( '/cocart/v2/products/reviews/mine' );

		$this->assert_rest_response_status( 403, $response );
	}

	/**
	 * Test that reviews/mine returns 200 when logged in.
	 *
	 * @return void
	 */
	public function test_get_my_reviews_returns_200_when_logged_in() {
		wp_set_current_user( $this->customer_id );

		$response = $this->rest_get( '/cocart/v2/products/reviews/mine' );

		$this->assert_rest_response_status( 200, $response );
	}

	/**
	 * Test that reviews/mine only returns reviews by the current user.
	 *
	 * @return void
	 */
	public function test_get_my_reviews_returns_only_own_reviews() {
		$product = $this->create_product( array( 'name' => 'Mine Review Product' ) );

		// Review by our customer.
		$own_comment_id = wp_insert_comment( array(
			'comment_post_ID'  => $product->get_id(),
			'comment_author'   => 'Customer',
			'user_id'          => $this->customer_id,
			'comment_content'  => 'My review',
			'comment_type'     => 'review',
			'comment_approved' => 1,
		) );
		add_comment_meta( $own_comment_id, 'rating', 4 );

		// Review by someone else.
		$other_comment_id = wp_insert_comment( array(
			'comment_post_ID'  => $product->get_id(),
			'comment_author'   => 'Someone Else',
			'user_id'          => 0,
			'comment_content'  => 'Other review',
			'comment_type'     => 'review',
			'comment_approved' => 1,
		) );
		add_comment_meta( $other_comment_id, 'rating', 3 );

		wp_set_current_user( $this->customer_id );

		$response = $this->rest_get( '/cocart/v2/products/reviews/mine' );

		$this->assert_rest_response_status( 200, $response );

		$data   = $response->get_data();
		$ids    = array_column( $data, 'id' );

		$this->assertContains( (int) $own_comment_id, array_map( 'intval', $ids ) );
		$this->assertNotContains( (int) $other_comment_id, array_map( 'intval', $ids ) );
	}
}
