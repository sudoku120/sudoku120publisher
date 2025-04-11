<?php
/**
 * Admin Menu and Settings for Sudoku120 Publisher Plugin
 *
 * This file handles the creation of the admin menu and the inclusion of necessary files
 * for each admin page in the WordPress dashboard for the Sudoku120 Publisher plugin.
 *
 * It adds a main menu item for the Sudoku list and a submenu for the settings. Additionally,
 * if the proxy feature is active, it includes a menu item for managing the proxy URLs.
 */

/**
 * Prevent direct access to this file
 *
 * Ensures that the file is accessed through WordPress and not directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Adds the Sudoku120 Publisher menu to the WordPress admin panel.
 *
 * @return void
 */
function sudoku120publisher_menu() {
	// Main menu for the Sudoku list.
	add_menu_page(
		esc_html__( 'Sudoku120 Publisher', 'sudoku120publisher' ),
		esc_html__( 'Sudoku120', 'sudoku120publisher' ),
		'manage_options',
		'sudoku120publisher',
		'sudoku120publisher_sudoku_list_page',
		'dashicons-list-view',
		21
	);

	// Submenu for settings.
	add_submenu_page(
		'sudoku120publisher',
		esc_html__( 'Settings', 'sudoku120publisher' ),
		esc_html__( 'Settings', 'sudoku120publisher' ),
		'manage_options',
		'sudoku120publisher_settings',
		'sudoku120publisher_settings_page'
	);

	// Only display if the proxy is active.
	if ( get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE ) === '1' ) {
		add_menu_page(
			esc_html__( 'Sudoku120 Proxy', 'sudoku120publisher' ),
			esc_html__( 'Sudoku120 Proxy', 'sudoku120publisher' ),
			'manage_options',
			'sudoku120publisher_proxy',
			'sudoku120publisher_proxy_url_page',
			'dashicons-networking',
			22
		);
	}
}

add_action( 'admin_menu', 'sudoku120publisher_menu' );

/**
 * Includes the necessary files for each admin page.
 *
 * Based on the 'page' parameter from the query string, this function includes the respective
 * PHP file for the settings page, proxy URL page, or Sudoku list page.
 *
 * @return void
 */
function sudoku120publisher_include_admin_files() {
	if ( isset( $_GET['page'] ) ) {
		$page = sanitize_text_field( wp_unslash( $_GET['page'] ) );

		switch ( $page ) {
			case 'sudoku120publisher_settings':
				require_once SUDOKU120PUBLISHER_PATH . 'inc/admin/sudoku120publisher-settings.php';
				break;
			case 'sudoku120publisher_proxy':
				require_once SUDOKU120PUBLISHER_PATH . 'inc/admin/sudoku120publisher-proxy-urls.php';
				break;
			case 'sudoku120publisher':
				require_once SUDOKU120PUBLISHER_PATH . 'inc/admin/sudoku120publisher-sudoku-list.php';
				break;
		}
	}
}

add_action( 'admin_init', 'sudoku120publisher_include_admin_files' );
