<?php
/**
 * The front page template.
 *
 * @package Greenlight
 */

get_header();

if ( have_posts() ) :
	while ( have_posts() ) :
		the_post();
		?>
		<section class="page-hero" aria-labelledby="hero-heading">
			<div class="hero-lead">
				<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<h1 id="hero-heading"><?php the_title(); ?></h1>
			</div>
			<div class="hero-body">
				<?php if ( has_excerpt() ) : ?>
					<p class="hero-description"><?php echo esc_html( get_the_excerpt() ); ?></p>
				<?php elseif ( get_bloginfo( 'description' ) ) : ?>
					<p class="hero-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
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
	?>
	<section class="page-hero" aria-labelledby="hero-heading">
		<div class="hero-lead">
			<?php echo greenlight_carbon_badge(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<h1 id="hero-heading"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
		</div>
		<div class="hero-body">
			<?php if ( get_bloginfo( 'description' ) ) : ?>
				<p class="hero-description"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
endif;

get_footer();
