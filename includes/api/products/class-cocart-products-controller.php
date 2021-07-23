<?php
/**
 * CoCart - Products controller
 *
 * Handles requests to the /products/ endpoint.
 *
 * @author   Sébastien Dumont
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Products_Controller
 */
class CoCart_Products_V2_Controller extends CoCart_Products_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

	/**
	 * Register the routes for products.
	 *
	 * @access public
	 */
	public function register_routes() {
		// Get Products - cocart/v2/products (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'args'                => $this->get_collection_params(),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);

		// Get a single product by ID - cocart/v2/products/32 (GET).
		// Get a single product by SKU - cocart/v2/products/woo-vneck-tee (GET).
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\w-]+)',
			array(
				'args'   => array(
					'id' => array(
						'description' => __( 'Unique identifier for the product.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
					),
				),
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'args'                => array(
						'context' => $this->get_context_param(
							array(
								'default' => 'view',
							)
						),
					),
					'permission_callback' => '__return_true',
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Prepare a single product output for response.
	 *
	 * @access public
	 * @param  WC_Data         $object  Object data.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $object, $request ) {
		// Check what product type before returning product data.
		if ( $object->get_type() !== 'variation' ) {
			$data = $this->get_product_data( $object );
		} else {
			$data = $this->get_variation_product_data( $object );
		}

		// Add review data to products if requested.
		if ( $request['show_reviews'] ) {
			$data['reviews'] = $this->get_reviews( $object );
		}

		// Add variations to variable products. Returns just IDs by default.
		if ( $object->is_type( 'variable' ) && $object->has_child() ) {
			$variation_ids = $object->get_children();

			foreach ( $variation_ids as $variation_id ) {
				$variation = wc_get_product( $variation_id );

				// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
				if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
					continue;
				}

				// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
				if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $variation_id, $variation ) && ! $variation->variation_is_visible() ) {
					continue;
				}

				$data['variations'][ $variation_id ] = array( 'id' => $variation_id );

				// If requested to return variations then fetch them.
				if ( $request['return_variations'] ) {
					// $variation_object                         = new WC_Product_Variation( $variation_id );
					$data['variations'][ $variation_id ] = $this->get_variation_product_data( $variation );
				}
			}
		}

		// Add grouped products data.
		if ( $object->is_type( 'grouped' ) && $object->has_child() ) {
			$data['grouped_products'] = $object->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $object, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Data          $object   Object data.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'cocart_prepare_product_object', $response, $object, $request );
	} // END prepare_object_for_response()

	/**
	 * Get a single item.
	 *
	 * @throws  CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response
	 */
	public function get_item( $request ) {
		try {
			$product_id = ! isset( $request['id'] ) ? 0 : wc_clean( wp_unslash( $request['id'] ) );

			// If the product ID was used by a SKU ID, then look up the product ID and return it.
			if ( ! is_numeric( $product_id ) ) {
				$product_id_by_sku = wc_get_product_id_by_sku( $product_id );

				if ( ! empty( $product_id_by_sku ) && $product_id_by_sku > 0 ) {
					$product_id = $product_id_by_sku;
				} else {
					$message = __( 'Product does not exist! Check that you have submitted a product ID or SKU ID correctly for a product that exists.', 'cart-rest-api-for-woocommerce' );

					throw new CoCart_Data_Exception( 'cocart_unknown_product_id', $message, 500 );
				}

				// Force product ID to be integer.
				$product_id = (int) $product_id;
			}

			$object = $this->get_object( (int) $product_id );

			if ( ! $object || 0 === $object->get_id() ) {
				throw new CoCart_Data_Exception( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $object, $request );
			$response = rest_ensure_response( $data );

			return $response;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END get_item()

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access protected
	 * @param  WC_Product|WC_Product_Variation $product Product instance.
	 * @return array $images
	 */
	protected function get_images( $product ) {
		$images           = array();
		$attachment_ids   = array();
		$attachment_sizes = apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );

		// Add featured image.
		if ( $product->get_image_id() ) {
			$attachment_ids[] = $product->get_image_id();
		}

		// Add gallery images.
		$attachment_ids = array_merge( $attachment_ids, $product->get_gallery_image_ids() );

		$attachments = array();

		// Build image data.
		foreach ( $attachment_ids as $position => $attachment_id ) {
			$attachment_post = get_post( $attachment_id );
			if ( is_null( $attachment_post ) ) {
				continue;
			}

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( $attachment_id, $size ) );
			}

			$images[] = array(
				'id'       => (int) $attachment_id,
				'src'      => $attachments,
				'name'     => get_the_title( $attachment_id ),
				'alt'      => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position' => (int) $position,
			);
		}

		// Set a placeholder image if the product has no images set.
		if ( empty( $images ) ) {
			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( get_option( 'woocommerce_placeholder_image', 0 ), $size ) );
			}

			$images[] = array(
				'id'       => 0,
				'src'      => $attachments,
				'name'     => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'alt'      => __( 'Placeholder', 'cart-rest-api-for-woocommerce' ),
				'position' => 0,
			);
		}

		return $images;
	} // END get_images()

	/**
	 * Get product data.
	 *
	 * @access protected
	 * @param  WC_Product $product Product instance.
	 * @return array
	 */
	protected function get_product_data( $product ) {
		$controller = new CoCart_Cart_V2_Controller();

		$type         = $product->get_type();
		$rating_count = $product->get_rating_count( 'view' );
		$average      = $product->get_average_rating( 'view' );

		$data = array(
			'id'                 => $product->get_id(),
			'parent_id'          => $product->get_parent_id( 'view' ),
			'name'               => $product->get_name( 'view' ),
			'type'               => $type,
			'slug'               => $product->get_slug( 'view' ),
			'permalink'          => $product->get_permalink(),
			'sku'                => $product->get_sku( 'view' ),
			'description'        => $product->get_description( 'view' ),
			'short_description'  => $product->get_short_description( 'view' ),
			'dates'              => array(
				'created'      => wc_rest_prepare_date_response( $product->get_date_created( 'view' ), false ),
				'created_gmt'  => wc_rest_prepare_date_response( $product->get_date_created( 'view' ) ),
				'modified'     => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ), false ),
				'modified_gmt' => wc_rest_prepare_date_response( $product->get_date_modified( 'view' ) ),
			),
			'featured'           => $product->is_featured(),
			'prices'             => array(
				'price'         => $controller->prepare_money_response( $product->get_price( 'view' ), wc_get_price_decimals() ),
				'regular_price' => $controller->prepare_money_response( $product->get_regular_price( 'view' ), wc_get_price_decimals() ),
				'sale_price'    => $product->get_sale_price( 'view' ) ? $controller->prepare_money_response( $product->get_sale_price( 'view' ), wc_get_price_decimals() ) : '',
				'price_range'   => '',
				'on_sale'       => $product->is_on_sale( 'view' ),
				'date_on_sale'  => array(
					'from'     => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ), false ),
					'from_gmt' => wc_rest_prepare_date_response( $product->get_date_on_sale_from( 'view' ) ),
					'to'       => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ), false ),
					'to_gmt'   => wc_rest_prepare_date_response( $product->get_date_on_sale_to( 'view' ) ),
				),
				'currency'      => $controller->get_store_currency(),
			),
			'hidden_conditions'  => array(
				'virtual'           => $product->is_virtual(),
				'downloadable'      => $product->is_downloadable(),
				'manage_stock'      => $product->managing_stock(),
				'sold_individually' => $product->is_sold_individually(),
				'reviews_allowed'   => $product->get_reviews_allowed( 'view' ),
				'shipping_required' => $product->needs_shipping(),
			),
			'average_rating'     => $average,
			'review_count'       => $product->get_review_count( 'view' ),
			'rating_count'       => $rating_count,
			'rated_out_of'       => html_entity_decode( wp_strip_all_tags( wc_get_rating_html( $average, $rating_count ) ) ),
			'images'             => $this->get_images( $product ),
			'categories'         => $this->get_taxonomy_terms( $product ),
			'tags'               => $this->get_taxonomy_terms( $product, 'tag' ),
			'attributes'         => $this->get_attributes( $product ),
			'default_attributes' => $this->get_default_attributes( $product ),
			'variations'         => array(),
			'grouped_products'   => array(),
			'stock'              => array(
				'is_in_stock'        => $product->is_in_stock(),
				'stock_quantity'     => $product->get_stock_quantity( 'view' ),
				'stock_status'       => $product->get_stock_status( 'view' ),
				'backorders'         => $product->get_backorders( 'view' ),
				'backorders_allowed' => $product->backorders_allowed(),
				'backordered'        => $product->is_on_backorder(),
				'low_stock_amount'   => $product->get_low_stock_amount( 'view' ),
			),
			'weight'             => array(
				'value' => $product->get_weight( 'view' ),
				'unit'  => get_option( 'woocommerce_weight_unit' ),
			),
			'dimensions'         => array(
				'length' => $product->get_length( 'view' ),
				'width'  => $product->get_width( 'view' ),
				'height' => $product->get_height( 'view' ),
				'unit'   => get_option( 'woocommerce_dimension_unit' ),
			),
			'reviews'            => array(),
			'related'            => $this->get_connected_products( $product, 'related' ),
			'upsells'            => $this->get_connected_products( $product, 'upsells' ),
			'cross_sells'        => $this->get_connected_products( $product, 'cross_sells' ),
			'total_sales'        => $product->get_total_sales( 'view' ),
			'external_url'       => $product->is_type( 'external' ) ? $product->get_product_url( 'view' ) : '',
			'button_text'        => $product->is_type( 'external' ) ? $product->get_button_text( 'view' ) : '',
			'add_to_cart'        => array(
				'text'           => $product->add_to_cart_text(),
				'description'    => $product->add_to_cart_description(),
				'has_options'    => $product->has_options(),
				'is_purchasable' => $product->is_purchasable(),
				'rest_url'       => $this->add_to_cart_rest_url( $product, $type ),
			),
			'meta_data'          => $product->get_meta_data(),
		);

		return $data;
	} // END get_product_data()

	/**
	 * Get variation product data.
	 *
	 * @access protected
	 * @param  WC_Variation_Product $product Product instance.
	 * @return array
	 */
	protected function get_variation_product_data( $product ) {
		$data = self::get_product_data( $product );

		// Remove fields not required for a variaion.
		unset( $data['type'] );
		unset( $data['short_description'] );
		unset( $data['conditions']['has_options'] );
		unset( $data['conditions']['reviews_allowed'] );
		unset( $data['average_rating'] );
		unset( $data['review_count'] );
		unset( $data['rating_count'] );
		unset( $data['rated_out_of'] );
		unset( $data['reviews'] );
		unset( $data['default_attributes'] );
		unset( $data['variations'] );
		unset( $data['grouped_products'] );
		unset( $data['related'] );
		unset( $data['upsells'] );
		unset( $data['cross_sells'] );
		unset( $data['external_url'] );
		unset( $data['button_text'] );

		return $data;
	} // END get_variation_product_data()

	/**
	 * Get taxonomy terms.
	 *
	 * @access protected
	 * @param  WC_Product $product  Product instance.
	 * @param  string     $taxonomy Taxonomy slug.
	 * @return array
	 */
	protected function get_taxonomy_terms( $product, $taxonomy = 'cat' ) {
		$terms = array();

		foreach ( wc_get_object_terms( $product->get_id(), 'product_' . $taxonomy ) as $term ) {
			$terms[] = array(
				'id'       => $term->term_id,
				'name'     => $term->name,
				'slug'     => $term->slug,
				'rest_url' => $this->product_rest_url( $term->term_id, $taxonomy ),
			);
		}

		return $terms;
	} // END get_taxonomy_terms()

	/**
	 * Get minimum details on connected products.
	 *
	 * @access public
	 * @param  WC_Product                      $product
	 * @param  $type Type of products to return.
	 */
	public function get_connected_products( $product, $type ) {
		$controller = new CoCart_Cart_V2_Controller();

		switch ( $type ) {
			case 'upsells':
				$ids = array_map( 'absint', $product->get_upsell_ids( 'view' ) );
				break;
			case 'cross_sells':
				$ids = array_map( 'absint', $product->get_cross_sell_ids( 'view' ) );
				break;
			case 'related':
			case 'default':
				$ids = array_map( 'absint', array_values( wc_get_related_products( $product->get_id(), apply_filters( 'cocart_products_get_related_products_limit', 5 ) ) ) );
				break;
		}

		$connected_products = array();

		foreach ( $ids as $id ) {
			$_product = wc_get_product( $id );

			$type = $_product->get_type();

			$connected_products[] = array(
				'id'          => $id,
				'name'        => $_product->get_name( 'view' ),
				'permalink'   => $_product->get_permalink(),
				'price'       => $controller->prepare_money_response( $_product->get_price( 'view' ), wc_get_price_decimals() ),
				'add_to_cart' => array(
					'text'        => $_product->add_to_cart_text(),
					'description' => $_product->add_to_cart_description(),
					'rest_url'    => $this->add_to_cart_rest_url( $_product, $type ),
				),
				'rest_url'    => $this->product_rest_url( $id ),
			);
		}

		return $connected_products;
	} // END get_connected_products()

	/**
	 * Returns the REST URL for a specific product or taxonomy.
	 *
	 * @access public
	 * @param  int    $id       Product ID or Taxonomy ID.
	 * @param  string $taxonomy Taxonomy type.
	 * @return string
	 */
	public function product_rest_url( $id, $taxonomy = '' ) {
		if ( ! empty( $taxonomy ) ) {
			switch ( $taxonomy ) {
				case 'cat':
					$route = '/%s/products/categories/%s';
					break;
				case 'tag':
					$route = '/%s/products/tags/%s';
					break;
			}
		} else {
			$route = '/%s/products/%s';
		}

		return rest_url( sprintf( $route, $this->namespace, $id ) );
	} // END product_rest_url()

	/**
	 * Returns an array of REST URLs for each ID.
	 *
	 * @access public
	 * @param  array $ids
	 * @return array
	 */
	public function product_rest_urls( $ids = array() ) {
		$rest_urls = array();

		foreach ( $ids as $id ) {
			$rest_urls[] = $this->product_rest_url( $id );
		}

		return $rest_urls;
	} // END product_rest_urls()

	/**
	 * Returns the REST URL for adding product to the cart.
	 *
	 * @access public
	 * @param  WC_Product $product
	 * @param  string     $type Product type.
	 * @return string
	 */
	public function add_to_cart_rest_url( $product, $type ) {
		$id = $product->get_id();

		$rest_url = rest_url( sprintf( '/%s/cart/add-item?id=%d', $this->namespace, $id ) );

		switch ( $type ) {
			case 'variation':
			case 'subscription_variation':
			case 'external':
			case 'grouped':
				$rest_url = ''; // Return nothing for these product types.
				break;
			case 'default':
				$rest_url = apply_filter( 'cocart_products_add_to_cart_rest_url', '', $product, $type, $id );
				break;
		}

		return $rest_url;
	} // END add_to_cart_rest_url()

} // END class
