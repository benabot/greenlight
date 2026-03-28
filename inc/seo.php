<?php
/**
 * Core SEO helpers and head tags.
 *
 * @package Greenlight
 */

if ( ! defined( 'GREENLIGHT_SEO_OPTION_KEY' ) ) {
	define( 'GREENLIGHT_SEO_OPTION_KEY', 'greenlight_seo_options' );
}

if ( ! defined( 'GREENLIGHT_SEO_TITLE_META_KEY' ) ) {
	define( 'GREENLIGHT_SEO_TITLE_META_KEY', '_greenlight_seo_title' );
}

if ( ! defined( 'GREENLIGHT_SEO_DESCRIPTION_META_KEY' ) ) {
	define( 'GREENLIGHT_SEO_DESCRIPTION_META_KEY', '_greenlight_seo_description' );
}

if ( ! defined( 'GREENLIGHT_SEO_IMAGE_META_KEY' ) ) {
	define( 'GREENLIGHT_SEO_IMAGE_META_KEY', '_greenlight_seo_image' );
}

if ( ! defined( 'GREENLIGHT_SEO_NOINDEX_META_KEY' ) ) {
	define( 'GREENLIGHT_SEO_NOINDEX_META_KEY', '_greenlight_seo_noindex' );
}

/**
 * Returns the default SEO option values.
 *
 * @return array<string, mixed>
 */
function greenlight_get_seo_defaults() {
	return array(
		'site_title'              => '',
		'site_description'        => '',
		'title_separator'         => '-',
		'enable_sitemap'          => 1,
		'noindex_author_archives' => 1,
		'noindex_tag_archives'    => 1,
		'show_breadcrumbs'        => 0,
		'custom_robots_txt'       => '',
	);
}

/**
 * Normalizes a text field for SEO usage.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function greenlight_sanitize_seo_text( $value ) {
	return sanitize_text_field( wp_unslash( (string) $value ) );
}

/**
 * Normalizes a textarea field for SEO usage.
 *
 * @param mixed $value Raw value.
 * @return string
 */
function greenlight_sanitize_seo_textarea( $value ) {
	return sanitize_textarea_field( wp_unslash( (string) $value ) );
}

/**
 * Normalizes a boolean-like SEO value.
 *
 * @param mixed $value Raw value.
 * @return bool
 */
function greenlight_sanitize_seo_boolean( $value ) {
	return rest_sanitize_boolean( $value );
}

/**
 * Returns the merged SEO options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_seo_options() {
	$options = get_option( GREENLIGHT_SEO_OPTION_KEY, array() );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	return wp_parse_args( $options, greenlight_get_seo_defaults() );
}

/**
 * Returns a single SEO option value.
 *
 * @param string $key Option key.
 * @return mixed
 */
function greenlight_get_seo_option( $key ) {
	$options = greenlight_get_seo_options();

	return isset( $options[ $key ] ) ? $options[ $key ] : null;
}

/**
 * Returns the SEO site title or the WordPress site title.
 *
 * @return string
 */
function greenlight_get_seo_site_title() {
	$site_title = greenlight_sanitize_seo_text( greenlight_get_seo_option( 'site_title' ) );

	if ( '' !== $site_title ) {
		return $site_title;
	}

	return greenlight_sanitize_seo_text( get_bloginfo( 'name' ) );
}

/**
 * Returns the SEO site description or the WordPress tagline.
 *
 * @return string
 */
function greenlight_get_seo_site_description() {
	$description = greenlight_sanitize_seo_textarea( greenlight_get_seo_option( 'site_description' ) );

	if ( '' !== $description ) {
		return $description;
	}

	return greenlight_sanitize_seo_textarea( get_bloginfo( 'description' ) );
}

/**
 * Trims a text string for meta tag output.
 *
 * @param string $text Raw text.
 * @param int    $max_length Max length.
 * @return string
 */
function greenlight_trim_seo_text( $text, $max_length = 160 ) {
	$text = preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $text ) );
	$text = trim( (string) $text );

	if ( '' === $text ) {
		return '';
	}

	return wp_html_excerpt( $text, absint( $max_length ), '...' );
}

/**
 * Returns the current post SEO meta values.
 *
 * @param int $post_id Optional post ID.
 * @return array<string, mixed>
 */
function greenlight_get_seo_post_meta_values( $post_id = 0 ) {
	$post_id = absint( $post_id );

	if ( ! $post_id && is_singular() ) {
		$post_id = get_queried_object_id();
	}

	if ( ! $post_id ) {
		return array(
			'title'       => '',
			'description' => '',
			'image_id'    => 0,
			'noindex'     => false,
		);
	}

	return array(
		'title'       => greenlight_sanitize_seo_text( get_post_meta( $post_id, GREENLIGHT_SEO_TITLE_META_KEY, true ) ),
		'description' => greenlight_sanitize_seo_textarea( get_post_meta( $post_id, GREENLIGHT_SEO_DESCRIPTION_META_KEY, true ) ),
		'image_id'    => absint( get_post_meta( $post_id, GREENLIGHT_SEO_IMAGE_META_KEY, true ) ),
		'noindex'     => greenlight_sanitize_seo_boolean( get_post_meta( $post_id, GREENLIGHT_SEO_NOINDEX_META_KEY, true ) ),
	);
}

/**
 * Returns the best available SEO title for the current request.
 *
 * @return string
 */
function greenlight_get_seo_title_text() {
	if ( is_singular() ) {
		$meta_title = greenlight_get_seo_post_meta_values()['title'];

		if ( '' !== $meta_title ) {
			return $meta_title;
		}

		return greenlight_sanitize_seo_text( get_the_title( get_queried_object_id() ) );
	}

	if ( is_front_page() ) {
		return greenlight_get_seo_site_title();
	}

	if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
		return greenlight_sanitize_seo_text( get_the_title( (int) get_option( 'page_for_posts' ) ) );
	}

	if ( is_archive() ) {
		return greenlight_trim_seo_text( get_the_archive_title(), 110 );
	}

	if ( is_search() ) {
		return sprintf(
			/* translators: %s: search query */
			esc_html__( 'Resultats pour %s', 'greenlight' ),
			greenlight_sanitize_seo_text( get_search_query() )
		);
	}

	if ( is_404() ) {
		return esc_html__( 'Page introuvable', 'greenlight' );
	}

	return greenlight_get_seo_site_title();
}

/**
 * Returns the best available SEO description for the current request.
 *
 * @return string
 */
function greenlight_get_seo_description() {
	if ( is_singular() ) {
		$post_id          = get_queried_object_id();
		$meta_description = greenlight_get_seo_post_meta_values( $post_id )['description'];

		if ( '' !== $meta_description ) {
			return greenlight_trim_seo_text( $meta_description );
		}

		return greenlight_trim_seo_text( get_the_excerpt( $post_id ) );
	}

	if ( is_front_page() || is_home() ) {
		return greenlight_trim_seo_text( greenlight_get_seo_site_description() );
	}

	if ( is_archive() ) {
		$archive_description = greenlight_trim_seo_text( get_the_archive_description() );

		if ( '' !== $archive_description ) {
			return $archive_description;
		}
	}

	if ( is_search() ) {
		return greenlight_trim_seo_text(
			sprintf(
				/* translators: %s: search query */
				__( 'Resultats de recherche pour %s.', 'greenlight' ),
				greenlight_sanitize_seo_text( get_search_query() )
			)
		);
	}

	return greenlight_trim_seo_text( greenlight_get_seo_site_description() );
}

/**
 * Returns the best available social image attachment ID.
 *
 * @return int
 */
function greenlight_get_seo_image_id() {
	if ( ! is_singular() ) {
		return 0;
	}

	$post_id = get_queried_object_id();
	$image_id = greenlight_get_seo_post_meta_values( $post_id )['image_id'];

	if ( $image_id ) {
		return $image_id;
	}

	if ( has_post_thumbnail( $post_id ) ) {
		return (int) get_post_thumbnail_id( $post_id );
	}

	return 0;
}

/**
 * Returns the best available social image URL.
 *
 * @return string
 */
function greenlight_get_seo_image_url() {
	$image_id = greenlight_get_seo_image_id();

	if ( ! $image_id ) {
		return '';
	}

	$image = wp_get_attachment_image_url( $image_id, 'full' );

	return $image ? esc_url_raw( $image ) : '';
}

/**
 * Returns whether the current request should be noindexed.
 *
 * @return bool
 */
function greenlight_is_noindex_request() {
	if ( is_search() || is_404() ) {
		return true;
	}

	if ( is_author() && greenlight_sanitize_seo_boolean( greenlight_get_seo_option( 'noindex_author_archives' ) ) ) {
		return true;
	}

	if ( is_tag() && greenlight_sanitize_seo_boolean( greenlight_get_seo_option( 'noindex_tag_archives' ) ) ) {
		return true;
	}

	if ( is_singular() ) {
		return (bool) greenlight_get_seo_post_meta_values()['noindex'];
	}

	return false;
}

/**
 * Returns the best canonical URL for the current request.
 *
 * @return string
 */
function greenlight_get_seo_canonical_url() {
	if ( is_404() ) {
		return '';
	}

	if ( is_singular() ) {
		$canonical = wp_get_canonical_url( get_queried_object_id() );

		if ( $canonical ) {
			return esc_url_raw( $canonical );
		}
	}

	if ( is_front_page() ) {
		return esc_url_raw( home_url( '/' ) );
	}

	if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id > 0 ) {
			$canonical = get_permalink( $posts_page_id );
			$paged     = max( 1, absint( get_query_var( 'paged' ) ) );

			if ( $paged > 1 ) {
				$canonical = trailingslashit( $canonical ) . user_trailingslashit( 'page/' . $paged, 'paged' );
			}

			return esc_url_raw( $canonical );
		}
	}

	return esc_url_raw(
		remove_query_arg(
			array( 'replytocom', 'preview', 'preview_id', 'preview_nonce' ),
			get_pagenum_link( max( 1, absint( get_query_var( 'paged' ) ) ) )
		)
	);
}

/**
 * Filters the title separator for the native document title.
 *
 * @return string
 */
function greenlight_filter_document_title_separator() {
	$separator = greenlight_sanitize_seo_text( greenlight_get_seo_option( 'title_separator' ) );

	return '' !== $separator ? $separator : '-';
}
add_filter( 'document_title_separator', 'greenlight_filter_document_title_separator' );

/**
 * Filters the document title parts.
 *
 * @param array<string, string> $parts Existing title parts.
 * @return array<string, string>
 */
function greenlight_filter_document_title_parts( $parts ) {
	$site_title = greenlight_get_seo_site_title();

	if ( '' !== $site_title ) {
		$parts['site'] = $site_title;
	}

	if ( is_singular() ) {
		$custom_title = greenlight_get_seo_post_meta_values()['title'];

		if ( '' !== $custom_title ) {
			$parts['title'] = $custom_title;
		}
	}

	if ( is_front_page() && ! is_paged() ) {
		$parts = array(
			'title' => $site_title,
		);

		$site_description = greenlight_get_seo_site_description();

		if ( '' !== $site_description ) {
			$parts['tagline'] = $site_description;
		}
	}

	return $parts;
}
add_filter( 'document_title_parts', 'greenlight_filter_document_title_parts' );

/**
 * Filters WordPress robots directives.
 *
 * @param array<string, bool> $robots Existing robots directives.
 * @return array<string, bool>
 */
function greenlight_filter_wp_robots( $robots ) {
	unset( $robots['index'], $robots['noindex'] );

	if ( greenlight_is_noindex_request() ) {
		$robots['noindex'] = true;
	} else {
		$robots['index'] = true;
	}

	$robots['follow'] = true;

	return $robots;
}
add_filter( 'wp_robots', 'greenlight_filter_wp_robots' );

/**
 * Removes the core canonical tag so the theme can output a single canonical tag.
 *
 * @return void
 */
function greenlight_disable_core_canonical_tag() {
	remove_action( 'wp_head', 'rel_canonical' );
}
add_action( 'init', 'greenlight_disable_core_canonical_tag' );

/**
 * Outputs SEO meta tags in the document head.
 *
 * @return void
 */
function greenlight_output_seo_meta_tags() {
	$title       = greenlight_get_seo_title_text();
	$description = greenlight_get_seo_description();
	$canonical   = greenlight_get_seo_canonical_url();
	$image_url   = greenlight_get_seo_image_url();
	$og_type     = is_singular( 'post' ) ? 'article' : 'website';

	if ( '' !== $description ) {
		printf(
			"<meta name=\"description\" content=\"%s\">\n",
			esc_attr( $description )
		);
	}

	if ( '' !== $canonical ) {
		printf(
			"<link rel=\"canonical\" href=\"%s\">\n",
			esc_url( $canonical )
		);
	}

	printf(
		"<meta property=\"og:title\" content=\"%s\">\n",
		esc_attr( $title )
	);
	printf(
		"<meta property=\"og:description\" content=\"%s\">\n",
		esc_attr( $description )
	);
	printf(
		"<meta property=\"og:url\" content=\"%s\">\n",
		esc_url( $canonical )
	);
	printf(
		"<meta property=\"og:type\" content=\"%s\">\n",
		esc_attr( $og_type )
	);
	printf(
		"<meta property=\"og:site_name\" content=\"%s\">\n",
		esc_attr( greenlight_get_seo_site_title() )
	);

	if ( '' !== $image_url ) {
		printf(
			"<meta property=\"og:image\" content=\"%s\">\n",
			esc_url( $image_url )
		);
	}

	echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";

	printf(
		"<meta name=\"twitter:title\" content=\"%s\">\n",
		esc_attr( $title )
	);
	printf(
		"<meta name=\"twitter:description\" content=\"%s\">\n",
		esc_attr( $description )
	);

	if ( '' !== $image_url ) {
		printf(
			"<meta name=\"twitter:image\" content=\"%s\">\n",
			esc_url( $image_url )
		);
	}
}
add_action( 'wp_head', 'greenlight_output_seo_meta_tags', 1 );
