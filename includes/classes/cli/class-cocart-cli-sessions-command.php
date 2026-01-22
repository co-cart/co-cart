<?php
/**
 * WP-CLI: CoCart Sessions command class file.
 *
 * @author  Sébastien Dumont
 * @package CoCart\CLI
 * @since   5.0.0 Introduced.
 * @license GPL-3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages CoCart via CLI.
 *
 * @version 5.0.0
 * @package CoCart\CLI
 */
class CoCart_CLI_Sessions_Command {

	/**
	 * Registers the sessions command.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function register_commands() {
		WP_CLI::add_command(
			'cocart sessions', // Command.
			array( __CLASS__, 'get_sessions' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Lists cart sessions in the database.', 'cocart-core' ),
				'synopsis'  => array(
					array(
						'type'        => 'assoc',
						'name'        => 'limit',
						'description' => __( 'Limit the number of sessions to display.', 'cocart-core' ),
						'optional'    => true,
						'default'     => 25,
					),
					array(
						'type'        => 'assoc',
						'name'        => 'offset',
						'description' => __( 'Offset the sessions list.', 'cocart-core' ),
						'optional'    => true,
						'default'     => 0,
					),
					array(
						'type'        => 'assoc',
						'name'        => 'orderby',
						'description' => __( 'Order the results by a specific column.', 'cocart-core' ),
						'optional'    => true,
						'default'     => 'cart_created',
						'options'     => array( 'cart_created', 'cart_expiry' ),
					),
					array(
						'type'        => 'assoc',
						'name'        => 'order',
						'description' => __( 'Order the results in ascending or descending order.', 'cocart-core' ),
						'optional'    => true,
						'default'     => 'DESC',
						'options'     => array( 'ASC', 'DESC' ),
					),
					array(
						'type'        => 'assoc',
						'name'        => 'format',
						'description' => __( 'Render output in a particular format.', 'cocart-core' ),
						'optional'    => true,
						'default'     => 'table',
						'options'     => array( 'table', 'json', 'csv', 'yaml' ),
					),
				),
			)
		);

		WP_CLI::add_command(
			'cocart sessions exists', // Command.
			array( __CLASS__, 'session_exists' ), // Callback.
			array( // Arguments.
				'shortdesc' => __( 'Checks if a cart ID or cart key exists in the database.', 'cocart-core' ),
				'synopsis'  => array(
					array(
						'type'        => 'positional',
						'name'        => 'identifier',
						'description' => __( 'The cart ID or cart key to check.', 'cocart-core' ),
						'optional'    => false,
					),
				),
			)
		);
	} // END register_commands()

	/**
	 * Provides usage instructions for the CLI command.
	 *
	 * @access public
	 *
	 * @static
	 */
	public static function get_usage() {
		WP_CLI::line( 'Usage: wp cocart sessions [--limit=<number>] [--offset=<number>] [--orderby=<column>] [--order=<order>] [--format=<format>]' );
		WP_CLI::line( 'Example: wp cocart sessions --limit=5 --offset=10 --orderby=cart_expiry --order=ASC --format=json' );
	}

	/**
	 * List sessions.
	 *
	 * ## OPTIONS
	 *
	 * [--limit=<number>]
	 * : Limit the number of sessions to display.
	 * ---
	 * default: 25
	 *
	 * [--offset=<number>]
	 * : Offset the sessions list.
	 * ---
	 * default: 0
	 *
	 * [--orderby=<column>]
	 * : Order the results by a specific column.
	 * ---
	 * default: cart_created
	 * options:
	 *   - cart_created
	 *   - cart_expiry
	 *
	 * [--order=<order>]
	 * : Order the results in ascending or descending order.
	 * ---
	 * default: DESC
	 * options:
	 *   - ASC
	 *   - DESC
	 *
	 * [--format=<format>]
	 * : Render output in a particular format.
	 * ---
	 * default: table
	 * options:
	 *   - table
	 *   - json
	 *   - csv
	 *   - yaml
	 *
	 * ## EXAMPLES
	 *
	 * wp cocart sessions --limit=5 --offset=10 --orderby=cart_expiry --order=ASC --format=json
	 *
	 * @when after_wp_load
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param array $args       WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function get_sessions( array $args, array $assoc_args ) {
		$limit   = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : 25;
		$offset  = isset( $assoc_args['offset'] ) ? intval( $assoc_args['offset'] ) : 0;
		$orderby = isset( $assoc_args['orderby'] ) ? $assoc_args['orderby'] : 'cart_created';
		$order   = isset( $assoc_args['order'] ) ? $assoc_args['order'] : 'DESC';
		$format  = isset( $assoc_args['format'] ) ? $assoc_args['format'] : 'table';

		$valid_formats = array( 'table', 'json', 'csv', 'yaml' );

		if ( ! in_array( $format, $valid_formats, true ) ) {
			WP_CLI::error( 'Invalid format. Valid formats are: table, json, csv, yaml.' );
			return;
		}

		if ( isset( $assoc_args['page'] ) && empty( $assoc_args['offset'] ) ) {
			$offset = $limit * ( absint( $assoc_args['page'] ) - 1 );
		}

		global $wpdb;

		$wpdb->hide_errors();

		// Fetch sessions from the database.
		$query  = "SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}cocart_carts";
		$query .= ' ORDER BY ' . esc_sql( $orderby ) . ' ' . esc_sql( $order );
		$query .= ' LIMIT %d OFFSET %d';

		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( $query, $limit, $offset ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		);

		$total_results = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$max_pages     = ceil( $total_results / $limit );

		if ( empty( $results ) ) {
			WP_CLI::log( 'No sessions found.' );
			return;
		}

		// Contains the results of sessions.
		$sessions = array();

		foreach ( $results as $cart ) {
			$cart_value = maybe_unserialize( $cart->cart_value );
			$customer   = maybe_unserialize( $cart_value['customer'] );

			$email      = ! empty( $customer['email'] ) ? $customer['email'] : '';
			$first_name = ! empty( $customer['first_name'] ) ? $customer['first_name'] : '';
			$last_name  = ! empty( $customer['last_name'] ) ? ' ' . $customer['last_name'] : '';

			$name = trim( $first_name . ' ' . $last_name );

			$expiry_time  = gmdate( 'm/d/Y H:i:s', $cart->cart_expiry );
			$current_time = time();
			$expiry_color = ( $cart->cart_expiry - $current_time ) < 86400 ? '%y' : '%n'; // Yellow if expiring in less than 24 hours, normal otherwise.

			$sessions[] = array(
				'cart_id'         => $cart->cart_id,
				'cart_key'        => $cart->cart_key,
				'customers_name'  => $name,
				'customers_email' => $email,
				'created'         => gmdate( 'm/d/Y H:i:s', $cart->cart_created ),
				'expiry'          => WP_CLI::colorize( $expiry_color . $expiry_time . '%n' ),
				'source'          => $cart->cart_source,
			);
		}

		switch ( $format ) {
			case 'json':
				WP_CLI::print_value( $sessions, array( 'format' => 'json' ) );
				break;
			default:
				WP_CLI\Utils\format_items( $format, $sessions, array( 'cart_id', 'cart_key', 'customers_name', 'customers_email', 'created', 'expiry', 'source' ) );
				break;
		}
	} // END get_sessions()

	/**
	 * Checks if a cart ID or cart key exists in the database.
	 *
	 * ## OPTIONS
	 *
	 * <identifier>
	 * : The cart ID or cart key to check.
	 *
	 * ## EXAMPLES
	 *
	 * wp cocart sessions exists <identifier>
	 *
	 * @when after_wp_load
	 *
	 * @access public
	 *
	 * @static
	 *
	 * @param array $args       WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public static function session_exists( array $args, array $assoc_args ) {
		$identifier = $args[0];

		if ( empty( $identifier ) ) {
			WP_CLI::warning( __( 'Identifier is required!', 'cocart-core' ) );
			WP_CLI::log(
				WP_CLI::colorize(
					'%7%R 😞 ' . __( 'You did not specify a cart ID or cart key.', 'cocart-core' ) . '%n'
				)
			);
			return;
		}

		global $wpdb;

		$wpdb->hide_errors();

		// Check if the cart ID or cart key exists in the database.
		$query = $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}cocart_carts WHERE cart_id = %s OR cart_key = %s", $identifier, $identifier );
		$count = $wpdb->get_var( $query ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		if ( $count > 0 ) {
			WP_CLI::log(
				sprint_f(
					/* translators: %s = Identifier */
					__( 'Session ID %s exists.', 'cocart-core' ),
					$identifier
				)
			);
		} else {
			WP_CLI::error(
				sprint_f(
					/* translators: %s = Identifier */
					__( 'Session ID %s does not exist.', 'cocart-core' ),
					$identifier
				)
			);
		}
	} // END session_exists()
} // END class
