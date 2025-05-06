<?php
/**
 * Constants Definitions for the Sudoku120 Publisher Plugin.
 *
 * This file contains the definitions of important constants used throughout the
 * Sudoku120 Publisher plugin, particularly for database table names. It ensures
 * that these constants are only defined once and only when WordPress is fully loaded,
 * avoiding any potential issues with undefined variables or conflicts with other plugins.
 */

// Prevent direct access to this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define the plugin constants.

global $wpdb;


// paths.
define( 'SUDOKU120PUBLISHER_PATH', plugin_dir_path( __DIR__ ) );
define( 'SUDOKU120PUBLISHER_URL', plugin_dir_url( __DIR__ ) );

// BD table names.
define( 'SUDOKU120PUBLISHER_TABLE_SUDOKU', $wpdb->prefix . 'sudoku120publisher' );
define( 'SUDOKU120PUBLISHER_TABLE_PROXY', $wpdb->prefix . 'sudoku120publisher_proxy' );

// options.
define( 'SUDOKU120PUBLISHER_OPTION_DOMAIN', 'sudoku120publisher_domain' );
define( 'SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS', 'sudoku120publisher_domain_status' );
define( 'SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE', 'sudoku120publisher_proxy_active' );
define( 'SUDOKU120PUBLISHER_OPTION_DESIGN', 'sudoku120publisher_design' );
define( 'SUDOKU120PUBLISHER_OPTION_LINK_REL', 'sudoku120publisher_link_rel' );
define( 'SUDOKU120PUBLISHER_OPTION_LINK_BLANK', 'sudoku120publisher_link_blank' );
define( 'SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR', 'sudoku120publisher_sudoku_div_attr' );

// default values.
define( 'SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT', 'style="width: 95%; max-width: 500px; min-width: 300px; margin: 10px auto;"' );

// needed url to the file directory, neded to download files with the map.
define( 'SUDOKU120PUBLISHER_FILEDIR_URL', 'https://webmaster.sudoku120.com/sudoku-css-js/' );
// define( 'SUDOKU120PUBLISHER_API_URL', 'http://test.example.org/' ); // local test system.
define( 'SUDOKU120PUBLISHER_API_URL', 'http://sudokuapi.sudoku120.com/' ); // public api.
define( 'SUDOKU120PUBLISHER_SERVICE_URL', 'https://webmaster.sudoku120.com/' );
define( 'SUDOKU120PUBLISHER_SERVICE_NAME', 'Webmaster.Sudoku120.com' );
$servicelink = '<a href="' . SUDOKU120PUBLISHER_SERVICE_URL .
'" rel="noopener noreferrer" target="_blank">' . SUDOKU120PUBLISHER_SERVICE_NAME . '</a>';
define( 'SUDOKU120PUBLISHER_SERVICE_LINK', $servicelink );


// remote path for the files and local store position inside uploads folder.
define(
	'SUDOKU120PUBLISHER_FILE_MAP',
	array(
		'sudoku-assets/sudokuonline.css' => 'sudoku120publisher/assets/sudokuonline.css',
		'sudoku-assets/sudokuonline.js'  => 'sudoku120publisher/assets/sudokuonline.js',
		'blue.css'                       => 'sudoku120publisher/designs/blue.css',
		'blue-dark.css'                  => 'sudoku120publisher/designs/blue-dark.css',
		'blue-round-dark.css'            => 'sudoku120publisher/designs/blue-round-dark.css',
		'blue-round.css'                 => 'sudoku120publisher/designs/blue-round.css',
		'orange.css'                     => 'sudoku120publisher/designs/orange.css',
		'orange-dark.css'                => 'sudoku120publisher/designs/orange-dark.css',
		'orange-round-dark.css'          => 'sudoku120publisher/designs/orange-round-dark.css',
		'orange-round.css'               => 'sudoku120publisher/designs/orange-round.css',
	)
);

// slugs.
define( 'SUDOKU120PUBLISHER_PROXY_SLUG', 'sudoku120proxy' );

define(
	'SUDOKU120PUBLISHER_MIMETYPES',
	array(
		'json' => array(
			'mime'  => array( 'json', 'x-json', 'ld+json' ),
			'group' => 'json',
		),
		'xml'  => array(
			'mime'  => array( 'xml', 'rss+xml', 'atom+xml', 'xslt+xml' ),
			'group' => 'xml',
		),
		'txt'  => array(
			'mime'  => array( 'plain' ),
			'group' => 'txt',
		),
		'html' => array(
			'mime'  => array( 'html', 'xhtml+xml' ),
			'group' => 'utf8',
		),
		'js'   => array(
			'mime'  => array( 'javascript', 'x-javascript' ),
			'group' => 'utf8',
		),
		'css'  => array(
			'mime'  => array( 'css' ),
			'group' => 'utf8',
		),
		'csv'  => array(
			'mime'  => array( 'csv', 'vnd.ms-excel' ),
			'group' => 'utf8',
		),
		'yaml' => array(
			'mime'  => array( 'x-yaml', 'yaml' ),
			'group' => 'utf8',
		),
		'md'   => array(
			'mime'  => array( 'markdown' ),
			'group' => 'utf8',
		),
		'php'  => array(
			'mime'  => array( 'x-httpd-php' ),
			'group' => 'utf8',
		),
		'mp3'  => array(
			'mime'  => array( 'mpeg' ),
			'group' => 'media',
		),
		'wav'  => array(
			'mime'  => array( 'wav', 'x-wav' ),
			'group' => 'media',
		),
		'ogg'  => array(
			'mime'  => array( 'ogg', 'x-ogg' ),
			'group' => 'media',
		),
		'flac' => array(
			'mime'  => array( 'flac' ),
			'group' => 'media',
		),
		'm4a'  => array(
			'mime'  => array( 'mp4', 'x-m4a' ),
			'group' => 'media',
		),
		'aac'  => array(
			'mime'  => array( 'aac' ),
			'group' => 'media',
		),
		'mp4'  => array(
			'mime'  => array( 'mp4' ),
			'group' => 'media',
		),
		'webm' => array(
			'mime'  => array( 'webm' ),
			'group' => 'media',
		),
		'mov'  => array(
			'mime'  => array( 'quicktime' ),
			'group' => 'media',
		),
		'avi'  => array(
			'mime'  => array( 'x-msvideo' ),
			'group' => 'media',
		),
		'mkv'  => array(
			'mime'  => array( 'x-matroska' ),
			'group' => 'media',
		),
		'3gp'  => array(
			'mime'  => array( '3gpp' ),
			'group' => 'media',
		),
		'flv'  => array(
			'mime'  => array( 'x-flv' ),
			'group' => 'media',
		),
		'mpeg' => array(
			'mime'  => array( 'mpeg' ),
			'group' => 'media',
		),
		'm4v'  => array(
			'mime'  => array( 'x-m4v' ),
			'group' => 'media',
		),
		'png'  => array(
			'mime'  => array( 'png' ),
			'group' => 'media',
		),
		'jpg'  => array(
			'mime'  => array( 'jpeg', 'pjpeg' ),
			'group' => 'media',
		),
		'gif'  => array(
			'mime'  => array( 'gif' ),
			'group' => 'media',
		),
		'webp' => array(
			'mime'  => array( 'webp' ),
			'group' => 'media',
		),
		'svg'  => array(
			'mime'  => array( 'svg+xml' ),
			'group' => 'media',
		),
		'bmp'  => array(
			'mime'  => array( 'bmp' ),
			'group' => 'media',
		),
		'avif' => array(
			'mime'  => array( 'avif' ),
			'group' => 'media',
		),
		'apng' => array(
			'mime'  => array( 'apng' ),
			'group' => 'media',
		),
		'tiff' => array(
			'mime'  => array( 'tiff', 'x-tiff' ),
			'group' => 'media',
		),
		'ico'  => array(
			'mime'  => array( 'vnd.microsoft.icon', 'x-icon' ),
			'group' => 'media',
		),
	)
);
