<?php
/**
 * Plugin Activation Class for Sudoku120Publisher.
 *
 * This file contains the class responsible for handling the plugin activation process of the
 * Sudoku120Publisher plugin. During activation, the class ensures that necessary actions are taken,
 * such as adding error messages to the admin area, initializing plugin settings, and checking for
 * required conditions.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Class to handle plugin activation.
 */
class Sudoku120Publisher_Activate {

	/**
	 * Method to handle activation logic, such as creating database tables.
	 */
	public static function activate() {
		// Create necessary database tables for the plugin.
		sudoku120publisher_create_tables();

		// download files and copy them into uploads.
		self::check_and_activate_files();

		// Check and add plugin options.
		self::check_and_add_plugin_options();
	}




	/**
	 *  Checks and sets the options during plugin activation
	 */
	public static function check_and_add_plugin_options() {
		// Check if the 'domain' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_DOMAIN ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN, '' ); // Default to empty string.
		}

		// Check if the 'domain_status' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS, '' ); // Default to empty string.
		}

		// Check if the 'proxy_active' option is set. If not, set it with 'false'.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, '1' ); // Default to true.
		}

		// Check if the 'design' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_DESIGN ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_DESIGN, '' ); // Default to empty string.
		}
			// Check if the 'design' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_LINK_REL ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_LINK_REL, '1' ); // Default to true.
		}
					// Check if the 'design' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK, '1' ); // Default to true.
		}

			// Check if the 'sudoku div attr' option is set. If not, set it with an empty value.
		if ( ! get_option( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR ) ) {
			update_option( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR, SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT ); // Default.
		}

		if ( get_option( 'sudoku120publisher_admin_alert_sudoku' ) === false ) {
				add_option( 'sudoku120publisher_admin_alert_sudoku', 0 );
		}
		if ( get_option( 'sudoku120publisher_admin_alert_message' ) === false ) {
			add_option( 'sudoku120publisher_admin_alert_message', 0 );
		}

		update_option( 'sudoku120publisher_plugin_active', '1' );
		update_option( 'sudoku120publisher_needs_rewrite_flush', '1' );
	}

	/**
	 * Checks if the necessary files exist. If they do not, it triggers the download
	 */
	private static function check_and_activate_files() {
		$upload_dir    = wp_upload_dir();
		$missing_files = false;

		foreach ( SUDOKU120PUBLISHER_FILE_MAP as $zip_path => $local_path ) {
			$destination = $upload_dir['basedir'] . '/' . $local_path;
			if ( ! file_exists( $destination ) ) {
				$missing_files = true;
				break;
			}
		}

		if ( $missing_files ) {
			$result = self::download_and_copy_files();
			if ( is_wp_error( $result ) ) {
				self::add_error_message( esc_html__( 'Error during plugin activation: ', 'sudoku120publisher' ) . esc_html( $result->get_error_message() ) );
			}
		}
	}


	/**
	 * Download the needed files and copy them into the uploads folder
	 */
	private static function download_and_copy_files() {
			// Base URL for files.
			$base_url = SUDOKU120PUBLISHER_FILEDIR_URL;

			// Upload directory path.
			$upload_dir = wp_upload_dir();

			// Loop through the file map and download each file.
		foreach ( SUDOKU120PUBLISHER_FILE_MAP as $remote_file => $local_file ) {
			// Construct the full URL for the remote file.
			$file_url = $base_url . $remote_file;

			// Download the file.
			$temp_file = download_url( $file_url );

			// Check if there was an error with the download.
			if ( is_wp_error( $temp_file ) ) {
					continue; // Skip to the next file if download fails.
			}

			// Define the target file path in the uploads directory.
			$target_file     = $upload_dir['basedir'] . '/' . $local_file;
			$target_dir_path = dirname( $target_file );
			if ( ! wp_mkdir_p( $target_dir_path ) ) {
				return;
			}
			// Move the downloaded file to the target location.
			copy( $temp_file, $target_file );
					// delete temp file.
				wp_delete_file( $temp_file );

		}
	}



	/**
	 * Allows the admin to manually trigger the download, extraction, and copying process.
	 */
	public static function repeat_download() {
		$result = self::download_and_copy_files();
		if ( is_wp_error( $result ) ) {
			self::add_error_message( esc_html__( 'Error when manually triggering the process: ', 'sudoku120publisher' ) . esc_html( $result->get_error_message() ) );
		}
	}

	/**
	 * Adds an error message to the WordPress admin area.
	 *
	 * This function adds a custom error message to the settings errors of the WordPress admin area.
	 * The message is added using the `add_settings_error()` function, and the errors are stored in
	 * a transient for 30 seconds, allowing them to be displayed in the admin interface.
	 *
	 * @param string $message The error message to display in the admin area.
	 *
	 * @return void
	 */
	private static function add_error_message( $message ) {
		add_settings_error(
			'plugin_activation_errors',
			'plugin_activation_error',
			$message,
			'error'
		);
		set_transient( 'settings_errors', get_settings_errors(), 30 );
	}
}

// Plugin activation.
register_activation_hook( __FILE__, array( 'Sudoku120Publisher_Activate', 'activate' ) );
