<?php
/**
 * CoCart REST API helpers.
 *
 * Provides functions that provide helpful data for the plugin.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart/Helpers
 * @since    2.3.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API helper class.
 *
 * @package CoCart REST API/Helpers
 */
class CoCart_Helpers {

	/**
	 * Cache 'gte' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results.
	 *
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @access private
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	}

	/**
	 * Returns true if the installed version of WooCommerce is 3.6 or greater.
	 *
	 * @access public
	 * @return boolean
	 */
	public static function is_wc_version_gte_3_6() {
		return self::is_wc_version_gte( '3.6' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is 4.0 or greater.
	 *
	 * @access public
	 * @return boolean
	 */
	public static function is_wc_version_gte_4_0() {
		return self::is_wc_version_gte( '4.0' );
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @access public
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	}

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @access public
	 * @param  string  $version the version to compare
	 * @return boolean true if the installed version of WooCommerce is > $version
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}

		return self::$is_wc_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WordPress is greater than or equal to $version.
	 *
	 * @access public
	 * @param  string  $version
	 * @return boolean
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( WC_PB()->plugin_version( true, $wp_version ), $version, '>' );
		}

		return self::$is_wp_version_gt[ $version ];
	}

	/**
	 * Returns true if the installed version of WordPress is greater than or equal to $version.
	 *
	 * @access public
	 * @param  string  $version
	 * @return boolean
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( WC_PB()->plugin_version( true, $wp_version ), $version, '>=' );
		}

		return self::$is_wp_version_gte[ $version ];
	} // END is_wp_version_gte()

	/**
	 * Returns true if CoCart is a pre-release.
	 *
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function is_cocart_pre_release() {
		if ( 
			strpos( COCART_VERSION, 'beta' ) ||
			strpos( COCART_VERSION, 'rc' )
		) {
			return true;
		}

		return false;
	} // END is_cocart_pre_release()

	/**
	 * Returns true if CoCart is a Beta release.
	 *
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function is_cocart_beta() {
		if ( strpos( COCART_VERSION, 'beta' ) ) {
			return true;
		}

		return false;
	} // END is_cocart_beta()

	/**
	 * Returns true if CoCart is a Release Candidate.
	 *
	 * @access public
	 * @static
	 * @return boolean
	 */
	public static function is_cocart_rc() {
		if ( strpos( COCART_VERSION, 'rc' ) ) {
			return true;
		}

		return false;
	} // END is_cocart_rc()

	/**
	 * Returns true if we are making a REST API request for CoCart.
	 *
	 * @access  public
	 * @static
	 * @since   2.1.0
	 * @version 2.2.0
	 * @return  bool
	 */
	public static function is_rest_api_request() {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		$rest_prefix         = trailingslashit( rest_get_url_prefix() );
		$is_rest_api_request = ( false !== strpos( $_SERVER['REQUEST_URI'], $rest_prefix . 'cocart/' ) );

		return $is_rest_api_request;
	} // END is_rest_api_request()

	/**
	 * Checks if CoCart Pro is installed.
	 *
	 * @access public
	 * @static
	 * @return array
	 */
	public static function is_cocart_pro_installed() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'cocart-pro/cocart-pro.php', $active_plugins ) || array_key_exists( 'cocart-pro/cocart-pro.php', $active_plugins );
	} // END is_cocart_pro_installed()

} // END class

return new CoCart_Helpers();
