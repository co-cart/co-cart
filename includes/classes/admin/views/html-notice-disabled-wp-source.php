<?php
/**
 * Admin View: Disabled WordPress Source Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info cocart-notice is-dismissible">
	<p>
		<?php
		printf(
			/* translators: %s: CoCart */
			esc_html__( "We've automatically deactivated the legacy version of %s Core as it cannot run side by side with the new version.", 'cocart-core' ),
			'CoCart'
		);
		?>
	</p>
</div>
