<?php
/**
 * CoCart Test Spy REST Server
 *
 * Provides a WP_REST_Server double that records headers instead of sending
 * them, for use in tests that need to assert on header logic.
 *
 * @package CoCart\Tests\Framework
 */

/**
 * Spy REST Server Class
 *
 * Records headers passed to send_header() instead of sending them, so
 * header logic can be asserted without relying on PHP's header() function
 * (which is a no-op under the PHPUnit CLI SAPI).
 *
 * @package CoCart\Tests\Framework
 */
class CoCart_Test_Spy_REST_Server extends WP_REST_Server {

	/**
	 * Headers captured via send_header().
	 *
	 * @var array
	 */
	public $sent_headers = array();

	/**
	 * Record the header instead of sending it.
	 *
	 * @param string $key     Header key.
	 * @param string $value   Header value.
	 * @param bool   $replace Whether to replace an existing header. Unused.
	 *
	 * @return void
	 */
	public function send_header( $key, $value, $replace = true ) {
		$this->sent_headers[] = array( $key, $value );
	}
}
