<?php
/**
 * Abstract: CoCart Updates.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Settings
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class to provide plugin updates.
 */
abstract class CoCart_Plugin_Updates {

	/**
	 * Customer Portal API URL
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @var string
	 */
	protected static $customer_portal_api_url = 'https://api.polar.sh/v1/customer-portal';

	/**
	 * Expected plugin data.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @var array
	 */
	protected static $plugins = array(
		'cart-rest-api-for-woocommerce.php' => 'cart-rest-api-for-woocommerce', // Community.
		'cocart-core.php'                   => 'cocart-core', // temp
		'cocart-basic.php'                  => 'cocart-basic',
		'cocart-plus.php'                   => 'cocart-plus',
		'cocart-pro.php'                    => 'cocart-pro',
		'cocart-white-label.php'            => 'cocart-white-label',
		'cocart-seo-pack.php'               => 'cocart-seo-pack',
	);

	/**
	 * Product slug to class map.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @var array
	 */
	protected static $slug_to_class_map = array(
		'cart-rest-api-for-woocommerce' => 'CoCart', // Community.
		'cocart-core'                   => 'CoCart', // temp
		'cocart-basic'                  => 'CoCart',
		'cocart-plus'                   => 'CoCart_Plus',
		'cocart-pro'                    => 'CoCart_Pro',
		'cocart-white-label'            => 'CoCart_White_Label',
		'cocart-seo-pack'               => 'CoCart\SEOPack\Plugin',
		'cocart-wpcli-addon'            => 'CoCart\WPCLI\Plugin',
	);

	/**
	 * Counts the number of plugin update checks.
	 *
	 * @access public
	 *
	 * @var integer
	 */
	public $checked = 0;

	/**
	 * Retrieves all installed WordPress plugins.
	 *
	 * @access protected
	 *
	 * @return array The plugins.
	 */
	protected function get_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins();
	} // END get_plugins()

	/**
	 * Checks if the given plugin file belongs to an active plugin.
	 *
	 * @access protected
	 *
	 * @param string $plugin_file The file path to the plugin.
	 *
	 * @return bool True when plugin is active.
	 */
	protected function is_plugin_active( $plugin_file ) {
		return is_plugin_active( $plugin_file );
	} // END is_plugin_active()

	/**
	 * Provides a list of plugin filenames.
	 *
	 * @access public
	 *
	 * @return string[] List of plugin filenames with their slugs.
	 */
	public function get_plugin_filenames() {
		return self::$plugins;
	} // END get_plugin_filenames()

	/**
	 * Finds the plugin file.
	 *
	 * @access public
	 *
	 * @param string $plugin_slug The plugin slug to search.
	 *
	 * @return bool|string Plugin file when installed, False when plugin isn't installed.
	 */
	public function get_plugin_file( $plugin_slug ) {
		$plugins            = $this->get_plugins();
		$plugin_files       = array_keys( $plugins );
		$target_plugin_file = array_search( $plugin_slug, $this->get_plugin_filenames(), true );

		if ( ! $target_plugin_file ) {
			return false;
		}

		foreach ( $plugin_files as $plugin_file ) {
			if ( strpos( $plugin_file, $target_plugin_file ) !== false ) {
				return $plugin_file;
			}
		}

		return false;
	} // END get_plugin_file()

	/**
	 * Checks if there are any installed plugins.
	 *
	 * @access public
	 *
	 * @return bool True when there are installed CoCart plugins.
	 */
	public function has_installed_plugins() {
		$installed_plugins = $this->get_installed_plugins();

		return ! empty( $installed_plugins );
	} // END has_installed_plugins()

	/**
	 * Checks if the plugin is installed and activated in WordPress.
	 *
	 * @access public
	 *
	 * @param string $slug The class' slug.
	 *
	 * @return bool True when installed and activated.
	 */
	public function is_installed( $slug ) {
		$slug_to_class_map = $this->slug_to_class_map;

		if ( ! isset( $slug_to_class_map[ $slug ] ) ) {
			return false;
		}

		return class_exists( $slug_to_class_map[ $slug ] );
	} // END is_installed()

	/**
	 * Checks if the given plugin_file belongs to a CoCart plugin.
	 *
	 * @access protected
	 *
	 * @param string $plugin_file Path to the plugin.
	 *
	 * @return bool True when plugin file is for a CoCart plugin.
	 */
	protected function is_cocart_plugin( $plugin_file ) {
		return $this->get_slug_by_plugin_file( $plugin_file ) !== '';
	} // END is_cocart_plugin()

	/**
	 * Retrieves the plugin slug by given plugin file path.
	 *
	 * @access protected
	 *
	 * @param string $plugin_file The file path to the plugin.
	 *
	 * @return string The slug when found or empty string when not.
	 */
	protected function get_slug_by_plugin_file( $plugin_file ) {
		$plugins = self::$plugins;

		foreach ( $plugins as $plugin => $plugin_slug ) {
			if ( strpos( $plugin_file, $plugin ) !== false ) {
				return $plugin_slug;
			}
		}

		return '';
	} // END get_slug_by_plugin_file()

	/**
	 * Retrieves the installed CoCart plugin.
	 *
	 * @access public
	 *
	 * @return array The installed plugins.
	 */
	public function get_installed_plugins() {
		return array_filter( $this->get_plugins(), array( $this, 'is_cocart_plugin' ), ARRAY_FILTER_USE_KEY );
	} // END get_installed_plugins()

	/**
	 * Retrieves a list of active addons.
	 *
	 * @access protected
	 *
	 * @return array The active addons.
	 */
	protected function get_active_plugins() {
		return array_filter( $this->get_installed_plugins(), array( $this, 'is_plugin_active' ), ARRAY_FILTER_USE_KEY );
	} // END get_active_plugins()

	/**
	 * Retrieves a list of versions for each plugin.
	 *
	 * @access public
	 *
	 * @return array The plugin versions.
	 */
	public function get_installed_plugin_versions() {
		$plugin_versions = array();

		foreach ( $this->get_installed_plugins() as $plugin_file => $installed_addon ) {
			$plugin_versions[ $this->get_slug_by_plugin_file( $plugin_file ) ] = $installed_addon['Version'];
		}

		return $plugin_versions;
	} // END get_installed_plugins_versions()

	/**
	 * Map the API response to the format WordPress expects.
	 * Only the bare minimum is required for the API response to work.
	 *
	 * @access public
	 *
	 * @param string $plugin_slug The plugin slug to map.
	 *
	 * @return array The mapped plugin data for the API response.
	 */
	public function plugin_map( $plugin_slug ) {
		if ( ! function_exists( 'get_plugin_data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugin_file = $this->get_plugin_file( $plugin_slug );

		$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin_file );

		return array(
			'id'           => 'cocart-headless/' . $plugin_slug,
			'slug'         => $plugin_slug,
			'new_version'  => $plugin_data['Version'],
			'url'          => $plugin_data['PluginURI'],
			'requires'     => $plugin_data['RequiresWP'],
			'requires_php' => $plugin_data['RequiresPHP'],
			'package'      => '', // Leave blank, will be provided via `upgrader_package_options` filter when the update is being installed.
		);
	} // END plugin_map()
}
