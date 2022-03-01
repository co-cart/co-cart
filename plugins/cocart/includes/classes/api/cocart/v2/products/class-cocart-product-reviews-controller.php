<?php
/**
 * CoCart - Product Reviews controller
 *
 * Handles requests to the /products/reviews/ endpoint.
 *
 * @author  Sébastien Dumont
 * @package CoCart\API\Products\v2
 * @since   3.1.0
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CoCart REST API v2 - Product Reviews controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Reviews_V2_Controller
 */
class CoCart_Product_Reviews_V2_Controller extends CoCart_Product_Reviews_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

}
