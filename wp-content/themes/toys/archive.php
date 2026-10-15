<?php
/**
 * The template for displaying archive pages.
 *
 * Learn more: https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package storefront
 */

get_header(); ?>

    <div class="welcome_box">
	    <div class="container">
	        <div class="col-sm-12" >

			<?php if ( have_posts() ) : ?>

				<header class="page-header">
					<?php
						the_archive_title( '<h1 class="page-title">', '</h1>' );
						the_archive_description( '<div class="taxonomy-description">', '</div>' );
					?>
				</header><!-- .page-header -->

				<?php
				get_template_part( 'loop' );

			else :

				get_template_part( 'content', 'none' );

			endif; ?>

			</div>
		</div>
	</div>
<?php
do_action( 'storefront_sidebar' );
get_footer();
