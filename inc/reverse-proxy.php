<?php
/**
 * Sudoku120 Publisher Plugin - Reverse Proxy Handler
 *
 * This file contains the logic for handling reverse proxy requests within the Sudoku120 Publisher plugin.
 * It ensures that requests to the proxy are properly routed, with error handling, data fetching, and caching.
 * The reverse proxy functionality forwards HTTP requests to an external server, retrieves the response, and
 * sends it back to the client. It supports both cURL and wp_remote_get for making requests.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Checks if a string is valid UTF-8 and does not contain disallowed ASCII control characters.
 *
 * Allowed control characters: horizontal tab (0x09), line feed (0x0A), carriage return (0x0D).
 * Disallowed: 0x00–0x08, 0x0B, 0x0C, 0x0E–0x1F, and delete (0x7F).
 *
 * @param string $input The input string to validate.
 * @return bool True if valid UTF-8 and no disallowed control characters, false otherwise.
 */
function sudoku120publisher_is_valid_utf8_text( $input ) {
	// Check for valid UTF-8 encoding.
	if ( ! preg_match( '//u', $input ) ) {
		return false;
	}

	// Check for disallowed ASCII control characters.
	if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $input ) ) {
		return false;
	}

	return true;
}

/**
 * Validates whether a string is well-formed JSON and UTF-8 encoded without disallowed control characters.
 *
 * @param string $input The input string to validate.
 * @return bool True if valid JSON and passes UTF-8 text check, false otherwise.
 */
function sudoku120publisher_is_valid_json( $input ) {
	if ( ! sudoku120publisher_is_valid_utf8_text( $input ) ) {
		return false;
	}

	json_decode( $input );
	return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Validates whether a string is well-formed XML and UTF-8 encoded without disallowed control characters.
 *
 * @param string $input The input string to validate.
 * @return bool True if valid XML and passes UTF-8 text check, false otherwise.
 */
function sudoku120publisher_is_valid_xml( $input ) {
	if ( ! sudoku120publisher_is_valid_utf8_text( $input ) ) {
		return false;
	}

	libxml_use_internal_errors( true );
	$doc = simplexml_load_string( $input );
	return ( false !== $doc );
}


/**
 * Handle reverse proxy requests for Sudoku
 *
 * This function manages the reverse proxy logic for the Sudoku plugin.
 * It checks if the proxy is active, retrieves proxy details from the database,
 * and then forwards the request to the proxy server using either cURL or wp_remote_get.
 * The function also handles request headers and responses, ensuring proper error handling and caching.
 *
 * @param string $proxy_uuid The unique identifier for the proxy.
 * @param string $path The requested path on the proxy server.
 */
function sudoku120publisher_reverse_proxy( $proxy_uuid, $path ) {
	global $wpdb;
	$proxy_active = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, false );
	if ( ! $proxy_active ) {
		wp_die( esc_html__( 'Proxy is disabled.', 'sudoku120publisher' ), 403 );
	}

	$table_name = SUDOKU120PUBLISHER_TABLE_PROXY;
	$cache_key  = 'proxy_' . $proxy_uuid;

	$proxy = wp_cache_get( $cache_key, 'sudoku120publisher' );
	if ( false === $proxy ) {
		$proxy = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE proxy_uuid = %s', $proxy_uuid ) );
		if ( $proxy ) {
			wp_cache_set( $cache_key, $proxy, 'sudoku120publisher', HOUR_IN_SECONDS );
		}
	}

	if ( ! $proxy ) {
		wp_die( esc_html__( 'Invalid proxy UUID.', 'sudoku120publisher' ), 404 );
	}
	$allowed_groups = $proxy->mimetypes ? json_decode( $proxy->mimetypes, true ) : array();

	$remote_url = rtrim( $proxy->url, '/' ) . '/' . ltrim( $path, '/' );

	// Optional request headers.
	$headers = array();

	if ( $proxy->user_agent ) {
		$headers['User-Agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
	}

	if ( $proxy->referrer ) {
		$headers['Referer'] = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	} else {
		$headers['Referer'] = '';
	}

	if ( $proxy->client_ip ) {
		$headers['X-Forwarded-For'] = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	}

	$allowed = array( 'Accept', 'Accept-Language', 'X-Requested-With', 'Origin', 'Content-Type', 'Cache-Control' );

	foreach ( $allowed as $key ) {
		$server_key = 'HTTP_' . strtoupper( str_replace( '-', '_', $key ) );
		if ( isset( $_SERVER[ $server_key ] ) ) {
			$headers[ $key ] = sanitize_text_field( wp_unslash( $_SERVER[ $server_key ] ) );
		}
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';

	$args = array(
		'headers'     => $headers,
		'timeout'     => 15,
		'redirection' => 5,
		// We intentionally forward the raw POST body without sanitization when no filters are set.
		// This is required to preserve XML, JSON, or binary data integrity during proxying.
		// No user input is executed or stored, and the response is sent directly to the remote server.
		// No nonce check is used here, as this is a public endpoint not tied to a user session.
			'body'    => ( 'POST' === $method ) ? file_get_contents( 'php://input' ) : null,
	);

	$response = ( 'POST' === $method )
		? wp_remote_post( $remote_url, $args )
		: wp_remote_get( $remote_url, $args );

	if ( is_wp_error( $response ) ) {
		wp_die( esc_html__( 'Failed to fetch remote content.', 'sudoku120publisher' ), 502 );
	}

	$http_code = wp_remote_retrieve_response_code( $response );
	$headers   = wp_remote_retrieve_headers( $response );
	if ( $headers instanceof \WpOrg\Requests\Utility\CaseInsensitiveDictionary ) {
		$headers = $headers->getAll();
	}
	$body = wp_remote_retrieve_body( $response );

	$protocol        = isset( $_SERVER['SERVER_PROTOCOL'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_PROTOCOL'] ) ) : 'HTTP/1.0';
	$valid_protocols = array( 'HTTP/1.0', 'HTTP/1.1', 'HTTP/2.0', 'HTTP/3' );
	if ( ! in_array( $protocol, $valid_protocols, true ) ) {
		$protocol = 'HTTP/1.1';
	}

	// Filter content based on allowed mimetype groups (e.g. JSON, XML, text).
	// Blocks response early with 406 if the content-type is invalid, malformed, or not permitted.

	if ( ! empty( $allowed_groups ) ) {

		if ( 200 === $http_code ) {
			$content_type = '';
			if ( isset( $response['headers']['content-type'] ) ) {
				$content_type = $response['headers']['content-type'];
			} elseif ( isset( $response['headers']['Content-Type'] ) ) {
				$content_type = $response['headers']['Content-Type'];
			}

			$mime_parts   = explode( '/', explode( ';', $content_type )[0] );
			$mime_subtype = isset( $mime_parts[1] ) ? strtolower( trim( $mime_parts[1] ) ) : '';

			$group = null;
			foreach ( SUDOKU120PUBLISHER_MIMETYPES as $g => $data ) {
				if ( in_array( $mime_subtype, $data['mime'], true ) ) {
					$group = $data['group'];
					break;
				}
			}

			switch ( $group ) {
				case 'json':
					if ( ! sudoku120publisher_is_valid_json( $body ) ) {
						status_header( 406 );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo 'Blocked: Invalid JSON or encoding.';
						exit;
					}
					break;

				case 'xml':
					if ( ! sudoku120publisher_is_valid_xml( $body ) ) {
						status_header( 406 );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo 'Blocked: Invalid XML or encoding.';
						exit;
					}
					break;

				case 'txt':
				case 'utf8':
					if ( ! sudoku120publisher_is_valid_utf8_text( $body ) ) {
						status_header( 406 );
						header( 'Content-Type: text/plain; charset=UTF-8' );
						echo 'Blocked: Invalid UTF-8.';
						exit;
					}

					break;

				default:
					break;
			}

			if ( ! $group || ! in_array( $group, $allowed_groups, true ) ) {
				status_header( 406 );
				header( 'Content-Type: text/plain; charset=UTF-8' );
				echo 'Blocked by Sudoku120Publisher Proxy: Mimetype group "' . esc_html( $group ?? $mime_subtype ) . '" is not allowed.';
				exit;
			}
		} elseif ( ! in_array( $http_code, array( 204, 304, 301, 302, 307, 308, 202 ), true ) ) {
				$body  = wp_strip_all_tags( $body );
				$lines = explode( "\n", $body );
				$lines = array_map( 'trim', $lines );
				$lines = array_filter(
					$lines,
					static function ( $line ) {
						return '' !== $line;
					}
				);
				unset( $headers['content-type'] );
				$headers['Content-Type'] = 'text/plain; charset=UTF-8';
				$body                    = esc_textarea( "*\n* HTTP RESPONSE CODE " . $http_code . "\n*\n\n" . implode( "\n", $lines ) );

		}
	}

	// The raw response body from the remote server is stored in $body without sanitization or validation.
	// This is necessary to preserve the integrity of the response data, which may contain XML, JSON,
	// or binary content. The data is only forwarded as-is and is not further processed, modified,
	// or stored within WordPress. Any sanitization or modification would risk altering the response format
	// or corrupting the data. This ensures that the data remains intact for forwarding to the client.

	$headers_white_list = array(
		'content-type',
		'cache-control',
		'etag',
		'location',
		'x-request-id',
		'x-frame-options',
		'accept-ranges',
		'content-disposition',
		'vary',
	);
	if ( ! headers_sent() && is_array( $headers ) ) {
		header( 'X-Content-Type-Options: nosniff', true );
		header( "$protocol $http_code" );
		foreach ( $headers as $name => $value ) {

			if ( ! in_array( strtolower( $name ), $headers_white_list, true ) ) {
				continue;
			}

			$name = ucwords( str_replace( '-', ' ', $name ) );
			$name = str_replace( ' ', '-', $name );

			if ( is_array( $value ) ) {
				foreach ( $value as $v ) {
					header( "$name: $v", false );
				}
			} else {
				header( "$name: $value", true );
			}
		}
		header( 'X-Robots-Tag: noindex, nofollow', true );
	}

	// We intentionally output the raw response body without escaping,
	// as this is a proxy response (e.g., XML, JSON, or HTML) from the remote server
	// that must be delivered unaltered to the client.
 	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $body;
	exit;
}




/**
 * Handle incoming proxy requests
 *
 * This function hooks into WordPress's template_redirect action to intercept requests for the proxy.
 * It checks if the request includes a proxy UUID and optional path, then calls the reverse proxy function.
 */
function sudoku120publisher_handle_proxy_request() {
	global $wp_query;

	if ( isset( $wp_query->query_vars['sudoku120publisher_proxy'] ) ) {
		$proxy_uuid = sanitize_text_field( $wp_query->query_vars['proxy_uuid'] );
		$path       = isset( $wp_query->query_vars['path'] ) ? sanitize_text_field( $wp_query->query_vars['path'] ) : '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri  = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
			$parsed_url   = wp_parse_url( $request_uri );
			$query_string = isset( $parsed_url['query'] ) ? sanitize_text_field( $parsed_url['query'] ) : '';

			if ( ! empty( $query_string ) ) {
				$path .= ( strpos( $path, '?' ) === false ? '?' : '&' ) . $query_string;
			}
		}

		if ( strpos( $path, '..' ) !== false ) {

			status_header( 400 );
			header( 'Content-Type: text/plain; charset=UTF-8' );
			echo 'Invalid path: .. found';
			exit;

		}

		sudoku120publisher_reverse_proxy( $proxy_uuid, $path );
	}
}


add_action( 'template_redirect', 'sudoku120publisher_handle_proxy_request' );
