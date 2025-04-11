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
 * Insert a new proxy URL into the database.
 *
 * @param string   $url The proxy URL.
 * @param int|null $sudoku_id The associated Sudoku ID (if any).
 * @param bool     $client_ip Whether to pass the client IP.
 * @param bool     $user_agent Whether to pass the user agent.
 * @param bool     $referrer Whether to pass the referrer.
 * @return int|false The inserted row ID or false on failure.
 */
function sudoku120publisher_insert_proxy_url( $url, $sudoku_id = null, $client_ip = false, $user_agent = false, $referrer = true ) {
	global $wpdb;

	// Convert international domain names (IDN) to ASCII if applicable.
	$url = sudoku120publisher_idn_to_ascii_url( $url );

	// Validate the URL after conversion to ASCII.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		// Show an error message.
		add_settings_error(
			'sudoku120publisher_error',  // Setting name (for the error).
			'invalid_url',               // Error code.
			esc_html__( 'Please enter a valid URL.', 'sudoku120publisher' ), // Error message.
			'error'                      // Type of message (error).
		);
		return false;  // Invalid URL.
	}

	// Generate a unique UUID and remove the dashes.
	$proxy_uuid = str_replace( '-', '', wp_generate_uuid4() );

	// Insert using $wpdb->insert() with prepared statements to prevent SQL injection.
	// We validate the $url as url in the forms, so no risks are involved.
	$result = $wpdb->insert(
		SUDOKU120PUBLISHER_TABLE_PROXY,
		array(
			'url'        => $url,
			'proxy_uuid' => $proxy_uuid,
			'sudoku_id'  => $sudoku_id,
			'client_ip'  => $client_ip,
			'user_agent' => $user_agent,
			'referrer'   => $referrer,
		),
		array( '%s', '%s', '%d', '%d', '%d', '%d' )  // Specify data types for each column.
	);

	return $result ? $wpdb->insert_id : false;
}


/**
 * Convert IDN domain in an URL to an ASCII domain.
 *
 * @param string $url The URL to convert.
 * @return string The ASCII URL, or the original URL if the conversion fails.
 */
function sudoku120publisher_idn_to_ascii_url( $url ) {
	// Check if idn_to_ascii function is available.
	if ( ! function_exists( 'idn_to_ascii' ) ) {
		return $url; // Return the original URL if the function is not available.
	}

	$tempurl = idn_to_ascii( $url );

	if ( ! filter_var( $tempurl, FILTER_VALIDATE_URL ) ) {
		return $url; // Return the original URL if the URL is invalid.
	}

	// Parse the URL into its components.
	$parts = wp_parse_url( $url );

	if ( ! isset( $parts['host'] ) ) {
		return $url; // Return the original URL if no host is found.
	}

	// Convert only the host to ASCII.
	$converted_host = idn_to_ascii( $parts['host'] );

	if ( false === $converted_host ) {
		return $url; // Return the original URL if the conversion fails.
	}

	// Replace the host in the URL components.
	$parts['host'] = $converted_host;

	// Rebuild the URL.
	$final_url = ( isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '' ) .
				( isset( $parts['user'] ) ? $parts['user'] . ( isset( $parts['pass'] ) ? ':' . $parts['pass'] : '' ) . '@' : '' ) .
				$parts['host'] .
				( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) .
				( isset( $parts['path'] ) ? $parts['path'] : '' ) .
				( isset( $parts['query'] ) ? '?' . $parts['query'] : '' ) .
				( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' );

	return $final_url;
}


/**
 * Convert IDN domain in a URL to a UTF-8 domain.
 *
 * @param string $url The URL to convert.
 * @return string The UTF-8 URL, or the original URL if the conversion fails.
 */
function sudoku120publisher_idn_to_utf8_url( $url ) {
	// Check if idn_to_utf8 function is available.
	if ( ! function_exists( 'idn_to_utf8' ) ) {
		return $url; // Return the original URL if the function is not available.
	}

	// Validate the URL.
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return $url; // Return the original URL if the URL is invalid.
	}

	// Parse the URL into its components.
	$parts = wp_parse_url( $url );

	if ( ! isset( $parts['host'] ) ) {
		return $url; // Return the original URL if no host is found.
	}

	// Convert only the host to UTF-8.
	$converted_host = idn_to_utf8( $parts['host'] );

	if ( false === $converted_host ) {
		return $url; // Return the original URL if the conversion fails.
	}

	// Replace the host in the URL components.
	$parts['host'] = $converted_host;

	// Rebuild the URL.
	$final_url = ( isset( $parts['scheme'] ) ? $parts['scheme'] . '://' : '' ) .
				( isset( $parts['user'] ) ? $parts['user'] . ( isset( $parts['pass'] ) ? ':' . $parts['pass'] : '' ) . '@' : '' ) .
				$parts['host'] .
				( isset( $parts['port'] ) ? ':' . $parts['port'] : '' ) .
				( isset( $parts['path'] ) ? $parts['path'] : '' ) .
				( isset( $parts['query'] ) ? '?' . $parts['query'] : '' ) .
				( isset( $parts['fragment'] ) ? '#' . $parts['fragment'] : '' );

	return $final_url;
}


/**
 * Fix the 'rel' attribute of links within a specific div by removing unwanted values.
 *
 * @param string $content The content to fix.
 * @return string The content with corrected 'rel' attributes for links.
 */
function sudoku120publisher_fix_link_rel( $content ) {
	// Check if the content contains the specific div with id="sdkushadowdom".
	if ( strpos( $content, 'id="sdkushadowdom"' ) === false ) {
		return $content; // Return the content if the div is not found.
	}

	// Regex to capture the entire content of the div with id="sdkushadowdom" (including nested divs).
	$pattern = '/<div id="sdkushadowdom"[^>]*>.*<\/div>/is';
	if ( preg_match( $pattern, $content, $matches ) ) {
		$sdkushadowdom_html = $matches[0]; // Entire <div id="sdkushadowdom">.

		// Find the first <div> with links.
		$pattern_div_with_links = '/<div[^>]*>\s*<a\s+[^>]*>.*?<\/a>.*?<\/div>/is';
		if ( preg_match( $pattern_div_with_links, $sdkushadowdom_html, $div_match ) ) {
			$div_with_links = $div_match[0];

			// Fix the links within this div.
			$fixed_div_with_links = preg_replace_callback(
				'/<a\s+([^>]*?)rel="([^"]*?)"([^>]*?)>/i',
				function ( $link_match ) {
					$attributes_before = $link_match[1];
					$current_rel       = strtolower( $link_match[2] );
					$attributes_after  = $link_match[3];

					// Remove unwanted values from the 'rel' attribute.
					$rel_array = explode( ' ', $current_rel );
					$rel_array = array_diff( $rel_array, array( 'nofollow', 'ugc', 'sponsored' ) );

					return '<a ' . $attributes_before . 'rel="' . implode( ' ', $rel_array ) . '"' . $attributes_after . '>';
				},
				$div_with_links
			);

			// Replace the original div with the fixed version.
			$content = str_replace( $div_with_links, $fixed_div_with_links, $content );
		}
	}

	return $content;
}
