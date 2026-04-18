<?php // phpcs:ignoreFile -- Theme bootstrap file triggers a persistent Squiz file-comment false positive.
/**
 * Greenlight theme functions and definitions.
 *
 * Core hooks, loaders, and shared helpers.
 *
 * @package Greenlight
 * @since Greenlight 1.0.0
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 */

require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/seo-fields.php' );
require_once get_theme_file_path( 'inc/seo-json-ld.php' );
require_once get_theme_file_path( 'inc/seo-sitemap.php' );
require_once get_theme_file_path( 'inc/seo-settings.php' );
require_once get_theme_file_path( 'inc/images.php' );
require_once get_theme_file_path( 'inc/images-settings.php' );
require_once get_theme_file_path( 'inc/admin.php' );
require_once get_theme_file_path( 'inc/customizer.php' );
require_once get_theme_file_path( 'inc/minify.php' );
require_once get_theme_file_path( 'inc/cache.php' );
require_once get_theme_file_path( 'inc/svg.php' );
require_once get_theme_file_path( 'inc/seo-analysis.php' );
require_once get_theme_file_path( 'inc/seo-redirects.php' );
require_once get_theme_file_path( 'inc/seo-breadcrumbs.php' );
require_once get_theme_file_path( 'inc/seo-robots.php' );
require_once get_theme_file_path( 'inc/critical-css.php' );
require_once get_theme_file_path( 'inc/prefetch.php' );
require_once get_theme_file_path( 'inc/db-cleanup.php' );
require_once get_theme_file_path( 'inc/heartbeat.php' );
require_once get_theme_file_path( 'inc/concat.php' );
require_once get_theme_file_path( 'inc/images-bulk.php' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function greenlight_setup() {
	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for post thumbnails.
	add_theme_support( 'post-thumbnails' );

	// Switch core markup to valid HTML5.
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	// Add support for block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for wide alignment.
	add_theme_support( 'align-wide' );

	// Register navigation menus.
	register_nav_menus(
		array(
			'primary' => __( 'Navigation principale', 'greenlight' ),
			'footer'  => __( 'Navigation secondaire', 'greenlight' ),
		)
	);
}
add_action( 'after_setup_theme', 'greenlight_setup' );

/**
 * Enqueue theme styles and deregister jQuery on the front end.
 */
function greenlight_enqueue() {
	$perf    = get_option( 'greenlight_performance_options', array() );
	$use_min = ! empty( $perf['enable_css_min'] );

	// Génération lazy du .min.css si absent.
	if ( $use_min && function_exists( 'greenlight_ensure_min_file' ) ) {
		greenlight_ensure_min_file( 'style.css', 'css' );
	}

	$style_file = ( $use_min && file_exists( get_stylesheet_directory() . '/style.min.css' ) )
		? 'style.min.css'
		: 'style.css';

	wp_enqueue_style(
		'greenlight-style',
		get_stylesheet_directory_uri() . '/' . $style_file,
		array(),
		filemtime( get_stylesheet_directory() . '/' . $style_file )
	);

	// Remove jQuery on the front end — zero JS policy.
	wp_deregister_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'greenlight_enqueue' );

/**
 * Disable WordPress emoji assets and filters on the front end.
 */
function greenlight_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'greenlight_disable_emojis' );

/**
 * Supprime les balises inutiles dans <head> pour alléger le DOM et le poids de la page.
 */
function greenlight_clean_wp_head() {
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10 );
	remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'greenlight_clean_wp_head' );

/**
 * Returns a short editorial lead for archive-like listings.
 *
 * @param string $context Archive context.
 * @return string
 */
function greenlight_get_archive_lead_text( $context = 'archive' ) {
	if ( 'home' === $context ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id > 0 ) {
			$page_excerpt = trim( (string) get_post_field( 'post_excerpt', $posts_page_id ) );

			if ( '' !== $page_excerpt ) {
				return wp_strip_all_tags( $page_excerpt );
			}
		}

		return __( 'Les derniers articles publiés, présentés sans superflu pour une lecture rapide.', 'greenlight' );
	}

	if ( is_category() || is_tag() || is_tax() ) {
		$term      = get_queried_object();
		$term_name = ( $term && ! is_wp_error( $term ) && ! empty( $term->name ) ) ? wp_strip_all_tags( $term->name ) : wp_strip_all_tags( get_the_archive_title() );

		/* translators: %s: term name. */
		$term_lead_template = __( 'Une sélection d’articles autour de %s, pensée pour aller droit à l’essentiel.', 'greenlight' );
		$term_lead          = sprintf( $term_lead_template, $term_name );

		return $term_lead;
	}

	if ( is_date() ) {
		return __( 'Les articles archivés par date, du plus récent au plus ancien, pour remonter le fil du site.', 'greenlight' );
	}

	if ( is_author() ) {
		$author = get_queried_object();

		if ( $author && ! is_wp_error( $author ) && ! empty( $author->display_name ) ) {
			/* translators: %s: author display name. */
			$author_lead_template = __( 'Les articles signés par %s, réunis dans une lecture continue.', 'greenlight' );
			$author_lead          = sprintf( $author_lead_template, wp_strip_all_tags( $author->display_name ) );

			return $author_lead;
		}
	}

	return __( 'Une archive sobre pour parcourir les contenus récents et retrouver l’essentiel en quelques secondes.', 'greenlight' );
}

/**
 * Returns the slug of the page assigned as the posts index, if any.
 *
 * @return string
 */
function greenlight_get_posts_page_slug() {
	$posts_page_id = (int) get_option( 'page_for_posts' );

	if ( $posts_page_id <= 0 ) {
		return '';
	}

	$posts_page_slug = (string) get_post_field( 'post_name', $posts_page_id );

	return sanitize_title( $posts_page_slug );
}

/**
 * Returns whether the current request targets the articles index route.
 *
 * @return bool
 */
function greenlight_is_articles_index_request() {
	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	if ( is_admin() || '' === $request_uri ) {
		return false;
	}

	$request_path = wp_parse_url( $request_uri, PHP_URL_PATH );

	if ( ! is_string( $request_path ) || '' === $request_path ) {
		return false;
	}

	$articles_path = wp_parse_url( home_url( '/articles/' ), PHP_URL_PATH );
	$articles_path = is_string( $articles_path ) ? untrailingslashit( $articles_path ) : '/articles';
	$request_path  = untrailingslashit( $request_path );

	return $request_path === $articles_path || 0 === strpos( $request_path, $articles_path . '/page/' );
}

/**
 * Registers a stable `/articles/` route for the blog index.
 *
 * @return void
 */
function greenlight_register_articles_index_rewrite_rules() {
	add_rewrite_rule( '^articles/?$', 'index.php?greenlight_articles_index=1', 'top' );
	add_rewrite_rule( '^articles/page/([0-9]+)/?$', 'index.php?greenlight_articles_index=1&paged=$matches[1]', 'top' );
}
add_action( 'init', 'greenlight_register_articles_index_rewrite_rules' );

/**
 * Exposes the custom blog index query var.
 *
 * @param string[] $vars Registered query vars.
 * @return string[]
 */
function greenlight_register_articles_index_query_var( $vars ) {
	$vars[] = 'greenlight_articles_index';

	return $vars;
}
add_filter( 'query_vars', 'greenlight_register_articles_index_query_var' );

/**
 * Forces the blog query when `/articles/` is requested.
 *
 * @param WP_Query $query Current query object.
 * @return void
 */
function greenlight_pre_get_articles_index_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( ! $query->get( 'greenlight_articles_index' ) && ! greenlight_is_articles_index_request() ) {
		return;
	}

	$query->set( 'post_type', 'post' );
	$query->set( 'ignore_sticky_posts', true );
	$query->is_home              = true;
	$query->is_page              = false;
	$query->is_singular          = false;
	$query->is_archive           = true;
	$query->is_post_type_archive = false;
	$query->is_404               = false;
}
add_action( 'pre_get_posts', 'greenlight_pre_get_articles_index_query' );

/**
 * Loads the posts index template for the custom `/articles/` route.
 *
 * @param string $template Template path.
 * @return string
 */
function greenlight_template_include_articles_index( $template ) {
	if ( get_query_var( 'greenlight_articles_index' ) || greenlight_is_articles_index_request() ) {
		$articles_template = get_theme_file_path( 'home.php' );

		if ( file_exists( $articles_template ) ) {
			return $articles_template;
		}
	}

	return $template;
}
add_filter( 'template_include', 'greenlight_template_include_articles_index' );

/**
 * Flush rewrite rules once so the articles index route resolves immediately.
 *
 * @return void
 */
function greenlight_maybe_flush_articles_rewrites() {
	$version = (int) get_option( 'greenlight_articles_route_version', 0 );

	if ( 1 === $version ) {
		return;
	}

	greenlight_register_articles_index_rewrite_rules();
	flush_rewrite_rules( false );
	update_option( 'greenlight_articles_route_version', 1, false );
}
add_action( 'init', 'greenlight_maybe_flush_articles_rewrites', 20 );

/**
 * Routes the posts page slug to the blog index so it does not 404.
 *
 * @param array $query_vars Parsed request vars.
 * @return array
 */
function greenlight_route_posts_page_request( $query_vars ) {
	if ( is_admin() ) {
		return $query_vars;
	}

	$posts_page_slug = greenlight_get_posts_page_slug();

	if ( '' === $posts_page_slug ) {
		return $query_vars;
	}

	$current_page_slug = '';

	if ( isset( $query_vars['pagename'] ) ) {
		$current_page_slug = sanitize_title( (string) $query_vars['pagename'] );
	} elseif ( isset( $query_vars['name'] ) ) {
		$current_page_slug = sanitize_title( (string) $query_vars['name'] );
	}

	if ( $current_page_slug !== $posts_page_slug ) {
		return $query_vars;
	}

	$paged = isset( $query_vars['paged'] ) ? absint( $query_vars['paged'] ) : 0;

	unset(
		$query_vars['pagename'],
		$query_vars['name'],
		$query_vars['page_id'],
		$query_vars['p'],
		$query_vars['error']
	);

	if ( $paged > 1 ) {
		$query_vars['paged'] = $paged;
	}

	return $query_vars;
}
add_filter( 'request', 'greenlight_route_posts_page_request' );

/**
 * Enqueue block styles conditionally — loaded only when the block is present on the page.
 */
function greenlight_block_styles() {
	$perf    = get_option( 'greenlight_performance_options', array() );
	$use_min = ! empty( $perf['enable_css_min'] );

	$blocks = array(
		'core/navigation' => 'navigation',
		'core/image'      => 'image',
		'core/heading'    => 'heading',
		'core/paragraph'  => 'paragraph',
		'core/separator'  => 'separator',
		'core/button'     => 'button',
		'core/group'      => 'group',
		'core/query'      => 'query',
	);

	foreach ( $blocks as $block => $file ) {
		// Génération lazy du .min.css si absent.
		if ( $use_min && function_exists( 'greenlight_ensure_min_file' ) ) {
			greenlight_ensure_min_file( 'assets/css/blocks/' . $file . '.css', 'css' );
		}

		$min_path = get_theme_file_path( 'assets/css/blocks/' . $file . '.min.css' );
		$src_file = ( $use_min && file_exists( $min_path ) ) ? $file . '.min.css' : $file . '.css';
		$abs_path = get_theme_file_path( 'assets/css/blocks/' . $src_file );

		wp_enqueue_block_style(
			$block,
			array(
				'handle' => 'greenlight-block-' . $file,
				'src'    => get_theme_file_uri( 'assets/css/blocks/' . $src_file ),
				'path'   => $abs_path,
				'ver'    => filemtime( $abs_path ),
			)
		);
	}
}
add_action( 'init', 'greenlight_block_styles' );

/**
 * Register the Greenlight pattern category.
 */
function greenlight_pattern_categories() {
	register_block_pattern_category(
		'greenlight',
		array(
			'label'       => __( 'Greenlight', 'greenlight' ),
			'description' => __( 'Patterns du thème Greenlight.', 'greenlight' ),
		)
	);
}
add_action( 'init', 'greenlight_pattern_categories' );

/**
 * Returns the Carbon Badge HTML pill.
 *
 * Displays an eco-metric chip with estimated CO2 per page view.
 * A manual override is possible via the `greenlight_carbon_badge_value` option.
 * Badge placement is controlled via the Appearance settings.
 *
 * @param string $placement Requested placement: top or footer.
 * @return string HTML string for the badge.
 */
function greenlight_carbon_badge( $placement = 'top' ) {
	// Check appearance options first (set via Greenlight admin), fall back to legacy option.
	$appearance = get_option( 'greenlight_appearance_options', array() );
	$placement  = sanitize_key( (string) $placement );
	$configured  = isset( $appearance['carbon_badge_position'] ) ? sanitize_key( (string) $appearance['carbon_badge_position'] ) : 'top';
	$manual     = isset( $appearance['carbon_badge_value'] ) && '' !== $appearance['carbon_badge_value']
		? $appearance['carbon_badge_value']
		: get_option( 'greenlight_carbon_badge_value', '' );

	if ( ! empty( $appearance['carbon_badge_enabled'] ) && $placement !== $configured ) {
		return '';
	}

	if ( empty( $appearance['carbon_badge_enabled'] ) ) {
		return '';
	}

	$co2        = ( '' !== $manual ) ? esc_html( $manual ) : '0.2g';

	return '<span class="carbon-badge">' .
		$co2 .
		' <abbr title="' . esc_attr__( 'dioxyde de carbone', 'greenlight' ) . '">CO₂</abbr>' .
		esc_html__( '/vue', 'greenlight' ) .
		'</span>';
}
