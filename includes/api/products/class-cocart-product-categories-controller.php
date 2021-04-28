<?php
/**
 * CoCart- Product Categories controller
 *
 * Handles requests to the products/categories endpoint.
 *
 * @author   Sébastien Dumont
 * @category API
 * @package  CoCart\API\Products\v2
 * @since    3.1.0
 * @license  GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST API Product Categories controller class.
 *
 * @package CoCart/API
 * @extends CoCart_REST_Terms_V2_Controller
 */
class CoCart_Product_Categories_V2_Controller extends CoCart_REST_Terms_V2_Controller {

	/**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'products/categories';

	/**
	 * Taxonomy.
	 *
	 * @var string
	 */
	protected $taxonomy = 'product_cat';

}
