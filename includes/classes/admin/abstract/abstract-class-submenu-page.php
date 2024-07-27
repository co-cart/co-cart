<?php
/**
 * Abstract: CoCart Submenu Page.
 *
 * @author  Sébastien Dumont
 * @package CoCart\Admin\Settings
 * @since   3.10.0 Introduced.
 * @license GPL-2.0+
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base class to add custom submenu pages.
 */
abstract class CoCart_Submenu_Page {

	/**
	 * The menu page under which the submenu page should be added.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $parent_slug;

	/**
	 * The title of the submenu page.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $page_title;

	/**
	 * The title that should appear in the menu.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $menu_title;

	/**
	 * The user capability required to view this page.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $capability;

	/**
	 * The menu.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $menu_slug;

	/**
	 * The current section url query arg.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $current_section;

	/**
	 * The current tab url query arg.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $current_tab;

	/**
	 * The admin path to the page, used in admin_url.
	 *
	 * @access protected
	 *
	 * @var string
	 */
	protected $admin_url;

	/**
	 * Constructor.
	 *
	 * @access public
	 *
	 * @param string $page_title Page title.
	 * @param string $menu_title Menu title.
	 * @param string $capability Capability.
	 * @param string $menu_slug  Menu Slug.
	 */
	public function __construct( $page_title = '', $menu_title = '', $capability = '', $menu_slug = '' ) {
		$this->parent_slug = 'cocart';
		$this->page_title  = $page_title;
		$this->menu_title  = $menu_title;
		$this->capability  = $capability;
		$this->menu_slug   = $menu_slug;

		$this->current_section = ( ! empty( $_GET['section'] ) ? trim( sanitize_key( wp_unslash( $_GET['section'] ) ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$this->current_tab     = ( ! empty( $_GET['tab'] ) ? trim( sanitize_key( wp_unslash( $_GET['tab'] ) ) ) : '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$this->admin_url = add_query_arg( array( 'page' => $this->menu_slug ), 'admin.php' );

		add_action( 'admin_menu', array( $this, 'add_submenu_page' ), 10 );

		$this->init();
	} // END __construct()

	/**
	 * Helper init method to avoid rewriting of the __construct method by subclasses.
	 *
	 * @access protected
	 */
	protected function init() {}

	/**
	 * Callback to add the submenu page.
	 *
	 * @access public
	 */
	public function add_submenu_page() {
		if ( empty( $this->page_title ) || empty( $this->menu_title ) || empty( $this->capability ) || empty( $this->menu_slug ) ) {
			return;
		}

		add_submenu_page( $this->parent_slug, 'CoCart - ' . $this->page_title, $this->menu_title, $this->capability, $this->menu_slug, array( $this, 'output' ) );
	} // END add_submenu_page()

	/**
	 * Callback for the HTML output for the page.
	 *
	 * @access public
	 */
	public function output() {}
} // END class
