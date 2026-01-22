<?php
/**
 * Utilities: Cache Helpers class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helper class to handle cache functions.
 *
 * @since 5.0.0 Introduced.
 */
class CoCart_Utilities_Cache_Helpers {

	/**
	 * Transients to delete on shutdown.
	 *
	 * @var array Array of transient keys.
	 */
	private static $delete_transients = array();

	/**
	 * Hook in methods.
	 */
	public static function init() {
		add_action( 'shutdown', array( __CLASS__, 'delete_transients_on_shutdown' ), 10 );
		add_action( 'admin_notices', array( __CLASS__, 'notices' ) );
	}

	/**
	 * Get prefix for use with wp_cache_set. Allows all cache in a group to be invalidated at once.
	 *
	 * @access public
	 *
	 * @param string $group Group of cache to get.
	 *
	 * @return string Prefix.
	 */
	public static function get_cache_prefix( $group ) {
		// Get cache key - uses cache key cocart_cart_id_cache_prefix to invalidate when needed.
		$prefix = wp_cache_get( 'cocart_' . $group . '_cache_prefix', $group );

		if ( false === $prefix ) {
			$prefix = microtime();
			wp_cache_set( 'cocart_' . $group . '_cache_prefix', $prefix, $group );
		}

		return 'cocart_cache_' . $prefix . '_';
	} // END get_cache_prefix()

	/**
	 * Invalidate cache group.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $group Group of cache to clear.
	 */
	public static function invalidate_cache_group( $group ) {
		return wp_cache_set( 'cocart_' . $group . '_cache_prefix', microtime(), $group );
	} // END invalidate_cache_group()

	/**
	 * Helper method to get prefixed key.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $key   Key to prefix.
	 * @param string $group Group of cache to get.
	 *
	 * @return string Prefixed key.
	 */
	public static function get_prefixed_key( $key, $group ) {
		return self::get_cache_prefix( $group ) . $key;
	} // END get_prefixed_key()

	/**
	 * Add a transient to delete on shutdown.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string|array $keys Transient key or keys.
	 */
	public static function queue_delete_transient( $keys ) {
		self::$delete_transients = array_unique( array_merge( is_array( $keys ) ? $keys : array( $keys ), self::$delete_transients ) );
	} // END queue_delete_transient()

	/**
	 * Transients that don't need to be cleaned right away can be deleted on shutdown to avoid repetition.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function delete_transients_on_shutdown() {
		if ( self::$delete_transients ) {
			foreach ( self::$delete_transients as $key ) {
				delete_transient( $key );
			}
			self::$delete_transients = array();
		}
	} // END delete_transients_on_shutdown()

	/**
	 * Get transient version.
	 *
	 * When using transients with unpredictable names, e.g. those containing an md5
	 * hash in the name, we need a way to invalidate them all at once.
	 *
	 * When using default WP transients we're able to do this with a DB query to
	 * delete transients manually.
	 *
	 * With external cache however, this isn't possible. Instead, this function is used
	 * to append a unique string (based on time()) to each transient. When transients
	 * are invalidated, the transient version will increment and data will be regenerated.
	 *
	 * Adapted from ideas in http://tollmanz.com/invalidation-schemes/.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string  $group   Name for the group of transients we need to invalidate.
	 * @param boolean $refresh true to force a new version.
	 *
	 * @return string transient version based on time(), 10 digits.
	 */
	public static function get_transient_version( $group, $refresh = false ) {
		$transient_name  = $group . '-transient-version';
		$transient_value = get_transient( $transient_name );

		if ( false === $transient_value || true === $refresh ) {
			$transient_value = (string) time();

			set_transient( $transient_name, $transient_value );
		}

		return $transient_value;
	} // END get_transient_version()

	/**
	 * Notices function.
	 */
	public static function notices() {
		if ( ! function_exists( 'w3tc_pgcache_flush' ) || ! function_exists( 'w3_instance' ) ) {
			return;
		}

		$config   = w3_instance( 'W3_Config' );
		$enabled  = $config->get_integer( 'dbcache.enabled' );
		$settings = array_map( 'trim', $config->get_array( 'dbcache.reject.sql' ) );

		if ( $enabled && ! in_array( '_cocart_session_', $settings, true ) ) {
			?>
			<div class="error">
				<p>
				<?php
				/* translators: 1: key 2: URL */
				echo wp_kses_post( sprintf( __( 'In order for <strong>database caching</strong> to work with CoCart you must add %1$s to the "Ignored Query Strings" option in <a href="%2$s">W3 Total Cache settings</a>.', 'cocart-core' ), '<code>_cocart_session_</code>', esc_url( admin_url( 'admin.php?page=w3tc_dbcache' ) ) ) );
				?>
				</p>
			</div>
			<?php
		}
	}
}

CoCart_Utilities_Cache_Helpers::init();
