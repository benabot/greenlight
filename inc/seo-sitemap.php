<?php
/**
 * Native XML sitemap support.
 *
 * @package Greenlight
 */

/**
 * Registers the sitemap rewrite rules.
 *
 * @return void
 */
function greenlight_register_sitemap_rewrite_rules() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?greenlight_sitemap=index', 'top' );
	add_rewrite_rule( '^sitemap-posts\.xml$', 'index.php?greenlight_sitemap=posts', 'top' );
	add_rewrite_rule( '^sitemap-pages\.xml$', 'index.php?greenlight_sitemap=pages', 'top' );
}
add_action( 'init', 'greenlight_register_sitemap_rewrite_rules' );

/**
 * Adds the sitemap query var.
 *
 * @param string[] $vars Existing vars.
 * @return string[]
 */
function greenlight_register_sitemap_query_var( $vars ) {
	$vars[] = 'greenlight_sitemap';

	return $vars;
}
add_filter( 'query_vars', 'greenlight_register_sitemap_query_var' );

/**
 * Returns the sitemap transient key.
 *
 * @param string $type Sitemap type.
 * @return string
 */
function greenlight_get_sitemap_transient_key( $type ) {
	return 'greenlight_sitemap_' . sanitize_key( $type );
}

/**
 * Flushes all sitemap caches.
 *
 * @return void
 */
function greenlight_flush_sitemap_cache() {
	delete_transient( greenlight_get_sitemap_transient_key( 'index' ) );
	delete_transient( greenlight_get_sitemap_transient_key( 'posts' ) );
	delete_transient( greenlight_get_sitemap_transient_key( 'pages' ) );
}
add_action( 'save_post', 'greenlight_flush_sitemap_cache' );
add_action( 'deleted_post', 'greenlight_flush_sitemap_cache' );
add_action( 'trashed_post', 'greenlight_flush_sitemap_cache' );
add_action( 'untrashed_post', 'greenlight_flush_sitemap_cache' );
add_action( 'update_option_' . GREENLIGHT_SEO_OPTION_KEY, 'greenlight_flush_sitemap_cache' );

/**
 * Flushes rewrite rules when the theme is activated.
 *
 * @return void
 */
function greenlight_flush_sitemap_rewrite_rules() {
	greenlight_register_sitemap_rewrite_rules();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'greenlight_flush_sitemap_rewrite_rules' );

/**
 * Returns sitemap entries for a given post type.
 *
 * @param string $type Sitemap type.
 * @return array<int, array<string, string>>
 */
function greenlight_get_sitemap_entries( $type ) {
	$post_type = 'posts' === $type ? 'post' : 'page';

	$query = new WP_Query(
		array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => 'modified',
			'order'                  => 'DESC',
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'meta_query'             => array(
				'relation' => 'OR',
				array(
					'key'     => GREENLIGHT_SEO_NOINDEX_META_KEY,
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => GREENLIGHT_SEO_NOINDEX_META_KEY,
					'value'   => '1',
					'compare' => '!=',
				),
			),
		)
	);

	$entries = array();

	foreach ( $query->posts as $post_id ) {
		$entries[] = array(
			'loc'     => get_permalink( $post_id ),
			'lastmod' => get_post_modified_time( 'c', true, $post_id ),
		);
	}

	return $entries;
}

/**
 * Returns the cached sitemap XML for a given type.
 *
 * @param string $type Sitemap type.
 * @return string
 */
function greenlight_get_sitemap_xml( $type ) {
	$cache_key = greenlight_get_sitemap_transient_key( $type );
	$cached    = get_transient( $cache_key );

	if ( false !== $cached ) {
		return (string) $cached;
	}

	$xml = '';

	if ( 'index' === $type ) {
		$posts_entries = greenlight_get_sitemap_entries( 'posts' );
		$pages_entries = greenlight_get_sitemap_entries( 'pages' );
		$lastmod       = current_time( 'c' );

		if ( ! empty( $posts_entries ) ) {
			$lastmod = $posts_entries[0]['lastmod'];
		} elseif ( ! empty( $pages_entries ) ) {
			$lastmod = $pages_entries[0]['lastmod'];
		}

		$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= "<sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
		$xml .= "\t<sitemap>\n";
		$xml .= "\t\t<loc>" . esc_url( home_url( '/sitemap-posts.xml' ) ) . "</loc>\n";
		$xml .= "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
		$xml .= "\t</sitemap>\n";
		$xml .= "\t<sitemap>\n";
		$xml .= "\t\t<loc>" . esc_url( home_url( '/sitemap-pages.xml' ) ) . "</loc>\n";
		$xml .= "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
		$xml .= "\t</sitemap>\n";
		$xml .= "</sitemapindex>\n";
	} else {
		$entries = greenlight_get_sitemap_entries( $type );

		$xml .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$xml .= "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";

		foreach ( $entries as $entry ) {
			$xml .= "\t<url>\n";
			$xml .= "\t\t<loc>" . esc_url( $entry['loc'] ) . "</loc>\n";
			$xml .= "\t\t<lastmod>" . esc_html( $entry['lastmod'] ) . "</lastmod>\n";
			$xml .= "\t</url>\n";
		}

		$xml .= "</urlset>\n";
	}

	set_transient( $cache_key, $xml, DAY_IN_SECONDS );

	return $xml;
}

/**
 * Renders the sitemap response when the request matches a sitemap route.
 *
 * @return void
 */
function greenlight_maybe_render_sitemap() {
	$type = get_query_var( 'greenlight_sitemap' );

	if ( ! $type ) {
		return;
	}

	if ( ! greenlight_sanitize_seo_boolean( greenlight_get_seo_option( 'enable_sitemap' ) ) ) {
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();

		return;
	}

	if ( ! in_array( $type, array( 'index', 'posts', 'pages' ), true ) ) {
		global $wp_query;

		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();

		return;
	}

	status_header( 200 );
	header( 'Content-Type: application/xml; charset=' . esc_attr( get_bloginfo( 'charset' ) ) );
	echo greenlight_get_sitemap_xml( $type ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit;
}
add_action( 'template_redirect', 'greenlight_maybe_render_sitemap' );
