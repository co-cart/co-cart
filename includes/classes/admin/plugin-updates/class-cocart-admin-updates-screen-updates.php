<?php
/**
 * Manages CoCart plugin updating on the updates screen.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin
 * @since   4.3.0 Introduced.
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'CoCart_Admin_Plugin_Updates' ) ) {
	include_once __DIR__ . '/class-cocart-admin-plugin-updates.php';
}

/**
 * Class CoCart_Admin_Updates_Screen_Updates
 */
class CoCart_Admin_Updates_Screen_Updates extends CoCart_Admin_Plugin_Updates {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_print_footer_scripts', array( $this, 'update_screen_modal' ) );
	}

	/**
	 * Show a warning message on the upgrades screen if the user tries to upgrade and has untested plugins.
	 *
	 * @access public
	 */
	public function update_screen_modal() {
		$updateable_plugins = get_plugin_updates();
		if ( empty( $updateable_plugins[ COCART_SLUG . '/' . COCART_SLUG . '.php' ] )
			|| empty( $updateable_plugins[ COCART_SLUG . '/' . COCART_SLUG . '.php' ]->update )
			|| empty( $updateable_plugins[ COCART_SLUG . '/' . COCART_SLUG . '.php' ]->update->new_version ) ) {
			return;
		}

		$this->new_version            = sanitize_text_field( $updateable_plugins[ COCART_SLUG . '/' . COCART_SLUG . '.php' ]->update->new_version );
		$this->major_untested_plugins = $this->get_untested_plugins( $this->new_version, 'major' );

		if ( ! empty( $this->major_untested_plugins ) ) {
			echo $this->get_extensions_modal_warning(); // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped, WordPress.Security.EscapeOutput.OutputNotEscaped 
			$this->update_screen_modal_js();
		}
	} // END update_screen_modal()

	/**
	 * JS for the modal window on the updates screen.
	 *
	 * @access protected
	 */
	protected function update_screen_modal_js() {
		?>
		<script>
			( function( $ ) {
				var modal_dismissed = false;

				// Show the modal if the CoCart upgrade checkbox is checked.
				var show_modal_if_checked = function() {
					if ( modal_dismissed ) {
						return;
					}
					var $checkbox = $( 'input[value="cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php"]' );
					if ( $checkbox.prop( 'checked' ) ) {
						$( '#cocart-upgrade-warning' ).trigger( 'click' );
					}
				}

				$( '#plugins-select-all, input[value="cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php"]' ).on( 'change', function() {
					show_modal_if_checked();
				} );

				// Add a hidden thickbox link to use for bringing up the modal.
				$('body').append( '<a href="#TB_inline?height=600&width=550&inlineId=cocart_untested_extensions_modal" class="cocart-thickbox" id="cocart-upgrade-warning" style="display:none"></a>' );

				// Don't show the modal again once it's been accepted.
				$( '#cocart_untested_extensions_modal .accept' ).on( 'click', function( evt ) {
					evt.preventDefault();
					modal_dismissed = true;
					tb_remove();
				});

				// Uncheck the CoCart update checkbox if the modal is canceled.
				$( '#cocart_untested_extensions_modal .cancel' ).on( 'click', function( evt ) {
					evt.preventDefault();
					$( 'input[value="cart-rest-api-for-woocommerce/cart-rest-api-for-woocommerce.php"]' ).prop( 'checked', false );
					tb_remove();
				});
			})( jQuery );
		</script>
		<?php
		$this->generic_modal_js();
	} // END update_screen_modal_js()
} // END class

return new CoCart_Admin_Updates_Screen_Updates();
