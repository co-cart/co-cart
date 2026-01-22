<?php
/**
 * Callback: CoCart\RestApi\Callbacks\UpdateCustomer.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   4.1.0 Introduced.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update customer details callback.
 *
 * Allows you to update the customers details to the cart.
 *
 * @since 4.1.0 Introduced.
 */
class CoCart_Update_Customer_Callback extends CoCart_Cart_Extension_Callback {

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
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param object          $controller The cart controller.
	 *
	 * @return bool Returns true.
	 */
	public function callback( $request, $controller ) {
		try {
			if ( $controller->is_completely_empty() ) {
				throw new CoCart_Data_Exception( 'cocart_cart_empty', __( 'Cart is empty. Please add items to cart first.', 'cart-rest-api-for-woocommerce' ), 404 );
			}

			if ( $this->update_customer_on_cart( $request, $controller ) ) {
				$this->recalculate_totals( $request, $controller );

				// Only returns success notice if there are no error notices.
				if ( 0 === wc_notice_count( 'error' ) ) {
					wc_add_notice( __( 'Cart updated.', 'cart-rest-api-for-woocommerce' ), 'success' );
				}
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return CoCart_Response::get_error_response( $e->getErrorCode(), $e->getMessage(), $e->getCode(), $e->getAdditionalData() );
		}
	} // END callback()

	/**
	 * Return route parameters so we can Ignore them,
	 * as we don't want to save them as meta data for the customer.
	 *
	 * @access public
	 *
	 * @param object $controller The cart controller.
	 *
	 * @return array Default parameters.
	 */
	public function ignore_default_params( $controller ) {
		if ( empty( $controller ) ) {
			return array();
		}

		return $controller->get_collection_params();
	} // END ignore_default_params()

	/**
	 * For each field the customer passes validation, it will be applied to the cart.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param object          $controller The cart controller.
	 *
	 * @return bool
	 */
	protected function update_customer_on_cart( $request, $controller ) {
		$params = ! is_array( $request ) && method_exists( $request, 'get_params' ) ? $request->get_params() : array();

		if ( ! empty( $params ) ) {
			$details = array();

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
				// Prepares customer billing field.
				if ( array_key_exists( $key, $params ) ) {
					$details[ 'billing_' . $key ] = wc_clean( wp_unslash( $params[ $key ] ) );
				}

				// If a field has not provided a value, then unset it.
				if ( empty( $details[ 'billing_' . $key ] ) ) {
					unset( $details[ 'billing_' . $key ] );
				}

				// Validates customer billing fields for email, phone, country and postcode.
				if ( 'email' === $key && ! empty( $details['billing_email'] ) ) {
					if ( ! \WC_Validation::is_email( $details['billing_email'] ) ) {
						unset( $details['billing_email'] );
						$details['billing_email'] = $customer->get_billing_email();
					} else {
						$details['billing_email'] = sanitize_email( $details['billing_email'] );
					}
				}

				if ( 'phone' === $key && ! empty( $details['billing_phone'] ) ) {
					$details['billing_phone'] = wc_sanitize_phone_number( $details['billing_phone'] );
					if ( ! \WC_Validation::is_phone( $details['billing_phone'] ) ) {
						unset( $details['billing_phone'] );
						$details['billing_phone'] = $customer->get_billing_phone();
					} else {
						$details['billing_phone'] = wc_format_phone_number( $details['billing_phone'] );
					}
				}

				if ( 'country' === $key && ! empty( $details['billing_country'] ) ) {
					if ( ! $this->validate_country( $request ) ) {
						unset( $details['billing_country'] );
					}
				}

				if ( 'postcode' === $key && ! empty( $details['billing_postcode'] ) ) {
					if ( ! $this->validate_postcode( $request ) ) {
						unset( $details['billing_postcode'] );
					} else {
						$country                     = empty( $details['billing_country'] ) ? \WC()->countries->get_base_country() : $details['billing_country'];
						$details['billing_postcode'] = wc_format_postcode( $details['billing_postcode'], $country );
					}
				}
			}

			/**
			 * Filter allows for additional customer fields to be validated and added if supported.
			 *
			 * @since 4.1.0 Introduced.
			 *
			 * @param array           $details  Current customer details.
			 * @param WP_REST_Request $request  The request object.
			 * @param array           $fields   Default customer fields.
			 * @param object          $customer The customer object.
			 * @param object          $callback Callback class.
			 */
			$details = apply_filters( 'cocart_update_customer_fields', $details, $request, $fields, $customer, $this );

			// If there are any customer details remaining then set the details, save and return true.
			if ( ! empty( $details ) ) {
				foreach ( $params as $key => $value ) {
					// Ignore default parameters as we don't want to save those as meta data.
					if ( array_key_exists( $key, $this->ignore_default_params( $controller ) ) ) {
						continue;
					}

					// Rename the key so we can use the callable functions to set customer data.
					if ( 0 === stripos( $key, 's_' ) ) {
						$key = preg_replace( '/^s_/', 'shipping_', $key, 1 );
					}

					// If the prefix is not for shipping, then assume the field is for billing.
					if ( 0 !== stripos( $key, 'shipping_' ) ) {
						// By default if the prefix `billing_` is missing then add the prefix for the key.
						if ( 0 !== stripos( $key, 'billing_' ) ) {
							$key = 'billing_' . $key;
						}
					}

					// Use setters where available.
					if ( is_callable( array( $customer, "set_{$key}" ) ) ) {
						// Nullify field if no value is provided to prevent "Undefined array key" error.
						if ( empty( $details[ $key ] ) ) {
							$details[ $key ] = null;
						}

						// Set customer information.
						$customer->{"set_{$key}"}( $details[ $key ] );

						// Store custom fields prefixed with either `billing_` or `shipping_`.
					} elseif ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) ) {
						$customer->update_meta_data( $key, wc_clean( wp_unslash( $value ) ) );
					}
				}

				// Sees if the customer has entered enough data to calculate shipping yet.
				if ( ! $customer->get_shipping_country() || ( ! $customer->get_shipping_state() && ! $customer->get_shipping_postcode() ) ) {
					$customer->set_calculated_shipping( true );
				}

				$customer->save();

				return true;
			}
		}

		return false;
	} // END update_customer_on_cart()

	/**
	 * Validates the requested country.
	 *
	 * Returns false and adds an error notice to the cart if not valid else true.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request      The request object.
	 * @param string          $fieldset_key The address type we are validating the country for. Default is `billing` else `shipping`.
	 *
	 * @return bool
	 */
	public function validate_country( $request, $fieldset_key = 'billing' ) {
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

		$country_exists = WC()->countries->country_exists( $country );

		if ( empty( $country_exists ) ) {
			/* translators: ISO 3166-1 alpha-2 country code */
			wc_add_notice( sprintf( __( "'%s' is not a valid country code.", 'cart-rest-api-for-woocommerce' ), $country ), 'error' );
			return false;
		}

		$allowed_countries = WC()->countries->get_shipping_countries();

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
	 * @access public
	 *
	 * @param WP_REST_Request $request      The request object.
	 * @param string          $fieldset_key The address type we are validating the country for. Default is `billing` else `shipping`.
	 *
	 * @return bool
	 */
	public function validate_postcode( $request, $fieldset_key = 'billing' ) {
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

return new CoCart_Update_Customer_Callback();
