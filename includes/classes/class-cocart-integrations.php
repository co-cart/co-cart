<?php
/**
 * CoCart - Integrations.
 *
 * Central registry, enable/disable persistence, and loader for all built-in
 * integrations (compatibility modules and third-party plugin support).
 * Each integration file is responsible for its own gating.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   4.9.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Integrations' ) ) {

	/**
	 * Central registry for all CoCart integrations.
	 *
	 * Integrations are enabled by default. An absent key in the stored option is
	 * treated as enabled, preserving existing behaviour on installs that upgrade
	 * before interacting with this system.
	 */
	class CoCart_Integrations {

		/**
		 * All registered integrations.
		 *
		 * @var array<string, array>
		 */
		private static $registry = array();

		/**
		 * Map of slug → absolute file path for integrations that have a loadable file.
		 *
		 * @var array<string, string>
		 */
		private static $integration_files = array();

		/**
		 * Cached value of the 'cocart_integrations' option.
		 * Null until first access to avoid a DB hit on every request.
		 *
		 * @var array<string, bool>|null
		 */
		private static $settings = null;

		// ── Registry ─────────────────────────────────────────────────────────────

		/**
		 * Register an integration.
		 *
		 * External registrants (e.g. CoCart Plus, hooking `cocart_register_integrations`)
		 * can pass `$file` to have their integration file included automatically by
		 * `load()`, the same way built-in integrations are loaded via `add()`.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Unique slug for the integration (e.g. 'taxjar').
		 * @param array  $args { Integration arguments.
		 *     @type string $name             Human-readable name.
		 *     @type string $description      Short description shown on the admin page.
		 *     @type string $type             'compatibility', 'third-party', or 'plus'.
		 *     @type string $detect_class     PHP class whose existence signals the required
		 *                                   plugin is active. Empty = always loadable.
		 *     @type string $doc_url          Documentation URL (optional).
		 *     @type string $icon             Absolute URL to a 48×48 icon image (optional).
		 *     @type bool   $upgrade_required True = show Upgrade to Pro button; no toggle.
		 *     @type bool   $coming_soon True = show "Coming soon" label next to the Upgrade button.
		 * }
		 * @param string $file Optional. Absolute path to a file to include when this
		 *                     integration is loaded.
		 */
		public static function register( string $slug, array $args, string $file = '' ): void {
			$defaults = array(
				'name'             => '',
				'description'      => '',
				'type'             => 'third-party',
				'detect_class'     => '',
				'doc_url'          => '',
				'icon'             => '',
				'upgrade_required' => false,
				'coming_soon'      => false,
			);

			self::$registry[ $slug ] = array_merge( $defaults, $args );

			if ( '' !== $file ) {
				self::$integration_files[ $slug ] = $file;
			}
		} // END register()

		/**
		 * Return all registered integrations.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return array<string, array>
		 */
		public static function get_all(): array {
			return self::$registry;
		} // END get_all()

		/**
		 * Check whether an integration is enabled by the user.
		 *
		 * Absent key = enabled (default-on, preserves behaviour on existing installs).
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Integration slug.
		 *
		 * @return bool
		 */
		public static function is_enabled( string $slug ): bool {
			$settings = self::get_settings();

			return ! isset( $settings[ $slug ] ) || (bool) $settings[ $slug ];
		} // END is_enabled()

		/**
		 * Check whether an integration can be enabled (i.e. its required plugin is active).
		 *
		 * When no detect_class is set the integration is always considered loadable.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Integration slug.
		 *
		 * @return bool
		 */
		public static function can_be_enabled( string $slug ): bool {
			if ( ! isset( self::$registry[ $slug ] ) ) {
				return false;
			}

			$detect = self::$registry[ $slug ]['detect_class'];

			if ( '' === $detect ) {
				return true;
			}

			return class_exists( $detect );
		} // END can_be_enabled()

		/**
		 * Enable an integration.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Integration slug.
		 */
		public static function enable( string $slug ): void {
			$settings          = self::get_settings();
			$settings[ $slug ] = true;
			self::save_settings( $settings );
		} // END enable()

		/**
		 * Disable an integration.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @param string $slug Integration slug.
		 */
		public static function disable( string $slug ): void {
			$settings          = self::get_settings();
			$settings[ $slug ] = false;

			self::save_settings( $settings );
		} // END disable()

		/**
		 * Return all integrations that are both enabled by the user and loadable.
		 *
		 * @access public
		 *
		 * @static
		 *
		 * @return array<string, array>
		 */
		public static function get_enabled_integrations(): array {
			return array_filter(
				self::$registry,
				function ( $slug ) {
					return self::is_enabled( $slug ) && self::can_be_enabled( $slug );
				},
				ARRAY_FILTER_USE_KEY
			);
		} // END get_enabled_integrations()

		// ── Loader ───────────────────────────────────────────────────────────────

		/**
		 * Register all built-in integrations and load their files.
		 *
		 * @access public
		 *
		 * @static
		 */
		public static function load(): void {
			self::register_all();

			/**
			 * Hook: Fires after all built-in integrations are registered.
			 * Use this to register additional integrations from CoCart add-ons or third-party plugins.
			 *
			 * @since 4.9.0 Introduced.
			 */
			do_action( 'cocart_register_integrations' );

			foreach ( self::$integration_files as $file ) {
				include_once $file;
			}
		} // END load()

		/**
		 * Register all built-in integrations.
		 *
		 * Registrations are unconditional — unavailable or requires higher tier.
		 * Integrations still appear on the admin page.
		 *
		 * @access private
		 *
		 * @static
		 */
		private static function register_all(): void {
			$base      = COCART_ABSPATH . 'includes/classes/integrations/';
			$icons_url = COCART_URL_PATH . '/assets/images/integration/';

			// ── Compatibility ─────────────────────────────────────────────────────

			self::add(
				'advanced-shipping-packages',
				array(
					'name'         => 'Advanced Shipping Packages for WooCommerce',
					'description'  => __( 'Names shipping packages correctly when using the Advanced Shipping Packages extension.', 'cart-rest-api-for-woocommerce' ),
					'type'         => 'compatibility',
					'detect_class' => 'Advanced_Shipping_Packages_for_WooCommerce',
					'doc_url'      => 'https://woocommerce.com/products/woocommerce-advanced-shipping-packages/',
					'icon'         => $icons_url . 'advanced-shipping-packages.png',
				),
				$base . 'compatibility/class-cocart-advanced-shipping-packages.php'
			);

			self::add(
				'free-gift-coupons',
				array(
					'name'         => 'Free Gift Coupons',
					'description'  => __( 'Prevents quantity manipulation of free gift cart items and displays them correctly.', 'cart-rest-api-for-woocommerce' ),
					'type'         => 'compatibility',
					'detect_class' => 'WC_Free_Gift_Coupons',
					'doc_url'      => 'https://woocommerce.com/products/free-gift-coupons/',
					'icon'         => $icons_url . 'free-gift-coupons.jpg',
				),
				$base . 'compatibility/class-cocart-free-gift-coupons.php'
			);

			// ── Third Party ───────────────────────────────────────────────────────

			self::add(
				'litespeed-cache',
				array(
					'name'         => 'LiteSpeed Cache',
					'description'  => __( 'Prevents LiteSpeed Cache from caching CoCart API responses.', 'cart-rest-api-for-woocommerce' ),
					'type'         => 'third-party',
					'detect_class' => 'LiteSpeed\\Core',
					'doc_url'      => 'https://wordpress.org/plugins/litespeed-cache/',
					'icon'         => $icons_url . 'litespeed.svg',
				),
				$base . 'third-party/class-cocart-plugin-litespeed-cache.php'
			);

			self::add(
				'taxjar',
				array(
					'name'         => 'TaxJar',
					'description'  => __( 'Allows TaxJar to recalculate cart taxes when a CoCart API request is made.', 'cart-rest-api-for-woocommerce' ),
					'type'         => 'third-party',
					'detect_class' => 'WC_Taxjar',
					'doc_url'      => 'https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/',
					'icon'         => '',
				),
				$base . 'third-party/class-cocart-plugin-taxjar.php'
			);

			// ── CoCart Plus (metadata-only — no file to load) ─────────────────────

			self::register(
				'two-factor-authentication',
				array(
					'name'             => 'Two-Factor Authentication (2FA)',
					'description'      => __( 'Provides support for WordPress Two Factor Authentication during CoCart login endpoint.', 'cart-rest-api-for-woocommerce' ),
					'type'             => 'plus',
					'detect_class'     => 'Two_Factor_Core',
					'doc_url'          => 'https://wordpress.org/plugins/two-factor/',
					'icon'             => $icons_url . 'two-factor.svg',
					'upgrade_required' => true,
					'coming_soon'      => true,
				)
			);

			self::register(
				'acf',
				array(
					'name'             => 'Advanced Custom Fields (ACF)',
					'description'      => __( 'Returns ACF fields for Products API.', 'cart-rest-api-for-woocommerce' ),
					'type'             => 'plus',
					'detect_class'     => 'ACF',
					'doc_url'          => 'https://www.advancedcustomfields.com/',
					'icon'             => $icons_url . 'acf.svg',
					'upgrade_required' => true,
					'coming_soon'      => true,
				)
			);
		} // END register_all()

		/**
		 * Register a single integration and record its file path.
		 *
		 * @access private
		 *
		 * @static
		 *
		 * @param string $slug Integration slug.
		 * @param array  $args Registration metadata.
		 * @param string $file Absolute path to the integration file.
		 */
		private static function add( string $slug, array $args, string $file ): void {
			self::register( $slug, $args, $file );
		} // END add()

		// ── Settings persistence ──────────────────────────────────────────────────

		/**
		 * Lazy-load and return the stored enabled/disabled settings.
		 *
		 * @access private
		 *
		 * @static
		 *
		 * @return array<string, bool>
		 */
		private static function get_settings(): array {
			if ( null === self::$settings ) {
				self::$settings = (array) get_option( 'cocart_integrations', array() );
			}

			return self::$settings;
		} // END get_settings()

		/**
		 * Persist settings to the database and update the in-memory cache.
		 *
		 * @access private
		 *
		 * @static
		 *
		 * @param array<string, bool> $settings Integration enabled/disabled state keyed by slug.
		 */
		private static function save_settings( array $settings ): void {
			self::$settings = $settings;

			update_option( 'cocart_integrations', $settings );
		} // END save_settings()
	} // END class.
} // END if class exists.
