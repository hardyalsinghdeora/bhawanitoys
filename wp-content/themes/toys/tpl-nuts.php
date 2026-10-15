<?php
/**
 *
 * Template Name: Nuts Template
 *
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages and that
 * other 'pages' on your WordPress site will use a different template.
 *
 * @package WordPress
 * @subpackage TEPL_theme
 * @since TEPL theme 1.0
 */

get_header(); ?>
<div class="container">
	<div class="contact_bg">						
		<div class="contact_form">
		<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h2 class="entry-title"><?php the_title(); ?></h2>
				<div class="entry-content">
					<?php the_content(); ?>
					<?php wp_link_pages( array( 'before' => '<div class="page-links">' . __( 'Pages:', 'tepl' ), 'after' => '</div>' ) ); ?>
				</div><!-- .entry-content -->
				<footer class="entry-meta">
					<?php edit_post_link( __( 'Edit', 'tepl' ), '<span class="edit-link">', '</span>' ); ?>
				</footer><!-- .entry-meta -->
			</article><!-- #post -->
			<?php comments_template( '', true ); ?>
		<?php endwhile; // end of the loop. ?>
		</div>
	</div><!-- #content -->
	<?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Contact Content Area')) : ?>
	<?php endif; ?>
</div><!-- #main -->
<?php get_footer(); ?>
