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

	$remote_url = rtrim( $proxy->url, '/' ) . '/' . ltrim( $path, '/' );

	// Optional request headers
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
		// We intentionally forward the raw POST body without sanitization.
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

	// The raw response body from the remote server is stored in $body without sanitization or validation.
	// This is necessary to preserve the integrity of the response data, which may contain XML, JSON,
	// or binary content. The data is only forwarded as-is and is not further processed, modified,
	// or stored within WordPress. Any sanitization or modification would risk altering the response format
	// or corrupting the data. This ensures that the data remains intact for forwarding to the client.
	$body = wp_remote_retrieve_body( $response );

	header( "HTTP/1.1 $http_code" );
	if ( ! headers_sent() && is_array( $headers ) ) {
		foreach ( $headers as $name => $value ) {

			if ( 'set-cookie' === strtolower( $name ) ) {
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
	// Check if the request is for the proxy.
	if ( isset( $wp_query->query_vars['sudoku120publisher_proxy'] ) ) {
		$proxy_uuid = $wp_query->query_vars['proxy_uuid'];
		$path       = isset( $wp_query->query_vars['path'] ) ? $wp_query->query_vars['path'] : '';

		sudoku120publisher_reverse_proxy( $proxy_uuid, $path );

	}
}

add_action( 'template_redirect', 'sudoku120publisher_handle_proxy_request' );
