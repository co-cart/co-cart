<?php
/**
 * CoCart Register
 *
 * Functions for registering.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.0.0
 */

// use CoCart\Data\CoCart_Data_Exception as DataException;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a REST route.
 *
 * Makes it easy to register a new REST route for CoCart with validation for callback and schema.
 *
 * @since X.0.0 Introduced.
 *
 * @param string $namespace The namespace for the route.
 * @param string $path      The REST route.
 * @param array  $args      The route arguments. Example: array(
 *          array(
 *              'methods'             => WP_REST_Server::READABLE,
 *              'callback'            => 'get_cart',
 *              'permission_callback' => '__return_true',
 *              'args'                => array(
 *                  'cart_key' => array(
 *                      'description' => 'Unique identifier for the cart or customer.',
 *                      'type'        => 'string',
 *                      'required'    => false,
 *                  ),
 *              ),
 *              'schema' => array( $this, 'get_public_cart_schema' ),
 *          )
 */
function cocart_register_rest_route( $route = '/cart', $path = '/', $args = array() ) {
	// try {
		$count_routes = count( array_keys( $args ) );

	if ( $count_routes > 1 ) {
		_doing_it_wrong(
			'cocart_registering_many_routes',
			sprintf(
				/* translators: %s: cocart_register_rest_route */
				__( 'REST API routes can only be registered with %s function 1 at a time.', 'cart-rest-api-for-woocommerce' ),
				'<code>' . __FUNCTION__ . '</code>'
			),
			'4.0.0'
		);
	}

		// Default args.
		$default_args = array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => null,
				'permission_callback' => '__return_true', // Default as true.
				'args'                => array(), // No arguments by default.
				'override'            => false, // Default as false.
			),
			'schema' => array(),
		);

		$args = wp_parse_args( $args, $default_args );

		if ( ! did_action( 'rest_api_init' ) ) {
			_doing_it_wrong(
				'cocart_register_rest_route',
				sprintf(
					/* translators: %s: rest_api_init */
					__( 'REST API routes must be registered on the %s action.', 'cart-rest-api-for-woocommerce' ),
					'<code>rest_api_init</code>'
				),
				'4.0.0'
			);
		}

		if ( empty( $args['callback'] ) ) {
			_doing_it_wrong(
				'cocart_no_route_callback',
				__( 'Callback for ${route} ${path} does not exist!', 'cart-rest-api-for-woocommerce' ),
				'4.0.0'
			);
		}

		if ( empty( $args['schema'] ) && ! isset( $args['schema']['properties'] ) ) {
			_doing_it_wrong(
				'cocart_no_schema_properties',
				__( 'Schema properties for ${route} ${path} does not exist!', 'cart-rest-api-for-woocommerce' ),
				'4.0.0'
			);
		}

		// error_log( print_r( $args ) );

		register_rest_route( 'cocart/v3', $route . $path, $args );

		/*
		} catch ( \CoCart_Data_Exception $e ) {
		var_dump( print_r( $e ) );
		return new \WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
		}*/
} // END cocart_register_rest_route()
