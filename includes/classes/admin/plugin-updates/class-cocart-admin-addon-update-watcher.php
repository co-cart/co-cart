<?php
/**
 * CoCart - Add-on Update Watcher.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   4.0.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables CoCart add-on auto updates when CoCart is enabled and the other way around.
 *
 * Also removes the auto-update toggles from the CoCart add-ons.
 *
 * @since 4.0.0 Introduced.
 */
class CoCart_Admin_Addon_Update_Watcher {

	/**
	 * ID string used by WordPress to identify the core plugin of CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var string
	 */
	public static $cocart_core_plugin_id = 'cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php';

	/**
	 * A list of CoCart add-on identifiers.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @var array
	 */
	public static $add_on_plugin_files = array(
		'cocart-plus/cocart-plus.php',
		'cocart-pro/cocart-pro.php',
		'cocart-jwt-authentication/cocart-jwt-authentication.php',
		'cocart-rate-limiting/cocart-rate-limiting.php',
		'cocart-pos/cocart-pos.php',
	);

	/**
	 * Registers the hooks.
	 *
	 * @return void
	 */
	public function __construct() {
		add_action( 'add_site_option_auto_update_plugins', array( $this, 'call_toggle_auto_updates_with_empty_array' ), 10, 2 );
		add_action( 'update_site_option_auto_update_plugins', array( $this, 'toggle_auto_updates_for_add_ons' ), 10, 3 );
		add_filter( 'plugin_auto_update_setting_html', array( $this, 'replace_auto_update_toggles_of_addons' ), 10, 2 );
		add_action( 'activated_plugin', array( $this, 'maybe_toggle_auto_updates_for_new_install' ) );
	} // END __construct()

	/**
	 * Replaces the auto-update toggle links for the CoCart add-ons
	 * with a text explaining that toggling the CoCart auto-update setting
	 * automatically toggles the one for the setting for the add-ons as well.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $old_html The old HTML.
	 * @param string $plugin   The plugin.
	 *
	 * @return string The new HTML, with the auto-update toggle link replaced.
	 */
	public function replace_auto_update_toggles_of_addons( $old_html, $plugin ) {
		if ( ! is_string( $old_html ) ) {
			return $old_html;
		}

		$not_a_cocart_addon = ! in_array( $plugin, self::$add_on_plugin_files, true );

		if ( $not_a_cocart_addon ) {
			return $old_html;
		}

		$auto_updated_plugins = get_site_option( 'auto_update_plugins' );

		if ( $this->are_auto_updates_enabled( self::$cocart_core_plugin_id, $auto_updated_plugins ) ) {
			return sprintf(
				'<em>%s</em>',
				sprintf(
					/* translators: %1$s resolves to CoCart. */
					esc_html__( 'Auto-updates are enabled based on this setting for %1$s.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				)
			);
		}

		return sprintf(
			'<em>%s</em>',
			sprintf(
				/* translators: %1$s resolves to CoCart. */
				esc_html__( 'Auto-updates are disabled based on this setting for %1$s.', 'cart-rest-api-for-woocommerce' ),
				'CoCart'
			)
		);
	} // END replace_auto_update_toggles_of_addons()

	/**
	 * Handles the situation where the auto_update_plugins option did not previously exist.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string      $option The name of the option that is being created.
	 * @param array|mixed $value  The new (and first) value of the option that is being created.
	 *
	 * @return void
	 */
	public function call_toggle_auto_updates_with_empty_array( $option, $value ) {
		if ( 'auto_update_plugins' !== $option ) {
			return;
		}

		$this->toggle_auto_updates_for_add_ons( $option, $value, array() );
	} // END call_toggle_auto_updates_with_empty_array()

	/**
	 * Enables premium auto updates when free are enabled and the other way around.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $option    The name of the option that has been updated.
	 * @param array  $new_value The new value of the `auto_update_plugins` option.
	 * @param array  $old_value The old value of the `auto_update_plugins` option.
	 *
	 * @return void
	 */
	public function toggle_auto_updates_for_add_ons( $option, $new_value, $old_value ) {
		if ( 'auto_update_plugins' !== $option ) {
			// If future versions of WordPress change this filter's behavior, our behavior should stay consistent.
			return;
		}

		if ( ! is_array( $old_value ) || ! is_array( $new_value ) ) {
			return;
		}

		$auto_updates_are_enabled  = $this->are_auto_updates_enabled( self::$cocart_core_plugin_id, $new_value );
		$auto_updates_were_enabled = $this->are_auto_updates_enabled( self::$cocart_core_plugin_id, $old_value );

		if ( $auto_updates_are_enabled === $auto_updates_were_enabled ) {
			// Auto-updates for CoCart have stayed the same, so have neither been enabled or disabled.
			return;
		}

		$auto_updates_have_been_enabled = $auto_updates_are_enabled && ! $auto_updates_were_enabled;

		if ( $auto_updates_have_been_enabled ) {
			$this->enable_auto_updates_for_addons( $new_value );
			return;
		} else {
			$this->disable_auto_updates_for_addons( $new_value );
			return;
		}

		if ( ! $auto_updates_are_enabled ) {
			return;
		}

		$auto_updates_have_been_removed = false;
		foreach ( self::$add_on_plugin_files as $addon ) {
			if ( ! $this->are_auto_updates_enabled( $addon, $new_value ) ) {
				$auto_updates_have_been_removed = true;
				break;
			}
		}

		if ( $auto_updates_have_been_removed ) {
			$this->enable_auto_updates_for_addons( $new_value );
		}
	} // END toggle_auto_updates_for_add_ons()

	/**
	 * Trigger a change in the auto update detection whenever a new CoCart addon is activated.
	 *
	 * @access public
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $plugin The plugin that is activated.
	 *
	 * @return void
	 */
	public function maybe_toggle_auto_updates_for_new_install( $plugin ) {
		$not_a_cocart_addon = ! in_array( $plugin, self::$add_on_plugin_files, true );

		if ( $not_a_cocart_addon ) {
			return;
		}

		$enabled_auto_updates = get_site_option( 'auto_update_plugins' );
		$this->toggle_auto_updates_for_add_ons( 'auto_update_plugins', $enabled_auto_updates, array() );
	} // END maybe_toggle_auto_updates_for_new_install()

	/**
	 * Enables auto-updates for all addons.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $auto_updated_plugins The current list of auto-updated plugins.
	 *
	 * @return void
	 */
	protected function enable_auto_updates_for_addons( $auto_updated_plugins ) {
		$plugins = array_unique( array_merge( $auto_updated_plugins, self::$add_on_plugin_files ) );
		update_site_option( 'auto_update_plugins', $plugins );
	} // END enable_auto_updates_for_addons()

	/**
	 * Disables auto-updates for all addons.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $auto_updated_plugins The current list of auto-updated plugins.
	 *
	 * @return void
	 */
	protected function disable_auto_updates_for_addons( $auto_updated_plugins ) {
		$plugins = array_values( array_diff( $auto_updated_plugins, self::$add_on_plugin_files ) );
		update_site_option( 'auto_update_plugins', $plugins );
	} // END disable_auto_updates_for_addons()

	/**
	 * Checks whether auto updates for a plugin are enabled.
	 *
	 * @access protected
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $plugin_id            The plugin ID.
	 * @param array  $auto_updated_plugins The array of auto updated plugins.
	 *
	 * @return bool Whether auto updates for a plugin are enabled.
	 */
	protected function are_auto_updates_enabled( $plugin_id, $auto_updated_plugins ) {
		if ( false === $auto_updated_plugins || ! is_array( $auto_updated_plugins ) ) {
			return false;
		}

		return in_array( $plugin_id, $auto_updated_plugins, true );
	} // END are_auto_updates_enabled()
} // END class

return new CoCart_Admin_Addon_Update_Watcher();
