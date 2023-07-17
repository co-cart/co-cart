<?php
/**
 * CoCart Deprecated Functions.
 *
 * Where functions come to die.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.0.0 Introduced.
 */

use CoCart\RestApi\Authentication;
use CoCart\Logger;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Runs a deprecated action with notice only if used.
 *
 * @since 4.0.0 Introduced.
 *
 * @uses cocart_deprecated_hook()
 *
 * @param string $tag         The name of the action hook.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of CoCart that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_do_deprecated_action( $tag, $args, $version = '', $replacement = null, $message = null ) {
	if ( ! has_action( $tag ) ) {
		return;
	}

	cocart_deprecated_hook( $tag, $version, $replacement, $message );
	do_action_ref_array( $tag, $args );
} // END cocart_do_deprecated_action()

/**
 * Runs a deprecated filter with notice only if used.
 *
 * @since 4.0.0 Introduced.
 *
 * @uses cocart_deprecated_filter()
 *
 * @param string $tag         The name of the filter.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 * @param string $version     The version of CoCart that deprecated the filter.
 * @param string $replacement The filter that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_do_deprecated_filter( $tag, $args, $version = '', $replacement = null, $message = null ) {
	if ( ! has_filter( $tag ) ) {
		return;
	}

	cocart_deprecated_filter( $tag, $version, $replacement, $message );
	apply_filters_ref_array( $tag, $args );
} // END cocart_do_deprecated_filter()

/**
 * Wrapper for deprecated hook so we can apply some extra logic.
 *
 * Uses "wp_doing_ajax()" to check if the request is an AJAX request.
 *
 * @since 3.0.7 Introduced.
 * @since 3.1.0 Changed function `is_ajax()` to `wp_doing_ajax()`.
 * @since 4.0.0 Updated to use functions via Namespace.
 *
 * @uses Authentication::is_rest_api_request() to check if the request is a REST API request.
 * @uses Logger::log() to log the deprecation.
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of CoCart that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_deprecated_hook( $hook, $version = '', $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $hook, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		Logger::log( $log_string . $message, 'debug' );
	} else {
		_deprecated_hook( $hook, $version, $replacement, $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} // END cocart_deprecated_hook()

/**
 * Wrapper for deprecated filter so we can apply some extra logic.
 *
 * Uses "wp_doing_ajax()" to check if the request is AJAX.
 *
 * @since 3.0.0 Introduced.
 * @since 3.1.0 Changed function `is_ajax()` to `wp_doing_ajax()`.
 *
 * @uses Authentication::is_rest_api_request() to check if the request is a REST API request.
 * @uses Logger::log() to log the deprecation.
 *
 * @param string $filter      The filter that was used.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of CoCart that deprecated the filter.
 * @param string $replacement The filter that should have been used.
 * @param string $message     A message regarding the change.
 *
 * @return string $filter The filtered value after all hooked functions are applied to it.
 */
function cocart_deprecated_filter( $filter, $args = array(), $version = '', $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_filter_run', $filter, $args, $replacement, $version, $message );

		$message = empty( $message ) ? '' : ' ' . $message;
		/* translators: %1$s: filter name, %2$s: version */
		$log_string = sprintf( esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ), $filter, $version );
		/* translators: %s: filter name */
		$log_string .= $replacement ? sprintf( esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ), $replacement ) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		Logger::log( $log_string . $message, 'debug' );
	} else {
		return apply_filters_deprecated( $filter, $args, $version, $replacement, $message );
	}
} // END cocart_deprecated_filter()

/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * Uses "wp_doing_ajax()" to check if the request is an AJAX request.
 *
 * @since 4.0.0 Introduced.
 *
 * @uses Authentication::is_rest_api_request() to check if the request is a REST API request.
 * @uses Logger::log() to log the deprecation.
 *
 * @param string $function    Function used.
 * @param string $version     The version of CoCart the message was added in.
 * @param string $replacement Replacement for the called function.
 */
function cocart_deprecated_function( $function, $version = '', $replacement = null ) {
	if ( wp_doing_ajax() || Authentication::is_rest_api_request() ) {
		do_action( 'deprecated_function_run', $function, $replacement, $version );

		$log_string  = sprintf( esc_html__( 'The %1$s function is deprecated since version %2$s.', 'cart-rest-api-for-woocommerce' ), $function, $version );
		$log_string .= $replacement ? sprintf( esc_html__( ' Replace with %s.', 'cart-rest-api-for-woocommerce' ), $replacement ) : '';

		Logger::log( $log_string, 'debug' );
	} else {
		_deprecated_function( $function, $version, $replacement );
	}
} // END cocart_deprecated_function()
