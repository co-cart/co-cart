<?php
/**
 * Wrapper for PHP DateTime which adds support for gmt/utc offset when a
 * timezone is absent
 *
 * @author  Sébastien Dumont
 * @package CoCart\Classes
 * @since   3.0.0
 * @version 3.7.11
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Datetime class.
 */
class CoCart_DateTime extends DateTime {

	/**
	 * UTC Offset, if needed. Only used when a timezone is not set. When
	 * timezones are used this will equal 0.
	 *
	 * @access protected
	 *
	 * @var integer
	 */
	protected $utc_offset = 0;

	/**
	 * Output an ISO 8601 date string in local (WordPress) timezone.
	 *
	 * @access public
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->format( DATE_ATOM );
	}

	/**
	 * Set UTC offset - this is a fixed offset instead of a timezone.
	 *
	 * @access public
	 *
	 * @param int $offset Offset.
	 */
	public function set_utc_offset( $offset ) {
		$this->utc_offset = intval( $offset );
	}

	/**
	 * Get UTC offset if set, or default to the DateTime object's offset.
	 *
	 * @access public
	 */
	#[\ReturnTypeWillChange]
	public function getOffset() {
		return $this->utc_offset ? $this->utc_offset : parent::getOffset();
	}

	/**
	 * Set timezone.
	 *
	 * @access public
	 *
	 * @param DateTimeZone $timezone DateTimeZone instance.
	 *
	 * @return DateTime
	 */
	#[\ReturnTypeWillChange]
	public function setTimezone( $timezone ) {
		$this->utc_offset = 0;

		return parent::setTimezone( $timezone );
	}

	/**
	 * Missing in PHP 5.2 so just here so it can be supported consistently.
	 *
	 * @access public
	 *
	 * @return int
	 */
	#[\ReturnTypeWillChange]
	public function getTimestamp() {
		return method_exists( 'DateTime', 'getTimestamp' ) ? parent::getTimestamp() : $this->format( 'U' );
	}

	/**
	 * Get the timestamp with the WordPress timezone offset added or subtracted.
	 *
	 * @access public
	 * @return int
	 */
	public function getOffsetTimestamp() {
		return $this->getTimestamp() + $this->getOffset();
	}

	/**
	 * Format a date based on the offset timestamp.
	 *
	 * @access public
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function date( $format ) {
		return gmdate( $format, $this->getOffsetTimestamp() );
	}

	/**
	 * Return a localized date based on offset timestamp. Wrapper for date_i18n function.
	 *
	 * @access public
	 *
	 * @param string $format Date format.
	 *
	 * @return string
	 */
	public function date_i18n( $format = 'Y-m-d' ) {
		return date_i18n( $format, $this->getOffsetTimestamp() );
	}

} // END class
