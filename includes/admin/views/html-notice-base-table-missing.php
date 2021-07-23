<?php
/**
 * Admin View: Notice - Base table missing.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.0.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3><?php esc_html_e( 'Database table missing', 'cart-rest-api-for-woocommerce' ); ?></h3>
			<p>
				<?php
				$verify_db_tool_available = array_key_exists( 'cocart_verify_db_tables', WC_Admin_Status::get_tools() );
				$missing_tables           = get_option( 'cocart_schema_missing_tables' );
				if ( $verify_db_tool_available ) {
					echo wp_kses_post(
						sprintf(
						/* translators: %1%s: Missing table (separated by ",") %2$s: Link to check again */
							__( 'One table is required for CoCart to function is missing and will not work as expected. Missing table: <code>%1$s</code> <a href="%2$s">Check again.</a>', 'cart-rest-api-for-woocommerce' ),
							esc_html( implode( ', ', $missing_tables ) ),
							wp_nonce_url( admin_url( 'admin.php?page=wc-status&tab=tools&action=cocart_verify_db_tables' ), 'debug_action' )
						)
					);
				} else {
					echo wp_kses_post(
						sprintf(
						/* translators: %1%s: Missing table (separated by ",") */
							__( 'One table is required for CoCart to function is missing and will not work as expected. Missing table: <code>%1$s</code>', 'cart-rest-api-for-woocommerce' ),
							esc_html( implode( ', ', $missing_tables ) )
						)
					);
				}
				?>
			</p>
		</div>

		<div class="cocart-action">
			<a class="button button-primary cocart-button" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'cocart-hide-notice', 'base_tables_missing', CoCart_Helpers::cocart_get_current_admin_url() ), 'cocart_hide_notices_nonce', '_cocart_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss', 'cart-rest-api-for-woocommerce' ); ?></a>
		</div>
	</div>
</div>
