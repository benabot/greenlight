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

	// Admin preview JS — uniquement sur l'onglet Apparence.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$current_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'seo';
	if ( 'appearance' === $current_tab ) {
		$preview_path = get_theme_file_path( 'assets/js/admin-preview.js' );
		if ( file_exists( $preview_path ) ) {
			wp_enqueue_script(
				'greenlight-admin-preview',
				get_theme_file_uri( 'assets/js/admin-preview.js' ),
				array(),
				filemtime( $preview_path ),
				true
			);
		}
	}
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
		// Global
		'carbon_badge_enabled'     => 1,
		'carbon_badge_value'       => '',
		'newsletter_enabled'       => 1,
		// Couleurs
		'color_primary'            => '',
		'color_surface'            => '',
		'color_text'               => '',
		'color_background'         => '',
		'color_tertiary'           => '',
		'color_border'             => '',
		'color_on_surface_variant' => '',
		// Header
		'color_header_bg'          => '',
		'show_tagline'             => 0,
		// Hero
		'hero_style'               => 'asymmetric',
		'show_hero_badge'          => 1,
		'hero_text'                => '',
		// Single
		'show_date'                => 1,
		'show_author'              => 1,
		'show_tags'                => 1,
		'show_newsletter_single'   => 1,
		// Archive
		'archive_layout'           => 'asymmetric',
		'show_excerpts_archive'    => 1,
		'show_thumbnails_archive'  => 1,
		// Footer
		'color_footer_bg'          => '',
		'show_low_emission'        => 1,
		'custom_copyright'         => '',
		'show_footer_nav'          => 1,
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

	$sanitize_color = static function ( $val ) {
		return ! empty( $val ) ? ( sanitize_hex_color( $val ) ?: '' ) : '';
	};

	return array(
		// Global
		'carbon_badge_enabled'     => isset( $input['carbon_badge_enabled'] ) ? 1 : 0,
		'carbon_badge_value'       => $badge_value,
		'newsletter_enabled'       => isset( $input['newsletter_enabled'] ) ? 1 : 0,
		// Couleurs
		'color_primary'            => $sanitize_color( $input['color_primary'] ?? '' ),
		'color_surface'            => $sanitize_color( $input['color_surface'] ?? '' ),
		'color_text'               => $sanitize_color( $input['color_text'] ?? '' ),
		'color_background'         => $sanitize_color( $input['color_background'] ?? '' ),
		'color_tertiary'           => $sanitize_color( $input['color_tertiary'] ?? '' ),
		'color_border'             => $sanitize_color( $input['color_border'] ?? '' ),
		'color_on_surface_variant' => $sanitize_color( $input['color_on_surface_variant'] ?? '' ),
		// Header
		'color_header_bg'          => $sanitize_color( $input['color_header_bg'] ?? '' ),
		'show_tagline'             => isset( $input['show_tagline'] ) ? 1 : 0,
		// Hero
		'hero_style'               => in_array( $input['hero_style'] ?? '', array( 'asymmetric', 'centered' ), true )
			? sanitize_key( $input['hero_style'] )
			: $defaults['hero_style'],
		'show_hero_badge'          => isset( $input['show_hero_badge'] ) ? 1 : 0,
		'hero_text'                => isset( $input['hero_text'] ) ? sanitize_textarea_field( $input['hero_text'] ) : '',
		// Single
		'show_date'                => isset( $input['show_date'] ) ? 1 : 0,
		'show_author'              => isset( $input['show_author'] ) ? 1 : 0,
		'show_tags'                => isset( $input['show_tags'] ) ? 1 : 0,
		'show_newsletter_single'   => isset( $input['show_newsletter_single'] ) ? 1 : 0,
		// Archive
		'archive_layout'           => in_array( $input['archive_layout'] ?? '', array( 'asymmetric', 'list' ), true )
			? sanitize_key( $input['archive_layout'] )
			: $defaults['archive_layout'],
		'show_excerpts_archive'    => isset( $input['show_excerpts_archive'] ) ? 1 : 0,
		'show_thumbnails_archive'  => isset( $input['show_thumbnails_archive'] ) ? 1 : 0,
		// Footer
		'color_footer_bg'          => $sanitize_color( $input['color_footer_bg'] ?? '' ),
		'show_low_emission'        => isset( $input['show_low_emission'] ) ? 1 : 0,
		'custom_copyright'         => isset( $input['custom_copyright'] ) ? sanitize_text_field( $input['custom_copyright'] ) : '',
		'show_footer_nav'          => isset( $input['show_footer_nav'] ) ? 1 : 0,
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
		'color_primary'            => '--wp--preset--color--primary',
		'color_surface'            => '--wp--preset--color--surface',
		'color_text'               => '--wp--preset--color--text',
		'color_background'         => '--wp--preset--color--background',
		'color_tertiary'           => '--wp--preset--color--tertiary',
		'color_border'             => '--wp--preset--color--border',
		'color_on_surface_variant' => '--wp--preset--color--on-surface-variant',
		'color_header_bg'          => '--greenlight-header-bg',
		'color_footer_bg'          => '--greenlight-footer-bg',
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
		'tools'       => __( 'Outils', 'greenlight' ),
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
				case 'tools':
					greenlight_render_admin_tab_tools();
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
	$options    = get_option( GREENLIGHT_PERF_OPTION_KEY, greenlight_get_performance_defaults() );
	$stats      = greenlight_get_cache_stats();
	$theme_dir  = get_stylesheet_directory();

	// Notifications GET.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['purged'] ) && '1' === $_GET['purged'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache purgé avec succès.', 'greenlight' ) . '</p></div>';
	}
	if ( isset( $_GET['regen'] ) && '1' === $_GET['regen'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Fichiers minifiés supprimés — ils seront régénérés au prochain chargement.', 'greenlight' ) . '</p></div>';
	}
	// phpcs:enable
	?>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_performance' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><?php esc_html_e( 'Minification CSS', 'greenlight' ); ?></th>
				<td>
					<label for="gl-css-min">
						<input id="gl-css-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_css_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_css_min'], 1 ); ?>>
						<?php esc_html_e( 'Charger les fichiers .min.css (générés automatiquement si absents).', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Minification JS', 'greenlight' ); ?></th>
				<td>
					<label for="gl-js-min">
						<input id="gl-js-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_js_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_js_min'], 1 ); ?>>
						<?php esc_html_e( 'Charger les fichiers .min.js (générés automatiquement si absents).', 'greenlight' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Cache page HTML', 'greenlight' ); ?></th>
				<td>
					<label for="gl-page-cache">
						<input id="gl-page-cache" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_page_cache]" type="checkbox" value="1" <?php checked( (int) $options['enable_page_cache'], 1 ); ?>>
						<?php esc_html_e( 'Activer le cache statique HTML.', 'greenlight' ); ?>
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

	<h2><?php esc_html_e( 'Statut de la minification', 'greenlight' ); ?></h2>
	<?php
	$min_files = array(
		'style.min.css'                          => $theme_dir . '/style.min.css',
		'assets/js/seo-sidebar.min.js'           => $theme_dir . '/assets/js/seo-sidebar.min.js',
	);
	$block_min_dir = $theme_dir . '/assets/css/blocks/';
	foreach ( array( 'navigation', 'image', 'heading', 'paragraph', 'separator', 'button', 'group', 'query' ) as $b ) {
		$min_files[ 'blocks/' . $b . '.min.css' ] = $block_min_dir . $b . '.min.css';
	}
	echo '<table class="widefat striped" style="max-width:600px"><thead><tr><th>' . esc_html__( 'Fichier', 'greenlight' ) . '</th><th>' . esc_html__( 'Statut', 'greenlight' ) . '</th><th>' . esc_html__( 'Taille', 'greenlight' ) . '</th></tr></thead><tbody>';
	foreach ( $min_files as $label => $path ) {
		$exists = file_exists( $path );
		$status = $exists
			? '<span style="color:#46b450">&#10003; ' . esc_html__( 'Présent', 'greenlight' ) . '</span>'
			: '<span style="color:#dc3232">&#10007; ' . esc_html__( 'Absent', 'greenlight' ) . '</span>';
		$size   = $exists ? esc_html( size_format( (int) filesize( $path ), 1 ) ) : '—';
		$mtime  = $exists ? ' <small style="color:#999">(' . esc_html( gmdate( 'd/m H:i', filemtime( $path ) ) ) . ')</small>' : '';
		echo '<tr><td><code>' . esc_html( $label ) . '</code></td><td>' . $status . $mtime . '</td><td>' . $size . '</td></tr>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
	echo '</tbody></table>';
	?>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:1em">
		<input type="hidden" name="action" value="greenlight_regen_min">
		<?php wp_nonce_field( 'greenlight_regen_min' ); ?>
		<button type="submit" class="button button-secondary"><?php esc_html_e( 'Supprimer les fichiers minifiés (régénération au prochain chargement)', 'greenlight' ); ?></button>
	</form>

	<h2><?php esc_html_e( 'Statut du cache HTML', 'greenlight' ); ?></h2>
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
		<button type="submit" class="button button-secondary"><?php esc_html_e( 'Purger le cache HTML', 'greenlight' ); ?></button>
	</form>

	<h2><?php esc_html_e( 'Environnement serveur', 'greenlight' ); ?></h2>
	<?php
	$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	if ( stripos( $server_software, 'nginx' ) !== false ) {
		echo '<p>' . esc_html__( 'Serveur nginx détecté — activez gzip/brotli dans nginx.conf pour compresser les assets.', 'greenlight' ) . '</p>';
		echo '<code>gzip on; gzip_types text/css application/javascript;</code>';
	} elseif ( stripos( $server_software, 'apache' ) !== false ) {
		echo '<p>' . esc_html__( 'Serveur Apache détecté — ajoutez mod_deflate et mod_expires dans votre .htaccess.', 'greenlight' ) . '</p>';
	} else {
		echo '<p>' . esc_html( sprintf(
			/* translators: %s: server software string */
			__( 'Serveur : %s', 'greenlight' ),
			$server_software ?: __( 'inconnu', 'greenlight' )
		) ) . '</p>';
	}
	?>
	<?php
}

/**
 * Renders the Appearance tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_appearance() {
	$o   = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, array() );
	$def = greenlight_get_appearance_defaults();
	$o   = array_merge( $def, $o );
	$key = GREENLIGHT_APPEARANCE_OPTION_KEY;
	?>
	<style>
		.gl-details { border:1px solid #dcdcde; border-radius:4px; margin-bottom:1rem }
		.gl-details summary { padding:.75rem 1rem; font-weight:600; cursor:pointer; background:#f9f9f9; border-radius:4px }
		.gl-details[open] summary { border-bottom:1px solid #dcdcde }
		.gl-details .form-table { margin:0; padding:.5rem 0 }
	</style>
	<form method="post" action="options.php">
		<?php settings_fields( 'greenlight_appearance' ); ?>

		<!-- ── Global ───────────────────────────────────── -->
		<details class="gl-details" open>
			<summary><?php esc_html_e( 'Global', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><?php esc_html_e( 'Carbon Badge', 'greenlight' ); ?></th>
					<td>
						<label><input name="<?php echo esc_attr( $key ); ?>[carbon_badge_enabled]" type="checkbox" value="1" <?php checked( (int) $o['carbon_badge_enabled'], 1 ); ?>> <?php esc_html_e( 'Afficher le badge CO₂', 'greenlight' ); ?></label><br>
						<label style="margin-top:.4em;display:block"><?php esc_html_e( 'Valeur manuelle :', 'greenlight' ); ?>
							<input name="<?php echo esc_attr( $key ); ?>[carbon_badge_value]" type="text" class="small-text" value="<?php echo esc_attr( $o['carbon_badge_value'] ); ?>" placeholder="0.2g">
						</label>
						<p class="description"><?php esc_html_e( 'Laisser vide pour 0.2g (valeur par défaut).', 'greenlight' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Newsletter CTA', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[newsletter_enabled]" type="checkbox" value="1" <?php checked( (int) $o['newsletter_enabled'], 1 ); ?>> <?php esc_html_e( 'Afficher la section newsletter', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Couleur primaire', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_primary]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_primary'] ); ?>" data-default-color="#4c6547"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Fond de page', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_background]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_background'] ); ?>" data-default-color="#faf9f4"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Surface', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_surface]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_surface'] ); ?>" data-default-color="#f4f4ee"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Texte', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_text]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_text'] ); ?>" data-default-color="#2f342d"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Tertiary (badge)', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_tertiary]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_tertiary'] ); ?>" data-default-color="#e5f4c9"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Bordure', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_border]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_border'] ); ?>" data-default-color="#afb3aa"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Texte secondaire', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_on_surface_variant]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_on_surface_variant'] ); ?>" data-default-color="#5c6058"></td>
				</tr>
			</table>
		</details>

		<!-- ── Header ───────────────────────────────────── -->
		<details class="gl-details">
			<summary><?php esc_html_e( 'Header', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Fond header', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_header_bg]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_bg'] ); ?>" data-default-color="#faf9f4"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Tagline', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_tagline]" type="checkbox" value="1" <?php checked( (int) $o['show_tagline'], 1 ); ?>> <?php esc_html_e( 'Afficher la description du site sous le nom', 'greenlight' ); ?></label></td>
				</tr>
			</table>
		</details>

		<!-- ── Hero ─────────────────────────────────────── -->
		<details class="gl-details">
			<summary><?php esc_html_e( 'Hero / Accueil', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th><label for="gl-hero-style"><?php esc_html_e( 'Style hero', 'greenlight' ); ?></label></th>
					<td>
						<select id="gl-hero-style" name="<?php echo esc_attr( $key ); ?>[hero_style]">
							<option value="asymmetric" <?php selected( $o['hero_style'], 'asymmetric' ); ?>><?php esc_html_e( 'Asymétrique', 'greenlight' ); ?></option>
							<option value="centered" <?php selected( $o['hero_style'], 'centered' ); ?>><?php esc_html_e( 'Centré', 'greenlight' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Carbon Badge', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_hero_badge]" type="checkbox" value="1" <?php checked( (int) $o['show_hero_badge'], 1 ); ?>> <?php esc_html_e( 'Afficher le badge CO₂ sur le hero', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="gl-hero-text"><?php esc_html_e( 'Texte hero personnalisé', 'greenlight' ); ?></label></th>
					<td>
						<textarea id="gl-hero-text" name="<?php echo esc_attr( $key ); ?>[hero_text]" class="large-text" rows="3" placeholder="<?php esc_attr_e( 'Laisser vide pour utiliser la description du site.', 'greenlight' ); ?>"><?php echo esc_textarea( $o['hero_text'] ); ?></textarea>
					</td>
				</tr>
			</table>
		</details>

		<!-- ── Single ───────────────────────────────────── -->
		<details class="gl-details">
			<summary><?php esc_html_e( 'Articles (single)', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Date', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_date]" type="checkbox" value="1" <?php checked( (int) $o['show_date'], 1 ); ?>> <?php esc_html_e( 'Afficher la date de publication', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Auteur', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_author]" type="checkbox" value="1" <?php checked( (int) $o['show_author'], 1 ); ?>> <?php esc_html_e( 'Afficher l\'auteur', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Tags', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_tags]" type="checkbox" value="1" <?php checked( (int) $o['show_tags'], 1 ); ?>> <?php esc_html_e( 'Afficher les tags en bas de l\'article', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Newsletter', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_newsletter_single]" type="checkbox" value="1" <?php checked( (int) $o['show_newsletter_single'], 1 ); ?>> <?php esc_html_e( 'Afficher le CTA newsletter en bas de l\'article', 'greenlight' ); ?></label></td>
				</tr>
			</table>
		</details>

		<!-- ── Archive ──────────────────────────────────── -->
		<details class="gl-details">
			<summary><?php esc_html_e( 'Archive / Index', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Layout', 'greenlight' ); ?></th>
					<td>
						<label><input type="radio" name="<?php echo esc_attr( $key ); ?>[archive_layout]" value="asymmetric" <?php checked( $o['archive_layout'], 'asymmetric' ); ?>> <?php esc_html_e( 'Grille asymétrique', 'greenlight' ); ?></label><br>
						<label><input type="radio" name="<?php echo esc_attr( $key ); ?>[archive_layout]" value="list" <?php checked( $o['archive_layout'], 'list' ); ?>> <?php esc_html_e( 'Liste simple', 'greenlight' ); ?></label>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Extraits', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_excerpts_archive]" type="checkbox" value="1" <?php checked( (int) $o['show_excerpts_archive'], 1 ); ?>> <?php esc_html_e( 'Afficher les extraits', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Miniatures', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_thumbnails_archive]" type="checkbox" value="1" <?php checked( (int) $o['show_thumbnails_archive'], 1 ); ?>> <?php esc_html_e( 'Afficher les miniatures', 'greenlight' ); ?></label></td>
				</tr>
			</table>
		</details>

		<!-- ── Footer ───────────────────────────────────── -->
		<details class="gl-details">
			<summary><?php esc_html_e( 'Footer', 'greenlight' ); ?></summary>
			<table class="form-table" role="presentation">
				<tr>
					<th><?php esc_html_e( 'Fond footer', 'greenlight' ); ?></th>
					<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_footer_bg]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_footer_bg'] ); ?>" data-default-color="#f4f4ee"></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Low Emission', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_low_emission]" type="checkbox" value="1" <?php checked( (int) $o['show_low_emission'], 1 ); ?>> <?php esc_html_e( 'Afficher la mention "Low Emission Mode"', 'greenlight' ); ?></label></td>
				</tr>
				<tr>
					<th><label for="gl-copyright"><?php esc_html_e( 'Copyright personnalisé', 'greenlight' ); ?></label></th>
					<td>
						<input id="gl-copyright" name="<?php echo esc_attr( $key ); ?>[custom_copyright]" type="text" class="regular-text" value="<?php echo esc_attr( $o['custom_copyright'] ); ?>" placeholder="<?php esc_attr_e( 'Laisser vide : © {year} {sitename}', 'greenlight' ); ?>">
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Navigation footer', 'greenlight' ); ?></th>
					<td><label><input name="<?php echo esc_attr( $key ); ?>[show_footer_nav]" type="checkbox" value="1" <?php checked( (int) $o['show_footer_nav'], 1 ); ?>> <?php esc_html_e( 'Afficher le menu de navigation footer', 'greenlight' ); ?></label></td>
				</tr>
			</table>
		</details>

		<?php submit_button(); ?>
	</form>

	<h2><?php esc_html_e( 'Prévisualisation', 'greenlight' ); ?></h2>
	<iframe id="greenlight-preview-frame"
	        src="<?php echo esc_url( home_url( '/' ) ); ?>"
	        title="<?php esc_attr_e( 'Prévisualisation du site', 'greenlight' ); ?>"
	        style="width:100%;height:500px;border:1px solid #dcdcde;border-radius:4px;margin-top:.5rem"></iframe>
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

/* =========================================================
 * Onglet Outils — Import / Export
 * ======================================================= */

/**
 * Affiche l'onglet Outils (Import/Export JSON).
 *
 * @return void
 */
function greenlight_render_admin_tab_tools() {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$import_status = isset( $_GET['import'] ) ? sanitize_key( $_GET['import'] ) : '';
	// phpcs:enable
	if ( 'success' === $import_status ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Réglages importés avec succès.', 'greenlight' ) . '</p></div>';
	} elseif ( 'error' === $import_status ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Erreur lors de l\'import — fichier manquant.', 'greenlight' ) . '</p></div>';
	} elseif ( 'invalid' === $import_status ) {
		echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Fichier JSON invalide ou non reconnu.', 'greenlight' ) . '</p></div>';
	}
	?>
	<h2><?php esc_html_e( 'Exporter les réglages', 'greenlight' ); ?></h2>
	<p><?php esc_html_e( 'Télécharge un fichier JSON avec tous les réglages Greenlight (SEO, Images, Performance, Apparence, SVG).', 'greenlight' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="greenlight_export">
		<?php wp_nonce_field( 'greenlight_export' ); ?>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'Exporter (JSON)', 'greenlight' ); ?></button>
	</form>

	<hr>

	<h2><?php esc_html_e( 'Importer des réglages', 'greenlight' ); ?></h2>
	<p><?php esc_html_e( 'Sélectionne un fichier JSON exporté depuis ce thème. Les réglages actuels seront écrasés.', 'greenlight' ); ?></p>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
		<input type="hidden" name="action" value="greenlight_import">
		<?php wp_nonce_field( 'greenlight_import' ); ?>
		<table class="form-table" role="presentation">
			<tr>
				<th scope="row"><label for="gl-import-file"><?php esc_html_e( 'Fichier JSON', 'greenlight' ); ?></label></th>
				<td><input id="gl-import-file" name="greenlight_import_file" type="file" accept=".json"></td>
			</tr>
		</table>
		<?php submit_button( __( 'Importer', 'greenlight' ), 'secondary' ); ?>
	</form>
	<?php
}

/**
 * Gère l'export JSON des réglages Greenlight.
 *
 * @return void
 */
function greenlight_handle_export() {
	check_admin_referer( 'greenlight_export' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$data = array(
		'greenlight_export_version' => 1,
		'exported_at'               => gmdate( 'c' ),
		'seo'                       => get_option( GREENLIGHT_SEO_OPTION_KEY, array() ),
		'images'                    => get_option( GREENLIGHT_IMAGES_OPTION_KEY, array() ),
		'performance'               => get_option( GREENLIGHT_PERF_OPTION_KEY, array() ),
		'appearance'                => get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, array() ),
		'svg'                       => get_option( GREENLIGHT_SVG_OPTION_KEY, array() ),
	);

	$filename = 'greenlight-settings-' . gmdate( 'Y-m-d' ) . '.json';

	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Cache-Control: no-cache, no-store, must-revalidate' );

	echo wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'admin_post_greenlight_export', 'greenlight_handle_export' );

/**
 * Gère l'import JSON des réglages Greenlight.
 *
 * @return void
 */
function greenlight_handle_import() {
	check_admin_referer( 'greenlight_import' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	$redirect_base = admin_url( 'admin.php?page=greenlight&tab=tools' );

	if ( empty( $_FILES['greenlight_import_file']['tmp_name'] ) ) {
		wp_safe_redirect( $redirect_base . '&import=error' );
		exit;
	}

	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$json = file_get_contents( sanitize_text_field( $_FILES['greenlight_import_file']['tmp_name'] ) );
	$data = json_decode( $json, true );

	if ( ! is_array( $data ) || empty( $data['greenlight_export_version'] ) ) {
		wp_safe_redirect( $redirect_base . '&import=invalid' );
		exit;
	}

	$map = array(
		'seo'         => array( GREENLIGHT_SEO_OPTION_KEY, 'greenlight_sanitize_seo_settings' ),
		'images'      => array( GREENLIGHT_IMAGES_OPTION_KEY, 'greenlight_sanitize_images_settings' ),
		'performance' => array( GREENLIGHT_PERF_OPTION_KEY, 'greenlight_sanitize_performance_settings' ),
		'appearance'  => array( GREENLIGHT_APPEARANCE_OPTION_KEY, 'greenlight_sanitize_appearance_settings' ),
		'svg'         => array( GREENLIGHT_SVG_OPTION_KEY, 'greenlight_sanitize_svg_settings' ),
	);

	foreach ( $map as $section => $config ) {
		if ( isset( $data[ $section ] ) && is_array( $data[ $section ] ) && function_exists( $config[1] ) ) {
			update_option( $config[0], call_user_func( $config[1], $data[ $section ] ) );
		}
	}

	wp_safe_redirect( $redirect_base . '&import=success' );
	exit;
}
add_action( 'admin_post_greenlight_import', 'greenlight_handle_import' );

/**
 * Supprime les fichiers .min générés pour forcer la régénération lazy au prochain chargement.
 *
 * @return void
 */
function greenlight_handle_regen_min() {
	check_admin_referer( 'greenlight_regen_min' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Permission refusée.', 'greenlight' ) );
	}

	if ( function_exists( 'greenlight_clear_min_files' ) ) {
		greenlight_clear_min_files();
	}
	if ( function_exists( 'greenlight_clear_minify_transients' ) ) {
		greenlight_clear_minify_transients();
	}

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=performance&regen=1' ) );
	exit;
}
add_action( 'admin_post_greenlight_regen_min', 'greenlight_handle_regen_min' );
