<?php
/**
 * REST API Utilities: Monetary Formatting class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Utilities
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monetary Formatting class.
 *
 * @since 5.0.0 Introduced.
 */
class CoCart_REST_Utilities_Monetary_Formatting {

	/**
	 * Formats money values based on request.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 5.0.0 Introduced.
	 *
	 * @param float|string    $value   Original money value.
	 * @param WP_REST_Request $request The request object.
	 *
	 * @return float|string Money value formatted.
	 */
	public static function format_money( $value, $request ) {
		if ( is_bool( $request['formatted'] ) && $request['formatted'] ) {
			return (string) cocart_price_no_html( $value );
		} else {
			return (string) cocart_format_money( $value );
		}
	} // END format_money()
} // END class
