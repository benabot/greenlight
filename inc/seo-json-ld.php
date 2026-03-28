<?php
/**
 * JSON-LD output.
 *
 * @package Greenlight
 */

/**
 * Builds breadcrumb items for the current request.
 *
 * @return array<int, array<string, string>>
 */
function greenlight_get_breadcrumb_items() {
	$items = array(
		array(
			'name' => greenlight_get_seo_site_title(),
			'url'  => home_url( '/' ),
		),
	);

	if ( is_front_page() ) {
		return $items;
	}

	if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id > 0 ) {
			$items[] = array(
				'name' => greenlight_sanitize_seo_text( get_the_title( $posts_page_id ) ),
				'url'  => get_permalink( $posts_page_id ),
			);
		}

		return $items;
	}

	if ( is_page() ) {
		$post_id   = get_queried_object_id();
		$ancestors = array_reverse( get_post_ancestors( $post_id ) );

		foreach ( $ancestors as $ancestor_id ) {
			$items[] = array(
				'name' => greenlight_sanitize_seo_text( get_the_title( $ancestor_id ) ),
				'url'  => get_permalink( $ancestor_id ),
			);
		}

		$items[] = array(
			'name' => greenlight_sanitize_seo_text( get_the_title( $post_id ) ),
			'url'  => greenlight_get_seo_canonical_url(),
		);

		return $items;
	}

	if ( is_singular( 'post' ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id > 0 ) {
			$items[] = array(
				'name' => greenlight_sanitize_seo_text( get_the_title( $posts_page_id ) ),
				'url'  => get_permalink( $posts_page_id ),
			);
		}

		$categories = get_the_category( get_queried_object_id() );

		if ( ! empty( $categories ) ) {
			$items[] = array(
				'name' => greenlight_sanitize_seo_text( $categories[0]->name ),
				'url'  => get_category_link( $categories[0] ),
			);
		}

		$items[] = array(
			'name' => greenlight_sanitize_seo_text( get_the_title( get_queried_object_id() ) ),
			'url'  => greenlight_get_seo_canonical_url(),
		);

		return $items;
	}

	if ( is_archive() ) {
		$items[] = array(
			'name' => greenlight_trim_seo_text( get_the_archive_title(), 110 ),
			'url'  => greenlight_get_seo_canonical_url(),
		);

		return $items;
	}

	if ( is_search() ) {
		$items[] = array(
			'name' => sprintf(
				/* translators: %s: search query */
				__( 'Recherche : %s', 'greenlight' ),
				greenlight_sanitize_seo_text( get_search_query() )
			),
			'url'  => greenlight_get_seo_canonical_url(),
		);
	}

	return $items;
}

/**
 * Outputs JSON-LD schema markup.
 *
 * @return void
 */
function greenlight_output_json_ld() {
	$graph = array();

	if ( is_front_page() ) {
		$website = array(
			'@type'           => 'WebSite',
			'@id'             => trailingslashit( home_url( '/' ) ) . '#website',
			'url'             => home_url( '/' ),
			'name'            => greenlight_get_seo_site_title(),
			'description'     => greenlight_get_seo_site_description(),
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => home_url( '/?s={search_term_string}' ),
				'query-input' => 'required name=search_term_string',
			),
		);

		$graph[] = $website;
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$article = array(
			'@type'            => 'Article',
			'headline'         => greenlight_sanitize_seo_text( get_the_title( $post_id ) ),
			'description'      => greenlight_get_seo_description(),
			'datePublished'    => get_post_time( 'c', true, $post_id ),
			'dateModified'     => get_post_modified_time( 'c', true, $post_id ),
			'mainEntityOfPage' => greenlight_get_seo_canonical_url(),
			'author'           => array(
				'@type' => 'Person',
				'name'  => greenlight_sanitize_seo_text( get_the_author_meta( 'display_name', (int) get_post_field( 'post_author', $post_id ) ) ),
			),
			'publisher'        => array(
				'@type' => 'Organization',
				'name'  => greenlight_get_seo_site_title(),
			),
		);

		$image_url = greenlight_get_seo_image_url();

		if ( '' !== $image_url ) {
			$article['image'] = array( $image_url );
		}

		$graph[] = $article;
	}

	$breadcrumbs = greenlight_get_breadcrumb_items();

	if ( count( $breadcrumbs ) > 1 ) {
		$item_list = array();

		foreach ( $breadcrumbs as $index => $item ) {
			$item_list[] = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $item['name'],
				'item'     => $item['url'],
			);
		}

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $item_list,
		);
	}

	if ( empty( $graph ) ) {
		return;
	}

	printf(
		"<script type=\"application/ld+json\">%s</script>\n",
		wp_json_encode(
			array(
				'@context' => 'https://schema.org',
				'@graph'   => $graph,
			),
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		)
	);
}
add_action( 'wp_footer', 'greenlight_output_json_ld', 5 );
