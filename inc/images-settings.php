<?php
/**
 * Image settings page.
 *
 * @package Greenlight
 */

/**
 * Sanitizes the image settings.
 *
 * @param mixed $input Raw submitted settings.
 * @return array<string, mixed>
 */
function greenlight_sanitize_images_settings( $input ) {
	return greenlight_sanitize_image_settings( $input );
}

/**
 * Registers the image settings.
 *
 * @return void
 */
function greenlight_register_image_settings() {
	register_setting(
		'greenlight_images',
		GREENLIGHT_IMAGES_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'greenlight_sanitize_images_settings',
			'default'           => greenlight_get_images_defaults(),
		)
	);
}
add_action( 'admin_init', 'greenlight_register_image_settings' );

/**
 * Adds the image settings page under Appearance.
 *
 * @return void
 */
function greenlight_add_image_settings_page() {
	add_theme_page(
		__( 'Greenlight Images', 'greenlight' ),
		__( 'Images', 'greenlight' ),
		'edit_theme_options',
		'greenlight-images',
		'greenlight_render_image_settings_page'
	);
}
add_action( 'admin_menu', 'greenlight_add_image_settings_page' );

/**
 * Formats a byte count for display.
 *
 * @param int $bytes Byte count.
 * @return string
 */
function greenlight_format_image_bytes( $bytes ) {
	return size_format( max( 0, absint( $bytes ) ), 2 );
}

/**
 * Returns a simple WebP storage report.
 *
 * @return array<string, int>
 */
function greenlight_get_image_storage_report() {
	$attachments = get_posts(
		array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'post_mime_type'         => 'image',
			'fields'                 => 'ids',
			'posts_per_page'         => -1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);

	$count      = 0;
	$original   = 0;
	$webp       = 0;
	$saved      = 0;

	foreach ( $attachments as $attachment_id ) {
		$file_path = get_attached_file( $attachment_id );

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			continue;
		}

		$webp_path = greenlight_get_webp_sidecar_path( $file_path );

		if ( '' === $webp_path || ! file_exists( $webp_path ) ) {
			continue;
		}

		$original_size = (int) filesize( $file_path );
		$webp_size     = (int) filesize( $webp_path );

		if ( $original_size <= 0 || $webp_size <= 0 ) {
			continue;
		}

		$count++;
		$original += $original_size;
		$webp     += $webp_size;

		if ( $webp_size < $original_size ) {
			$saved += $original_size - $webp_size;
		}
	}

	return array(
		'count'    => $count,
		'original' => $original,
		'webp'     => $webp,
		'saved'    => $saved,
	);
}

/**
 * Renders the image settings page.
 *
 * @return void
 */
function greenlight_render_image_settings_page() {
	$options = greenlight_get_images_options();
	$report  = greenlight_get_image_storage_report();
	$webp_supported = greenlight_is_webp_conversion_enabled();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Greenlight Images', 'greenlight' ); ?></h1>

		<?php if ( ! $webp_supported ) : ?>
			<div class="notice notice-warning inline">
				<p><?php esc_html_e( 'La conversion WebP n’est pas disponible sur ce serveur ou est désactivée dans les réglages.', 'greenlight' ); ?></p>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'greenlight_images' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Conversion WebP', 'greenlight' ); ?></th>
					<td>
						<label for="greenlight-enable-webp">
							<input id="greenlight-enable-webp" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[enable_webp_conversion]" type="checkbox" value="1" <?php checked( (int) $options['enable_webp_conversion'], 1 ); ?>>
							<?php esc_html_e( 'Générer un fichier WebP à l’upload.', 'greenlight' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="greenlight-webp-quality"><?php esc_html_e( 'Qualité WebP', 'greenlight' ); ?></label>
					</th>
					<td>
						<input id="greenlight-webp-quality" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[webp_quality]" type="range" min="1" max="100" value="<?php echo esc_attr( (int) $options['webp_quality'] ); ?>">
						<span><?php echo esc_html( (int) $options['webp_quality'] ); ?>/100</span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Tailles core', 'greenlight' ); ?></th>
					<td>
						<label for="greenlight-remove-core-sizes">
							<input id="greenlight-remove-core-sizes" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[remove_core_sizes]" type="checkbox" value="1" <?php checked( (int) $options['remove_core_sizes'], 1 ); ?>>
							<?php esc_html_e( 'Supprimer medium_large, 1536x1536 et 2048x2048.', 'greenlight' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>

		<h2><?php esc_html_e( 'Espace économisé', 'greenlight' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: count, 2: saved bytes */
				esc_html__( '%1$s image(s) WebP détectée(s), environ %2$s économisés.', 'greenlight' ),
				esc_html( number_format_i18n( (int) $report['count'] ) ),
				esc_html( greenlight_format_image_bytes( (int) $report['saved'] ) )
			);
			?>
		</p>
		<p class="description">
			<?php
			printf(
				/* translators: 1: original bytes, 2: webp bytes */
				esc_html__( 'Stockage total original : %1$s. Stockage WebP : %2$s.', 'greenlight' ),
				esc_html( greenlight_format_image_bytes( (int) $report['original'] ) ),
				esc_html( greenlight_format_image_bytes( (int) $report['webp'] ) )
			);
			?>
		</p>
	</div>
	<?php
}
