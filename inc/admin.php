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

define( 'GREENLIGHT_PERF_OPTION_KEY', 'greenlight_performance_options' );
define( 'GREENLIGHT_APPEARANCE_OPTION_KEY', 'greenlight_appearance_options' );
define( 'GREENLIGHT_SVG_OPTION_KEY', 'greenlight_svg_options' );

/**
 * Menu registration.
 */

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
	// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode -- Benign SVG icon data URI for the admin menu.
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

/**
 * Enqueue color picker (Appearance tab only).
 */

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

	$admin_css_path = get_theme_file_path( 'assets/css/admin-ui.css' );
	if ( file_exists( $admin_css_path ) ) {
		wp_enqueue_style(
			'greenlight-admin-ui',
			get_theme_file_uri( 'assets/css/admin-ui.css' ),
			array(),
			filemtime( $admin_css_path )
		);
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
			$variant_maps   = array(
				'theme_preset'       => array(),
				'density_scale'      => array(),
				'archive_card_style' => array(),
				'single_layout'      => array(),
				'footer_layout'      => array(),
			);
			$hero_gradients = array();

			foreach ( greenlight_get_appearance_presets() as $preset_key => $preset_data ) {
				$variant_maps['theme_preset'][ $preset_key ] = isset( $preset_data['vars'] ) ? (array) $preset_data['vars'] : array();
			}

			foreach ( greenlight_get_appearance_densities() as $density_key => $density_data ) {
				$variant_maps['density_scale'][ $density_key ] = isset( $density_data['vars'] ) ? (array) $density_data['vars'] : array();
			}

			foreach ( greenlight_get_archive_card_styles() as $archive_key => $archive_data ) {
				$variant_maps['archive_card_style'][ $archive_key ] = isset( $archive_data['vars'] ) ? (array) $archive_data['vars'] : array();
			}

			foreach ( greenlight_get_single_layout_variants() as $single_key => $single_data ) {
				$variant_maps['single_layout'][ $single_key ] = isset( $single_data['vars'] ) ? (array) $single_data['vars'] : array();
			}

			foreach ( greenlight_get_footer_layout_variants() as $footer_key => $footer_data ) {
				$variant_maps['footer_layout'][ $footer_key ] = isset( $footer_data['vars'] ) ? (array) $footer_data['vars'] : array();
			}

			foreach ( greenlight_get_hero_gradient_presets() as $gradient_key => $gradient_data ) {
				$hero_gradients[ $gradient_key ] = isset( $gradient_data['value'] ) ? (string) $gradient_data['value'] : '';
			}

			$preview_data = array(
				'optionKey'         => GREENLIGHT_APPEARANCE_OPTION_KEY,
				'variants'          => $variant_maps,
				'heroGradients'     => $hero_gradients,
				'siteName'          => get_bloginfo( 'name' ),
				'siteTagline'       => get_bloginfo( 'description' ),
				'previewQueryArg'   => 'greenlight_preview',
				'previewQueryValue' => 'appearance',
			);

			wp_enqueue_script(
				'greenlight-admin-preview',
				get_theme_file_uri( 'assets/js/admin-preview.js' ),
				array(),
				filemtime( $preview_path ),
				true
			);

			wp_add_inline_script(
				'greenlight-admin-preview',
				'window.greenlightAdminPreview = ' . wp_json_encode( $preview_data ) . ';',
				'before'
			);
		}
	}
}
add_action( 'admin_enqueue_scripts', 'greenlight_admin_enqueue' );

/**
 * Performance settings.
 */

/**
 * Returns default performance options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_performance_defaults() {
	return array(
		'enable_css_min'      => 0,
		'enable_js_min'       => 0,
		'enable_page_cache'   => 0,
		'cache_lifetime'      => 3600,
		'enable_critical_css' => 0,
		'prefetch_domains'    => '',
		'enable_concat'       => 0,
		'heartbeat_front'     => 'default',
		'heartbeat_admin'     => 'default',
		'heartbeat_editor'    => 'default',
		'enable_auto_cleanup' => 0,
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

	$allowed_heartbeat = array( 'default', 'reduce', 'disable' );
	$hb_front          = isset( $input['heartbeat_front'] ) ? sanitize_key( $input['heartbeat_front'] ) : 'default';
	$hb_admin          = isset( $input['heartbeat_admin'] ) ? sanitize_key( $input['heartbeat_admin'] ) : 'default';
	$hb_editor         = isset( $input['heartbeat_editor'] ) ? sanitize_key( $input['heartbeat_editor'] ) : 'default';

	return array(
		'enable_css_min'      => isset( $input['enable_css_min'] ) ? 1 : 0,
		'enable_js_min'       => isset( $input['enable_js_min'] ) ? 1 : 0,
		'enable_page_cache'   => isset( $input['enable_page_cache'] ) ? 1 : 0,
		'cache_lifetime'      => in_array( $lifetime, $allowed_lifetimes, true ) ? $lifetime : $defaults['cache_lifetime'],
		'enable_critical_css' => isset( $input['enable_critical_css'] ) ? 1 : 0,
		'prefetch_domains'    => isset( $input['prefetch_domains'] ) ? sanitize_textarea_field( $input['prefetch_domains'] ) : '',
		'enable_concat'       => isset( $input['enable_concat'] ) ? 1 : 0,
		'heartbeat_front'     => in_array( $hb_front, $allowed_heartbeat, true ) ? $hb_front : 'default',
		'heartbeat_admin'     => in_array( $hb_admin, $allowed_heartbeat, true ) ? $hb_admin : 'default',
		'heartbeat_editor'    => in_array( $hb_editor, $allowed_heartbeat, true ) ? $hb_editor : 'default',
		'enable_auto_cleanup' => isset( $input['enable_auto_cleanup'] ) ? 1 : 0,
	);
}

/**
 * Registers performance settings.
 *
 * @return void
 */
function greenlight_register_performance_settings() {
	register_setting(
		'greenlight_performance',
		GREENLIGHT_PERF_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'greenlight_sanitize_performance_settings',
			'default'           => greenlight_get_performance_defaults(),
		)
	);
}
add_action( 'admin_init', 'greenlight_register_performance_settings' );

/**
 * Appearance settings.
 */

/**
 * Returns default appearance options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_appearance_defaults() {
	return array(
		'theme_preset'             => 'editorial',
		'density_scale'            => 'balanced',
		// Global.
		'carbon_badge_enabled'     => 1,
		'carbon_badge_value'       => '',
		'carbon_badge_position'    => 'top',
		'newsletter_enabled'       => 1,
		// Couleurs.
		'color_primary'            => '',
		'color_surface'            => '',
		'color_text'               => '',
		'color_background'         => '',
		'color_tertiary'           => '',
		'color_border'             => '',
		'color_on_surface_variant' => '',
		// Header.
		'color_header_bg'          => '',
		'color_header_text'        => '',
		'color_header_accent'      => '',
		'header_layout'            => 'inline',
		'header_sticky'            => 0,
		'nav_link_case'            => 'normal',
		'submenu_style'            => 'plain',
		'show_tagline'             => 0,
		// Hero.
		'hero_enabled'             => 1,
		'hero_style'               => 'asymmetric',
		'hero_background_mode'     => 'none',
		'hero_background_image'    => '',
		'hero_background_color'    => '',
		'hero_gradient_preset'     => 'moss',
		'hero_heading_mode'        => 'page_title',
		'hero_heading_text'        => '',
		'hero_subheading_mode'     => 'page_excerpt',
		'hero_subheading_text'     => '',
		'hero_height_mode'         => 'content',
		'hero_overlay_strength'    => 'soft',
		'show_hero_badge'          => 1,
		'hero_text'                => '',
		// Single.
		'show_date'                => 1,
		'show_author'              => 1,
		'show_tags'                => 1,
		'show_newsletter_single'   => 1,
		// Archive.
		'archive_layout'           => 'asymmetric',
		'archive_card_style'       => 'balanced',
		'show_excerpts_archive'    => 1,
		'show_thumbnails_archive'  => 1,
		'single_layout'            => 'editorial',
		// Footer.
		'color_footer_bg'          => '',
		'footer_layout'            => 'split',
		'show_low_emission'        => 1,
		'custom_copyright'         => '',
		'show_footer_nav'          => 1,
	);
}

/**
 * Returns the available editorial presets for the theme.
 *
 * @return array<string, array<string, mixed>>
 */
function greenlight_get_appearance_presets() {
	return array(
		'editorial' => array(
			'label'       => __( 'Éditorial', 'greenlight' ),
			'description' => __( 'Équilibré, lisible, polyvalent.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-content-size'  => 'min(90vw, 1200px)',
				'--greenlight-wide-size'     => 'min(95vw, 1400px)',
				'--greenlight-main-gap'      => 'var(--wp--preset--spacing--xl)',
				'--greenlight-section-gap'   => 'var(--wp--preset--spacing--xl)',
				'--greenlight-hero-width'    => '14ch',
				'--greenlight-card-radius'   => '1em',
				'--greenlight-pill-radius'   => '1em',
				'--greenlight-button-radius' => '0.375rem',
			),
		),
		'magazine'  => array(
			'label'       => __( 'Magazine', 'greenlight' ),
			'description' => __( 'Plus ample et plus visuel.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-content-size'  => 'min(92vw, 1280px)',
				'--greenlight-wide-size'     => 'min(96vw, 1480px)',
				'--greenlight-main-gap'      => 'var(--wp--preset--spacing--xl)',
				'--greenlight-section-gap'   => 'var(--wp--preset--spacing--xl)',
				'--greenlight-hero-width'    => '16ch',
				'--greenlight-card-radius'   => '1.125em',
				'--greenlight-pill-radius'   => '1em',
				'--greenlight-button-radius' => '0.5rem',
			),
		),
		'studio'    => array(
			'label'       => __( 'Studio', 'greenlight' ),
			'description' => __( 'Plus compact et plus cadré.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-content-size'  => 'min(88vw, 1120px)',
				'--greenlight-wide-size'     => 'min(94vw, 1320px)',
				'--greenlight-main-gap'      => 'var(--wp--preset--spacing--lg)',
				'--greenlight-section-gap'   => 'var(--wp--preset--spacing--lg)',
				'--greenlight-hero-width'    => '13ch',
				'--greenlight-card-radius'   => '0.875em',
				'--greenlight-pill-radius'   => '0.875em',
				'--greenlight-button-radius' => '0.5rem',
			),
		),
		'journal'   => array(
			'label'       => __( 'Journal', 'greenlight' ),
			'description' => __( 'Dense, direct, lecture continue.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-content-size'  => 'min(86vw, 1040px)',
				'--greenlight-wide-size'     => 'min(94vw, 1220px)',
				'--greenlight-main-gap'      => 'var(--wp--preset--spacing--md)',
				'--greenlight-section-gap'   => 'var(--wp--preset--spacing--lg)',
				'--greenlight-hero-width'    => '12ch',
				'--greenlight-card-radius'   => '0.75em',
				'--greenlight-pill-radius'   => '0.9em',
				'--greenlight-button-radius' => '0.5rem',
			),
		),
	);
}

/**
 * Returns the available density scales.
 *
 * @return array<string, array<string, mixed>>
 */
function greenlight_get_appearance_densities() {
	return array(
		'airy'     => array(
			'label'       => __( 'Aéré', 'greenlight' ),
			'description' => __( 'Plus d’air entre les blocs.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-main-gap'    => 'var(--wp--preset--spacing--xl)',
				'--greenlight-section-gap' => 'var(--wp--preset--spacing--xl)',
			),
		),
		'balanced' => array(
			'label'       => __( 'Équilibré', 'greenlight' ),
			'description' => __( 'Rythme de base.', 'greenlight' ),
			'vars'        => array(),
		),
		'compact'  => array(
			'label'       => __( 'Compact', 'greenlight' ),
			'description' => __( 'Plus serré, plus dense.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-main-gap'    => 'var(--wp--preset--spacing--lg)',
				'--greenlight-section-gap' => 'var(--wp--preset--spacing--lg)',
			),
		),
	);
}

/**
 * Returns the available archive card styles.
 *
 * @return array<string, array<string, mixed>>
 */
function greenlight_get_archive_card_styles() {
	return array(
		'balanced' => array(
			'label'       => __( 'Équilibré', 'greenlight' ),
			'description' => __( 'Cartes lisibles et stables.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-archive-card-surface'        => 'var(--wp--preset--color--surface)',
				'--greenlight-archive-card-border'         => 'transparent',
				'--greenlight-archive-card-shadow'         => '0 20px 40px rgba(47, 52, 45, 0.04)',
				'--greenlight-archive-card-padding'        => 'var(--wp--preset--spacing--lg)',
				'--greenlight-archive-card-gap'            => 'var(--wp--preset--spacing--md)',
				'--greenlight-archive-featured-direction'  => 'row',
				'--greenlight-archive-featured-media'      => 'clamp(200px, 45%, 520px)',
				'--greenlight-archive-featured-body'       => 'clamp(220px, 45%, 520px)',
				'--greenlight-archive-featured-title-width' => '14ch',
				'--greenlight-archive-featured-summary-size' => 'var(--wp--preset--font-size--large)',
				'--greenlight-archive-featured-summary-width' => '44ch',
				'--greenlight-archive-teaser-direction'    => 'row',
				'--greenlight-archive-teaser-media'        => 'clamp(160px, 40%, 420px)',
				'--greenlight-archive-teaser-body'         => 'clamp(220px, 50%, 520px)',
				'--greenlight-archive-teaser-title-width'  => '22ch',
				'--greenlight-archive-teaser-summary-size' => 'var(--wp--preset--font-size--medium)',
				'--greenlight-archive-teaser-summary-width' => '52ch',
				'--greenlight-archive-teaser-direction-even' => 'row-reverse',
				'--greenlight-archive-list-gap'            => 'var(--wp--preset--spacing--lg)',
			),
		),
		'compact'  => array(
			'label'       => __( 'Compact', 'greenlight' ),
			'description' => __( 'Plus serré et plus vertical.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-archive-card-surface'        => 'var(--wp--preset--color--surface-alt)',
				'--greenlight-archive-card-border'         => '1px solid var(--wp--preset--color--border)',
				'--greenlight-archive-card-shadow'         => '0 14px 28px rgba(47, 52, 45, 0.03)',
				'--greenlight-archive-card-padding'        => 'var(--wp--preset--spacing--md)',
				'--greenlight-archive-card-gap'            => 'var(--wp--preset--spacing--sm)',
				'--greenlight-archive-featured-direction'  => 'column',
				'--greenlight-archive-featured-media'      => '100%',
				'--greenlight-archive-featured-body'       => '100%',
				'--greenlight-archive-featured-title-width' => '18ch',
				'--greenlight-archive-featured-summary-size' => 'var(--wp--preset--font-size--medium)',
				'--greenlight-archive-featured-summary-width' => '100%',
				'--greenlight-archive-teaser-direction'    => 'column',
				'--greenlight-archive-teaser-media'        => '100%',
				'--greenlight-archive-teaser-body'         => '100%',
				'--greenlight-archive-teaser-title-width'  => '18ch',
				'--greenlight-archive-teaser-summary-size' => 'var(--wp--preset--font-size--small)',
				'--greenlight-archive-teaser-summary-width' => '100%',
				'--greenlight-archive-teaser-direction-even' => 'column-reverse',
				'--greenlight-archive-list-gap'            => 'var(--wp--preset--spacing--md)',
			),
		),
		'framed'   => array(
			'label'       => __( 'Encadré', 'greenlight' ),
			'description' => __( 'Bordure plus nette.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-archive-card-surface'        => 'linear-gradient(180deg, rgba(255, 255, 255, 0.92), rgba(244, 241, 233, 0.96))',
				'--greenlight-archive-card-border'         => '1px solid rgba(77, 101, 72, 0.12)',
				'--greenlight-archive-card-shadow'         => '0 18px 34px rgba(47, 52, 45, 0.05)',
				'--greenlight-archive-card-padding'        => 'var(--wp--preset--spacing--lg)',
				'--greenlight-archive-card-gap'            => 'var(--wp--preset--spacing--md)',
				'--greenlight-archive-featured-direction'  => 'row',
				'--greenlight-archive-featured-media'      => 'clamp(200px, 42%, 500px)',
				'--greenlight-archive-featured-body'       => 'clamp(220px, 48%, 540px)',
				'--greenlight-archive-featured-title-width' => '15ch',
				'--greenlight-archive-featured-summary-size' => 'var(--wp--preset--font-size--large)',
				'--greenlight-archive-featured-summary-width' => '42ch',
				'--greenlight-archive-teaser-direction'    => 'row',
				'--greenlight-archive-teaser-media'        => 'clamp(160px, 38%, 380px)',
				'--greenlight-archive-teaser-body'         => 'clamp(220px, 54%, 540px)',
				'--greenlight-archive-teaser-title-width'  => '20ch',
				'--greenlight-archive-teaser-summary-size' => 'var(--wp--preset--font-size--medium)',
				'--greenlight-archive-teaser-summary-width' => '50ch',
				'--greenlight-archive-teaser-direction-even' => 'row-reverse',
				'--greenlight-archive-list-gap'            => 'var(--wp--preset--spacing--lg)',
			),
		),
	);
}

/**
 * Returns the available single layout variants.
 *
 * @return array<string, array<string, mixed>>
 */
function greenlight_get_single_layout_variants() {
	return array(
		'editorial' => array(
			'label'       => __( 'Éditorial', 'greenlight' ),
			'description' => __( 'Lecture dense mais respirante.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-single-content-width'    => '65ch',
				'--greenlight-single-heading-width'    => '18ch',
				'--greenlight-single-gap'              => 'var(--wp--preset--spacing--md)',
				'--greenlight-single-intro-font-style' => 'italic',
				'--greenlight-single-intro-size'       => 'var(--wp--preset--font-size--large)',
				'--greenlight-single-media-radius'     => 'var(--greenlight-card-radius, 1em)',
			),
		),
		'classic'   => array(
			'label'       => __( 'Classique', 'greenlight' ),
			'description' => __( 'Plus court et plus direct.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-single-content-width'    => '58ch',
				'--greenlight-single-heading-width'    => '18ch',
				'--greenlight-single-gap'              => 'var(--wp--preset--spacing--sm)',
				'--greenlight-single-intro-font-style' => 'normal',
				'--greenlight-single-intro-size'       => 'var(--wp--preset--font-size--medium)',
				'--greenlight-single-media-radius'     => '0.85em',
			),
		),
		'immersive' => array(
			'label'       => __( 'Immersif', 'greenlight' ),
			'description' => __( 'Plus large et plus ample.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-single-content-width'    => '72ch',
				'--greenlight-single-heading-width'    => '22ch',
				'--greenlight-single-gap'              => 'var(--wp--preset--spacing--lg)',
				'--greenlight-single-intro-font-style' => 'normal',
				'--greenlight-single-intro-size'       => 'var(--wp--preset--font-size--x-large)',
				'--greenlight-single-media-radius'     => '1.25em',
			),
		),
	);
}

/**
 * Returns the available footer layout variants.
 *
 * @return array<string, array<string, mixed>>
 */
function greenlight_get_footer_layout_variants() {
	return array(
		'split'   => array(
			'label'       => __( 'Séparé', 'greenlight' ),
			'description' => __( 'Navigation et mentions sur une ligne.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-footer-direction'     => 'row',
				'--greenlight-footer-justify'       => 'space-between',
				'--greenlight-footer-align'         => 'center',
				'--greenlight-footer-nav-justify'   => 'flex-start',
				'--greenlight-footer-text-align'    => 'left',
				'--greenlight-footer-gap'           => 'var(--wp--preset--spacing--sm) var(--wp--preset--spacing--md)',
				'--greenlight-footer-padding-block' => 'var(--wp--preset--spacing--md)',
			),
		),
		'stacked' => array(
			'label'       => __( 'Empilé', 'greenlight' ),
			'description' => __( 'Tout centré en colonne.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-footer-direction'     => 'column',
				'--greenlight-footer-justify'       => 'center',
				'--greenlight-footer-align'         => 'center',
				'--greenlight-footer-nav-justify'   => 'center',
				'--greenlight-footer-text-align'    => 'center',
				'--greenlight-footer-gap'           => 'var(--wp--preset--spacing--sm)',
				'--greenlight-footer-padding-block' => 'var(--wp--preset--spacing--lg)',
			),
		),
		'compact' => array(
			'label'       => __( 'Compact', 'greenlight' ),
			'description' => __( 'Plus court et plus dense.', 'greenlight' ),
			'vars'        => array(
				'--greenlight-footer-direction'     => 'row',
				'--greenlight-footer-justify'       => 'flex-start',
				'--greenlight-footer-align'         => 'center',
				'--greenlight-footer-nav-justify'   => 'flex-start',
				'--greenlight-footer-text-align'    => 'left',
				'--greenlight-footer-gap'           => 'var(--wp--preset--spacing--xs) var(--wp--preset--spacing--sm)',
				'--greenlight-footer-padding-block' => 'var(--wp--preset--spacing--sm)',
			),
		),
	);
}

/**
 * Returns the available Carbon Badge placements.
 *
 * @return array<string, array<string, string>>
 */
function greenlight_get_carbon_badge_positions() {
	return array(
		'top'    => array(
			'label'       => __( 'Haut de page', 'greenlight' ),
			'description' => __( 'Affiché dans le hero ou l’intro.', 'greenlight' ),
		),
		'footer' => array(
			'label'       => __( 'Pied de page', 'greenlight' ),
			'description' => __( 'Affiché dans le footer.', 'greenlight' ),
		),
	);
}

/**
 * Returns the available hero gradients.
 *
 * @return array<string, array<string, string>>
 */
function greenlight_get_hero_gradient_presets() {
	return array(
		'moss'  => array(
			'label' => __( 'Mousse', 'greenlight' ),
			'value' => 'linear-gradient(135deg, #556d51 0%, #d8e0c5 100%)',
		),
		'dawn'  => array(
			'label' => __( 'Aube', 'greenlight' ),
			'value' => 'linear-gradient(135deg, #f4ede0 0%, #d9c2a3 100%)',
		),
		'stone' => array(
			'label' => __( 'Pierre', 'greenlight' ),
			'value' => 'linear-gradient(135deg, #ced4cb 0%, #f5f3eb 100%)',
		),
		'sea'   => array(
			'label' => __( 'Mer', 'greenlight' ),
			'value' => 'linear-gradient(135deg, #54737e 0%, #d7e7e6 100%)',
		),
	);
}

/**
 * Returns CSS variables for the selected theme preset and density.
 *
 * @param array<string, mixed> $options Appearance options.
 * @return array<string, string>
 */
function greenlight_get_appearance_variant_vars( $options ) {
	$options             = is_array( $options ) ? $options : array();
	$presets             = greenlight_get_appearance_presets();
	$densities           = greenlight_get_appearance_densities();
	$archive_card_styles = greenlight_get_archive_card_styles();
	$single_layouts      = greenlight_get_single_layout_variants();
	$footer_layouts      = greenlight_get_footer_layout_variants();

	$preset_key         = isset( $options['theme_preset'] ) ? sanitize_key( (string) $options['theme_preset'] ) : 'editorial';
	$density_key        = isset( $options['density_scale'] ) ? sanitize_key( (string) $options['density_scale'] ) : 'balanced';
	$archive_card_key   = isset( $options['archive_card_style'] ) ? sanitize_key( (string) $options['archive_card_style'] ) : 'balanced';
	$single_layout_key  = isset( $options['single_layout'] ) ? sanitize_key( (string) $options['single_layout'] ) : 'editorial';
	$footer_layout_key  = isset( $options['footer_layout'] ) ? sanitize_key( (string) $options['footer_layout'] ) : 'split';
	$preset_vars        = isset( $presets[ $preset_key ]['vars'] ) ? (array) $presets[ $preset_key ]['vars'] : $presets['editorial']['vars'];
	$density_vars       = isset( $densities[ $density_key ]['vars'] ) ? (array) $densities[ $density_key ]['vars'] : array();
	$archive_card_vars  = isset( $archive_card_styles[ $archive_card_key ]['vars'] ) ? (array) $archive_card_styles[ $archive_card_key ]['vars'] : $archive_card_styles['balanced']['vars'];
	$single_layout_vars = isset( $single_layouts[ $single_layout_key ]['vars'] ) ? (array) $single_layouts[ $single_layout_key ]['vars'] : $single_layouts['editorial']['vars'];
	$footer_layout_vars = isset( $footer_layouts[ $footer_layout_key ]['vars'] ) ? (array) $footer_layouts[ $footer_layout_key ]['vars'] : $footer_layouts['split']['vars'];

	return array_merge( $preset_vars, $density_vars, $archive_card_vars, $single_layout_vars, $footer_layout_vars );
}

/**
 * Outputs variant CSS variables for the active appearance preset.
 *
 * @return void
 */
function greenlight_output_appearance_variants() {
	$opts = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, array() );

	if ( ! is_array( $opts ) ) {
		$opts = array();
	}

	$vars = greenlight_get_appearance_variant_vars( $opts );

	if ( empty( $vars ) ) {
		return;
	}

	$css = '';
	foreach ( $vars as $var => $value ) {
		if ( '' !== $value ) {
			$css .= $var . ':' . $value . ';';
		}
	}

	if ( '' !== $css ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<style id="greenlight-appearance-variants">:root{' . $css . '}</style>' . "\n";
	}
}
add_action( 'wp_head', 'greenlight_output_appearance_variants', 998 );

/**
 * Sanitizes appearance settings and syncs badge value to legacy option.
 *
 * @param mixed $input Raw input.
 * @return array<string, mixed>
 */
function greenlight_sanitize_appearance_settings( $input ) {
	$input               = is_array( $input ) ? $input : array();
	$defaults            = greenlight_get_appearance_defaults();
	$presets             = greenlight_get_appearance_presets();
	$densities           = greenlight_get_appearance_densities();
	$archive_card_styles = greenlight_get_archive_card_styles();
	$single_layouts      = greenlight_get_single_layout_variants();
	$footer_layouts      = greenlight_get_footer_layout_variants();
	$positions           = greenlight_get_carbon_badge_positions();
	$gradients           = greenlight_get_hero_gradient_presets();

	$badge_value = isset( $input['carbon_badge_value'] )
		? sanitize_text_field( $input['carbon_badge_value'] )
		: '';

	// Keep legacy standalone option in sync for backward compatibility.
	update_option( 'greenlight_carbon_badge_value', $badge_value );

	$sanitize_color = static function ( $val ) {
		if ( empty( $val ) ) {
			return '';
		}

		$color = sanitize_hex_color( $val );

		return $color ? $color : '';
	};

	return array(
		'theme_preset'             => isset( $input['theme_preset'] ) && isset( $presets[ sanitize_key( (string) $input['theme_preset'] ) ] )
			? sanitize_key( (string) $input['theme_preset'] )
			: $defaults['theme_preset'],
		'density_scale'            => isset( $input['density_scale'] ) && isset( $densities[ sanitize_key( (string) $input['density_scale'] ) ] )
			? sanitize_key( (string) $input['density_scale'] )
			: $defaults['density_scale'],
		// Global.
		'carbon_badge_enabled'     => isset( $input['carbon_badge_enabled'] ) ? 1 : 0,
		'carbon_badge_value'       => $badge_value,
		'carbon_badge_position'    => isset( $input['carbon_badge_position'] ) && isset( $positions[ sanitize_key( (string) $input['carbon_badge_position'] ) ] )
			? sanitize_key( (string) $input['carbon_badge_position'] )
			: $defaults['carbon_badge_position'],
		'newsletter_enabled'       => isset( $input['newsletter_enabled'] ) ? 1 : 0,
		// Couleurs.
		'color_primary'            => $sanitize_color( $input['color_primary'] ?? '' ),
		'color_surface'            => $sanitize_color( $input['color_surface'] ?? '' ),
		'color_text'               => $sanitize_color( $input['color_text'] ?? '' ),
		'color_background'         => $sanitize_color( $input['color_background'] ?? '' ),
		'color_tertiary'           => $sanitize_color( $input['color_tertiary'] ?? '' ),
		'color_border'             => $sanitize_color( $input['color_border'] ?? '' ),
		'color_on_surface_variant' => $sanitize_color( $input['color_on_surface_variant'] ?? '' ),
		// Header.
		'color_header_bg'          => $sanitize_color( $input['color_header_bg'] ?? '' ),
		'color_header_text'        => $sanitize_color( $input['color_header_text'] ?? '' ),
		'color_header_accent'      => $sanitize_color( $input['color_header_accent'] ?? '' ),
		'header_layout'            => in_array( $input['header_layout'] ?? '', array( 'inline', 'split', 'stacked' ), true )
			? sanitize_key( $input['header_layout'] )
			: $defaults['header_layout'],
		'header_sticky'            => isset( $input['header_sticky'] ) ? 1 : 0,
		'nav_link_case'            => in_array( $input['nav_link_case'] ?? '', array( 'normal', 'uppercase' ), true )
			? sanitize_key( $input['nav_link_case'] )
			: $defaults['nav_link_case'],
		'submenu_style'            => in_array( $input['submenu_style'] ?? '', array( 'plain', 'surface' ), true )
			? sanitize_key( $input['submenu_style'] )
			: $defaults['submenu_style'],
		'show_tagline'             => isset( $input['show_tagline'] ) ? 1 : 0,
		// Hero.
		'hero_enabled'             => isset( $input['hero_enabled'] ) ? 1 : 0,
		'hero_style'               => in_array( $input['hero_style'] ?? '', array( 'asymmetric', 'centered' ), true )
			? sanitize_key( $input['hero_style'] )
			: $defaults['hero_style'],
		'hero_background_mode'     => in_array( $input['hero_background_mode'] ?? '', array( 'none', 'color', 'gradient', 'image' ), true )
			? sanitize_key( $input['hero_background_mode'] )
			: $defaults['hero_background_mode'],
		'hero_background_image'    => isset( $input['hero_background_image'] ) ? esc_url_raw( $input['hero_background_image'] ) : '',
		'hero_background_color'    => $sanitize_color( $input['hero_background_color'] ?? '' ),
		'hero_gradient_preset'     => isset( $input['hero_gradient_preset'] ) && isset( $gradients[ sanitize_key( (string) $input['hero_gradient_preset'] ) ] )
			? sanitize_key( (string) $input['hero_gradient_preset'] )
			: $defaults['hero_gradient_preset'],
		'hero_heading_mode'        => in_array( $input['hero_heading_mode'] ?? '', array( 'page_title', 'site_title', 'custom', 'none' ), true )
			? sanitize_key( $input['hero_heading_mode'] )
			: $defaults['hero_heading_mode'],
		'hero_heading_text'        => isset( $input['hero_heading_text'] ) ? sanitize_text_field( $input['hero_heading_text'] ) : '',
		'hero_subheading_mode'     => in_array( $input['hero_subheading_mode'] ?? '', array( 'page_excerpt', 'site_tagline', 'custom', 'none' ), true )
			? sanitize_key( $input['hero_subheading_mode'] )
			: $defaults['hero_subheading_mode'],
		'hero_subheading_text'     => isset( $input['hero_subheading_text'] ) ? sanitize_textarea_field( $input['hero_subheading_text'] ) : '',
		'hero_height_mode'         => in_array( $input['hero_height_mode'] ?? '', array( 'content', 'tall', 'full' ), true )
			? sanitize_key( $input['hero_height_mode'] )
			: $defaults['hero_height_mode'],
		'hero_overlay_strength'    => in_array( $input['hero_overlay_strength'] ?? '', array( 'none', 'soft', 'strong' ), true )
			? sanitize_key( $input['hero_overlay_strength'] )
			: $defaults['hero_overlay_strength'],
		'show_hero_badge'          => isset( $input['show_hero_badge'] ) ? 1 : 0,
		'hero_text'                => isset( $input['hero_text'] ) ? sanitize_textarea_field( $input['hero_text'] ) : '',
		// Single.
		'show_date'                => isset( $input['show_date'] ) ? 1 : 0,
		'show_author'              => isset( $input['show_author'] ) ? 1 : 0,
		'show_tags'                => isset( $input['show_tags'] ) ? 1 : 0,
		'show_newsletter_single'   => isset( $input['show_newsletter_single'] ) ? 1 : 0,
		// Archive.
		'archive_layout'           => in_array( $input['archive_layout'] ?? '', array( 'asymmetric', 'list' ), true )
			? sanitize_key( $input['archive_layout'] )
			: $defaults['archive_layout'],
		'archive_card_style'       => isset( $input['archive_card_style'] ) && isset( $archive_card_styles[ sanitize_key( (string) $input['archive_card_style'] ) ] )
			? sanitize_key( (string) $input['archive_card_style'] )
			: $defaults['archive_card_style'],
		'show_excerpts_archive'    => isset( $input['show_excerpts_archive'] ) ? 1 : 0,
		'show_thumbnails_archive'  => isset( $input['show_thumbnails_archive'] ) ? 1 : 0,
		'single_layout'            => isset( $input['single_layout'] ) && isset( $single_layouts[ sanitize_key( (string) $input['single_layout'] ) ] )
			? sanitize_key( (string) $input['single_layout'] )
			: $defaults['single_layout'],
		// Footer.
		'color_footer_bg'          => $sanitize_color( $input['color_footer_bg'] ?? '' ),
		'footer_layout'            => isset( $input['footer_layout'] ) && isset( $footer_layouts[ sanitize_key( (string) $input['footer_layout'] ) ] )
			? sanitize_key( (string) $input['footer_layout'] )
			: $defaults['footer_layout'],
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
	register_setting(
		'greenlight_appearance',
		GREENLIGHT_APPEARANCE_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'greenlight_sanitize_appearance_settings',
			'default'           => greenlight_get_appearance_defaults(),
		)
	);
}
add_action( 'admin_init', 'greenlight_register_appearance_settings' );

/**
 * SVG settings.
 */

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
	register_setting(
		'greenlight_svg',
		GREENLIGHT_SVG_OPTION_KEY,
		array(
			'type'              => 'array',
			'sanitize_callback' => 'greenlight_sanitize_svg_settings',
			'default'           => array( 'enable_svg' => 0 ),
		)
	);
}
add_action( 'admin_init', 'greenlight_register_svg_settings' );

/**
 * Custom colour output on the front end.
 */

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
		'color_header_text'        => '--greenlight-header-text',
		'color_header_accent'      => '--greenlight-header-accent',
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

/**
 * Cache helpers.
 */

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

	return array(
		'count' => $count,
		'size'  => $size,
	);
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
			$files = glob( $cache_dir . '*.html' );
			if ( ! is_array( $files ) ) {
				$files = array();
			}
			array_map( 'unlink', $files );
		}
	}

	wp_safe_redirect( admin_url( 'admin.php?page=greenlight&tab=performance&purged=1' ) );
	exit;
}
add_action( 'admin_post_greenlight_purge_cache', 'greenlight_handle_purge_cache' );

/**
 * Returns the shell context shown above the current admin tab.
 *
 * @param string $current_tab Current tab slug.
 * @return array<string, mixed>
 */
function greenlight_get_admin_shell_context( $current_tab ) {
	$tabs = array(
		'seo'         => __( 'SEO', 'greenlight' ),
		'images'      => __( 'Images', 'greenlight' ),
		'performance' => __( 'Performance', 'greenlight' ),
		'appearance'  => __( 'Apparence', 'greenlight' ),
		'svg'         => __( 'SVG', 'greenlight' ),
		'tools'       => __( 'Outils', 'greenlight' ),
	);

	$seo_options          = function_exists( 'greenlight_get_seo_options' ) ? greenlight_get_seo_options() : array();
	$image_options        = function_exists( 'greenlight_get_images_options' ) ? greenlight_get_images_options() : array();
	$perf_options         = get_option( GREENLIGHT_PERF_OPTION_KEY, greenlight_get_performance_defaults() );
	$appearance_options   = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, greenlight_get_appearance_defaults() );
	$appearance_presets   = greenlight_get_appearance_presets();
	$appearance_densities = greenlight_get_appearance_densities();
	$svg_options          = get_option( GREENLIGHT_SVG_OPTION_KEY, array( 'enable_svg' => 0 ) );
	$redirects            = get_option( 'greenlight_redirects', array() );
	$log_404              = get_option( 'greenlight_404_log', array() );
	$cache_stats          = greenlight_get_cache_stats();
	$image_report         = function_exists( 'greenlight_get_image_storage_report' )
		? greenlight_get_image_storage_report()
		: array(
			'count' => 0,
			'saved' => 0,
		);

		$metrics = array();
		$note    = __( 'Chaque onglet couvre un usage précis.', 'greenlight' );

	switch ( $current_tab ) {
		case 'seo':
			$metrics = array(
				array(
					'label' => __( 'Sitemap', 'greenlight' ),
					'value' => ! empty( $seo_options['enable_sitemap'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ),
				),
				array(
					'label' => __( 'Redirections', 'greenlight' ),
					'value' => number_format_i18n( is_array( $redirects ) ? count( $redirects ) : 0 ),
				),
				array(
					'label' => __( '404 récents', 'greenlight' ),
					'value' => number_format_i18n( is_array( $log_404 ) ? count( $log_404 ) : 0 ),
				),
				array(
					'label' => __( 'Fil d’Ariane', 'greenlight' ),
					'value' => ! empty( $seo_options['show_breadcrumbs'] ) ? __( 'Actifs', 'greenlight' ) : __( 'Désactivés', 'greenlight' ),
				),
			);
			$note    = __( 'Indexation, crawl et redirections.', 'greenlight' );
			break;
		case 'images':
			$metrics = array(
				array(
					'label' => __( 'WebP', 'greenlight' ),
					'value' => ! empty( $image_options['enable_webp_conversion'] ) ? __( 'Actif', 'greenlight' ) : __( 'Désactivé', 'greenlight' ),
				),
				array(
					'label' => __( 'AVIF', 'greenlight' ),
					'value' => ! empty( $image_options['enable_avif'] ) ? __( 'Actif', 'greenlight' ) : __( 'Désactivé', 'greenlight' ),
				),
				array(
					'label' => __( 'Images optimisées', 'greenlight' ),
					'value' => number_format_i18n( isset( $image_report['count'] ) ? (int) $image_report['count'] : 0 ),
				),
				array(
					'label' => __( 'Économie', 'greenlight' ),
					'value' => function_exists( 'greenlight_format_image_bytes' ) ? greenlight_format_image_bytes( isset( $image_report['saved'] ) ? (int) $image_report['saved'] : 0 ) : '0 B',
				),
			);
			$note    = __( 'Formats utiles, tailles justes, poids réduit.', 'greenlight' );
			break;
		case 'performance':
			$metrics = array(
				array(
					'label' => __( 'Pages en cache', 'greenlight' ),
					'value' => number_format_i18n( (int) $cache_stats['count'] ),
				),
				array(
					'label' => __( 'Taille du cache', 'greenlight' ),
					'value' => size_format( (int) $cache_stats['size'], 2 ),
				),
				array(
					'label' => __( 'Diffusion CSS', 'greenlight' ),
					'value' => ! empty( $perf_options['enable_css_min'] ) ? __( 'Minifiee', 'greenlight' ) : __( 'Source', 'greenlight' ),
				),
				array(
					'label' => __( 'Rythme frontal', 'greenlight' ),
					'value' => isset( $perf_options['heartbeat_front'] ) && 'reduce' === $perf_options['heartbeat_front']
						? __( 'Reduit', 'greenlight' )
						: (
							isset( $perf_options['heartbeat_front'] ) && 'disable' === $perf_options['heartbeat_front']
								? __( 'Coupe', 'greenlight' )
								: __( 'Par defaut', 'greenlight' )
						),
				),
			);
			$note    = __( 'Cache, diffusion et maintenance.', 'greenlight' );
			break;
		case 'appearance':
			$preset_key  = isset( $appearance_options['theme_preset'] ) && isset( $appearance_presets[ sanitize_key( (string) $appearance_options['theme_preset'] ) ] )
				? sanitize_key( (string) $appearance_options['theme_preset'] )
				: 'editorial';
			$density_key = isset( $appearance_options['density_scale'] ) && isset( $appearance_densities[ sanitize_key( (string) $appearance_options['density_scale'] ) ] )
				? sanitize_key( (string) $appearance_options['density_scale'] )
				: 'balanced';
			$metrics     = array(
				array(
					'label' => __( 'Preset', 'greenlight' ),
					'value' => isset( $appearance_presets[ $preset_key ]['label'] ) ? $appearance_presets[ $preset_key ]['label'] : $appearance_presets['editorial']['label'],
				),
				array(
					'label' => __( 'Densité', 'greenlight' ),
					'value' => isset( $appearance_densities[ $density_key ]['label'] ) ? $appearance_densities[ $density_key ]['label'] : $appearance_densities['balanced']['label'],
				),
				array(
					'label' => __( 'Hero', 'greenlight' ),
					'value' => isset( $appearance_options['hero_style'] ) && 'centered' === $appearance_options['hero_style'] ? __( 'Centré', 'greenlight' ) : __( 'Asymétrique', 'greenlight' ),
				),
				array(
					'label' => __( 'Archives', 'greenlight' ),
					'value' => isset( $appearance_options['archive_layout'] ) && 'list' === $appearance_options['archive_layout'] ? __( 'Liste', 'greenlight' ) : __( 'Grille', 'greenlight' ),
				),
			);
			$note        = __( 'Preset éditorial, densité et rendu.', 'greenlight' );
			break;
		case 'svg':
			$metrics = array(
				array(
					'label' => __( 'SVG', 'greenlight' ),
					'value' => ! empty( $svg_options['enable_svg'] ) ? __( 'Actif', 'greenlight' ) : __( 'Désactivé', 'greenlight' ),
				),
				array(
					'label' => __( 'Sanitisation', 'greenlight' ),
					'value' => __( 'DOMDocument', 'greenlight' ),
				),
				array(
					'label' => __( 'Filtre d’upload', 'greenlight' ),
					'value' => __( 'Actif', 'greenlight' ),
				),
				array(
					'label' => __( 'Périmètre', 'greenlight' ),
					'value' => __( 'Médias', 'greenlight' ),
				),
			);
			$note    = __( 'Autoriser, nettoyer, limiter le risque.', 'greenlight' );
			break;
		case 'tools':
			$metrics = array(
				array(
					'label' => __( 'Export', 'greenlight' ),
					'value' => __( 'JSON', 'greenlight' ),
				),
				array(
					'label' => __( 'Import', 'greenlight' ),
					'value' => __( 'JSON', 'greenlight' ),
				),
				array(
					'label' => __( 'Purge cache', 'greenlight' ),
					'value' => __( 'Manuel', 'greenlight' ),
				),
				array(
					'label' => __( 'Statut', 'greenlight' ),
					'value' => __( 'Prêt', 'greenlight' ),
				),
			);
			$note    = __( 'Import et export des réglages.', 'greenlight' );
			break;
	}

		return array(
			'tab_label' => isset( $tabs[ $current_tab ] ) ? $tabs[ $current_tab ] : $tabs['seo'],
			'headline'  => __( 'Suite éditoriale', 'greenlight' ),
			'lead'      => __( 'Gérez SEO, images, performance, apparence, SVG et outils dans un seul espace d’éco-conception.', 'greenlight' ),
			'note'      => $note,
			'metrics'   => $metrics,
		);
}

/**
 * Main page render.
 */

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
	$shell     = greenlight_get_admin_shell_context( $current_tab );
	$tab_steps = array(
		'seo'         => '01',
		'images'      => '02',
		'performance' => '03',
		'appearance'  => '04',
		'svg'         => '05',
		'tools'       => '06',
	);
	?>
	<div class="wrap greenlight-admin-shell">
		<header class="greenlight-admin-hero">
			<div class="greenlight-admin-hero__copy">
				<p class="greenlight-admin-eyebrow"><?php echo esc_html( $shell['headline'] ); ?></p>
				<h1><?php esc_html_e( 'Greenlight', 'greenlight' ); ?></h1>
				<p class="greenlight-admin-lead"><?php echo esc_html( $shell['lead'] ); ?></p>
			</div>
			<div class="greenlight-admin-hero__meta">
				<span class="greenlight-admin-chip"><?php echo esc_html( $shell['tab_label'] ); ?></span>
				<span class="greenlight-admin-chip greenlight-admin-chip--muted"><?php esc_html_e( 'Accès admin', 'greenlight' ); ?></span>
			</div>
		</header>

		<div class="greenlight-admin-layout">
			<aside class="greenlight-admin-rail">
				<div class="greenlight-admin-panel greenlight-admin-panel--metrics">
					<p class="greenlight-admin-panel__eyebrow"><?php esc_html_e( 'Etat actuel', 'greenlight' ); ?></p>
					<div class="greenlight-admin-metrics">
						<?php foreach ( $shell['metrics'] as $metric ) : ?>
							<div class="greenlight-admin-metric">
								<span class="greenlight-admin-metric__label"><?php echo esc_html( $metric['label'] ); ?></span>
								<span class="greenlight-admin-metric__value"><?php echo esc_html( $metric['value'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
				</div>

				<div class="greenlight-admin-panel greenlight-admin-panel--note">
				<p class="greenlight-admin-panel__eyebrow"><?php esc_html_e( 'À garder en tête', 'greenlight' ); ?></p>
					<p><?php echo esc_html( $shell['note'] ); ?></p>
				</div>
			</aside>

			<div class="greenlight-admin-main">
				<nav class="greenlight-admin-tabs" aria-label="<?php esc_attr_e( 'Sections Greenlight', 'greenlight' ); ?>">
					<?php foreach ( $tabs as $slug => $label ) : ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=greenlight&tab=' . $slug ) ); ?>"
							class="nav-tab<?php echo $slug === $current_tab ? ' nav-tab-active' : ''; ?>">
							<span class="greenlight-admin-tab__step"><?php echo esc_html( $tab_steps[ $slug ] ); ?></span>
							<span class="greenlight-admin-tab__label"><?php echo esc_html( $label ); ?></span>
						</a>
					<?php endforeach; ?>
				</nav>

				<div class="greenlight-admin-tab-panel greenlight-admin-tab-panel--<?php echo esc_attr( $current_tab ); ?>">
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
		</div>
	</div>
	<?php
}

/**
 * Tab renders.
 */

/**
 * Renders the SEO tab — same option_group as inc/seo-settings.php.
 *
 * @return void
 */
function greenlight_render_admin_tab_seo() {
	$options   = greenlight_get_seo_options();
	$redirects = get_option( 'greenlight_redirects', array() );
	$log_404   = get_option( 'greenlight_404_log', array() );

	if ( ! is_array( $redirects ) ) {
		$redirects = array();
	}
	if ( ! is_array( $log_404 ) ) {
		$log_404 = array();
	}
	?>
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'SEO', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'Indexation', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Réglez l’indexation, les métadonnées et les redirections.', 'greenlight' ); ?></p>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__summary">
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Sitemap', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $options['enable_sitemap'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Signal de crawl natif.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Fil d’Ariane', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $options['show_breadcrumbs'] ) ? __( 'Visible', 'greenlight' ) : __( 'Désactivé', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Maillage interne.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Redirections', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( number_format_i18n( count( $redirects ) ) ); ?></strong>
			<span><?php esc_html_e( 'Règles de secours actives.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( '404 récents', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( number_format_i18n( count( $log_404 ) ) ); ?></strong>
			<span><?php esc_html_e( 'Points à corriger ou à rediriger.', 'greenlight' ); ?></span>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--seo">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Réglages essentiels', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Métadonnées globales', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Réglages globaux.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_seo' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="gl-site-title"><?php esc_html_e( 'Titre SERP', 'greenlight' ); ?></label>
							</th>
							<td>
								<input id="gl-site-title" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_title]" type="text" class="regular-text" value="<?php echo esc_attr( $options['site_title'] ); ?>">
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="gl-site-desc"><?php esc_html_e( 'Description du site', 'greenlight' ); ?></label>
							</th>
							<td>
								<textarea id="gl-site-desc" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[site_description]" class="large-text" rows="4"><?php echo esc_textarea( $options['site_description'] ); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="gl-title-sep"><?php esc_html_e( 'Séparateur', 'greenlight' ); ?></label>
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
									<?php esc_html_e( 'Activer le sitemap natif', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Archives auteur', 'greenlight' ); ?></th>
							<td>
								<label for="gl-noindex-author">
									<input id="gl-noindex-author" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_author_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_author_archives'], 1 ); ?>>
									<?php esc_html_e( 'Passer les archives auteur en noindex', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Archives de tags', 'greenlight' ); ?></th>
							<td>
								<label for="gl-noindex-tags">
									<input id="gl-noindex-tags" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[noindex_tag_archives]" type="checkbox" value="1" <?php checked( (int) $options['noindex_tag_archives'], 1 ); ?>>
									<?php esc_html_e( 'Passer les archives de tags en noindex', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Fil d\'Ariane', 'greenlight' ); ?></th>
							<td>
								<label for="gl-breadcrumbs">
									<input id="gl-breadcrumbs" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[show_breadcrumbs]" type="checkbox" value="1" <?php checked( ! empty( $options['show_breadcrumbs'] ), true ); ?>>
									<?php esc_html_e( 'Afficher le fil d\'Ariane', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer les réglages SEO', 'greenlight' ) ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Navigation', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Menu et sous-menus', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Layout, couleurs, sticky et dropdowns CSS-only.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'color_header_text', 'color_header_accent', 'header_layout', 'header_sticky', 'nav_link_case', 'submenu_style' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th><label for="gl-header-layout"><?php esc_html_e( 'Layout', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-header-layout" name="<?php echo esc_attr( $key ); ?>[header_layout]">
									<option value="inline" <?php selected( $o['header_layout'], 'inline' ); ?>><?php esc_html_e( 'Ligne simple', 'greenlight' ); ?></option>
									<option value="split" <?php selected( $o['header_layout'], 'split' ); ?>><?php esc_html_e( 'Séparé', 'greenlight' ); ?></option>
									<option value="stacked" <?php selected( $o['header_layout'], 'stacked' ); ?>><?php esc_html_e( 'Empilé', 'greenlight' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Définit la lecture du brand, du menu et du CTA.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Sticky', 'greenlight' ); ?></th>
							<td><label><input name="<?php echo esc_attr( $key ); ?>[header_sticky]" type="checkbox" value="1" <?php checked( (int) $o['header_sticky'], 1 ); ?>> <?php esc_html_e( 'Rendre le header collant au scroll', 'greenlight' ); ?></label></td>
						</tr>
						<tr>
							<th><label for="gl-header-text"><?php esc_html_e( 'Texte header', 'greenlight' ); ?></label></th>
							<td><input type="text" id="gl-header-text" name="<?php echo esc_attr( $key ); ?>[color_header_text]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_text'] ); ?>" data-default-color="#2f342d"></td>
						</tr>
						<tr>
							<th><label for="gl-header-accent"><?php esc_html_e( 'Accent header', 'greenlight' ); ?></label></th>
							<td><input type="text" id="gl-header-accent" name="<?php echo esc_attr( $key ); ?>[color_header_accent]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_accent'] ); ?>" data-default-color="#4c6547"></td>
						</tr>
						<tr>
							<th><label for="gl-nav-case"><?php esc_html_e( 'Casse menu', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-nav-case" name="<?php echo esc_attr( $key ); ?>[nav_link_case]">
									<option value="normal" <?php selected( $o['nav_link_case'], 'normal' ); ?>><?php esc_html_e( 'Normale', 'greenlight' ); ?></option>
									<option value="uppercase" <?php selected( $o['nav_link_case'], 'uppercase' ); ?>><?php esc_html_e( 'Majuscules', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-submenu-style"><?php esc_html_e( 'Sous-menus', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-submenu-style" name="<?php echo esc_attr( $key ); ?>[submenu_style]">
									<option value="plain" <?php selected( $o['submenu_style'], 'plain' ); ?>><?php esc_html_e( 'Discrets', 'greenlight' ); ?></option>
									<option value="surface" <?php selected( $o['submenu_style'], 'surface' ); ?>><?php esc_html_e( 'En surface', 'greenlight' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Ouvert au survol et au focus, sans JavaScript.', 'greenlight' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Robots.txt', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Fichier personnalisé', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Remplace la sortie WordPress.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_seo' ); ?>
					<?php
					foreach ( $options as $key => $value ) {
						if ( 'custom_robots_txt' === $key || 'show_breadcrumbs' === $key ) {
							continue;
						}
						if ( is_numeric( $value ) ) {
							if ( (int) $value ) {
								echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_SEO_OPTION_KEY ) . '[' . esc_attr( $key ) . ']" value="1">';
							}
						} else {
							echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_SEO_OPTION_KEY ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '">';
						}
					}
					if ( ! empty( $options['show_breadcrumbs'] ) ) {
						echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_SEO_OPTION_KEY ) . '[show_breadcrumbs]" value="1">';
					}
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="gl-robots-txt"><?php esc_html_e( 'Contenu robots.txt', 'greenlight' ); ?></label>
							</th>
							<td>
								<textarea id="gl-robots-txt" name="<?php echo esc_attr( GREENLIGHT_SEO_OPTION_KEY ); ?>[custom_robots_txt]" class="large-text code" rows="8"><?php echo esc_textarea( ! empty( $options['custom_robots_txt'] ) ? $options['custom_robots_txt'] : greenlight_get_default_robots_txt() ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Vide = robots.txt par défaut.', 'greenlight' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer le robots.txt', 'greenlight' ) ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Redirections', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Règles 301 et 302', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Gardez les URLs propres.', 'greenlight' ); ?></p>
					</div>
				</div>
				<?php
				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['redirect_added'] ) ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Redirection ajoutée.', 'greenlight' ) . '</p></div>';
				}
				if ( isset( $_GET['redirect_deleted'] ) ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Redirection supprimée.', 'greenlight' ) . '</p></div>';
				}
				if ( isset( $_GET['redirects_imported'] ) ) {
					/* translators: %d: number of imported redirects. */
					$redirects_imported_message = esc_html__( '%d redirection(s) importée(s).', 'greenlight' );
					printf(
						'<div class="notice notice-success is-dismissible"><p>' . esc_html( $redirects_imported_message ) . '</p></div>',
						absint( $_GET['redirects_imported'] )
					);
				}
				// phpcs:enable

				if ( ! empty( $redirects ) ) :
					?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Source', 'greenlight' ); ?></th>
								<th><?php esc_html_e( 'Destination', 'greenlight' ); ?></th>
								<th><?php esc_html_e( 'Code', 'greenlight' ); ?></th>
								<th><?php esc_html_e( 'Hits', 'greenlight' ); ?></th>
								<th><?php esc_html_e( 'Action', 'greenlight' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $redirects as $index => $rule ) : ?>
								<tr>
									<td><code><?php echo esc_html( $rule['source'] ); ?></code></td>
									<td><?php echo esc_html( $rule['destination'] ); ?></td>
									<td><?php echo esc_html( $rule['code'] ); ?></td>
									<td><?php echo esc_html( isset( $rule['hits'] ) ? $rule['hits'] : 0 ); ?></td>
									<td>
										<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
											<input type="hidden" name="action" value="greenlight_delete_redirect">
											<input type="hidden" name="redirect_index" value="<?php echo esc_attr( $index ); ?>">
											<?php wp_nonce_field( 'greenlight_delete_redirect' ); ?>
											<button type="submit" class="button button-link-delete button-small"><?php esc_html_e( 'Supprimer', 'greenlight' ); ?></button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Aucune redirection.', 'greenlight' ); ?></p>
				<?php endif; ?>

				<h4><?php esc_html_e( 'Ajouter une règle', 'greenlight' ); ?></h4>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="greenlight_add_redirect">
					<?php wp_nonce_field( 'greenlight_add_redirect' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="gl-redirect-source"><?php esc_html_e( 'URL source', 'greenlight' ); ?></label></th>
							<td><input id="gl-redirect-source" name="redirect_source" type="text" class="regular-text" placeholder="/ancienne-page"></td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-redirect-dest"><?php esc_html_e( 'URL destination', 'greenlight' ); ?></label></th>
							<td><input id="gl-redirect-dest" name="redirect_destination" type="text" class="regular-text" placeholder="https://example.com/nouvelle-page"></td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-redirect-code"><?php esc_html_e( 'Code HTTP', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-redirect-code" name="redirect_code">
									<option value="301"><?php esc_html_e( '301 — Permanent', 'greenlight' ); ?></option>
									<option value="302"><?php esc_html_e( '302 — Temporaire', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Ajouter', 'greenlight' ), 'secondary' ); ?>
				</form>

				<h4><?php esc_html_e( 'Importer un CSV', 'greenlight' ); ?></h4>
				<p class="description"><?php esc_html_e( 'Format : source,destination,code.', 'greenlight' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
					<input type="hidden" name="action" value="greenlight_import_redirects">
					<?php wp_nonce_field( 'greenlight_import_redirects' ); ?>
					<input type="file" name="redirects_csv" accept=".csv">
					<?php submit_button( __( 'Importer', 'greenlight' ), 'secondary' ); ?>
				</form>
			</section>
		</div>

		<aside class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Signal SEO', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'État de l’indexation', 'greenlight' ); ?></h3>
					</div>
				</div>
				<ul class="greenlight-admin-tab-panel__list">
					<?php /* translators: 1: sitemap status. */ ?>
					<li><?php echo esc_html( sprintf( __( 'Sitemap XML : %s', 'greenlight' ), ! empty( $options['enable_sitemap'] ) ? __( 'actif', 'greenlight' ) : __( 'désactivé', 'greenlight' ) ) ); ?></li>
					<?php /* translators: 1: breadcrumb status. */ ?>
					<li><?php echo esc_html( sprintf( __( 'Fil d’Ariane : %s', 'greenlight' ), ! empty( $options['show_breadcrumbs'] ) ? __( 'actif', 'greenlight' ) : __( 'désactivé', 'greenlight' ) ) ); ?></li>
					<?php /* translators: 1: redirect count. */ ?>
					<li><?php echo esc_html( sprintf( __( 'Redirections : %d', 'greenlight' ), count( $redirects ) ) ); ?></li>
					<?php /* translators: 1: 404 log count. */ ?>
					<li><?php echo esc_html( sprintf( __( '404 récents : %d', 'greenlight' ), count( $log_404 ) ) ); ?></li>
				</ul>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Impact attendu', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Ce que ces réglages changent', 'greenlight' ); ?></h3>
					</div>
				</div>
				<ul class="greenlight-admin-tab-panel__list">
					<li><?php esc_html_e( 'Un sitemap actif aide les moteurs à parcourir les contenus sans charger d’extension dédiée.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Les archives noindex gardent le crawl centré sur les pages utiles.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Redirections et 404 servent à corriger les liens.', 'greenlight' ); ?></li>
				</ul>
			</section>

			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Logs 404', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Erreurs récentes', 'greenlight' ); ?></h3>
					</div>
				</div>
				<?php if ( empty( $log_404 ) ) : ?>
					<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Aucune erreur.', 'greenlight' ); ?></p>
				<?php else : ?>
					<table class="widefat striped">
						<thead>
							<tr>
								<th><?php esc_html_e( 'URL', 'greenlight' ); ?></th>
								<th><?php esc_html_e( 'Date', 'greenlight' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( array_slice( $log_404, 0, 50 ) as $entry ) : ?>
								<tr>
									<td><code><?php echo esc_html( $entry['url'] ); ?></code></td>
									<td><?php echo esc_html( $entry['time'] ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endif; ?>
			</section>
		</aside>
	</div>
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
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Images', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'Médias', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Convertissez les médias et gardez des fichiers légers.', 'greenlight' ); ?></p>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__summary">
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'WebP', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $options['enable_webp_conversion'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Conversion à l’upload.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'AVIF', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $options['enable_avif'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Poids réduit.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Économie', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( greenlight_format_image_bytes( (int) $report['saved'] ) ); ?></strong>
			<span><?php esc_html_e( 'Poids évité.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Lot', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( number_format_i18n( (int) $report['count'] ) ); ?></strong>
			<span><?php esc_html_e( 'WebP générés.', 'greenlight' ); ?></span>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--images">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Conversion', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'WebP et tailles cibles', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Chaîne simple.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_images' ); ?>
					<?php
					foreach ( array( 'enable_avif', 'avif_quality', 'max_original_width', 'keep_original_copy' ) as $image_key ) {
						if ( isset( $options[ $image_key ] ) ) {
							echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ) . '[' . esc_attr( $image_key ) . ']" value="' . esc_attr( $options[ $image_key ] ) . '">';
						}
					}
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Conversion WebP', 'greenlight' ); ?></th>
							<td>
								<label for="gl-enable-webp">
									<input id="gl-enable-webp" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[enable_webp_conversion]" type="checkbox" value="1" <?php checked( (int) $options['enable_webp_conversion'], 1 ); ?>>
									<?php esc_html_e( 'Générer un WebP à l\'upload', 'greenlight' ); ?>
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
									<?php esc_html_e( 'Retirer medium_large, 1536×1536 et 2048×2048', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer les réglages médias', 'greenlight' ) ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Traitement en masse', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Optimisation des médias', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Optimisation par lots.', 'greenlight' ); ?></p>
					</div>
				</div>
				<?php
				$bulk_stats = function_exists( 'greenlight_get_bulk_stats' ) ? greenlight_get_bulk_stats() : array(
					'total'     => 0,
					'optimized' => 0,
					'savings'   => 0,
				);
				$pct        = $bulk_stats['total'] > 0 ? round( ( $bulk_stats['optimized'] / $bulk_stats['total'] ) * 100 ) : 0;
				?>
				<p>
					<?php
					printf(
						/* translators: 1: optimized count 2: total count 3: percentage */
						esc_html__( '%1$d / %2$d images optimisées (%3$d%%)', 'greenlight' ),
						(int) $bulk_stats['optimized'],
						(int) $bulk_stats['total'],
						(int) $pct
					);
					if ( $bulk_stats['savings'] > 0 ) {
						echo ' — ';
						printf(
							/* translators: %s: bytes saved */
							esc_html__( 'Économie : %s', 'greenlight' ),
							esc_html( size_format( $bulk_stats['savings'], 2 ) )
						);
					}
					?>
				</p>

				<div id="greenlight-bulk-optimize" class="greenlight-admin-tab-panel__actions">
					<label for="gl-batch-size"><?php esc_html_e( 'Lot :', 'greenlight' ); ?></label>
					<select id="gl-batch-size">
						<option value="5">5</option>
						<option value="10" selected>10</option>
						<option value="20">20</option>
					</select>
					<button type="button" id="gl-bulk-start" class="button button-primary"><?php esc_html_e( 'Lancer le lot', 'greenlight' ); ?></button>
				</div>
				<div id="gl-bulk-progress" style="display:none;margin-top:1em">
					<div style="background:#dcdcde;border-radius:4px;height:24px;max-width:400px">
						<div id="gl-bulk-bar" style="background:#46b450;height:100%;border-radius:4px;width:0%;transition:width .3s"></div>
					</div>
					<p id="gl-bulk-status"></p>
				</div>
			</section>
		</div>

		<aside class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Stockage', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Espace économisé', 'greenlight' ); ?></h3>
					</div>
				</div>
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
						esc_html__( 'Original : %1$s. WebP : %2$s.', 'greenlight' ),
						esc_html( greenlight_format_image_bytes( (int) $report['original'] ) ),
						esc_html( greenlight_format_image_bytes( (int) $report['webp'] ) )
					);
					?>
				</p>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'AVIF', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'AVIF', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Si le serveur le permet.', 'greenlight' ); ?></p>
					</div>
				</div>
				<?php
				$avif_available = function_exists( 'greenlight_is_avif_conversion_enabled' ) && greenlight_is_avif_conversion_enabled();
				if ( ! $avif_available && ! empty( $options['enable_avif'] ) ) {
					echo '<div class="notice notice-warning inline"><p>' . esc_html__( 'Le support AVIF n\'est pas disponible sur ce serveur (PHP 8.1+ avec GD imageavif ou Imagick requis).', 'greenlight' ) . '</p></div>';
				}
				?>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_images' ); ?>
					<?php
					// Preserve existing options as hidden fields.
					foreach ( $options as $k => $v ) {
						if ( in_array( $k, array( 'enable_avif', 'avif_quality', 'max_original_width', 'keep_original_copy' ), true ) ) {
							continue;
						}
						if ( is_numeric( $v ) && (int) $v ) {
							echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ) . '[' . esc_attr( $k ) . ']" value="' . esc_attr( $v ) . '">';
						} elseif ( ! is_numeric( $v ) ) {
							echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ) . '[' . esc_attr( $k ) . ']" value="' . esc_attr( $v ) . '">';
						}
					}
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Conversion AVIF', 'greenlight' ); ?></th>
							<td>
								<label for="gl-enable-avif">
									<input id="gl-enable-avif" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[enable_avif]" type="checkbox" value="1" <?php checked( ! empty( $options['enable_avif'] ), true ); ?>>
									<?php esc_html_e( 'Générer un AVIF en plus du WebP', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="gl-avif-quality"><?php esc_html_e( 'Qualité AVIF', 'greenlight' ); ?></label>
							</th>
							<td>
								<input id="gl-avif-quality" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[avif_quality]" type="range" min="1" max="100" value="<?php echo esc_attr( isset( $options['avif_quality'] ) ? (int) $options['avif_quality'] : 70 ); ?>">
								<span><?php echo esc_html( isset( $options['avif_quality'] ) ? (int) $options['avif_quality'] : 70 ); ?>/100</span>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="gl-max-width"><?php esc_html_e( 'Largeur max. originaux', 'greenlight' ); ?></label>
							</th>
							<td>
								<input id="gl-max-width" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[max_original_width]" type="number" min="800" max="10000" class="small-text" value="<?php echo esc_attr( isset( $options['max_original_width'] ) ? (int) $options['max_original_width'] : 2560 ); ?>">
								<span>px</span>
								<p class="description"><?php esc_html_e( 'Images plus larges redimensionnées à l\'upload.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Garder l\'original', 'greenlight' ); ?></th>
							<td>
								<label for="gl-keep-original">
									<input id="gl-keep-original" name="<?php echo esc_attr( GREENLIGHT_IMAGES_OPTION_KEY ); ?>[keep_original_copy]" type="checkbox" value="1" <?php checked( ! empty( $options['keep_original_copy'] ), true ); ?>>
									<?php esc_html_e( 'Conserver une copie de l\'original avant redimensionnement.', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer les options AVIF', 'greenlight' ) ); ?>
				</form>
			</section>
		</aside>
	</div>

	<script>
	( function() {
		var startBtn  = document.getElementById( "gl-bulk-start" );
		var batchSel  = document.getElementById( "gl-batch-size" );
		var progress  = document.getElementById( "gl-bulk-progress" );
		var bar       = document.getElementById( "gl-bulk-bar" );
		var statusEl  = document.getElementById( "gl-bulk-status" );

		if ( ! startBtn ) return;

		startBtn.addEventListener( "click", function() {
			startBtn.disabled = true;
			progress.style.display = "block";
			run( 0, parseInt( batchSel.value, 10 ) );
		} );

		function run( offset, batchSize ) {
			var formData = new FormData();
			formData.append( "action", "greenlight_bulk_optimize" );
			formData.append( "nonce", "<?php echo esc_js( wp_create_nonce( 'greenlight_bulk_optimize' ) ); ?>" );
			formData.append( "offset", offset );
			formData.append( "batch_size", batchSize );

			var xhr = new XMLHttpRequest();
			xhr.open( "POST", "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" );
			xhr.onload = function() {
				var resp;
				try { resp = JSON.parse( xhr.responseText ); } catch( e ) { return; }
				if ( ! resp.success ) { statusEl.textContent = "Erreur"; return; }
				var d = resp.data;
				var pct = d.total > 0 ? Math.round( ( d.offset / d.total ) * 100 ) : 100;
				bar.style.width = pct + "%";
				statusEl.textContent = d.offset + " / " + d.total;
				if ( ! d.done ) {
					run( d.offset, batchSize );
				} else {
					statusEl.textContent += " — <?php echo esc_js( __( 'Terminé', 'greenlight' ) ); ?>";
					startBtn.disabled = false;
				}
			};
			xhr.send( formData );
		}
	}() );
	</script>
	<?php
}

/**
 * Renders the Performance tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_performance() {
	$options          = get_option( GREENLIGHT_PERF_OPTION_KEY, greenlight_get_performance_defaults() );
	$stats            = greenlight_get_cache_stats();
	$theme_dir        = get_stylesheet_directory();
	$perf_fields      = array_keys( greenlight_get_performance_defaults() );
	$bundle_path      = $theme_dir . '/assets/css/greenlight-bundle.css';
	$critical_path    = get_theme_file_path( 'assets/css/critical.css' );
	$bundle_exists    = file_exists( $bundle_path );
	$critical_exists  = file_exists( $critical_path );
	$detected_domains = get_transient( 'greenlight_detected_domains' );
	$server_software  = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
	$heartbeat_labels = array(
		'default' => __( 'Par defaut', 'greenlight' ),
		'reduce'  => __( 'Reduit', 'greenlight' ),
		'disable' => __( 'Coupe', 'greenlight' ),
	);
	$lifetimes        = array(
		3600   => __( '1 heure', 'greenlight' ),
		21600  => __( '6 heures', 'greenlight' ),
		43200  => __( '12 heures', 'greenlight' ),
		86400  => __( '24 heures', 'greenlight' ),
		604800 => __( '1 semaine', 'greenlight' ),
	);
	$min_files        = array(
		'style.min.css'                => $theme_dir . '/style.min.css',
		'assets/js/seo-sidebar.min.js' => $theme_dir . '/assets/js/seo-sidebar.min.js',
	);
	$cleanup_tasks    = array(
		'revisions'  => __( 'Supprimer les revisions', 'greenlight' ),
		'autodraft'  => __( 'Supprimer les brouillons auto', 'greenlight' ),
		'trash'      => __( 'Vider la corbeille', 'greenlight' ),
		'spam'       => __( 'Supprimer les commentaires spam', 'greenlight' ),
		'transients' => __( 'Supprimer les transients expires', 'greenlight' ),
		'optimize'   => __( 'Optimiser les tables', 'greenlight' ),
	);
	$server_note      = '';

	foreach ( array( 'navigation', 'image', 'heading', 'paragraph', 'separator', 'button', 'group', 'query' ) as $block_slug ) {
		$min_files[ 'blocks/' . $block_slug . '.min.css' ] = $theme_dir . '/assets/css/blocks/' . $block_slug . '.min.css';
	}

	if ( false !== stripos( $server_software, 'nginx' ) ) {
		$server_note = __( 'nginx détecté : gardez la compression et les headers de cache actifs pour diffuser moins de poids.', 'greenlight' );
	} elseif ( false !== stripos( $server_software, 'apache' ) ) {
		$server_note = __( 'Apache détecté : activez mod_deflate, mod_expires et les headers de cache pour garder une diffusion sobre.', 'greenlight' );
	} else {
		/* translators: %s: server software string. */
		$server_note = sprintf( __( 'Serveur détecté : %s.', 'greenlight' ), '' !== $server_software ? $server_software : __( 'inconnu', 'greenlight' ) );
	}

	$emit_perf_hidden_fields = static function ( array $exclude ) use ( $perf_fields, $options ) {
		foreach ( $perf_fields as $perf_field ) {
			if ( in_array( $perf_field, $exclude, true ) || ! isset( $options[ $perf_field ] ) ) {
				continue;
			}

			$value = $options[ $perf_field ];

			if ( is_numeric( $value ) ) {
				if ( 0 === (int) $value ) {
					continue;
				}

				echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_PERF_OPTION_KEY ) . '[' . esc_attr( $perf_field ) . ']" value="' . esc_attr( $value ) . '">';
				continue;
			}

			echo '<input type="hidden" name="' . esc_attr( GREENLIGHT_PERF_OPTION_KEY ) . '[' . esc_attr( $perf_field ) . ']" value="' . esc_attr( $value ) . '">';
		}
	};

	// Notifications GET.
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['purged'] ) && '1' === $_GET['purged'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Cache purge avec succes.', 'greenlight' ) . '</p></div>';
	}
	if ( isset( $_GET['regen'] ) && '1' === $_GET['regen'] ) {
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Fichiers minifies supprimes: ils seront regeneres au prochain chargement.', 'greenlight' ) . '</p></div>';
	}
	// phpcs:enable
	?>
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Performance', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'Cache et diffusion', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Cache, minification et maintenance.', 'greenlight' ); ?></p>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--performance">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Impact actuel', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Vue d’ensemble', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'État du cache et des fichiers servis.', 'greenlight' ); ?></p>
					</div>
				</div>
				<div class="greenlight-admin-metrics">
					<div class="greenlight-admin-metric">
						<span class="greenlight-admin-metric__label"><?php esc_html_e( 'Cache HTML', 'greenlight' ); ?></span>
						<span class="greenlight-admin-metric__value"><?php echo esc_html( ! empty( $options['enable_page_cache'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ) ); ?></span>
					</div>
					<div class="greenlight-admin-metric">
						<span class="greenlight-admin-metric__label"><?php esc_html_e( 'Diffusion CSS', 'greenlight' ); ?></span>
						<span class="greenlight-admin-metric__value"><?php echo esc_html( ! empty( $options['enable_css_min'] ) ? __( 'Minifiée', 'greenlight' ) : __( 'Source', 'greenlight' ) ); ?></span>
					</div>
					<div class="greenlight-admin-metric">
						<span class="greenlight-admin-metric__label"><?php esc_html_e( 'Bundle CSS', 'greenlight' ); ?></span>
						<span class="greenlight-admin-metric__value"><?php echo esc_html( ! empty( $options['enable_concat'] ) ? __( 'Actif', 'greenlight' ) : __( 'Désactivé', 'greenlight' ) ); ?></span>
					</div>
					<div class="greenlight-admin-metric">
						<span class="greenlight-admin-metric__label"><?php esc_html_e( 'Entretien auto', 'greenlight' ); ?></span>
						<span class="greenlight-admin-metric__value"><?php echo esc_html( ! empty( $options['enable_auto_cleanup'] ) ? __( 'Hebdomadaire', 'greenlight' ) : __( 'Manuel', 'greenlight' ) ); ?></span>
					</div>
				</div>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Diffusion', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Fichiers servis', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Réduisez le poids servi. Minification, bundle CSS et critical CSS.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_performance' ); ?>
					<?php $emit_perf_hidden_fields( array( 'enable_css_min', 'enable_js_min', 'enable_critical_css', 'enable_concat' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Minification CSS', 'greenlight' ); ?></th>
							<td>
								<label for="gl-css-min">
									<input id="gl-css-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_css_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_css_min'], 1 ); ?>>
									<?php esc_html_e( 'Servir les fichiers .min.css.', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Minification JS', 'greenlight' ); ?></th>
							<td>
								<label for="gl-js-min">
									<input id="gl-js-min" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_js_min]" type="checkbox" value="1" <?php checked( (int) $options['enable_js_min'], 1 ); ?>>
									<?php esc_html_e( 'Servir les fichiers .min.js.', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Bundle CSS', 'greenlight' ); ?></th>
							<td>
								<label for="gl-concat">
									<input id="gl-concat" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_concat]" type="checkbox" value="1" <?php checked( ! empty( $options['enable_concat'] ), true ); ?>>
									<?php esc_html_e( 'Fusionner style.css et les blocs.', 'greenlight' ); ?>
								</label>
								<?php if ( $bundle_exists ) : ?>
									<p class="description"><?php esc_html_e( 'Bundle genere.', 'greenlight' ); ?> (<?php echo esc_html( size_format( (int) filesize( $bundle_path ), 1 ) ); ?>)</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Critical CSS', 'greenlight' ); ?></th>
							<td>
								<label for="gl-critical-css">
									<input id="gl-critical-css" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_critical_css]" type="checkbox" value="1" <?php checked( ! empty( $options['enable_critical_css'] ), true ); ?>>
									<?php esc_html_e( 'Inliner le CSS critique.', 'greenlight' ); ?>
								</label>
								<?php if ( ! $critical_exists ) : ?>
									<p class="description"><?php esc_html_e( 'critical.css absent.', 'greenlight' ); ?></p>
								<?php endif; ?>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer la diffusion', 'greenlight' ) ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Cache HTML', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Pages statiques servies', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Le cache HTML allège le serveur.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_performance' ); ?>
					<?php $emit_perf_hidden_fields( array( 'enable_page_cache', 'cache_lifetime' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Cache page HTML', 'greenlight' ); ?></th>
							<td>
								<label for="gl-page-cache">
									<input id="gl-page-cache" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_page_cache]" type="checkbox" value="1" <?php checked( (int) $options['enable_page_cache'], 1 ); ?>>
									<?php esc_html_e( 'Activer le cache statique HTML pour les visites anonymes.', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-lifetime"><?php esc_html_e( 'Duree de vie du cache', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-lifetime" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[cache_lifetime]">
									<?php foreach ( $lifetimes as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $options['cache_lifetime'], $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Adaptez à votre rythme.', 'greenlight' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer le cache', 'greenlight' ), 'secondary' ); ?>
				</form>
				<div class="greenlight-admin-tab-panel__actions">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="greenlight_purge_cache">
						<?php wp_nonce_field( 'greenlight_purge_cache' ); ?>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Purger le cache HTML', 'greenlight' ); ?></button>
					</form>
				</div>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Rythme', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Connexions et rythme', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Connexions externes et Heartbeat.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_performance' ); ?>
					<?php $emit_perf_hidden_fields( array( 'prefetch_domains', 'heartbeat_front', 'heartbeat_admin', 'heartbeat_editor' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="gl-prefetch"><?php esc_html_e( 'Domaines externes', 'greenlight' ); ?></label></th>
							<td>
								<textarea id="gl-prefetch" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[prefetch_domains]" class="large-text code" rows="4" placeholder="https://fonts.googleapis.com"><?php echo esc_textarea( isset( $options['prefetch_domains'] ) ? $options['prefetch_domains'] : '' ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Un domaine par ligne. Détection possible.', 'greenlight' ); ?></p>
								<?php if ( is_array( $detected_domains ) && ! empty( $detected_domains ) ) : ?>
									<p class="description">
										<?php esc_html_e( 'Domaines detectes :', 'greenlight' ); ?>
										<code><?php echo esc_html( implode( ', ', $detected_domains ) ); ?></code>
									</p>
								<?php endif; ?>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-hb-front"><?php esc_html_e( 'Heartbeat front', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hb-front" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[heartbeat_front]">
									<?php foreach ( $heartbeat_labels as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $options['heartbeat_front'] ) ? $options['heartbeat_front'] : 'default', $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-hb-admin"><?php esc_html_e( 'Heartbeat admin', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hb-admin" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[heartbeat_admin]">
									<?php foreach ( $heartbeat_labels as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $options['heartbeat_admin'] ) ? $options['heartbeat_admin'] : 'default', $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="gl-hb-editor"><?php esc_html_e( 'Heartbeat editeur', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hb-editor" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[heartbeat_editor]">
									<?php foreach ( $heartbeat_labels as $value => $label ) : ?>
										<option value="<?php echo esc_attr( $value ); ?>" <?php selected( isset( $options['heartbeat_editor'] ) ? $options['heartbeat_editor'] : 'default', $value ); ?>><?php echo esc_html( $label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer le rythme', 'greenlight' ), 'secondary' ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Maintenance', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Nettoyage et régénération', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Actions ponctuelles et routines.', 'greenlight' ); ?></p>
					</div>
				</div>
				<?php
				// phpcs:disable WordPress.Security.NonceVerification.Recommended
				if ( isset( $_GET['cleanup'] ) && isset( $_GET['cleaned'] ) ) {
					/* translators: 1: cleanup task slug 2: number of cleaned items. */
					$cleanup_message = esc_html__( 'Nettoyage "%1$s" termine: %2$d element(s) traite(s).', 'greenlight' );
					printf(
						'<div class="notice notice-success is-dismissible"><p>' . esc_html( $cleanup_message ) . '</p></div>',
						esc_html( sanitize_key( $_GET['cleanup'] ) ),
						absint( $_GET['cleaned'] )
					);
				}
				// phpcs:enable
				?>
				<div class="greenlight-admin-tab-panel__actions">
					<?php foreach ( $cleanup_tasks as $task_key => $task_label ) : ?>
						<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
							<input type="hidden" name="action" value="greenlight_db_cleanup">
							<input type="hidden" name="cleanup_task" value="<?php echo esc_attr( $task_key ); ?>">
							<?php wp_nonce_field( 'greenlight_db_cleanup' ); ?>
							<button type="submit" class="button button-secondary"><?php echo esc_html( $task_label ); ?></button>
						</form>
					<?php endforeach; ?>
				</div>

				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_performance' ); ?>
					<?php $emit_perf_hidden_fields( array( 'enable_auto_cleanup' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Nettoyage auto', 'greenlight' ); ?></th>
							<td>
								<label for="gl-auto-cleanup">
									<input id="gl-auto-cleanup" name="<?php echo esc_attr( GREENLIGHT_PERF_OPTION_KEY ); ?>[enable_auto_cleanup]" type="checkbox" value="1" <?php checked( ! empty( $options['enable_auto_cleanup'] ), true ); ?>>
									<?php esc_html_e( 'Executer un cycle hebdomadaire sur revisions, brouillons, corbeille, spam et transients expires.', 'greenlight' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer la maintenance', 'greenlight' ), 'secondary' ); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Fichiers generes', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Sorties générées', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Vérifiez avant régénération.', 'greenlight' ); ?></p>
					</div>
				</div>
				<table class="widefat striped">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Fichier', 'greenlight' ); ?></th>
							<th><?php esc_html_e( 'Statut', 'greenlight' ); ?></th>
							<th><?php esc_html_e( 'Taille', 'greenlight' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $min_files as $label => $path ) : ?>
							<?php
							$exists       = file_exists( $path );
							$status_class = $exists ? 'greenlight-admin-state greenlight-admin-state--ok' : 'greenlight-admin-state greenlight-admin-state--off';
							$status_label = $exists ? __( 'Present', 'greenlight' ) : __( 'Absent', 'greenlight' );
							$size_label   = $exists ? size_format( (int) filesize( $path ), 1 ) : '—';
							$mtime_markup = '';

							if ( $exists ) {
								$mtime_markup = ' <small class="greenlight-admin-tab-panel__meta">(' . esc_html( gmdate( 'd/m H:i', filemtime( $path ) ) ) . ')</small>';
							}
							?>
							<tr>
								<td><code><?php echo esc_html( $label ); ?></code></td>
								<td>
									<span class="<?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span>
									<?php echo wp_kses_post( $mtime_markup ); ?>
								</td>
								<td><?php echo esc_html( $size_label ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<div class="greenlight-admin-tab-panel__actions">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
						<input type="hidden" name="action" value="greenlight_regen_min">
						<?php wp_nonce_field( 'greenlight_regen_min' ); ?>
						<button type="submit" class="button button-secondary"><?php esc_html_e( 'Supprimer les sorties minifiees', 'greenlight' ); ?></button>
					</form>
				</div>
			</section>
		</div>

		<aside class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'État actuel', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Diffusion et cache', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'État servi.', 'greenlight' ); ?></p>
					</div>
					</div>
					<ul class="greenlight-admin-tab-panel__list">
						<?php /* translators: %s: cache HTML status. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Cache HTML: %s', 'greenlight' ), ! empty( $options['enable_page_cache'] ) ? __( 'actif', 'greenlight' ) : __( 'inactif', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: number of cached pages. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Pages en cache: %s', 'greenlight' ), number_format_i18n( (int) $stats['count'] ) ) ); ?></li>
						<?php /* translators: %s: total cache size. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Poids en cache: %s', 'greenlight' ), size_format( (int) $stats['size'], 2 ) ) ); ?></li>
						<?php /* translators: %s: critical CSS status. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Critical CSS: %s', 'greenlight' ), ! empty( $options['enable_critical_css'] ) ? __( 'actif', 'greenlight' ) : __( 'inactif', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: CSS bundle status. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Bundle CSS: %s', 'greenlight' ), ! empty( $options['enable_concat'] ) ? __( 'actif', 'greenlight' ) : __( 'inactif', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: heartbeat mode label. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Heartbeat front: %s', 'greenlight' ), isset( $heartbeat_labels[ $options['heartbeat_front'] ] ) ? $heartbeat_labels[ $options['heartbeat_front'] ] : $heartbeat_labels['default'] ) ); ?></li>
					</ul>
			</section>

			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Impact', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Effets des réglages', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Chaque option doit rester lisible.', 'greenlight' ); ?></p>
					</div>
				</div>
				<ul class="greenlight-admin-tab-panel__list">
					<li><?php esc_html_e( 'La minification et le bundle CSS reduisent le volume et le nombre de fichiers servis.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Le cache HTML diminue le travail serveur sur les visites anonymes.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Le prefetch et le preconnect anticipent certaines connexions externes utiles.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Le Heartbeat et le nettoyage base de donnees protègent la stabilite de l’administration dans le temps.', 'greenlight' ); ?></li>
				</ul>
			</section>

			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Infrastructure', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Environnement serveur', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php echo esc_html( $server_note ); ?></p>
					</div>
					</div>
					<ul class="greenlight-admin-tab-panel__list">
						<?php /* translators: %s: server software string. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Serveur: %s', 'greenlight' ), '' !== $server_software ? $server_software : __( 'inconnu', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: cache lifetime label. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Cache courant: %s', 'greenlight' ), isset( $lifetimes[ (int) $options['cache_lifetime'] ] ) ? $lifetimes[ (int) $options['cache_lifetime'] ] : __( '1 heure', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: bundle presence label. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Bundle genere: %s', 'greenlight' ), $bundle_exists ? __( 'oui', 'greenlight' ) : __( 'non', 'greenlight' ) ) ); ?></li>
						<?php /* translators: %s: critical CSS file presence label. */ ?>
						<li><?php echo esc_html( sprintf( __( 'Critical CSS present: %s', 'greenlight' ), $critical_exists ? __( 'oui', 'greenlight' ) : __( 'non', 'greenlight' ) ) ); ?></li>
					</ul>
			</section>
		</aside>
	</div>
	<?php
}

/**
 * Renders the Appearance tab.
 *
 * @return void
 */
function greenlight_render_admin_tab_appearance() {
	$o                                        = get_option( GREENLIGHT_APPEARANCE_OPTION_KEY, array() );
	$def                                      = greenlight_get_appearance_defaults();
	$o                                        = array_merge( $def, $o );
	$appearance_presets                       = greenlight_get_appearance_presets();
	$appearance_densities                     = greenlight_get_appearance_densities();
	$archive_card_styles                      = greenlight_get_archive_card_styles();
	$single_layouts                           = greenlight_get_single_layout_variants();
	$footer_layouts                           = greenlight_get_footer_layout_variants();
	$hero_gradients                           = greenlight_get_hero_gradient_presets();
	$hero_background_labels                   = array(
		'none'     => __( 'Aucun', 'greenlight' ),
		'color'    => __( 'Couleur', 'greenlight' ),
		'gradient' => __( 'Dégradé', 'greenlight' ),
		'image'    => __( 'Image', 'greenlight' ),
	);
	$hero_height_labels                       = array(
		'content' => __( 'Contenu', 'greenlight' ),
		'tall'    => __( '70vh', 'greenlight' ),
		'full'    => __( '100vh', 'greenlight' ),
	);
	$key                                      = GREENLIGHT_APPEARANCE_OPTION_KEY;
	$appearance_fields                        = array(
		'theme_preset',
		'density_scale',
		'carbon_badge_enabled',
		'carbon_badge_value',
		'newsletter_enabled',
		'color_primary',
		'color_background',
		'color_surface',
		'color_text',
		'color_tertiary',
		'color_border',
		'color_on_surface_variant',
		'carbon_badge_position',
		'color_header_bg',
		'color_header_text',
		'color_header_accent',
		'header_layout',
		'header_sticky',
		'nav_link_case',
		'submenu_style',
		'show_tagline',
		'hero_enabled',
		'hero_style',
		'hero_background_mode',
		'hero_background_image',
		'hero_background_color',
		'hero_gradient_preset',
		'hero_heading_mode',
		'hero_heading_text',
		'hero_subheading_mode',
		'hero_subheading_text',
		'hero_height_mode',
		'hero_overlay_strength',
		'show_hero_badge',
		'hero_text',
		'show_date',
		'show_author',
		'show_tags',
		'show_newsletter_single',
		'archive_layout',
		'archive_card_style',
		'show_excerpts_archive',
		'show_thumbnails_archive',
		'single_layout',
		'color_footer_bg',
		'footer_layout',
		'show_low_emission',
		'custom_copyright',
		'show_footer_nav',
	);
	$greenlight_emit_appearance_hidden_fields = static function ( array $exclude ) use ( $appearance_fields, $key, $o ) {
		foreach ( $appearance_fields as $appearance_field ) {
			if ( in_array( $appearance_field, $exclude, true ) || ! isset( $o[ $appearance_field ] ) ) {
				continue;
			}
			echo '<input type="hidden" name="' . esc_attr( $key ) . '[' . esc_attr( $appearance_field ) . ']" value="' . esc_attr( $o[ $appearance_field ] ) . '">';
		}
	};
	?>
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Apparence', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'Apparence du site', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Réglez le style, les cartes, l’article, le hero et le badge CO₂.', 'greenlight' ); ?></p>
		</div>
	</div>

	<section class="greenlight-admin-tab-panel__preview">
		<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
			<div class="greenlight-admin-tab-panel__card-head">
				<div>
					<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Prévisualisation', 'greenlight' ); ?></p>
					<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Rendu du site', 'greenlight' ); ?></h3>
					<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Aperçu du rendu.', 'greenlight' ); ?></p>
				</div>
			</div>
			<iframe id="greenlight-preview-frame"
					src="<?php echo esc_url( add_query_arg( 'greenlight_preview', 'appearance', home_url( '/' ) ) ); ?>"
					title="<?php esc_attr_e( 'Prévisualisation du site', 'greenlight' ); ?>"></iframe>
		</section>
	</section>

	<div class="greenlight-admin-tab-panel__summary">
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Style', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( isset( $appearance_presets[ $o['theme_preset'] ]['label'] ) ? $appearance_presets[ $o['theme_preset'] ]['label'] : $appearance_presets['editorial']['label'] ); ?></strong>
			<span><?php esc_html_e( 'Largeur et rythme du site.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Rythme', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( isset( $appearance_densities[ $o['density_scale'] ]['label'] ) ? $appearance_densities[ $o['density_scale'] ]['label'] : $appearance_densities['balanced']['label'] ); ?></strong>
			<span><?php esc_html_e( 'Respiration entre les blocs.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Fond hero', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $o['hero_enabled'] ) ? ( isset( $hero_background_labels[ $o['hero_background_mode'] ] ) ? $hero_background_labels[ $o['hero_background_mode'] ] : $hero_background_labels['none'] ) : __( 'Simple', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Mode d’entrée.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Hauteur', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $o['hero_enabled'] ) ? ( isset( $hero_height_labels[ $o['hero_height_mode'] ] ) ? $hero_height_labels[ $o['hero_height_mode'] ] : $hero_height_labels['content'] ) : __( 'Auto', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'Emprise du hero.', 'greenlight' ); ?></span>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--appearance">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Fondations', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Style, rythme et badge CO₂', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Choisissez le style éditorial, l’espace entre les blocs et l’emplacement du badge.', 'greenlight' ); ?></p>
					</div>
				</div>
				<div class="greenlight-admin-appearance-guide" aria-hidden="true">
					<div class="greenlight-admin-appearance-guide__item">
						<span class="greenlight-admin-appearance-guide__icon"><span class="dashicons dashicons-admin-site-alt3"></span></span>
						<strong><?php esc_html_e( 'Style éditorial', 'greenlight' ); ?></strong>
						<span><?php esc_html_e( 'Magazine, studio ou journal.', 'greenlight' ); ?></span>
					</div>
					<div class="greenlight-admin-appearance-guide__item">
						<span class="greenlight-admin-appearance-guide__icon"><span class="dashicons dashicons-align-wide"></span></span>
						<strong><?php esc_html_e( 'Rythme visuel', 'greenlight' ); ?></strong>
						<span><?php esc_html_e( 'Aéré, équilibré ou compact.', 'greenlight' ); ?></span>
					</div>
					<div class="greenlight-admin-appearance-guide__item">
						<span class="greenlight-admin-appearance-guide__icon"><span class="dashicons dashicons-chart-area"></span></span>
						<strong><?php esc_html_e( 'Badge CO₂', 'greenlight' ); ?></strong>
						<span><?php esc_html_e( 'En haut de page ou dans le footer.', 'greenlight' ); ?></span>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'theme_preset', 'density_scale', 'carbon_badge_enabled', 'carbon_badge_value', 'carbon_badge_position', 'newsletter_enabled', 'color_primary', 'color_background', 'color_surface', 'color_text', 'color_tertiary', 'color_border', 'color_on_surface_variant' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<span class="greenlight-admin-field-illustration greenlight-admin-field-illustration--preset" aria-hidden="true"><span class="dashicons dashicons-admin-page"></span></span>
								<label for="gl-theme-preset"><?php esc_html_e( 'Style éditorial', 'greenlight' ); ?></label>
							</th>
							<td>
								<select id="gl-theme-preset" name="<?php echo esc_attr( $key ); ?>[theme_preset]">
									<?php foreach ( $appearance_presets as $preset_key => $preset ) : ?>
										<option value="<?php echo esc_attr( $preset_key ); ?>" <?php selected( isset( $o['theme_preset'] ) ? $o['theme_preset'] : 'editorial', $preset_key ); ?>><?php echo esc_html( $preset['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Définit la largeur, les rayons et l’allure générale.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<span class="greenlight-admin-field-illustration greenlight-admin-field-illustration--density" aria-hidden="true"><span class="dashicons dashicons-editor-expand"></span></span>
								<label for="gl-density-scale"><?php esc_html_e( 'Rythme de page', 'greenlight' ); ?></label>
							</th>
							<td>
								<select id="gl-density-scale" name="<?php echo esc_attr( $key ); ?>[density_scale]">
									<?php foreach ( $appearance_densities as $density_key => $density ) : ?>
										<option value="<?php echo esc_attr( $density_key ); ?>" <?php selected( isset( $o['density_scale'] ) ? $o['density_scale'] : 'balanced', $density_key ); ?>><?php echo esc_html( $density['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Ajuste l’espace entre les blocs et la respiration visuelle.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<span class="greenlight-admin-field-illustration greenlight-admin-field-illustration--carbon" aria-hidden="true"><span class="dashicons dashicons-chart-line"></span></span>
								<label><?php esc_html_e( 'Badge CO₂', 'greenlight' ); ?></label>
							</th>
							<td>
								<label><input name="<?php echo esc_attr( $key ); ?>[carbon_badge_enabled]" type="checkbox" value="1" <?php checked( (int) $o['carbon_badge_enabled'], 1 ); ?>> <?php esc_html_e( 'Afficher le badge CO₂', 'greenlight' ); ?></label><br>
								<label style="margin-top:.4em;display:block"><?php esc_html_e( 'Valeur manuelle :', 'greenlight' ); ?>
									<input name="<?php echo esc_attr( $key ); ?>[carbon_badge_value]" type="text" class="small-text" value="<?php echo esc_attr( $o['carbon_badge_value'] ); ?>" placeholder="0.2g">
								</label>
								<label style="margin-top:.4em;display:block"><?php esc_html_e( 'Emplacement :', 'greenlight' ); ?>
									<select name="<?php echo esc_attr( $key ); ?>[carbon_badge_position]">
										<?php foreach ( greenlight_get_carbon_badge_positions() as $position_key => $position ) : ?>
											<option value="<?php echo esc_attr( $position_key ); ?>" <?php selected( isset( $o['carbon_badge_position'] ) ? $o['carbon_badge_position'] : 'top', $position_key ); ?>><?php echo esc_html( $position['label'] ); ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<p class="description">
									<?php esc_html_e( 'Vide = 0.2g. Lien EcoIndex :', 'greenlight' ); ?>
									<a href="https://www.ecoindex.fr/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'calculer la page', 'greenlight' ); ?></a>.
								</p>
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
					<?php submit_button(); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Navigation', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Header et menu', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Header, tagline et sous-menus.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'color_header_bg', 'color_header_text', 'color_header_accent', 'header_layout', 'header_sticky', 'nav_link_case', 'submenu_style', 'show_tagline' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th><label for="gl-header-layout"><?php esc_html_e( 'Layout header', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-header-layout" name="<?php echo esc_attr( $key ); ?>[header_layout]">
									<option value="inline" <?php selected( $o['header_layout'], 'inline' ); ?>><?php esc_html_e( 'Ligne simple', 'greenlight' ); ?></option>
									<option value="split" <?php selected( $o['header_layout'], 'split' ); ?>><?php esc_html_e( 'Séparé', 'greenlight' ); ?></option>
									<option value="stacked" <?php selected( $o['header_layout'], 'stacked' ); ?>><?php esc_html_e( 'Empilé', 'greenlight' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Branding, menu et CTA.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Sticky', 'greenlight' ); ?></th>
							<td><label><input name="<?php echo esc_attr( $key ); ?>[header_sticky]" type="checkbox" value="1" <?php checked( (int) $o['header_sticky'], 1 ); ?>> <?php esc_html_e( 'Header collant', 'greenlight' ); ?></label></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Tagline', 'greenlight' ); ?></th>
							<td><label><input name="<?php echo esc_attr( $key ); ?>[show_tagline]" type="checkbox" value="1" <?php checked( (int) $o['show_tagline'], 1 ); ?>> <?php esc_html_e( 'Afficher la description du site', 'greenlight' ); ?></label></td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Fond header', 'greenlight' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( $key ); ?>[color_header_bg]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_bg'] ); ?>" data-default-color="#faf9f4"></td>
						</tr>
						<tr>
							<th><label for="gl-header-text"><?php esc_html_e( 'Texte header', 'greenlight' ); ?></label></th>
							<td><input type="text" id="gl-header-text" name="<?php echo esc_attr( $key ); ?>[color_header_text]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_text'] ); ?>" data-default-color="#2f342d"></td>
						</tr>
						<tr>
							<th><label for="gl-header-accent"><?php esc_html_e( 'Accent header', 'greenlight' ); ?></label></th>
							<td><input type="text" id="gl-header-accent" name="<?php echo esc_attr( $key ); ?>[color_header_accent]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['color_header_accent'] ); ?>" data-default-color="#4c6547"></td>
						</tr>
						<tr>
							<th><label for="gl-nav-case"><?php esc_html_e( 'Casse menu', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-nav-case" name="<?php echo esc_attr( $key ); ?>[nav_link_case]">
									<option value="normal" <?php selected( $o['nav_link_case'], 'normal' ); ?>><?php esc_html_e( 'Normale', 'greenlight' ); ?></option>
									<option value="uppercase" <?php selected( $o['nav_link_case'], 'uppercase' ); ?>><?php esc_html_e( 'Majuscules', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-submenu-style"><?php esc_html_e( 'Sous-menus', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-submenu-style" name="<?php echo esc_attr( $key ); ?>[submenu_style]">
									<option value="plain" <?php selected( $o['submenu_style'], 'plain' ); ?>><?php esc_html_e( 'Discrets', 'greenlight' ); ?></option>
									<option value="surface" <?php selected( $o['submenu_style'], 'surface' ); ?>><?php esc_html_e( 'En surface', 'greenlight' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Dropdowns CSS-only.', 'greenlight' ); ?></p>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Structure', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Hero et introduction', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Type d’entrée, fond et texte.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'hero_enabled', 'hero_style', 'hero_background_mode', 'hero_background_image', 'hero_background_color', 'hero_gradient_preset', 'hero_heading_mode', 'hero_heading_text', 'hero_subheading_mode', 'hero_subheading_text', 'hero_height_mode', 'hero_overlay_strength', 'show_hero_badge', 'hero_text' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th><?php esc_html_e( 'Hero', 'greenlight' ); ?></th>
							<td>
								<label><input name="<?php echo esc_attr( $key ); ?>[hero_enabled]" type="checkbox" value="1" <?php checked( (int) $o['hero_enabled'], 1 ); ?>> <?php esc_html_e( 'Utiliser le hero avancé', 'greenlight' ); ?></label>
								<p class="description"><?php esc_html_e( 'Désactivé = intro simple.', 'greenlight' ); ?></p>
							</td>
						</tr>
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
							<th><label for="gl-hero-background-mode"><?php esc_html_e( 'Fond hero', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-background-mode" name="<?php echo esc_attr( $key ); ?>[hero_background_mode]">
									<?php foreach ( $hero_background_labels as $background_key => $background_label ) : ?>
										<option value="<?php echo esc_attr( $background_key ); ?>" <?php selected( $o['hero_background_mode'], $background_key ); ?>><?php echo esc_html( $background_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Couleur hero', 'greenlight' ); ?></th>
							<td><input type="text" name="<?php echo esc_attr( $key ); ?>[hero_background_color]" class="greenlight-color-picker" value="<?php echo esc_attr( $o['hero_background_color'] ); ?>" data-default-color="#dfe6d7"></td>
						</tr>
						<tr>
							<th><label for="gl-hero-gradient"><?php esc_html_e( 'Dégradé hero', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-gradient" name="<?php echo esc_attr( $key ); ?>[hero_gradient_preset]">
									<?php foreach ( $hero_gradients as $gradient_key => $gradient_data ) : ?>
										<option value="<?php echo esc_attr( $gradient_key ); ?>" <?php selected( $o['hero_gradient_preset'], $gradient_key ); ?>><?php echo esc_html( $gradient_data['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-image"><?php esc_html_e( 'Image hero', 'greenlight' ); ?></label></th>
							<td>
								<input id="gl-hero-image" name="<?php echo esc_attr( $key ); ?>[hero_background_image]" type="url" class="regular-text code" value="<?php echo esc_attr( $o['hero_background_image'] ); ?>" placeholder="https://">
								<p class="description"><?php esc_html_e( 'URL d’image de fond.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-heading-mode"><?php esc_html_e( 'Titre hero', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-heading-mode" name="<?php echo esc_attr( $key ); ?>[hero_heading_mode]">
									<option value="page_title" <?php selected( $o['hero_heading_mode'], 'page_title' ); ?>><?php esc_html_e( 'Titre de page', 'greenlight' ); ?></option>
									<option value="site_title" <?php selected( $o['hero_heading_mode'], 'site_title' ); ?>><?php esc_html_e( 'Titre du site', 'greenlight' ); ?></option>
									<option value="custom" <?php selected( $o['hero_heading_mode'], 'custom' ); ?>><?php esc_html_e( 'Titre personnalisé', 'greenlight' ); ?></option>
									<option value="none" <?php selected( $o['hero_heading_mode'], 'none' ); ?>><?php esc_html_e( 'Aucun titre', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-heading-text"><?php esc_html_e( 'Titre personnalisé', 'greenlight' ); ?></label></th>
							<td><input id="gl-hero-heading-text" name="<?php echo esc_attr( $key ); ?>[hero_heading_text]" type="text" class="regular-text" value="<?php echo esc_attr( $o['hero_heading_text'] ); ?>"></td>
						</tr>
						<tr>
							<th><label for="gl-hero-subheading-mode"><?php esc_html_e( 'Sous-titre hero', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-subheading-mode" name="<?php echo esc_attr( $key ); ?>[hero_subheading_mode]">
									<option value="page_excerpt" <?php selected( $o['hero_subheading_mode'], 'page_excerpt' ); ?>><?php esc_html_e( 'Extrait de page', 'greenlight' ); ?></option>
									<option value="site_tagline" <?php selected( $o['hero_subheading_mode'], 'site_tagline' ); ?>><?php esc_html_e( 'Slogan du site', 'greenlight' ); ?></option>
									<option value="custom" <?php selected( $o['hero_subheading_mode'], 'custom' ); ?>><?php esc_html_e( 'Texte personnalisé', 'greenlight' ); ?></option>
									<option value="none" <?php selected( $o['hero_subheading_mode'], 'none' ); ?>><?php esc_html_e( 'Aucun sous-titre', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-subheading-text"><?php esc_html_e( 'Sous-titre personnalisé', 'greenlight' ); ?></label></th>
							<td>
								<textarea id="gl-hero-subheading-text" name="<?php echo esc_attr( $key ); ?>[hero_subheading_text]" class="large-text" rows="3"><?php echo esc_textarea( $o['hero_subheading_text'] ); ?></textarea>
								<p class="description"><?php esc_html_e( 'Le texte historique reste en secours.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-height"><?php esc_html_e( 'Hauteur hero', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-height" name="<?php echo esc_attr( $key ); ?>[hero_height_mode]">
									<?php foreach ( $hero_height_labels as $height_key => $height_label ) : ?>
										<option value="<?php echo esc_attr( $height_key ); ?>" <?php selected( $o['hero_height_mode'], $height_key ); ?>><?php echo esc_html( $height_label ); ?></option>
									<?php endforeach; ?>
								</select>
							</td>
						</tr>
						<tr>
							<th><label for="gl-hero-overlay"><?php esc_html_e( 'Overlay', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-hero-overlay" name="<?php echo esc_attr( $key ); ?>[hero_overlay_strength]">
									<option value="none" <?php selected( $o['hero_overlay_strength'], 'none' ); ?>><?php esc_html_e( 'Aucun', 'greenlight' ); ?></option>
									<option value="soft" <?php selected( $o['hero_overlay_strength'], 'soft' ); ?>><?php esc_html_e( 'Léger', 'greenlight' ); ?></option>
									<option value="strong" <?php selected( $o['hero_overlay_strength'], 'strong' ); ?>><?php esc_html_e( 'Soutenu', 'greenlight' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th><?php esc_html_e( 'Carbon Badge', 'greenlight' ); ?></th>
							<td><label><input name="<?php echo esc_attr( $key ); ?>[show_hero_badge]" type="checkbox" value="1" <?php checked( (int) $o['show_hero_badge'], 1 ); ?>> <?php esc_html_e( 'Afficher le badge CO₂ dans le hero', 'greenlight' ); ?></label></td>
						</tr>
						<tr>
							<th><label for="gl-hero-text"><?php esc_html_e( 'Texte de secours', 'greenlight' ); ?></label></th>
							<td>
								<textarea id="gl-hero-text" name="<?php echo esc_attr( $key ); ?>[hero_text]" class="large-text" rows="2" placeholder="<?php esc_attr_e( 'Ancien sous-titre conservé pour compatibilité.', 'greenlight' ); ?>"><?php echo esc_textarea( $o['hero_text'] ); ?></textarea>
							</td>
						</tr>
					</table>
					<?php submit_button(); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Contenu', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Articles et archives', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Lecture des contenus, des cartes et du gabarit article.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'show_date', 'show_author', 'show_tags', 'show_newsletter_single', 'archive_layout', 'archive_card_style', 'show_excerpts_archive', 'show_thumbnails_archive', 'single_layout' ) ); ?>
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
						<tr>
							<th><label for="gl-archive-layout"><?php esc_html_e( 'Archives', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-archive-layout" name="<?php echo esc_attr( $key ); ?>[archive_layout]">
									<option value="asymmetric" <?php selected( $o['archive_layout'], 'asymmetric' ); ?>><?php esc_html_e( 'Grille asymétrique', 'greenlight' ); ?></option>
									<option value="list" <?php selected( $o['archive_layout'], 'list' ); ?>><?php esc_html_e( 'Liste simple', 'greenlight' ); ?></option>
								</select>
								<p class="description"><?php esc_html_e( 'Contrôle la structure des listes d’articles.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="gl-archive-card-style"><?php esc_html_e( 'Cartes d\'archive', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-archive-card-style" name="<?php echo esc_attr( $key ); ?>[archive_card_style]">
									<?php foreach ( $archive_card_styles as $archive_card_style_key => $archive_card_style ) : ?>
										<option value="<?php echo esc_attr( $archive_card_style_key ); ?>" <?php selected( $o['archive_card_style'], $archive_card_style_key ); ?>><?php echo esc_html( $archive_card_style['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Ajuste la matière, l’espace et la tenue des cartes.', 'greenlight' ); ?></p>
							</td>
						</tr>
						<tr>
							<th><label for="gl-single-layout"><?php esc_html_e( 'Article', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-single-layout" name="<?php echo esc_attr( $key ); ?>[single_layout]">
									<?php foreach ( $single_layouts as $single_layout_key => $single_layout ) : ?>
										<option value="<?php echo esc_attr( $single_layout_key ); ?>" <?php selected( $o['single_layout'], $single_layout_key ); ?>><?php echo esc_html( $single_layout['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Définit la largeur et le ton de l’article.', 'greenlight' ); ?></p>
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
					<?php submit_button(); ?>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Pied de page', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Pied de page et mentions', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Mise en page, marque et navigation.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_appearance' ); ?>
					<?php $greenlight_emit_appearance_hidden_fields( array( 'color_footer_bg', 'footer_layout', 'show_low_emission', 'custom_copyright', 'show_footer_nav' ) ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th><label for="gl-footer-layout"><?php esc_html_e( 'Mise en page footer', 'greenlight' ); ?></label></th>
							<td>
								<select id="gl-footer-layout" name="<?php echo esc_attr( $key ); ?>[footer_layout]">
									<?php foreach ( $footer_layouts as $footer_layout_key => $footer_layout ) : ?>
										<option value="<?php echo esc_attr( $footer_layout_key ); ?>" <?php selected( $o['footer_layout'], $footer_layout_key ); ?>><?php echo esc_html( $footer_layout['label'] ); ?></option>
									<?php endforeach; ?>
								</select>
								<p class="description"><?php esc_html_e( 'Aligne mentions et navigation.', 'greenlight' ); ?></p>
							</td>
						</tr>
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
					<?php submit_button(); ?>
				</form>
			</section>
		</div>
	</div>
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
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'SVG', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'SVG sécurisé', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Autorisez les SVG si besoin, puis nettoyez-les.', 'greenlight' ); ?></p>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__summary">
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'SVG', 'greenlight' ); ?></p>
			<strong><?php echo esc_html( ! empty( $options['enable_svg'] ) ? __( 'Actif', 'greenlight' ) : __( 'Inactif', 'greenlight' ) ); ?></strong>
			<span><?php esc_html_e( 'SVG.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'MIME', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'Validé', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Contrôle au chargement.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Sanitisation', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'DOMDocument', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Nettoyage auto.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Portée', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'Médias', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Sans effet sur le front.', 'greenlight' ); ?></span>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--svg">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Contrôle', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Autoriser les SVG', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Le thème valide le MIME puis nettoie les SVG.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="options.php">
					<?php settings_fields( 'greenlight_svg' ); ?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Upload SVG', 'greenlight' ); ?></th>
							<td>
								<label for="gl-svg">
									<input id="gl-svg" name="<?php echo esc_attr( GREENLIGHT_SVG_OPTION_KEY ); ?>[enable_svg]" type="checkbox" value="1" <?php checked( (int) $options['enable_svg'], 1 ); ?>>
									<?php esc_html_e( 'Autoriser l\'upload de fichiers SVG', 'greenlight' ); ?>
								</label>
								<p class="description">
									<?php esc_html_e( 'Nettoyage via DOMDocument.', 'greenlight' ); ?>
								</p>
							</td>
						</tr>
					</table>
					<?php submit_button( __( 'Enregistrer les SVG', 'greenlight' ) ); ?>
				</form>
			</section>
		</div>

		<aside class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Sécurité', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Nettoyage', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Scripts et attributs dangereux retirés.', 'greenlight' ); ?></p>
					</div>
				</div>
				<ul class="greenlight-admin-tab-panel__list">
					<li><?php esc_html_e( 'Balises `script` retirées.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Attributs `on*` et liens `javascript:` retirés.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( '`use` externe bloqué.', 'greenlight' ); ?></li>
				</ul>
			</section>
		</aside>
	</div>
	<?php
}

/**
 * Onglet Outils — Import / Export.
 */

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
	<div class="greenlight-admin-tab-panel__intro">
		<div>
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Outils', 'greenlight' ); ?></p>
			<h2><?php esc_html_e( 'Import / export', 'greenlight' ); ?></h2>
			<p class="greenlight-admin-tab-panel__lead"><?php esc_html_e( 'Exportez et importez les réglages sans toucher au contenu.', 'greenlight' ); ?></p>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__summary">
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Export', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'JSON', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Snapshot.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Import', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'JSON', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Restauration.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Sécurité', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'Nonce', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Protégé.', 'greenlight' ); ?></span>
		</div>
		<div class="greenlight-admin-summary-card">
			<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Portée', 'greenlight' ); ?></p>
			<strong><?php esc_html_e( 'Réglages', 'greenlight' ); ?></strong>
			<span><?php esc_html_e( 'Pas de contenu.', 'greenlight' ); ?></span>
		</div>
	</div>

	<div class="greenlight-admin-tab-panel__shell greenlight-admin-tab-panel__shell--tools">
		<div class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Export', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Exporter le JSON', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Réglages Greenlight.', 'greenlight' ); ?></p>
					</div>
				</div>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="greenlight_export">
					<?php wp_nonce_field( 'greenlight_export' ); ?>
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Exporter', 'greenlight' ); ?></button>
				</form>
			</section>

			<section class="greenlight-admin-tab-panel__card">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Import', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Restaurer un JSON', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Snapshot Greenlight.', 'greenlight' ); ?></p>
					</div>
				</div>
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
			</section>
		</div>

		<aside class="greenlight-admin-tab-panel__column">
			<section class="greenlight-admin-tab-panel__card greenlight-admin-tab-panel__card--soft">
				<div class="greenlight-admin-tab-panel__card-head">
					<div>
						<p class="greenlight-admin-tab-panel__eyebrow"><?php esc_html_e( 'Usage', 'greenlight' ); ?></p>
						<h3 class="greenlight-admin-tab-panel__card-title"><?php esc_html_e( 'Flux recommandé', 'greenlight' ); ?></h3>
						<p class="greenlight-admin-tab-panel__card-note"><?php esc_html_e( 'Export avant changement.', 'greenlight' ); ?></p>
					</div>
				</div>
				<ul class="greenlight-admin-tab-panel__list">
					<li><?php esc_html_e( 'Un export fige l’état courant.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'L’import remplace les réglages actuels.', 'greenlight' ); ?></li>
					<li><?php esc_html_e( 'Contenus, pages et médias restent intacts.', 'greenlight' ); ?></li>
				</ul>
			</section>
		</aside>
	</div>
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
