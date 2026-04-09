<?php
/**
 * CoCart - Admin Updates.
 *
 * @author  Sébastien Dumont
 * @package CoCart/Admin/Updates
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Updates' ) ) {

	class CoCart_Admin_Updates extends CoCart_Plugin_Updates {

		/**
		 * Update API URL
		 *
		 * ?Dev note: Will be all connected on the $api_url only once API is completed.
		 *
		 * @access protected
		 *
		 * @static
		 *
		 * @var string
		 */
		protected static $update_api_url = 'https://packages.cocartapi.com/plugin-info';

		/**
		 * Setup class.
		 */
		public function __construct() {
			// Return plugin information and check for updates from remote server.
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_updates' ) );
			add_filter( 'plugins_api', array( $this, 'get_remote_plugin_info' ), 20, 3 );

			// Excludes any CoCart plugins from WP.org updates.
			add_filter( 'http_request_args', array( $this, 'exclude_plugins_from_update_check' ), 5, 2 );

			add_action( 'upgrader_process_complete', array( $this, 'purge' ), 10, 2 );

			// Check each CoCart plugin installed.
			foreach ( array_keys( $this->get_installed_plugins() ) as $plugin_file ) {
				add_action( 'in_plugin_update_message-' . $plugin_file, array( $this, 'modify_plugin_update_message' ), 10, 2 );
				add_action( 'after_plugin_row_' . $plugin_file, array( $this, 'compatibility_check' ), 10, 2 );
				add_action( 'after_plugin_row_' . $plugin_file, array( $this, 'license_information' ), 10, 2 );
			}
		} // END __construct()

		/**
		 * Checks if the license key has expired.
		 *
		 * @access protected
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param array  $plugin_data An array of plugin data.
		 * @param string $license_key License key passed if already fetched.
		 *
		 * @return boolean True if expired, false if not.
		 */
		protected function has_license_expired( $plugin_data, $license_key = '' ) {
			// Should either be false return nothing.
			if ( ! is_array( $plugin_data ) || empty( $plugin_data ) ) {
				return false;
			}

			if ( empty( $license_key ) ) {
				$license_key = CoCart_Helpers::get_license_key( $plugin_data );
			}

			// Bail early if we don't have a key.
			if ( ! $license_key ) {
				return false;
			}

			// Verify the license key.
			$results = json_decode( self::verify_license( $license_key ) );

			// If results failed to return then set as false so it can be checked again laster.
			if ( is_wp_error( $results ) ) {
				return false;
			}

			$license_status = $results->license_key->status;

			// If the results don't return the license key disabled or expired then return as false.
			if ( 'disabled' !== $license_status || 'expired' !== $license_status ) {
				return false;
			}

			return true;
		} // END has_license_expired()

		/**
		 * Generates a unique key based on the product slug.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $product_slug The product slug to generate the cache key for.
		 *
		 * @return string The cache key.
		 */
		public static function get_cache_key( $product_slug ) {
			$hash = hash_hmac( 'sha256', $product_slug, 'cocart' );

			$length = strlen( $hash );
			$key    = substr( $hash, 0, $length / 2 );

			return $key;
		} // END get_cache_key()

		/**
		 * Deletes transients and allows a fresh lookup.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 */
		public static function refresh_plugins_transient() {
			delete_site_transient( 'update_plugins' );
			cocart_delete_site_transient( 'updates_count' );
		} // END refresh_plugins_transient()

		/**
		 * Fetch the plugin info from the remote server.
		 *
		 * @access protected
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string  $product_slug Product slug to identify and filter updates.
		 * @param boolean $force_check  Bypasses cached result. Defaults to false.
		 *
		 * @return object|boolean
		 */
		protected function get_plugin_info( $product_slug, $force_check = false ) {
			// Check cache if $force_check is not overridden.
			if ( ! $force_check ) {
				$plugin_info = cocart_site_transient( 'info_' . self::get_cache_key( $product_slug ) );

				if ( false !== $plugin_info ) {
					return $plugin_info;
				}
			}

			$timeout = wp_doing_cron() ? 30 : 15;

			$response = wp_remote_get(
				self::$update_api_url . '/' . $product_slug . '.json',
				array(
					'timeout' => $timeout,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			$plugin_info = json_decode( wp_remote_retrieve_body( $response ) );
			$status_code = wp_remote_retrieve_response_code( $response );

			$cache_duration = ( 200 === $status_code ) ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS;

			// We got a 4xx or 5xx HTTP error.
			if ( $status_code >= 400 ) {
				$plugin_info = $this->get_request_error(
					$status_code,
					array(
						'response' => $plugin_info,
						'plugin'   => $product_slug,
					)
				);

				$cache_duration = 2 * HOUR_IN_SECONDS;
			}

			cocart_site_transient( 'info_' . self::get_cache_key( $product_slug ), $plugin_info, 'set', $cache_duration );

			return $plugin_info;
		} // END get_plugin_info()

		/**
		 * Fetch the plugin info and packages from the remote server.
		 *
		 * @access protected
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string  $product_slug Product slug to identify and filter updates.
		 * @param boolean $force_check  Bypasses cached result. Defaults to false.
		 *
		 * @return object|boolean
		 */
		protected function get_updates( $product_slug, $force_check = false ) {
			// Check cache if $force_check is not overridden.
			if ( ! $force_check ) {
				$plugin_info = cocart_site_transient( 'updater_' . self::get_cache_key( $product_slug ) );

				if ( false !== $plugin_info ) {
					return $plugin_info;
				}
			}

			$timeout = wp_doing_cron() ? 30 : 15;

			$api_url = add_query_arg(
				array(
					'locale' => get_locale(),
				),
				self::$update_api_url . '/' . $product_slug . '.json'
			);

			$license_key = CoCart_Helpers::get_license_key( $product_slug );

			if ( ! empty( $license_key ) ) {
				$api_url = add_query_arg( 'license_key', $license_key, $api_url );
			}

			$response = wp_remote_get(
				$api_url,
				array(
					'timeout' => $timeout,
					'headers' => array(
						'Accept' => 'application/json',
					),
				)
			);

			$plugin_info = json_decode( wp_remote_retrieve_body( $response ) );
			$status_code = wp_remote_retrieve_response_code( $response );

			$cache_duration = ( 200 === $status_code ) ? 12 * HOUR_IN_SECONDS : HOUR_IN_SECONDS;

			// We got a 4xx or 5xx HTTP error.
			if ( $status_code >= 400 ) {
				$plugin_info = $this->get_request_error(
					$status_code,
					array(
						'response' => $plugin_info,
						'plugin'   => $product_slug,
					)
				);

				$cache_duration = 2 * HOUR_IN_SECONDS;
			}

			cocart_site_transient( 'updater_' . self::get_cache_key( $product_slug ), $plugin_info, 'set', $cache_duration );

			return $plugin_info;
		} // END get_updates()

		/**
		 * Get a WP_Error object to use when the request to CoCart’s server fails.
		 *
		 * @access protected
		 *
		 * @param int   $status_code HTTP status code from the failed request.
		 * @param mixed $data Error data to pass along the WP_Error object.
		 *
		 * @return WP_Error object.
		 */
		protected function get_request_error( $status_code = 404, $data = array() ) {
			$logger = new CoCart_Logger();

			$logger->log(
				sprintf(
					/* translators: %s is a plugin name. */
					! empty( $data['plugin'] ) ? esc_html__( 'Error when contacting the CoCartAPI.com server for %s.', 'cocart-core' ) : esc_html__( 'Error when contacting the CoCartAPI.com server.', 'cocart-core' ),
					$data['plugin']
				),
				'debug'
			);

			return new \WP_Error(
				'cocart_update_failed',
				sprintf(
					/* translators: %s is an email address. */
					__( 'An unexpected error occurred. Something may be wrong with CoCartAPI.com or this server&#8217;s configuration. If you continue to have problems, <a href="%s">contact support</a>.', 'cocart-core' ),
					'mailto:support@cocartapi.com'
				),
				array(
					'status' => $status_code,
				),
				$data
			);
		} // END get_request_error()

		/**
		 * Display plugin details in the update modal.
		 *
		 * When a user clicks "View version x.x.x details" in the WordPress admin,
		 * this serves the full plugin information from CoCart's server.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/plugins_api/
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param false|object|array $result Requested results.
		 * @param string             $action The requested plugins_api().
		 * @param object             $args   Arguments passed to plugins_api().
		 *
		 * @return object|boolean Updated response.
		 */
		public function get_remote_plugin_info( $result, $action, $args ) {
			if ( 'plugin_information' !== $action ) {
				return false;
			}

			if ( ! isset( $args->slug ) ) {
				return false;
			}

			$plugin_file = $this->get_plugin_file( $args->slug );

			// Bail early if not a CoCart plugin.
			if ( empty( $plugin_file ) || ! $this->is_cocart_plugin( $plugin_file ) ) {
				return false;
			}

			// Bail early if community plugin detected. - This is to avoid detecting plugins hosted on WordPress.org.
			if ( 'cart-rest-api-for-woocommerce' === $args->slug ) {
				return false;
			}

			$plugin_info = $this->get_plugin_info( $args->slug );

			if ( is_wp_error( $plugin_info ) ) {
				echo wp_kses_post( $plugin_info->get_error_message() );
				return false;
			}

			$plugin_info = wp_parse_args(
				array(
					'name'           => $plugin_info->title ? $plugin_info->title : $plugin_info->name,
					'slug'           => $plugin_info->slug,
					'version'        => $plugin_info->version,
					'author'         => $plugin_info->author ?? '',
					'author_profile' => $plugin_info->author_profile ?? '',
					'requires'       => $plugin_info->requires ?? '',
					'tested'         => $plugin_info->tested_up_to ?? '',
					'requires_php'   => $plugin_info->requires_php ?? '',
					'download_link'  => $plugin_info->download_link,
					'last_updated'   => $plugin_info->last_updated ?? '',
					'sections'       => array(
						'description' => $plugin_info->sections->description ?? '',
						'changelog'   => $plugin_info->sections->changelog ?? '',
					),
					'banners'        => array(),
					'banners_rtl'    => array(),
				),
				$this->plugin_map( $args->slug )
			);

			unset( $plugin_info['new_version'] ); // Remove new_version as it is not needed in the plugin details modal and can cause confusion with the version field.

			return (object) $plugin_info;
		} // END get_remote_plugin_info()

		/**
		 * Override the WordPress request to check if an update is available.
		 *
		 * @see https://make.wordpress.org/core/2020/07/30/recommended-usage-of-the-updates-api-to-support-the-auto-updates-ui-for-plugins-and-themes-in-wordpress-5-5/
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param mixed $transient The current data for update_plugins.
		 *
		 * @return stdClass Extended data for update_plugins.
		 */
		public function check_for_updates( $transient ) {
			if ( empty( $transient->checked ) ) {
				return $transient;
			}

			$count_updates = 0;

			// If updates are paused then don't check for any updates.
			$updates_paused = cocart_site_transient( 'updates_paused' );

			if ( ! empty( $updates_paused ) ) {
				return $transient;
			}

			// Force-check (only once).
			$force_check = ! empty( $_GET['force-check'] ) ? true : false;

			foreach ( $this->get_installed_plugins() as $plugin_file => $installed_plugin ) {
				$plugin_slug = $this->get_slug_by_plugin_file( $plugin_file );

				// Ignore looking up updates for community version.
				if ( 'cart-rest-api-for-woocommerce' === $plugin_slug ) {
					continue;
				}

				$current_version = $transient->checked[ $plugin_file ] ?? '';

				if ( empty( $current_version ) ) {
					// Add it to the no_update list if not already there.
					if ( ! isset( $transient->no_update[ $plugin_file ] ) ) {
						$transient->no_update[ $plugin_file ] = (object) wp_parse_args( array( 'slug' => $plugin_slug ), $this->plugin_map( $plugin_slug ) );
						unset( $transient->response[ $plugin_file ] );
					}
					continue;
				}

				$plugin_info = $this->get_plugin_info( $plugin_slug, $force_check );

				if ( is_wp_error( $plugin_info ) ) {
					// Add it to the no_update list if not already there.
					if ( ! isset( $transient->no_update[ $plugin_file ] ) ) {
						$transient->no_update[ $plugin_file ] = (object) wp_parse_args( array( 'slug' => $plugin_slug ), $this->plugin_map( $plugin_slug ) );
						unset( $transient->response[ $plugin_file ] );
					}
					continue;
				}

				if ( version_compare( $plugin_info->version, $current_version, '>' ) ) {
					$update = array(
						'plugin'       => $plugin_file,
						'new_version'  => $plugin_info->version,
						'url'          => $plugin_info->author_profile ?? '',
						'tested'       => $plugin_info->tested_up_to ?? '',
						'requires'     => $plugin_info->requires ?? '',
						'requires_php' => $plugin_info->requires_php ?? '',
						'icons'          => array(
							'2x' => esc_url( COCART_URL_PATH . '/assets/images/updater/icon-256x256.png' ),
							'1x' => esc_url( COCART_URL_PATH . '/assets/images/updater/icon-128x128.png' ),
						),
					);

					$result = (object) wp_parse_args( $update, $this->plugin_map( $plugin_slug ) );

					$transient->response[ $plugin_file ] = $result;
					unset( $transient->no_update[ $plugin_file ] );

					++$count_updates;
				} else {
					$transient->no_update[ $plugin_file ] = (object) wp_parse_args( array( 'slug' => $plugin_slug ), $this->plugin_map( $plugin_slug ) );
					unset( $transient->response[ $plugin_file ] );
				}
			}

			cocart_site_transient( 'updates_count', $count_updates, 'set', 12 * HOUR_IN_SECONDS );

			if ( ! empty( $transient ) ) {
				// Enable the two lines below once the translations payload API is created.
				// $translations       = $this->get_translations_update_data();
				// $transient->translations = array_merge( isset( $transient->translations ) ? $transient->translations : array(), $translations );
			}

			// Set the time updates were last checked for CoCart.
			update_option( 'cocart_updates_last_checked', time() );

			return $transient;
		} // END check_for_updates()

		/**
		 * Get translations updates information.
		 *
		 * Scans through all active plugins and obtain data for each product.
		 *
		 * ?Dev note: API to fetch translations does not exist in GlotPress yet. Need to create one, then this function can be used.
		 *
		 * @access public
		 *
		 * @return array Update data {product_id => data}
		 */
		public function get_translations_update_data() {
			$installed_translations = wp_get_installed_translations( 'plugins' );

			$locales = array_values( get_available_languages() );

			/**
			 * Filters the locales requested for plugin translations.
			 *
			 * @since 3.7.0
			 * @since 4.5.0 The default value of the `$locales` parameter changed to include all locales.
			 *
			 * @param array $locales Plugin locales. Default is all available locales of the site.
			 */
			$locales = apply_filters( 'plugins_update_check_locales', $locales ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			$locales = array_unique( $locales );

			// No locales, the response will be empty, we can return now.
			if ( empty( $locales ) ) {
				return array();
			}

			// Get active plugins.
			$active_plugins = $this->get_active_plugins();

			// Nothing to check for so exit.
			if ( empty( $active_plugins ) ) {
				return array();
			}

			// If NOT CRON, three seconds, plus one extra second for every 10 plugins.
			$timeout = wp_doing_cron() ? 30 : 3 + (int) ( count( $active_plugins ) / 10 );

			$request_body = array(
				'locales' => $locales,
				'plugins' => array(),
			);

			// For each active plugin fetch the current version installed.
			foreach ( $active_plugins as $plugin ) {
				$request_body['plugins'][ $plugin['slug'] ] = array( 'version' => $plugin['Version'] );
			}

			// Look up CoCart's GlotPress for each requested plugin for translations.
			$raw_response = wp_remote_post(
				$this->api_url . '/translations',
				array(
					'body'    => wp_json_encode( $request_body ),
					'headers' => array( 'Content-Type: application/json' ),
					'timeout' => $timeout,
				)
			);

			// Something wrong happened on the translate server side.
			$response_code = wp_remote_retrieve_response_code( $raw_response );
			if ( 200 !== $response_code ) {
				return array();
			}

			$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

			// API error, api returned but something was wrong.
			if ( array_key_exists( 'success', $response ) && false === $response['success'] ) {
				return array();
			}

			$translations = array();

			foreach ( $response['data'] as $plugin_name => $language_packs ) {
				foreach ( $language_packs as $language_pack ) {
					// Maybe we have this language pack already installed so lets check revision date.
					if ( array_key_exists( $plugin_name, $installed_translations ) && array_key_exists( $language_pack['wp_locale'], $installed_translations[ $plugin_name ] ) ) {
						$installed_translation_revision_time = new DateTime( $installed_translations[ $plugin_name ][ $language_pack['wp_locale'] ]['PO-Revision-Date'] );
						$new_translation_revision_time       = new DateTime( $language_pack['last_modified'] );

						// Skip if translation language pack is not newer than what is installed already.
						if ( $new_translation_revision_time <= $installed_translation_revision_time ) {
							continue;
						}
					}

					// New language pack to download.
					$translations[] = array(
						'type'       => 'plugin',
						'slug'       => $plugin_name,
						'language'   => $language_pack['wp_locale'],
						// 'version'    => $language_pack['version'],
						'updated'    => $language_pack['last_modified'],
						'package'    => $language_pack['package'],
						'autoupdate' => true,
					);
				}
			}

			return $translations;
		} // END get_translations_update_data()

		/**
		 * Get the number of products that have updates.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @return int The number of products with updates.
		 */
		public static function get_updates_count() {
			$count = cocart_site_transient( 'updates_count' );

			if ( false !== $count ) {
				return $count;
			}
		} // END get_updates_count()

		/**
		 * Return the updates count markup.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @return string Updates count markup, empty string if no updates available.
		 */
		public static function get_updates_count_html() {
			$count = self::get_updates_count();

			if ( $count > 0 ) {
				return sprintf( '<span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $count, number_format_i18n( $count ) );
			}

			return '';
		} // END get_updates_count_html()

		/**
		 * When WP checks plugin versions against the latest versions hosted on WordPress.org, remove any CoCart plugin from the list.
		 *
		 * @see wp_update_plugins()
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param array  $request An array of HTTP request arguments.
		 * @param string $url     The request URL.
		 *
		 * @return array $request Updated array of HTTP request arguments.
		 */
		public function exclude_plugins_from_update_check( $request, $url ) {
			if ( ! is_string( $url ) ) { // @phpstan-ignore-line - $url variable may be changed by other plugins to something else other than string.
				return $request;
			}

			if ( ! preg_match( '@^https?://api.wordpress.org/plugins/update-check(/|\?|$)@', $url ) || empty( $request['body']['plugins'] ) ) {
				// Not a plugin update request. Stop immediately.
				return $request;
			}

			/**
			 * Depending on the API version, the data can have several forms:
			 * - Can be serialized or JSON encoded,
			 * - Can be an object of arrays or an object of objects.
			 */
			$is_serialized = is_serialized( $request['body']['plugins'] );
			$edited        = false;

			if ( $is_serialized ) {
				$plugins = maybe_unserialize( $request['body']['plugins'] );
			} else {
				$plugins = json_decode( $request['body']['plugins'], true );
			}

			if ( ! empty( $plugins->plugins ) ) {
				if ( is_object( $plugins->plugins ) ) {
					foreach ( array_keys( $this->get_installed_plugins() ) as $plugin_file ) {
						if ( isset( $plugins->plugins->$plugin_file ) ) {
							unset( $plugins->plugins->$plugin_file );
							$edited = true;
						}
					}
				} elseif ( is_array( $plugins->plugins ) ) {
					foreach ( array_keys( $this->get_installed_plugins() ) as $plugin_file ) {
						if ( isset( $plugins->plugins[ $plugin_file ] ) ) {
							unset( $plugins->plugins[ $plugin_file ] );
							$edited = true;
						}
					}
				}

				// Remove community plugin.
				unset( $plugins->plugins['cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php'] );
			}

			if ( ! empty( $plugins->active ) ) {
				$active_is_object = is_object( $plugins->active );

				if ( $active_is_object || is_array( $plugins->active ) ) {
					foreach ( $plugins->active as $key => $plugin_basename ) {
						if ( ! in_array( $plugin_basename, array_keys( $this->get_installed_plugins() ), true ) ) {
							continue;
						}

						if ( $active_is_object ) {
							unset( $plugins->active->$key );
						} else {
							unset( $plugins->active[ $key ] );
						}

						$edited = true;
						break;
					}
				}
			}

			if ( $edited ) {
				if ( $is_serialized ) {
					$request['body']['plugins'] = maybe_serialize( $plugins );
				} else {
					$request['body']['plugins'] = wp_json_encode( $plugins );
				}
			}

			return $request;
		} // END exclude_plugins_from_update_check()

		/**
		 * When the update is complete, purge the cache.
		 *
		 * @see https://developer.wordpress.org/reference/hooks/upgrader_process_complete/
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param WP_Upgrader $upgrader WP_Upgrader instance.
		 * @param array       $options  Array of bulk item update data.
		 *
		 * @return void
		 */
		public function purge( $upgrader, $options ) {
			if (
				'update' === $options['action']
				&& 'plugin' === $options['type']
				&& ! empty( $options['plugins'] )
			) {
				foreach ( $options['plugins'] as $plugin ) {
					if ( $plugin === $this->is_cocart_plugin( $plugin ) ) {
						$plugin_slug = $this->get_slug_by_plugin_file( $plugin );
						cocart_site_transient( 'updated', 1, 'set' );
					}
				}
				$this->refresh_plugins_transient();
			}
		} // END purge()

		/**
		 * Warn user to enter their license key to enable updates for the plugin.
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param array  $plugin_data An array of plugin metadata.
		 * @param object $response    An object of metadata about the available plugin update.
		 *
		 * @return void
		 */
		public function modify_plugin_update_message( $plugin_data, $response ) {
			if ( ! current_user_can( 'update_plugins' ) ) {
				return;
			}

			/*
			$plugin = isset( $plugin_data['plugin'] ) ? explode( '/', $plugin_data['plugin'] ) : array();
			$plugin = ! empty( $plugin ) ? $plugin[0] : ''; // Plugin folder should match the slug.

			if ( empty( $plugin ) ) {
				return;
			}*/

			// Do not modify message if legacy plugin detected.
			if (
				isset( $plugin_data['slug'] ) && 'cart-rest-api-for-woocommerce' === $plugin_data['slug']
				// 'cart-rest-api-for-woocommerce' === $plugin
			) {
				return;
			}

			// Bail early if we do have a license key.
			if ( CoCart_Helpers::get_license_key( $plugin_data ) ) {
				return;
			}

			if ( is_multisite() ) {
				/* translators: %s = A link to the updates page. */
				$message           = __( 'Please enter your license key on the <a href="%s">Updates</a> page of the main site.', 'cocart-core' );
				$updates_page_link = add_query_arg( array( 'page' => 'cocart-updates' ), get_admin_url( get_main_site_id(), 'admin.php' ) );

				printf(
					wp_kses(
						$message,
						array(
							'a' => array(
								'href' => array(),
							),
						)
					),
					esc_url( $updates_page_link )
				);
			} else {
				$message = ' ' . __( 'Please <a href="#" class="cocart-open-license-modal" data-plugin-name="%1$s" data-plugin="%2$s">enter your license key</a> to enable updates.', 'cocart-core' );

				printf(
					/* translators: %1 = Plugin name, %2 = Plugin slug. */
					wp_kses(
						$message,
						array(
							'a' => array(
								'href'             => array(),
								'target'           => array(),
								'class'            => array(),
								'data-plugin-name' => array(),
								'data-plugin'      => array(),
							),
						)
					),
					esc_html( $plugin_data['Name'] ),
					esc_attr( $plugin_data['slug'] )
				);
			}
		} // END modify_plugin_update_message()

		/**
		 * Displays a message to the user under the listed plugin if the CoCart plugin
		 * is not tested with the version of CoCart core installed.
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 *
		 * @return void
		 */
		public function compatibility_check( $plugin_file, $plugin_data ) {
			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			// Should either be false return nothing.
			if ( ! is_array( $plugin_data ) || empty( $plugin_data ) ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

			$plugin_slug = $this->get_slug_by_plugin_file( $plugin_file );

			// If plugin is the community plugin, return message.
			if ( 'cart-rest-api-for-woocommerce' === $plugin_slug ) {
				echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_slug . '-update-info' ) . '" data-slug="' . $plugin_slug . '" data-plugin="' . esc_attr( $plugin_file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				echo '<p>';
				echo wp_kses_post( __( 'The community version of CoCart can not be activated because you have a newer version active. It is recommended to <strong>delete</strong> this plugin.', 'cocart-core' ) );
				echo '</p></div></td></tr>';
				return;
			}

			if ( ! empty( $plugin_slug ) && COCART_SLUG !== $plugin_slug ) {
				if ( empty( $plugin_data['CoCart tested up to'] ) || version_compare( COCART_VERSION, $plugin_data['CoCart tested up to'], '<' ) ) {
					echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_slug . '-update-info' ) . '" data-slug="' . $plugin_slug . '" data-plugin="' . esc_attr( $plugin_file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="update-message notice inline notice-error notice-alt">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

					echo '<p>';
					printf(
						/* translators: %s = Plugin name. */
						esc_html__( 'This version of %s has not been tested with the version of CoCart you have installed.', 'cocart-core' ),
						esc_html( $plugin_data['Name'] )
					);
					echo '</p></div></td></tr>';
					return;
				}
			}
		} // END compatibility_check()

		/**
		 * Displays a message to the user under the listed plugin to enter their license key
		 * if not setup, purchase one or warn them that the license has expired.
		 *
		 * @access public
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $plugin_file Path to the plugin file relative to the plugins directory.
		 * @param array  $plugin_data An array of plugin data.
		 *
		 * @return void
		 */
		public function license_information( $plugin_file, $plugin_data ) {
			if ( ! current_user_can( 'activate_plugins' ) ) {
				return;
			}

			// Should either be false return nothing.
			if ( ! is_array( $plugin_data ) || empty( $plugin_data ) ) {
				return;
			}

			$plugin_slug = $this->get_slug_by_plugin_file( $plugin_file );

			// If plugin is the community plugin, return early as it does not require a license key.
			if ( 'cart-rest-api-for-woocommerce' === $plugin_slug ) {
				return;
			}

			$license_key = CoCart_Helpers::get_license_key( $plugin_data );

			$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );

			// Warn users to provide license key to get updates.
			if ( ! empty( $plugin_slug ) && empty( $license_key ) ) {
				echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_slug . '-update-info' ) . '" data-slug="' . $plugin_slug . '" data-plugin="' . esc_attr( $plugin_file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice notice-info notice-alt inline">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

				if ( is_multisite() ) {
					/* translators: %1 = Plugin name, %2 = A link to the updates page. %3 link to the pricing page */
					$message           = __( 'To enable updates for %1$s, please enter your license key on the <a href="%2$s">Updates</a> page of the main site. If you don\'t have a license key, please consider <a href="%3$s" target="_blank">purchasing one</a> to keep up to date, secure and receive support.', 'cocart-core' );
					$updates_page_link = add_query_arg( array( 'page' => 'cocart-updates' ), get_admin_url( get_main_site_id(), 'admin.php' ) );
				} else {
					/* translators: %1 = Plugin name, %2 = A link to the license modal. %3 link to the pricing page, %4 plugin slug for data attribute */
					$message           = __( 'To enable updates for %1$s, please <a href="#" class="cocart-open-license-modal" data-plugin-name="%1$s" data-plugin="%4$s">enter your license key</a>. If you don\'t have a license key, please consider <a href="%3$s" target="_blank">purchasing one</a> to keep up to date, secure and receive support.', 'cocart-core' );
					$updates_page_link = '#';
				}

				$get_license = CoCart_Helpers::build_shortlink( COCART_STORE_URL . 'pricing/' );

				echo '<p><strong><span class="dashicons dashicons-info"></span></strong> '
					. wp_kses(
						sprintf(
							/* translators: %1 = Plugin name, %2 = A link to the updates page. %3 link to the pricing page */
							$message,
							$plugin_data['Name'],
							$updates_page_link,
							$get_license,
							esc_attr( $plugin_slug )
						),
						array(
							'a' => array(
								'href'             => array(),
								'target'           => array(),
								'class'            => array(),
								'data-plugin-name' => array(),
								'data-plugin'      => array(),
							),
						)
					);

				echo '</p></div></td></tr>';
			}

			// Bail early if license has not expired.
			if ( ! $this->has_license_expired( $plugin_data, $license_key ) ) {
				return;
			}

			echo '<tr class="plugin-update-tr" id="' . esc_attr( $plugin_slug . '-update-warning' ) . '" data-slug="' . $plugin_slug . '" data-plugin="' . esc_attr( $plugin_file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice notice-warning notice-alt inline">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			echo '<p><strong><span class="dashicons dashicons-warning"></span></strong> '
				. sprintf(
					/* translators: %1$s is the plugin name, %2$s and %3$s are a link. */
					esc_html__( '%1$s can\'t be updated because your license has expired. %2$sRenew your license%3$s to get the latest updates for %1$s.', 'cocart-core' ),
					esc_html( $plugin_data['Name'] ),
					'<a href="' . esc_url( COCART_BILLING_URL ) . '">',
					'</a>'
				);

			echo '</p></div></td></tr>';
		} // END license_information()

		/**
		 * Verify license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $license_key License key to verify.
		 * @param string $plugin_slug Plugin slug to verify the license for.
		 *
		 * @return object Returns the license information if valid.
		 */
		public static function verify_license( $license_key, $plugin_slug ) {
			$verify_url = self::$customer_portal_api_url . '/license-keys/validate' . $license_key;

			$plugin_key = self::get_cache_key( $plugin_slug );

			$response = wp_remote_post(
				$verify_url,
				array(
					'body'    => array(
						'id'              => $license_key,
						'organization_id' => 'd2729179-cce9-479c-96cc-43de492209cc', // @todo
					),
					'headers' => array(
						'Content-Type: application/json',
					),
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) && 400 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				cocart_site_transient( 'le_' . $plugin_key, wp_remote_retrieve_body( $response ), 'set', 2 * HOUR_IN_SECONDS );
				return;
			}

			$response = wp_remote_retrieve_body( $response );

			delete_option( 'cocart_license_error' );
			cocart_site_transient( 'lv_' . $plugin_slug, $response, 'set', DAY_IN_SECONDS );

			return $response;
		} // END verify_license()

		/**
		 * Activate license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $license_key License key to activate.
		 * @param string $plugin_slug Plugin slug to activate the license for.
		 */
		public static function activate_license( $license_key, $plugin_slug ) {
			$activation_url = self::$customer_portal_api_url . '/license-keys/activate';

			$plugin_key = self::get_cache_key( $plugin_slug );

			// CoCart_Status::strip_protocol( CoCart_Status::get_site_url() );

			$response = wp_remote_post(
				$activation_url,
				array(
					'headers' => array(
						'Content-Type: application/json',
					),
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) && 400 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				// Store the error message to display to the user for no more than 2 hours.
				cocart_site_transient( 'le_' . $plugin_key, wp_remote_retrieve_body( $response ), 'set', 2 * HOUR_IN_SECONDS );
				return;
			}

			// Clear any previous error message and store the license details.
			cocart_delete_site_transient( 'le_' . $plugin_key );
			update_option( 'cocart_ld_' . $plugin_key, wp_remote_retrieve_body( $response ) );
		} // END activate_license()

		/**
		 * Deactivate license.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 5.0.0 Introduced.
		 *
		 * @param string $license_key License key to deactivate.
		 * @param string $plugin_slug Plugin slug to deactivate the license for.
		 */
		public static function deactivate_license( $license_key, $plugin_slug ) {
			$deactivation_url = self::$customer_portal_api_url . '/license-keys/deactivate';

			$plugin_key = $plugin_key;

			$response = wp_remote_post(
				$deactivation_url,
				array(
					'headers' => array(
						'Content-Type: application/json',
					),
				)
			);

			if (
				is_wp_error( $response )
				|| ( 200 !== wp_remote_retrieve_response_code( $response ) )
				|| empty( wp_remote_retrieve_body( $response ) )
			) {
				cocart_site_transient( 'le_' . $plugin_key, wp_remote_retrieve_body( $response ), 'set', 2 * HOUR_IN_SECONDS );
				return;
			}

			// Clear any previous error message and delete the license details.
			cocart_delete_site_transient( 'le_' . $plugin_key );
			cocart_delete_site_transient( 'lv_' . $plugin_key );
			cocart_delete_site_transient( 'ld_' . $plugin_key );
			delete_option( 'cocart_instance_name' ); // Status of the sites environment.
		} // END deactivate_license()
	} // END class

	return new CoCart_Admin_Updates();
} // END if class exists
