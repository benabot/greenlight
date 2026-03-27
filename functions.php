<?php
/**
 * Greenlight theme functions and definitions.
 *
 * @package Greenlight
 */

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
