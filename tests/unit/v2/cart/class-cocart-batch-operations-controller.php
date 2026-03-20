<?php
/**
 * Test CoCart Batch Operations
 *
 * Tests that CoCart cart endpoints support the WP REST API batch v1 protocol,
 * which allows multiple requests to be processed in a single HTTP call via
 * POST /batch/v1 with { "requests": [...] }.
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Batch_Operations_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test that the add-item endpoint supports batch requests.
	 *
	 * CoCart routes that set allow_batch => ['v1' => true] can be called via
	 * the WP REST API batch endpoint.
	 *
	 * @return void
	 */
	public function test_add_item_supports_batch() {
		$product1 = $this->create_product( array(
			'name'          => 'Product 1',
			'regular_price' => '25.00',
		) );
		$product2 = $this->create_product( array(
			'name'          => 'Product 2',
			'regular_price' => '30.00',
		) );

		$request = new WP_REST_Request( 'POST', '/batch/v1' );
		$request->set_body_params( array(
			'requests' => array(
				array(
					'method' => 'POST',
					'path'   => '/cocart/v2/cart/add-item',
					'body'   => array(
						'id'       => (string) $product1->get_id(),
						'quantity' => '1',
					),
				),
				array(
					'method' => 'POST',
					'path'   => '/cocart/v2/cart/add-item',
					'body'   => array(
						'id'       => (string) $product2->get_id(),
						'quantity' => '1',
					),
				),
			),
		) );

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 207, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'responses', $data );
		$this->assertCount( 2, $data['responses'] );
	}

	/**
	 * Test that individual batch sub-requests return 200 when valid.
	 *
	 * @return void
	 */
	public function test_batch_sub_requests_return_200() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$request = new WP_REST_Request( 'POST', '/batch/v1' );
		$request->set_body_params( array(
			'requests' => array(
				array(
					'method' => 'POST',
					'path'   => '/cocart/v2/cart/add-item',
					'body'   => array(
						'id'       => (string) $product->get_id(),
						'quantity' => '2',
					),
				),
			),
		) );

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 207, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'responses', $data );
		$this->assertEquals( 200, $data['responses'][0]['status'] );
	}

	/**
	 * Test that calculate endpoint supports batch.
	 *
	 * @return void
	 */
	public function test_calculate_supports_batch() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$request = new WP_REST_Request( 'POST', '/batch/v1' );
		$request->set_body_params( array(
			'requests' => array(
				array(
					'method' => 'POST',
					'path'   => '/cocart/v2/cart/calculate',
					'body'   => array(),
				),
			),
		) );

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 207, $response->get_status() );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'responses', $data );
		$this->assertEquals( 200, $data['responses'][0]['status'] );
	}

	/**
	 * Test that clear cart endpoint supports batch.
	 *
	 * @return void
	 */
	public function test_clear_cart_supports_batch() {
		$product = $this->create_product( array(
			'name'          => 'Test Product',
			'regular_price' => '25.00',
		) );

		$this->add_item_to_cart( $product->get_id(), 1 );

		$request = new WP_REST_Request( 'POST', '/batch/v1' );
		$request->set_body_params( array(
			'requests' => array(
				array(
					'method' => 'POST',
					'path'   => '/cocart/v2/cart/clear',
					'body'   => array(),
				),
			),
		) );

		$response = rest_get_server()->dispatch( $request );
		$this->assertEquals( 207, $response->get_status() );
	}
}
