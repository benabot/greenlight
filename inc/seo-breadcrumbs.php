<?php
/**
 * SEO breadcrumbs with Schema.org JSON-LD.
 *
 * @package Greenlight
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns an array of breadcrumb items [label, url].
 *
 * @return array<int, array{label: string, url: string}>
 */
function greenlight_get_breadcrumb_items() {
	$items = array();

	$items[] = array(
		'label' => __( 'Accueil', 'greenlight' ),
		'url'   => home_url( '/' ),
	);

	if ( is_singular( 'post' ) ) {
		$categories = get_the_category();

		if ( ! empty( $categories ) ) {
			$cat = $categories[0];
			$items[] = array(
				'label' => $cat->name,
				'url'   => get_category_link( $cat->term_id ),
			);
		}

		$items[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_page() ) {
		$items[] = array(
			'label' => get_the_title(),
			'url'   => '',
		);
	} elseif ( is_category() || is_tag() || is_tax() ) {
		$items[] = array(
			'label' => single_term_title( '', false ),
			'url'   => '',
		);
	} elseif ( is_archive() ) {
		$items[] = array(
			'label' => wp_strip_all_tags( get_the_archive_title() ),
			'url'   => '',
		);
	} elseif ( is_search() ) {
		$items[] = array(
			'label' => sprintf(
				/* translators: %s: search query */
				__( 'Recherche : %s', 'greenlight' ),
				get_search_query()
			),
			'url'   => '',
		);
	} elseif ( is_404() ) {
		$items[] = array(
			'label' => __( 'Page non trouvée', 'greenlight' ),
			'url'   => '',
		);
	}

	return $items;
}

/**
 * Outputs breadcrumb navigation with Schema.org JSON-LD.
 *
 * @return void
 */
function greenlight_breadcrumbs() {
	$seo_options = greenlight_get_seo_options();

	if ( empty( $seo_options['show_breadcrumbs'] ) ) {
		return;
	}

	if ( is_front_page() ) {
		return;
	}

	$items = greenlight_get_breadcrumb_items();

	if ( count( $items ) < 2 ) {
		return;
	}

	echo '<nav class="greenlight-breadcrumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'greenlight' ) . '">';
	echo '<ol>';

	$json_ld_items = array();

	foreach ( $items as $index => $item ) {
		$position  = $index + 1;
		$is_last   = $index === count( $items ) - 1;

		if ( ! $is_last && '' !== $item['url'] ) {
			echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
		} else {
			echo '<li><span aria-current="page">' . esc_html( $item['label'] ) . '</span></li>';
		}

		$ld_item = array(
			'@type'    => 'ListItem',
			'position' => $position,
			'name'     => $item['label'],
		);

		if ( '' !== $item['url'] ) {
			$ld_item['item'] = $item['url'];
		}

		$json_ld_items[] = $ld_item;
	}

	echo '</ol>';
	echo '</nav>';

	// Inline JSON-LD.
	$json_ld = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $json_ld_items,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
