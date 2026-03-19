<?php
/**
 * CoCart Test Case
 *
 * @author  Sébastien Dumont
 * @package CoCart
 */

/**
 * CoCart test case class.
 */
class CoCart_Test_Case extends WP_UnitTestCase {

	/**
	 * Set up the test case.
	 */
	public function setUp(): void {
		parent::setUp();

		// Ensure WooCommerce is active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$this->markTestSkipped( 'WooCommerce is not active.' );
		}
	}
}
