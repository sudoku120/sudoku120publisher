<?php
/**
 * Admin settings for the Sudoku120 Publisher plugin.
 *
 * This file registers and handles the plugin's admin settings page,
 * allowing the user to configure proxy, design, link, and container attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register settings
 */
function sudoku120publisher_register_settings() {
	register_setting( 'sudoku120publisher_settings_group', SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE );
	register_setting( 'sudoku120publisher_settings_group', SUDOKU120PUBLISHER_OPTION_DESIGN );
	register_setting( 'sudoku120publisher_settings_group', SUDOKU120PUBLISHER_OPTION_LINK_REL );
	register_setting( 'sudoku120publisher_settings_group', SUDOKU120PUBLISHER_OPTION_LINK_BLANK );
	register_setting( 'sudoku120publisher_settings_group', SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR );
}
add_action( 'admin_init', 'sudoku120publisher_register_settings' );


/**
 * Display settings page
 */
function sudoku120publisher_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Verify nonce.
	if ( isset( $_POST['sudoku120publisher_settings_submit'] ) && check_admin_referer( 'sudoku120publisher_settings_action', 'sudoku120publisher_nonce' ) ) {
		$old_proxy_settings = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE );

		update_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, isset( $_POST['proxy_active'] ) ? '1' : '0' );

		// Check if 'design' is set and sanitize the file name.
		if ( isset( $_POST['design'] ) ) {
			$design = sanitize_file_name( wp_unslash( $_POST['design'] ) );

			// Ensure the file name ends with .css.
			if ( ! preg_match( '/\.css$/', $design ) ) {
				$design = '';
			}
		} else {
			$design = '';
		}

		// Update the option with the sanitized file name.
		update_option( SUDOKU120PUBLISHER_OPTION_DESIGN, $design );

		update_option( SUDOKU120PUBLISHER_OPTION_LINK_REL, isset( $_POST['link_rel'] ) ? '1' : '0' );
		update_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK, isset( $_POST['targetblank'] ) ? '1' : '0' );

		// Allowed attributes for a <div> tag.
		$allowed_html = array(
			'div' => array(
				'id'     => true,       // Allow 'id' attribute.
				'class'  => true,    // Allow 'class' attribute.
				'style'  => true,    // Allow 'style' attribute.
				'title'  => true,    // Allow 'title' attribute.
				'data-*' => true,   // Allow custom 'data-*' attributes (e.g., data-some="value").
			),
		);
		// Check if 'custom_attr' is set and sanitize immediately.
		$custom_attr = isset( $_POST['custom_attr'] ) ? wp_kses( wp_unslash( $_POST['custom_attr'] ), $allowed_html ) : '';

		// Only proceed if 'custom' is selected and custom attributes are provided.
		if ( isset( $_POST['sudoku_div_attr'] ) && 'custom' === $_POST['sudoku_div_attr'] && ! empty( $custom_attr ) ) {

			// Update the option with the sanitized custom attribute.
			update_option(
				SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR,
				$custom_attr
			);
		} else {
			// If no custom attribute is provided, update with the default value.
			update_option(
				SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR,
				SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT
			);
		}

		$new_proxy_settings = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE );

		if ( $old_proxy_settings !== $new_proxy_settings ) {
			update_option( 'sudoku120publisher_needs_rewrite_flush', '1' );
		}
	}

	$proxy_active    = get_option( SUDOKU120PUBLISHER_OPTION_PROXY_ACTIVE, 0 );
	$design          = get_option( SUDOKU120PUBLISHER_OPTION_DESIGN, '' );
	$link_rel        = get_option( SUDOKU120PUBLISHER_OPTION_LINK_REL, '' );
	$targetblank     = get_option( SUDOKU120PUBLISHER_OPTION_LINK_BLANK, '' );
	$sudoku_div_attr = get_option( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_ATTR, SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT );

	// Get available CSS files.
	$designs    = array( '' => esc_html__( 'None', 'sudoku120publisher' ) );
	$upload_dir = wp_upload_dir();
	$design_dir = $upload_dir['basedir'] . '/sudoku120publisher/designs/';
	if ( is_dir( $design_dir ) ) {
		foreach ( glob( $design_dir . '*.css' ) as $file ) {
			$designs[ basename( $file ) ] = esc_html( basename( $file ) );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Sudoku120 Publisher Settings', 'sudoku120publisher' ); ?></h1>
		<form method="post">
			<?php wp_nonce_field( 'sudoku120publisher_settings_action', 'sudoku120publisher_nonce' ); ?>

			<h2><?php esc_html_e( 'Reverse Proxy Settings', 'sudoku120publisher' ); ?></h2>
			<label>
				<input type="checkbox" name="proxy_active" value="1" <?php checked( $proxy_active, 1 ); ?>>
				<?php esc_html_e( 'Enable Reverse Proxy', 'sudoku120publisher' ); ?>
			</label>
			<p class="description"> <?php esc_html_e( 'Enable or disable the proxy feature.', 'sudoku120publisher' ); ?> </p>

			<h2><?php esc_html_e( 'Sudoku Design Settings', 'sudoku120publisher' ); ?></h2>
			<label>
				<select name="design">
					<?php foreach ( $designs as $filename => $name ) : ?>
						<option value="<?php echo esc_attr( $filename ); ?>" <?php selected( $design, $filename ); ?>><?php echo esc_html( $name ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<p class="description"> <?php esc_html_e( 'Choose a CSS file for styling or select none. You can place your own design css in uploads/sudoku120publisher/designs/ and select it then here', 'sudoku120publisher' ); ?> </p>

			<h2><?php esc_html_e( 'Sudoku Container Attributes', 'sudoku120publisher' ); ?></h2>
			<label>
				<input type="radio" name="sudoku_div_attr" value="custom" <?php checked( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT !== $sudoku_div_attr ); ?>>
				<input type="text" name="custom_attr"
				<?php
				if ( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT !== $sudoku_div_attr ) {
								echo ' value="' . esc_textarea( $sudoku_div_attr ) . '" ';
				}
				?>
				class="regular-text">
				<?php esc_html_e( 'Custom Attributes', 'sudoku120publisher' ); ?>
			</label>
			<br>
			<label>
				<input type="radio" name="sudoku_div_attr" value="default" <?php checked( $sudoku_div_attr, SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT ); ?>>
				<?php echo esc_html( SUDOKU120PUBLISHER_OPTION_SUDOKU_DIV_DEFAULT ); ?>
			</label>
			<p class="description"> <?php esc_html_e( 'Extra Attributes for the div around the sudoku, set id, classes or direct a style..', 'sudoku120publisher' ); ?> </p>

			<?php wp_nonce_field( 'sudoku120publisher_settings_action', 'sudoku120publisher_nonce' ); ?>

			<h2><?php esc_html_e( 'Outgoing Sudoku Link Settings', 'sudoku120publisher' ); ?></h2>
			<label>
				<input type="checkbox" name="link_rel" value="1" <?php checked( $link_rel, 1 ); ?>>
				<?php esc_html_e( 'rel="noopener noreferrer"', 'sudoku120publisher' ); ?>
			</label>
			<p class="description"> <?php esc_html_e( 'rel="noopener noreferrer" are security features for links', 'sudoku120publisher' ); ?> </p>
			<label>
				<input type="checkbox" name="targetblank" value="1" <?php checked( $targetblank, 1 ); ?>>
				<?php esc_html_e( 'target="_blank"', 'sudoku120publisher' ); ?>
			</label>
			<p class="description"> <?php esc_html_e( 'target="_blank" will open the Link in a new browser tab or window when you click it.', 'sudoku120publisher' ); ?> </p>

			<p><input type="submit" name="sudoku120publisher_settings_submit" value="<?php esc_attr_e( 'Save Changes', 'sudoku120publisher' ); ?>" class="button-primary"></p>
		</form>
		<script>
document.addEventListener("DOMContentLoaded", function() {
	const customAttrInput = document.querySelector('input[name="custom_attr"]');
	const customRadio = document.querySelector('input[name="sudoku_div_attr"][value="custom"]');

	if (customAttrInput && customRadio) {
		customAttrInput.addEventListener("focus", function() {
			customRadio.checked = true;
		});
	}
});
</script>
	</div>
	<?php
}
