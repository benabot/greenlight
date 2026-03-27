<?php
/**
 * Greenlight theme functions and definitions.
 *
 * @package Greenlight
 */

require_once get_theme_file_path( 'inc/seo.php' );
require_once get_theme_file_path( 'inc/seo-fields.php' );
require_once get_theme_file_path( 'inc/seo-json-ld.php' );
require_once get_theme_file_path( 'inc/seo-sitemap.php' );
require_once get_theme_file_path( 'inc/seo-settings.php' );
require_once get_theme_file_path( 'inc/images.php' );
require_once get_theme_file_path( 'inc/images-settings.php' );

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function greenlight_setup() {
	// Let WordPress manage the document title.
	add_theme_support( 'title-tag' );

	// Enable support for post thumbnails.
	add_theme_support( 'post-thumbnails' );

	// Switch core markup to valid HTML5.
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Add support for responsive embedded content.
	add_theme_support( 'responsive-embeds' );

	// Add support for editor styles.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	// Add support for block styles.
	add_theme_support( 'wp-block-styles' );

	// Add support for wide alignment.
	add_theme_support( 'align-wide' );

	// Register primary navigation menu.
	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'greenlight' ),
	) );
}
add_action( 'after_setup_theme', 'greenlight_setup' );

/**
 * Enqueue theme styles and deregister jQuery on the front end.
 */
function greenlight_enqueue() {
	// Enqueue main stylesheet with cache busting.
	wp_enqueue_style(
		'greenlight-style',
		get_stylesheet_uri(),
		array(),
		filemtime( get_stylesheet_directory() . '/style.css' )
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

		return sprintf(
			__( 'Une sélection d’articles autour de %s, pensée pour aller droit à l’essentiel.', 'greenlight' ),
			$term_name
		);
	}

	if ( is_date() ) {
		return __( 'Les articles archivés par date, du plus récent au plus ancien, pour remonter le fil du site.', 'greenlight' );
	}

	if ( is_author() ) {
		$author = get_queried_object();

		if ( $author && ! is_wp_error( $author ) && ! empty( $author->display_name ) ) {
			return sprintf(
				__( 'Les articles signés par %s, réunis dans une lecture continue.', 'greenlight' ),
				wp_strip_all_tags( $author->display_name )
			);
		}
	}

	return __( 'Une archive sobre pour parcourir les contenus récents et retrouver l’essentiel en quelques secondes.', 'greenlight' );
}

/**
 * Enqueue block styles conditionally — loaded only when the block is present on the page.
 */
function greenlight_block_styles() {
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
		wp_enqueue_block_style( $block, array(
			'handle' => 'greenlight-block-' . $file,
			'src'    => get_theme_file_uri( 'assets/css/blocks/' . $file . '.css' ),
			'path'   => get_theme_file_path( 'assets/css/blocks/' . $file . '.css' ),
			'ver'    => filemtime( get_theme_file_path( 'assets/css/blocks/' . $file . '.css' ) ),
		) );
	}
}
add_action( 'init', 'greenlight_block_styles' );

/**
 * Register the Greenlight pattern category.
 */
function greenlight_pattern_categories() {
	register_block_pattern_category( 'greenlight', array(
		'label'       => __( 'Greenlight', 'greenlight' ),
		'description' => __( 'Patterns du thème Greenlight.', 'greenlight' ),
	) );
}
add_action( 'init', 'greenlight_pattern_categories' );
