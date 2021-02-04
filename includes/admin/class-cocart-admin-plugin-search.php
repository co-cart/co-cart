<?php
/**
 * Includes cards in the plugin search results when users 
 * enter terms that match CoCart add-ons.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart\Admin
 * @since    2.*.*
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Plugin_Search' ) ) {

	class CoCart_Plugin_Search {

		/**
		 * Constructor.
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'current_screen', array( $this, 'start' ) );
		} // END __construct()

		/**
		 * Add actions and filters only if this is the plugin installation screen and it's the first page.
		 *
		 * @param object $screen WP Screen object.
		 */
		public function start( $screen ) {
			if ( 'plugin-install' === $screen->base && ( ! isset( $_GET['paged'] ) || 1 === intval( $_GET['paged'] ) ) ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'load_plugins_search_script' ) );
				add_filter( 'plugins_api_result', array( $this, 'inject_cocart_suggestion' ), 10, 3 );
				add_filter( 'plugin_install_action_links', array( $this, 'inset_related_links' ), 10, 2 );
			}
		}

		/**
		 * Load the search scripts and CSS for Plugin Search Suggestion.
		 *
		 * @access public
		 */
		public function load_plugins_search_script() {
			$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_script( COCART_SLUG . '-search-suggestion', COCART_URL_PATH . '/assets/js/admin/plugin-search' . $suffix . '.js', array( 'jquery' ), COCART_VERSION, true );
			wp_localize_script(
				COCART_SLUG . '-search-suggestion',
				'CoCartPluginSearch',
				array(
					'purchaseAddon' => esc_html__( 'Purchase Addon', 'cart-rest-api-for-woocommerce' ),
					'getStarted'    => esc_html__( 'Get started', 'cart-rest-api-for-woocommerce' ),
					'activated'     => esc_html__( 'Activated', 'cart-rest-api-for-woocommerce' ),
					'legend'        => sprintf( 
						esc_html__( 'This suggestion was made by %s, the awesome REST API plugin already installed on your site.',
						'cart-rest-api-for-woocommerce' ), 'CoCart'
					),
					'supportText'   => esc_html__(
						'Learn more about these suggestions.',
						'cart-rest-api-for-woocommerce'
					),
					'supportLink'   => 'https://cocart.xyz/search-suggestion/',
				)
			);

			wp_register_style( COCART_SLUG . '-search-suggestion', COCART_URL_PATH . '/assets/css/admin/plugin-search' . $suffix . '.css', array(), COCART_VERSION );
			wp_enqueue_style( COCART_SLUG . '-search-suggestion' );
		} // END load_plugins_search_script()

		/**
		 * Get the plugin repo's data for CoCart to populate the fields with.
		 *
		 * @access public
		 * @static
		 * @return array|mixed|object|WP_Error
		 */
		public static function get_cocart_plugin_data() {
			$data = get_transient( 'cocart_plugin_data' );

			if ( false === $data || is_wp_error( $data ) ) {
				$query_args = array(
					'slug'   => 'cart-rest-api-for-woocommerce',
					'is_ssl' => is_ssl(),
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'versions'          => false,
						'reviews'           => true,
						'banners'           => false,
						'icons'             => false,
						'active_installs'   => true,
					),
				);

				$data = plugins_api( 'plugin_information', $query_args );

				set_transient( 'cocart_plugin_data', $data, DAY_IN_SECONDS );
			}

			return $data;
		} // END get_cocart_plugin_data()

		/**
		 * Create a list of CoCart add-ons.
		 *
		 * @access public
		 * @return array List of add-ons.
		 */
		public function get_addons_list() {
			return array(
				'acf' => array(
					'name'              => esc_html__( 'Advanced Custom Fields', 'cart-rest-api-for-woocommerce' ),
					'plugin'            => 'acf',
					'search_terms'      => 'advanced, acf, fields, custom fields, meta, repeater',
					'short_description' => esc_html__( 'Returns all custom meta data saved for all products using Advanced Custom Fields.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => COCART_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => sprintf( esc_html__( '%s Products' ), 'CoCart' ),
					'learn_more'        => esc_url( 'https://cocart.xyz/add-ons/advanced-custom-fields/' ),
					'third_party'       => false,
				),
				'yoast-seo' => array(
					'name'              => esc_html__( 'Yoast SEO', 'cart-rest-api-for-woocommerce' ),
					'plugin'            => 'yoast-seo',
					'search_terms'      => 'yoast',
					'short_description' => esc_html__( 'Returns all Yoast SEO data for all products, product categories and tags.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => COCART_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => sprintf( esc_html__( '%s Products' ), 'CoCart' ),
					'learn_more'        => esc_url( 'https://cocart.xyz/add-ons/yoast-seo/' ),
					'third_party'       => false,
				),
				/*'wpml' => array(
					'name'              => esc_html__( 'Advanced Custom Fields', 'cart-rest-api-for-woocommerce' ),
					'plugin'            => 'wpml',
					'search_terms'      => '',
					'short_description' => esc_html__( 'Returns all custom meta data saved for all products using Advanced Custom Fields.', 'cart-rest-api-for-woocommerce' ),
					'logo'              => COCART_URL_PATH . '/assets/images/logo.jpg',
					'requirement'       => '',
					'learn_more'        => esc_url( 'https://cocart.xyz/add-ons/wpml/' ),
					'third_party'       => false,
				),*/
			);
		} // END get_addons_list()

		/**
		 * Create a list of CoCart supported third party plugins.
		 *
		 * @access public
		 * @return array List of third party plugins.
		 */
		public function get_third_party_list() {
			return array(
				'wcnyp' => array(
					'name'              => sprintf( esc_html__( '%s Name Your Price', 'cart-rest-api-for-woocommerce' ), 'WooCommerce' ),
					'plugin'            => 'woocommerce-name-your-price',
					'author'            => 'Kathy Darling',
					'search_terms'      => 'nyp',
					'short_description' => esc_html__( 'Let customers pay what they want with Name Your Price', 'cart-rest-api-for-woocommerce' ),
					'logo'              => 'https://ps.w.org/woocommerce/assets/icon-128x128.png?rev=2366418',
					'requirement'       => false,
					'purchase'          => esc_url( '' ),
					'learn_more'        => esc_url( 'https://woocommerce.com/products/name-your-price/' ),
					'third_party'       => true,
				)
			);
		} // END get_third_party_list()

		/**
		 * Filter plugin fetching API results to inject CoCart add-ons.
		 *
		 * @access public
		 * @param  object|WP_Error $result Response object or WP_Error.
		 * @param  string          $action The type of information being requested from the Plugin Install API.
		 * @param  object          $args   Plugin API arguments.
		 * @return array Updated array of results
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

			// Get CoCart plugin data.
			$inject = (array) self::get_cocart_plugin_data();

			// Return current results if failed to get plugin data.
			if ( is_wp_error( $inject ) ) {
				return $result;
			}

			$suggestions = array_merge( self::get_addons_list(), self::get_third_party_list() );

			// Get each add-on and see if we should suggest it to the user.
			foreach( $suggestions as $slug => $data ) {
				$show_addon = false;

				$inject_data = array(
					'name'              => ! isset( $data['third_party'] ) ? sprintf( esc_html__( '%1$s Add-on', 'cart-rest-api-for-woocommerce' ), $data['name'] ) : $data['name'],
					'slug'              => 'cocart-plugin-search',
					'plugin'            => $data['plugin'],
					'version'           => '',
					'author'            => ! empty( $data['author'] ) ? esc_html( $data['author'] ) : 'CoCart',
					'author_profile'    => 'https://cocart.xyz',
					'requires'          => $inject['requires'],
					'tested'            => $inject['tested'],
					'requires_php'      => $inject['requires_php'],
					'rating'            => $inject['rating'],
					'num_ratings'       => $inject['num_ratings'],
					'active_installs'   => $inject['active_installs'],
					'last_updated'      => $inject['last_updated'],
					'short_description' => $data['short_description'],
					'download_link'     => '',
					'icons'             => $inject['icons'],
					'logo'              => array(
						'1x'  => esc_url( $data['logo'] ),
						'2x'  => esc_url( $data['logo'] ),
						'svg' => esc_url( $data['logo'] ),
					),
					'purchase'          => ! empty( $data['purchase'] ) ? esc_url( $data['purchase'] ): esc_url( 'https://cocart.xyz/pro/#pricing' ),
					'learn_more'        => esc_url( $data['learn_more'] ),
					'third_party'       => $data['third_party']
				);

				// Override card title and icon.
				$inject_data['name'] = '<h3>' . $inject_data['name'] . '</h3><strong>by ' . $inject_data['author'] . '</strong>';
				$inject_data['icons'] = $inject_data['logo'];

				// Lowercase, trim, remove punctuation/special chars, decode url, remove 'cart-rest-api-for-woocommerce'.
				$normalized_term = $this->sanitize_search_term( $args->search );

				// Show if searched keywords matched any of the tags.
				if ( false !== stripos( $data['search_terms'] . ', ' . $data['name'], $normalized_term ) ) {
					$show_addon = true;
					break;
				}
			} // END foreach add-on

			// Inject result if we are to show them.
			if ( $show_addon ) {
				array_unshift( $result->plugins, $inject_data );
			}

			return $result;
		} // END inject_cocart_suggestion()

		/**
		 * Take a raw search query and return something a bit more standardized and
		 * easy to work with.
		 *
		 * @access private
		 * @param  string $term The raw search term.
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
		 * Put some more appropriate links on our custom result cards.
		 *
		 * @access public
		 * @param array $links Related links.
		 * @param array $plugin Plugin result information.
		 */
		public function inset_related_links( $links, $plugin ) {
			if ( 'cocart-plugin-search' !== $plugin['slug'] ) {
				return $links;
			}

			$links = array();

			/*$links['cocart_get_started'] = '<a
				id="plugin-select-settings"
				class="cocart-plugin-search__primary cocart-plugin-search__get-started button"
				href="' . esc_url( Redirect::get_url( 'plugin-hint-learn-' . $plugin['plugin'] ) ) . '"
				data-plugin="' . esc_attr( $plugin['plugin'] ) . '"
				>' . esc_html__( 'Get started', 'cart-rest-api-for-woocommerce' ) . '</a>';*/

			$links['cocart_purchase_addon'] = '<a
				class="cocart-plugin-search__primary button"
				href="' . esc_url( $plugin['purchase'] ) . '"
				target="_blank"
				data-addon="' . esc_attr( $plugin['plugin'] ) . '"
				>' . esc_html__( 'Purchase', 'cart-rest-api-for-woocommerce' ) . '</a>';

			// Add link pointing to a relevant doc page in cocart.xyz only if the Get Started button isn't displayed.
			if ( ! empty( $plugin['learn_more'] ) ) {
				$links['cocart_learn_more'] = '<a
					class="cocart-plugin-search__learn-more"
					href="' . esc_url( $plugin['learn_more'] ) . '"
					target="_blank"
					data-addon="' . esc_attr( $plugin['plugin'] ) . '"
					data-track="learn_more"
					>' . esc_html__( 'Learn more', 'cart-rest-api-for-woocommerce' ) . '</a>';
			}

			return $links;
		} // END inset_related_links()

	} // END class

} // END if class exists

return new CoCart_Plugin_Search();
