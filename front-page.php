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
$_gl_preview_mode     = function_exists( 'greenlight_is_admin_preview_request' ) && greenlight_is_admin_preview_request();
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
$_gl_preview_image     = get_theme_file_uri( 'screenshot.png' );

// CTA boutons hero.
$_gl_cta1_on = ! empty( $_gl_app['hero_cta_enabled'] ) && '' !== trim( $_gl_app['hero_cta_text'] ?? '' ) && '' !== trim( $_gl_app['hero_cta_url'] ?? '' );
$_gl_cta2_on = ! empty( $_gl_app['hero_cta2_enabled'] ) && '' !== trim( $_gl_app['hero_cta2_text'] ?? '' ) && '' !== trim( $_gl_app['hero_cta2_url'] ?? '' );
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
		<?php if ( $_gl_preview_mode || $_gl_use_rich_hero ) : ?>
			<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>"
			<?php if ( '' !== $_gl_hero_style_attr ) : ?>style="<?php echo esc_attr( $_gl_hero_style_attr ); ?>"<?php endif; ?>
			<?php
			if ( $_gl_preview_mode ) :
				?>
				data-greenlight-page-title="<?php echo esc_attr( get_the_title() ); ?>" data-greenlight-page-excerpt="<?php echo esc_attr( has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' ) ); ?>"<?php endif; ?> <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Hero principal', 'greenlight' ) . '"'; ?><?php echo ( $_gl_preview_mode && ! $_gl_use_rich_hero ) ? ' hidden' : ''; ?>>
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
		<?php endif; ?>
		<?php if ( $_gl_preview_mode || ! $_gl_use_rich_hero ) : ?>
			<section class="page-intro-simple"
			<?php
			if ( $_gl_preview_mode ) :
				?>
				data-greenlight-page-title="<?php echo esc_attr( get_the_title() ); ?>" data-greenlight-page-excerpt="<?php echo esc_attr( has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' ) ); ?>"<?php endif; ?> <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Introduction principale', 'greenlight' ) . '"'; ?><?php echo ( $_gl_preview_mode && $_gl_use_rich_hero ) ? ' hidden' : ''; ?>>
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
		if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) :
			?>
			<div class="page-content">
				<?php the_content(); ?>
			</div>
			<?php
		endif;
		if ( $_gl_preview_mode ) :
			?>
			<nav class="greenlight-preview-nav" aria-label="<?php esc_attr_e( 'Sections de l\'aperçu', 'greenlight' ); ?>">
				<a href="#greenlight-preview-hero"><?php esc_html_e( 'Hero', 'greenlight' ); ?></a>
				<a href="#greenlight-preview-archive-title"><?php esc_html_e( 'Archives', 'greenlight' ); ?></a>
				<a href="#greenlight-preview-single-title"><?php esc_html_e( 'Article', 'greenlight' ); ?></a>
				<a href="#greenlight-preview-footer-title"><?php esc_html_e( 'Footer', 'greenlight' ); ?></a>
			</nav>
			<div class="greenlight-preview-stack" aria-label="<?php esc_attr_e( 'Échantillons de mise en page', 'greenlight' ); ?>">
				<section class="greenlight-preview-section" aria-labelledby="greenlight-preview-archive-title">
					<p class="eyebrow"><?php esc_html_e( 'Aperçu archive', 'greenlight' ); ?></p>
					<h2 id="greenlight-preview-archive-title"><?php esc_html_e( 'Cartes et listes d\'articles', 'greenlight' ); ?></h2>
					<p class="greenlight-preview-variant greenlight-preview-archive-variant"><?php esc_html_e( 'Grille asymétrique · Équilibré', 'greenlight' ); ?></p>
					<p class="archive-note"><?php esc_html_e( 'Cette zone reflète les réglages d\'archives et de cartes.', 'greenlight' ); ?></p>
				</section>
				<article class="entry entry--featured greenlight-preview-featured">
					<figure class="entry-media">
						<a class="entry-media-link" href="#preview-featured" tabindex="-1" aria-hidden="true">
							<img src="<?php echo esc_url( $_gl_preview_image ); ?>" alt="">
						</a>
					</figure>
					<div class="entry-body">
						<header class="entry-header">
							<p class="entry-label">
								<a href="#preview-category" class="entry-category"><?php esc_html_e( 'Éditorial', 'greenlight' ); ?></a>
								<time datetime="2026-03-29"><?php esc_html_e( '29 mars 2026', 'greenlight' ); ?></time>
							</p>
							<h2 class="entry-title"><a href="#preview-featured"><?php esc_html_e( 'Article mis en avant', 'greenlight' ); ?></a></h2>
						</header>
						<p class="entry-summary"><?php esc_html_e( 'Un exemple de carte principale pour juger le rythme, la matière et la largeur des textes.', 'greenlight' ); ?></p>
						<a href="#preview-featured" class="entry-more"><?php esc_html_e( 'Lire', 'greenlight' ); ?></a>
					</div>
				</article>
				<ul class="post-list post-list--grid greenlight-preview-archive-list" aria-label="<?php esc_attr_e( 'Exemples d\'articles', 'greenlight' ); ?>">
					<li class="post-item">
						<article class="entry entry--teaser">
							<figure class="entry-media">
								<a class="entry-media-link" href="#preview-one" tabindex="-1" aria-hidden="true">
									<img src="<?php echo esc_url( $_gl_preview_image ); ?>" alt="">
								</a>
							</figure>
							<div class="entry-body">
								<header class="entry-header">
									<p class="entry-label">
										<a href="#preview-category" class="entry-category"><?php esc_html_e( 'Climat', 'greenlight' ); ?></a>
										<time datetime="2026-03-28"><?php esc_html_e( '28 mars 2026', 'greenlight' ); ?></time>
									</p>
									<h2 class="entry-title"><a href="#preview-one"><?php esc_html_e( 'Carte secondaire', 'greenlight' ); ?></a></h2>
								</header>
								<p class="entry-summary"><?php esc_html_e( 'Résumé court pour vérifier l\'équilibre des extraits.', 'greenlight' ); ?></p>
								<a href="#preview-one" class="entry-more"><?php esc_html_e( 'Lire', 'greenlight' ); ?></a>
							</div>
						</article>
					</li>
					<li class="post-item">
						<article class="entry entry--teaser">
							<figure class="entry-media">
								<a class="entry-media-link" href="#preview-two" tabindex="-1" aria-hidden="true">
									<img src="<?php echo esc_url( $_gl_preview_image ); ?>" alt="">
								</a>
							</figure>
							<div class="entry-body">
								<header class="entry-header">
									<p class="entry-label">
										<a href="#preview-category" class="entry-category"><?php esc_html_e( 'Sobriété', 'greenlight' ); ?></a>
										<time datetime="2026-03-27"><?php esc_html_e( '27 mars 2026', 'greenlight' ); ?></time>
									</p>
									<h2 class="entry-title"><a href="#preview-two"><?php esc_html_e( 'Carte alternée', 'greenlight' ); ?></a></h2>
								</header>
								<p class="entry-summary"><?php esc_html_e( 'Deuxième exemple pour valider la cadence des cartes et leur orientation.', 'greenlight' ); ?></p>
								<a href="#preview-two" class="entry-more"><?php esc_html_e( 'Lire', 'greenlight' ); ?></a>
							</div>
						</article>
					</li>
				</ul>
				<section class="greenlight-preview-section" aria-labelledby="greenlight-preview-single-title">
					<p class="eyebrow"><?php esc_html_e( 'Aperçu article', 'greenlight' ); ?></p>
					<h2 id="greenlight-preview-single-title"><?php esc_html_e( 'Article et footer', 'greenlight' ); ?></h2>
					<p class="greenlight-preview-variant greenlight-preview-single-variant"><?php esc_html_e( 'Éditorial', 'greenlight' ); ?></p>
					<p class="archive-note"><?php esc_html_e( 'Cette zone reflète le gabarit article, les tags et la hiérarchie de lecture.', 'greenlight' ); ?></p>
				</section>
				<article class="entry entry--single greenlight-preview-single">
					<header class="entry-header">
						<p class="entry-badges">
							<a href="#preview-category" class="entry-category-pill"><?php esc_html_e( 'Dossier', 'greenlight' ); ?></a>
						</p>
						<h1><?php esc_html_e( 'Exemple de page longue', 'greenlight' ); ?></h1>
						<p class="entry-meta">
							<a href="#preview-author" class="entry-author"><?php esc_html_e( 'Greenlight Studio', 'greenlight' ); ?></a>
							<span class="entry-date"><?php esc_html_e( 'Publié', 'greenlight' ); ?> <time datetime="2026-03-29"><?php esc_html_e( '29 mars 2026', 'greenlight' ); ?></time></span>
						</p>
					</header>
					<figure class="entry-hero-media">
						<img src="<?php echo esc_url( $_gl_preview_image ); ?>" alt="">
					</figure>
					<p class="entry-intro"><?php esc_html_e( 'Un court chapô pour vérifier la largeur de lecture et le ton du gabarit article.', 'greenlight' ); ?></p>
					<section class="entry-content">
						<p><?php esc_html_e( 'Le texte principal sert d\'échantillon pour la largeur de colonne, le rythme vertical et la respiration générale.', 'greenlight' ); ?></p>
						<blockquote><p><?php esc_html_e( 'Le bon aperçu n\'est pas décoratif. Il doit permettre de juger immédiatement le rendu final.', 'greenlight' ); ?></p></blockquote>
						<p><?php esc_html_e( 'Les réglages de densité, de preset et de carte doivent rester visibles ici sans enregistrer.', 'greenlight' ); ?></p>
					</section>
					<footer class="entry-footer">
						<ul class="entry-tags" aria-label="<?php esc_attr_e( 'Tags', 'greenlight' ); ?>">
							<li><a href="#preview-tag-1" class="tag-pill"><?php esc_html_e( 'Éco-conception', 'greenlight' ); ?></a></li>
							<li><a href="#preview-tag-2" class="tag-pill"><?php esc_html_e( 'WordPress', 'greenlight' ); ?></a></li>
						</ul>
					</footer>
				</article>
				<section class="greenlight-preview-section" aria-labelledby="greenlight-preview-footer-title">
					<p class="eyebrow"><?php esc_html_e( 'Aperçu footer', 'greenlight' ); ?></p>
					<h2 id="greenlight-preview-footer-title"><?php esc_html_e( 'Footer et mentions', 'greenlight' ); ?></h2>
					<p class="greenlight-preview-variant greenlight-preview-footer-variant"><?php esc_html_e( 'Séparé', 'greenlight' ); ?></p>
					<p class="archive-note"><?php esc_html_e( 'Cette zone reflète la mise en page du footer, le badge et la navigation basse.', 'greenlight' ); ?></p>
				</section>
				<div class="site-footer greenlight-preview-footer-sample" aria-label="<?php esc_attr_e( 'Échantillon de footer', 'greenlight' ); ?>">
					<p class="footer-copy">
						<span class="greenlight-preview-footer-text">
							&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?>
							<strong><?php echo esc_html( strtoupper( get_bloginfo( 'name' ) ) ); ?></strong>.
							<?php esc_html_e( 'CONÇU POUR DURER.', 'greenlight' ); ?>
						</span>
						<span class="footer-copy__badge"><?php echo greenlight_carbon_badge( 'footer' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					</p>
					<nav class="footer-nav" aria-label="<?php esc_attr_e( 'Navigation secondaire', 'greenlight' ); ?>">
						<ul>
							<li><a href="#preview-footer-1"><?php esc_html_e( 'Archives', 'greenlight' ); ?></a></li>
							<li><a href="#preview-footer-2"><?php esc_html_e( 'À propos', 'greenlight' ); ?></a></li>
							<li><a href="#preview-footer-3"><?php esc_html_e( 'Contact', 'greenlight' ); ?></a></li>
						</ul>
					</nav>
					<p class="footer-emission"><?php esc_html_e( '☘ MODE BASSE ÉMISSION', 'greenlight' ); ?></p>
				</div>
			</div>
			<?php
		endif;
	endwhile;
else :
	$_gl_intro = $greenlight_build_front_intro(
		$_gl_app,
		$_gl_hero_text,
		get_bloginfo( 'name' ),
		get_bloginfo( 'description' )
	);
	?>
	<?php if ( $_gl_preview_mode || $_gl_use_rich_hero ) : ?>
		<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>"
		<?php if ( '' !== $_gl_hero_style_attr ) : ?>style="<?php echo esc_attr( $_gl_hero_style_attr ); ?>"<?php endif; ?>
		<?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Hero principal', 'greenlight' ) . '"'; ?><?php echo ( $_gl_preview_mode && ! $_gl_use_rich_hero ) ? ' hidden' : ''; ?>>
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
	<?php endif; ?>
	<?php if ( $_gl_preview_mode || ! $_gl_use_rich_hero ) : ?>
		<section class="page-intro-simple" <?php echo '' !== $_gl_intro['heading'] ? 'aria-labelledby="hero-heading"' : 'aria-label="' . esc_attr__( 'Introduction principale', 'greenlight' ) . '"'; ?><?php echo ( $_gl_preview_mode && $_gl_use_rich_hero ) ? ' hidden' : ''; ?>>
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
