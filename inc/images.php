<?php
/**
 * Image size, WebP, and responsive image helpers.
 *
 * @package Greenlight
 */

if ( ! defined( 'GREENLIGHT_IMAGES_OPTION_KEY' ) ) {
	define( 'GREENLIGHT_IMAGES_OPTION_KEY', 'greenlight_images_options' );
}

/**
 * Returns the default image option values.
 *
 * @return array<string, mixed>
 */
function greenlight_get_images_defaults() {
	return array(
		'enable_webp_conversion' => 1,
		'webp_quality'           => 82,
		'remove_core_sizes'      => 1,
	);
}

/**
 * Returns the merged image options.
 *
 * @return array<string, mixed>
 */
function greenlight_get_images_options() {
	$options = get_option( GREENLIGHT_IMAGES_OPTION_KEY, array() );

	if ( ! is_array( $options ) ) {
		$options = array();
	}

	return wp_parse_args( $options, greenlight_get_images_defaults() );
}

/**
 * Returns a single image option value.
 *
 * @param string $key Option key.
 * @return mixed
 */
function greenlight_get_images_option( $key ) {
	$options = greenlight_get_images_options();

	return isset( $options[ $key ] ) ? $options[ $key ] : null;
}

/**
 * Sanitizes the image settings array.
 *
 * @param mixed $input Raw submitted settings.
 * @return array<string, mixed>
 */
function greenlight_sanitize_image_settings( $input ) {
	$defaults = greenlight_get_images_defaults();
	$input    = is_array( $input ) ? $input : array();

	return array(
		'enable_webp_conversion' => isset( $input['enable_webp_conversion'] ) ? 1 : 0,
		'webp_quality'           => isset( $input['webp_quality'] ) ? min( 100, max( 1, absint( $input['webp_quality'] ) ) ) : $defaults['webp_quality'],
		'remove_core_sizes'      => isset( $input['remove_core_sizes'] ) ? 1 : 0,
	);
}

/**
 * Registers the custom image sizes.
 *
 * @return void
 */
function greenlight_register_image_sizes() {
	add_image_size( 'greenlight-hero', 1200, 675, true );
	add_image_size( 'greenlight-card', 600, 450, true );
	add_image_size( 'greenlight-thumb', 300, 300, true );
}
add_action( 'after_setup_theme', 'greenlight_register_image_sizes' );

/**
 * Removes unnecessary default image sizes.
 *
 * @return void
 */
function greenlight_remove_core_image_sizes() {
	if ( ! greenlight_sanitize_seo_boolean( greenlight_get_images_option( 'remove_core_sizes' ) ) ) {
		return;
	}

	remove_image_size( 'medium_large' );
	remove_image_size( '1536x1536' );
	remove_image_size( '2048x2048' );
}
add_action( 'init', 'greenlight_remove_core_image_sizes', 20 );

/**
 * Returns whether WebP generation is available and enabled.
 *
 * @return bool
 */
function greenlight_is_webp_conversion_enabled() {
	return greenlight_sanitize_seo_boolean( greenlight_get_images_option( 'enable_webp_conversion' ) ) && wp_image_editor_supports( array( 'mime_type' => 'image/webp' ) );
}

/**
 * Returns the WebP quality to use.
 *
 * @return int
 */
function greenlight_get_webp_quality() {
	return min( 100, max( 1, absint( greenlight_get_images_option( 'webp_quality' ) ) ) );
}

/**
 * Returns the sidecar WebP path for a source image.
 *
 * @param string $file_path Source file path.
 * @return string
 */
function greenlight_get_webp_sidecar_path( $file_path ) {
	$path_info = pathinfo( $file_path );

	if ( empty( $path_info['dirname'] ) || empty( $path_info['filename'] ) ) {
		return '';
	}

	return trailingslashit( $path_info['dirname'] ) . $path_info['filename'] . '.webp';
}

/**
 * Generates a WebP sidecar file during upload.
 *
 * @param array<string, string> $upload Upload data.
 * @return array<string, string>
 */
function greenlight_generate_webp_on_upload( $upload ) {
	if ( ! greenlight_is_webp_conversion_enabled() || empty( $upload['file'] ) ) {
		return $upload;
	}

	$source_path = $upload['file'];
	$source_type = wp_check_filetype( $source_path );

	if ( empty( $source_type['type'] ) || 0 !== strpos( $source_type['type'], 'image/' ) || 'image/webp' === $source_type['type'] ) {
		return $upload;
	}

	$webp_path = greenlight_get_webp_sidecar_path( $source_path );

	if ( '' === $webp_path ) {
		return $upload;
	}

	$editor = wp_get_image_editor( $source_path );

	if ( is_wp_error( $editor ) ) {
		return $upload;
	}

	$editor->set_quality( greenlight_get_webp_quality() );
	$saved = $editor->save( $webp_path, 'image/webp' );

	if ( is_wp_error( $saved ) ) {
		return $upload;
	}

	return $upload;
}
add_filter( 'wp_handle_upload', 'greenlight_generate_webp_on_upload', 20 );

/**
 * Returns the attachment ID that represents the hero image for the current view.
 *
 * @return int
 */
function greenlight_get_hero_image_attachment_id() {
	if ( is_front_page() ) {
		$front_page_id = (int) get_queried_object_id();

		if ( $front_page_id > 0 ) {
			return (int) get_post_thumbnail_id( $front_page_id );
		}
	}

	if ( is_home() && 'page' === get_option( 'show_on_front' ) ) {
		$posts_page_id = (int) get_option( 'page_for_posts' );

		if ( $posts_page_id > 0 ) {
			return (int) get_post_thumbnail_id( $posts_page_id );
		}
	}

	if ( is_singular() ) {
		return (int) get_post_thumbnail_id( get_queried_object_id() );
	}

	return 0;
}

/**
 * Returns the best hero image data for preload output.
 *
 * @return array<string, string>
 */
function greenlight_get_hero_image_data() {
	$attachment_id = greenlight_get_hero_image_attachment_id();

	if ( $attachment_id <= 0 ) {
		return array();
	}

	$image = wp_get_attachment_image_src( $attachment_id, 'greenlight-hero' );

	if ( empty( $image[0] ) ) {
		$image = wp_get_attachment_image_src( $attachment_id, 'full' );
	}

	if ( empty( $image[0] ) ) {
		return array();
	}

	return array(
		'url'    => $image[0],
		'srcset' => (string) wp_get_attachment_image_srcset( $attachment_id, 'greenlight-hero' ),
		'sizes'  => (string) wp_get_attachment_image_sizes( $attachment_id, 'greenlight-hero' ),
	);
}

/**
 * Preloads the hero image when one is available.
 *
 * @return void
 */
function greenlight_preload_hero_image() {
	$hero = greenlight_get_hero_image_data();

	if ( empty( $hero['url'] ) ) {
		return;
	}

	printf(
		'<link rel="preload" as="image" href="%s"%s%s>' . "\n",
		esc_url( $hero['url'] ),
		! empty( $hero['srcset'] ) ? ' imagesrcset="' . esc_attr( $hero['srcset'] ) . '"' : '',
		! empty( $hero['sizes'] ) ? ' imagesizes="' . esc_attr( $hero['sizes'] ) . '"' : ''
	);
}
add_action( 'wp_head', 'greenlight_preload_hero_image', 2 );

/**
 * Forces responsive image loading defaults.
 *
 * @param array<string, string> $attr Image attributes.
 * @param WP_Post               $attachment Attachment object.
 * @param string|array          $size Requested size.
 * @return array<string, string>
 */
function greenlight_filter_attachment_image_attributes( $attr, $attachment, $size ) {
	if ( is_admin() || ! $attachment instanceof WP_Post ) {
		return $attr;
	}

	$attachment_id = (int) $attachment->ID;
	$is_hero       = greenlight_get_hero_image_attachment_id() === $attachment_id;

	$attr['decoding'] = 'async';

	if ( $is_hero ) {
		$attr['loading']      = 'eager';
		$attr['fetchpriority'] = 'high';

		return $attr;
	}

	$attr['loading']      = 'lazy';
	$attr['fetchpriority'] = 'low';

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'greenlight_filter_attachment_image_attributes', 10, 3 );
