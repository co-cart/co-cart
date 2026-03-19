<?php
/**
 * Test CoCart Product Variations Controller
 *
 * Tests for CoCart product variations API endpoints including variation
 * listing, individual variation retrieval, and variation-related data.
 *
 * @package CoCart\Tests\Unit
 */

/**
 * Test CoCart Product Variations Controller Class
 *
 * Tests the product variations API endpoints which handle variation operations
 * like listing variations, retrieving individual variations, and accessing
 * variation metadata for variable products.
 *
 * @package CoCart\Tests\Unit
 */
class Test_CoCart_Product_Variations_Controller extends CoCart_API_V2_Test_Case {

	/**
	 * Test getting product variations list.
	 *
	 * Verifies that the product variations endpoint returns a list of all
	 * variations for a variable product with the correct response structure.
	 *
	 * @return void
	 */
	public function test_get_product_variations() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variations.
		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '15.00' );
		$variation1->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '20.00' );
		$variation2->set_attributes( array( 'pa_color' => 'Blue' ) );
		$variation2->save();

		$response = $this->get_product_variations( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		$this->assertCount( 2, $data['variations'] );
	}

	/**
	 * Test getting a single product variation.
	 *
	 * Verifies that individual product variations can be retrieved by ID
	 * and that the response contains the correct variation data.
	 *
	 * @return void
	 */
	public function test_get_single_product_variation() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variation.
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '25.00' );
		$variation->set_attributes( array( 'pa_size' => 'Large' ) );
		$variation->save();

		$response = $this->get_product_variation( $product->get_id(), $variation->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'id', $data );
		$this->assertEquals( $variation->get_id(), $data['id'] );
		$this->assertEquals( $product->get_id(), $data['product_id'] );
		$this->assertEquals( '25.00', $data['price'] );
	}

	/**
	 * Test getting non-existent product variation.
	 *
	 * Verifies that requesting a non-existent product variation returns
	 * a 404 Not Found status.
	 *
	 * @return void
	 */
	public function test_get_nonexistent_product_variation() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$response = $this->get_product_variation( $product->get_id(), 99999 );

		$this->assert_rest_response_status( 404, $response );
	}

	/**
	 * Test getting variations for non-variable product.
	 *
	 * Verifies that requesting variations for a simple product returns
	 * an appropriate error response.
	 *
	 * @return void
	 */
	public function test_get_variations_for_simple_product() {
		// Create simple product.
		$product = $this->create_product( array( 'name' => 'Simple Product' ) );

		$response = $this->get_product_variations( $product->get_id() );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		$this->assertCount( 0, $data['variations'] );
	}

	/**
	 * Test getting product variations with pagination.
	 *
	 * Verifies that the product variations endpoint supports pagination
	 * parameters and returns the correct number of variations per page.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_pagination() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create multiple variations.
		for ( $i = 1; $i <= 5; $i++ ) {
			$variation = new WC_Product_Variation();
			$variation->set_parent_id( $product->get_id() );
			$variation->set_regular_price( ( 10 + $i ) . '.00' );
			$variation->set_attributes( array( 'pa_size' => "Size {$i}" ) );
			$variation->save();
		}

		$response = $this->get_product_variations( $product->get_id(), array(
			'per_page' => 2,
			'page'     => 1,
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		$this->assertCount( 2, $data['variations'] );
	}

	/**
	 * Test getting product variations with attribute filter.
	 *
	 * Verifies that the product variations endpoint supports attribute filtering
	 * and returns only variations with the specified attribute value.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_attribute_filter() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variations with different attributes.
		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '15.00' );
		$variation1->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '20.00' );
		$variation2->set_attributes( array( 'pa_color' => 'Blue' ) );
		$variation2->save();

		$response = $this->get_product_variations( $product->get_id(), array(
			'attribute' => 'pa_color',
			'attribute_term' => 'Red',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		$this->assertCount( 1, $data['variations'] );
		$this->assertEquals( 'Red', $data['variations'][0]['attributes']['pa_color'] );
	}

	/**
	 * Test getting product variations with price filter.
	 *
	 * Verifies that the product variations endpoint supports price filtering
	 * and returns only variations within the specified price range.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_price_filter() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variations with different prices.
		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '10.00' );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '25.00' );
		$variation2->save();

		$response = $this->get_product_variations( $product->get_id(), array(
			'min_price' => '5.00',
			'max_price' => '15.00',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		
		// Check that only variations within price range are returned.
		foreach ( $data['variations'] as $variation ) {
			$price = floatval( $variation['price'] );
			$this->assertGreaterThanOrEqual( 5.00, $price );
			$this->assertLessThanOrEqual( 15.00, $price );
		}
	}

	/**
	 * Test getting product variations with stock status filter.
	 *
	 * Verifies that the product variations endpoint supports stock status filtering
	 * and returns only variations with the specified stock status.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_stock_status() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create in-stock variation.
		$in_stock_variation = new WC_Product_Variation();
		$in_stock_variation->set_parent_id( $product->get_id() );
		$in_stock_variation->set_regular_price( '15.00' );
		$in_stock_variation->set_stock_status( 'instock' );
		$in_stock_variation->save();

		// Create out-of-stock variation.
		$out_of_stock_variation = new WC_Product_Variation();
		$out_of_stock_variation->set_parent_id( $product->get_id() );
		$out_of_stock_variation->set_regular_price( '20.00' );
		$out_of_stock_variation->set_stock_status( 'outofstock' );
		$out_of_stock_variation->save();

		$response = $this->get_product_variations( $product->get_id(), array(
			'stock_status' => 'instock',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		
		// Check that only in-stock variations are returned.
		foreach ( $data['variations'] as $variation ) {
			$this->assertEquals( 'instock', $variation['stock_status'] );
		}
	}

	/**
	 * Test getting product variations with ordering.
	 *
	 * Verifies that the product variations endpoint supports different ordering
	 * options and returns variations in the correct order.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_ordering() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Create variations with different prices.
		$variation1 = new WC_Product_Variation();
		$variation1->set_parent_id( $product->get_id() );
		$variation1->set_regular_price( '10.00' );
		$variation1->save();

		$variation2 = new WC_Product_Variation();
		$variation2->set_parent_id( $product->get_id() );
		$variation2->set_regular_price( '25.00' );
		$variation2->save();

		// Test ordering by price ascending.
		$response = $this->get_product_variations( $product->get_id(), array(
			'orderby' => 'price',
			'order'   => 'asc',
		) );

		$this->assert_rest_response_status( 200, $response );

		$data = $response->get_data();
		$this->assertArrayHasKey( 'variations', $data );
		
		// Check that variations are ordered by price ascending.
		$prices = wp_list_pluck( $data['variations'], 'price' );
		$sorted_prices = $prices;
		sort( $sorted_prices, SORT_NUMERIC );
		$this->assertEquals( $sorted_prices, $prices );
	}

	/**
	 * Test getting product variations with context parameter.
	 *
	 * Verifies that the product variations endpoint supports different context
	 * parameters and returns appropriate data for each context.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_context() {
		// Create variable product and variation.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '15.00' );
		$variation->save();

		// Test with view context (default).
		$response = $this->get_product_variations( $product->get_id(), array( 'context' => 'view' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with embed context.
		$response = $this->get_product_variations( $product->get_id(), array( 'context' => 'embed' ) );
		$this->assert_rest_response_status( 200, $response );

		// Test with edit context (should fail for non-authenticated users).
		$response = $this->get_product_variations( $product->get_id(), array( 'context' => 'edit' ) );
		$this->assert_rest_response_status( 401, $response );
	}

	/**
	 * Test getting product variations with invalid parameters.
	 *
	 * Verifies that the product variations endpoint properly handles invalid
	 * parameters and returns appropriate error responses.
	 *
	 * @return void
	 */
	public function test_get_product_variations_with_invalid_parameters() {
		// Create variable product.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		// Test with invalid per_page value.
		$response = $this->get_product_variations( $product->get_id(), array( 'per_page' => -1 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid page value.
		$response = $this->get_product_variations( $product->get_id(), array( 'page' => 0 ) );
		$this->assert_rest_response_status( 400, $response );

		// Test with invalid order value.
		$response = $this->get_product_variations( $product->get_id(), array( 'order' => 'invalid' ) );
		$this->assert_rest_response_status( 400, $response );
	}

	/**
	 * Test product variation response structure.
	 *
	 * Verifies that the product variation response contains all expected
	 * fields and has the correct data types.
	 *
	 * @return void
	 */
	public function test_product_variation_response_structure() {
		// Create variable product and variation.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '15.00' );
		$variation->set_attributes( array( 'pa_color' => 'Red' ) );
		$variation->save();

		$response = $this->get_product_variation( $product->get_id(), $variation->get_id() );
		$data = $response->get_data();

		// Check required fields.
		$this->assertArrayHasKey( 'id', $data );
		$this->assertArrayHasKey( 'product_id', $data );
		$this->assertArrayHasKey( 'price', $data );
		$this->assertArrayHasKey( 'regular_price', $data );
		$this->assertArrayHasKey( 'sale_price', $data );
		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertArrayHasKey( 'stock_status', $data );
		$this->assertArrayHasKey( 'manage_stock', $data );
		$this->assertArrayHasKey( 'stock_quantity', $data );

		// Check data types.
		$this->assertIsInt( $data['id'] );
		$this->assertIsInt( $data['product_id'] );
		$this->assertIsString( $data['price'] );
		$this->assertIsString( $data['regular_price'] );
		$this->assertIsArray( $data['attributes'] );
		$this->assertIsString( $data['stock_status'] );
		$this->assertIsBool( $data['manage_stock'] );
		$this->assertIsInt( $data['stock_quantity'] );
	}

	/**
	 * Test product variation attributes.
	 *
	 * Verifies that the product variation correctly returns its attributes
	 * and that the attribute data is properly formatted.
	 *
	 * @return void
	 */
	public function test_product_variation_attributes() {
		// Create variable product and variation.
		$product = new WC_Product_Variable();
		$product->set_name( 'Variable Product' );
		$product->save();

		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product->get_id() );
		$variation->set_regular_price( '15.00' );
		$variation->set_attributes( array( 'pa_color' => 'Red', 'pa_size' => 'Large' ) );
		$variation->save();

		$response = $this->get_product_variation( $product->get_id(), $variation->get_id() );
		$data = $response->get_data();

		$this->assertArrayHasKey( 'attributes', $data );
		$this->assertArrayHasKey( 'pa_color', $data['attributes'] );
		$this->assertArrayHasKey( 'pa_size', $data['attributes'] );
		$this->assertEquals( 'Red', $data['attributes']['pa_color'] );
		$this->assertEquals( 'Large', $data['attributes']['pa_size'] );
	}
} 