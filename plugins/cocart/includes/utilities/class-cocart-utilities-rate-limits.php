<?php
/**
 * Utilities: RateLimits class.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Utilities
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Utilities;

use \WC_Rate_Limiter as Limiter;
use \WC_Cache_Helper as CacheHelp;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the RateLimits for using the API.
 *
 * @since 4.0.0 Introduced.
 */
class RateLimits extends Limiter {

	/**
	 * Cache group.
	 */
	const CACHE_GROUP = 'cocart_api_rate_limit';

	/**
	 * Rate limiting enabled default value.
	 *
	 * @var boolean
	 */
	const ENABLED = false;

	/**
	 * Proxy support enabled default value.
	 *
	 * @var boolean
	 */
	const PROXY_SUPPORT = false;

	/**
	 * Default amount of max requests allowed for the defined time frame.
	 *
	 * @var int
	 */
	const LIMIT = 25;

	/**
	 * Default time in seconds before rate limits are reset.
	 *
	 * @var int
	 */
	const SECONDS = 10;

	/**
	 * Gets a cache prefix.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @param string $action_id Identifier of the action.
	 *
	 * @return string
	 */
	protected static function get_cache_key( $action_id ) {
		return CacheHelp::get_cache_prefix( 'cocart_api_rate_limit' . $action_id );
	} // END get_cache_key()

	/**
	 * Get current rate limit row from DB and normalize types. This query is not cached,
	 * and returns a new rate limit row if none exists.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @param string $action_id Identifier of the action.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return object Object containing reset and remaining.
	 */
	protected static function get_rate_limit_row( $action_id ) {
		global $wpdb;

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"
					SELECT rate_limit_expiry as reset, rate_limit_remaining as remaining
					FROM {$wpdb->prefix}wc_rate_limits
					WHERE rate_limit_key = %s
					AND rate_limit_expiry > %s
				",
				$action_id,
				time()
			),
			'OBJECT'
		);

		if ( empty( $row ) ) {
			$options = self::get_options();

			return (object) array(
				'reset'     => (int) $options->seconds + time(),
				'remaining' => (int) $options->limit,
			);
		}

		return (object) array(
			'reset'     => (int) $row->reset,
			'remaining' => (int) $row->remaining,
		);
	} // END get_rate_limit_row()

	/**
	 * Returns current rate limit values using cache where possible.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $action_id Identifier of the action.
	 *
	 * @return object|null
	 */
	public static function get_rate_limit( $action_id ) {
		$current_limit = self::get_cached( $action_id );

		if ( false === $current_limit ) {
			$current_limit = self::get_rate_limit_row( $action_id );
			self::set_cache( $action_id, $current_limit );
		}

		return $current_limit;
	} // END get_rate_limit()

	/**
	 * If exceeded, seconds until reset.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $action_id Identifier of the action.
	 *
	 * @return bool|int
	 */
	public static function is_exceeded_retry_after( $action_id ) {
		$current_limit = self::get_rate_limit( $action_id );

		// Before the next run is allowed, retry forbidden.
		if ( time() <= $current_limit->reset && 0 === $current_limit->remaining ) {
			return (int) $current_limit->reset - time();
		}

		// After the next run is allowed, retry allowed.
		return false;
	} // END is_exceeded_retry_after()

	/**
	 * Retrieve a cached store api rate limit.
	 *
	 * @param string $action_id Identifier of the action.
	 * @return bool|object
	 */
	protected static function get_cached( $action_id ) {
		return wp_cache_get( self::get_cache_key( $action_id ), self::CACHE_GROUP );
	}

	/**
	 * Cache a rate limit.
	 *
	 * @param string $action_id Identifier of the action.
	 * @param object $current_limit Current limit object with expiry and retries remaining.
	 * @return bool
	 */
	protected static function set_cache( $action_id, $current_limit ) {
		return wp_cache_set( self::get_cache_key( $action_id ), $current_limit, self::CACHE_GROUP );
	}

	/**
	 * Sets the rate limit delay in seconds for action with identifier $id.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $action_id Identifier of the action.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 *
	 * @return object Current rate limits.
	 */
	public static function update_rate_limit( $action_id ) {
		global $wpdb;

		$options = self::get_options();

		$rate_limit_expiry = time() + $options->seconds;

		$wpdb->query(
			$wpdb->prepare(
				"INSERT INTO {$wpdb->prefix}wc_rate_limits
					(`rate_limit_key`, `rate_limit_expiry`, `rate_limit_remaining`)
				VALUES
					(%s, %d, %d)
				ON DUPLICATE KEY UPDATE
					`rate_limit_remaining` = IF(`rate_limit_expiry` < %d, VALUES(`rate_limit_remaining`), GREATEST(`rate_limit_remaining` - 1, 0)),
					`rate_limit_expiry` = IF(`rate_limit_expiry` < %d, VALUES(`rate_limit_expiry`), `rate_limit_expiry`);
				",
				$action_id,
				$rate_limit_expiry,
				$options->limit - 1,
				time(),
				time()
			)
		);

		$current_limit = self::get_rate_limit_row( $action_id );

		self::set_cache( $action_id, $current_limit );

		return $current_limit;
	} // END update_rate_limit()

	/**
	 * Return options for Rate Limits, to be returned by the "cocart_api_rate_limit_options" filter.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return object Default options.
	 */
	public static function get_options() {
		$default_options = array(
			/**
			 * Filters the API rate limit check, which is disabled by default.
			 *
			 * This can be used also to disable the rate limit check when testing API endpoints via a REST API client.
			 */
			'enabled'       => self::ENABLED,

			/**
			 * Filters whether proxy support is enabled for the API rate limit check. This is disabled by default.
			 *
			 * If the store is behind a proxy, load balancer, CDN etc. the user can enable this to properly obtain
			 * the client's IP address through standard transport headers.
			 */
			'proxy_support' => self::PROXY_SUPPORT,

			'limit'         => self::LIMIT,
			'seconds'       => self::SECONDS,
		);

		return (object) array_merge( // By using array_merge we ensure we get a properly populated options object.
			$default_options,
			/**
			 * Filters options for Rate Limits.
			 *
			 * @param array $default_options Array of default option values.
			 *
			 * @return array $rate_limit_options Array of option values after filter.
			 */
			apply_filters(
				'cocart_api_rate_limit_options',
				$default_options
			)
		);
	} // END get_options()

	/**
	 * Gets a single option through provided name.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $option Option name.
	 *
	 * @return mixed
	 */
	public static function get_option( $option ) {
		if ( ! is_string( $option ) || ! defined( 'RateLimits::' . strtoupper( $option ) ) ) {
			return null;
		}

		return self::get_options()[ $option ];
	} // END get_option()

} // END class
