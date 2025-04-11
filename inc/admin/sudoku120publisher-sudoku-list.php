<?php
/**
 * Sudoku120 Publisher Plugin - Functions for interacting with Sudoku API and managing database entries.
 *
 * This file contains functions responsible for:
 * - Creating and updating Sudoku entries in the database.
 * - Fetching and processing Sudoku HTML content from the remote API.
 * - Handling proxy URLs and managing related data in the database.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the page for managing configured Sudokus.
 */
function sudoku120publisher_sudoku_list_page() {
	global $wpdb;

	// Load options.
	$domain        = get_option( SUDOKU120PUBLISHER_OPTION_DOMAIN, '' );
	$domain_status = get_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS, '' );
	$proxy_active  = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, '0' );

	// Form processing.
	if ( isset( $_POST['sudoku120publisher_create_sudoku'] ) && check_admin_referer( 'sudoku120publisher_add_sudoku', 'sudoku120publisher_nonce' ) ) {
		if ( ! empty( $_POST['api_key'] ) ) {
			$api_key = sanitize_text_field( wp_unslash( $_POST['api_key'] ) );
		} else {
			$api_key = null;
		}
		if ( ! empty( $_POST['proxy_url'] ) ) {
			$proxy_url = sanitize_text_field( wp_unslash( $_POST['proxy_url'] ) );
		} else {
			$proxy_url = '';
		}

		if ( ! empty( $api_key ) && ! preg_match( '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $api_key ) ) {
						echo '<div class="error"><p>' . esc_html__( 'Please enter a valid API Key in UUID format (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx).', 'sudoku120publisher' ) . '</p></div>';
						return;
		}

		if ( empty( $api_key ) ) {
			$api_key = null;
		}

		if ( ! empty( $proxy_url ) ) {
			$proxy_url = sudoku120publisher_idn_to_ascii_url( $proxy_url );

			if ( ! filter_var( $proxy_url, FILTER_VALIDATE_URL ) ) {
				return new WP_Error( 'invalid_url', __( 'Please enter a valid URL.', 'sudoku120publisher' ) );
			}
		} else {
			$proxy_url = null;
		}

		if ( empty( $api_key ) && empty( $proxy_url ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Please provide either an API Key or a Proxy URL.', 'sudoku120publisher' ) . '</p></div>';
		} else {
			// Save data in db and get errors.
			$result = sudoku120publisher_create_sudoku( $api_key, $proxy_url );

			// Error processing.
			if ( is_wp_error( $result ) ) {
				echo '<div class="error"><p>' . esc_html( $result->get_error_message() ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . esc_html__( 'New Sudoku created successfully.', 'sudoku120publisher' ) . '</p></div>';
			}
		}
	}

	if ( isset( $_GET['update'] ) && isset( $_GET['nonce'] ) ) {
		$sudoku_id = (int) $_GET['update'];
		$nonce     = sanitize_text_field( wp_unslash( $_GET['nonce'] ) );

		// Check nonce and user permissions.
		if ( ! wp_verify_nonce( $nonce, 'update_sudoku_' . $sudoku_id ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Invalid request, please try again.', 'sudoku120publisher' ) . '</p></div>';
		} elseif ( ! current_user_can( 'manage_options' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'You do not have permission to perform this action.', 'sudoku120publisher' ) . '</p></div>';
		} else {
			sudoku120publisher_update_sudoku_from_api( $sudoku_id );
			echo '<div class="updated"><p>' . esc_html__( 'API data updated successfully.', 'sudoku120publisher' ) . '</p></div>';
		}
	}

	if ( isset( $_GET['delete'] ) && isset( $_GET['nonce'] ) && ! empty( $_GET['delete'] ) && is_numeric( $_GET['delete'] ) ) {
		$sudoku_id = (int) $_GET['delete'];
		$nonce     = sanitize_text_field( wp_unslash( $_GET['nonce'] ) );

		// Check nonce and user permissions.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'delete_sudoku_' ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Invalid request, please try again.', 'sudoku120publisher' ) . '</p></div>';
		} elseif ( ! current_user_can( 'manage_options' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'You do not have permission to perform this action.', 'sudoku120publisher' ) . '</p></div>';
		} else {
			// Check if id is in db.
			$cache_key = "sudoku_{$sudoku_id}";
			$sudoku    = wp_cache_get( $cache_key, 'sudoku120publisher' );

			if ( false === $sudoku ) {
				$sudoku = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT * FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' WHERE id = %d',
						$sudoku_id
					)
				);

				if ( $sudoku ) {
					wp_cache_set( $cache_key, $sudoku, 'sudoku120publisher', 3600 );
				}
			}

			// Delete sudoku from db.
			$wpdb->delete( SUDOKU120PUBLISHER_TABLE_SUDOKU, array( 'id' => $sudoku_id ), array( '%d' ) );

			// Delete proxy from db, when there is one for this sudoku is.
			$wpdb->delete( SUDOKU120PUBLISHER_TABLE_PROXY, array( 'sudoku_id' => $sudoku_id ), array( '%d' ) );

			// Delete cacxhe für this id.
			wp_cache_delete( $cache_key, 'sudoku120publisher' );

			echo '<div class="updated"><p>' . esc_html__( 'Sudoku deleted successfully.', 'sudoku120publisher' ) . '</p></div>';
		}
	}

	// Request sudoku.
	$sudokus = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' ORDER BY id DESC' );

	?>
	<div class="wrap">

		<h1><?php esc_html_e( 'Sudoku120 Publisher - Sudoku Management', 'sudoku120publisher' ); ?></h1>
<h2><?php echo esc_html( sudoku120publisher_idn_to_utf8_url( $domain ) ) . ' '; ?>
<span style="color:
	<?php
	echo ( 'approved' === $domain_status ) ? 'green' :
					( ( 'pending' === $domain_status ) ? 'orange' :
					( ( 'rejected' === $domain_status ) ? 'red' : 'gray' ) );
	?>
						;">
	<?php
	if ( 'approved' === $domain_status ) {
		echo esc_html__( 'Approved', 'sudoku120publisher' );
	} elseif ( 'pending' === $domain_status ) {
		echo esc_html__( 'Pending', 'sudoku120publisher' );
	} elseif ( 'rejected' === $domain_status ) {
		echo esc_html__( 'Rejected', 'sudoku120publisher' );
	} else {
		echo esc_html__( 'Unknown', 'sudoku120publisher' );
	}
	?>
</span></h2>

			<h2><?php esc_html_e( 'Sudokus', 'sudoku120publisher' ); ?></h2>
		<div class="sudoku-list">
			<?php foreach ( $sudokus as $sudoku ) : ?>
				<div class="sudoku-item" style="border-bottom: 1px solid #ddd; padding: 20px;">
<p><strong>ID: <?php echo esc_html( $sudoku->id ); ?> | <?php echo esc_html( $sudoku->name ); ?> <span style="color:
							<?php
							echo ( 'active' === $sudoku->status ) ? 'green' :
							( ( 'config' === $sudoku->status ) ? 'orange' :
							( ( 'inactive' === $sudoku->status ) ? 'gray' : 'red' ) );
							?>
						;">
				<?php
				if ( 'active' === $sudoku->status ) {
					echo esc_html__( 'Active', 'sudoku120publisher' );
				} elseif ( 'config' === $sudoku->status ) {
					echo esc_html__( 'Configuration', 'sudoku120publisher' );
				} elseif ( 'inactive' === $sudoku->status ) {
					echo esc_html__( 'Inactive', 'sudoku120publisher' );
				} else {
					echo esc_html__( 'Problem', 'sudoku120publisher' );
				}
				?>
</span></strong></p>

<p>
				<?php
				if ( extension_loaded( 'intl' ) && ! empty( $sudoku->lang ) ) {
					$language  = Locale::getDisplayLanguage( $sudoku->lang, get_locale() );
					$language .= ' - ';
					$language .= Locale::getDisplayRegion( $sudoku->lang, get_locale() );
					echo esc_html( $language );
				} else {
					echo esc_html( $sudoku->lang );
				}
				?>
	,
				<?php echo esc_html( $sudoku->timezone ); ?></p>
				<?php if ( ! empty( $sudoku->apiurl ) ) : ?>
<p><?php esc_html_e( 'API URL:', 'sudoku120publisher' ); ?>
<a href="<?php echo esc_url( $sudoku->apiurl ); ?>" target="_blank"> <?php echo esc_html( sudoku120publisher_idn_to_utf8_url( $sudoku->apiurl ) ); ?></a>
					<?php if ( 'config' === $sudoku->status ) : ?>
						<?php if ( empty( $sudoku->sudoku_content ) ) : ?>
<button class="button copy-btn" data-clipboard-text="<?php echo esc_attr( $sudoku->apiurl ); ?>"><?php esc_html_e( 'Copy', 'sudoku120publisher' ); ?></button><br>
<span>
							<?php
							/* translators: %s is a link to the webmaster sudoku website. */
							printf( esc_html__( 'Copy the API URL and enter it on %s', 'sudoku120publisher' ), wp_kses_post( SUDOKU120PUBLISHER_SERVICE_LINK ) );
							?>
</span>
<?php elseif ( empty( $sudoku->sudokuurl ) && 'approved' === $domain_status ) : ?>
<br><span><?php esc_html_e( 'Use now the shortcode in a page or post', 'sudoku120publisher' ); ?></span>
<?php endif; ?>
<?php endif; ?>
</p>
<?php endif; ?>

				<?php if ( ! empty( $sudoku->sudokuurl ) ) : ?>
<p><?php esc_html_e( 'Sudoku URL:', 'sudoku120publisher' ); ?>
<a href="<?php echo esc_url( $sudoku->sudokuurl ); ?>" target="_blank"> <?php echo esc_html( sudoku120publisher_idn_to_utf8_url( $sudoku->sudokuurl ) ); ?></a>
					<?php if ( 'config' === $sudoku->status && ! empty( $sudoku->sudoku_content ) ) : ?>
<button class="button copy-btn" data-clipboard-text="<?php echo esc_attr( $sudoku->sudokuurl ); ?>"><?php esc_html_e( 'Copy', 'sudoku120publisher' ); ?></button><br>
<span>
						<?php
						/* translators: %s is a link to the webmaster sudoku website. */
						printf( esc_html__( 'Copy the Sudoku URL and enter it on %s', 'sudoku120publisher' ), wp_kses_post( SUDOKU120PUBLISHER_SERVICE_LINK ) );
						?>
</span>
	<?php endif; ?>
</p>
				<?php endif; ?>

					<p>
				<?php if ( ! empty( $_GET['page'] ) ) { ?>
	<a href="?page=<?php	echo esc_html( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ); ?>&update=<?php echo esc_attr( $sudoku->id ); ?>&nonce=<?php echo esc_html( wp_create_nonce( 'update_sudoku_' . sanitize_text_field( wp_unslash( $sudoku->id ) ) ) ); ?>" class="button">Update API Data</a>
				<?php } ?>
	<a href="#" class="button" style="background-color: red; color: white;" onclick="return sudoku120publisherconfirmDelete(<?php echo esc_attr( $sudoku->id ); ?>)">
				<?php esc_html_e( 'Delete Sudoku', 'sudoku120publisher' ); ?>
</a>
</p>
				</div>
			<?php endforeach; ?>
			<p><?php esc_html_e( 'Shortcode:', 'sudoku120publisher' ); ?> <b>[sudoku120 id=x]</b> <?php esc_html_e( 'replace the x with the id of the Sudoku.', 'sudoku120publisher' ); ?> </p>
		</div>

		<h2><?php esc_html_e( 'Add New Sudoku', 'sudoku120publisher' ); ?></h2>
		<p>
		<?php
		/* translators: %s is a link to the webmaster sudoku website. */
		printf( esc_html__( 'Login on %s and create a sudoku there', 'sudoku120publisher' ), wp_kses_post( SUDOKU120PUBLISHER_SERVICE_LINK ) );
		?>
		<br>
		<?php esc_html_e( 'When the reverse proxy function is enabled you can enter the API Token to create the proxy url.', 'sudoku120publisher' ); ?><br>
			<?php esc_html_e( 'Or create a reverse proxy in the webserver config and enter the url as Proxy URL.', 'sudoku120publisher' ); ?><br>
			<?php esc_html_e( 'Update the API Data after every setup step, to get the actual status here.', 'sudoku120publisher' ); ?><br></p>
		<form method="post">
			<?php wp_nonce_field( 'sudoku120publisher_add_sudoku', 'sudoku120publisher_nonce' ); ?>
			<table class="form-table">
			<?php if ( get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, false ) === '1' ) : ?>
				<tr>
					<th><?php esc_html_e( 'API Token', 'sudoku120publisher' ); ?></th>
					<td>
						<input type="text" name="api_key" class="regular-text" >
					</td>
				</tr>
				<?php endif; ?>
				<tr>
					<th><?php esc_html_e( 'Proxy URL', 'sudoku120publisher' ); ?></th>
					<td>
						<input type="text" name="proxy_url" class="regular-text">
					</td>
				</tr>
			</table>
			<p>
				<input type="submit" name="sudoku120publisher_create_sudoku" value="<?php esc_attr_e( 'Create Sudoku', 'sudoku120publisher' ); ?>" class="button-primary">
			</p>
		</form>
	</div>

	<?php
	add_action(
		'admin_footer',
		function () {
			?>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				if (typeof ClipboardJS !== 'undefined') {
					new ClipboardJS('.copy-btn').on('success', function (e) {
						e.trigger.textContent = 'Copied!';
						setTimeout(() => {
							e.trigger.textContent = 'Copy';
						}, 2000);
					});
				}
			});
		</script>
		<script>
	function sudoku120publisherconfirmDelete(sudoku_id) {
		if (confirm('<?php esc_html_e( 'Are you sure you want to delete this Sudoku?', 'sudoku120publisher' ); ?>')) {
			let page = "<?php echo isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : ''; ?>";
			let nonce = "<?php echo esc_attr( wp_create_nonce( 'delete_sudoku_' ) ); ?>";
			window.location.href = "?page=" + page + "&delete=" + sudoku_id + "&nonce=" + nonce;
		}
		return false;
	}
	</script>

			<?php
		}
	);
	wp_enqueue_script( 'clipboard' );
}


/**
 * Creates a new Sudoku entry and sets up the proxy if an API key is provided.
 *
 * @param string|null $api_key   Optional API key for the remote Sudoku service.
 * @param string|null $proxy_url Optional existing proxy URL.
 * @return int|WP_Error          The ID of the created Sudoku or WP_Error on failure.
 */
function sudoku120publisher_create_sudoku( $api_key = null, $proxy_url = null ) {
	global $wpdb;

	// Convert url to ascii url in case it is an idn domain and validate it.
	if ( ! empty( $proxy_url ) ) {
		$proxy_url = sudoku120publisher_idn_to_ascii_url( $proxy_url );

		if ( ! filter_var( $proxy_url, FILTER_VALIDATE_URL ) ) {
			return new WP_Error( 'invalid_url', __( 'Please enter a valid URL.', 'sudoku120publisher' ) );
		}
	} else {
		$proxy_url = null;
	}

	// Write sudoku into db.
	$wpdb->insert(
		SUDOKU120PUBLISHER_TABLE_SUDOKU,
		array(
			'api_key' => $api_key,
			'apiurl'  => $proxy_url,
		),
		array( '%s', '%s' )
	);

	$sudoku_id = $wpdb->insert_id;
	if ( ! $sudoku_id ) {
		return new WP_Error( 'db_insert_error', __( 'Failed to create Sudoku entry.', 'sudoku120publisher' ) );
	}

	// When api key is given, create remote url and write it into proxy db.
	if ( ! empty( $api_key ) ) {
		$remote_api_url = SUDOKU120PUBLISHER_API_URL . $api_key . '/';
		$proxy_id       = sudoku120publisher_insert_proxy_url( $remote_api_url, $sudoku_id );
		if ( is_wp_error( $proxy_id ) ) {
			return $proxy_id;
		} else {

			// Get proxy id for the sudoku.
			$proxy = $wpdb->get_row(
				$wpdb->prepare(
					'SELECT proxy_uuid FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_PROXY ) . ' WHERE sudoku_id = %d LIMIT 1',
					$sudoku_id
				)
			);

			if ( ! $proxy ) {
				return new WP_Error( 'proxy_not_found', __( 'No proxy found for the Sudoku.', 'sudoku120publisher' ) );
			}

			// generate local proxy url for the remote api.
			$local_proxy_url = home_url( '/' . SUDOKU120PUBLISHER_PROXY_SLUG . '/' . $proxy->proxy_uuid . '/' );

			// Update remote proxy url for the sudoku.
			$wpdb->update(
				SUDOKU120PUBLISHER_TABLE_SUDOKU,
				array(
					'apiurl' => $local_proxy_url,
				),
				array( 'id' => $sudoku_id ),
				array( '%s' ),
				array( '%d' )
			);
		}
	}

	// Get data from remote api and update sudoku db entry.
	$update_result = sudoku120publisher_update_sudoku_from_api( $sudoku_id );
	if ( is_wp_error( $update_result ) ) {
		return $update_result;
	}

	return $sudoku_id;
}


/**
 * Fetches the Sudoku HTML from a remote API, processes it by replacing asset paths,
 * and saves the updated HTML in the database.
 *
 * This function:
 * - Retrieves the API URL for the specified Sudoku ID from the database.
 * - Fetches the raw HTML for the Sudoku puzzle from the remote API.
 * - Replaces the default CSS and JS file paths in the HTML with local paths.
 * - Saves the modified HTML content into the database.
 *
 * @param int $sudoku_id The ID of the Sudoku entry to fetch the HTML for.
 *
 * @return bool|WP_Error True on success, or a WP_Error object on failure.
 */
function sudoku120publisher_fetch_sudoku_html( $sudoku_id ) {
	global $wpdb;

	// Get sudoku data from db.
	$sudoku = $wpdb->get_row(
		$wpdb->prepare(
			'SELECT apiurl FROM ' . esc_sql( SUDOKU120PUBLISHER_TABLE_SUDOKU ) . ' WHERE id = %d',
			$sudoku_id
		)
	);

	if ( ! $sudoku || empty( $sudoku->apiurl ) ) {
		return new WP_Error( 'missing_data', __( 'Invalid Sudoku ID or missing API URL.', 'sudoku120publisher' ) );
	}

	$sudoku_api_url = rtrim( $sudoku->apiurl, '/' ) . '/sudoku.txt';

	// Get Sudoku HTML from remote Api.
	$response = wp_remote_get(
		$sudoku_api_url,
		array(
			'timeout'     => 10,
			'redirection' => 5,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$status_code = wp_remote_retrieve_response_code( $response );
	if ( 200 !== $status_code ) {
		/* translators: %d is the response HTTP status code. */
		return new WP_Error( 'http_error', sprintf( __( 'Failed to fetch Sudoku HTML (HTTP %d).', 'sudoku120publisher' ), $status_code ) );
	}

	$html = wp_remote_retrieve_body( $response );

	$upload_dir = wp_upload_dir();
	$local_css  = $upload_dir['baseurl'] . '/sudoku120publisher/assets/sudokuonline.css';
	$local_js   = $upload_dir['baseurl'] . '/sudoku120publisher/assets/sudokuonline.js';

	// Replace default paths for the neede css and js files with the local ones.
	$search  = array(
		'href="/sudoku-assets/sudokuonline.css"',
		'src="/sudoku-assets/sudokuonline.js"',
	);
	$replace = array(
		'href="' . esc_url( $local_css ) . '"',
		'src="' . esc_url( $local_js ) . '"',
	);
	$html    = str_replace( $search, $replace, $html );

	// Save sudoku HTML into the db.
	$wpdb->update(
		SUDOKU120PUBLISHER_TABLE_SUDOKU,
		array( 'sudoku_content' => $html ),
		array( 'id' => $sudoku_id ),
		array( '%s' ),
		array( '%d' )
	);

	return true;
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
		'name'     => $data['name'] ?? null,
		'lang'     => $data['lang'] ?? null,
		'timezone' => $data['timezone'] ?? null,
		'status'   => $data['status'] ?? null,
	);

	// Only update if the values are not empty.
	if ( ! empty( $data['sudokuurl'] ) ) {
		$update_data['sudokuurl'] = $data['sudokuurl'];
	}
	if ( ! empty( $data['apiurl'] ) ) {
		$update_data['apiurl'] = $data['apiurl'];
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
		update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN, $data['domain'] );
	}
	if ( isset( $data['domainstatus'] ) ) {
		update_option( SUDOKU120PUBLISHER_OPTION_DOMAIN_STATUS, $data['domainstatus'] );
	}

	return true;
}
