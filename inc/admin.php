<?php
/**
 * Unified Greenlight admin page with CSS-only tabs.
 *
 * Tabs: SEO · Images · Performance · Apparence · SVG
 * SEO and Images reuse the same option_group as inc/seo-settings.php
 * and inc/images-settings.php for zero data duplication.
 *
 * @package Greenlight
 */

define( 'GREENLIGHT_PERF_OPTION_KEY',       'greenlight_performance_options' );
define( 'GREENLIGHT_APPEARANCE_OPTION_KEY', 'greenlight_appearance_options' );
define( 'GREENLIGHT_SVG_OPTION_KEY',        'greenlight_svg_options' );

/* =========================================================
 * Menu registration
 * ======================================================= */

/**
 * Registers the top-level Greenlight admin menu.
 *
 * @return void
 */
function greenlight_add_admin_menu() {
	// Leaf SVG icon — base64 encoded, colour matches WP admin sidebar.
	$svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">';
	$svg .= '<path fill="#a7aaad" d="M10 2c-1.8 3.2-1.2 6 0 8-2-1.2-5-1-6.5 1.5';
	$svg .= ' 2.2 4.5 5.5 6.5 9.5 5.5-1-2.2-.8-4.5 0-6.5-1.2 1-2.8 1.5-4.5 1';
	$svg .= ' 1.8-2 3.5-5.5 1.5-9.5z"/></svg>';
	$icon = 'data:image/svg+xml;base64,' . base64_encode( $svg );

	add_menu_page(
		__( 'Greenlight', 'greenlight' ),
		__( 'Greenlight', 'greenlight' ),
		'manage_options',
		'greenlight',
		'greenlight_render_admin_page',
		$icon,
		65
	);
}
add_action( 'admin_menu', 'greenlight_add_admin_menu' );

/* =========================================================
 * Enqueue color picker (Appearance tab only)
 * ======================================================= */

/**
 * Enqueues wp-color-picker on the Greenlight admin page.
 *
 * @param string $hook_suffix Current admin page hook suffix.
 * @return void
 */
function greenlight_admin_enqueue( $hook_suffix ) {
	if ( 'toplevel_page_greenlight' !== $hook_suffix ) {
		return;
	}

	// phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script( 'wp-color-picker' );
	// phpcs:enable

	wp_add_inline_script(
		'wp-color-picker',
		'jQuery(function($){ $(".greenlight-color-picker").wpColorPicker(); });'
	);
}
add_action( 'admin_enqueue_scripts', 'greenlight_admin_enqueue' );

/* =========================================================
 * Performance settings
 * ======================================================= */

/**
 * Returns default performance options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_performance_defaults() {
	return array(
		'enable_css_min'    => 0,
		'enable_js_min'     => 0,
		'enable_page_cache' => 0,
		'cache_lifetime'    => 3600,
	);
}

/**
 * Sanitizes performance settings.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function greenlight_sanitize_performance_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = greenlight_get_performance_defaults();

	$allowed_lifetimes = array( 3600, 21600, 43200, 86400, 604800 );
	$lifetime          = isset( $input['cache_lifetime'] ) ? (int) $input['cache_lifetime'] : $defaults['cache_lifetime'];

	return array(
		'enable_css_min'    => isset( $input['enable_css_min'] ) ? 1 : 0,
		'enable_js_min'     => isset( $input['enable_js_min'] ) ? 1 : 0,
		'enable_page_cache' => isset( $input['enable_page_cache'] ) ? 1 : 0,
		'cache_lifetime'    => in_array( $lifetime, $allowed_lifetimes, true ) ? $lifetime : $defaults['cache_lifetime'],
	);
}

/**
 * Registers performance settings.
 *
 * @return void
 */
function greenlight_register_performance_settings() {
	register_setting( 'greenlight_performance', GREENLIGHT_PERF_OPTION_KEY, array(
		'type'              => 'array',
		'sanitize_callback' => 'greenlight_sanitize_performance_settings',
		'default'           => greenlight_get_performance_defaults(),
	) );
}
add_action( 'admin_init', 'greenlight_register_performance_settings' );

/* =========================================================
 * Appearance settings
 * ======================================================= */

/**
 * Returns default appearance options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_appearance_defaults() {
	return array(
		'carbon_badge_enabled' => 1,
		'carbon_badge_value'   => '',
		'newsletter_enabled'   => 1,
		'archive_layout'       => 'asymmetric',
		'hero_style'           => 'asymmetric',
		'color_primary'        => '',
		'color_surface'        => '',
		'color_text'           => '',
		'color_header_bg'      => '',
		'color_footer_bg'      => '',
	);
}

/**
 * Sanitizes appearance settings and syncs badge value to legacy option.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function greenlight_sanitize_appearance_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$defaults = greenlight_get_appearance_defaults();

	$badge_value = isset( $input['carbon_badge_value'] )
		? sanitize_text_field( $input['carbon_badge_value'] )
		: '';

	// Keep legacy standalone option in sync for backward compatibility.
	update_option( 'greenlight_carbon_badge_value', $badge_value );

	return array(
		'carbon_badge_enabled' => isset( $input['carbon_badge_enabled'] ) ? 1 : 0,
		'carbon_badge_value'   => $badge_value,
		'newsletter_enabled'   => isset( $input['newsletter_enabled'] ) ? 1 : 0,
		'archive_layout'       => in_array( $input['archive_layout'] ?? '', array( 'asymmetric', 'list' ), true )
			? sanitize_key( $input['archive_layout'] )
			: $defaults['archive_layout'],
		'hero_style'           => in_array( $input['hero_style'] ?? '', array( 'asymmetric', 'centered' ), true )
			? sanitize_key( $input['hero_style'] )
			: $defaults['hero_style'],
		'color_primary'        => ! empty( $input['color_primary'] ) ? sanitize_hex_color( $input['color_primary'] ) : '',
		'color_surface'        => ! empty( $input['color_surface'] ) ? sanitize_hex_color( $input['color_surface'] ) : '',
		'color_text'           => ! empty( $input['color_text'] ) ? sanitize_hex_color( $input['color_text'] ) : '',
		'color_header_bg'      => ! empty( $input['color_header_bg'] ) ? sanitize_hex_color( $input['color_header_bg'] ) : '',
		'color_footer_bg'      => ! empty( $input['color_footer_bg'] ) ? sanitize_hex_color( $input['color_footer_bg'] ) : '',
	);
}

/**
 * Registers appearance settings.
 *
 * @return void
 */
function greenlight_register_appearance_settings() {
	register_setting( 'greenlight_appearance', GREENLIGHT_APPEARANCE_OPTION_KEY, array(
		'type'              => 'array',
		'sanitize_callback' => 'greenlight_sanitize_appearance_settings',
		'default'           => greenlight_get_appearance_defaults(),
	) );
}
add_action( 'admin_init', 'greenlight_register_appearance_settings' );

/* =========================================================
 * SVG settings
 * ======================================================= */

/**
 * Sanitizes SVG settings.
 *
 * @param mixed $input Raw input.
 * @return array<string, int>
 */
function greenlight_sanitize_svg_settings( $input ) {
	$input = is_array( $input ) ? $input : array();
	return array( 'enable_svg' => isset( $input['enable_svg'] ) ? 1 : 0 );
}

/**
 * Registers SVG settings.
 *
 * @return void
 */
function greenlight_register_svg_settings() {
	register_setting( 'greenlight_svg', GREENLIGHT_SVG_OPTION_KEY, array(
		'type'              => 'array',
		'sanitize_callback' => 'greenlight_sanitize_svg_settings',
		'default'           => array( 'enable_svg' => 0 ),
	) );
}
add_action( 'admin_init', 'greenlight_register_svg_settings' );

/* =========================================================
 * Custom colour output on the front end
 * ======================================================= */

/**
 * Outputs CSS variable overrides when custom colours are configured.
 *
 * @return void
 */
function greenlight_output_custom_colors() {
	$opts = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, array() );
	if ( empty( $opts ) ) {
		return;
	}

	$map = array(
		'color_primary'   => '--wp--preset--color--primary',
		'color_surface'   => '--wp--preset--color--surface',
		'color_text'      => '--wp--preset--color--text',
		'color_header_bg' => '--greenlight-header-bg',
		'color_footer_bg' => '--greenlight-footer-bg',
	);

	$css = '';
	foreach ( $map as $key => $var ) {
		if ( ! empty( $opts[ $key ] ) ) {
			$color = sanitize_hex_color( $opts[ $key ] );
			if ( $color ) {
				$css .= $var . ':' . $color . ';';
			}
		}
	}

	if ( $css ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<style id="greenlight-custom-colors">:root{' . $css . '}</style>' . "\n";
	}
}
add_action( 'wp_head', 'greenlight_output_custom_colors', 999 );

/* =========================================================
 * Cache helpers
 * ======================================================= */

/**
 * Returns basic stats for the HTML page cache directory.
 *
 * @return array{count: int, size: int}
 */
function greenlight_get_cache_stats() {
	$cache_dir = WP_CONTENT_DIR . '/cache/greenlight/';
	$count     = 0;
	$size      = 0;

	if ( is_dir( $cache_dir ) ) {
		$files = glob( $cache_dir . '*.html' );
		if ( $files ) {
			$count = count( $files );
			foreach ( $files as $file ) {
				$size += (int) filesize( $file );
			}
		}
	}

	return array( 'count' => $count, 'size' => $size );
}

/**
 * Handles the cache purge POST action.
 *
 * @return void
 */
function greenlight_handle_purge_cache() {
	check_admin_referer( 'greenlight_purge_cache' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	if ( function_exists( 'greenlight_purge_page_cache' ) ) {
		greenlight_purge_page_cache();
	} else {
		$cache_dir = WP_CONTENT_DIR . '/cache/greenlight/';
		if ( is_dir( $cache_dir ) ) {
			$files = glob( $cache_dir . '*.html' ) ?: array();
			array_map( 'unlink', $files );
		}
	}

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=performance&purged=1' ) );
	exit;
}
add_action( 'admin_post_greenlight_purge_cache', 'greenlight_handle_purge_cache' );

/* =========================================================
 * Main page render
 * ======================================================= */

/**
 * Renders the main Greenlight admin page with tab navigation.
 *
 * @return void
 */
function greenlight_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$tabs = array(
		'seo'         => __( 'SEO', 'greenlight' ),
		'images'      => __( 'Images', 'greenlight' ),
		'performance' => __( 'Performance', 'greenlight' ),
		'appearance'  => __( 'Apparence', 'greenlight' ),
		'svg'         => __( 'SVG', 'greenlight' ),
	);

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'seo';
	if ( ! array_key_exists( $current_tab, $tabs ) ) {
		$current_tab = 'seo';
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Greenlight', 'greenlight' ); ?></h1>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=greenlight&tab=' . $slug ) ); ?>"
				   class="nav-tab<?php echo $slug === $current_tab ? ' nav-tab-active' : ''; ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</h2>

		<div class="greenlight-tab-content" style="max-width:800px;margin-top:1.5rem">
			<?php
			switch ( $current_tab ) {
				case 'seo':
					greenlight_render_admin_tab_seo();
					break;
				case 'images':
					greenlight_render_admin_tab_images();
					break;
				case 'performance':
					greenlight_render_admin_tab_performance();
					break;
				case 'appearance':
					greenlight_render_admin_tab_appearance();
					break;
				case 'svg':
					greenlight_render_admin_tab_svg();
					break;
			}
			?>
		</div>
	</div>
	<?php
}

/* =========================================================
 * Tab renders
 * ======================================================= */

/**
 * Renders the SEO tab — same option_group as inc/seo-settings.php.
 *
 * @return void
 */
function greenlight_render_admin_tab_seo() {
	$options = greenlight_get_seo_options();
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_seo' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label for="gl-site-title"><?php esc_html_e( 'Titre du site pour les SERP', 'greenlight' ); ?></label>
				</th>
				<td>
					<input id="gl-site-title" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_title]" type="text" class="regular-text" value="<?php echo esc_attr( $options['site_title'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="gl-site-desc"><?php esc_html_e( 'Description globale', 'greenlight' ); ?></label>
				</th>
				<td>
					<textarea id="gl-site-desc" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_description]" class="large-text" rows="4"><?php echo esc_textarea( $options['site_description'] ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="gl-title-sep"><?php esc_html_e( 'Séparateur de titre', 'greenlight' ); ?></label>
				</th>
				<td>
					<input id="gl-title-sep" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[title_separator]" type="text" class="small-text" value="<?php echo esc_attr( $options['title_separator'] ); ?>">
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sitemap XML', 'greenlight' ); ?></th>
				<td>
					<label for="gl-sitemap">
						<input id="gl-sitemap" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[enable_sitemap]" type="checkbox" value="1" <?php checked( (int) $options['enable_sitemap'], 1 ); ?>>
						<?php esc_html_e( 'Activer le sitemap natif.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Archives auteur', 'greenlight' ); ?></th>
				<td>
					<label for="gl-noindex-author">
						<input id="gl-noindex-author" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_author_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_author_archives'], 1 ); ?>>
						<?php esc_html_e( 'Noindexer les archives auteur.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Archives de tags', 'greenlight' ); ?></th>
				<td>
					<label for="gl-noindex-tags">
						<input id="gl-noindex-tags" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_tag_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_tag_archives'], 1 ); ?>>
						<?php esc_html_e( 'Noindexer les archives de tags.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}

/**
 * Renders the Images tab — same option_group as inc/images-settings.php.
 *
 * @return void
 */
function greenlight_render_admin_tab_images() {
	$options = greenlight_get_images_options();
	$report  = greenlight_get_image_storage_report();

	if ( ! greenlight_is_webp_conversion_enabled() ) {
		echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'La conversion WebP n\'est pas disponible sur ce serveur.', 'greenlight' ) . '</p></div>';
	}
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_images' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Conversion WebP', 'greenlight' ); ?></th>
				<td>
					<label for="gl-enable-webp">
						<input id="gl-enable-webp" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[enable_webp_conversion]" type="checkbox" value="1" <?php checked( (int) $options['enable_webp_conversion'], 1 ); ?>>
						<?php esc_html_e( 'Générer un fichier WebP à l\'upload.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="gl-webp-quality"><?php esc_html_e( 'Qualité WebP', 'greenlight' ); ?></label>
				</th>
				<td>
					<input id="gl-webp-quality" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[webp_quality]" type="range" min="1" max="100" value="<?php echo esc_attr( (int) $options['webp_quality'] ); ?>">
					<span><?php echo esc_html( (int) $options['webp_quality'] ); ?>/100</span>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Tailles core', 'greenlight' ); ?></th>
				<td>
					<label for="gl-remove-sizes">
						<input id="gl-remove-sizes" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[remove_core_sizes]" type="checkbox" value="1" <?php checked( (int) $options['remove_core_sizes'], 1 ); ?>>
						<?php esc_html_e( 'Supprimer medium_large, 1536×1536 et 2048×2048.', 'greenlight' ); ?>
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
			/* translators: 1: count 2: saved bytes */
			esc_html__( '%1$s image(s) WebP détectée(s), environ %2$s économisés.', 'greenlight' ),
			esc_html( number_format_i18n( (int) $report['count'] ) ),
			esc_html( greenlight_format_image_bytes( (int) $report['saved'] ) )
		);
		?>
	</p>
	<p class="description">
		<?php
		printf(
			/* translators: 1: original size 2: webp size */
			esc_html__( 'Stockage original : %1$s. Stockage WebP : %2$s.', 'greenlight' ),
			esc_html( greenlight_format_image_bytes( (int) $report['original'] ) ),
			esc_html( greenlight_format_image_bytes( (int) $report['webp'] ) )
		);
		?>
	</p>
	<?php
}

/**
 * Renders the Performance tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_performance() {
	$options = get_option( GREENLIGHT_PERF_OPTION_KEY, greenlight_get_performance_defaults() );
	$stats   = greenlight_get_cache_stats();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['purged'] ) && '1' === $_GET['purged'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache purgé avec succès.', 'greenlight' ) . '</p></div>';
	}
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_performance' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Minification CSS', 'greenlight' ); ?></th>
				<td>
					<label for="gl-css-min">
						<input id="gl-css-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_css_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_css_min'], 1 ); ?>>
						<?php esc_html_e( 'Charger les fichiers .min.css lorsqu\'ils existent.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Minification JS', 'greenlight' ); ?></th>
				<td>
					<label for="gl-js-min">
						<input id="gl-js-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_js_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_js_min'], 1 ); ?>>
						<?php esc_html_e( 'Charger les fichiers .min.js lorsqu\'ils existent.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cache page HTML', 'greenlight' ); ?></th>
				<td>
					<label for="gl-page-cache">
						<input id="gl-page-cache" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_page_cache]" type="checkbox" value="1" <?php checked( (int) $options['enable_page_cache'], 1 ); ?>>
						<?php esc_html_e( 'Activer le cache statique HTML (nécessite inc/cache.php).', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="gl-lifetime"><?php esc_html_e( 'Durée de vie du cache', 'greenlight' ); ?></label>
				</th>
				<td>
					<select id="gl-lifetime" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[cache_lifetime]">
						<?php
						$lifetimes = array(
							3600   => __( '1 heure', 'greenlight' ),
							21600  => __( '6 heures', 'greenlight' ),
							43200  => __( '12 heures', 'greenlight' ),
							86400  => __( '24 heures', 'greenlight' ),
							604800 => __( '1 semaine', 'greenlight' ),
						);
						foreach ( $lifetimes as $value => $label ) :
							?>
							<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $options['cache_lifetime'], $value ); ?>><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>

	<h2><?php esc_html_e( 'Statut du cache', 'greenlight' ); ?></h2>
	<p>
		<?php
		printf(
			/* translators: 1: page count 2: total size */
			esc_html__( '%1$s page(s) en cache · %2$s', 'greenlight' ),
			esc_html( number_format_i18n( $stats['count'] ) ),
			esc_html( size_format( $stats['size'], 2 ) )
		);
		?>
	</p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="greenlight_purge_cache">
		<?php wp_nonce_field( 'greenlight_purge_cache' ); ?>
		<p><button type="submit" class="button button-secondary"><?php esc_html_e( 'Purger le cache', 'greenlight' ); ?></button></p>
	</form>
	<?php
}

/**
 * Renders the Appearance tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_appearance() {
	$options = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, greenlight_get_appearance_defaults() );
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_appearance' ); ?>
		<table class="form-table" role="presentation">

			<tr>
				<th scope="row"><?php esc_html_e( 'Carbon Badge', 'greenlight' ); ?></th>
				<td>
					<label for="gl-badge-on">
						<input id="gl-badge-on" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[carbon_badge_enabled]" type="checkbox" value="1" <?php checked( (int) $options['carbon_badge_enabled'], 1 ); ?>>
						<?php esc_html_e( 'Afficher le badge CO₂.', 'greenlight' ); ?>
					</label>
					<br>
					<label for="gl-badge-val" style="margin-top:0.5em;display:block">
						<?php esc_html_e( 'Valeur manuelle (ex. 0.15g) :', 'greenlight' ); ?>
						<input id="gl-badge-val" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[carbon_badge_value]" type="text" class="small-text" value="<?php echo esc_attr( $options['carbon_badge_value'] ); ?>" placeholder="0.2g">
					</label>
					<p class="description"><?php esc_html_e( 'Laisser vide pour la valeur estimée par défaut (0.2g).', 'greenlight' ); ?></p>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Newsletter CTA', 'greenlight' ); ?></th>
				<td>
					<label for="gl-newsletter">
						<input id="gl-newsletter" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[newsletter_enabled]" type="checkbox" value="1" <?php checked( (int) $options['newsletter_enabled'], 1 ); ?>>
						<?php esc_html_e( 'Afficher la section newsletter dans le footer et les articles.', 'greenlight' ); ?>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Layout archive', 'greenlight' ); ?></th>
				<td>
					<label><input type="radio" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[archive_layout]" value="asymmetric" <?php checked( $options['archive_layout'], 'asymmetric' ); ?>> <?php esc_html_e( 'Grille asymétrique', 'greenlight' ); ?></label><br>
					<label><input type="radio" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[archive_layout]" value="list" <?php checked( $options['archive_layout'], 'list' ); ?>> <?php esc_html_e( 'Liste simple', 'greenlight' ); ?></label>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label for="gl-hero-style"><?php esc_html_e( 'Style hero', 'greenlight' ); ?></label>
				</th>
				<td>
					<select id="gl-hero-style" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[hero_style]">
						<option value="asymmetric" <?php selected( $options['hero_style'], 'asymmetric' ); ?>><?php esc_html_e( 'Asymétrique (titre gauche, texte droite)', 'greenlight' ); ?></option>
						<option value="centered" <?php selected( $options['hero_style'], 'centered' ); ?>><?php esc_html_e( 'Centré', 'greenlight' ); ?></option>
					</select>
				</td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Couleur primaire', 'greenlight' ); ?></th>
				<td><input type="text" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[color_primary]" class="greenlight-color-picker" value="<?php echo esc_attr( $options['color_primary'] ); ?>" data-default-color="#4c6547"></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Couleur surface', 'greenlight' ); ?></th>
				<td><input type="text" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[color_surface]" class="greenlight-color-picker" value="<?php echo esc_attr( $options['color_surface'] ); ?>" data-default-color="#f4f4ee"></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Couleur texte', 'greenlight' ); ?></th>
				<td><input type="text" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[color_text]" class="greenlight-color-picker" value="<?php echo esc_attr( $options['color_text'] ); ?>" data-default-color="#2f342d"></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Fond header', 'greenlight' ); ?></th>
				<td><input type="text" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[color_header_bg]" class="greenlight-color-picker" value="<?php echo esc_attr( $options['color_header_bg'] ); ?>" data-default-color="#faf9f4"></td>
			</tr>

			<tr>
				<th scope="row"><?php esc_html_e( 'Fond footer', 'greenlight' ); ?></th>
				<td><input type="text" name="<?php echo esc_attr( GREENLIGHT_APPEARANCE_OPTION_KEY ); ?>[color_footer_bg]" class="greenlight-color-picker" value="<?php echo esc_attr( $options['color_footer_bg'] ); ?>" data-default-color="#f4f4ee"></td>
			</tr>

		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}

/**
 * Renders the SVG tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_svg() {
	$options = get_option( GREENLIGHT_SVG_OPTION_KEY, array( 'enable_svg' => 0 ) );
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_svg' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Upload SVG', 'greenlight' ); ?></th>
				<td>
					<label for="gl-svg">
						<input id="gl-svg" name="<?php echo esc_attr( GREENLIGHT_SVG_OPTION_KEY ); ?>[enable_svg]" type="checkbox" value="1" <?php checked( (int) $options['enable_svg'], 1 ); ?>>
						<?php esc_html_e( 'Autoriser l\'upload de fichiers SVG.', 'greenlight' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Les SVG uploadés sont sanitisés via DOMDocument (suppression des scripts inline, des gestionnaires d\'événements JS et des xlink malveillants).', 'greenlight' ); ?>
					</p>
				</td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	<?php
}
