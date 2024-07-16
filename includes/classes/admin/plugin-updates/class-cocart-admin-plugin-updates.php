<?php
/**
 * Helps display a plugin warning notification if needed
 * and determining 3rd party plugin compatibility.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   4.3.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart_Admin_Plugin_Updates Class.
 */
class CoCart_Admin_Plugin_Updates {

	/**
	 * This is the header used by extensions to show requirements.
	 *
	 * @var string
	 */
	const VERSION_REQUIRED_HEADER = 'CoCart requires at least';

	/**
	 * This is the header used by extensions to show testing.
	 *
	 * @var string
	 */
	const VERSION_TESTED_HEADER = 'CoCart tested up to';

	/**
	 * The version for the update to CoCart.
	 *
	 * @var string
	 */
	protected $new_version = '';

	/**
	 * Array of plugins lacking testing with the major version.
	 *
	 * @var array
	 */
	protected $major_untested_plugins = array();

	/**
	 * Common JS for initializing and managing thickbox-based modals.
	 */
	protected function generic_modal_js() {
		?>
		<script>
			( function( $ ) {
				// Initialize thickbox.
				tb_init( '.cocart-thickbox' );

				var old_tb_position = false;

				// Make the thickboxes look good when opened.
				$( '.cocart-thickbox' ).on( 'click', function( evt ) {
					var $overlay = $( '#TB_overlay' );
					if ( ! $overlay.length ) {
						$( 'body' ).append( '<div id="TB_overlay"></div><div id="TB_window" class="cocart_untested_extensions_modal_container"></div>' );
					} else {
						$( '#TB_window' ).removeClass( 'thickbox-loading' ).addClass( 'cocart_untested_extensions_modal_container' );
					}

					// WP overrides the tb_position function. We need to use a different tb_position function than that one.
					// This is based on the original tb_position.
					if ( ! old_tb_position ) {
						old_tb_position = tb_position;
					}
					tb_position = function() {
						$( '#TB_window' ).css( { marginLeft: '-' + parseInt( ( TB_WIDTH / 2 ), 10 ) + 'px', width: TB_WIDTH + 'px' } );
						$( '#TB_window' ).css( { marginTop: '-' + parseInt( ( TB_HEIGHT / 2 ), 10 ) + 'px' } );
					};
				});

				// Reset tb_position to WP default when modal is closed.
				$( 'body' ).on( 'thickbox:removed', function() {
					if ( old_tb_position ) {
						tb_position = old_tb_position;
					}
				});
			})( jQuery );
		</script>
		<?php
	} // END generic_modal_js()

	/**
	 * Get the inline warning notice for major version updates.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function get_extensions_inline_warning_major() {
		$upgrade_type  = 'major';
		$plugins       = $this->major_untested_plugins;
		$version_parts = explode( '.', $this->new_version );
		$new_version   = $version_parts[0] . '.0';

		if ( empty( $plugins ) ) {
			return;
		}

		/* translators: %s: version number */
		$message = sprintf( __( "<strong>Heads up!</strong> The versions of the following plugins you're running haven't been tested with CoCart %s. Please update them or confirm compatibility before updating CoCart, or you may experience issues:", 'cart-rest-api-for-woocommerce' ), $new_version );

		ob_start();
		include __DIR__ . '/views/html-notice-untested-extensions-inline.php';
		return ob_get_clean();
	} // END get_extensions_inline_warning_major()

	/**
	 * Get the warning notice for the modal window.
	 *
	 * @access protected
	 *
	 * @return string
	 */
	protected function get_extensions_modal_warning() {
		$version_parts = explode( '.', $this->new_version );
		$new_version   = $version_parts[0] . '.0';
		$plugins       = $this->major_untested_plugins;

		ob_start();
		include __DIR__ . '/views/html-notice-untested-extensions-modal.php';
		return ob_get_clean();
	} // END get_extensions_modal_warning()

	/**
	 * Get installed plugins that have a tested version lower than the input version.
	 *
	 * In case of testing major version compatibility and if current CoCart version is >= major version part
	 * of the $new_version, no plugins are returned, even if they don't explicitly declare compatibility
	 * with the $new_version.
	 *
	 * @access public
	 *
	 * @param string $new_version CoCart version to test against.
	 * @param string $release 'major', 'minor', or 'none'.
	 *
	 * @return array of plugin info arrays
	 */
	public function get_untested_plugins( $new_version, $release ) {
		if ( 'none' === $release ) {
			return array();
		}

		$extensions        = array_merge( $this->get_plugins_with_header( self::VERSION_TESTED_HEADER ), $this->get_plugins_for_cocart() );
		$untested          = array();
		$new_version_parts = explode( '.', $new_version );
		$version           = $new_version_parts[0];

		if ( 'minor' === $release ) {
			$version .= '.' . $new_version_parts[1];
		}

		foreach ( $extensions as $file => $plugin ) {
			if ( ! empty( $plugin[ self::VERSION_TESTED_HEADER ] ) ) {
				$plugin_version_parts = explode( '.', $plugin[ self::VERSION_TESTED_HEADER ] );

				if ( ! is_numeric( $plugin_version_parts[0] )
					|| ( 'minor' === $release && ! isset( $plugin_version_parts[1] ) )
					|| ( 'minor' === $release && ! is_numeric( $plugin_version_parts[1] ) )
					) {
					continue;
				}

				$plugin_version = $plugin_version_parts[0];

				if ( 'minor' === $release ) {
					$plugin_version .= '.' . $plugin_version_parts[1];
				}

				if ( version_compare( $plugin_version, $version, '<' ) ) {
					$untested[ $file ] = $plugin;
				}
			} else {
				$plugin[ self::VERSION_TESTED_HEADER ] = __( 'unknown', 'cart-rest-api-for-woocommerce' );
				$untested[ $file ]                     = $plugin;
			}
		}

		return $untested;
	} // END get_untested_plugins()

	/**
	 * Get plugins that have a valid value for a specific header.
	 *
	 * @access protected
	 *
	 * @param string $header Plugin header to search for.
	 *
	 * @return array Array of plugins that contain the searched header.
	 */
	protected function get_plugins_with_header( $header ) {
		$plugins = get_plugins();
		$matches = array();

		foreach ( $plugins as $file => $plugin ) {
			if ( ! empty( $plugin[ $header ] ) ) {
				$matches[ $file ] = $plugin;
			}
		}

		/**
		 * Filter allows you to get the plugins that have a valid value for a specific header.
		 *
		 * @since 4.3.0 Introduced.
		 *
		 * @param array  $matches Array of plugins matched with header.
		 * @param string $header  Plugin header to search for.
		 * @param array  $plugins Array of plugins installed.
		 */
		return apply_filters( 'cocart_get_plugins_with_header', $matches, $header, $plugins );
	} // END get_plugins_with_header()

	/**
	 * Get plugins which "maybe" are for CoCart.
	 *
	 * @access protected
	 *
	 * @return array of plugin info arrays
	 */
	protected function get_plugins_for_cocart() {
		$plugins = get_plugins();
		$matches = array();

		foreach ( $plugins as $file => $plugin ) {
			if ( 'CoCart' !== $plugin['Name'] && ( stristr( $plugin['Name'], 'cocart' ) || stristr( $plugin['Description'], 'cocart' ) ) ) {
				$matches[ $file ] = $plugin;
			}
		}

		/**
		 * Filter allows you to get plugins which "maybe" are for CoCart.
		 *
		 * @since 4.3.0 Introduced.
		 *
		 * @param array $matches Array of plugins that "maybe" are for CoCart.
		 * @param array $plugins Array of plugins installed.
		 */
		return apply_filters( 'cocart_get_plugins_for_cocart', $matches, $plugins );
	} // END get_plugins_for_cocart()
} // END class
