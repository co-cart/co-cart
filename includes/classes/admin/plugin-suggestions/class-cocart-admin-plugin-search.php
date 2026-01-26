<?php
/**
 * Includes cards in the plugin search results when users
 * enter terms that match CoCart add-ons or view all add-ons.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   3.0.0
 * @version 4.3.25
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Plugin_Search' ) ) {

	class CoCart_Admin_Plugin_Search {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			/**
			 * If "cocart_show_plugin_search" filter is set to false,
			 * the plugin search suggestions will not show on the plugin install page.
			 */
			if ( ! CoCart_Helpers::is_cocart_ps_active() ) {
				return;
			}

			add_action( 'current_screen', array( $this, 'start' ) );
			add_action( 'admin_init', array( $this, 'get_suggestions_api_data' ) );
		} // END __construct()

		/**
		 * Add actions and filters only if this is the plugin installation screen.
		 *
		 * @access public
		 *
		 * @param object $screen WP Screen object.
		 */
		public function start( $screen ) {
			if ( 'plugin-install' === $screen->base ) {
				// Filters below inject plugin suggestion.
				add_action( 'admin_enqueue_scripts', array( $this, 'load_plugins_search_script' ) );
				add_filter( 'plugins_api_result', array( $this, 'inject_cocart_suggestion' ), 10, 3 );
				add_filter( 'plugin_install_action_links', array( $this, 'insert_related_links' ), 10, 2 );

				// Filters below are for CoCarts own plugin section.
				if ( self::is_airplane_mode_enabled() !== 'on' ) {
					add_filter( 'plugins_api_result', array( $this, 'cocart_plugins' ), 10, 3 );
				}
				add_filter( 'install_plugins_tabs', array( $this, 'plugins_tab' ) );
				add_filter( 'install_plugins_table_api_args_cocart', array( $this, 'plugin_list_args' ) );
				add_action( 'install_plugins_cocart', array( $this, 'cocart_plugin_dashboard' ) );
			}
		} // END start()

		/**
		 * Add CoCart plugin tab.
		 *
		 * @access public
		 *
		 * @param array $tabs Default plugin tabs.
		 *
		 * @return array $tabs Altered plugin tabs.
		 */
		public function plugins_tab( $tabs ) {
			return array_merge(
				$tabs,
				array(
					'cocart' => 'CoCart',
				)
			);
		} // END plugins_tab()

		/**
		 * Set the CoCart tab to force plugin results we author only.
		 *
		 * This is triggered by "plugins_api_result" action hook.
		 *
		 * @access public
		 *
		 * @param object $args Default arguments.
		 *
		 * @hooked: install_plugins_table_api_args_{$tab}
		 *
		 * @return object $args
		 */
		public function plugin_list_args( $args ) {
			$cocart_args = array(
				'page'     => isset( $_GET['paged'] ) ? max( 0, intval( $_GET['paged'] - 1 ) * $per_page ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				'per_page' => 36,
				'author'   => 'cocartforwc',
			);

			$args = wp_parse_args( $cocart_args, $args );

			return $args;
		} // END plugin_list_args()

		/**
		 * Displays our own plugin dashboard on the plugin install page.
		 *
		 * @access public
		 *
		 * @since 3.0.0 Introduced.
		 * @since 3.5.0 Added condition to only show suggestions if allowed.
		 */
		public function cocart_plugin_dashboard() {
			if ( self::is_airplane_mode_enabled() === 'on' ) {
				?>
				<p>
					<?php
					printf(
						/* translators: %s: CoCart */
						esc_html__( "Airplane Mode is Enabled so we're unable to return plugin suggestions for %s. Please disable Airplane Mode to view results.", 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					);
					?>
				</p>
				<?php
			} else {
				?>
				<div class="cocart-plugin-install-dashboard">
					<p>
						<?php esc_html_e( 'These plugins suggestions are provided to help with decoupling your store for headless needs. Some plugins may or may not support or extend the functionality of CoCart. You may learn more about each of them via their card listed below.', 'cart-rest-api-for-woocommerce' ); ?>
					</p>

					<p>
						<?php esc_html_e( 'Please note: Other than CoCart, we do not provide support for any WooCommerce extension or third party plugin unless stated otherwise. See plugin requirements at the bottom of each plugin card.', 'cart-rest-api-for-woocommerce' ); ?>
					</p>

				</div>
				<?php
				do_action( 'cocart_before_display_plugins_table' );

				if ( self::allow_suggestions() ) {
					display_plugins_table();
				} else {
					?>
					<p>
						<?php
						echo esc_html__( 'Currently only provide suggestions in English.', 'cart-rest-api-for-woocommerce' );
						?>
					</p>
					<?php
				}

				do_action( 'cocart_after_display_plugins_table' );
			}
		} // END cocart_plugin_dashboard()

		/**
		 * Load the search scripts and CSS for Plugin Search Suggestion and tweaks.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.0.17
		 */
		public function load_plugins_search_script() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( COCART_SLUG . '-plugin-suggestions', COCART_URL_PATH . '/assets/js/admin/plugin-suggestions' . $suffix . '.js', array( 'jquery' ), COCART_VERSION, true );
			wp_localize_script(
				COCART_SLUG . '-plugin-suggestions',
				'CoCartPluginSearch',
				array(
					'legend'      => sprintf(
						/* translators: %s: CoCart */
						esc_html__(
							'This suggestion was made by %s, the awesome REST API plugin already installed on your site.',
							'cart-rest-api-for-woocommerce'
						),
						'CoCart'
					),
					'supportText' => esc_html__( 'Learn more about these suggestions.', 'cart-rest-api-for-woocommerce' ),
					'supportLink' => 'https://docs.cocartapi.com/knowledge-base/plugin-suggestions',
				)
			);

			wp_register_style( COCART_SLUG . '-plugin-suggestions', COCART_URL_PATH . '/assets/css/admin/plugin-suggestions' . $suffix . '.css', array(), COCART_VERSION );
			wp_enqueue_style( COCART_SLUG . '-plugin-suggestions' );
			wp_style_add_data( COCART_SLUG . '-plugin-suggestions', 'rtl', 'replace' );
			if ( $suffix ) {
				wp_style_add_data( COCART_SLUG . '-plugin-suggestions', 'suffix', '.min' );
			}
		} // END load_plugins_search_script()

		/**
		 * Get the plugin data from WP.org to populate fields with.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Plugin slug.
		 *
		 * @return array|mixed|object|WP_Error
		 */
		public static function get_wporg_plugin_data( $slug = '' ) {
			$data = get_transient( 'cocart_plugin_data_' . $slug );

			if ( false === $data || is_wp_error( $data ) ) {
				$query_args = array(
					'slug'   => $slug,
					'is_ssl' => is_ssl(),
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'versions'          => false,
						'reviews'           => true,
						'banners'           => false,
						'icons'             => true,
						'active_installs'   => true,
					),
				);

				$data = plugins_api( 'plugin_information', $query_args );

				set_transient( 'cocart_plugin_data_' . $slug, $data, DAY_IN_SECONDS );
			}

			return $data;
		} // END get_wporg_plugin_data()

		/**
		 * Returns all plugin suggestions.
		 *
		 * @access public
		 *
		 * @since 3.1.0 Introduced
		 * @since 3.5.0 Changed to fetch cached data or request new data if out of date.
		 *
		 * @return array
		 */
		public function get_suggestions() {
			$data = get_option(
				'cocart_plugin_suggestions',
				array(
					'suggestions' => array(),
					'updated'     => '',
				)
			);

			// If the options have never been updated, or were updated over a week ago, request suggestions.
			if ( empty( $data['updated'] ) || ( time() - WEEK_IN_SECONDS ) > $data['updated'] ) {
				$data = CoCart_Admin_Plugin_Suggestions_Updater::update_plugin_suggestions();
			}

			return ! empty( $data['suggestions'] ) ? $data['suggestions'] : array();
		} // END get_suggestions()

		/**
		 * Gets data to inject results.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 4.3.25
		 *
		 * @param array $inject Plugin information from WordPress.org.
		 * @param array $data   Plugin information from CoCart.
		 *
		 * @return array Plugin results to inject.
		 */
		public function get_inject_data( $inject, $data ) {
			return array(
				'name'              => empty( $data['third_party'] ) ? sprintf(
					/* translators: %1$s: Add-on name */
					esc_html__( '%1$s Add-on', 'cart-rest-api-for-woocommerce' ),
					$data['name']
				) : $data['name'],
				'slug'              => empty( $data['third_party'] ) ? 'cocart-' . $data['slug'] : $data['slug'],
				'version'           => '',
				'author'            => ! empty( $data['author'] ) ? esc_html( $data['author'] ) : 'CoCart',
				'author_profile'    => 'https://cocartapi.com',
				'short_description' => $data['short_description'],
				'requirement'       => ! empty( $data['requirement'] ) ? $data['requirement'] : '',
				'requires'          => ! empty( $inject['requires'] ) ? $inject['requires'] : $data['requires'],
				'tested'            => ! empty( $inject['tested'] ) ? $inject['tested'] : $data['tested'],
				'requires_php'      => ! empty( $inject['requires_php'] ) ? $inject['requires_php'] : $data['requires_php'],
				'rating'            => ! empty( $inject['rating'] ) ? $inject['rating'] : $data['rating'],
				'num_ratings'       => ! empty( $inject['num_ratings'] ) ? $inject['num_ratings'] : $data['num_ratings'],
				'active_installs'   => ! empty( $inject['active_installs'] ) ? $inject['active_installs'] : $data['active_installs'],
				'last_updated'      => ! empty( $inject['last_updated'] ) ? $inject['last_updated'] : $data['last_updated'],
				'download_link'     => ! empty( $inject['download_link'] ) ? $inject['download_link'] : ( isset( $data['download_link'] ) ? $data['download_link'] : '' ),
				'icons'             => isset( $inject['icons'] ) ? $inject['icons'] : $data['logo'],
				'logo'              => array(
					'1x'  => esc_url( $data['logo'] ),
					'2x'  => esc_url( $data['logo'] ),
					'svg' => esc_url( $data['logo'] ),
				),
				'plugin_does'       => ! empty( $data['plugin_does'] ) ? $data['plugin_does'] : esc_html__( 'Requires', 'cart-rest-api-for-woocommerce' ),
				'purchase'          => ! empty( $data['purchase'] ) ? esc_url( $data['purchase'] ) : '',
				'learn_more'        => ! empty( $data['learn_more'] ) ? esc_url( $data['learn_more'] ) : '',
				'third_party'       => $data['third_party'],
				'wporg'             => isset( $data['wporg'] ) ? true : false,
			);
		} // END get_inject_data()

		/**
		 * Filter plugin fetching API results to inject CoCart add-ons.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param object|WP_Error $result Response object or WP_Error.
		 * @param string          $action The type of information being requested from the Plugin Install API.
		 * @param object          $args   Plugin API arguments.
		 *
		 * @return array $result Updated array of results.
		 */
		public function inject_cocart_suggestion( $result, $action, $args ) {
			// Return current results if we are not searching for suggestion.
			if ( empty( $args->search ) ) {
				return $result;
			}

			// Return current results if we are not on the first page of results.
			if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
				return $result;
			}

			$suggestions = self::get_suggestions();

			$show_suggestion = false;

			// Lowercase, trim, remove punctuation/special chars, decode url, remove 'cart-rest-api-for-woocommerce'.
			$normalized_term = $this->sanitize_search_term( $args->search );

			$plugin_results = array();

			// Re-format current results so we can manipulate them after.
			foreach ( $result->plugins as $key => $value ) {
				$plugin_results[ $value['slug'] ] = $value;
			}

			// Override current results with new format.
			$result->plugins = $plugin_results;

			// Get each add-on and see if we should suggest it to the user.
			foreach ( $suggestions as $slug => $data ) {
				// If searched keywords matched any of the tags, get information.
				if ( false !== stripos( $data['search_terms'] . ', ' . $data['name'], $normalized_term ) ) {

					// If plugin has not already returned in the results then add suggestion.
					if ( ! isset( $plugin_results[ $data['slug'] ] ) ) {

						// If suggestion is hosted on WP.org then get plugin data.
						if ( ! empty( $data['wporg'] ) ) {
							$suggestion_info = (array) self::get_wporg_plugin_data( $slug );

							$inject_data = self::get_inject_data( $suggestion_info, $data );
						} else {
							// Get prepared data to inject the results.
							$inject_data = self::get_inject_data( array(), $data );
						}

						// Override plugin slug to identify suggestion.
						$inject_data['slug'] = 'cocart-suggestion';

						// Override card title and icon.
						$inject_data['name'] = '<h3>' . $inject_data['name'] . '</h3><strong>' . sprintf(
							/* translators: %s: Plugin author */
							esc_html__( 'by %s', 'cart-rest-api-for-woocommerce' ),
							$inject_data['author']
						) . '</strong>';
						$inject_data['icons'] = $inject_data['logo'];

						// Show plugin suggestion.
						$show_suggestion = true;
						break;
					}
				}
			} // END foreach add-on

			// Inject single search result from list of suggestions to the bottom of the results.
			if ( $show_suggestion ) {
				array_push( $result->plugins, $inject_data );
			}

			// Return search results.
			return $result;
		} // END inject_cocart_suggestion()

		/**
		 * Filter plugin fetching API results to return CoCart add-ons.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param object|WP_Error $result Response object or WP_Error.
		 * @param string          $action The type of information being requested from the Plugin Install API.
		 * @param object          $args   Plugin API arguments.
		 *
		 * @return array $result Updated array of results.
		 */
		public function cocart_plugins( $result, $action, $args ) {
			// If we are not browsing just CoCart then return results.
			if ( ! isset( $args->author ) || 'cocartforwc' !== $args->author ) {
				return $result;
			}

			// Should WordPress.ORG fail in returning results successfully.
			if ( is_wp_error( $result ) ) {
				return $result;
			}

			// Get results previously stored if any.
			$saved_results = get_transient( 'cocart_plugin_data' );

			// If saved results don't exist then use the new results and add our suggestions.
			if ( false === $saved_results || is_wp_error( $saved_results ) ) {
				$suggestions = self::get_suggestions();

				$total_items = $result->info['results'];

				// Get each add-on and see if we should suggest it to the user.
				foreach ( $suggestions as $slug => $data ) {
					// If suggestion is hosted on WP.org then get plugin data.
					if ( ! empty( $data['wporg'] ) ) {
						$suggestion_info = (array) self::get_wporg_plugin_data( $slug );

						$inject_data = self::get_inject_data( $suggestion_info, $data );
					} else {
						// Get prepared data to inject the results.
						$inject_data = self::get_inject_data( array(), $data );

						// Override card icon.
						$inject_data['icons'] = $inject_data['logo'];
					}

					// Inserts suggestion as part of results.
					array_push( $result->plugins, $inject_data );

					// Updates the total amount of plugins found.
					$result->info['results'] = $total_items++;
				} // END foreach add-on

				set_transient( 'cocart_plugin_data', $result, DAY_IN_SECONDS );
			} else {
				// Return saved results.
				$result = $saved_results;
			}

			// Return search results.
			return $result;
		} // END cocart_plugins()

		/**
		 * Take a raw search query and return something a bit more standardized and
		 * easy to work with.
		 *
		 * @access private
		 *
		 * @param string $term The raw search term.
		 *
		 * @return string A simplified/sanitized version.
		 */
		private function sanitize_search_term( $term ) {
			$term = strtolower( urldecode( $term ) );

			// remove non-alpha/space chars.
			$term = preg_replace( '/[^a-z ]/', '', $term );

			// remove strings that don't help matches.
			$term = trim( str_replace( array( 'cocart', 'cart-rest-api-for-woocommerce', 'free', 'wordpress', 'woocommerce' ), '', $term ) );

			return $term;
		} // END sanitize_search_term()

		/**
		 * Returns allowed html tags.
		 *
		 * @access public
		 *
		 * @return array
		 */
		public function plugins_allowedtags() {
			return array(
				'a'       => array(
					'href'   => array(),
					'title'  => array(),
					'target' => array(),
				),
				'abbr'    => array( 'title' => array() ),
				'acronym' => array( 'title' => array() ),
				'code'    => array(),
				'pre'     => array(),
				'em'      => array(),
				'strong'  => array(),
				'ul'      => array(),
				'ol'      => array(),
				'li'      => array(),
				'p'       => array(),
				'br'      => array(),
			);
		} // END plugins_allowedtags()

		/**
		 * Appropriate action links for our custom result cards.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param string[] $links  An array of plugin action links. Defaults are links to Details and Install Now.
		 * @param array    $plugin An array of plugin data.
		 *
		 * @return array $links Returns our related links or falls back to default.
		 */
		public function insert_related_links( $links, $plugin ) {
			// Alter action links if not hosted on WordPress dot ORG.
			if ( empty( $plugin['wporg'] ) ) {
				if ( isset( $_GET['tab'] ) && 'cocart' === $_GET['tab'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					$links = self::get_action_links( $plugin, $links );
				} elseif ( 'cocart-suggestion' === $plugin['slug'] ) {
					$links = self::get_suggestion_links( $plugin );
				}

				// Add link pointing to a relevant page.
				if ( ! empty( $plugin['learn_more'] ) ) {
					$links['1'] = '<a
						class="cocart-suggestion__learn-more button"
						href="' . esc_url( $plugin['learn_more'] ) . '"
						target="_blank"
						rel="noopener noreferrer"
						data-addon="' . esc_attr( $plugin['slug'] ) . '"
						data-track="learn_more"
						>' . esc_html__( 'Learn more', 'cart-rest-api-for-woocommerce' ) . ' <span class="dashicons dashicons-external"></span></a>';

					// Remove main button as it will be disabled.
					unset( $links['0'] );
				}
			}

			return $links;
		} // END insert_related_links()

		/**
		 * Returns related links for suggested plugin.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param array $plugin An array of plugin data.
		 *
		 * @return array $links Related links after change.
		 */
		public function get_suggestion_links( $plugin ) {
			// Resets the links.
			$links = array();

			return self::get_action_links( $plugin, $links );
		} // END get_suggestion_links()

		/**
		 * Returns action links.
		 *
		 * @access public
		 *
		 * @since   3.0.0 Introduced.
		 * @version 3.1.0
		 *
		 * @param array $plugin An array of plugin data.
		 * @param array $links  Related links before change.
		 *
		 * @return array $links Related links after change.
		 */
		public function get_action_links( $plugin, $links = array() ) {
			$plugins_allowed_tags = self::plugins_allowedtags();

			if ( in_array( $plugin['slug'], self::get_suggestions() ) ) {
				$title          = wp_kses( $plugin['name'], $plugins_allowed_tags );
				$version        = wp_kses( $plugin['version'], $plugins_allowed_tags );
				$name           = wp_strip_all_tags( $title . ' ' . $version );
				$requires_php   = isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : null;
				$requires_wp    = isset( $plugin['requires'] ) ? $plugin['requires'] : null;
				$compatible_php = is_php_version_compatible( $requires_php );
				$compatible_wp  = is_wp_version_compatible( $requires_wp );
				$tested_wp      = ( empty( $plugin['tested'] ) || version_compare( get_bloginfo( 'version' ), $plugin['tested'], '<=' ) );

				if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
					$status = install_plugin_install_status( $plugin );

					switch ( $status['status'] ) {
						case 'install':
							if ( $status['url'] ) {
								if ( $compatible_php && $compatible_wp ) {
									// $nonce = wp_create_nonce( 'install-cocart-plugin_' . $plugin['slug'] );
									// $url   = self_admin_url( 'update.php?action=install-cocart-plugin&plugin=' . $plugin['slug'] . '&_wpnonce=' . $nonce );
									if ( ! empty( $plugin['purchase'] ) ) { // @TODO: Add check if CoCart license is active to download and install if source available.
										$links['cocart-purchase'] = sprintf(
											'<a class="cocart-plugin-primary button" data-slug="%s" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s" data-name="%s">%s</a>',
											esc_attr( $plugin['slug'] ),
											esc_url( $plugin['purchase'] ),
											esc_attr(
												sprintf(
													/* translators: %s: Plugin name */
													__( 'Purchase %s now', 'cart-rest-api-for-woocommerce' ),
													$name
												)
											),
											esc_attr( $name ),
											__( 'Purchase Now', 'cart-rest-api-for-woocommerce' )
										);
									}
								} else {
									$links['cocart-not-compatible'] = sprintf(
										'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
										__( 'Not Compatible', 'cart-rest-api-for-woocommerce' )
									);
								}
							}

							break;

							break;
					} // END switch

					/**
					 * Filters the action links for plugin suggestions.
					 *
					 * @since 3.1.0 Introduced.
					 *
					 * @param string[] $links  An array of plugin action links.
					 * @param array    $status Data about the plugin retrieved from the API.
					 * @param array    $plugin An array of plugin data.
					 * @param string   $name   Plugin title and version
					 */
					$links = apply_filters( 'cocart_plugin_search_action_links', $links, $status, $plugin, $name );
				} // END if user can install or update plugins.
			} // END if plugin matches.

			return $links;
		} // END get_action_links()

		/**
		 * Checks if Airplane mode is enabled.
		 *
		 * @access public
		 *
		 * @since 3.1.0 Introduced.
		 *
		 * @return string Status of Airplane mode.
		 */
		public function is_airplane_mode_enabled() {
			// Pull our status from the options table.
			$option = get_site_option( 'airplane-mode' );

			// Backup check for regular options table.
			if ( false === $option ) {
				$option = get_option( 'airplane-mode' );
			}

			// Return the option flag.
			return 'on' === $option;
		} // END is_airplane_mode_enabled()

		/**
		 * Should suggestions be displayed?
		 *
		 * @access public
		 *
		 * @since 3.5.0 Introduced.
		 *
		 * @return bool
		 */
		public function allow_suggestions() {
			// We currently only support English suggestions.
			$locale             = get_locale();
			$suggestion_locales = array(
				'en_AU',
				'en_CA',
				'en_GB',
				'en_NZ',
				'en_US',
				'en_ZA',
			);

			if ( ! in_array( $locale, $suggestion_locales, true ) ) {
				return false;
			}

			// Suggestions are only displayed if user can install plugins.
			if ( ! current_user_can( 'install_plugins' ) ) {
				return false;
			}

			return true;
		} // END allow_suggestions()

		/**
		 * Pull suggestion data from options. This is retrieved from a remote endpoint.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @since 3.10.8 Introduced.
		 */
		public static function get_suggestions_api_data() {
			if ( ! method_exists( '\ActionScheduler', 'is_initialized' ) ) {
				return;
			}

			$data = get_option(
				'cocart_plugin_suggestions',
				array(
					'suggestions' => array(),
					'updated'     => '',
				)
			);

			// If the options have never been updated, or were updated over a week ago, queue update.
			if ( empty( $data['updated'] ) || ( time() - WEEK_IN_SECONDS ) > $data['updated'] ) {
				$next = WC()->queue()->get_next( 'cocart_update_plugin_suggestions' );
				if ( ! $next ) {
					WC()->queue()->cancel_all( 'cocart_update_plugin_suggestions' );
					WC()->queue()->schedule_single( time() + DAY_IN_SECONDS, 'cocart_update_plugin_suggestions' );
				}
			}
		} // END get_suggestions_api_data()
	} // END class

	return new CoCart_Admin_Plugin_Search();

} // END if class exists
