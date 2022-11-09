<?php
/**
 * Class: CoCart\Status.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   4.0.0 Introduced.
 */

namespace CoCart;

use CoCart\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart status.
 *
 * Provides functions that help identify the status of the plugin.
 *
 * @since 4.0.0 Introduced.
 */
class Status {

	/**
	 * Is CoCart in offline mode?
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return bool Whether CoCart's offline mode is active.
	 */
	public static function is_offline_mode() {
		$offline_mode = false;

		if ( defined( '\\COCART_DEV_DEBUG' ) ) {
			$offline_mode = constant( '\\COCART_DEV_DEBUG' );
		} elseif ( defined( '\\WP_LOCAL_DEV' ) ) {
			$offline_mode = constant( '\\WP_LOCAL_DEV' );
		} elseif ( self::is_local_site() ) {
			$offline_mode = true;
		}

		/**
		 * Filters CoCart's offline mode.
		 *
		 * @param bool $offline_mode Is CoCart's offline mode active.
		 */
		$offline_mode = (bool) apply_filters( 'cocart_is_offline_mode', $offline_mode );

		return $offline_mode;
	} // END is_offline_mode()

	/**
	 * Whether this is a system with a multiple networks.
	 *
	 * Implemented since there is no core is_multi_network function.
	 * Right now there is no way to tell which network is the dominant network on the system.
	 *
	 * Forked from: https://github.com/Automattic/jetpack/blob/master/projects/packages/status/src/class-status.php
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return boolean True if this is a multi-network system.
	 */
	public static function is_multi_network() {
		global $wpdb;

		// If we don't have a multi site setup no need to do any more.
		if ( ! is_multisite() ) {
			return false;
		}

		$num_sites = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->site}" );
		if ( $num_sites > 1 ) {
			return true;
		}

		return false;
	} // END is_multi_network()

	/**
	 * If the site is a local site.
	 *
	 * Forked from: https://github.com/Automattic/jetpack/blob/master/projects/packages/status/src/class-status.php
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_local_site() {
		$site_url = site_url();

		// Check for localhost and sites using an IP only first.
		if ( in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) {
			$is_local = true;
		} else {
			$is_local = $site_url && false === strpos( $site_url, '.' );
		}

		// Use Core's environment check, if available.
		if ( 'local' === wp_get_environment_type() ) {
			$is_local = true;
		}

		// Then check for usual usual domains used by local dev tools.
		$known_local = array(
			'#\.local$#i',
			'#\.localhost$#i',
			'#\.test$#i',
			'#\.docksal$#i',       // Docksal.
			'#\.docksal\.site$#i', // Docksal.
			'#\.dev\.cc$#i',       // ServerPress.
			'#\.lndo\.site$#i',    // Lando.
		);

		if ( ! $is_local ) {
			foreach ( $known_local as $url ) {
				if ( preg_match( $url, $site_url ) ) {
					$is_local = true;
					break;
				}
			}
		}

		/**
		 * Filters is_local_site check.
		 *
		 * @param bool $is_local If the current site is a local site.
		 */
		$is_local = apply_filters( 'cocart_is_local_site', $is_local );

		return $is_local;
	} // END is_local_site()

	/**
	 * If is a staging site.
	 *
	 * Forked from: https://github.com/Automattic/jetpack/blob/master/projects/packages/status/src/class-status.php
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_staging_site() {
		// Core's wp_get_environment_type allows for a few specific options. We should default to bowing out gracefully for anything other than production or local.
		$is_staging = ! in_array( wp_get_environment_type(), array( 'production', 'local' ), true );

		$known_staging = array(
			'urls'      => array(
				'#\.staging\.wpengine\.com$#i', // WP Engine. This is their legacy staging URL structure. Their new platform does not have a common URL.
				'#\.staging\.kinsta\.com$#i',   // Kinsta.com.
				'#\.kinsta\.cloud$#i',          // Kinsta.com.
				'#\.stage\.site$#i',            // DreamPress.
				'#\.newspackstaging\.com$#i',   // Newspack.
				'#\.pantheonsite\.io$#i',       // Pantheon.
				'#\.flywheelsites\.com$#i',     // Flywheel.
				'#\.flywheelstaging\.com$#i',   // Flywheel.
				'#\.cloudwaysapps\.com$#i',     // Cloudways.
				'#\.azurewebsites\.net$#i',     // Azure.
				'#\.wpserveur\.net$#i',         // WPServeur.
				'#\-liquidwebsites\.com$#i',    // Liquidweb.
				'#\.myftpupload\.com$#i',       // Go Daddy
				'#\.sg-host\.com$#i',           // Singapore Web Hosting.
				'#\.platformsh\.site$#i',       // Platform.sh
				'#\.wpstage\.net$#i',           // WP Stagecoach.
			),
			'constants' => array(
				'IS_WPE_SNAPSHOT',      // WP Engine. This is used on their legacy staging environment. Their new platform does not have a constant.
				'KINSTA_DEV_ENV',       // Kinsta.com.
				'WPSTAGECOACH_STAGING', // WP Stagecoach.
				'COCART_STAGING_MODE',  // Generic.
				'WP_LOCAL_DEV',         // Generic.
			),
		);

		/**
		 * Filters the flags of known staging sites.
		 *
		 * @param array $known_staging {
		 *     An array of arrays that each are used to check if the current site is staging.
		 *
		 *     @type array $urls      URLs of staging sites in regex to check against site_url.
		 *     @type array $constants PHP constants of known staging/development environments.
		 *  }
		 */
		$known_staging = apply_filters( 'cocart_known_staging', $known_staging );

		if ( isset( $known_staging['urls'] ) ) {
			$site_url = site_url();

			foreach ( $known_staging['urls'] as $url ) {
				if ( preg_match( $url, wp_parse_url( $site_url, PHP_URL_HOST ) ) ) {
					$is_staging = true;
					break;
				}
			}
		}

		if ( isset( $known_staging['constants'] ) ) {
			foreach ( $known_staging['constants'] as $constant ) {
				if ( defined( $constant ) && constant( $constant ) ) {
					$is_staging = true;
				}
			}
		}

		/**
		 * Filters is_staging_site check.
		 *
		 * @param bool $is_staging If the current site is a staging site.
		 */
		$is_staging = apply_filters( 'cocart_is_staging_site', $is_staging );

		return $is_staging;
	} // END is_staging_site()

	/**
	 * Determine if this is a WP VIP-hosted site.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return bool
	 */
	public static function is_vip_site() {
		return defined( 'WPCOM_IS_VIP_ENV' ) && true === constant( 'WPCOM_IS_VIP_ENV' );
	} // END is_vip_site()

} // END class

return new Status();
