<?php
/**
 * Handles the uninstallation process of the Sudoku120 Publisher plugin.
 *
 * This file is automatically called when the plugin is uninstalled.
 * It is responsible for removing all plugin-related data, including database tables
 * and uploaded files, ensuring a clean removal of all stored information.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; // Exit if accessed directly.
}

// Load the constants for the tables.
require_once plugin_dir_path( __FILE__ ) . 'inc/const-defines.php'; // Or the correct path.

global $wpdb;


		// Escaping table names to prevent SQL injection.
		$table_sudoku = esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU );
		$table_proxy  = esc_sql( SUDOKU120PUBLISHER_TABLE_PROXY );

		// SQL queries to drop the tables.
		$sql_sudoku = "DROP TABLE IF EXISTS `$table_sudoku`;";
		$sql_proxy  = "DROP TABLE IF EXISTS `$table_proxy`;";

		// Execute the SQL queries.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql_sudoku );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
		$wpdb->query( $sql_proxy );


// Remove all plugin options.
delete_option( SUDOKU120PUBLISHER_OPTION_DOMAIN );
delete_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS );
delete_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE );
delete_option( SUDOKU120PUBLISHER_OPTION_DESIGN );
delete_option( SUDOKU120PUBLISHER_OPTION_LINK_REL );
delete_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK );
delete_option( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR );

// Remove extra files and directories from the uploads directory.
$upload_dir = wp_upload_dir();
$target_dir = $upload_dir['basedir'] . '/sudoku120publisher/';

// Delete the directory and its contents.
if ( is_dir( $target_dir ) ) {
	sudoku120publisher_delete_directory_with_glob( $target_dir );
}

/**
 * Removes extra files and folders from uploads.
 *
 * @param string $dir The directory path to delete.
 */
function sudoku120publisher_delete_directory_with_glob( $dir ) {
	global $wp_filesystem;

	// Initialize WP_Filesystem if not already done.
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		WP_Filesystem();
	}

	// If the directory doesn't exist, do nothing.
	if ( ! is_dir( $dir ) ) {
		return;
	}

	// Recursively search all files and subdirectories in the directory.
	$files = glob( $dir . '/*' );

	// Loop through all files and subdirectories.
	foreach ( $files as $file ) {
		// If it's a directory, delete it recursively.
		if ( is_dir( $file ) ) {
			delete_directory_with_glob( $file );
		} else {
			// If it's a file, delete it using WordPress function.
			wp_delete_file( $file );
		}
	}

	// Remove the empty directory with WP_Filesystem.
	$wp_filesystem->rmdir( $dir );
}
