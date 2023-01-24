<?php
/**
 * Class: CoCart\Help.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   2.3.0 Introduced.
 * @version 4.0.0
 */

namespace CoCart;

use CoCart\Core;
use CoCart\Status;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Helps CoCart gather data.
 *
 * Provides functions that provide helpful data for the plugin.
 *
 * @since 2.3.0 Introduced.
 */
class Help {

	/**
	 * Cache 'gte' comparison results for WooCommerce version.
	 *
	 * @var array
	 */
	private static $is_wc_version_gte = array();

	/**
	 * Cache 'gt' comparison results for WooCommerce version.
	 *
	 * @var array
	 */
	private static $is_wc_version_gt = array();

	/**
	 * Cache 'lte' comparison results for WooCommerce version.
	 *
	 * @since 2.6.0 Introduced.
	 *
	 * @var array
	 */
	private static $is_wc_version_lte = array();

	/**
	 * Cache 'lt' comparison results for WooCommerce version.
	 *
	 * @since 2.6.0 Introduced.
	 *
	 * @var array
	 */
	private static $is_wc_version_lt = array();

	/**
	 * Cache 'gt' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gt = array();

	/**
	 * Cache 'gte' comparison results for WP version.
	 *
	 * @var array
	 */
	private static $is_wp_version_gte = array();

	/**
	 * Cache 'lt' comparison results for WP version.
	 *
	 * @since 2.5.0 Introduced.
	 *
	 * @var array
	 */
	private static $is_wp_version_lt = array();

	/**
	 * Cache WC Admin status result.
	 *
	 * @since 3.2.0 Introduced.
	 *
	 * @var bool
	 */
	private static $is_wc_admin_enabled = null;

	/**
	 * Helper method to get the version of the currently installed WooCommerce.
	 *
	 * @access private
	 * @static
	 *
	 * @return string
	 */
	private static function get_wc_version() {
		return defined( 'WC_VERSION' ) && WC_VERSION ? WC_VERSION : null;
	} // END get_wc_version()

	/**
	 * Returns true if the installed version of WooCommerce is greater than or equal to $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gte( $version ) {
		if ( ! isset( self::$is_wc_version_gte[ $version ] ) ) {
			self::$is_wc_version_gte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>=' );
		}
		return self::$is_wc_version_gte[ $version ];
	} // END is_wc_version_gte()

	/**
	 * Returns true if the installed version of WooCommerce is greater than $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_gt( $version ) {
		if ( ! isset( self::$is_wc_version_gt[ $version ] ) ) {
			self::$is_wc_version_gt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '>' );
		}

		return self::$is_wc_version_gt[ $version ];
	} // END is_wc_version_gt()

	/**
	 * Returns true if the installed version of WooCommerce is lower than or equal to $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_lte( $version ) {
		if ( ! isset( self::$is_wc_version_lte[ $version ] ) ) {
			self::$is_wc_version_lte[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<=' );
		}
		return self::$is_wc_version_lte[ $version ];
	} // END is_wc_version_lte()

	/**
	 * Returns true if the installed version of WooCommerce is less than $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wc_version_lt( $version ) {
		if ( ! isset( self::$is_wc_version_lt[ $version ] ) ) {
			self::$is_wc_version_lt[ $version ] = self::get_wc_version() && version_compare( self::get_wc_version(), $version, '<' );
		}

		return self::$is_wc_version_lt[ $version ];
	} // END is_wc_version_lt()

	/**
	 * Returns true if the WooCommerce version does not meet CoCart requirements.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return boolean
	 */
	public static function is_not_wc_version_required() {
		if ( version_compare( self::get_wc_version(), Core::$required_woo, '<' ) ) {
			return true;
		}

		return false;
	} // END is_note_wc_version_required()

	/**
	 * Returns true if the installed version of WordPress is greater than $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wp_version_gt( $version ) {
		if ( ! isset( self::$is_wp_version_gt[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gt[ $version ] = $wp_version && version_compare( $wp_version, $version, '>' );
		}

		return self::$is_wp_version_gt[ $version ];
	} // END is_wp_version_gt()

	/**
	 * Returns true if the installed version of WordPress is greater than or equal to $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wp_version_gte( $version ) {
		if ( ! isset( self::$is_wp_version_gte[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_gte[ $version ] = $wp_version && version_compare( $wp_version, $version, '>=' );
		}

		return self::$is_wp_version_gte[ $version ];
	} // END is_wp_version_gte()

	/**
	 * Returns true if the installed version of WordPress is less than $version.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.5.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_wp_version_lt( $version ) {
		if ( ! isset( self::$is_wp_version_lt[ $version ] ) ) {
			global $wp_version;

			self::$is_wp_version_lt[ $version ] = $wp_version && version_compare( $wp_version, $version, '<' );
		}

		return self::$is_wp_version_lt[ $version ];
	} // END is_wp_version_lt()

	/**
	 * Helper method to get the version of the currently installed CoCart.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function get_cocart_version() {
		return defined( 'COCART_VERSION' ) && COCART_VERSION ? COCART_VERSION : null;
	} // END get_cocart_version()

	/**
	 * Returns true if version is a Beta release.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_beta_release( $version = '' ) {
		if ( empty( $version ) ) {
			return esc_html__( 'Unknown version specified', 'cart-rest-api-for-woocommerce' );
		}

		if ( strpos( $version, 'beta' ) ) {
			return true;
		}

		return false;
	} // END is_beta_release()

	/**
	 * Returns true if version is a Release Candidate.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_rc_release( $version = '' ) {
		if ( empty( $version ) ) {
			return esc_html__( 'Unknown version specified', 'cart-rest-api-for-woocommerce' );
		}

		if ( strpos( $version, 'rc' ) ) {
			return true;
		}

		return false;
	} // END is_rc_release()

	/**
	 * Returns true if version is a pre-release.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @param string $version The version to compare.
	 *
	 * @return boolean
	 */
	public static function is_pre_release( $version = '' ) {
		if ( empty( $version ) ) {
			return esc_html__( 'Unknown version specified', 'cart-rest-api-for-woocommerce' );
		}

		if (
			strpos( $version, 'beta' ) || strpos( $version, 'rc' )
		) {
			return true;
		}

		return false;
	} // END is_pre_release()

	/**
	 * Returns true if CoCart is a pre-release.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return boolean
	 */
	public static function is_cocart_pre_release() {
		if ( self::is_pre_release( self::get_cocart_version() ) ) {
			return true;
		}

		return false;
	} // END is_cocart_pre_release()

	/**
	 * Returns true if CoCart is a Beta release.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return boolean
	 */
	public static function is_cocart_beta() {
		if ( self::is_beta_release( self::get_cocart_version() ) ) {
			return true;
		}

		return false;
	} // END is_cocart_beta()

	/**
	 * Returns true if CoCart is a Release Candidate.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return boolean
	 */
	public static function is_cocart_rc() {
		if ( self::is_rc_release( self::get_cocart_version() ) ) {
			return true;
		}

		return false;
	} // END is_cocart_rc()

	/**
	 * Checks if CoCart Pro is installed.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function is_cocart_pro_installed() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			$active_plugins = array_merge( $active_plugins, get_option( 'active_sitewide_plugins', array() ) );
		}

		return in_array( 'cocart-pro/cocart-pro.php', $active_plugins ) || array_key_exists( 'cocart-pro/cocart-pro.php', $active_plugins ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	} // END is_cocart_pro_installed()

	/**
	 * Check if CoCart Pro is activated.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.3 Introduced.
	 *
	 * @return boolean
	 */
	public static function is_cocart_pro_activated() {
		if ( class_exists( 'CoCart_Pro' ) ) {
			return true;
		}

		return false;
	} // END is_cocart_pro_activated()

	/**
	 * These are the only screens CoCart will focus
	 * on displaying notices or enqueue scripts/styles.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.0.0 Introduced.
	 * @version 3.0.3
	 *
	 * @return array The screen IDs.
	 */
	public static function cocart_get_admin_screens() {
		return apply_filters(
			'cocart_admin_screens',
			array(
				'dashboard',
				'dashboard-network',
				'plugins',
				'plugins-network',
				'woocommerce_page_wc-status',
				'toplevel_page_cocart',
				'toplevel_page_cocart-network',
			)
		);
	} // END cocart_get_admin_screens()

	/**
	 * Returns true|false if the user is on a CoCart page.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.3.0 Introduced.
	 *
	 * @return boolean True if on a CoCart page.
	 */
	public static function is_cocart_admin_page() {
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( ! in_array( $screen_id, self::cocart_get_admin_screens() ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return false;
		}

		return true;
	} // END is_cocart_admin_page()

	/**
	 * Checks if the current user has the capabilities to install a plugin.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.1.0 Introduced.
	 *
	 * @return boolean True if the user has the capabilities.
	 */
	public static function user_has_capabilities() {
		if ( current_user_can( apply_filters( 'cocart_install_capability', 'install_plugins' ) ) ) {
			return true;
		}

		// If the current user can not install plugins then return nothing!
		return false;
	} // END user_has_capabilities()

	/**
	 * Is CoCart Plugin Suggestions active?
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.0 Introduced.
	 *
	 * @return boolean True if CoCart Plugin Suggestions is active.
	 */
	public static function is_cocart_ps_active() {
		return apply_filters( 'cocart_show_plugin_search', true );
	} // END is_cocart_ps_active()

	/**
	 * Returns CoCart Campaign for plugin identification.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.3 Introduced.
	 * @since 4.0.0 Added check if tracking is allowed.
	 *
	 * @param array $args Passed arguments.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return array The CoCart Campaign.
	 */
	public static function cocart_campaign( $args = array() ) {
		$client   = self::appsero_client();
		$insights = $client->insights();

		if ( ! $insights->tracking_allowed() ) {
			return array();
		}

		$defaults = array(
			'utm_medium'   => 'cocart',
			'utm_source'   => 'WordPress',
			'utm_campaign' => 'coreplugin',
			'utm_content'  => '',
		);

		$campaign = wp_parse_args( $args, $defaults );

		return $campaign;
	} // END cocart_campaign()

	/**
	 * Returns an array of CoCart add-ons listed on WordPress.org
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.1.0 Introduced.
	 *
	 * @return array The CoCart add-ons.
	 */
	public static function get_wporg_cocart_plugins() {
		return array(
			'cocart-cors',
			'cocart-get-cart-enhanced',
			'cocart-carts-in-session',
		);
	} // ENF get_wporg_cocart_plugins()

	/**
	 * Seconds to words.
	 *
	 * Forked from: https://github.com/thatplugincompany/login-designer/blob/master/includes/admin/class-login-designer-feedback.php
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param string $seconds Seconds in time.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The time in words.
	 */
	public static function cocart_seconds_to_words( $seconds ) {
		// Get the years.
		$years = ( intval( $seconds ) / YEAR_IN_SECONDS ) % 100;
		if ( $years > 1 ) {
			/* translators: %s: Number of years */
			return sprintf( __( '%s years', 'cart-rest-api-for-woocommerce' ), $years );
		} elseif ( $years > 0 ) {
			return __( 'a year', 'cart-rest-api-for-woocommerce' );
		}

		// Get the months.
		$months = ( intval( $seconds ) / MONTH_IN_SECONDS ) % 52;
		if ( $months > 1 ) {
			/* translators: %s: Number of months */
			return sprintf( __( '%s months', 'cart-rest-api-for-woocommerce' ), $months );
		} elseif ( $months > 0 ) {
			return __( '1 month', 'cart-rest-api-for-woocommerce' );
		}

		// Get the weeks.
		$weeks = ( intval( $seconds ) / WEEK_IN_SECONDS ) % 52;
		if ( $weeks > 1 ) {
			/* translators: %s: Number of weeks */
			return sprintf( __( '%s weeks', 'cart-rest-api-for-woocommerce' ), $weeks );
		} elseif ( $weeks > 0 ) {
			return __( 'a week', 'cart-rest-api-for-woocommerce' );
		}

		// Get the days.
		$days = ( intval( $seconds ) / DAY_IN_SECONDS ) % 7;
		if ( $days > 1 ) {
			/* translators: %s: Number of days */
			return sprintf( __( '%s days', 'cart-rest-api-for-woocommerce' ), $days );
		} elseif ( $days > 0 ) {
			return __( 'a day', 'cart-rest-api-for-woocommerce' );
		}

		// Get the hours.
		$hours = ( intval( $seconds ) / HOUR_IN_SECONDS ) % 24;
		if ( $hours > 1 ) {
			/* translators: %s: Number of hours */
			return sprintf( __( '%s hours', 'cart-rest-api-for-woocommerce' ), $hours );
		} elseif ( $hours > 0 ) {
			return __( 'an hour', 'cart-rest-api-for-woocommerce' );
		}

		// Get the minutes.
		$minutes = ( intval( $seconds ) / MINUTE_IN_SECONDS ) % 60;
		if ( $minutes > 1 ) {
			/* translators: %s: Number of minutes */
			return sprintf( __( '%s minutes', 'cart-rest-api-for-woocommerce' ), $minutes );
		} elseif ( $minutes > 0 ) {
			return __( 'a minute', 'cart-rest-api-for-woocommerce' );
		}

		// Get the seconds.
		$seconds = intval( $seconds ) % 60;
		if ( $seconds > 1 ) {
			/* translators: %s: Number of seconds */
			return sprintf( __( '%s seconds', 'cart-rest-api-for-woocommerce' ), $seconds );
		} elseif ( $seconds > 0 ) {
			return __( 'a second', 'cart-rest-api-for-woocommerce' );
		}
	} // END cocart_seconds_to_words()

	/**
	 * Check how long CoCart has been active for.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.3.0 Introduced.
	 * @version 2.8.3
	 *
	 * @param int $seconds Time in seconds to check.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return boolean|int Whether or not CoCart has been active for $seconds.
	 */
	public static function cocart_active_for( $seconds = '' ) {
		if ( empty( $seconds ) ) {
			return true;
		}

		// Getting install timestamp.
		$cocart_installed = get_option( 'cocart_install_date', false );

		if ( false === $cocart_installed ) {
			return false;
		}

		return ( ( time() - $cocart_installed ) >= $seconds );
	} // END cocart_active_for()

	/**
	 * Get current admin page URL.
	 *
	 * Returns an empty string if it cannot generate a URL.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since   2.6.0 Introduced.
	 * @version 3.0.0
	 *
	 * @return string The current admin page URL.
	 */
	public static function cocart_get_current_admin_url() {
		$uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$uri = preg_replace( '|^.*/wp-admin/|i', '', $uri );

		if ( ! $uri ) {
			return '';
		}

		return remove_query_arg( array( 'cocart-hide-notice', '_cocart_notice_nonce' ), admin_url( $uri ) );
	} // END cocart_get_current_admin_url()

	/**
	 * Determines if the server environment is compatible with this plugin.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 *
	 * @param string $required_php Required PHP version.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return boolean True if the server environment is compatible. False otherwise.
	 */
	public static function is_environment_compatible( $required_php = '' ) {
		if ( empty( $required_php ) ) {
			$required_php = Core::$required_php;
		}

		return version_compare( PHP_VERSION, $required_php, '>=' );
	} // END is_environment_compatible()

	/**
	 * Gets the message for display when the environment is incompatible with this plugin.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.6.0 Introduced.
	 * @since 4.0.0 Added $plugin_name and $required_php parameters.
	 *
	 * @param string $plugin_name Plugin name.
	 * @param string $required_php Required PHP version.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The message for display when the environment is incompatible with this plugin.
	 */
	public static function get_environment_message( $plugin_name = 'CoCart', $required_php = '' ) {
		if ( empty( $required_php ) ) {
			$required_php = Core::$required_php;
		}

		/* translators: 1: Plugin Name, 2: Required PHP version */
		return sprintf( esc_html__( 'The minimum PHP version required for %1$s is %2$s. You are running %3$s.', 'cart-rest-api-for-woocommerce' ), $plugin_name, $required_php, self::get_php_version() );
	} // END get_environment_message()

	/**
	 * Collects the additional data necessary for the shortlink.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 * @since 4.0.0 Added statues of the site.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return array The shortlink data.
	 */
	protected static function collect_additional_shortlink_data() {
		$memory = cocart_let_to_num( WP_MEMORY_LIMIT );

		if ( function_exists( 'memory_get_usage' ) ) {
			$memory = max( $memory, cocart_let_to_num( @ini_get( 'memory_limit' ) ) ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		// WordPress 5.5+ environment type specification.
		// 'production' is the default in WP, thus using it as a default here, too.
		$environment_type = 'production';
		if ( function_exists( 'wp_get_environment_type' ) ) {
			$environment_type = wp_get_environment_type();
		}

		return array(
			'php_version'       => self::get_php_version(),
			'wp_version'        => self::get_wordpress_version(),
			'wc_version'        => self::get_wc_version(),
			'cocart_version'    => self::get_cocart_version(),
			'days_active'       => self::get_days_active(),
			'debug_mode'        => ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? 'Yes' : 'No',
			'memory_limit'      => esc_html( size_format( $memory ) ),
			'user_language'     => self::get_user_language(),
			'multisite'         => Status::is_multi_network() ? 'Yes' : 'No',
			'environment_type'  => $environment_type,
			'is_offline_mode'   => Status::is_offline_mode() ? 'Yes' : 'No',
			'is_local_site'     => Status::is_local_site() ? 'Yes' : 'No',
			'is_staging_site'   => Status::is_staging_site() ? 'Yes' : 'No',
			'is_vip_site'       => Status::is_vip_site() ? 'Yes' : 'No',
			'is_white_labelled' => self::is_white_labelled() ? 'Yes' : 'No',
		);
	} // END collect_additional_shortlink_data()

	/**
	 * Builds a URL to use in the plugin as shortlink.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 * @since 4.0.0 Added check if tracking is allowed.
	 *
	 * @param string $url The URL to build upon.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The final URL.
	 */
	public static function build_shortlink( $url ) {
		$client   = self::appsero_client();
		$insights = $client->insights();

		if ( ! $insights->tracking_allowed() ) {
			return $url;
		}

		return add_query_arg( self::collect_additional_shortlink_data(), $url );
	} // END build_shortlink()

	/**
	 * Gets the current site's PHP version, without the extra info.
	 *
	 * @access private
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The PHP version.
	 */
	private static function get_php_version() {
		$version = explode( '.', PHP_VERSION );

		return (int) $version[0] . '.' . (int) $version[1];
	} // END get_php_version()

	/**
	 * Gets the current site's WordPress version.
	 *
	 * @access protected
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The wp_version.
	 */
	protected static function get_wordpress_version() {
		return $GLOBALS['wp_version'];
	} // END get_wordpress_version()

	/**
	 * Gets the number of days the plugin has been active.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 * @since 4.0.0 Changed from "private" access to "public" access.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return int The number of days the plugin is active.
	 */
	public static function get_days_active() {
		$date_activated = get_option( 'cocart_install_date', time() );
		$datediff       = ( time() - $date_activated );
		$days           = (int) round( $datediff / DAY_IN_SECONDS );

		return $days;
	} // END get_days_active()

	/**
	 * Gets the user's language.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 2.7.2 Introduced.
	 * @since 4.0.0 Changed from "private" access to "public" access.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return string The user's language.
	 */
	public static function get_user_language() {
		if ( function_exists( 'get_user_locale' ) ) {
			return get_user_locale();
		}

		return false;
	} // END get_user_language()

	/**
	 * Checks if CoCart is white labelled.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.0.12 Introduced.
	 *
	 * @return boolean True if white labelled, false otherwise.
	 */
	public static function is_white_labelled() {
		if ( ! defined( 'COCART_WHITE_LABEL' ) || false === COCART_WHITE_LABEL ) {
			return false;
		}

		return true;
	} // END is_white_labelled()

	/**
	 * Returns true if the WC Admin feature is installed and enabled.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 3.2.0 Introduced.
	 *
	 * @return boolean True if the WC Admin feature is installed and enabled, false otherwise.
	 */
	public static function is_wc_admin_enabled() {
		if ( is_null( self::$is_wc_admin_enabled ) ) {
			$enabled = false;

			if ( function_exists( 'wc_admin_connect_page' ) ) {
				$enabled = true;

				if ( apply_filters( 'woocommerce_admin_disabled', false ) ) {
					$enabled = false;
				}
			}

			self::$is_wc_admin_enabled = $enabled;
		}

		return self::$is_wc_admin_enabled;
	} // END is_wc_admin_enabled()

	/**
	 * Sets the Appsero Client and returns the class object.
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @since 4.0.0 Introduced.
	 *
	 * @ignore Function ignored when parsed into Code Reference.
	 *
	 * @return object
	 */
	public static function appsero_client() {
		return new \Appsero\Client( '3898b319-80b0-4f93-bc96-1809486b15fd', 'CoCart - Headless ecommerce', COCART_FILE );
	} // END appsero_client()

} // END class

return new Help();
