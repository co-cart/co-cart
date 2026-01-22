<?php
/**
 * CoCart - Status of the sites environment.
 *
 * @author  Sébastien Dumont
 * @package CoCart/Classes
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Status' ) ) {

	/**
	 * CoCart status.
	 *
	 * Provides functions that help identify the status of the plugin.
	 *
	 * @since 5.0.0 Introduced.
	 */
	class CoCart_Status {

		/**
		 * Is CoCart in offline mode?
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return boolean Whether CoCart's offline mode is active.
		 */
		public static function is_offline_mode() {
			$offline_mode = false;

			if ( defined( 'COCART_DEV_DEBUG' ) ) {
				$offline_mode = constant( 'COCART_DEV_DEBUG' );
			} elseif ( defined( 'WP_LOCAL_DEV' ) ) {
				$offline_mode = constant( 'WP_LOCAL_DEV' );
			} elseif ( self::is_local_site() ) {
				$offline_mode = true;
			}

			/**
			 * Filters CoCart's offline mode.
			 *
			 * @param boolean $offline_mode Is CoCart's offline mode active.
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

			$cache_key = 'cocart_site_count';

			$num_sites = wp_cache_get( $cache_key );

			if ( false === $num_sites ) {
				$num_sites = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->site}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery

				wp_cache_set( $cache_key, $num_sites );
			}

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
		 * @return boolean
		 */
		public static function is_local_site() {
			$site_url = site_url();

			// Check for localhost and sites using an IP only first.
			if ( isset( $_SERVER['REMOTE_ADDR'] ) && in_array( $_SERVER['REMOTE_ADDR'], array( '127.0.0.1', '::1' ) ) ) {
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
				'#\.wip$#i',
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
			 * @param boolean $is_local If the current site is a local site.
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
		 * @return boolean
		 */
		public static function is_staging_site() {
			// Core's wp_get_environment_type allows for a few specific options. We should default to bowing out gracefully for anything other than production or local.
			$is_staging = ! in_array( wp_get_environment_type(), array( 'production', 'local' ), true );

			$known_staging = array(
				'urls'      => array(
					'#\.staging\.wpengine\.com$#i',                    // WP Engine. This is their legacy staging URL structure. Their new platform does not have a common URL.
					'#\.staging\.kinsta\.com$#i',                      // Kinsta.com.
					'#\.kinsta\.cloud$#i',                             // Kinsta.com.
					'#\.stage\.site$#i',                               // DreamPress.
					'#\.newspackstaging\.com$#i',                      // Newspack.
					'#^(?!live-)([a-zA-Z0-9-]+)\.pantheonsite\.io$#i', // Pantheon.
					'#\.pantheonsite\.io$#i',                          // Pantheon.
					'#\.flywheelsites\.com$#i',                        // Flywheel.
					'#\.flywheelstaging\.com$#i',                      // Flywheel.
					'#\.cloudwaysapps\.com$#i',                        // Cloudways.
					'#\.azurewebsites\.net$#i',                        // Azure.
					'#\.wpserveur\.net$#i',                            // WPServeur.
					'#\-liquidwebsites\.com$#i',                       // Liquidweb.
					'#\.myftpupload\.com$#i',                          // Go Daddy.
					'#\.sg-host\.com$#i',                              // Singapore Web Hosting.
					'#\.platformsh\.site$#i',                          // Platform.sh.
					'#\.wpstage\.net$#i',                              // WP Stagecoach.
					'#\.instawp\.xyz$#i',                              // InstaWP.
					'#\.instawp\.co$#i',                               // InstaWP suffix alternate.
					'#\.instawp\.link$#i',                             // InstaWP suffix alternate.
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
			 * @param boolean $is_staging If the current site is a staging site.
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
		 * @return boolean
		 */
		public static function is_vip_site() {
			return defined( 'WPCOM_IS_VIP_ENV' ) && true === constant( 'WPCOM_IS_VIP_ENV' );
		} // END is_vip_site()

		/**
		 * Determine if this is a WP dot COM hosted site.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return boolean
		 */
		public static function is_wp_com_site() {
			return defined( 'IS_WPCOM' ) && true === constant( 'IS_WPCOM' );
		} // END is_wp_com_site()

		/**
		 * Gets the sites WordPress URL.
		 *
		 * This is typically the URL the current site is accessible via.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return string The site URL.
		 */
		public static function get_site_url() {
			if ( ! is_multisite() && defined( 'WP_SITEURL' ) ) {
				$site_url = WP_SITEURL;
			} else {
				$site_url = get_site_url();
			}

			return $site_url;
		} // END get_site_url()

		/**
		 * Gets the URL considers as the live site URL.
		 *
		 * This URL is set by @see self::set_site_url_lock(). This function removes the obfuscation to get a raw URL.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param int|null    $blog_id The blog to get the URL for. Optional. Default is null. Used for multisites only.
		 * @param string      $path    The URL path to append. Optional. Default is ''.
		 * @param string|null $scheme  The URL scheme passed to @see set_url_scheme(). Optional. Default is null which automatically returns the URL as https or http depending on @see is_ssl().
		 *
		 * @return string $url The live site URL.
		 */
		public static function get_live_site_url( $blog_id = null, $path = '', $scheme = null ) {
			if ( empty( $blog_id ) || ! is_multisite() ) {
				$url = get_option( 'cocart_instance_name' );
			} else {
				switch_to_blog( $blog_id );
				$url = get_option( 'cocart_instance_name' );
				restore_current_blog();
			}

			// Remove the prefix used to prevent the site URL being updated on WP Engine.
			$url = str_replace( '_[cocart_instance_name]_', '', $url );

			$url = set_url_scheme( $url, $scheme );

			if ( ! empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false ) {
				$url .= '/' . ltrim( $path, '/' );
			}

			return $url;
		} // END get_live_site_url()

		/**
		 * Removes the protocol and the "www." prefix (if set) from a url.
		 *
		 * Used to allow licenses to remain active should a site switch to HTTPS.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $url The URL to strip the protocol from.
		 *
		 * @return string
		 */
		public static function strip_protocol( $url ) {
			$url = str_replace( array( 'http://', 'https://' ), '', $url );

			// Treat www the same as non-www.
			if ( substr( $url, 0, 4 ) === 'www.' ) {
				$url = substr( $url, 4 );
			}

			return $url;
		} // END strip_protocol()

		/**
		 * Generates a unique key based on the sites URL used to determine staging sites.
		 *
		 * The key cannot simply be the site URL, e.g. http://example.com, because some hosts (WP Engine) replaces all
		 * instances of the site URL in the database when creating a staging site. As a result, we obfuscate
		 * the URL by inserting '_[cocart_instance_name]_' into the middle of it.
		 *
		 * We don't use a hash because keeping the URL in the value allows for viewing and editing the URL
		 * directly in the database.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return string The lock key.
		 */
		public static function get_site_lock_key() {
			$site_url = self::get_site_url();
			$scheme   = wp_parse_url( $site_url, PHP_URL_SCHEME ) . '://';
			$site_url = str_replace( $scheme, '', $site_url );

			return $scheme . substr_replace(
				$site_url,
				'_[cocart_instance_name]_',
				intval( strlen( $site_url ) / 2 ),
				0
			);
		} // END get_site_lock_key()

		/**
		 * Sets the site lock key to record the site's "live" url.
		 *
		 * This key is checked to determine if this database has moved to a different URL.
		 *
		 * @see self::get_site_lock_key() which generates the key.
		 *
		 * @access public
		 *
		 * @static
		 */
		public static function set_site_url_lock() {
			update_option( 'cocart_instance_name', self::get_site_lock_key() );
		} // END set_site_url_lock()
	} // END class

	return new CoCart_Status();
}
