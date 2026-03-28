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
 * Outputs breadcrumb navigation with Schema.org JSON-LD.
 *
 * @return void
 */
function greenlight_breadcrumbs() {
	if ( ! function_exists( 'greenlight_get_breadcrumb_items' ) ) {
		return;
	}

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

	foreach ( $items as $index => $item ) {
		$is_last = ( count( $items ) - 1 ) === $index;
		$label   = isset( $item['name'] ) ? $item['name'] : '';
		$url     = isset( $item['url'] ) ? $item['url'] : '';

		if ( ! $is_last && '' !== $url ) {
			echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label ) . '</a></li>';
		} else {
			echo '<li><span aria-current="page">' . esc_html( $label ) . '</span></li>';
		}
	}

	echo '</ol>';
	echo '</nav>';
}
