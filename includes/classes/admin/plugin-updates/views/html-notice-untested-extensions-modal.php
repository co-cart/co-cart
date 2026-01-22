<?php
/**
 * Admin View: Notice - Untested extensions - modal.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Plugin Updates\Views
 * @since   4.3.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$untested_plugins_msg = sprintf(
	/* translators: %s: version number */
	__( 'The following active plugin(s) have not declared compatibility with CoCart %s yet and should be updated and examined further before you proceed:', 'cocart-core' ),
	$new_version
);

?>
<div id="cocart_untested_extensions_modal">
	<div class="cocart_untested_extensions_modal--content">
		<h1><?php esc_html_e( "Are you sure you're ready?", 'cocart-core' ); ?></h1>
		<div class="cocart_plugin_upgrade_notice extensions_warning">
			<p><?php echo esc_html( $untested_plugins_msg ); ?></p>

			<div class="plugin-details-table-container">
				<table class="plugin-details-table" cellspacing="0">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Plugin', 'cocart-core' ); ?></th>
							<th><?php esc_html_e( 'Tested up to Cart version', 'cocart-core' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $plugins as $plugin ) : ?>
							<tr>
								<td><?php echo esc_html( $plugin['Name'] ); ?></td>
								<td><?php echo esc_html( $plugin['CoCart tested up to'] ); ?></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>

			<p><?php esc_html_e( 'We strongly recommend creating a backup of your site before updating.', 'cocart-core' ); ?> <a href="https://woocommerce.com/2017/05/create-use-backups-woocommerce/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more', 'cocart-core' ); ?></a></p>

			<?php if ( current_user_can( 'update_plugins' ) ) : ?>
				<div class="actions">
					<a href="#" class="button button-secondary cancel"><?php esc_html_e( 'Cancel', 'cocart-core' ); ?></a>
					<a class="button button-primary accept" href="#"><?php esc_html_e( 'Update now', 'cocart-core' ); ?></a>
				</div>
			<?php endif ?>
		</div>
	</div>
</div>
