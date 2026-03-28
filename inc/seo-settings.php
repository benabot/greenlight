<?php
/**
 * SEO settings page.
 *
 * @package Greenlight
 */

/**
 * Sanitizes the SEO settings array.
 *
 * @param mixed $input Raw submitted settings.
 * @return array<string, mixed>
 */
function greenlight_sanitize_seo_settings( $input ) {
	$defaults = greenlight_get_seo_defaults();
	$input    = is_array( $input ) ? $input : array();

	return array(
		'site_title'              => isset( $input['site_title'] ) ? greenlight_sanitize_seo_text( $input['site_title'] ) : $defaults['site_title'],
		'site_description'        => isset( $input['site_description'] ) ? greenlight_sanitize_seo_textarea( $input['site_description'] ) : $defaults['site_description'],
		'title_separator'         => isset( $input['title_separator'] ) ? greenlight_sanitize_seo_text( $input['title_separator'] ) : $defaults['title_separator'],
		'enable_sitemap'          => isset( $input['enable_sitemap'] ) ? 1 : 0,
		'noindex_author_archives' => isset( $input['noindex_author_archives'] ) ? 1 : 0,
		'noindex_tag_archives'    => isset( $input['noindex_tag_archives'] ) ? 1 : 0,
		'show_breadcrumbs'        => isset( $input['show_breadcrumbs'] ) ? 1 : 0,
		'custom_robots_txt'       => isset( $input['custom_robots_txt'] ) ? sanitize_textarea_field( $input['custom_robots_txt'] ) : $defaults['custom_robots_txt'],
	);
}

/**
 * Registers the SEO settings.
 *
 * @return void
 */
function greenlight_register_seo_settings() {
	register_setting(
		'greenlight_seo',
		GREENLIGHT_SEO_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'greenlight_sanitize_seo_settings',
			'default'           => greenlight_get_seo_defaults(),
		)
	);
}
add_action( 'admin_init', 'greenlight_register_seo_settings' );

/**
 * Adds the SEO settings page under Appearance.
 *
 * @return void
 */
function greenlight_add_seo_settings_page() {
	add_theme_page(
		__( 'Greenlight SEO', 'greenlight' ),
		__( 'SEO', 'greenlight' ),
		'edit_theme_options',
		'greenlight-seo',
		'greenlight_render_seo_settings_page'
	);
}
add_action( 'admin_menu', 'greenlight_add_seo_settings_page' );

/**
 * Renders the SEO settings page.
 *
 * @return void
 */
function greenlight_render_seo_settings_page() {
	$options = greenlight_get_seo_options();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Greenlight SEO', 'greenlight' ); ?></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'greenlight_seo' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="greenlight-site-title"><?php esc_html_e( 'Titre du site pour les SERP', 'greenlight' ); ?></label>
					</th>
					<td>
						<input id="greenlight-site-title" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_title]" type="text" class="regular-text" value="<?php echo esc_attr( $options['site_title'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="greenlight-site-description"><?php esc_html_e( 'Description globale', 'greenlight' ); ?></label>
					</th>
					<td>
						<textarea id="greenlight-site-description" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_description]" class="large-text" rows="4"><?php echo esc_textarea( $options['site_description'] ); ?></textarea>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="greenlight-title-separator"><?php esc_html_e( 'Separateur de titre', 'greenlight' ); ?></label>
					</th>
					<td>
						<input id="greenlight-title-separator" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[title_separator]" type="text" class="small-text" value="<?php echo esc_attr( $options['title_separator'] ); ?>">
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Sitemap XML', 'greenlight' ); ?></th>
					<td>
						<label for="greenlight-enable-sitemap">
							<input id="greenlight-enable-sitemap" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[enable_sitemap]" type="checkbox" value="1" <?php checked( (int) $options['enable_sitemap'], 1 ); ?>>
							<?php esc_html_e( 'Activer le sitemap natif.', 'greenlight' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Archives auteur', 'greenlight' ); ?></th>
					<td>
						<label for="greenlight-noindex-author">
							<input id="greenlight-noindex-author" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_author_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_author_archives'], 1 ); ?>>
							<?php esc_html_e( 'Noindexer les archives auteur.', 'greenlight' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Archives de tags', 'greenlight' ); ?></th>
					<td>
						<label for="greenlight-noindex-tags">
							<input id="greenlight-noindex-tags" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_tag_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_tag_archives'], 1 ); ?>>
							<?php esc_html_e( 'Noindexer les archives de tags.', 'greenlight' ); ?>
						</label>
					</td>
				</tr>
			</table>

			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
