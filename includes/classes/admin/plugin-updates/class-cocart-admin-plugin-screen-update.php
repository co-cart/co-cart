<?php
/**
 * Manages CoCart plugin update notices.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   2.0.12 Introduced.
 * @version 4.3.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Plugin_Updates' ) ) {
	include_once __DIR__ . '/class-cocart-admin-plugin-updates.php';
}

class CoCart_Admin_Plugin_Screen_Update extends CoCart_Admin_Plugin_Updates {

	/**
	 * The upgrade notice shown inline.
	 *
	 * @var string
	 */
	protected $upgrade_notice = '';

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {
		add_action( 'in_plugin_update_message-' . plugin_basename( COCART_FILE ), array( $this, 'in_plugin_update_message' ), 10, 2 );

		// Add after_plugin_row... action for CoCart.
		add_action( 'after_plugin_row_' . plugin_basename( COCART_FILE ), array( $this, 'plugin_row' ), 11, 2 );
	} // END __construct()


	/**
	 * Show plugin changes on the plugins screen.
	 *
	 * @access public
	 *
	 * @since 2.0.12 Introduced.
	 * @since 4.3.0  Now includes list of untested extensions if any.
	 *
	 * @param array    $args Unused parameter.
	 * @param stdClass $response Plugin update response.
	 */
	public function in_plugin_update_message( $args, $response ) {
		$this->new_version            = $response->new_version;
		$this->upgrade_notice         = $this->get_upgrade_notice( $response->new_version );
		$this->major_untested_plugins = $this->get_untested_plugins( $response->new_version, 'minor' );

		$current_version_parts = explode( '.', COCART_VERSION );
		$new_version_parts     = explode( '.', $this->new_version );

		// If user has already moved to the minor version, we don't need to flag up anything.
		if ( version_compare( $current_version_parts[0] . '.' . $current_version_parts[1], $new_version_parts[0] . '.' . $new_version_parts[1], '=' ) ) {
			// return; // @todo Uncomment when we no longer make file changes that are not backwards compatible.
		}

		if ( ! empty( $this->major_untested_plugins ) ) {
			$this->upgrade_notice .= $this->get_extensions_inline_warning_major();
		}

		if ( ! empty( $this->major_untested_plugins ) ) {
			$this->upgrade_notice .= $this->get_extensions_modal_warning();
			add_action( 'admin_print_footer_scripts', array( $this, 'plugin_screen_modal_js' ) );
		}

		/**
		 * Filter allows you to change the upgrade notice.
		 *
		 * @since 4.3.0 Introduced.
		 */
		echo apply_filters( 'cocart_in_plugin_update_message', $this->upgrade_notice ? '</p>' . wp_kses_post( $this->upgrade_notice ) . '<p class="dummy">' : '' ); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
	} // END in_plugin_update_message()

	/**
	 * Get the upgrade notice from WordPress.org.
	 *
	 * @access protected
	 *
	 * @since 2.0.12 Introduced.
	 *
	 * @param string $version CoCart new version.
	 *
	 * @return string
	 */
	protected function get_upgrade_notice( $version ) {
		$transient_name = 'cocart_readme_upgrade_notice_' . $version;
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( esc_url_raw( 'https://plugins.svn.wordpress.org/' . COCART_SLUG . '/trunk/readme.txt' ) );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = $this->parse_update_notice( $response['body'], $version );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}
		return $upgrade_notice;
	} // END get_upgrade_notice()

	/**
	 * Parse update notice from readme file.
	 *
	 * @access private
	 *
	 * @since 2.0.12 Introduced.
	 *
	 * @param string $content CoCart readme file content.
	 * @param string $new_version CoCart new version.
	 *
	 * @return string
	 */
	private function parse_update_notice( $content, $new_version ) {
		$version_parts     = explode( '.', $new_version );
		$check_for_notices = array(
			$version_parts[0] . '.0', // Major.
			$version_parts[0] . '.0.0', // Major.
			$version_parts[0] . '.' . $version_parts[1], // Minor.
			$version_parts[0] . '.' . $version_parts[1] . '.' . $version_parts[2], // Patch.
		);
		$notice_regexp     = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( $new_version ) . '\s*=|$)~Uis';
		$upgrade_notice    = '';

		foreach ( $check_for_notices as $check_version ) {
			if ( version_compare( COCART_VERSION, $check_version, '>' ) ) {
				continue;
			}

			$matches = null;

			if ( preg_match( $notice_regexp, $content, $matches ) ) {
				$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

				if ( version_compare( trim( $matches[1] ), $check_version, '=' ) ) {
					$upgrade_notice .= '<p class="cocart_plugin_upgrade_notice">';

					foreach ( $notices as $index => $line ) {
						$upgrade_notice .= preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line );
					}

					$upgrade_notice .= '</p>';
				}
				break;
			}
		}
		return wp_kses_post( $upgrade_notice );
	} // END parse_update_notice()

	/**
	 * JS for the modal window on the plugins screen.
	 *
	 * @access public
	 */
	public function plugin_screen_modal_js() {
		?>
		<script>
			( function( $ ) {
				var $update_box = $( '#cart-rest-api-for-woocommerce-update' );
				var $update_link = $update_box.find('a.update-link').first();
				var update_url = $update_link.attr( 'href' );

				// Set up thickbox.
				$update_link.removeClass( 'update-link' );
				$update_link.addClass( 'cocart-thickbox' );
				$update_link.attr( 'href', '#TB_inline?height=600&width=550&inlineId=cocart_untested_extensions_modal' );

				// Trigger the update if the user accepts the modal's warning.
				$( '#cocart_untested_extensions_modal .accept' ).on( 'click', function( evt ) {
					evt.preventDefault();
					tb_remove();
					$update_link.removeClass( 'cocart-thickbox open-plugin-details-modal' );
					$update_link.addClass( 'update-link' );
					$update_link.attr( 'href', update_url );
					$update_link.trigger( 'click' );
				});

				$( '#cocart_untested_extensions_modal .cancel' ).on( 'click', function( evt ) {
					evt.preventDefault();
					tb_remove();
				});
			})( jQuery );
		</script>
		<?php
		$this->generic_modal_js();
	}

	/**
	 * Displays a notice under the plugin row for CoCart.
	 *
	 * @todo Deprecate this in the future.
	 *
	 * @access public
	 *
	 * @since 2.0.3 Introduced.
	 *
	 * @param string $file        Plugin basename.
	 * @param array  $plugin_data Plugin information.
	 *
	 * @return false|void
	 */
	public function plugin_row( $file, $plugin_data ) {
		$plugins_allowedtags = array(
			'a'       => array(
				'href'  => array(),
				'title' => array(),
			),
			'abbr'    => array( 'title' => array() ),
			'acronym' => array( 'title' => array() ),
			'code'    => array(),
			'em'      => array(),
			'strong'  => array(),
		);

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		$plugin_name   = wp_kses( $plugin_data['Name'], $plugins_allowedtags );

		if ( is_network_admin() || ! is_multisite() ) {
			if ( is_network_admin() ) {
				$active_class = is_plugin_active_for_network( $file ) ? ' active' : '';
			} else {
				$active_class = is_plugin_active( $file ) ? ' active' : '';
			}

			$notice_type = 'notice-cocart';

			// Only show the plugin notice if this version of CoCart is not a pre-release or is lower than the version mentioned in the notice.
			$version = strstr( COCART_VERSION, '-', true );

			// If version returns empty then just set as the current plugin version.
			if ( empty( $version ) ) {
				$version = COCART_VERSION;
			}

			if ( CoCart_Helpers::is_cocart_pre_release() || version_compare( COCART_NEXT_VERSION, $version, '<=' ) ) {
				return;
			}

			echo '<tr class="plugin-update-tr' . esc_attr( $active_class ) . ' cocart-row-notice" id="' . esc_attr( 'cart-rest-api-for-woocommerce-update' ) . '" data-slug="cart-rest-api-for-woocommerce" data-plugin="' . esc_attr( $file ) . '"><td colspan="' . $wp_list_table->get_column_count() . '" class="plugin-update colspanchange"><div class="notice inline ' . esc_attr( $notice_type ) . '"><p class="cart">'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			printf(
				/* translators: %1$s: Hyperlink opening, %2$s: Hyperlink closing , %3$s: plugin name, %4$s: version mentioned, */
				__( '%1$sSee what\'s coming next%2$s in %3$s v%4$s.', 'cart-rest-api-for-woocommerce' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				'<a href="' . esc_url( 'https://github.com/co-cart/co-cart/blob/dev/NEXT_CHANGELOG.md' ) . '" target="_blank">',
				'</a>',
				'CoCart',
				esc_attr( COCART_NEXT_VERSION )
			);

			echo '</p></div></td></tr>';
		}
	} // END plugin_row()
} // END class

return new CoCart_Admin_Plugin_Screen_Update();
