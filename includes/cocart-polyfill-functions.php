<?php
/**
 * CoCart Polyfill Functions.
 *
 * These functions provide support for those below specific versions of PHP.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   3.11.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polyfill for PHP versions below 8.0
 */
if ( ! function_exists( 'str_starts_with' ) ) {

	/**
	 * Checks if a string starts with a given substring.
	 *
	 * Performs a case-sensitive check indicating if haystack begins with needle.
	 *
	 * @param string $haystack The string to search in.
	 * @param string $needle   The substring to search for in the haystack.
	 */
	function str_starts_with( string $haystack, string $needle ): bool { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return 0 === strncmp( $haystack, $needle, strlen( $needle ) );
	}
}
