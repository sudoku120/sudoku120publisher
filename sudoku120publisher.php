<?php
/*
Plugin Name: Sudoku120 Publisher
Plugin URI: https://github.com/sudoku120/sudoku120publisher
Description: Plugin to integrate the Sudoku120.com webmaster Sudoku in WordPress
Version: 1.0.2
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.4
Author: msdevcoder
Author URI: https://webmaster.sudoku120.com/
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Text Domain: sudoku120publisher
Domain Path: /lang
GitHub Plugin URI: https://github.com/sudoku120/sudoku120publisher
GitHub Branch:     main
*/

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

require_once plugin_dir_path( __FILE__ ) . 'inc/const-defines.php';



/**
 * Main plugin class to handle activation, deactivation, and uninstall hooks.
 */
class Sudoku120Publisher {

	/**
	 * Constructor method to include necessary files and register hooks.
	 */
	public function __construct() {

		// Include the necessary files.
		require_once SUDOKU120PUBLISHER_PATH . 'functions.php';
		require_once SUDOKU120PUBLISHER_PATH . 'inc/shortcode.php';
		require_once SUDOKU120PUBLISHER_PATH . 'inc/class-sudoku120publisher-activate.php';
		require_once SUDOKU120PUBLISHER_PATH . 'inc/admin/admin-menus.php';
		require_once SUDOKU120PUBLISHER_PATH . 'inc/reverse-proxy.php';

		// Register activation and uninstall hooks.
		register_activation_hook( __FILE__, array( 'Sudoku120Publisher_Activate', 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'sudoku120publisher_deactivate' ) );

		// Fonts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_sudoku120publisher_fonts' ) );

		// Hook into the plugin action links.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'sudoku120publisher_add_settings_link' ) );

		add_filter( 'query_vars', array( $this, 'sudoku120publisher_query_vars' ) );

		add_filter( 'the_content', 'sudoku120publisher_fix_link_rel', 999 );

		add_action( 'init', array( $this, 'load_textdomain_with_fallback' ) );

		add_action( 'init', array( $this, 'set_version_const' ) );

		add_action(
			'after_switch_theme',
			function () {
				update_option( 'sudoku120publisher_needs_rewrite_flush', '1' );
			}
		);

		add_action(
			'init',
			function () {

				if (
				get_option( 'sudoku120publisher_plugin_active' ) === '1' &&
				get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE ) === '1' &&
				( ! ( isset( $_GET['action'] ) && 'deactivate' === $_GET['action'] &&
					isset( $_GET['plugin'] ) && plugin_basename( __FILE__ ) === $_GET['plugin'] ) )
				) {
					$proxy_slug = SUDOKU120PUBLISHER_PROXY_SLUG;
					add_rewrite_rule(
						"{$proxy_slug}/([a-f0-9]{32})/?(.*)$",
						'index.php?sudoku120publisher_proxy=1&proxy_uuid=$matches[1]&path=$matches[2]',
						'top'
					);
				}

				if ( get_option( 'sudoku120publisher_needs_rewrite_flush' ) === '1' ) {
					flush_rewrite_rules();
					delete_option( 'sudoku120publisher_needs_rewrite_flush' );
				}
			}
		);
	}

	/**
	 * Enqueue the necessary Google Fonts for the plugin.
	 */
	public static function enqueue_sudoku120publisher_fonts() {
		wp_enqueue_style(
			'sudoku120publisher-fonts',
			'https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700&display=swap',
			array(),
			null
		);
	}

	/**
	 * Get the plugin version from the plugin metadata and store it in a constant.
	 * This constant is used in wp_enqueue_* functions to append the correct version to script/style URLs.
	 */
	public function set_version_const() {
		if ( ! defined( 'SUDOKU120PUBLISHER_VERSION' ) ) {
			if ( ! function_exists( 'get_plugin_data' ) ) {
					require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
						$plugin_data = get_plugin_data( __FILE__ );
						define( 'SUDOKU120PUBLISHER_VERSION', $plugin_data['Version'] );
		}
	}

	/**
	 * Load the .mo translation file for the language when no lang/region .mo file is there
	 */
	public function load_textdomain_with_fallback() {
		$locale        = determine_locale();
		$language_code = substr( $locale, 0, 2 );

		$path = plugin_dir_path( __FILE__ ) . 'lang/';

		$full_mo  = $path . "sudoku120publisher-{$locale}.mo";
		$short_mo = $path . "sudoku120publisher-{$language_code}.mo";

		if ( file_exists( $full_mo ) ) {
			load_textdomain( 'sudoku120publisher', $full_mo );
		} elseif ( file_exists( $short_mo ) ) {
			load_textdomain( 'sudoku120publisher', $short_mo );
		}
	}

	/**
	 * Add settings link to the plugin list.
	 *
	 * @param array $links Existing plugin action links.
	 * @return array Modified plugin action links with the settings link added.
	 */
	public function sudoku120publisher_add_settings_link( $links ) {
		$settings_link = '<a href="admin.php?page=sudoku120publisher_settings">Settings</a>';
		$links[]       = $settings_link;

		$video_url = 'https://www.youtube.com/watch?v=OAV-H_LYO2Y';
		$links[]   = '<a href="' . esc_url( $video_url ) . '" target="_blank">' . esc_html__( 'Tutorial Video', 'sudoku120publisher' ) . ' (Youtube)</a>';
		return $links;
	}


	/**
	 * Remove the rewriterule when plugin get deactivated
	 */
	public function sudoku120publisher_deactivate() {
		update_option( 'sudoku120publisher_plugin_active', '0' );
		flush_rewrite_rules();
	}

	/**
	 * Registers custom query variables for the Sudoku120 Publisher plugin.
	 *
	 * @param array $query_vars The existing query variables.
	 * @return array Modified query variables including custom ones.
	 */
	public function sudoku120publisher_query_vars( $query_vars ) {
		$query_vars[] = 'sudoku120publisher_proxy';  // To flag this request as proxy request.
		$query_vars[] = 'proxy_uuid';                // To get the UUID from the URL.
		$query_vars[] = 'path';                      // To capture the remaining path of the URL.
		return $query_vars;
	}
}


// Initialize the plugin.
new Sudoku120Publisher();
