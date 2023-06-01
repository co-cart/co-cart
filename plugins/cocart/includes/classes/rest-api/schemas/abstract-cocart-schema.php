<?php
/**
 * Abstract: CoCart\Schemas\AbstractSchema.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Abstracts
 * @since   4.0.0 Introduced.
 */

namespace CoCart\Schemas;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Abstract Schema Class
 *
 * For REST Route Schemas.
 */
abstract class AbstractSchema {

	/**
	 * The schema item name.
	 *
	 * Note: Title must be all lowercase.
	 *
	 * @var string
	 */
	protected $title = 'schema';

	/**
	 * Returns the full item schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_item_schema() {
		return array(
			'$schema'    => 'https://json-schema.org/draft-04/schema#',
			'title'      => $this->title,
			'type'       => 'object',
			'properties' => $this->get_properties(),
		);
	} // END get_item_schema()

	/**
	 * Return schema properties.
	 *
	 * @access public
	 *
	 * @return array
	 */
	abstract public function get_properties();

	/**
	 * Returns the public schema.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_public_item_schema() {
		return $this->get_item_schema();
	} // END get_public_item_schema()

	/**
	 * Force all schema properties to be readonly.
	 *
	 * @access protected
	 *
	 * @param array $properties Schema.
	 *
	 * @return array Updated schema.
	 */
	protected function force_schema_readonly( $properties ) {
		return array_map(
			function( $property ) {
				$property['readonly'] = true;
				if ( isset( $property['items']['properties'] ) ) {
					$property['items']['properties'] = $this->force_schema_readonly( $property['items']['properties'] );
				}
				return $property;
			},
			(array) $properties
		);
	} // END force_schema_readonly()

} // END class
