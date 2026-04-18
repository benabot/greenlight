<?php
/**
 * SEO post meta registration and editor integrations.
 *
 * @package Greenlight
 */

/**
 * Returns the post types that support Greenlight SEO fields.
 *
 * @return string[]
 */
function greenlight_get_seo_post_types() {
	$post_types = get_post_types(
		array(
			'public' => true,
		),
		'names'
	);

	unset( $post_types['attachment'] );

	return array_values( $post_types );
}

/**
 * Checks whether the current user can edit SEO meta.
 *
 * @param mixed  $allowed Whether meta is currently allowed.
 * @param string $meta_key Meta key.
 * @param int    $post_id Post ID.
 * @return bool
 */
function greenlight_can_edit_seo_meta( $allowed = null, $meta_key = '', $post_id = 0 ) {
	$post_id = absint( $post_id );

	if ( $post_id > 0 ) {
		return current_user_can( 'edit_post', $post_id );
	}

	return current_user_can( 'edit_posts' );
}

/**
 * Registers SEO post meta for supported public post types.
 *
 * @return void
 */
function greenlight_register_seo_post_meta() {
	$post_types = greenlight_get_seo_post_types();

	foreach ( $post_types as $post_type ) {
		register_post_meta(
			$post_type,
			GREENLIGHT_SEO_TITLE_META_KEY,
			array(
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'greenlight_sanitize_seo_text',
				'show_in_rest'      => true,
				'auth_callback'     => 'greenlight_can_edit_seo_meta',
			)
		);

		register_post_meta(
			$post_type,
			GREENLIGHT_SEO_DESCRIPTION_META_KEY,
			array(
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'greenlight_sanitize_seo_textarea',
				'show_in_rest'      => true,
				'auth_callback'     => 'greenlight_can_edit_seo_meta',
			)
		);

		register_post_meta(
			$post_type,
			GREENLIGHT_SEO_IMAGE_META_KEY,
			array(
				'single'            => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'show_in_rest'      => true,
				'auth_callback'     => 'greenlight_can_edit_seo_meta',
			)
		);

		register_post_meta(
			$post_type,
			GREENLIGHT_SEO_NOINDEX_META_KEY,
			array(
				'single'            => true,
				'type'              => 'boolean',
				'sanitize_callback' => 'greenlight_sanitize_seo_boolean',
				'show_in_rest'      => true,
				'auth_callback'     => 'greenlight_can_edit_seo_meta',
			)
		);
	}
}
add_action( 'init', 'greenlight_register_seo_post_meta' );

/**
 * Registers the native SEO meta box.
 *
 * @return void
 */
function greenlight_register_seo_meta_box() {
	$post_types = greenlight_get_seo_post_types();

	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'greenlight-seo',
			__( 'SEO', 'greenlight' ),
			'greenlight_render_seo_meta_box',
			$post_type,
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'greenlight_register_seo_meta_box' );

/**
 * Renders the SEO meta box fields.
 *
 * @param WP_Post $post Current post object.
 * @return void
 */
function greenlight_render_seo_meta_box( $post ) {
	$meta = greenlight_get_seo_post_meta_values( $post->ID );

	wp_nonce_field( 'greenlight_save_seo_meta', 'greenlight_seo_nonce' );
	?>
	<p>
		<label for="greenlight-seo-title"><strong><?php esc_html_e( 'Titre SEO', 'greenlight' ); ?></strong></label><br>
		<input id="greenlight-seo-title" name="greenlight_seo_title" type="text" class="widefat" value="<?php echo esc_attr( $meta['title'] ); ?>">
	</p>

	<p>
		<label for="greenlight-seo-description"><strong><?php esc_html_e( 'Meta description', 'greenlight' ); ?></strong></label><br>
		<textarea id="greenlight-seo-description" name="greenlight_seo_description" class="widefat" rows="4"><?php echo esc_textarea( $meta['description'] ); ?></textarea>
	</p>

	<p>
		<label for="greenlight-seo-image"><strong><?php esc_html_e( 'Image Open Graph', 'greenlight' ); ?></strong></label><br>
		<input id="greenlight-seo-image" name="greenlight_seo_image" type="number" min="0" class="small-text" value="<?php echo esc_attr( $meta['image_id'] ); ?>">
	</p>

	<p>
		<label for="greenlight-seo-noindex">
			<input id="greenlight-seo-noindex" name="greenlight_seo_noindex" type="checkbox" value="1" <?php checked( $meta['noindex'] ); ?>>
			<?php esc_html_e( 'Demander l exclusion de cette page des index.', 'greenlight' ); ?>
		</label>
	</p>
	<?php
}

/**
 * Saves the SEO meta box values.
 *
 * @param int $post_id Current post ID.
 * @return void
 */
function greenlight_save_seo_meta_box( $post_id ) {
	if ( ! isset( $_POST['greenlight_seo_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['greenlight_seo_nonce'] ) ), 'greenlight_save_seo_meta' ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( wp_is_post_revision( $post_id ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$seo_title_raw       = isset( $_POST['greenlight_seo_title'] ) ? sanitize_text_field( wp_unslash( $_POST['greenlight_seo_title'] ) ) : '';
	$seo_description_raw = isset( $_POST['greenlight_seo_description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['greenlight_seo_description'] ) ) : '';

	$title       = greenlight_sanitize_seo_text( $seo_title_raw );
	$description = greenlight_sanitize_seo_textarea( $seo_description_raw );
	$image_id    = isset( $_POST['greenlight_seo_image'] ) ? absint( wp_unslash( $_POST['greenlight_seo_image'] ) ) : 0;
	$noindex     = isset( $_POST['greenlight_seo_noindex'] ) ? 1 : 0;

	if ( '' !== $title ) {
		update_post_meta( $post_id, GREENLIGHT_SEO_TITLE_META_KEY, $title );
	} else {
		delete_post_meta( $post_id, GREENLIGHT_SEO_TITLE_META_KEY );
	}

	if ( '' !== $description ) {
		update_post_meta( $post_id, GREENLIGHT_SEO_DESCRIPTION_META_KEY, $description );
	} else {
		delete_post_meta( $post_id, GREENLIGHT_SEO_DESCRIPTION_META_KEY );
	}

	if ( $image_id > 0 ) {
		update_post_meta( $post_id, GREENLIGHT_SEO_IMAGE_META_KEY, $image_id );
	} else {
		delete_post_meta( $post_id, GREENLIGHT_SEO_IMAGE_META_KEY );
	}

	if ( $noindex ) {
		update_post_meta( $post_id, GREENLIGHT_SEO_NOINDEX_META_KEY, 1 );
	} else {
		delete_post_meta( $post_id, GREENLIGHT_SEO_NOINDEX_META_KEY );
	}
}
add_action( 'save_post', 'greenlight_save_seo_meta_box' );

/**
 * Enqueues the Gutenberg SEO sidebar.
 *
 * @return void
 */
function greenlight_enqueue_seo_sidebar() {
	$screen = get_current_screen();

	if ( ! $screen || 'post' !== $screen->base ) {
		return;
	}

	$perf        = get_option( 'greenlight_performance_options', array() );
	$use_min     = ! empty( $perf['enable_js_min'] );
	$script_file = ( $use_min && file_exists( get_theme_file_path( 'assets/js/seo-sidebar.min.js' ) ) )
		? 'assets/js/seo-sidebar.min.js'
		: 'assets/js/seo-sidebar.js';
	$script_path = get_theme_file_path( $script_file );

	wp_enqueue_script(
		'greenlight-seo-sidebar',
		get_theme_file_uri( $script_file ),
		array(
			'wp-block-editor',
			'wp-components',
			'wp-core-data',
			'wp-data',
			'wp-edit-post',
			'wp-element',
			'wp-i18n',
			'wp-plugins',
		),
		filemtime( $script_path ),
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'greenlight_enqueue_seo_sidebar' );
