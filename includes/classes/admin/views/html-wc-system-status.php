<?php
/**
 * Outputs the status section for CoCart.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   2.7.2
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $debug_data ) || ! is_array( $debug_data ) ) {
	return;
}
?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
	<tr>
		<th colspan="3" data-export-label="<?php echo esc_attr( $section_title ); ?>">
			<h2><?php echo esc_html( $section_title ); ?>
				<?php echo wc_help_tip( $section_tooltip ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</h2></th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $debug_data as $section => $data ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		// Use mark key if available, otherwise default back to the success key.
		if ( isset( $data['mark'] ) ) {
			$mark = $data['mark']; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		} elseif ( isset( $data['success'] ) && $data['success'] ) {
			$mark = 'yes'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		} else {
			$mark = 'error'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		}

		// Use mark_icon key if available, otherwise set based on $mark.
		if ( isset( $data['mark_icon'] ) ) {
			$mark_icon = $data['mark_icon']; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		} elseif ( 'yes' === $mark ) {
			$mark_icon = 'yes'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		} else {
			$mark_icon = 'no-alt'; // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		}
		?>
		<?php if ( isset( $data['type'] ) && 'titles' === $data['type'] ) { ?>
			<tr>
				<?php foreach ( $data['columns'] as $column ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
					<td style="border-bottom: 1px solid #ccd0d4; border-top: 1px solid #ccd0d4;" colspan="2" data-export-label="<?php echo esc_attr( $column ); ?>"><strong><?php echo esc_attr( $column ); ?></strong></td>
				<?php } ?>
			</tr>
		<?php } else { ?>
			<tr>
				<td data-export-label="<?php echo esc_attr( $data['label'] ); ?>"><?php echo esc_html( $data['name'] ); ?>:</td>
				<td class="help">
					<?php
					if ( isset( $data['tip'] ) ) {
						echo wc_help_tip( $data['tip'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					}
					?>
				</td>
				<td>
					<?php
					if ( isset( $data['data'] ) ) {
						if ( empty( $data['data'] ) ) {
							echo '&ndash;';
							continue;
						}

						$row_number = count( $data['data'] ); // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

						foreach ( $data['data'] as $row ) { // phpcs:ignore: WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
							echo wp_kses_post( $row );

							if ( 1 !== $row_number ) {
								echo ', ';
							}
							echo '<br />';
							--$row_number;
						}
					}
					if ( isset( $data['note'] ) ) {
						if ( empty( $mark ) ) {
							echo wp_kses_post( $data['note'] );
						} else {
							?>
							<mark class="<?php echo esc_html( $mark ); ?>">
								<?php
								if ( $mark_icon ) {
									echo '<span class="dashicons dashicons-' . esc_attr( $mark_icon ) . '"></span> ';
								}
								echo wp_kses_post( $data['note'] );
								?>
							</mark>
							<?php
						}
					}
					?>
				</td>
			</tr>
		<?php } ?>
	<?php } ?>
	</tbody>
</table>
