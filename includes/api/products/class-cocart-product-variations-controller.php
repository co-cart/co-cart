<?php
/**
 * CoCart - Product Variations controller
 *
 * Handles requests to the /products/variations endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

defined( 'ABSPATH' ) || exit;

/**
 * REST API variations controller class.
 *
 * @package CoCart Products/API
 * @extends CoCart_Product_Variations_Controller
 */
class CoCart_Product_Variations_V2_Controller extends CoCart_Product_Variations_Controller {

	/**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'cocart/v2';

}
