<?php
/**
 * Sudoku120 Publisher - Proxy Management Page.
 *
 * This file contains the code to display the proxy list page in the WordPress admin panel.
 * It allows administrators to add, edit, and delete proxy entries used for interacting with remote Sudoku APIs.
 *
 * The page handles the following operations:
 * 1. Add a new proxy entry.
 * 2. Edit an existing proxy entry.
 * 3. Delete a proxy entry (if it's not linked to a Sudoku).
 *
 * All operations are secured with nonce verification to ensure that the requests are valid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Display Proxy List Page.
 *
 * @return void
 */
function sudoku120publisher_proxy_url_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	global $wpdb;
	$allowed_groups = array( 'json', 'xml', 'txt', 'utf8', 'media' );
	$table_name     = SUDOKU120PUBLISHER_TABLE_PROXY;
	// Handle Deletion.
	if ( isset( $_GET['delete'] ) ) {

		if ( isset( $_GET['nonce'] ) && current_user_can( 'manage_options' ) ) {
			// Check if Nonce is valid.
			if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'sudoku120publisher_delete_proxy' ) ) {
				echo '<div class="error"><p>' . esc_html__( 'Nonce verification failed. Please try again.', 'sudoku120publisher' ) . '</p></div>';
				return;
			}

			// check if ID is valid.
			$id = intval( $_GET['delete'] );

			$cache_key = 'proxy_' . $id;  // Eindeutiger Cache-Key basierend auf der ID.
			// Check the proxy data.
			$proxy = wp_cache_get( $cache_key, 'sudoku120publisher' ); // check if data in cache.

			if ( false === $proxy ) {

				$proxy = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE id = %d', $id ) );

				if ( $proxy ) {
					wp_cache_set( $cache_key, $proxy, 'sudoku120publisher', HOUR_IN_SECONDS ); // save data in cache.
				}
			}

			// Check if proxy esists and not aligned to a sudoku.
			if ( $proxy && is_null( $proxy->sudoku_id ) ) {
				$wpdb->delete( $table_name, array( 'id' => $id ) );
				wp_cache_delete( $cache_key, 'sudoku120publisher' );
				echo '<div class="updated"><p>' . esc_html__( 'Proxy entry deleted.', 'sudoku120publisher' ) . '</p></div>';
			} else {
				echo '<div class="error"><p>' . esc_html__( 'Cannot delete proxy linked to a Sudoku.', 'sudoku120publisher' ) . '</p></div>';
			}
		} else {
			// Nonce not set or invalid.
			echo '<div class="error"><p>' . esc_html__( 'Invalid request. Please try again.', 'sudoku120publisher' ) . '</p></div>';
		}
	}

	// Handle Edit.
	if ( isset( $_POST['edit_proxy'] ) && current_user_can( 'manage_options' ) ) {
		if ( ! isset( $_POST['sudoku120publisher_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sudoku120publisher_nonce'] ) ), 'sudoku120publisher_edit_proxy' ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Nonce verification failed. Please try again.', 'sudoku120publisher' ) . '</p></div>';
			return;
		}
		if ( ! empty( $_POST['url'] ) ) {
			$url = sudoku120publisher_idn_to_ascii_url( sanitize_text_field( wp_unslash( $_POST['url'] ) ) );

			// Validate the URL after conversion to ASCII.
			if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
				// Show an error message with translation support.
				add_settings_error(
					'sudoku120publisher_error', // Setting name (for the error).
					'invalid_url',              // Error code.
					esc_html__( 'Please enter a valid URL.', 'sudoku120publisher' ), // Error message (translated).
					'error'                     // Type of message (error).
				);
				return false;  // Invalid URL.
			}
			$mimetype_groups = isset( $_POST['mimetype_groups'] ) && is_array( $_POST['mimetype_groups'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_POST['mimetype_groups'] ) )
			: array();

			$mimetype_groups      = array_filter(
				$mimetype_groups,
				function ( $group ) use ( $allowed_groups ) {
						return in_array( $group, $allowed_groups, true );
				}
			);
			$mimetype_groups_json = ! empty( $mimetype_groups ) ? wp_json_encode( $mimetype_groups ) : null;

		}
		if ( ! empty( $_POST['proxy_id'] ) ) {
			$id         = intval( $_POST['proxy_id'] );
			$client_ip  = isset( $_POST['client_ip'] ) ? 1 : 0;
			$user_agent = isset( $_POST['user_agent'] ) ? 1 : 0;
			$referrer   = isset( $_POST['referrer'] ) ? 1 : 0;
			$wpdb->update(
				$table_name,
				array(
					'url'        => $url,
					'client_ip'  => $client_ip,
					'user_agent' => $user_agent,
					'referrer'   => $referrer,
					'mimetypes'  => $mimetype_groups_json,
				),
				array( 'id' => $id ),
				array( '%s', '%d', '%d', '%d', '%s' ),
				array( '%d' )
			);
			echo '<div class="updated"><p>' . esc_html__( 'Proxy entry updated.', 'sudoku120publisher' ) . '</p></div>';
			unset( $_GET['edit'] );
		}
	}

	// Handle Insert.
	if ( isset( $_POST['add_proxy'] ) && current_user_can( 'manage_options' ) ) {

		// Check if nonce is valid.
		if ( ! isset( $_POST['sudoku120publisher_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sudoku120publisher_nonce'] ) ), 'sudoku120publisher_add_proxy' ) ) {
			echo '<div class="error"><p>' . esc_html__( 'Nonce verification failed. Please try again.', 'sudoku120publisher' ) . '</p></div>';
			return;
		}
		$mimetype_groups = isset( $_POST['mimetype_groups'] ) && is_array( $_POST['mimetype_groups'] )
		? array_map( 'sanitize_text_field', wp_unslash( $_POST['mimetype_groups'] ) )
		: array();

		$mimetype_groups      = array_filter(
			$mimetype_groups,
			function ( $group ) use ( $allowed_groups ) {
					return in_array( $group, $allowed_groups, true );
			}
		);
		$mimetype_groups_json = ! empty( $mimetype_groups ) ? wp_json_encode( $mimetype_groups ) : null;

		if ( ! empty( $_POST['url'] ) ) {
			$url        = sudoku120publisher_idn_to_ascii_url( sanitize_text_field( wp_unslash( $_POST['url'] ) ) );
			$client_ip  = isset( $_POST['client_ip'] ) ? 1 : 0;
			$user_agent = isset( $_POST['user_agent'] ) ? 1 : 0;
			$referrer   = isset( $_POST['referrer'] ) ? 1 : 0;

			sudoku120publisher_insert_proxy_url( $url, null, $client_ip, $user_agent, $referrer, $mimetype_groups_json );
		}
	}

	// Fetch Proxy Entries.
	$proxies = $wpdb->get_results( 'SELECT * FROM ' . esc_sql( $table_name ) );

	// Display errors.
	$errors = get_settings_errors( 'sudoku120publisher_error' );
	if ( $errors ) {
		foreach ( $errors as $error ) {
			echo '<div class="error"><p>' . esc_html( $error['message'] ) . '</p></div>';
		}
	} ?>
<div class="wrap">
	<h1><?php echo esc_html__( 'Sudoku120 Proxy List', 'sudoku120publisher' ); ?></h1>

	<table class="widefat" cellspacing="0">
		<thead>
			<tr>
				<th><?php echo esc_html__( 'ID', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Local URL', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Remote URL', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Client IP', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'User Agent', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Referrer', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Filter', 'sudoku120publisher' ); ?></th>
				<th><?php echo esc_html__( 'Actions', 'sudoku120publisher' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$editnounce = wp_create_nonce( 'edit_sudoku_nonce' );
			foreach ( $proxies as $proxy ) :
				// Generate the full local proxy URL.
				$local_proxy_url = home_url( '/' . SUDOKU120PUBLISHER_PROXY_SLUG . '/' . $proxy->proxy_uuid . '/' );
				$mimetype_groups = $proxy->mimetypes ? json_decode( $proxy->mimetypes, true ) : array();
				?>
			<tr>
				<td><?php echo esc_html( $proxy->id ); ?></td>
				<td><?php echo esc_html( $local_proxy_url ); ?><br>

					<button class="button sudoku120publisher-copy-btn" data-clipboard-text="<?php echo esc_url( sudoku120publisher_idn_to_ascii_url( $local_proxy_url ) ); ?>">
				<?php echo esc_html__( 'Copy', 'sudoku120publisher' ); ?>
</button>





</td>
<td>
				<?php
				// Convert the URL to IDN (Internationalized Domain Name) format.
				$proxy_idn = sudoku120publisher_idn_to_utf8_url( $proxy->url );

				// Compare the IDN version with the original URL.
				if ( $proxy_idn && $proxy_idn !== $proxy->url ) {
					// Display both versions (IDN and ASCII).
					echo '<b>' . esc_html( $proxy_idn ) . '</b><br> (' . esc_html( $proxy->url ) . ')';
				} else {
					// Display only the URL when it is no IDN Domain.
					echo '<b>' . esc_html( $proxy->url ) . '</b>';
				}
				?>
</td>
<td><?php echo $proxy->client_ip ? esc_html__( 'Yes', 'sudoku120publisher' ) . ' &#9888;' : esc_html__( 'No', 'sudoku120publisher' ) . ' &#10004;'; ?></td>
<td><?php echo $proxy->user_agent ? esc_html__( 'Yes', 'sudoku120publisher' ) : esc_html__( 'No', 'sudoku120publisher' ) . ' &#10004;'; ?></td>
<td><?php echo $proxy->referrer ? esc_html__( 'Yes', 'sudoku120publisher' ) : esc_html__( 'No', 'sudoku120publisher' ) . ' &#10004;'; ?></td>
<td><?php echo esc_html( implode( ' ', $mimetype_groups ) ); ?></td>
<td>
				<?php if ( is_null( $proxy->sudoku_id ) ) : ?>
					<a href="<?php echo esc_url( '?page=sudoku120publisher_proxy&edit=' . $proxy->id . '&editnonce=' . $editnounce ); ?>">
					<?php echo esc_html__( 'Edit', 'sudoku120publisher' ); ?>
				</a>
<a href="#" onclick="return sudoku120publisherconfirmDelete(<?php echo esc_html( $proxy->id ); ?>);">
					<?php echo esc_html__( 'Delete', 'sudoku120publisher' ); ?>
		</a>
	<?php else : ?>
		<em>
		<?php
		/* translators: %s is the id of the sudoku. */
		printf( esc_html__( 'Sudoku id=%s', 'sudoku120publisher' ), esc_html( $proxy->sudoku_id ) );
		?>
		</em>
	<?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>

	<?php
	if ( isset( $_GET['edit'] ) && isset( $_GET['editnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['editnonce'] ) ), 'edit_sudoku_nonce' ) ) :
		$edit_id = intval( $_GET['edit'] );
		$proxy   = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . esc_sql( $table_name ) . ' WHERE id = %d', $edit_id ) );
		if ( $proxy ) :
				$proxy->mimetype_groups = $proxy->mimetypes ? json_decode( $proxy->mimetypes, true ) : array();
			?>
		<h2><?php echo esc_html__( 'Edit Proxy', 'sudoku120publisher' ); ?></h2>
		<form method="post">
				<?php wp_nonce_field( 'sudoku120publisher_edit_proxy', 'sudoku120publisher_nonce' ); ?>
			<input type="hidden" name="proxy_id" value="<?php echo esc_attr( $proxy->id ); ?>">
			<label><?php echo esc_html__( 'URL:', 'sudoku120publisher' ); ?> <input type="text" name="url" value="<?php echo esc_attr( sudoku120publisher_idn_to_utf8_url( $proxy->url ) ); ?>" required class="regular-text"></label><br>
			<label><input type="checkbox" name="client_ip" <?php checked( $proxy->client_ip, 1 ); ?>> <?php echo esc_html__( 'Forward Client IP', 'sudoku120publisher' ); ?></label>
			<label><input type="checkbox" name="user_agent" <?php checked( $proxy->user_agent, 1 ); ?>> <?php echo esc_html__( 'Forward User Agent', 'sudoku120publisher' ); ?></label>
			<label><input type="checkbox" name="referrer" <?php checked( $proxy->referrer, 1 ); ?>> <?php echo esc_html__( 'Forward Referrer', 'sudoku120publisher' ); ?></label><br><br>

			<!-- Mimetype Group Checkboxes -->
			<b><?php echo esc_html__( 'Mimetype Filter', 'sudoku120publisher' ); ?></b><br><br>
			<label><input type="checkbox" name="mimetype_groups[]" value="json" <?php checked( in_array( 'json', $proxy->mimetype_groups, true ) ); ?>> <?php echo esc_html__( 'json: (json, x-json, ld+json)', 'sudoku120publisher' ); ?></label><br>
			<label><input type="checkbox" name="mimetype_groups[]" value="xml" <?php checked( in_array( 'xml', $proxy->mimetype_groups, true ) ); ?>> <?php echo esc_html__( 'xml: (xml, rss+xml, atom+xml)', 'sudoku120publisher' ); ?></label><br>
			<label><input type="checkbox" name="mimetype_groups[]" value="txt" <?php checked( in_array( 'txt', $proxy->mimetype_groups, true ) ); ?>> <?php echo esc_html__( 'txt: Plain Text (.txt)', 'sudoku120publisher' ); ?></label><br>
			<label><input type="checkbox" name="mimetype_groups[]" value="utf8" <?php checked( in_array( 'utf8', $proxy->mimetype_groups, true ) ); ?>> <?php echo esc_html__( 'utf8: (HTML, CSS, JS, etc.)', 'sudoku120publisher' ); ?></label><br>
			<label><input type="checkbox" name="mimetype_groups[]" value="media" <?php checked( in_array( 'media', $proxy->mimetype_groups, true ) ); ?>> <?php echo esc_html__( 'media: Audio, Video, Image formats', 'sudoku120publisher' ); ?></label><br><br>

			<input type="submit" name="edit_proxy" value="<?php echo esc_html__( 'Save Changes', 'sudoku120publisher' ); ?>" class="button-primary">
			<button type="button" onclick="window.location.href = '<?php echo esc_url( admin_url( 'admin.php?page=sudoku120publisher_proxy' ) ); ?>';" class="button-secondary"><?php echo esc_html__( 'Cancel', 'sudoku120publisher' ); ?></button>
		</form>
		<?php endif; ?>
	<?php endif; ?>

<h2><?php echo esc_html__( 'Add New Proxy', 'sudoku120publisher' ); ?></h2>
<form method="post">
	<?php wp_nonce_field( 'sudoku120publisher_add_proxy', 'sudoku120publisher_nonce' ); ?>
	<label><?php echo esc_html__( 'Remote URL:', 'sudoku120publisher' ); ?> <input type="text" name="url" required class="regular-text"></label><br>
	<label><input type="checkbox" name="client_ip"> <?php esc_html_e( 'Forward Client IP', 'sudoku120publisher' ); ?></label>
	<label><input type="checkbox" name="user_agent"> <?php esc_html_e( 'Forward User Agent', 'sudoku120publisher' ); ?></label>
	<label><input type="checkbox" name="referrer" checked> <?php esc_html_e( 'Forward Referrer', 'sudoku120publisher' ); ?></label><br><br>

	<!-- Mimetype Group Checkboxes -->
		<b><?php echo esc_html__( 'Mimetype Filter', 'sudoku120publisher' ); ?></b><br>
		<?php
		esc_html_e( 'Select which types of responses are allowed through the proxy. If no types are selected, all content types are allowed by default.', 'sudoku120publisher' );
		?>
		<br><br>
		<label><input type="checkbox" name="mimetype_groups[]" value="json"> <?php echo esc_html__( 'json: (json, x-json, ld+json)', 'sudoku120publisher' ); ?></label><br>
		<label><input type="checkbox" name="mimetype_groups[]" value="xml"> <?php echo esc_html__( 'xml: (xml, rss+xml, atom+xml)', 'sudoku120publisher' ); ?></label><br>
		<label><input type="checkbox" name="mimetype_groups[]" value="txt"> <?php echo esc_html__( 'txt: Plain Text (.txt)', 'sudoku120publisher' ); ?></label><br>
		<label><input type="checkbox" name="mimetype_groups[]" value="utf8"> <?php echo esc_html__( 'utf8: (HTML, CSS, JS, etc.)', 'sudoku120publisher' ); ?></label><br>
		<label><input type="checkbox" name="mimetype_groups[]" value="media"> <?php echo esc_html__( 'media: Audio, Video, Image formats', 'sudoku120publisher' ); ?></label><br><br>


	<input type="submit" name="add_proxy" value="<?php esc_html_e( 'Add Proxy', 'sudoku120publisher' ); ?>" class="button-primary">
</form>
</div>
	</p>
	<?php
}


/**
 * Enqueues the admin-specific JavaScript files.
 *
 * This function checks if the current admin page is the plugin settings page,
 * and if so, enqueues the necessary JavaScript files for the plugin.
 *
 * @param string $hook The current admin page hook.
 *
 * @return void
 */
function sudoku120publisher_enqueue_admin_scripts( $hook ) {

	if ( 'toplevel_page_sudoku120publisher_proxy' !== $hook ) {
		return;
	}

	wp_enqueue_script(
		'sudoku120publisher-admin-js',
		plugin_dir_url( __FILE__ ) . 'js/sudoku120publisher-delete-copy.js',
		array( 'clipboard' ),
		SUDOKU120PUBLISHER_VERSION,
		true
	);

	wp_localize_script(
		'sudoku120publisher-admin-js',
		'sudoku120publisherL10n',
		array(
			'copy'           => esc_html__( 'Copy', 'sudoku120publisher' ),
			'copied'         => esc_html__( 'Copied!', 'sudoku120publisher' ),
			'confirm_delete' => esc_html__( 'Are you sure you want to delete this Sudoku?', 'sudoku120publisher' ),
			'page'           => isset( $_GET['page'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) : '',
			'nonce'          => esc_attr( wp_create_nonce( 'sudoku120publisher_delete_proxy' ) ),
		)
	);
}
	add_action( 'admin_enqueue_scripts', 'sudoku120publisher_enqueue_admin_scripts' );
