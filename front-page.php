<?php
/**
 * The front page template.
 *
 * @package Greenlight
 */

get_header();

$_gl_app              = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_hero_badge       = ! empty( $_gl_app['show_hero_badge'] );
$_gl_use_rich_hero    = ! isset( $_gl_app['hero_enabled'] ) || ! empty( $_gl_app['hero_enabled'] );
$_gl_hero_style       = isset( $_gl_app['hero_style'] ) && 'centered' === $_gl_app['hero_style'] ? 'centered' : 'asymmetric';
$_gl_hero_mode        = isset( $_gl_app['hero_background_mode'] ) && in_array( $_gl_app['hero_background_mode'], array( 'none', 'color', 'gradient', 'image' ), true ) ? $_gl_app['hero_background_mode'] : 'none';
$_gl_hero_height      = isset( $_gl_app['hero_height_mode'] ) && in_array( $_gl_app['hero_height_mode'], array( 'content', 'tall', 'full' ), true ) ? $_gl_app['hero_height_mode'] : 'content';
$_gl_hero_overlay     = isset( $_gl_app['hero_overlay_strength'] ) && in_array( $_gl_app['hero_overlay_strength'], array( 'none', 'soft', 'strong' ), true ) ? $_gl_app['hero_overlay_strength'] : 'soft';
$_gl_hero_overlay_dir = isset( $_gl_app['hero_overlay_direction'] ) && in_array( $_gl_app['hero_overlay_direction'], array( 'full', 'top', 'bottom', 'left', 'right' ), true ) ? sanitize_key( $_gl_app['hero_overlay_direction'] ) : 'full';
$_gl_hero_text        = isset( $_gl_app['hero_text'] ) ? trim( (string) $_gl_app['hero_text'] ) : '';
$_gl_hero_cls         = 'page-hero page-hero--' . $_gl_hero_style . ' page-hero--background-' . $_gl_hero_mode . ' page-hero--height-' . $_gl_hero_height . ' page-hero--overlay-' . $_gl_hero_overlay . ' page-hero--overlay-dir-' . $_gl_hero_overlay_dir;
$_gl_hero_style_attr  = array();
$_gl_gradient_presets = function_exists( 'greenlight_get_hero_gradient_presets' ) ? greenlight_get_hero_gradient_presets() : array();

if ( 'color' === $_gl_hero_mode && ! empty( $_gl_app['hero_background_color'] ) ) {
	$_gl_color = sanitize_hex_color( $_gl_app['hero_background_color'] ?? '' );
	if ( $_gl_color ) {
		$_gl_hero_style_attr[] = '--greenlight-hero-background:' . $_gl_color;
	}
} elseif ( 'gradient' === $_gl_hero_mode ) {
	$_gl_gradient_key = isset( $_gl_app['hero_gradient_preset'] ) ? sanitize_key( (string) $_gl_app['hero_gradient_preset'] ) : 'moss';
	if ( isset( $_gl_gradient_presets[ $_gl_gradient_key ]['value'] ) ) {
		$_gl_hero_style_attr[] = '--greenlight-hero-background:' . $_gl_gradient_presets[ $_gl_gradient_key ]['value'];
	}
} elseif ( 'image' === $_gl_hero_mode && ! empty( $_gl_app['hero_background_image'] ) ) {
	$_gl_image_url = esc_url_raw( (string) $_gl_app['hero_background_image'] );
	if ( '' !== $_gl_image_url ) {
		$_gl_hero_style_attr[] = '--greenlight-hero-background-image:url("' . $_gl_image_url . '")';
	}
}

$_gl_overlay_opacity   = isset( $_gl_app['hero_overlay_opacity'] ) ? absint( $_gl_app['hero_overlay_opacity'] ) : 40;
$_gl_hero_style_attr[] = '--greenlight-overlay-opacity:' . ( $_gl_overlay_opacity / 100 );
$_gl_hero_style_attr   = implode( ';', $_gl_hero_style_attr );

// CTA boutons hero.
$_gl_has_real_hero_url = static function ( $url ) {
	$url = trim( (string) $url );

	return '' !== $url && '#' !== $url;
};

$_gl_cta1_on = ! empty( $_gl_app['hero_cta_enabled'] ) && '' !== trim( $_gl_app['hero_cta_text'] ?? '' ) && $_gl_has_real_hero_url( $_gl_app['hero_cta_url'] ?? '' );
$_gl_cta2_on = ! empty( $_gl_app['hero_cta2_enabled'] ) && '' !== trim( $_gl_app['hero_cta2_text'] ?? '' ) && $_gl_has_real_hero_url( $_gl_app['hero_cta2_url'] ?? '' );
$_gl_cta_pos = isset( $_gl_app['hero_cta_position'] ) && in_array( $_gl_app['hero_cta_position'], array( 'lead', 'body', 'center' ), true ) ? $_gl_app['hero_cta_position'] : 'lead';
$_gl_has_cta = $_gl_cta1_on || $_gl_cta2_on;

$_gl_render_hero_cta = static function ( $text, $url, $style ) {
	$cls = 'hero-cta hero-cta--' . esc_attr( $style );
	return '<a href="' . esc_url( $url ) . '" class="' . $cls . '">' . esc_html( $text ) . '</a>';
};

$_gl_cta_html = '';
if ( $_gl_has_cta ) {
	$_gl_cta_html .= '<div class="hero-cta-group' . ( 'center' === $_gl_cta_pos ? ' hero-cta-group--center' : '' ) . '">';
	if ( $_gl_cta1_on ) {
		$_gl_cta_html .= $_gl_render_hero_cta( $_gl_app['hero_cta_text'], $_gl_app['hero_cta_url'], $_gl_app['hero_cta_style'] ?? 'primary' );
	}
	if ( $_gl_cta2_on ) {
		$_gl_cta_html .= $_gl_render_hero_cta( $_gl_app['hero_cta2_text'], $_gl_app['hero_cta2_url'], $_gl_app['hero_cta2_style'] ?? 'secondary' );
	}
	$_gl_cta_html .= '</div>';
}

/**
 * Builds front-page heading and description from appearance options.
 *
 * @param array<string, mixed> $appearance Appearance settings.
 * @param string               $legacy     Legacy hero text.
 * @param string               $page_title Current page title.
 * @param string               $page_desc  Current page description.
 * @return array{heading: string, description: string}
 */
$greenlight_build_front_intro = static function ( $appearance, $legacy, $page_title, $page_desc ) {
	$heading_mode    = isset( $appearance['hero_heading_mode'] ) ? sanitize_key( (string) $appearance['hero_heading_mode'] ) : 'page_title';
	$subheading_mode = isset( $appearance['hero_subheading_mode'] ) ? sanitize_key( (string) $appearance['hero_subheading_mode'] ) : 'page_excerpt';
	$heading         = '';
	$description     = '';

	if ( 'site_title' === $heading_mode ) {
		$heading = get_bloginfo( 'name' );
	} elseif ( 'custom' === $heading_mode ) {
		$heading = isset( $appearance['hero_heading_text'] ) ? trim( (string) $appearance['hero_heading_text'] ) : '';
	} elseif ( 'none' !== $heading_mode ) {
		$heading = $page_title;
	}

	if ( 'site_tagline' === $subheading_mode ) {
		$description = get_bloginfo( 'description' );
	} elseif ( 'custom' === $subheading_mode ) {
		$description = isset( $appearance['hero_subheading_text'] ) ? trim( (string) $appearance['hero_subheading_text'] ) : '';
	} elseif ( 'none' !== $subheading_mode ) {
		$description = $page_desc;
	}

	if ( '' === $description && '' !== $legacy ) {
		$description = $legacy;
	}

	return array(
		'heading'     => $heading,
		'description' => $description,
	);
};

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$_gl_intro = $greenlight_build_front_intro(
			$_gl_app,
			$_gl_hero_text,
			get_the_title(),
			has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' )
		);
		?>
		<?php if ( $_gl_use_rich_hero ) : ?>
			<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>" <?php if ( '' !== $_gl_hero_style_attr ) : ?>style="<?php echo esc_attr( $_gl_hero_style_attr ); ?>"<?php endif; ?> <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Hero principal', 'greenlight' ) . '"'; ?>>
				<div class="hero-lead">
					<?php if ( $_gl_hero_badge ) : ?>
						<?php echo greenlight_carbon_badge( 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
					<?php if ( '' !== $_gl_intro['heading'] ) : ?>
						<h1 id="hero-heading"><?php echo esc_html( $_gl_intro['heading'] ); ?></h1>
					<?php endif; ?>
					<?php if ( $_gl_has_cta && 'lead' === $_gl_cta_pos ) : ?>
						<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</div>
				<?php if ( '' !== $_gl_intro['description'] ) : ?>
					<div class="hero-body">
						<p class="hero-description"><?php echo esc_html( $_gl_intro['description'] ); ?></p>
						<?php if ( $_gl_has_cta && 'body' === $_gl_cta_pos ) : ?>
							<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<?php if ( $_gl_has_cta && 'center' === $_gl_cta_pos ) : ?>
					<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</section>
		<?php else : ?>
			<section class="page-intro-simple" <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Introduction principale', 'greenlight' ) . '"'; ?>>
				<?php if ( $_gl_hero_badge ) : ?>
					<?php echo greenlight_carbon_badge( 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<?php if ( '' !== $_gl_intro['heading'] ) : ?>
					<h1 id="hero-heading"><?php echo esc_html( $_gl_intro['heading'] ); ?></h1>
				<?php endif; ?>
				<?php if ( '' !== $_gl_intro['description'] ) : ?>
					<p class="hero-description"><?php echo esc_html( $_gl_intro['description'] ); ?></p>
				<?php endif; ?>
			</section>
		<?php endif; ?>
		<main id="main-content" class="site-main">
		<?php if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) : ?>
			<div class="page-content">
				<?php the_content(); ?>
			</div>
		<?php endif; ?>
		<?php
	endwhile;
else :
	$_gl_intro = $greenlight_build_front_intro(
		$_gl_app,
		$_gl_hero_text,
		get_bloginfo( 'name' ),
		get_bloginfo( 'description' )
	);
	?>
	<?php if ( $_gl_use_rich_hero ) : ?>
		<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>" <?php if ( '' !== $_gl_hero_style_attr ) : ?>style="<?php echo esc_attr( $_gl_hero_style_attr ); ?>"<?php endif; ?> <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Hero principal', 'greenlight' ) . '"'; ?>>
			<div class="hero-lead">
				<?php if ( $_gl_hero_badge ) : ?>
					<?php echo greenlight_carbon_badge( 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<?php if ( '' !== $_gl_intro['heading'] ) : ?>
					<h1 id="hero-heading"><?php echo esc_html( $_gl_intro['heading'] ); ?></h1>
				<?php endif; ?>
				<?php if ( $_gl_has_cta && 'lead' === $_gl_cta_pos ) : ?>
					<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>
			<?php if ( '' !== $_gl_intro['description'] ) : ?>
				<div class="hero-body">
					<p class="hero-description"><?php echo esc_html( $_gl_intro['description'] ); ?></p>
					<?php if ( $_gl_has_cta && 'body' === $_gl_cta_pos ) : ?>
						<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if ( $_gl_has_cta && 'center' === $_gl_cta_pos ) : ?>
				<?php echo $_gl_cta_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</section>
	<?php else : ?>
		<section class="page-intro-simple" <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Introduction principale', 'greenlight' ) . '"'; ?>>
			<?php if ( $_gl_hero_badge ) : ?>
				<?php echo greenlight_carbon_badge( 'top' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
			<?php if ( '' !== $_gl_intro['heading'] ) : ?>
				<h1 id="hero-heading"><?php echo esc_html( $_gl_intro['heading'] ); ?></h1>
			<?php endif; ?>
			<?php if ( '' !== $_gl_intro['description'] ) : ?>
				<p class="hero-description"><?php echo esc_html( $_gl_intro['description'] ); ?></p>
			<?php endif; ?>
		</section>
	<?php endif; ?>
	<main id="main-content" class="site-main">
<?php
endif;

get_footer();
