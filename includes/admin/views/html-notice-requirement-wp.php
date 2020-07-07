<?php
/**
 * Admin View: WordPress Requirement Notice.
 *
 * @author   Sébastien Dumont
 * @category Admin
 * @package  CoCart/Admin/Views
 * @since    1.2.0
 * @version  2.3.0
 * @license  GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo sprintf( __( 'Sorry, %1$s%3$s%2$s requires WordPress %4$s or higher. Please upgrade your WordPress setup.', 'cart-rest-api-for-woocommerce' ), '<strong>', '</strong>', 'CoCart', CoCart::$required_wp ); ?></p>
</div>
