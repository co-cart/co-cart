<?php
/**
 * Callback: Allows you to update the customer.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callbacks
 * @since   4.1.0 Introduced.
 * @license GPL-3.0
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
class CoCart_Update_Customer_Callback extends CoCart_REST_Callback {

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
				throw new CoCart_Data_Exception( 'cocart_cart_empty', __( 'Cart is empty. Please add items to cart first.', 'cocart-core' ), 404 );
			}

			$this->update_customer_on_cart( $request, $controller );

			// Only returns success notice if there are no error notices.
			if ( 0 === wc_notice_count( 'error' ) ) {
				$controller->calculate_totals( $request, true );
				wc_add_notice( __( 'Cart updated.', 'cocart-core' ), 'success' );
			}

			return true;
		} catch ( CoCart_Data_Exception $e ) {
			return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ), $e->getAdditionalData() );
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
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access protected
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param object          $controller The cart controller.
	 */
	protected function update_customer_on_cart( $request, $controller ) {
		$params = ! is_array( $request ) && method_exists( $request, 'get_params' ) ? $request->get_params() : array();

		if ( ! empty( $params ) ) {
			$details = array();

			$fields = array(
				'billing'  => \WC()->countries->get_address_fields(
					$this->validate_country( $request, 'billing' ),
					'billing_'
				),
				'shipping' => \WC()->countries->get_address_fields(
					$this->validate_country( $request, 'shipping' ),
					'shipping_'
				),
			);

			// Get current details of the customer if any.
			$customer = $controller->get_cart_instance()->get_customer();

			foreach ( $fields as $key ) {
				// Prepares customer billing field.
				if ( array_key_exists( $key, $params ) ) {
					$details[ 'billing_' . $key ] = wc_clean( wp_unslash( $params[ $key ] ) );
				}

				// If a field has not provided a value, then unset it.
				if ( empty( $details[ 'billing_' . $param_key ] ) ) {
					unset( $details[ 'billing_' . $param_key ] );
				}

				// Validates customer billing fields for email, phone, country and postcode.
				if ( 'email' === $param_key && ! empty( $details['billing_email'] ) ) {
					$details['billing_email'] = sanitize_email( $details['billing_email'] );
				}

				if ( 'phone' === $param_key && ! empty( $details['billing_phone'] ) ) {
					$details['billing_phone'] = wc_sanitize_phone_number( $details['billing_phone'] );
					$details['billing_phone'] = wc_format_phone_number( $details['billing_phone'] );
				}

				if ( 'country' === $param_key && ! empty( $details['billing_country'] ) ) {
					if ( ! empty( $this->validate_country( $request ) ) ) {
						unset( $details['billing_country'] );
					}
				}

				if ( 'postcode' === $param_key && ! empty( $details['billing_postcode'] ) ) {
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
				$field_key = '';

				foreach ( $params as $key => $value ) {
					// Ignore default parameters as we don't want to save those as meta data.
					if ( array_key_exists( $key, $this->ignore_default_params( $controller ) ) ) {
						continue;
					}

					if ( 'ship_to_different_address' === $key ) {
						continue;
					}

					$field_key = $key;

					// Rename the key so we can use the callable functions to set customer data.
					if ( 0 === stripos( $field_key, 's_' ) ) {
						$field_key = preg_replace( '/^s_/', 'shipping_', $field_key, 1 );
					}

					// If the prefix is not for shipping, then assume the field is for billing or custom.
					if ( 0 !== stripos( $field_key, 'shipping_' ) ) {
						// By default if the prefix `billing_` is missing then add the prefix for the key.
						if ( 0 !== stripos( $field_key, 'billing_' ) ) {
							$field_key = 'billing_' . $field_key;
						}
					}

					// Use setters where available.
					if ( is_callable( array( $customer, "set_{$field_key}" ) ) && ! empty( $details[ $field_key ] ) ) {
						// Set customer information.
						$customer->{"set_{$field_key}"}( html_entity_decode( wc_clean( $details[ $field_key ] ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
					} else {
						// Store additional fields as meta data.
						$customer->update_meta_data( $key, html_entity_decode( wc_clean( wp_unslash( $value ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
					}
				}

				// Mark shipping as calculated when the customer has provided a complete enough address,
				// so WooCommerce will calculate and return shipping packages in the response.
				if ( $customer->get_shipping_country() && ( $customer->get_shipping_state() || $customer->get_shipping_postcode() ) ) {
					$customer->set_calculated_shipping( true );
				} else {
					$customer->set_calculated_shipping( false );
				}

				$customer->save();
			}
		}
	} // END update_customer_on_cart()

	/**
	 * Validates the requested country.
	 *
	 * Throws an error notice if not valid else true.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param string          $field_type The address type we are validating the country for. Default is `billing` else `shipping`.
	 *
	 * @return bool
	 */
	public function validate_country( $request, $field_type = 'billing' ) {
		switch ( $field_type ) {
			case 'shipping':
				$country  = isset( $request['s_country'] ) ? $request['s_country'] : '';
				$country  = empty( $country ) ? \WC()->countries->get_base_country() : $country;
				$fieldset = esc_html__( 'Shipping', 'cocart-core' );
				break;
			case 'billing':
			default:
				$country  = isset( $request['country'] ) ? $request['country'] : '';
				$country  = empty( $country ) ? \WC()->countries->get_base_country() : $country;
				$fieldset = esc_html__( 'Billing', 'cocart-core' );
				break;
		}

		if ( empty( $country ) ) {
			$country = \WC()->customer->{"get_{$field_type}_country"}();
		}

		$country_exists = \WC()->countries->country_exists( $country );

		if ( empty( $country_exists ) ) {
			throw new CoCart_Data_Exception(
				'cocart_invalid_country_code',
				sprintf(
					/* translators: ISO 3166-1 alpha-2 country code */
					__( '\'%s\' is not a valid country code.', 'cocart-core' ),
					$country
				),
				400
			);
		}

		$allowed_countries = \WC()->countries->get_shipping_countries();

		if ( ! array_key_exists( $country, $allowed_countries ) ) {
			throw new CoCart_Data_Exception(
				'cocart_invalid_country_code',
				sprintf(
					/* translators: 1: Country name, 2: Field Set */
					__( '\'%1$s\' is not allowed for \'%2$s\'.', 'cocart-core' ),
					\WC()->countries->get_countries()[ $country ],
					$fieldset
				),
				400
			);
		}

		return $country;
	} // END validate_country()

	/**
	 * Validates the requested postcode.
	 *
	 * Throws an error notice if not valid else true.
	 *
	 * @throws CoCart_Data_Exception Exception if invalid data is detected.
	 *
	 * @access public
	 *
	 * @param WP_REST_Request $request      The request object.
	 * @param string          $field_type The address type we are validating the country for. Default is `billing` else `shipping`.
	 *
	 * @return bool
	 */
	public function validate_postcode( $request, $field_type = 'billing' ) {
		switch ( $field_type ) {
			case 'shipping':
				$country    = isset( $request['s_country'] ) ? $request['s_country'] : '';
				$country    = empty( $country ) ? \WC()->countries->get_base_country() : $country;
				$postcode   = wc_format_postcode( $request['s_postcode'], $country );
				$field_name = esc_html__( 'Shipping postcode', 'cocart-core' );
				break;
			case 'billing':
			default:
				$country    = isset( $request['country'] ) ? $request['country'] : '';
				$postcode   = wc_format_postcode( $request['postcode'], $country );
				$field_name = esc_html__( 'Billing postcode', 'cocart-core' );
				break;
		}

		if ( empty( $country ) ) {
			$country = \WC()->customer->{"get_{$field_type}_country"}();
		}

		if ( ! empty( $postcode ) && ! \WC_Validation::is_postcode( $postcode, $country ) ) {
			throw new CoCart_Data_Exception(
				'cocart_invalid_postcode',
				sprintf(
					/* translators: %s: field name */
					__( '%s is not a valid postcode / ZIP.', 'cocart-core' ),
					esc_html( $field_name )
				),
				400
			);
		}

		return true;
	} // END validate_postcode()
} // END class

return new CoCart_Update_Customer_Callback();
