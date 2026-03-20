<?php
/**
 * Test CoCart Product Variations Controller
 *
 * @package CoCart\Tests\Unit
 */

class Test_CoCart_Product_Variations_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product variations list.
	 *
	 * @return void
	 */
	public function test_get_product_variations() {
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '15.00' );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '20.00' );
		$variation2->save();

		$response = $this->get_product_variations( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertIsArray( $data );
		$this->assertGreaterThanOrEqual( 2, count( $data ) );
	}

	/**
	 * Test getting a single product variation.
	 *
	 * @return void
	 */
	public function test_get_single_product_variation() {
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '25.00' );
		$variation->save();

		$response = $this->get_product_variation( $product->get_id(), $variation->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $variation->get_id(), $data['id'] );
	}

	/**
	 * Test getting variations for non-existent product returns 404.
	 *
	 * @return void
	 */
	public function test_get_variations_for_nonexistent_product() {
		$response = $this->get_product_variations( 99999 );

		$this->assert_rest_response_status( 404, $response );
	}
}
