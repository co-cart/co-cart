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
	 * Lists cart sessions in the database.
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
	 * [--page=<number>]
	 * : Page number to display, starting at 1. Overrides --offset when provided.
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
	 * wp cocart sessions list
	 * wp cocart sessions list --limit=5 --offset=10 --orderby=cart_expiry --order=ASC --format=json
	 *
	 * @subcommand list
	 *
	 * @when after_wp_load
	 *
	 * @access public
	 *
	 * @param array $args       WP-CLI positional arguments.
	 * @param array $assoc_args WP-CLI associative arguments.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function list_items( array $args, array $assoc_args ) {
		$limit   = isset( $assoc_args['limit'] ) ? intval( $assoc_args['limit'] ) : 25;
		$offset  = isset( $assoc_args['offset'] ) ? intval( $assoc_args['offset'] ) : 0;
		$orderby = isset( $assoc_args['orderby'] ) ? $assoc_args['orderby'] : 'cart_created';
		$order   = isset( $assoc_args['order'] ) ? $assoc_args['order'] : 'DESC';
		$format  = isset( $assoc_args['format'] ) ? $assoc_args['format'] : 'table';

		$valid_formats = array( 'table', 'json', 'csv', 'yaml' );
		$valid_orderby = array( 'cart_created', 'cart_expiry' );
		$valid_order   = array( 'ASC', 'DESC' );

		if ( ! in_array( $format, $valid_formats, true ) ) {
			WP_CLI::error( __( 'Invalid format. Valid formats are: table, json, csv, yaml.', 'cocart-core' ) );
			return;
		}

		if ( ! in_array( $orderby, $valid_orderby, true ) ) {
			WP_CLI::error( __( 'Invalid orderby value. Valid values are: cart_created, cart_expiry.', 'cocart-core' ) );
			return;
		}

		if ( ! in_array( $order, $valid_order, true ) ) {
			WP_CLI::error( __( 'Invalid order value. Valid values are: ASC, DESC.', 'cocart-core' ) );
			return;
		}

		if ( isset( $assoc_args['page'] ) && empty( $assoc_args['offset'] ) ) {
			$page   = max( 1, absint( $assoc_args['page'] ) );
			$offset = $limit * ( $page - 1 );
		}

		global $wpdb;

		$wpdb->hide_errors();

		// Fetch sessions from the database.
		$results = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT SQL_CALC_FOUND_ROWS * FROM {$wpdb->prefix}cocart_carts ORDER BY " . esc_sql( $orderby ) . ' ' . esc_sql( $order ) . ' LIMIT %d OFFSET %d',
				$limit,
				$offset
			)
		);

		$total_results = $wpdb->get_var( 'SELECT FOUND_ROWS()' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$max_pages     = ceil( $total_results / $limit );

		if ( empty( $results ) ) {
			WP_CLI::log( __( 'No sessions found.', 'cocart-core' ) );
			return;
		}

		// Contains the results of sessions.
		$sessions = array();

		foreach ( $results as $cart ) {
			$cart_value = maybe_unserialize( $cart->cart_value );
			$customer   = isset( $cart_value['customer'] ) ? maybe_unserialize( $cart_value['customer'] ) : array();

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
	} // END list_items()

	/**
	 * Checks if a cart ID or cart key lookup in the database.
	 *
	 * ## OPTIONS
	 *
	 * <identifier>
	 * : The cart ID or cart key to check.
	 *
	 * ## EXAMPLES
	 *
	 * wp cocart sessions lookup <identifier>
	 *
	 * @when after_wp_load
	 *
	 * @access public
	 *
	 * @param array $args WP-CLI positional arguments.
	 *
	 * @global wpdb $wpdb WordPress database abstraction object.
	 */
	public function lookup( array $args ) {
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

		// Check if the cart ID or cart key lookup in the database.
		$count = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}cocart_carts WHERE cart_id = %s OR cart_key = %s", $identifier, $identifier )
		);

		if ( $count > 0 ) {
			WP_CLI::log(
				sprintf(
					/* translators: %s = Identifier */
					__( 'Session ID %s lookup.', 'cocart-core' ),
					$identifier
				)
			);
			return;
		}

		WP_CLI::error(
			sprintf(
				/* translators: %s = Identifier */
				__( 'Session ID %s does not exist.', 'cocart-core' ),
				$identifier
			)
		);
	} // END lookup()
} // END class
