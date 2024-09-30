<?php
/**
 * CoCart Deprecated Functions.
 *
 * Where functions come to die.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.2.0 Introduced.
 */

/**
 * Runs a deprecated action with notice only if used.
 *
 * @since 3.10.8 Introduced.
 *
 * @uses cocart_deprecated_hook()
 *
 * @param string $tag         The name of the action hook.
 * @param string $version     The version of CoCart that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 */
function cocart_do_deprecated_action( $tag, $version = '', $replacement = null, $message = null, $args = array() ) {
	if ( ! has_action( $tag ) ) {
		return;
	}

	cocart_deprecated_hook( $tag, $version, $replacement, $message );
	do_action_ref_array( $tag, $args ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
} // END cocart_do_deprecated_action()

/**
 * Runs a deprecated filter with notice only if used.
 *
 * @since 3.10.8 Introduced.
 *
 * @uses cocart_deprecated_filter()
 *
 * @param string $tag         The name of the filter.
 * @param string $version     The version of CoCart that deprecated the filter.
 * @param string $replacement The filter that should have been used.
 * @param string $message     A message regarding the change.
 * @param array  $args        Array of additional function arguments to be passed to do_action().
 */
function cocart_do_deprecated_filter( $tag, $version = '', $replacement = null, $message = null, $args = array() ) {
	if ( ! has_filter( $tag ) ) {
		return;
	}

	cocart_deprecated_filter( $tag, $args, $version, $replacement, $message );
	apply_filters_ref_array( $tag, $args ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound
} // END cocart_do_deprecated_filter()

/**
 * Wrapper for deprecated hook so we can apply some extra logic.
 *
 * @since 3.0.7 Introduced.
 * @since 3.1.0 Changed function `is_ajax()` to `wp_doing_ajax()`.
 *
 * @uses CoCart::is_rest_api_request() to check if the request is a REST API request.
 * @uses wp_doing_ajax()
 *
 * @param string $hook        The hook that was used.
 * @param string $version     The version of CoCart that deprecated the hook.
 * @param string $replacement The hook that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_deprecated_hook( $hook, $version, $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || CoCart::is_rest_api_request() ) {
		do_action( 'deprecated_hook_run', $hook, $replacement, $version, $message ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$message = empty( $message ) ? '' : ' ' . $message;

		$log_string = sprintf(
			/* translators: %1$s: filter name, %2$s: version */
			esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ),
			$hook,
			$version
		);
		$log_string .= $replacement ? sprintf(
			/* translators: %s: filter name */
			esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ),
			$replacement
		) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		CoCart_Logger::log( $log_string . $message, 'debug' );
	} else {
		_deprecated_hook( $hook, $version, $replacement, $message ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} // END cocart_deprecated_hook()

/**
 * Wrapper for deprecated filter so we can apply some extra logic.
 *
 * @since 3.0.0  Introduced.
 * @since 3.1.0  Changed function `is_ajax()` to `wp_doing_ajax()`.
 *
 * @uses CoCart::is_rest_api_request() to check if the request is a REST API request.
 * @uses wp_doing_ajax()
 *
 * @param string $filter      The filter that was used.
 * @param array  $args        Array of additional function arguments to be passed to apply_filters().
 * @param string $version     The version of CoCart that deprecated the filter.
 * @param string $replacement The filter that should have been used.
 * @param string $message     A message regarding the change.
 */
function cocart_deprecated_filter( $filter, $args = array(), $version = '', $replacement = null, $message = null ) {
	if ( wp_doing_ajax() || CoCart::is_rest_api_request() ) {
		do_action( 'deprecated_filter_run', $filter, $args, $replacement, $version, $message ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$message = empty( $message ) ? '' : ' ' . $message;

		$log_string = sprintf(
			/* translators: %1$s: filter name, %2$s: version */
			esc_html__( '%1$s is deprecated since version %2$s', 'cart-rest-api-for-woocommerce' ),
			$filter,
			$version
		);
		$log_string .= $replacement ? sprintf(
			/* translators: %s: filter name */
			esc_html__( '! Use %s instead.', 'cart-rest-api-for-woocommerce' ),
			$replacement
		) : esc_html__( ' with no alternative available.', 'cart-rest-api-for-woocommerce' );

		CoCart_Logger::log( $log_string . $message, 'debug' );
	} else {
		return apply_filters_deprecated( $filter, $args, $version, $replacement, $message );
	}
} // END cocart_deprecated_filter()

/**
 * Wrapper for deprecated functions so we can apply some extra logic.
 *
 * Uses "wp_doing_ajax()" to check if the request is an AJAX request.
 *
 * @since 3.10.8 Introduced.
 *
 * @uses CoCart::is_rest_api_request() to check if the request is a REST API request.
 * @uses CoCart_Logger::log() to log the deprecation.
 * @uses wp_doing_ajax()
 *
 * @param string $function_name Function used.
 * @param string $version       The version of CoCart the message was added in.
 * @param string $replacement   Replacement for the called function.
 */
function cocart_deprecated_function( $function_name, $version = '', $replacement = null ) {
	if ( wp_doing_ajax() || CoCart::is_rest_api_request() ) {
		do_action( 'deprecated_function_run', $function_name, $replacement, $version ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound

		$log_string = sprintf(
			/* translators: %1$s: Function name, %2$s: Version */
			esc_html__( 'The %1$s function is deprecated since version %2$s.', 'cart-rest-api-for-woocommerce' ),
			$function_name,
			$version
		);
		$log_string .= $replacement ? sprintf(
			/* translators: %s: Function name */
			esc_html__( ' Replace with %s.', 'cart-rest-api-for-woocommerce' ),
			$replacement
		) : '';

		CoCart_Logger::log( $log_string, 'debug' );
	} else {
		_deprecated_function( esc_html( $function_name ), esc_html( $version ), esc_html( $replacement ) );
	}
} // END cocart_deprecated_function()
