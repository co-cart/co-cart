<?php
/**
 * Schema: CoCart\Schemas\v2\CartSchema.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Schemas
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Schemas\v2;

use CoCart\Schemas\AbstractSchema;

class CartSchema extends AbstractSchema {

	/**
	 * The schema item name.
	 *
	 * @var string
	 */
	protected $title = 'cocart_cart';

	/**
	 * Schema for cart properties.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_properties() {
		return array(
			'cart_hash'      => array(
				'description' => __( 'A unique hash key of the carts contents.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'cart_key'       => array(
				'description' => __( 'A cart key identifying the cart in session.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency'       => array(
				'description' => __( 'Store currency information.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $this->force_schema_readonly( $this->get_currency_properties() ),
			),
			'customer'       => array(
				'description' => __( 'Customer information.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $this->force_schema_readonly( $this->get_customer_properties() ),
			),
			'items'          => array(
				'description' => __( 'List of cart items.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => $this->force_schema_readonly( $this->get_item_properties() ),
				),
			),
			'item_count'     => array(
				'description' => __( 'Number of items in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'items_weight'   => array(
				'description' => __( 'Total weight (in grams) of all items in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'coupons'        => array(
				'description' => __( 'List of applied coupons to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => $this->force_schema_readonly( $this->get_coupon_properties() ),
				),
			),
			'needs_payment'  => array(
				'description' => __( 'True if the cart needs payment. False for carts with only free products and no shipping costs.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'needs_shipping' => array(
				'description' => __( 'True if the cart needs shipping and requires the showing of shipping costs.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'shipping'       => array(
				'description' => __( 'List of available shipping rates for the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $this->force_schema_readonly( $this->get_shipping_properties() ),
			),
			'fees'           => array(
				'description' => __( 'Cart fees.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					array(
						'name' => array(
							'description' => __( 'The fee name.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'fee'  => array(
							'description' => __( 'The fee value.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
			'taxes'          => array(
				'description' => __( 'Lines of taxes applied to items and shipping.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					array(
						'description' => __( 'Tax information.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'properties'  => array(
							'name'  => array(
								'description' => __( 'The name of the tax.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'price' => array(
								'description' => __( 'The amount of tax charged.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
						),
					),
				),
			),
			'totals'         => array(
				'description' => __( 'Cart total amounts.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => $this->force_schema_readonly( $this->get_totals_properties() ),
			),
			'removed_items'  => array(
				'description' => __( 'Items that have been removed from the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => $this->force_schema_readonly( $this->get_item_properties() ),
				),
			),
			'cross_sells'    => array(
				'description' => __( 'Items you may be interested in adding to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties' => $this->force_schema_readonly( $this->get_cross_sells_properties() ),
				),
			),
			'notices'        => array(
				'description' => __( 'Cart notices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'success' => array(
						'description' => __( 'Notices for successful actions.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'array',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'items'       => array(
							'type' => 'string',
						),
					),
					'info'    => array(
						'description' => __( 'Notices for informational actions.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'array',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'items'       => array(
							'type' => 'string',
						),
					),
					'error'   => array(
						'description' => __( 'Notices for error actions.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'array',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'items'       => array(
							'type' => 'string',
						),
					),
				),
			),
		);
	} // END get_properties()

	/**
	 * Schema for a single coupon.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_coupon_properties() {
		return array(
			'coupon'      => array(
				'description' => __( 'Coupon code.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'label'       => array(
				'description' => __( 'Coupon label.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'saving'      => array(
				'description' => __( 'Amount discounted from the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'saving_html' => array(
				'description' => __( 'Amount discounted from the cart (HTML formatted).', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	} // END get_coupon_properties()

	/**
	 * Schema for cart currency properties.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_currency_properties() {
		return array(
			'currency_code'               => array(
				'description' => __( 'Currency code (in ISO format) for returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_symbol'             => array(
				'description' => __( 'Currency symbol for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_minor_unit'         => array(
				'description' => __( 'Currency minor unit (number of digits after the decimal separator) for returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_decimal_separator'  => array(
				'description' => __( 'The decimal separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_thousand_separator' => array(
				'description' => __( 'The thousand separator for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_prefix'             => array(
				'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'currency_suffix'             => array(
				'description' => __( 'The price prefix for the currency which can be used to format returned prices.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	} // END get_currency_properties()

	/**
	 * Schema for cart customer properties.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_customer_properties() {
		return array( array_merge( $this->get_customer_billing_properties(), $this->get_customer_shipping_properties() ) );
	}

	/**
	 * Schema for cart customer billing properties.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_customer_billing_properties() {
		return array(
			'billing_address'  => array(
				'description' => __( 'Customers billing address.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'billing_first_name' => array(
						'description' => __( 'Customers billing first name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_last_name'  => array(
						'description' => __( 'Customers billing last name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_company'    => array(
						'description' => __( 'Customers billing company name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_country'    => array(
						'description' => __( 'Customers billing country.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_address_1'  => array(
						'description' => __( 'Customers billing address line one.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_address_2'  => array(
						'description' => __( 'Customers billing address line two.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_city'       => array(
						'description' => __( 'Customers billing address city.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_state'      => array(
						'description' => __( 'Customers billing state.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_postcode'   => array(
						'description' => __( 'Customers billing postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_phone'      => array(
						'description' => __( 'Customers billing phone.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'billing_email'      => array(
						'description' => __( 'Customers billing email address.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			)
		);
	} // END get_customer_billing_properties()

	/**
	 * Schema for cart customer shipping properties.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_customer_shipping_properties() {
		return array(
			'shipping_address' => array(
				'description' => __( 'Customers shipping address.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'shipping_first_name' => array(
						'description' => __( 'Customers shipping first name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_last_name'  => array(
						'description' => __( 'Customers shipping last name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_company'    => array(
						'description' => __( 'Customers shipping company name.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_country'    => array(
						'description' => __( 'Customers shipping country.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_address_1'  => array(
						'description' => __( 'Customers shipping address line one.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_address_2'  => array(
						'description' => __( 'Customers shipping address line two.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_city'       => array(
						'description' => __( 'Customers shipping address city.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_state'      => array(
						'description' => __( 'Customers shipping state.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'shipping_postcode'   => array(
						'description' => __( 'Customers shipping postcode or zip code.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
		);
	} // END get_customer_shipping_properties()

	/**
	 * Schema for a single cart item.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_item_properties() {
		return array(
			'item_key'       => array(
				'description' => __( 'Unique ID of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'id'             => array(
				'description' => __( 'Product ID or Variation ID of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'name'           => array(
				'description' => __( 'The name of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'          => array(
				'description' => __( 'The title of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'price'          => array(
				'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'float',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'quantity'       => array(
				'description' => __( 'The quantity of the item in the cart and minimum and maximum purchase capability.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'value'        => array(
						'description' => __( 'The quantity of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'min_purchase' => array(
						'description' => __( 'The minimum purchase amount required.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'max_purchase' => array(
						'description' => __( 'The maximum purchase amount allowed. If -1 the item has an unlimited purchase amount.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'totals'         => array(
				'description' => __( 'The totals of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'subtotal'     => array(
						'description' => __( 'The subtotal of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'subtotal_tax' => array(
						'description' => __( 'The subtotal tax of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'total'        => array(
						'description' => __( 'The total of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'total_tax'    => array(
						'description' => __( 'The total tax of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
			'slug'           => array(
				'description' => __( 'The product slug of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'meta'           => array(
				'description' => __( 'The meta data of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'product_type' => array(
						'description' => __( 'The product type of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'sku'          => array(
						'description' => __( 'The SKU of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'dimensions'   => array(
						'description' => __( 'The dimensions of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'properties'  => array(
							'length' => array(
								'description' => __( 'The length of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'width'  => array(
								'description' => __( 'The width of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'height' => array(
								'description' => __( 'The height of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
							'unit'   => array(
								'description' => __( 'The unit measurement of the item.', 'cart-rest-api-for-woocommerce' ),
								'type'        => 'string',
								'context'     => array( 'view', 'edit' ),
								'readonly'    => true,
							),
						),
					),
					'weight'       => array(
						'description' => __( 'The weight of the item.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'float',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
					'variation'    => array(
						'description' => __( 'The variation attributes of the item (if item is a variation of a variable product).', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'object',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
						'properties'  => array(),
					),
				),
			),
			'backorders'     => array(
				'description' => __( 'The price of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'cart_item_data' => array(
				'description' => __( 'Custom item data applied to the item (if any).', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(),
			),
			'featured_image' => array(
				'description' => __( 'The featured image of the item.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'extensions'     => array(
				'description' => __( 'Used by plugin extensions that display additional information.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(),
			),
		);
	} // END get_item_properties()

	/**
	 * Schema for cart shipping properties.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @return array
	 */
	public function get_shipping_properties() {
		return array(
			'total_packages'          => array(
				'description' => __( 'Number of shipping packages available calculated on the shipping address.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'show_package_details'    => array(
				'description' => __( 'True if the cart meets the criteria for showing items in the cart assigned to package.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'has_calculated_shipping' => array(
				'description' => __( 'True if the cart meets the criteria for showing shipping costs, and rates have been calculated and included in the totals.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'packages'                => array(
				'description' => __( 'Packages returned after calculating shipping.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'items'       => array(
					'type'       => 'object',
					'properties'  => array(
						'package_name'    => array(
							'description' => __( 'Name of the package.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'rates'           => array(
							'description' => __( 'List of shipping rates.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'array',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
							'items'       => array(
								'type'       => 'object',
								'properties' => $this->force_schema_readonly( $this->get_rate_properties() ),
							),
						),
						'package_details' => array(
							'description' => __( 'Package details (if any).', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'index'           => array(
							'description' => __( 'Package index.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'integer',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'chosen_method'   => array(
							'description' => __( 'True if this is the rate currently selected by the customer for the cart.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
						'formatted_destination' => array(
							'description' => __( 'Full destination for the package.', 'cart-rest-api-for-woocommerce' ),
							'type'        => 'string',
							'context'     => array( 'view', 'edit' ),
							'readonly'    => true,
						),
					),
				),
			),
		);
	} // END get_shipping_properties()

	/**
	 * Schema for a single shipping rate.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_rate_properties() {
		return array(
			'key'   => array(
				'description' => __( 'ID of the shipping rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'method_id' => array(
				'description' => __( 'ID of the shipping method that provided the rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'instance_id' => array(
				'description' => __( 'Instance ID of the shipping method that provided the rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'label' => array(
				'description' => __( 'Rate label.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'cost'  => array(
				'description' => __( 'Price of this shipping rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'html'  => array(
				'description' => __( 'Rate label and cost formatted.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'taxes' => array(
				'description' => __( 'Taxes applied to this shipping rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'string',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'chosen_method' => array(
				'description' => __( 'Chosen method.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'boolean',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
			),
			'meta_data' => array(
				'description' => __( 'Meta data attached to the shipping rate.', 'cart-rest-api-for-woocommerce' ),
				'type' => 'object',
				'context' => array( 'view', 'edit' ),
				'readonly' => true,
				'properties' => array(
					'items' => array(
						'description' => __( 'Items the shipping rate has calculated based on.', 'cart-rest-api-for-woocommerce' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'readonly'    => true,
					),
				),
			),
		);
	} // END get_rate_properties()

	/**
	 * Schema for a cart total properties.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_totals_properties() {
		return array(
			'subtotal'       => array(
				'description' => __( 'The subtotal of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'subtotal_tax'   => array(
				'description' => __( 'The subtotal tax of all items, shipping (if any) and fees (if any) before coupons applied (if any) to the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'fee_total'      => array(
				'description' => __( 'The fee total.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'fee_tax'        => array(
				'description' => __( 'The fee tax.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'discount_total' => array(
				'description' => __( 'The discount total.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'discount_tax'   => array(
				'description' => __( 'The discount tax.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'shipping_total' => array(
				'description' => __( 'The shipping total of the selected packages.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'shipping_tax'   => array(
				'description' => __( 'The shipping tax.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'total'          => array(
				'description' => __( 'The total of everything in the cart.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'total_tax'      => array(
				'description' => __( 'The total tax.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	} // END get_totals_properties()

	/**
	 * Schema for a single cross sells properties.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_cross_sells_properties() {
		return array(
			'id'             => array(
				'description' => __( 'Product ID or Variation ID of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'name'           => array(
				'description' => __( 'The name of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'title'          => array(
				'description' => __( 'The title of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'slug'           => array(
				'description' => __( 'The slug of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'price'          => array(
				'description' => __( 'The price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'regular_price'  => array(
				'description' => __( 'The regular price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'sale_price'     => array(
				'description' => __( 'The sale price of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'image'          => array(
				'description' => __( 'The image of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'average_rating' => array(
				'description' => __( 'The average rating of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'on_sale'        => array(
				'description' => __( 'The sale status of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'boolean',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
			'type'           => array(
				'description' => __( 'The product type of the cross-sell product.', 'cart-rest-api-for-woocommerce' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
			),
		);
	} // END get_cross_sells_properties()

} // END class
