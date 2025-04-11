<?php
/**
 * Functions used across various other files in the plugin
 *
 * This file contains helper functions and utilities that are included and
 * used in different parts of the plugin. It ensures modular functionality
 * and separation of concerns, making it easier to maintain and extend the plugin.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Renders the Sudoku shortcode output.
 *
 * This function processes the Sudoku shortcode, fetches the corresponding Sudoku
 * from the database (with caching), validates that the shortcode is used on the
 * correct page, and modifies links within the Sudoku content. It also enqueues
 * a design CSS file if specified. The function ensures that only one Sudoku
 * shortcode is rendered per page and handles the 'config' status of the Sudoku.
 *
 * @param array $atts Shortcode attributes. The 'id' attribute is required, which
 *                    corresponds to the Sudoku ID in the database.
 *
 * @return string The HTML output of the Sudoku, including any error messages
 *                if the validation fails or the content is incorrect.
 */
function sudoku120publisher_render_shortcode( $atts ) {
	global $wpdb;

	// Extract attributes and ensure ID is provided.
	$atts = shortcode_atts( array( 'id' => null ), $atts, 'sudoku120' );
	if ( ! isset( $atts['id'] ) || ! is_numeric( $atts['id'] ) ) {
		return '<p>' . __( 'Error: Missing or invalid Sudoku ID.', 'sudoku120publisher' ) . '</p>';
	}

	$sudoku_id = (int) $atts['id'];
	// Check if the result is cached.
	$sudoku = wp_cache_get( $sudoku_id, 'sudoku' );

	if ( false === $sudoku ) {
		// If not cached, fetch from the database.
		$sudoku = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' WHERE id = %d', $sudoku_id ) );

		// Store in the cache for future use.
		wp_cache_set( $sudoku_id, $sudoku, 'sudoku', 3600 ); // Cache for 1 hour (3600 seconds).
	}   if ( ! $sudoku ) {
		return '<p>' . __( 'Error: Sudoku not found.', 'sudoku120publisher' ) . '</p>';
	}

	// Get current page URL.
	$current_url = get_permalink();

	// If status is "config", store the URL in the database.
	if ( 'config' === $sudoku->status ) {
		$wpdb->update( SUDOKU120PUBLISHER_TABLE_SUDOKU, array( 'sudokuurl' => $current_url ), array( 'id' => $sudoku_id ), array( '%s' ), array( '%d' ) );
	}

	// Validate if the shortcode is placed on the correct page.
	if ( 'config' !== $sudoku->status && $sudoku->sudokuurl !== $current_url ) {
		/*
		* Translators: %s is the url for the sudoku id from database.
		*/
		return '<p>' . sprintf( __( 'Error: This Sudoku can only be displayed on %s.', 'sudoku120publisher' ), esc_url( $sudoku->sudokuurl ) ) . '</p>';
	}

	// Ensure only one shortcode per page.
	static $shortcode_used = false;
	if ( $shortcode_used ) {
		return '<p>' . __( 'Error: Only one Sudoku shortcode is allowed per page.', 'sudoku120publisher' ) . '</p>';
	}
	$shortcode_used = true;

	// Retrieve user-defined attributes for the Sudoku div.
	$div_attributes = get_option( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR, '' );

	// Modify links inside the Sudoku content.
	$sudoku_content = $sudoku->sudoku_content;
	if ( ! empty( $sudoku_content ) ) {
		$rel_value    = get_option( SUDOKU120PUBLISHER_OPTION_LINK_REL, '' ) ? 'noopener noreferrer' : '';
		$target_value = get_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK, false ) ? ' target="_blank"' : '';

		$sudoku_content = preg_replace_callback(
			'/<a([^>]*)>/',
			function ( $matches ) use ( $rel_value, $target_value ) {
				$modified_link = '<a' . $matches[1];
				if ( strpos( $modified_link, ' rel=' ) === false ) {
					$modified_link .= ' rel="' . esc_attr( $rel_value ) . '"';
				}
				if ( strpos( $modified_link, ' target=' ) === false ) {
					$modified_link .= $target_value;
				}
				return $modified_link . '>';
			},
			$sudoku_content
		);
	}

	// Load design CSS file if specified.
	$design_css = get_option( SUDOKU120PUBLISHER_OPTION_DESIGN, '' );
	if ( ! empty( $design_css ) ) {
		// Get the upload directory paths.
		$upload_dir = wp_upload_dir();

		// Check if the design CSS file exists.
		$css_file_path = $upload_dir['basedir'] . '/sudoku120publisher/designs/' . esc_attr( $design_css );

		// Use filemtime() to get the last modified time of the CSS file to set a version.
		if ( file_exists( $css_file_path ) ) {
			// Get the last modified time of the file to use as the version number.
			$version = filemtime( $css_file_path );

			// Enqueue the design CSS file with versioning.
			wp_enqueue_style(
				'sudoku120publisher-design', // Handle for the style.
				esc_url( $upload_dir['baseurl'] . '/sudoku120publisher/designs/' . esc_attr( $design_css ) ), // Secure the URL.
				array(), // No dependencies.
				$version // Version based on file modification time.
			);
		}
	}

	// Return the final output.
	return '<div ' . $div_attributes . '>' . $sudoku_content . '</div>';
}
add_shortcode( 'sudoku120', 'sudoku120publisher_render_shortcode' );
