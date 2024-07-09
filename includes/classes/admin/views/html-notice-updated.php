<?php
/**
 * Admin View: Database Updated Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   3.0.0
 * @version 3.10.0
 * @license GPL-2.0+
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
			esc_html__( '%s database update complete. Thank you for updating to the latest version!', 'cart-rest-api-for-woocommerce' ),
			'CoCart'
		);
		?>
	</p>
</div>
