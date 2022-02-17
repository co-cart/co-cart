<?php
/**
 * CoCart - Products controller
 *
 * Handles requests to the /products/ endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product controller class.
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
	 * Register routes.
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
	 * @param  WC_Product      $product Product instance.
	 * @param  WP_REST_Request $request Request object.
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		// Check what product type before returning product data.
		if ( $product->get_type() !== 'variation' ) {
			$data = $this->get_product_data( $product );
		} else {
			$data = $this->get_variation_product_data( $product );
		}

		// Add review data to products if requested.
		if ( $request['show_reviews'] ) {
			$data['reviews'] = $this->get_reviews( $product );
		}

		// Return each variation if the variable product has variations available.
		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$data['variations'] = $this->get_variations( $product );
		}

		// Add grouped products data.
		if ( $product->is_type( 'grouped' ) && $product->has_child() ) {
			$data['grouped_products'] = $product->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );
		$response->add_links( $this->prepare_links( $product, $request ) );

		/**
		 * Filter the data for a response.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  Product instance.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object_v2", $response, $product, $request );
	} // END prepare_object_for_response()

	/**
	 * Return the basic of each variation to make it easier
	 * for developers with their UI/UX.
	 *
	 * @access public
	 * @param  WC_Product $product    Product instance.
	 * @return array      $variations Returns the variations.
	 */
	public function get_variations( $product ) {
		$variation_ids    = $product->get_children();
		$tax_display_mode = $this->get_tax_display_mode();
		$price_function   = $this->get_price_from_tax_display_mode( $tax_display_mode );

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

			$expected_attributes = wc_get_product_variation_attributes( $variation_id );
			$featured_image_id   = $variation->get_image_id();
			$attachment_post     = get_post( $featured_image_id );
			$attachment_sizes    = apply_filters( 'cocart_products_image_sizes', array_merge( get_intermediate_image_sizes(), array( 'full', 'custom' ) ) );

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				$attachments[ $size ] = current( wp_get_attachment_image_src( $featured_image_id, $size ) );
			}

			$variations[] = array(
				'id'             => $variation_id,
				'sku'            => $variation->get_sku( 'view' ),
				'description'    => $variation->get_description( 'view' ),
				'attributes'     => $expected_attributes,
				'featured_image' => $attachments,
				'prices'         => array(
					'price'         => cocart_prepare_money_response( $price_function( $variation ), wc_get_price_decimals() ),
					'regular_price' => cocart_prepare_money_response( $price_function( $variation, array( 'price' => $variation->get_regular_price() ) ), wc_get_price_decimals() ),
					'sale_price'    => $variation->get_sale_price( 'view' ) ? cocart_prepare_money_response( $price_function( $variation, array( 'price' => $variation->get_sale_price() ) ), wc_get_price_decimals() ) : '',
					'on_sale'       => $variation->is_on_sale( 'view' ),
					'date_on_sale'  => array(
						'from'     => cocart_prepare_date_response( strtotime( $variation->get_date_on_sale_from( 'view' ) ), false ),
						'from_gmt' => cocart_prepare_date_response( strtotime( $variation->get_date_on_sale_from( 'view' ) ) ),
						'to'       => cocart_prepare_date_response( strtotime( $variation->get_date_on_sale_to( 'view' ) ), false ),
						'to_gmt'   => cocart_prepare_date_response( strtotime( $variation->get_date_on_sale_to( 'view' ) ) ),
					),
					'currency'      => cocart_get_store_currency(),
				),
				'add_to_cart'    => array(
					'is_purchasable'    => $variation->is_purchasable(),
					'purchase_quantity' => array(
						'min_purchase' => apply_filters( 'cocart_quantity_minimum_requirement', $variation->get_min_purchase_quantity(), $variation ),
						'max_purchase' => apply_filters( 'cocart_quantity_maximum_allowed', $variation->get_max_purchase_quantity(), $variation ),
					),
					'rest_url'          => $this->add_to_cart_rest_url( $variation, $variation->get_type() ),
				),
			);
		}

		return $variations;
	} // END get_variations()

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
			}

			// Force product ID to be integer.
			$product_id = (int) $product_id;

			$_product = wc_get_product( $product_id );

			if ( ! $_product || 0 === $_product->get_id() ) {
				throw new CoCart_Data_Exception( 'cocart_' . $this->post_type . '_invalid_id', __( 'Invalid ID.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			$data     = $this->prepare_object_for_response( $_product, $request );
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

			$featured = $position === 0 ? true : false;

			$images[] = array(
				'id'       => (int) $attachment_id,
				'src'      => $attachments,
				'name'     => get_the_title( $attachment_id ),
				'alt'      => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
				'position' => (int) $position,
				'featured' => $featured,
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
				'featured' => true,
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
		$type         = $product->get_type();
		$rating_count = $product->get_rating_count( 'view' );
		$average      = $product->get_average_rating( 'view' );

		$tax_display_mode = $this->get_tax_display_mode();
		$price_function   = $this->get_price_from_tax_display_mode( $tax_display_mode );

		// If we have a variable product, get the price from the variations (this will use the min value).
		if ( $product->is_type( 'variable' ) ) {
			$regular_price = $product->get_variation_regular_price();
			$sale_price    = $product->get_variation_sale_price();
		} else {
			$regular_price = $product->get_regular_price();
			$sale_price    = $product->get_sale_price();
		}

		// Provide purchase quantity if not a variable or external product type.
		$purchase_quantity = array();

		if ( ! $product->is_type( 'variable' ) && ! $product->is_type( 'external' ) ) {
			$purchase_quantity = array(
				'min_purchase' => apply_filters( 'cocart_quantity_minimum_requirement', $product->get_min_purchase_quantity(), $product ),
				'max_purchase' => apply_filters( 'cocart_quantity_maximum_allowed', $product->get_max_purchase_quantity(), $product ),
			);
		}

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
				'created'      => cocart_prepare_date_response( strtotime( $product->get_date_created( 'view' ) ), false ),
				'created_gmt'  => cocart_prepare_date_response( strtotime( $product->get_date_created( 'view' ) ) ),
				'modified'     => cocart_prepare_date_response( strtotime( $product->get_date_modified( 'view' ) ), false ),
				'modified_gmt' => cocart_prepare_date_response( strtotime( $product->get_date_modified( 'view' ) ) ),
			),
			'featured'           => $product->is_featured(),
			'prices'             => array(
				'price'         => cocart_prepare_money_response( $price_function( $product ), wc_get_price_decimals() ),
				'regular_price' => cocart_prepare_money_response( $price_function( $product, array( 'price' => $regular_price ) ), wc_get_price_decimals() ),
				'sale_price'    => $product->get_sale_price( 'view' ) ? cocart_prepare_money_response( $price_function( $product, array( 'price' => $sale_price ) ), wc_get_price_decimals() ) : '',
				'price_range'   => $this->get_price_range( $product, $tax_display_mode ),
				'on_sale'       => $product->is_on_sale( 'view' ),
				'date_on_sale'  => array(
					'from'     => cocart_prepare_date_response( strtotime( $product->get_date_on_sale_from( 'view' ) ), false ),
					'from_gmt' => cocart_prepare_date_response( strtotime( $product->get_date_on_sale_from( 'view' ) ) ),
					'to'       => cocart_prepare_date_response( strtotime( $product->get_date_on_sale_to( 'view' ) ), false ),
					'to_gmt'   => cocart_prepare_date_response( strtotime( $product->get_date_on_sale_to( 'view' ) ) ),
				),
				'currency'      => cocart_get_store_currency(),
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
				'text'              => $product->add_to_cart_text(),
				'description'       => $product->add_to_cart_description(),
				'has_options'       => $product->has_options(),
				'is_purchasable'    => $product->is_purchasable(),
				'purchase_quantity' => $purchase_quantity,
				'rest_url'          => $this->add_to_cart_rest_url( $product, $type ),
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

		// Remove fields not required for a variation.
		unset( $data['type'] );
		unset( $data['short_description'] );
		unset( $data['hidden_conditions']['reviews_allowed'] );
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
		unset( $data['add_to_cart']['has_options'] );

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
	 * Get attribute options.
	 *
	 * @access protected
	 * @param  int   $product_id Product ID.
	 * @param  array $attribute  Attribute data.
	 * @return array
	 */
	protected function get_attribute_options( $product_id, $attribute ) {
		$attributes = array();

		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {
			$terms = wc_get_product_terms(
				$product_id,
				$attribute['name'],
				array(
					'fields' => 'all',
				)
			);

			foreach ( $terms as $term ) {
				$attributes[ $term->slug ] = $term->name;
			}
		} elseif ( isset( $attribute['value'] ) ) {
			$options = explode( '|', $attribute['value'] );

			foreach ( $options as $attribute ) {
				$slug                = trim( $attribute );
				$attributes[ $slug ] = trim( $attribute );
			}
		}

		return $attributes;
	} // END get_attribute_options()

	/**
	 * Get the attributes for a product or product variation.
	 *
	 * @access protected
	 * @param  WC_Product|WC_Product_Variation $product Product instance.
	 * @return array
	 */
	protected function get_attributes( $product ) {
		$attributes = array();

		if ( $product->is_type( 'variation' ) || $product->is_type( 'subscription_variation' ) ) {
			$_product = wc_get_product( $product->get_parent_id() );

			foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
				$name = str_replace( 'attribute_', '', $attribute_name );

				if ( ! $attribute ) {
					continue;
				}

				// Taxonomy-based attributes are prefixed with `pa_`, otherwise simply `attribute_`.
				if ( 0 === strpos( $attribute_name, 'attribute_pa_' ) ) {
					$option_term = get_term_by( 'slug', $attribute, $name );

					$attributes[ 'attribute_' . $name ] = array(
						'id'     => wc_attribute_taxonomy_id_by_name( $name ),
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => $option_term && ! is_wp_error( $option_term ) ? array( $option_term->slug => $option_term->name ) : array( $attribute => $attribute ),
					);
				} else {
					$attributes[ 'attribute_' . $name ] = array(
						'id'     => 0,
						'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
						'option' => array( $attribute => $attribute ),
					);
				}
			}
		} else {
			foreach ( $product->get_attributes() as $attribute ) {
				$attribute_id = 'attribute_' . str_replace( ' ', '-', strtolower( $attribute['name'] ) );

				$attributes[ $attribute_id ] = array(
					'id'                   => $attribute['is_taxonomy'] ? wc_attribute_taxonomy_id_by_name( $attribute['name'] ) : 0,
					'name'                 => $this->get_attribute_taxonomy_name( $attribute['name'], $product ),
					'position'             => (int) $attribute['position'],
					'is_attribute_visible' => (bool) $attribute['is_visible'],
					'used_for_variation'   => (bool) $attribute['is_variation'],
					'options'              => $this->get_attribute_options( $product->get_id(), $attribute ),
				);
			}
		}

		return $attributes;
	} // END get_attributes()

	/**
	 * Get minimum details on connected products.
	 *
	 * @access public
	 * @param  WC_Product                      $product
	 * @param  $type Type of products to return.
	 */
	public function get_connected_products( $product, $type ) {
		switch ( $type ) {
			case 'upsells':
				$ids = array_map( 'absint', $product->get_upsell_ids( 'view' ) );
				break;
			case 'cross_sells':
				$ids = array_map( 'absint', $product->get_cross_sell_ids( 'view' ) );
				break;
			case 'related':
			default:
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
				'price'       => cocart_prepare_money_response( $_product->get_price( 'view' ), wc_get_price_decimals() ),
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
		$rest_url = add_query_arg( 'quantity', 1, $rest_url ); // Default Quantity = 1.

		switch ( $type ) {
			case 'variation':
			case 'subscription_variation':
				$_product = wc_get_product( $product->get_parent_id() );

				foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
					$name = str_replace( 'attribute_', '', $attribute_name );

					if ( ! $attribute ) {
						continue;
					}

					$rest_url = add_query_arg( array(
						"variation[attribute_$name]" => $attribute,
					), $rest_url );
				}

				$rest_url = urldecode( html_entity_decode( $rest_url ) );
				break;
			case 'variable':
			case 'variable-subscription':
			case 'external':
			case 'grouped':
				$rest_url = ''; // Return nothing for these product types.
				break;
			default:
				$rest_url = apply_filters( 'cocart_products_add_to_cart_rest_url', $rest_url, $product, $type, $id );
				break;
		}

		return $rest_url;
	} // END add_to_cart_rest_url()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 * @param  string $tax_display_mode - Provided tax display mode.
	 * @return string Valid tax display mode.
	 */
	protected function get_tax_display_mode( $tax_display_mode = '' ) {
		return in_array( $tax_display_mode, array( 'incl', 'excl' ), true ) ? $tax_display_mode : get_option( 'woocommerce_tax_display_shop' );
	} // END get_tax_display_mode()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 * @param  string $tax_display_mode - If returned prices are incl or excl of tax.
	 * @return string Function name.
	 */
	protected function get_price_from_tax_display_mode( $tax_display_mode ) {
		return 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';
	} // END get_price_from_tax_display_mode()

	/**
	 * Returns the price range for variable or grouped product.
	 *
	 * @access public
	 * @param  \WC_Product $product Product object.
	 * @param  string      $tax_display_mode If returned prices are incl or excl of tax.
	 * @return array
	 */
	public function get_price_range( $product, $tax_display_mode = '' ) {
		$tax_display_mode = $this->get_tax_display_mode( $tax_display_mode );

		$price = array();

		if ( $product->is_type( 'variable' ) && $product->has_child() ) {
			$prices = $product->get_variation_prices( true );

			if ( empty( $prices['price'] ) ) {
				$price = apply_filters( 'cocart_products_variable_empty_price', array(), $product );
			} else {
				$min_price     = current( $prices['price'] );
				$max_price     = end( $prices['price'] );
				$min_reg_price = current( $prices['regular_price'] );
				$max_reg_price = end( $prices['regular_price'] );

				if ( $min_price !== $max_price ) {
					$price = array(
						'from' => cocart_prepare_money_response( $min_price, wc_get_price_decimals() ),
						'to'   => cocart_prepare_money_response( $max_price, wc_get_price_decimals() ),
					);
				} else {
					$price = array(
						'from' => cocart_prepare_money_response( $min_price, wc_get_price_decimals() ),
						'to'   => '',
					);
				}
			}
		}

		if ( $product->is_type( 'grouped' ) ) {
			$children       = array_filter( array_map( 'wc_get_product', $product->get_children() ), 'wc_products_array_filter_visible_grouped' );
			$price_function = $this->get_price_from_tax_display_mode( $tax_display_mode );

			foreach ( $children as $child ) {
				if ( '' !== $child->get_price() ) {
					$child_prices[] = $price_function( $child );
				}
			}

			if ( ! empty( $child_prices ) ) {
				$price = array(
					'from' => cocart_prepare_money_response( min( $child_prices ), wc_get_price_decimals() ),
					'to'   => cocart_prepare_money_response( max( $child_prices ), wc_get_price_decimals() ),
				);
			}
		}

		return apply_filters( 'cocart_products_get_price_range', $price, $product );
	} // END get_price_range()

} // END class
