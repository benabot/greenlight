<?php
/**
 * The front page template.
 *
 * @package Greenlight
 */

get_header();

$_gl_app        = array_merge(
	function_exists( 'greenlight_get_appearance_defaults' ) ? greenlight_get_appearance_defaults() : array(),
	(array) get_option( 'greenlight_appearance_options', array() )
);
$_gl_hero_badge = ! empty( $_gl_app['show_hero_badge'] );
$_gl_hero_style = isset( $_gl_app['hero_style'] ) && 'centered' === $_gl_app['hero_style'] ? 'centered' : 'asymmetric';
$_gl_hero_text  = isset( $_gl_app['hero_text'] ) ? trim( $_gl_app['hero_text'] ) : '';
$_gl_hero_cls   = 'page-hero page-hero--' . $_gl_hero_style;

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		$_gl_desc = '' !== $_gl_hero_text
			? $_gl_hero_text
			: ( has_excerpt() ? get_the_excerpt() : get_bloginfo( 'description' ) );
		?>
		<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>" aria-labelledby="hero-heading">
			<div class="hero-lead">
				<?php if ( $_gl_hero_badge ) : ?>
					<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
				<h1 id="hero-heading"><?php the_title(); ?></h1>
			</div>
			<div class="hero-body">
				<?php if ( $_gl_desc ) : ?>
					<p class="hero-description"><?php echo esc_html( $_gl_desc ); ?></p>
				<?php endif; ?>
			</div>
		</section>
		<?php
		if ( '' !== trim( (string) get_post_field( 'post_content', get_the_ID() ) ) ) :
			?>
			<section class="page-content">
				<?php the_content(); ?>
			</section>
			<?php
		endif;
	endwhile;
else :
	$_gl_desc = '' !== $_gl_hero_text ? $_gl_hero_text : get_bloginfo( 'description' );
	?>
	<section class="<?php echo esc_attr( $_gl_hero_cls ); ?>" aria-labelledby="hero-heading">
		<div class="hero-lead">
			<?php if ( $_gl_hero_badge ) : ?>
				<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
			<h1 id="hero-heading"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		</div>
		<div class="hero-body">
			<?php if ( $_gl_desc ) : ?>
				<p class="hero-description"><?php echo esc_html( $_gl_desc ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
endif;

get_footer();
