<?php
/**
 * CoCart Core Functions.
 *
 * Functions for the core plugin.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Functions
 * @since   4.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set a cookie - wrapper for setcookie using WP constants.
 *
 * @access public
 *
 * @since 4.0.0 Introduced.
 *
 * @param string  $name Name of the cookie being set.
 * @param string  $value Value of the cookie.
 * @param integer $expire Expiry of the cookie.
 * @param bool    $secure Whether the cookie should be served only over https.
 * @param bool    $httponly Whether the cookie is only accessible over HTTP, not scripting languages like JavaScript. @since 2.7.2.
 */
function cocart_setcookie( $name, $value, $expire = 0, $secure = false, $httponly = false, $samesite = 'None' ) {
	if ( ! headers_sent() ) {
		$options = apply_filters(
			'cocart_set_cookie_options',
			array(
				'expires'  => $expire,
				'secure'   => $secure,
				'path'     => COOKIEPATH ? COOKIEPATH : '/',
				'domain'   => COOKIE_DOMAIN,
				/**
				 * Controls whether the cookie should only be accessible via the HTTP protocol, or if it should also be
				 * accessible to Javascript.
				 *
				 * @see https://www.php.net/manual/en/function.setcookie.php
				 *
				 * @param bool   $httponly If the cookie should only be accessible via the HTTP protocol.
				 * @param string $name   Cookie name.
				 * @param string $value  Cookie value.
				 * @param int    $expire When the cookie should expire.
				 * @param bool   $secure If the cookie should only be served over HTTPS.
				 */
				'httponly' => apply_filters( 'cocart_cookie_httponly', $httponly, $name, $value, $expire, $secure ),
				/**
				 * samesite - Set to None by default and only available to those using PHP 7.3 or above.
				 *
				 * @since 2.9.1
				 */
				'samesite' => apply_filters( 'cocart_cookie_samesite', $samesite )
			),
			$name,
			$value
		);

		if ( version_compare( PHP_VERSION, '7.3.0', '>=' ) ) {
			setcookie( $name, $value, $options ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound
		} else {
			setcookie( $name, $value, $options['expires'], $options['path'], $options['domain'], $options['secure'], $options['httponly'] );
		}
	} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		headers_sent( $file, $line );
		trigger_error( "{$name} cookie cannot be set - headers already sent by {$file} on line {$line}", E_USER_NOTICE ); // @codingStandardsIgnoreLine
	}
} // END cocart_cookie()
