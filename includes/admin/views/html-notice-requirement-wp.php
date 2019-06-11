<?php
/**
 * Admin View: WordPress Requirment Notice.
 *
 * @since    1.2.0
 * @version  2.0.0
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo sprintf( __( 'Sorry, %1$s%3$s%2$s requires WordPress %4$s or higher. Please upgrade your WordPress setup.', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart', COCART_WP_VERSION_REQUIRE ); ?></p>
</div>
