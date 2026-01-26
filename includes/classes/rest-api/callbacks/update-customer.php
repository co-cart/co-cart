<?php
/**
 * Callback: CoCart\RestApi\Callbacks\UpdateCustomer.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Callback
 * @since   4.1.0 Introduced.
 * @version 5.0.0
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
	 * @param object WP_REST_Request $request    The request object.
	 * @param object                 $controller The cart controller.
	 *
	 * @return bool Returns true.
	 */
	public function callback( $request, $controller ) {
		try {
			if ( $controller->is_completely_empty() ) {
				throw new CoCart_Data_Exception( 'cocart_cart_empty', __( 'Cart is empty. Please add items to cart first.', 'cocart-core' ), 404 );
			}

			$this->update_customer_on_cart( $request, $controller );
			$this->recalculate_totals( $request, $controller );

			// Only returns success notice if there are no error notices.
			if ( 0 === wc_notice_count( 'error' ) ) {
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

			$billing_country  = CoCart_Utilities_Cart_Helpers::is_country_valid( $request, 'billing' );
			$shipping_country = CoCart_Utilities_Cart_Helpers::is_country_valid( $request, 'shipping' );

			// Handle billing country validation error.
			if ( is_wp_error( $billing_country ) ) {
				throw new CoCart_Data_Exception(
					esc_attr( $billing_country->get_error_code() ),
					esc_html( $billing_country->get_error_message() ),
					absint( $billing_country->get_error_data()['status'] )
				);
			}

			// Handle shipping country validation error.
			if ( is_wp_error( $shipping_country ) ) {
				throw new CoCart_Data_Exception(
					esc_attr( $shipping_country->get_error_code() ),
					esc_html( $shipping_country->get_error_message() ),
					absint( $shipping_country->get_error_data()['status'] )
				);
			}

			$fields = array(
				'billing'  => \WC()->countries->get_address_fields(
					$billing_country,
					'billing_'
				),
				'shipping' => \WC()->countries->get_address_fields(
					$shipping_country,
					'shipping_'
				),
			);

			// Get current details of the customer if any.
			$customer = $controller->get_cart_instance()->get_customer();

			foreach ( $fields['billing'] as $key => $value ) {
				$param_key = str_replace( 'billing_', '', $key );

				// Check if field is required.
				if ( ! empty( $value['required'] ) && empty( $params[ $param_key ] ) ) {
					throw new CoCart_Data_Exception(
						'cocart_billing_field_required',
						sprintf(
							/* Translators: %s = Field label */
							esc_html__( '%s is required.', 'cocart-core' ),
							esc_html( $value['label'] )
						),
						400
					);
				}

				// Validate email fields.
				if (
					array_key_exists( 'email', $params ) && ! \WC_Validation::is_email( wc_clean( wp_unslash( $params['email'] ) ) ) ||
					! empty( $value['validate'] ) && in_array( 'email', $value['validate'] ) && ! \WC_Validation::is_email( wc_clean( wp_unslash( $params[ $param_key ] ) ) ) // Custom field validation.
				) {
					throw new CoCart_Data_Exception(
						'cocart_email_field_invalid',
						sprintf(
							/* Translators: %s = Field value */
							esc_html__( 'The provided email address (%s) is not valid — please provide a valid email address.', 'cocart-core' ),
							esc_html( $params[ $param_key ] )
						),
						400
					);
				}

				// Validate phone fields.
				if (
					array_key_exists( 'phone', $params ) && ! \WC_Validation::is_phone( wc_clean( wp_unslash( $params['phone'] ) ) ) ||
					! empty( $value['validate'] ) && isset( $value['validate'] ) && in_array( 'phone', $value['validate'] ) && ! \WC_Validation::is_phone( wc_clean( wp_unslash( $params[ $param_key ] ) ) ) // Custom field validation.
				) {
					throw new CoCart_Data_Exception(
						'cocart_phone_field_invalid',
						sprintf(
							/* Translators: %s = Field value */
							esc_html__( 'The provided phone number (%s) is not valid — please provide a valid phone number.', 'cocart-core' ),
							esc_html( $params[ $param_key ] )
						),
						400
					);
				}

				// Prepares customer billing fields.
				array_key_exists( $param_key, $params ) && ! empty( $params[ $param_key ] ) ? $details[ $key ] = wc_clean( wp_unslash( $params[ $param_key ] ) ) : ''; // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict

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
					$result = CoCart_Utilities_Cart_Helpers::is_country_valid( $request, 'billing' );

					if ( is_wp_error( $result ) ) {
						throw new CoCart_Data_Exception(
							esc_attr( $result->get_error_code() ),
							esc_html( $result->get_error_message() ),
							absint( $result->get_error_data()['status'] )
						);
					}

					if ( ! $result ) {
						unset( $details['billing_country'] );
					}
				}

				if ( 'postcode' === $param_key && ! empty( $details['billing_postcode'] ) ) {
					$result = CoCart_Utilities_Cart_Helpers::is_postcode_valid( $request, 'billing' );

					if ( is_wp_error( $result ) ) {
						throw new CoCart_Data_Exception(
							esc_attr( $result->get_error_code() ),
							esc_html( $result->get_error_message() ),
							absint( $result->get_error_data()['status'] )
						);
					}

					if ( ! $result ) {
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

				// Sees if the customer has entered enough data to calculate shipping yet.
				if ( ! $customer->get_shipping_country() || ( ! $customer->get_shipping_state() && ! $customer->get_shipping_postcode() ) ) {
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
	 * @since 4.1.0 Introduced.
	 *
	 * @deprecated 5.0.0 Use CoCart_Utilities_Cart_Helpers::is_country_valid() instead.
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param string          $field_type The address type we are validating the country for. Default is `billing` else `shipping`.
	 *
	 * @return string|bool Returns the validated country code or throws exception.
	 */
	public function validate_country( $request, $field_type = 'billing' ) {
		cocart_deprecated_function( 'CoCart_Update_Customer_Callback::validate_country', '5.0.0', 'CoCart_Utilities_Cart_Helpers::is_country_valid' );

		$result = CoCart_Utilities_Cart_Helpers::is_country_valid( $request, $field_type );

		if ( is_wp_error( $result ) ) {
			throw new CoCart_Data_Exception(
				esc_attr( $result->get_error_code() ),
				esc_html( $result->get_error_message() ),
				absint( $result->get_error_data()['status'] )
			);
		}

		return $result;
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
	 * @since 4.1.0 Introduced.
	 *
	 * @deprecated 5.0.0 Use CoCart_Utilities_Cart_Helpers::is_postcode_valid() instead.
	 *
	 * @param WP_REST_Request $request    The request object.
	 * @param string          $field_type The address type we are validating the postcode for. Default is `billing` else `shipping`.
	 *
	 * @return bool Returns true if valid or throws exception.
	 */
	public function validate_postcode( $request, $field_type = 'billing' ) {
		cocart_deprecated_function( 'CoCart_Update_Customer_Callback::validate_postcode', '5.0.0', 'CoCart_Utilities_Cart_Helpers::is_postcode_valid' );

		$result = CoCart_Utilities_Cart_Helpers::is_postcode_valid( $request, $field_type );

		if ( is_wp_error( $result ) ) {
			throw new CoCart_Data_Exception(
				esc_attr( $result->get_error_code() ),
				esc_html( $result->get_error_message() ),
				absint( $result->get_error_data()['status'] )
			);
		}

		return $result;
	} // END validate_postcode()
} // END class

return new CoCart_Update_Customer_Callback();
