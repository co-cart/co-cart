<?php
/**
 * Admin View: WordPress Requirement Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   1.2.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p>
		<?php
		echo sprintf(
			/* translators: 1: <strong>, 2: </strong>, 3: CoCart, 4: Required WordPress version number */
			__( 'Sorry, %1$s%3$s%2$s requires WordPress %4$s or higher. Please upgrade your WordPress setup.', 'cart-rest-api-for-woocommerce' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			'<strong>',
			'</strong>',
			'CoCart',
			esc_html( CoCart::$required_wp )
		);
		?>
	</p>
</div>
