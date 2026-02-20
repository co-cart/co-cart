<?php
/**
 * REST API: CoCart_REST_Products_V2_Controller class
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0 Introduced.
 * @version 5.0.0
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class_alias( 'CoCart_REST_Products_V2_Controller', 'CoCart_Products_V2_Controller' );

/**
 * Controller for returning products via the REST API (API v2).
 *
 * This REST API controller handles requests to return product details
 * via "cocart/v2/products" endpoint.
 *
 * @since 3.1.0 Introduced.
 *
 * @extends CoCart_REST_Products_Controller
 */
class CoCart_REST_Products_V2_Controller extends CoCart_REST_Products_Controller {

	/**
	 * The version of this controller's route.
	 *
	 * @var string
	 */
	protected $version = 'v2';

	/**
	 * Get the path of this rest route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return string
	 */
	public function get_path_regex() {
		return '/products';
	} // END get_path_regex()

	/**
	 * Get method arguments for this REST route.
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @return array An array of endpoints.
	 */
	public function get_args() {
		return array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'args'                => $this->get_collection_params(),
				'permission_callback' => '__return_true',
			),
			'allow_batch' => array( 'v1' => true ),
			'schema'      => array( $this, 'get_public_items_schema' ),
		);
	} // END get_args()

	/**
	 * Register routes.
	 *
	 * @deprecated 5.0.0 Routes are registered in the REST API class instead.
	 *
	 * @access public
	 */
	public function register_routes() {
		cocart_deprecated_function( __FUNCTION__, '5.0.0' );

		// Get Products - cocart/v2/products (GET).
		register_rest_route(
			$this->namespace,
			$this->get_path(),
			$this->get_args()
		);
	} // END register_routes()

	/**
	 * Get a collection of products.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 3.2.0 Moved products to its own object and returned also pagination information.
	 *
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_Error|WP_REST_Response The response, or an error.
	 */
	public function get_items( $request ) {
		$query_args    = $this->prepare_objects_query( $request );
		$query_results = $this->get_objects( $query_args );

		if ( is_wp_error( $query_results ) ) {
			return $query_results;
		}

		$objects = array();

		foreach ( $query_results['objects'] as $object ) {
			$data      = $this->prepare_object_for_response( $object, $request );
			$objects[] = $this->prepare_response_for_collection( $data );
		}

		$page        = (int) $query_args['paged'];
		$total_items = $query_results['total'];
		$max_pages   = $query_results['pages'];

		$results = array(
			'products'       => $objects,
			'page'           => $page,
			'total_pages'    => (int) $max_pages,
			'total_products' => $query_results['total'],
		);

		$response = rest_ensure_response( $results );
		$response = ( new CoCart_REST_Utilities_Pagination() )->add_headers( $response, $request, $total_items, $max_pages );

		// Prevent WordPress from filtering the collection wrapper fields.
		// The _fields parameter is already applied to individual products in prepare_object_for_response().
		// We don't want it to filter the collection metadata (page, total_pages, total_products).
		if ( isset( $request['_fields'] ) ) {
			$request->offsetUnset( '_fields' );
		}

		return $response;
	} // END get_items()

	/**
	 * Prepare a single product output for response.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Added Global Unique ID in response.
	 *
	 * @param WC_Product      $product The product object.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return WP_REST_Response
	 */
	public function prepare_object_for_response( $product, $request ) {
		$fields = $this->get_fields_for_response( $request );

		// Check what product type before returning product data.
		if ( $product->get_type() !== 'variation' ) {
			$data = $this->get_product_data( $product, $fields );
		} else {
			$data = $this->get_variation_product_data( $product, $fields );
		}

		// Get global unique ID if function and data exists.
		if ( rest_is_field_included( 'global_unique_id', $fields ) && method_exists( $product, 'get_global_unique_id' ) ) {
			$data['global_unique_id'] = $product->get_global_unique_id( 'view' );
		}

		// Add review data to products if requested.
		if ( rest_is_field_included( 'reviews', $fields ) && $request['show_reviews'] ) {
			$data['reviews'] = $this->get_reviews( $product );
		}

		// Return each variation if the variable product has variations available.
		if ( rest_is_field_included( 'variations', $fields ) && $product->is_type( 'variable' ) && $product->has_child() ) {
			$data['variations'] = $this->get_variations( $product );
		}

		// Add grouped products data.
		if ( rest_is_field_included( 'grouped_products', $fields ) && $product->is_type( 'grouped' ) && $product->has_child() ) {
			$data['grouped_products'] = $product->get_children();
		}

		$data     = $this->add_additional_fields_to_object( $data, $request );
		$data     = $this->filter_response_by_context( $data, 'view' );
		$response = rest_ensure_response( $data );

		// Only prepare links if requested (WordPress 6.1+ optimization).
		if ( rest_is_field_included( '_links', $fields ) || rest_is_field_included( '_embedded', $fields ) ) {
			$response->add_links( $this->prepare_links( $product, $request ) );
		}

		/**
		 * Filter the data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type,
		 * refers to product type being prepared for the response.
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WC_Product       $product  The product object.
		 * @param WP_REST_Request  $request  The request object.
		 */
		return apply_filters( "cocart_prepare_{$this->post_type}_object_v2", $response, $product, $request ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores
	} // END prepare_object_for_response()

	/**
	 * Return the basic of each variation to make it easier
	 * for developers with their UI/UX.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array $variations Returns the variations.
	 */
	public function get_variations( $product ) {
		$variation_ids    = $product->get_children();
		$tax_display_mode = CoCart_Utilities_Product_Helpers::get_tax_display_mode();
		$price_function   = CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode( $tax_display_mode );
		$variations       = array();

		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		foreach ( $variation_ids as $variation_id ) {
			$variation = wc_get_product( $variation_id );

			// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
			if ( ! $variation || ! $variation->exists() || ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) && ! $variation->is_in_stock() ) ) {
				continue;
			}

			// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
			if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $variation_id, $variation ) && ! $variation->variation_is_visible() ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
				continue;
			}

			$expected_attributes = wc_get_product_variation_attributes( $variation_id );
			$featured_image_id   = $variation->get_image_id();
			$attachment_post     = get_post( $featured_image_id );
			$attachments         = array();

			// Get each image size of the attachment.
			foreach ( $attachment_sizes as $size ) {
				if ( is_null( $attachment_post ) ) {
					continue;
				}

				$attachments[ $size ] = current( wp_get_attachment_image_src( $featured_image_id, $size ) );
			}

			$date_on_sale_from = $variation->get_date_on_sale_from( 'view' );
			$date_on_sale_to   = $variation->get_date_on_sale_to( 'view' );

			$variations[] = array(
				'id'             => $variation_id,
				'sku'            => $variation->get_sku( 'view' ),
				'description'    => $variation->get_description( 'view' ),
				'attributes'     => $expected_attributes,
				'featured_image' => $attachments,
				'prices'         => array(
					'price'         => cocart_format_money( $price_function( $variation ) ),
					'regular_price' => cocart_format_money( $price_function( $variation, array( 'price' => $variation->get_regular_price() ) ) ),
					'sale_price'    => $variation->get_sale_price( 'view' ) ? cocart_format_money( $price_function( $variation, array( 'price' => $variation->get_sale_price() ) ) ) : '',
					'on_sale'       => $variation->is_on_sale( 'view' ),
					'date_on_sale'  => array(
						'from'     => ! is_null( $date_on_sale_from ) ? cocart_prepare_date_response( $date_on_sale_from->date( 'Y-m-d\TH:i:s' ), false ) : null,
						'from_gmt' => ! is_null( $date_on_sale_from ) ? cocart_prepare_date_response( $date_on_sale_from->date( 'Y-m-d\TH:i:s' ) ) : null,
						'to'       => ! is_null( $date_on_sale_to ) ? cocart_prepare_date_response( $date_on_sale_to->date( 'Y-m-d\TH:i:s' ), false ) : null,
						'to_gmt'   => ! is_null( $date_on_sale_to ) ? cocart_prepare_date_response( $date_on_sale_to->date( 'Y-m-d\TH:i:s' ) ) : null,
					),
					'currency'      => cocart_get_store_currency(),
				),
				'stock'          => array(
					'is_in_stock'        => $variation->is_in_stock(),
					'stock_quantity'     => $variation->managing_stock() ? $variation->get_stock_quantity( 'view' ) : null,
					'stock_status'       => $variation->get_stock_status( 'view' ),
					'backorders'         => $variation->get_backorders( 'view' ),
					'backorders_allowed' => $variation->backorders_allowed(),
					'backordered'        => $variation->is_on_backorder(),
					'low_stock_amount'   => $variation->get_low_stock_amount( 'view' ),
				),
				'add_to_cart'    => array(
					'is_purchasable'    => $variation->is_purchasable(),
					'purchase_quantity' => array(
						'min_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_minimum_requirement( $variation ),
						'max_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_maximum_allowed( $variation ),
					),
					'rest_url'          => $this->add_to_cart_rest_url( $variation, $variation->get_type() ),
				),
			);
		}

		return $variations;
	} // END get_variations()

	/**
	 * Get product data.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Added $fields parameter for selective field inclusion.
	 *
	 * @param WC_Product $product The product object.
	 * @param array      $fields  Fields to include in the response.
	 *
	 * @return array $data The product details.
	 */
	protected function get_product_data( $product, $fields ) {
		$data = array();

		if ( rest_is_field_included( 'id', $fields ) ) {
			$data['id'] = $product->get_id();
		}

		if ( rest_is_field_included( 'parent_id', $fields ) ) {
			$data['parent_id'] = $product->get_parent_id( 'view' );
		}

		if ( rest_is_field_included( 'name', $fields ) ) {
			$data['name'] = $product->get_name( 'view' );
		}

		if ( rest_is_field_included( 'type', $fields ) ) {
			$data['type'] = $product->get_type();
		}

		if ( rest_is_field_included( 'slug', $fields ) ) {
			$data['slug'] = $product->get_slug( 'view' );
		}

		if ( rest_is_field_included( 'permalink', $fields ) ) {
			$data['permalink'] = $product->get_permalink();
		}

		if ( rest_is_field_included( 'sku', $fields ) ) {
			$data['sku'] = $product->get_sku( 'view' );
		}

		if ( rest_is_field_included( 'global_unique_id', $fields ) ) {
			$data['global_unique_id'] = '';
		}

		if ( rest_is_field_included( 'description', $fields ) ) {
			$data['description'] = $product->get_description( 'view' );
		}

		if ( rest_is_field_included( 'short_description', $fields ) ) {
			$data['short_description'] = $product->get_short_description( 'view' );
		}

		if ( rest_is_field_included( 'dates', $fields ) ) {
			$date_created  = $product->get_date_created( 'view' );
			$date_modified = $product->get_date_modified( 'view' );

			$date_sub = $this->get_nested_fields( 'dates', array( 'created', 'created_gmt', 'modified', 'modified_gmt' ), $fields );

			$data['dates'] = array();

			if ( isset( $date_sub['created'] ) ) {
				$data['dates']['created'] = cocart_prepare_date_response( $date_created->date( 'Y-m-d\TH:i:s' ), false );
			}
			if ( isset( $date_sub['created_gmt'] ) ) {
				$data['dates']['created_gmt'] = cocart_prepare_date_response( $date_created->date( 'Y-m-d\TH:i:s' ) );
			}
			if ( isset( $date_sub['modified'] ) ) {
				$data['dates']['modified'] = cocart_prepare_date_response( $date_modified->date( 'Y-m-d\TH:i:s' ), false );
			}
			if ( isset( $date_sub['modified_gmt'] ) ) {
				$data['dates']['modified_gmt'] = cocart_prepare_date_response( $date_modified->date( 'Y-m-d\TH:i:s' ) );
			}
		}

		if ( rest_is_field_included( 'featured', $fields ) ) {
			$data['featured'] = $product->is_featured();
		}

		if ( rest_is_field_included( 'prices', $fields ) ) {
			$tax_display_mode = CoCart_Utilities_Product_Helpers::get_tax_display_mode();
			$price_function   = CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode( $tax_display_mode );

			if ( $product->is_type( 'variable' ) ) {
				$regular_price = $product->get_variation_regular_price();
				$sale_price    = $product->get_variation_sale_price();
			} else {
				$regular_price = $product->get_regular_price();
				$sale_price    = $product->get_sale_price();
			}

			$prices_sub = $this->get_nested_fields( 'prices', array( 'price', 'regular_price', 'sale_price', 'price_range', 'on_sale', 'date_on_sale', 'currency' ), $fields );

			$data['prices'] = array();

			if ( isset( $prices_sub['price'] ) ) {
				$data['prices']['price'] = cocart_format_money( $price_function( $product ) );
			}
			if ( isset( $prices_sub['regular_price'] ) ) {
				$data['prices']['regular_price'] = cocart_format_money( $price_function( $product, array( 'price' => $regular_price ) ) );
			}
			if ( isset( $prices_sub['sale_price'] ) ) {
				$data['prices']['sale_price'] = $product->get_sale_price( 'view' ) ? cocart_format_money( $price_function( $product, array( 'price' => $sale_price ) ) ) : '';
			}
			if ( isset( $prices_sub['price_range'] ) ) {
				$data['prices']['price_range'] = CoCart_Utilities_Product_Helpers::get_price_range( $product, $tax_display_mode );
			}
			if ( isset( $prices_sub['on_sale'] ) ) {
				$data['prices']['on_sale'] = $product->is_on_sale( 'view' );
			}
			if ( isset( $prices_sub['date_on_sale'] ) ) {
				$date_on_sale_from = $product->get_date_on_sale_from( 'view' );
				$date_on_sale_to   = $product->get_date_on_sale_to( 'view' );

				$data['prices']['date_on_sale'] = array(
					'from'     => ! is_null( $date_on_sale_from ) ? cocart_prepare_date_response( $date_on_sale_from->date( 'Y-m-d\TH:i:s' ), false ) : null,
					'from_gmt' => ! is_null( $date_on_sale_from ) ? cocart_prepare_date_response( $date_on_sale_from->date( 'Y-m-d\TH:i:s' ) ) : null,
					'to'       => ! is_null( $date_on_sale_to ) ? cocart_prepare_date_response( $date_on_sale_to->date( 'Y-m-d\TH:i:s' ), false ) : null,
					'to_gmt'   => ! is_null( $date_on_sale_to ) ? cocart_prepare_date_response( $date_on_sale_to->date( 'Y-m-d\TH:i:s' ) ) : null,
				);
			}
			if ( isset( $prices_sub['currency'] ) ) {
				$data['prices']['currency'] = cocart_get_store_currency();
			}
		}

		if ( rest_is_field_included( 'hidden_conditions', $fields ) ) {
			$hc_sub = $this->get_nested_fields( 'hidden_conditions', array( 'virtual', 'downloadable', 'manage_stock', 'sold_individually', 'reviews_allowed', 'shipping_required' ), $fields );

			$data['hidden_conditions'] = array();

			if ( isset( $hc_sub['virtual'] ) ) {
				$data['hidden_conditions']['virtual'] = $product->is_virtual();
			}
			if ( isset( $hc_sub['downloadable'] ) ) {
				$data['hidden_conditions']['downloadable'] = $product->is_downloadable();
			}
			if ( isset( $hc_sub['manage_stock'] ) ) {
				$data['hidden_conditions']['manage_stock'] = $product->managing_stock();
			}
			if ( isset( $hc_sub['sold_individually'] ) ) {
				$data['hidden_conditions']['sold_individually'] = $product->is_sold_individually();
			}
			if ( isset( $hc_sub['reviews_allowed'] ) ) {
				$data['hidden_conditions']['reviews_allowed'] = $product->get_reviews_allowed( 'view' );
			}
			if ( isset( $hc_sub['shipping_required'] ) ) {
				$data['hidden_conditions']['shipping_required'] = $product->needs_shipping();
			}
		}

		if ( rest_is_field_included( 'average_rating', $fields ) ) {
			$data['average_rating'] = $product->get_average_rating();
		}

		if ( rest_is_field_included( 'review_count', $fields ) ) {
			$data['review_count'] = $product->get_review_count();
		}

		if ( rest_is_field_included( 'rating_count', $fields ) ) {
			$data['rating_count'] = $product->get_rating_count();
		}

		if ( rest_is_field_included( 'rated_out_of', $fields ) ) {
			$rating_count = $product->get_rating_count();
			$average      = $product->get_average_rating();

			$data['rated_out_of'] = html_entity_decode( wp_strip_all_tags( wc_get_rating_html( $average, $rating_count ) ) );
		}

		if ( rest_is_field_included( 'images', $fields ) ) {
			$data['images'] = CoCart_Utilities_Product_Helpers::get_images( $product );
		}

		if ( rest_is_field_included( 'categories', $fields ) ) {
			$data['categories'] = $this->get_taxonomy_terms( $product );
		}

		if ( rest_is_field_included( 'tags', $fields ) ) {
			$data['tags'] = $this->get_taxonomy_terms( $product, 'tag' );
		}

		if ( rest_is_field_included( 'brands', $fields ) ) {
			$data['brands'] = $this->get_taxonomy_terms( $product, 'brand' );
		}

		if ( rest_is_field_included( 'attributes', $fields ) ) {
			$data['attributes'] = $this->get_attributes( $product );
		}

		if ( rest_is_field_included( 'default_attributes', $fields ) ) {
			$data['default_attributes'] = $this->get_default_attributes( $product );
		}

		if ( rest_is_field_included( 'variations', $fields ) ) {
			$data['variations'] = array();
		}

		if ( rest_is_field_included( 'grouped_products', $fields ) ) {
			$data['grouped_products'] = array();
		}

		if ( rest_is_field_included( 'stock', $fields ) ) {
			$stock_sub = $this->get_nested_fields( 'stock', array( 'is_in_stock', 'stock_quantity', 'stock_status', 'backorders', 'backorders_allowed', 'backordered', 'low_stock_amount' ), $fields );

			$data['stock'] = array();

			if ( isset( $stock_sub['is_in_stock'] ) ) {
				$data['stock']['is_in_stock'] = $product->is_in_stock();
			}
			if ( isset( $stock_sub['stock_quantity'] ) ) {
				$data['stock']['stock_quantity'] = $product->get_stock_quantity( 'view' );
			}
			if ( isset( $stock_sub['stock_status'] ) ) {
				$data['stock']['stock_status'] = $product->get_stock_status( 'view' );
			}
			if ( isset( $stock_sub['backorders'] ) ) {
				$data['stock']['backorders'] = $product->get_backorders( 'view' );
			}
			if ( isset( $stock_sub['backorders_allowed'] ) ) {
				$data['stock']['backorders_allowed'] = $product->backorders_allowed();
			}
			if ( isset( $stock_sub['backordered'] ) ) {
				$data['stock']['backordered'] = $product->is_on_backorder();
			}
			if ( isset( $stock_sub['low_stock_amount'] ) ) {
				$data['stock']['low_stock_amount'] = $product->get_low_stock_amount( 'view' );
			}
		}

		if ( rest_is_field_included( 'weight', $fields ) ) {
			$weight_sub = $this->get_nested_fields( 'weight', array( 'value', 'unit' ), $fields );

			$data['weight'] = array();

			if ( isset( $weight_sub['value'] ) ) {
				$data['weight']['value'] = $product->get_weight( 'view' );
			}
			if ( isset( $weight_sub['unit'] ) ) {
				$data['weight']['unit'] = get_option( 'woocommerce_weight_unit' );
			}
		}

		if ( rest_is_field_included( 'dimensions', $fields ) ) {
			$dim_sub = $this->get_nested_fields( 'dimensions', array( 'length', 'width', 'height', 'unit' ), $fields );

			$data['dimensions'] = array();

			if ( isset( $dim_sub['length'] ) ) {
				$data['dimensions']['length'] = $product->get_length( 'view' );
			}
			if ( isset( $dim_sub['width'] ) ) {
				$data['dimensions']['width'] = $product->get_width( 'view' );
			}
			if ( isset( $dim_sub['height'] ) ) {
				$data['dimensions']['height'] = $product->get_height( 'view' );
			}
			if ( isset( $dim_sub['unit'] ) ) {
				$data['dimensions']['unit'] = get_option( 'woocommerce_dimension_unit' );
			}
		}

		if ( rest_is_field_included( 'reviews', $fields ) ) {
			$data['reviews'] = array();
		}

		if ( rest_is_field_included( 'related', $fields ) ) {
			$data['related'] = $this->get_connected_products( $product, 'related' );
		}

		if ( rest_is_field_included( 'upsells', $fields ) ) {
			$data['upsells'] = $this->get_connected_products( $product, 'upsells' );
		}

		if ( rest_is_field_included( 'cross_sells', $fields ) ) {
			$data['cross_sells'] = $this->get_connected_products( $product, 'cross_sells' );
		}

		if ( rest_is_field_included( 'total_sales', $fields ) ) {
			$data['total_sales'] = $product->get_total_sales( 'view' );
		}

		if ( rest_is_field_included( 'external_url', $fields ) ) {
			$data['external_url'] = $product->is_type( 'external' ) ? $product->get_product_url( 'view' ) : '';
		}

		if ( rest_is_field_included( 'button_text', $fields ) ) {
			$data['button_text'] = $product->is_type( 'external' ) ? $product->get_button_text( 'view' ) : '';
		}

		if ( rest_is_field_included( 'add_to_cart', $fields ) ) {
			$type    = $product->get_type();
			$atc_sub = $this->get_nested_fields( 'add_to_cart', array( 'text', 'description', 'has_options', 'is_purchasable', 'purchase_quantity', 'rest_url' ), $fields );

			$data['add_to_cart'] = array();

			if ( isset( $atc_sub['text'] ) ) {
				$data['add_to_cart']['text'] = $product->add_to_cart_text();
			}
			if ( isset( $atc_sub['description'] ) ) {
				$data['add_to_cart']['description'] = $product->add_to_cart_description();
			}
			if ( isset( $atc_sub['has_options'] ) ) {
				$data['add_to_cart']['has_options'] = $product->has_options();
			}
			if ( isset( $atc_sub['is_purchasable'] ) ) {
				$data['add_to_cart']['is_purchasable'] = $product->is_purchasable();
			}
			if ( isset( $atc_sub['purchase_quantity'] ) ) {
				$purchase_quantity = array();

				if ( ! $product->is_type( 'variable' ) && ! $product->is_type( 'external' ) ) {
					$purchase_quantity = array(
						'min_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_minimum_requirement( $product ),
						'max_purchase' => CoCart_Utilities_Product_Helpers::get_quantity_maximum_allowed( $product ),
					);
				}

				$data['add_to_cart']['purchase_quantity'] = $purchase_quantity;
			}
			if ( isset( $atc_sub['rest_url'] ) ) {
				$data['add_to_cart']['rest_url'] = $this->add_to_cart_rest_url( $product, $type );
			}
		}

		if ( rest_is_field_included( 'meta_data', $fields ) ) {
			$data['meta_data'] = CoCart_Utilities_Product_Helpers::get_meta_data( $product );
		}

		return $data;
	} // END get_product_data()

	/**
	 * Get variation product data.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Added $fields parameter for selective field inclusion.
	 *
	 * @param WC_Variation_Product $product The product object.
	 * @param array                $fields  Fields to include in the response.
	 *
	 * @return array $data Variation product details.
	 */
	public function get_variation_product_data( $product, $fields ) {
		// Fields that don't apply to variations — exclude from field checks.
		$variation_excluded = array(
			'type',
			'short_description',
			'average_rating',
			'review_count',
			'rating_count',
			'rated_out_of',
			'reviews',
			'default_attributes',
			'variations',
			'grouped_products',
			'related',
			'upsells',
			'cross_sells',
			'external_url',
			'button_text',
		);

		$variation_fields = array_diff( $fields, $variation_excluded );
		$data             = $this->get_product_data( $product, $variation_fields );

		// Remove sub-fields not applicable to variations.
		if ( isset( $data['hidden_conditions'] ) ) {
			unset( $data['hidden_conditions']['reviews_allowed'] );
		}

		if ( isset( $data['add_to_cart']['has_options'] ) ) {
			unset( $data['add_to_cart']['has_options'] );
		}

		return $data;
	} // END get_variation_product_data()

	/**
	 * Get taxonomy terms.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product  The product object.
	 * @param string     $taxonomy Taxonomy slug.
	 *
	 * @return array $terms Taxonomy terms.
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
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param int   $product_id Product ID.
	 * @param array $attribute  Attribute data.
	 *
	 * @return array $attributes Attribute options.
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
				$slug                = sanitize_title( $attribute );
				$attributes[ $slug ] = rawurldecode( $attribute );
			}
		}

		return $attributes;
	} // END get_attribute_options()

	/**
	 * Get the attributes for a product or product variation.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array $attributes Attributes data.
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

				// Taxonomy-based attributes are prefixed with `attribute_pa_`, otherwise simply `attribute_`.
				$attribute_prefix = 0 === strpos( $attribute_name, 'attribute_pa_' ) ? 'attribute_pa_' : 'attribute_';

				// Determine the attribute option.
				$option = array( sanitize_title( $attribute ) => rawurldecode( $attribute ) );

				if ( 'attribute_pa_' === $attribute_prefix ) {
					// If the attribute is taxonomy-based, fetch the term.
					$option_term = get_term_by( 'slug', $attribute, $name );

					// Set the option accordingly.
					$option = $option_term && ! is_wp_error( $option_term ) ? array( $option_term->slug => $option_term->name ) : $option;
				}

				$attributes[ 'attribute_' . $name ] = array(
					'id'     => 'attribute_pa_' === $attribute_prefix ? wc_attribute_taxonomy_id_by_name( $name ) : 0,
					'name'   => $this->get_attribute_taxonomy_name( $name, $_product ),
					'option' => $option,
				);
			}
		} else {
			foreach ( $product->get_attributes() as $attribute ) {
				$attribute_id = 'attribute_' . str_replace( ' ', '-', strtolower( sanitize_title( $attribute->get_name() ) ) );

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
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 * @param string     $type    Type of products to return.
	 *
	 * @return array $connected_products The product object.
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
				$ids = array_map( 'absint', array_values( wc_get_related_products( $product->get_id(), apply_filters( 'cocart_products_get_related_products_limit', 5 ), apply_filters( 'cocart_products_get_related_products_exclude_ids', array() ) ) ) );
				break;
		}

		$connected_products = array();

		// Proceed if we have product ID's.
		if ( ! empty( $ids ) ) {
			foreach ( $ids as $id ) {
				$_product = wc_get_product( $id );

				// If product exists, fetch product data.
				if ( $_product ) {
					$type = $_product->get_type();

					$connected_products[] = array(
						'id'          => $id,
						'name'        => $_product->get_name( 'view' ),
						'permalink'   => $_product->get_permalink(),
						'price'       => cocart_format_money( $_product->get_price( 'view' ) ),
						'add_to_cart' => array(
							'text'        => $_product->add_to_cart_text(),
							'description' => $_product->add_to_cart_description(),
							'rest_url'    => $this->add_to_cart_rest_url( $_product, $type ),
						),
						'rest_url'    => $this->product_rest_url( $id ),
					);
				}
			}
		}

		return $connected_products;
	} // END get_connected_products()

	/**
	 * Returns the REST URL for a specific product or taxonomy.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Added brand taxonomy support.
	 *
	 * @param int    $id       Product ID or Taxonomy ID.
	 * @param string $taxonomy Taxonomy type.
	 *
	 * @return string
	 */
	public function product_rest_url( $id, $taxonomy = '' ) {
		if ( ! empty( $taxonomy ) ) {
			switch ( $taxonomy ) {
				case 'cat':
					$path = 'products/categories/%d';
					break;
				case 'tag':
					$path = 'products/tags/%d';
					break;
				case 'brand':
					$path = 'products/brands/%d';
					break;
				default:
					$path = 'products/%d';
					break;
			}
		} else {
			$path = 'products/%d';
		}

		return rest_url( $this->build_rest_path( $path, array( $id ) ) );
	} // END product_rest_url()

	/**
	 * Returns an Array of REST URLs for each ID.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param array $ids Product ID's.
	 *
	 * @return array $urls Array of REST URLs.
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
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @param WC_Product $product The product object.
	 * @param string     $type    Product type.
	 *
	 * @return string $rest_url REST URL for adding product to the cart.
	 */
	public function add_to_cart_rest_url( $product, $type ) {
		$id = $product->get_id();

		$rest_url = rest_url( $this->build_rest_path( 'cart/add-item' ) );
		$rest_url = add_query_arg( 'id', $id, $rest_url );
		$rest_url = add_query_arg( 'quantity', CoCart_Utilities_Product_Helpers::get_quantity_minimum_requirement( $product ), $rest_url );

		switch ( $type ) {
			case 'variation':
			case 'subscription_variation':
				foreach ( $product->get_variation_attributes() as $attribute_name => $attribute ) {
					$name = str_replace( 'attribute_', '', $attribute_name );

					if ( ! $attribute ) {
						continue;
					}

					$rest_url = add_query_arg(
						array(
							"variation[attribute_{$name}]" => $attribute,
						),
						$rest_url
					);
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
				/**
				 * Filters the REST URL shortcut for adding the product to cart.
				 *
				 * @since 3.1.0 Introduced.
				 *
				 * @param string     $rest_url REST URL for adding product to the cart.
				 * @param WC_Product $product  The product object.
				 * @param string     $type     Product type.
				 * @param int        $id       Product ID.
				 */
				$rest_url = apply_filters( 'cocart_products_add_to_cart_rest_url', $rest_url, $product, $type, $id );
				break;
		}

		return $rest_url;
	} // END add_to_cart_rest_url()

	// ** Deprecated functions below this line. **//

	/**
	 * Get the images for a product or product variation.
	 *
	 * @access protected
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Product_Helpers::get_images()
	 *
	 * @param WC_Product|WC_Product_Variation $product The product object.
	 *
	 * @return array $images
	 */
	protected function get_images( $product ) {
		cocart_deprecated_function( 'CoCart_REST_Products_V2_Controller::get_images', '4.2.0', 'CoCart_Utilities_Product_Helpers::get_images' );

		return CoCart_Utilities_Product_Helpers::get_images( $product );
	} // END get_images()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.3.3 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Product_Helpers::get_tax_display_mode()
	 *
	 * @param string $tax_display_mode Provided tax display mode.
	 *
	 * @return string Valid tax display mode.
	 */
	protected function get_tax_display_mode( $tax_display_mode = '' ) {
		cocart_deprecated_function( 'CoCart_REST_Products_V2_Controller::get_tax_display_mode', '4.3.3', 'CoCart_Utilities_Product_Helpers::get_tax_display_mode' );

		return in_array( $tax_display_mode, array( 'incl', 'excl' ), true ) ? $tax_display_mode : get_option( 'woocommerce_tax_display_shop' );
	} // END get_tax_display_mode()

	/**
	 * WooCommerce can return prices including or excluding tax.
	 * Choose the correct method based on tax display mode.
	 *
	 * @access protected
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode()
	 *
	 * @param string $tax_display_mode If returned prices are incl or excl of tax.
	 *
	 * @return string Function name.
	 */
	protected function get_price_from_tax_display_mode( $tax_display_mode ) {
		cocart_deprecated_function( 'CoCart_REST_Products_V2_Controller::get_price_from_tax_display_mode', '4.2.0', 'CoCart_Utilities_Product_Helpers::get_price_from_tax_display_mode' );

		return 'incl' === $tax_display_mode ? 'wc_get_price_including_tax' : 'wc_get_price_excluding_tax';
	} // END get_price_from_tax_display_mode()

	/**
	 * Returns the price range for variable or grouped product.
	 *
	 * @access public
	 *
	 * @deprecated 4.2.0 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_REST_Products_V2_Controller::get_price_range()
	 *
	 * @param WC_Product $product          The product object.
	 * @param string     $tax_display_mode If returned prices are incl or excl of tax.
	 *
	 * @return array
	 */
	public function get_price_range( $product, $tax_display_mode = '' ) {
		cocart_deprecated_function( 'CoCart_REST_Products_V2_Controller::get_price_range', '4.2.0', 'CoCart_Utilities_Product_Helpers::get_price_range' );

		return CoCart_Utilities_Product_Helpers::get_price_range( $product, $tax_display_mode );
	} // END get_price_range()

	/**
	 * Gets the product meta data.
	 *
	 * @access public
	 *
	 * @since 3.11.0 Introduced.
	 *
	 * @deprecated 4.3.3 Replaced with the same function in the utilities class.
	 *
	 * @see CoCart_Utilities_Product_Helpers::get_meta_data()
	 *
	 * @param WC_Product $product The product object.
	 *
	 * @return array
	 */
	public function get_meta_data( $product ) {
		cocart_deprecated_function( 'CoCart_REST_Products_V2_Controller::get_meta_data', '4.3.3', 'CoCart_Utilities_Product_Helpers::get_meta_data' );

		return CoCart_Utilities_Product_Helpers::get_meta_data( $product );
	} // END get_meta_data()

	/**
	 * Retrieves the item’s schema, conforming to JSON Schema.
	 *
	 * @access public
	 *
	 * @since 3.1.0 Introduced.
	 * @since 5.0.0 Added Global Unique ID.
	 *
	 * @return array Product schema data.
	 */
	public function get_item_schema() {
		$weight_unit    = get_option( 'woocommerce_weight_unit' );
		$dimension_unit = get_option( 'woocommerce_dimension_unit' );

		$schema = array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => $this->post_type,
			'type'    => 'object',
		);

		$schema['properties'] = array(
			'id'                 => array(
				'description' => __( 'Unique identifier for the product.', 'cocart-core' ),
				'type'        => 'integer',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'parent_id'          => array(
				'description' => __( 'Product parent ID.', 'cocart-core' ),
				'type'        => 'integer',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'name'               => array(
				'description' => __( 'Product name.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'type'               => array(
				'description' => __( 'Product type. Default values are `simple | variable | variation` but other types maybe available with other product type extensions.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'default'     => 'simple',
				'enum'        => array_merge( array_keys( wc_get_product_types() ), array( 'variation' ) ),
				'readonly'    => true,
			),
			'slug'               => array(
				'description' => __( 'Product slug.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'permalink'          => array(
				'description' => __( 'Product permalink.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'sku'                => array(
				'description' => __( 'Unique identifier for the product.', 'cocart-core' ) . ' (SKU)',
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'global_unique_id'   => array(
				'description' => __( 'GTIN, UPC, EAN or ISBN.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
			),
			'description'        => array(
				'description' => __( 'Product description.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'short_description'  => array(
				'description' => __( 'Product short description.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'dates'              => array(
				'description' => __( 'Product dates.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'created'      => array(
						'description' => __( "The date the product was created, in the site's timezone.", 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'created_gmt'  => array(
						'description' => __( 'The date the product was created, as GMT.', 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'modified'     => array(
						'description' => __( "The date the product was last modified, in the site's timezone.", 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'modified_gmt' => array(
						'description' => __( 'The date the product was last modified, as GMT.', 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'featured'           => array(
				'description' => __( 'Featured product.', 'cocart-core' ),
				'type'        => 'boolean',
				'context'     => array( 'view' ),
				'default'     => false,
				'readonly'    => true,
			),
			'prices'             => array(
				'description' => __( 'Product prices.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'price'         => array(
						'description' => __( 'Product price (currently).', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'regular_price' => array(
						'description' => __( 'Product regular price.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'sale_price'    => array(
						'description' => __( 'Product sale price.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'price_range'   => array(
						'description' => __( 'Product price range.', 'cocart-core' ),
						'type'        => 'object',
						'context'     => array( 'view' ),
						'properties'  => array(
							'from' => array(
								'description' => __( 'Minimum product price range.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'to'   => array(
								'description' => __( 'Maximum product price range.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
						'readonly'    => true,
					),
					'on_sale'       => array(
						'description' => __( 'Shows if the product is on sale.', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'date_on_sale'  => array(
						'description' => __( 'Product dates for on sale.', 'cocart-core' ),
						'type'        => 'object',
						'context'     => array( 'view' ),
						'properties'  => array(
							'from'     => array(
								'description' => __( "Start date of sale price, in the site's timezone.", 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'from_gmt' => array(
								'description' => __( 'Start date of sale price, as GMT.', 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'to'       => array(
								'description' => __( "End date of sale price, in the site's timezone.", 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'to_gmt'   => array(
								'description' => __( 'End date of sale price, as GMT.', 'cocart-core' ),
								'type'        => 'date-time',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
						'readonly'    => true,
					),
					'currency'      => array(
						'description' => __( 'Product currency.', 'cocart-core' ),
						'type'        => 'object',
						'context'     => array( 'view' ),
						'properties'  => array(
							'currency_code'               => array(
								'description' => __( 'Currency code.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_symbol'             => array(
								'description' => __( 'Currency symbol.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_minor_unit'         => array(
								'description' => __( 'Currency minor unit.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_decimal_separator'  => array(
								'description' => __( 'Currency decimal separator.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_thousand_separator' => array(
								'description' => __( 'Currency thousand separator.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_prefix'             => array(
								'description' => __( 'Currency prefix.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
							'currency_suffix'             => array(
								'description' => __( 'Currency suffix.', 'cocart-core' ),
								'type'        => 'string',
								'context'     => array( 'view' ),
								'readonly'    => true,
							),
						),
						'readonly'    => true,
					),
				),
			),
			'hidden_conditions'  => array(
				'description' => __( 'Various hidden conditions.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'virtual'           => array(
						'description' => __( 'Is the product virtual?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'downloadable'      => array(
						'description' => __( 'Is the product downloadable?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'manage_stock'      => array(
						'description' => __( 'Is stock management at product level?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'sold_individually' => array(
						'description' => __( 'Are we limiting to just one of item to be bought in a single order?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'reviews_allowed'   => array(
						'description' => __( 'Are reviews allowed for this product?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => true,
						'readonly'    => true,
					),
					'shipping_required' => array(
						'description' => __( 'Does this product require shipping?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'average_rating'     => array(
				'description' => __( 'Reviews average rating.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'review_count'       => array(
				'description' => __( 'Amount of reviews that the product has.', 'cocart-core' ),
				'type'        => 'integer',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'rating_count'       => array(
				'description' => __( 'Rating count for the reviews in total.', 'cocart-core' ),
				'type'        => 'integer',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'rated_out_of'       => array(
				'description' => __( 'Reviews rated out of 5 on average.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'images'             => array(
				'description' => __( 'List of product images.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'Image ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'src'      => array(
							'description' => __( 'Image URL source for each attachment size registered.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(),
							'readonly'    => true,
						),
						'name'     => array(
							'description' => __( 'Image name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'alt'      => array(
							'description' => __( 'Image alternative text.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'position' => array(
							'description' => __( 'Image position.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'featured' => array(
							'description' => __( 'Image set featured?', 'cocart-core' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'default'     => false,
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'categories'         => array(
				'description' => __( 'List of product categories.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'Category ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'     => array(
							'description' => __( 'Category name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'slug'     => array(
							'description' => __( 'Category slug.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'rest_url' => array(
							'description' => __( 'The REST URL for viewing this product category.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'format'      => 'uri',
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'tags'               => array(
				'description' => __( 'List of product tags.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'Tag ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'     => array(
							'description' => __( 'Tag name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'slug'     => array(
							'description' => __( 'Tag slug.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'rest_url' => array(
							'description' => __( 'The REST URL for viewing this product tag.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'format'      => 'uri',
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'brands'             => array(
				'description' => __( 'List of brands, if applicable.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'       => array(
							'description' => __( 'Brand ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'     => array(
							'description' => __( 'Brand name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'slug'     => array(
							'description' => __( 'Brand slug.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'rest_url' => array(
							'description' => __( 'The REST URL for viewing this product brand.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'format'      => 'uri',
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'attributes'         => array(
				'description' => __( 'List of attributes.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'                   => array(
							'description' => __( 'Attribute ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'                 => array(
							'description' => __( 'Attribute name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'position'             => array(
							'description' => __( 'Attribute position.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'is_attribute_visible' => array(
							'description' => __( "Is the attribute visible on the \"Additional information\" tab in the product's page.", 'cocart-core' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'default'     => false,
							'readonly'    => true,
						),
						'used_for_variation'   => array(
							'description' => __( 'Can the attribute be used as a variation?', 'cocart-core' ),
							'type'        => 'boolean',
							'context'     => array( 'view' ),
							'default'     => false,
							'readonly'    => true,
						),
						'options'              => array(
							'description' => __( 'List of available term names of the attribute.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'default_attributes' => array(
				'description' => __( 'Defaults variation attributes.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'     => array(
							'description' => __( 'Attribute ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'name'   => array(
							'description' => __( 'Attribute name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'option' => array(
							'description' => __( 'Selected attribute term name.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
			'variations'         => array(
				'description' => __( 'List of all variations and data.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'             => array(
							'description' => __( 'Unique identifier for the variation product.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'sku'            => array(
							'description' => __( 'Unique identifier for the variation product.', 'cocart-core' ) . ' (SKU)',
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'description'    => array(
							'description' => __( 'Product description.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'attributes'     => array(
							'description' => __( 'Product attributes.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'featured_image' => array(
							'description' => __( 'Variation product featured image.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(),
							'readonly'    => true,
						),
						'prices'         => array(
							'description' => __( 'Product prices.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'price'         => array(
									'description' => __( 'Product price (currently).', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'regular_price' => array(
									'description' => __( 'Product regular price.', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'sale_price'    => array(
									'description' => __( 'Product sale price.', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'on_sale'       => array(
									'description' => __( 'Shows if the product is on sale.', 'cocart-core' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'date_on_sale'  => array(
									'description' => __( 'Product dates for on sale.', 'cocart-core' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'properties'  => array(
										'from'     => array(
											'description' => __( "Start date of sale price, in the site's timezone.", 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'from_gmt' => array(
											'description' => __( 'Start date of sale price, as GMT.', 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'to'       => array(
											'description' => __( "End date of sale price, in the site's timezone.", 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'to_gmt'   => array(
											'description' => __( 'End date of sale price, as GMT.', 'cocart-core' ),
											'type'        => 'date-time',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
									'readonly'    => true,
								),
								'currency'      => array(
									'description' => __( 'Product currency.', 'cocart-core' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'properties'  => array(
										'currency_code'   => array(
											'description' => __( 'Currency code.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_symbol' => array(
											'description' => __( 'Currency symbol.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_minor_unit' => array(
											'description' => __( 'Currency minor unit.', 'cocart-core' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_decimal_separator' => array(
											'description' => __( 'Currency decimal separator.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_thousand_separator' => array(
											'description' => __( 'Currency thousand separator.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_prefix' => array(
											'description' => __( 'Currency prefix.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
										'currency_suffix' => array(
											'description' => __( 'Currency suffix.', 'cocart-core' ),
											'type'        => 'string',
											'context'     => array( 'view' ),
											'readonly'    => true,
										),
									),
									'readonly'    => true,
								),
							),
							'readonly'    => true,
						),
						'stock'          => array(
							'description' => __( 'Product stock details.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'is_in_stock'        => array(
									'description' => __( 'Determines if product is listed as "in stock" or "out of stock".', 'cocart-core' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'default'     => true,
									'readonly'    => true,
								),
								'stock_quantity'     => array(
									'description' => __( 'Stock quantity. Returns "null" if not set.', 'cocart-core' ),
									'type'        => 'integer',
									'context'     => array( 'view' ),
									'readonly'    => true,
								),
								'stock_status'       => array(
									'description' => __( 'Stock status.', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'default'     => 'instock',
									'enum'        => wc_get_product_stock_status_options(),
									'readonly'    => true,
								),
								'backorders'         => array(
									'description' => __( 'If managing stock, this tells us if backorders are allowed.', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'default'     => 'no',
									'enum'        => wc_get_product_backorder_options(),
									'readonly'    => true,
								),
								'backorders_allowed' => array(
									'description' => __( 'Are backorders allowed?', 'cocart-core' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'default'     => false,
									'readonly'    => true,
								),
								'backordered'        => array(
									'description' => __( 'Do we show if the product is on backorder?', 'cocart-core' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'default'     => false,
									'readonly'    => true,
								),
							),
						),
						'add_to_cart'    => array(
							'description' => __( 'Add to Cart button.', 'cocart-core' ),
							'type'        => 'object',
							'context'     => array( 'view' ),
							'properties'  => array(
								'is_purchasable'    => array(
									'description' => __( 'Is product purchasable?', 'cocart-core' ),
									'type'        => 'boolean',
									'context'     => array( 'view' ),
									'default'     => true,
									'readonly'    => true,
								),
								'purchase_quantity' => array(
									'description' => __( 'Purchase limits depending on stock.', 'cocart-core' ),
									'type'        => 'object',
									'context'     => array( 'view' ),
									'properties'  => array(
										'min_purchase' => array(
											'description' => __( 'Minimum purchase quantity allowed for product.', 'cocart-core' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'default'     => 1,
											'readonly'    => true,
										),
										'max_purchase' => array(
											'description' => __( 'Maximum purchase quantity allowed based on stock (if managed).', 'cocart-core' ),
											'type'        => 'integer',
											'context'     => array( 'view' ),
											'default'     => -1,
											'readonly'    => true,
										),
									),
									'readonly'    => true,
								),
								'rest_url'          => array(
									'description' => __( 'The REST URL for adding the product to cart.', 'cocart-core' ),
									'type'        => 'string',
									'context'     => array( 'view' ),
									'format'      => 'uri',
									'readonly'    => true,
								),
							),
							'readonly'    => true,
						),
					),
					'readonly'   => true,
				),
			),
			'grouped_products'   => array(
				'description' => __( 'List of grouped products ID.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type' => 'integer',
				),
				'readonly'    => true,
			),
			'stock'              => array(
				'description' => __( 'Product stock details.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'is_in_stock'        => array(
						'description' => __( 'Determines if product is listed as "in stock" or "out of stock".', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => true,
						'readonly'    => true,
					),
					'stock_quantity'     => array(
						'description' => __( 'Stock quantity. Returns "null" if not set.', 'cocart-core' ),
						'type'        => 'integer',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'stock_status'       => array(
						'description' => __( 'Stock status.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'default'     => 'instock',
						'enum'        => wc_get_product_stock_status_options(),
						'readonly'    => true,
					),
					'backorders'         => array(
						'description' => __( 'If managing stock, this tells us if backorders are allowed.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'default'     => 'no',
						'enum'        => wc_get_product_backorder_options(),
						'readonly'    => true,
					),
					'backorders_allowed' => array(
						'description' => __( 'Are backorders allowed?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'backordered'        => array(
						'description' => __( 'Do we show if the product is on backorder?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
				),
			),
			'weight'             => array(
				'description' => sprintf(
					/* translators: %s: weight unit */
					__( 'Product weight (%s).', 'cocart-core' ),
					$weight_unit
				),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'value'  => array(
						'description' => __( 'Product weight value.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'weight' => array(
						'description' => __( 'Product weight unit.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'default'     => $weight_unit,
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'dimensions'         => array(
				'description' => __( 'Product dimensions.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'length' => array(
						'description' => sprintf(
							/* translators: %s: dimension unit */
							__( 'Product length (%s).', 'cocart-core' ),
							$dimension_unit
						),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'width'  => array(
						'description' => sprintf(
							/* translators: %s: dimension unit */
							__( 'Product width (%s).', 'cocart-core' ),
							$dimension_unit
						),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'height' => array(
						'description' => sprintf(
							/* translators: %s: dimension unit */
							__( 'Product height (%s).', 'cocart-core' ),
							$dimension_unit
						),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'unit'   => array(
						'description' => __( 'Product dimension unit.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'default'     => $dimension_unit,
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'reviews'            => array(
				'description' => __( 'Lists product reviews, when requested.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'review_id'       => array(
						'description' => __( 'Review ID.', 'cocart-core' ),
						'type'        => 'integer',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'author_name'     => array(
						'description' => __( 'Author name.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'author_url'      => array(
						'description' => __( 'Author URL.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'review_comment'  => array(
						'description' => __( 'Review comment.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'review_date'     => array(
						'description' => __( "Review date, in the site's timezone.", 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'review_date_gmt' => array(
						'description' => __( 'Review date, as GMT.', 'cocart-core' ),
						'type'        => 'date-time',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'rating'          => array(
						'description' => __( 'Rating number.', 'cocart-core' ),
						'type'        => 'integer',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'verified'        => array(
						'description' => __( 'Shows if the product review is verified.', 'cocart-core' ),
						'type'        => 'integer',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'rating_html'        => array(
				'description' => __( 'Returns the rating of the product in html.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'related'            => array(
				'description' => __( 'List of related products IDs.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type' => 'integer',
				),
				'readonly'    => true,
			),
			'upsells'            => array(
				'description' => __( 'List of up-sell products IDs.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type' => 'integer',
				),
				'readonly'    => true,
			),
			'cross_sells'        => array(
				'description' => __( 'List of cross-sell products IDs.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type' => 'integer',
				),
				'readonly'    => true,
			),
			'total_sales'        => array(
				'description' => __( 'Amount of product sales.', 'cocart-core' ),
				'type'        => 'integer',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'external_url'       => array(
				'description' => __( 'Product external URL. Only for external products.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'format'      => 'uri',
				'readonly'    => true,
			),
			'button_text'        => array(
				'description' => __( 'Product external button text. Only for external products.', 'cocart-core' ),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
			'add_to_cart'        => array(
				'description' => __( 'Add to Cart button.', 'cocart-core' ),
				'type'        => 'object',
				'context'     => array( 'view' ),
				'properties'  => array(
					'text'              => array(
						'description' => __( 'Add to Cart Text', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'default'     => __( 'Add to Cart', 'cocart-core' ),
						'readonly'    => true,
					),
					'description'       => array(
						'description' => __( 'Description', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'readonly'    => true,
					),
					'has_options'       => array(
						'description' => __( 'Determines whether or not the product has additional options that need selecting before adding to cart.', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => false,
						'readonly'    => true,
					),
					'is_purchasable'    => array(
						'description' => __( 'Is product purchasable?', 'cocart-core' ),
						'type'        => 'boolean',
						'context'     => array( 'view' ),
						'default'     => true,
						'readonly'    => true,
					),
					'purchase_quantity' => array(
						'description' => __( 'Purchase limits depending on stock.', 'cocart-core' ),
						'type'        => 'object',
						'context'     => array( 'view' ),
						'properties'  => array(
							'min_purchase' => array(
								'description' => __( 'Minimum purchase quantity allowed for product.', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'default'     => 1,
								'readonly'    => true,
							),
							'max_purchase' => array(
								'description' => __( 'Maximum purchase quantity allowed based on stock (if managed).', 'cocart-core' ),
								'type'        => 'integer',
								'context'     => array( 'view' ),
								'default'     => -1,
								'readonly'    => true,
							),
						),
						'readonly'    => true,
					),
					'rest_url'          => array(
						'description' => __( 'The REST URL for adding the product to cart.', 'cocart-core' ),
						'type'        => 'string',
						'context'     => array( 'view' ),
						'format'      => 'uri',
						'readonly'    => true,
					),
				),
				'readonly'    => true,
			),
			'meta_data'          => array(
				'description' => __( 'Product meta data.', 'cocart-core' ),
				'type'        => 'array',
				'context'     => array( 'view' ),
				'items'       => array(
					'type'       => 'object',
					'properties' => array(
						'id'    => array(
							'description' => __( 'Meta ID.', 'cocart-core' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'key'   => array(
							'description' => __( 'Meta key.', 'cocart-core' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
						'value' => array(
							'description' => __( 'Meta value.', 'cocart-core' ),
							'type'        => 'mixed',
							'context'     => array( 'view' ),
							'readonly'    => true,
						),
					),
				),
				'readonly'    => true,
			),
		);

		// Fetch each image size.
		$attachment_sizes = CoCart_Utilities_Product_Helpers::get_product_image_sizes();

		foreach ( $attachment_sizes as $size ) {
			// Generate the product image URL properties for each attachment size.
			$schema['properties']['images']['items']['properties']['src']['properties'][ $size ] = array(
				'description' => sprintf(
					/* translators: %s: Product image URL */
					__( 'Product image URL for "%s".', 'cocart-core' ),
					$size
				),
				'type'        => 'string',
				'context'     => array( 'view' ),
				'format'      => 'uri',
				'readonly'    => true,
			);

			// Generate the variation product featured image URL properties for each attachment size.
			if ( isset( $schema['properties']['variations']['items']['properties']['featured_image']['properties'] ) ) {
				$schema['properties']['variations']['items']['properties']['featured_image']['properties'][ $size ] = array(
					'description' => sprintf(
						/* translators: %s: Product image URL */
						__( 'Product image URL for "%s".', 'cocart-core' ),
						$size
					),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'format'      => 'uri',
					'readonly'    => true,
				);
			}
		}

		return $this->add_additional_fields_schema( $schema );
	} // END get_item_schema()

	/**
	 * Retrieves the item's schema for display / public consumption purposes
	 * for the product archive.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array Products archive schema data.
	 */
	public function get_public_items_schema() {
		if ( $this->schema ) {
			return $this->schema;
		}

		$product_schema = $this->get_item_schema();

		$this->schema = array(
			'$schema'    => 'http://json-schema.org/draft-04/schema#',
			'title'      => 'cocart_products_archive',
			'type'       => 'object',
			'properties' => array(
				'products'       => array(
					'description' => __( 'Returned products based on result criteria.', 'cocart-core' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'properties'  => $product_schema['properties'],
				),
				'page'           => array(
					'description' => __( 'Current page of pagination.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'total_pages'    => array(
					'description' => __( 'Total number of pages based on result criteria.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'total_products' => array(
					'description' => __( 'Total of available products in store.', 'cocart-core' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $this->schema;
	} // END get_public_items_schema()
} // END class
