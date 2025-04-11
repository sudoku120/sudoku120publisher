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

	// Proxy aktive?
	$proxy_active = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, false );
	if ( ! $proxy_active ) {
		wp_die( esc_html__( 'Proxy is disabled.', 'sudoku120publisher' ), 403 );
	}

	// get Proxy-UUID from db.
	$table_name = SUDOKU120PUBLISHER_TABLE_PROXY;
	$cache_key  = 'proxy_' . $proxy_uuid;  // Cache key based on the proxy UUID.

	$proxy = wp_cache_get( $cache_key, 'sudoku120publisher' ); // Try to get the data from the cache.

	if ( false === $proxy ) {
		// If the data is not in the cache, query it from the database.$query = "SELECT * FROM {$table_name} WHERE proxy_uuid = %s".
		$proxy = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE proxy_uuid = %s', $proxy_uuid ) );

		if ( $proxy ) {
			wp_cache_set( $cache_key, $proxy, 'sudoku120publisher', HOUR_IN_SECONDS ); // Store the data in the cache.
		}
	}

	if ( ! $proxy ) {
		wp_die( esc_html__( 'Invalid proxy UUID.', 'sudoku120publisher' ), 404 );
	}

	// build Remoteurl.
	if ( trim( $path ) === '' ) {
		$remote_url = rtrim( $proxy->url, '/' ) . '/';
	} else {
		$remote_url = rtrim( $proxy->url, '/' ) . '/' . ltrim( $path, '/' );
	}
	// prepare Header.
	$headers = array();

	if ( $proxy->user_agent ) {
		$user_agent            = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$headers['User-Agent'] = sanitize_text_field( $user_agent );
	}

	if ( $proxy->referrer ) {
		$referrer           = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
		$headers['Referer'] = esc_url_raw( $referrer );
	}

	if ( $proxy->client_ip ) {
		$remote_addr                = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$headers['X-Forwarded-For'] = sanitize_text_field( $remote_addr );
	}

	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : 'GET';

	// Using cURL directly for precise control over headers, timeouts, and proxy behavior.
	// wp_remote_get() does not offer the required level of control for this request.
	if ( function_exists( 'curl_init' ) ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $remote_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
		curl_setopt(
			$ch,
			CURLOPT_HTTPHEADER,
			array_map(
				function ( $key, $value ) {
					return "$key: $value";
				},
				array_keys( $headers ),
				$headers
			)
		);

		if ( 'POST' === $method ) {
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, file_get_contents( 'php://input' ) );
		}

		$response     = curl_exec( $ch );
		$http_code    = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$content_type = curl_getinfo( $ch, CURLINFO_CONTENT_TYPE );
		curl_close( $ch );
	} else {
		// Fallback to wp_remote_get / wp_remote_post.
		$args = array(
			'headers' => $headers,
			'body'    => ( 'POST' === $method ) ? file_get_contents( 'php://input' ) : null,
		);

		$response = ( 'POST' === $method )
			? wp_remote_post( $remote_url, $args )
			: wp_remote_get( $remote_url, $args );

		if ( is_wp_error( $response ) ) {
			wp_die( esc_html__( 'Failed to fetch remote content.', 'sudoku120publisher' ), 502 );
		}

		$http_code    = wp_remote_retrieve_response_code( $response );
		$content_type = wp_remote_retrieve_header( $response, 'content-type' );
		$response     = wp_remote_retrieve_body( $response );
	}

	// send Headers to client.
	header( "HTTP/1.1 $http_code" );
	header( "Content-Type: $content_type" );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $response;
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
