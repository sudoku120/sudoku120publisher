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
 * @param string      $url The proxy URL.
 * @param int|null    $sudoku_id The associated Sudoku ID (if any).
 * @param bool        $client_ip Whether to pass the client IP.
 * @param bool        $user_agent Whether to pass the user agent.
 * @param bool        $referrer Whether to pass the referrer.
 * @param string|null $mimetype_groups_json JSON-encoded list of allowed mimetype groups, or null for no filtering.
 * @return int|false The inserted row ID or false on failure.
 */
function sudoku120publisher_insert_proxy_url( $url, $sudoku_id = null, $client_ip = false, $user_agent = false, $referrer = true, $mimetype_groups_json = null ) {
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
			'mimetypes'  => $mimetype_groups_json,
		),
		array( '%s', '%s', '%d', '%d', '%d', '%d', '%s' )  // Specify data types for each column.
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


/**
 * Creates the database tables needed for the plugin to function.
 */
function sudoku120publisher_create_tables() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	// Create Sudoku table.
	$create_sudoku_table = '
			CREATE TABLE ' . SUDOKU120PUBLISHER_TABLE_SUDOKU . " (
					id INT(11) NOT NULL AUTO_INCREMENT,
					name VARCHAR(255) DEFAULT NULL,
					lang VARCHAR(10) DEFAULT NULL,
					api_key VARCHAR(255) DEFAULT NULL,
					timezone VARCHAR(100) DEFAULT NULL,
					sudokuurl TEXT DEFAULT NULL,
					status ENUM('active', 'inactive', 'config', 'problem') DEFAULT NULL,
					apiurl TEXT DEFAULT NULL,
					sudoku_content TEXT DEFAULT NULL,
					PRIMARY KEY (id)
			) $charset_collate;
			";

	// Create Proxy table.
	$create_proxy_table = '
			CREATE TABLE ' . SUDOKU120PUBLISHER_TABLE_PROXY . " (
					id INT(11) NOT NULL AUTO_INCREMENT,
					url TEXT NOT NULL,
					proxy_uuid VARCHAR(36) NOT NULL,
					sudoku_id INT(11) DEFAULT NULL,
					client_ip BOOLEAN DEFAULT FALSE,
					user_agent BOOLEAN DEFAULT FALSE,
					referrer BOOLEAN DEFAULT TRUE,
					mimetypes JSON DEFAULT NULL,
					PRIMARY KEY (id),
					UNIQUE KEY proxy_uuid_unique (proxy_uuid)
			) $charset_collate;
			";

	// Include the upgrade functions for dbDelta.
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Call dbDelta() to create or update tables.
	dbDelta( $create_sudoku_table );
	dbDelta( $create_proxy_table );
}

/**
 * Updates the Sudoku entry in the database with data fetched from the remote API.
 *
 * This function:
 * - Retrieves the existing Sudoku entry and its API URL from the database.
 * - Sends a request to the API to fetch updated data for the Sudoku.
 * - Decodes the API response and updates the database entry with relevant data, such as name, language, timezone, and status.
 * - If the `sudoku_content` is missing, it triggers the fetching of the Sudoku HTML from the API.
 * - Updates the domain and domain status options based on the API response.
 *
 * @param int $sudoku_id The ID of the Sudoku entry to update.
 *
 * @return bool|WP_Error True on success, or a WP_Error object on failure.
 */
function sudoku120publisher_update_sudoku_from_api( $sudoku_id ) {
	global $wpdb;

	$sudoku = $wpdb->get_row( $wpdb->prepare( 'SELECT apiurl, sudoku_content FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' WHERE id = %d', $sudoku_id ) );

	if ( ! $sudoku || empty( $sudoku->apiurl ) ) {
		return new WP_Error( 'missing_apiurl', __( 'API URL is missing or invalid.', 'sudoku120publisher' ) );
	}

	$response = wp_remote_get( $sudoku->apiurl );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body, true );

	if ( json_last_error() !== JSON_ERROR_NONE ) {
		return new WP_Error( 'json_decode_error', __( 'Failed to parse API response.', 'sudoku120publisher' ) );
	}

	// Prepare update data.
	$update_data = array(
		'name'     => isset( $data['name'] ) ? sanitize_text_field( $data['name'] ) : null,
		'lang'     => isset( $data['lang'] ) ? sanitize_text_field( $data['lang'] ) : null,
		'timezone' => isset( $data['timezone'] ) ? sanitize_text_field( $data['timezone'] ) : null,
		'status'   => isset( $data['status'] ) ? sanitize_text_field( $data['status'] ) : null,

	);

	// Only update if the values are not empty.
	if ( ! empty( $data['sudokuurl'] ) && wp_http_validate_url( $data['sudokuurl'] ) ) {
		$update_data['sudokuurl'] = esc_url_raw( $data['sudokuurl'] );
	}
	if ( ! empty( $data['apiurl'] ) && wp_http_validate_url( $data['apiurl'] ) ) {
		$update_data['apiurl'] = esc_url_raw( $data['apiurl'] );
	}

	// Update database entries.
	$wpdb->update(
		SUDOKU120PUBLISHER_TABLE_SUDOKU,
		$update_data,
		array( 'id' => $sudoku_id ),
		array_fill( 0, count( $update_data ), '%s' ),
		array( '%d' )
	);

	// Get Sudoku HTML when available and still empty in db.
	if ( ! empty( $data['apiurl'] ) && empty( $sudoku->sudoku_content ) ) {
		sudoku120publisher_fetch_sudoku_html( $sudoku_id );
	}

	// Update global domain options.
	if ( isset( $data['domain'] ) ) {
		update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN, sanitize_text_field( $data['domain'] ) );
	}
	if ( isset( $data['domainstatus'] ) ) {
		update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS, sanitize_text_field( $data['domainstatus'] ) );
	}
	// Check for a global service message in multiple languages.
	if ( isset( $data['message'] ) && is_array( $data['message'] ) ) {
		$new_message_json = wp_json_encode( $data['message'] );

		$current_message_json = get_option( 'sudoku120publisher_admin_sudoku_message', '' );

		if ( $new_message_json && $new_message_json !== $current_message_json ) {
			update_option( 'sudoku120publisher_admin_sudoku_message', $new_message_json );
			update_option( 'sudoku120publisher_admin_alert_message', 1 );
		} elseif ( empty( $data['message'] ) ) {
			update_option( 'sudoku120publisher_admin_sudoku_message', '' );
			update_option( 'sudoku120publisher_admin_alert_message', 0 );
		}
	} elseif ( empty( $data['message'] ) ) {
		update_option( 'sudoku120publisher_admin_sudoku_message', '' );
		update_option( 'sudoku120publisher_admin_alert_message', 0 );
	}

	return true;
}

/**
 * Updates the admin alert option with the number of Sudokus in 'problem' status.
 *
 * This function counts all entries in the Sudoku table with status 'problem'
 * and stores the result in the option 'sudoku120publisher_admin_alert_sudoku'.
 *
 * Called after automatic or manual status updates to reflect current state.
 */
function sudoku120publisher_update_problem_counter() {
	global $wpdb;

	// Count all Sudokus with status 'problem'.
	$problem_count = (int) $wpdb->get_var(
		$wpdb->prepare(
			'SELECT COUNT(*) FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' WHERE status = %s',
			'problem'
		)
	);

	// Store the count in the admin alert option.
	update_option( 'sudoku120publisher_admin_alert_sudoku', $problem_count );
}


/**
 * Cron job: Updates the status of all relevant Sudokus and refreshes the alert counter.
 *
 * This function loads all Sudokus with status 'config', 'active', or 'problem',
 * updates their data via API, and updates the problem counter option.
 *
 * Intended to be called once daily via WP-Cron.
 */
function sudoku120publisher_cron_check_sudokus() {
	global $wpdb;

	$sudoku_ids = $wpdb->get_col( 'SELECT id FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . " WHERE status IN ('config', 'active', 'problem')" );

	// Update each Sudoku via API.
	foreach ( $sudoku_ids as $sudoku_id ) {
		sudoku120publisher_update_sudoku_from_api( (int) $sudoku_id );
	}

	// Update problem counter after all updates are complete.
	sudoku120publisher_update_problem_counter();
}
