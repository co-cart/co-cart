<?php
/**
 * Callback: CoCart\RestApi\Callbacks\UpdateCustomer.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   4.0.0 Introduced.
 */

namespace CoCart\RestApi\Callbacks;

use CoCart\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update customer details callback.
 *
 * Allows you to update the customers details to the cart.
 *
 * @since 4.0.0 Introduced.
 */
class UpdateCustomer extends Abstracts\CoCart_Cart_Extension_Callback {

	/**
	 * Callback name.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $name = 'update-customer';

	/**
	 * Callback to update the cart.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request    Full details about the request.
	 * @param object          $controller The cart controller.
	 *
	 * @return bool Returns true.
	 */
	public function callback( $request, $controller ) {
		$items = isset( $request['quantity'] ) && is_array( $request['quantity'] ) ? wp_unslash( $request['quantity'] ) : array();

		if ( $this->update_customer_on_cart( $request, $controller ) ) {
			$controller->calculate_totals();

			// Only returns success notice if there are no error notices.
			if ( 0 === wc_notice_count( 'error' ) ) {
				wc_add_notice( __( 'Cart updated.', 'cart-rest-api-for-woocommerce' ), 'success' );
			}
		}

		return true;
	} // END callback()

	/**
	 * For each field the customer passes, it will be applied to the cart ready
	 * to calculate shipping and totals before checkout.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request    Full details about the request.
	 * @param object          $controller The cart controller.
	 */
	protected function update_customer_on_cart( $request, $controller ) {
		$params = ! is_array( $request ) && method_exists( $request, 'get_params' ) ? $request->get_params() : array();

		if ( ! empty( $params ) ) {
			$billing_details  = array();
			$shipping_details = array();

			$fields = array(
				'first_name',
				'last_name',
				'email',
				'phone',
				'company',
				'address_1',
				'address_2',
				'city',
				'state',
				'country',
				'postcode',
			);

			// Get current details of the customer if any.
			$customer = $controller->get_cart_instance()->get_customer();

			foreach ( $fields as $key ) {
				in_array( $key, $params ) ? $billing_details[ 'billing_' . $key ] = wc_clean( wp_unslash( $request[ $key ] ) ) : '';

				if ( 'email' === $key && ! empty( $billing_details['billing_email'] ) ) {
					if ( ! \WC_Validation::is_email( $billing_details['billing_email'] ) ) {
						unset( $billing_details['billing_email'] );
						$billing_details['billing_email'] = $customer->get_billing_email();
					}
				}

				if ( 'phone' === $key && ! empty( $billing_details['billing_phone'] ) ) {
					if ( ! \WC_Validation::is_phone( $billing_details['billing_phone'] ) ) {
						unset( $billing_details['billing_phone'] );
						$billing_details['billing_phone'] = $customer->get_billing_phone();
					}
				}

				if ( 'country' === $key && ! empty( $billing_details['billing_country'] ) ) {
					if ( ! $this->validate_country( $request ) ) {
						unset( $billing_details['billing_country'] );
						$billing_details['billing_country'] = $customer->get_billing_country();
					}
				}

				if ( 'postcode' === $key && ! empty( $billing_details['billing_postcode'] ) ) {
					if ( ! $this->validate_postcode( $request ) ) {
						unset( $billing_details['billing_postcode'] );
						$billing_details['billing_postcode'] = $customer->get_billing_postcode();
					}
				}

				if ( ! $request['ship_to_different_address'] && ! wc_ship_to_billing_address_only() ) {
					in_array( $key, $params ) ? $shipping_details[ 'shipping_' . $key ] = wc_clean( wp_unslash( $request[ $key ] ) ) : '';
				} else {
					in_array( $key, $params ) ? $shipping_details[ 'shipping_' . $key ] = wc_clean( wp_unslash( $request[ 's_' . $key ] ) ) : '';

					if ( 'country' === $key && ! empty( $shipping_details['shipping_country'] ) ) {
						if ( ! $this->validate_country( $request, 'shipping' ) ) {
							unset( $shipping_details['shipping_country'] );
							$shipping_details['shipping_country'] = $customer->get_shipping_country();
						}
					}

					if ( 'postcode' === $key && ! empty( $shipping_details['shipping_postcode'] ) ) {
						if ( ! $this->validate_postcode( $request, 'shipping' ) ) {
							unset( $shipping_details['shipping_postcode'] );
							$shipping_details['shipping_postcode'] = $customer->get_shipping_postcode();
						}
					}

					if ( 'phone' === $key && ! empty( $shipping_details['shipping_phone'] ) ) {
						if ( ! \WC_Validation::is_phone( $shipping_details['shipping_phone'] ) ) {
							unset( $shipping_details['shipping_phone'] );
							$shipping_details['shipping_phone'] = $customer->get_shipping_phone();
						}
					}
				}
			}

			if ( ! empty( $billing_details ) && ! empty( $shipping_details ) ) {
				$details = array_merge( $billing_details, $shipping_details );
			} elseif ( ! empty( $billing_details ) ) {
				$details = $billing_details;
			} elseif ( ! empty( $shipping_details ) ) {
				$details = $shipping_details;
			}

			WC()->customer->set_props( $details );
			WC()->customer->save();

			return true;
		}

		return false;
	} // END update_customer_on_cart()

	/**
	 * Validates the requested country.
	 *
	 * Returns false and adds an error notice to the cart if not valid else true.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request      Full details about the request.
	 * @param string          $fieldset_key The address type we are validating the country for. Default is `billing` else `shipping`
	 */
	protected function validate_country( $request, $fieldset_key = 'billing' ) {
		switch ( $fieldset_key ) {
			case 'shipping':
				$country  = isset( $request['s_country'] ) ? $request['s_country'] : '';
				$country  = empty( $country ) ? \WC()->countries->get_base_country() : $country;
				$fieldset = esc_html__( 'Shipping', 'cart-rest-api-for-woocommerce' );
				break;
			case 'billing':
			default:
				$country  = isset( $request['country'] ) ? $request['country'] : '';
				$fieldset = esc_html__( 'Billing', 'cart-rest-api-for-woocommerce' );
				break;
		}

		if ( empty( $country ) ) {
			$country = WC()->customer->{"get_{$fieldset_key}_country"}();
		}

		$country_exists = \WC()->countries->country_exists( $country );

		if ( empty( $country_exists ) ) {
			/* translators: ISO 3166-1 alpha-2 country code */
			wc_add_notice( sprintf( __( "'%s' is not a valid country code.", 'cart-rest-api-for-woocommerce' ), $country ), 'error' );
			return false;
		}

		$allowed_countries = \WC()->countries->get_shipping_countries();

		if ( ! array_key_exists( $country, $allowed_countries ) ) {
			/* translators: 1: Country name, 2: Field Set */
			wc_add_notice( sprintf( __( '\'%1$s\' is not allowed for \'%2$s\'.', 'cart-rest-api-for-woocommerce' ), \WC()->countries->get_countries()[ $country ], $fieldset ), 'error' );
			return false;
		}

		return true;
	} // END validate_country()

	/**
	 * Validates the requested postcode.
	 *
	 * Returns false and adds an error notice to the cart if not valid else true.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request      Full details about the request.
	 * @param string          $fieldset_key The address type we are validating the country for. Default is `billing` else `shipping`
	 */
	protected function validate_postcode( $request, $fieldset_key = 'billing' ) {
		switch ( $fieldset_key ) {
			case 'shipping':
				$country    = isset( $request['s_country'] ) ? $request['s_country'] : '';
				$country    = empty( $country ) ? \WC()->countries->get_base_country() : $country;
				$postcode   = wc_format_postcode( $request['s_postcode'], $country );
				$field_name = esc_html__( 'Shipping postcode', 'cart-rest-api-for-woocommerce' );
				break;
			case 'billing':
			default:
				$country    = isset( $request['country'] ) ? $request['country'] : '';
				$postcode   = wc_format_postcode( $request['postcode'], $country );
				$field_name = esc_html__( 'Billing postcode', 'cart-rest-api-for-woocommerce' );
				break;
		}

		if ( empty( $country ) ) {
			$country = WC()->customer->{"get_{$fieldset_key}_country"}();
		}

		if ( ! empty( $postcode ) && ! \WC_Validation::is_postcode( $postcode, $country ) ) {
			/* translators: %s: field name */
			wc_add_notice( sprintf( __( '%s is not a valid postcode / ZIP.', 'cart-rest-api-for-woocommerce' ), esc_html( $field_name ) ), 'error' );
			return false;
		}

		return true;
	} // END validate_postcode()

} // END class

return new UpdateCustomer();
