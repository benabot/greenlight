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
		'enable_avif'            => 0,
		'avif_quality'           => 70,
		'max_original_width'     => 2560,
		'keep_original_copy'     => 0,
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
		'enable_avif'            => isset( $input['enable_avif'] ) ? 1 : 0,
		'avif_quality'           => isset( $input['avif_quality'] ) ? min( 100, max( 1, absint( $input['avif_quality'] ) ) ) : $defaults['avif_quality'],
		'max_original_width'     => isset( $input['max_original_width'] ) ? max( 800, absint( $input['max_original_width'] ) ) : $defaults['max_original_width'],
		'keep_original_copy'     => isset( $input['keep_original_copy'] ) ? 1 : 0,
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
 * @param string|array          $_size Requested size.
 * @return array<string, string>
 */
function greenlight_filter_attachment_image_attributes( $attr, $attachment, $_size ) {
	unset( $_size );

	if ( is_admin() || ! $attachment instanceof WP_Post ) {
		return $attr;
	}

	$attachment_id = (int) $attachment->ID;
	$is_hero       = greenlight_get_hero_image_attachment_id() === $attachment_id;

	$attr['decoding'] = 'async';

	if ( $is_hero ) {
		$attr['loading']       = 'eager';
		$attr['fetchpriority'] = 'high';

		return $attr;
	}

	$attr['loading']       = 'lazy';
	$attr['fetchpriority'] = 'low';

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'greenlight_filter_attachment_image_attributes', 10, 3 );

/**
 * AVIF support.
 */

/**
 * Returns whether AVIF generation is available and enabled.
 *
 * @return bool
 */
function greenlight_is_avif_conversion_enabled() {
	$options = greenlight_get_images_options();

	if ( empty( $options['enable_avif'] ) ) {
		return false;
	}

	// Check PHP 8.1+ imageavif or Imagick AVIF support.
	if ( function_exists( 'imageavif' ) ) {
		return true;
	}

	if ( class_exists( 'Imagick' ) ) {
		$imagick = new Imagick();
		$formats = $imagick->queryFormats( 'AVIF' );

		if ( ! empty( $formats ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Returns the AVIF quality to use.
 *
 * @return int
 */
function greenlight_get_avif_quality() {
	return min( 100, max( 1, absint( greenlight_get_images_option( 'avif_quality' ) ) ) );
}

/**
 * Returns the sidecar AVIF path for a source image.
 *
 * @param string $file_path Source file path.
 * @return string
 */
function greenlight_get_avif_sidecar_path( $file_path ) {
	$path_info = pathinfo( $file_path );

	if ( empty( $path_info['dirname'] ) || empty( $path_info['filename'] ) ) {
		return '';
	}

	return trailingslashit( $path_info['dirname'] ) . $path_info['filename'] . '.avif';
}

/**
 * Generates an AVIF sidecar file during upload.
 *
 * @param array<string, string> $upload Upload data.
 * @return array<string, string>
 */
function greenlight_generate_avif_on_upload( $upload ) {
	if ( ! greenlight_is_avif_conversion_enabled() || empty( $upload['file'] ) ) {
		return $upload;
	}

	$source_path = $upload['file'];
	$source_type = wp_check_filetype( $source_path );

	if ( empty( $source_type['type'] ) || 0 !== strpos( $source_type['type'], 'image/' ) || 'image/avif' === $source_type['type'] || 'image/webp' === $source_type['type'] ) {
		return $upload;
	}

	$avif_path = greenlight_get_avif_sidecar_path( $source_path );

	if ( '' === $avif_path ) {
		return $upload;
	}

	$editor = wp_get_image_editor( $source_path );

	if ( is_wp_error( $editor ) ) {
		return $upload;
	}

	$editor->set_quality( greenlight_get_avif_quality() );
	$editor->save( $avif_path, 'image/avif' );

	return $upload;
}
add_filter( 'wp_handle_upload', 'greenlight_generate_avif_on_upload', 21 );

/**
 * Resize originals on upload.
 */

/**
 * Resizes uploaded images that exceed the maximum width.
 *
 * @param array<string, string> $upload Upload data.
 * @return array<string, string>
 */
function greenlight_resize_original_on_upload( $upload ) {
	if ( empty( $upload['file'] ) ) {
		return $upload;
	}

	$source_type = wp_check_filetype( $upload['file'] );

	if ( empty( $source_type['type'] ) || 0 !== strpos( $source_type['type'], 'image/' ) ) {
		return $upload;
	}

	$options   = greenlight_get_images_options();
	$max_width = isset( $options['max_original_width'] ) ? absint( $options['max_original_width'] ) : 2560;

	if ( $max_width < 800 ) {
		return $upload;
	}

	$editor = wp_get_image_editor( $upload['file'] );

	if ( is_wp_error( $editor ) ) {
		return $upload;
	}

	$size = $editor->get_size();

	if ( empty( $size['width'] ) || $size['width'] <= $max_width ) {
		return $upload;
	}

	// Keep original copy if option enabled.
	if ( ! empty( $options['keep_original_copy'] ) ) {
		$path_info = pathinfo( $upload['file'] );
		$backup    = trailingslashit( $path_info['dirname'] ) . $path_info['filename'] . '_original.' . $path_info['extension'];
		copy( $upload['file'], $backup );
	}

	$editor->resize( $max_width, null );
	$editor->save( $upload['file'] );

	return $upload;
}
add_filter( 'wp_handle_upload', 'greenlight_resize_original_on_upload', 5 );
