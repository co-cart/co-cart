<?php
/**
 * Admin View: PHP Requirement Notice.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Views
 * @since   2.6.0
 * @version 3.1.0
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-error">
	<p><?php echo esc_html( CoCart_Helpers::get_environment_message() ); ?></p>
</div>
