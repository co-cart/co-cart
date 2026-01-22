<?php
/**
 * Admin View: Notice - Untested extensions - inline.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Plugin Updates\Views
 * @since   4.3.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="cocart_plugin_upgrade_notice extensions_warning <?php echo esc_attr( $upgrade_type ); ?>">
	<p><?php echo wp_kses_post( $message ); ?></p>

	<table class="plugin-details-table" cellspacing="0">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Plugin', 'cocart-core' ); ?></th>
				<th><?php esc_html_e( 'Tested up to CoCart version', 'cocart-core' ); ?></th>
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
