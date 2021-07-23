<?php
/**
 * Admin View: Notice - Update
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.0.0
 * @version 3.0.7
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$update_url = wp_nonce_url(
	add_query_arg( 'do_update_cocart', 'true', CoCart_Helpers::cocart_get_current_admin_url() ),
	'cocart_db_update',
	'cocart_db_update_nonce'
);
?>
<div class="notice notice-info cocart-notice">
	<div class="cocart-notice-inner">
		<div class="cocart-notice-icon">
			<img src="<?php echo esc_url( COCART_URL_PATH . '/assets/images/brand/logo.jpg' ); ?>" alt="CoCart Logo" />
		</div>

		<div class="cocart-notice-content">
			<h3>
				<strong>
					<?php
					echo sprintf(
						/* translators: %s: CoCart */
						esc_html__( '%s database update required', 'cart-rest-api-for-woocommerce' ),
						'CoCart'
					);
					?>
				</strong>
			</h3>
			<p>
				<?php
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( '%s has been updated! To keep things running smoothly, we have to update your database to the newest version.', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);

				/* translators: 1: Link to docs 2: Close link. */
				printf( ' ' . esc_html__( 'The database update process runs in the background and may take a little while, so please be patient. Advanced users can alternatively update via %1$sWP CLI%2$s.', 'cart-rest-api-for-woocommerce' ), '<a href="' . esc_url( COCART_STORE_URL . 'upgrading-the-database-using-wp-cli/' ) . '" target="_blank">', '</a>' );
				?>
			</p>
		</div>

		<div class="cocart-action">
			<a href="<?php echo esc_url( $update_url ); ?>" class="button button-primary cocart-button">
				<?php
				echo sprintf(
					/* translators: %s: CoCart */
					esc_html__( 'Update %s Database', 'cart-rest-api-for-woocommerce' ),
					'CoCart'
				);
				?>
			</a>
			<span class="no-thanks"><a href="https://cocart.xyz/how-to-update-cocart/" target="_blank">
				<?php esc_html_e( 'Learn more about updates', 'cart-rest-api-for-woocommerce' ); ?>
			</a></span>
		</div>
	</div>
</div>
