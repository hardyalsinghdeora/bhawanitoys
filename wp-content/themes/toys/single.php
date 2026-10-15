<?php
/**
 * The template for displaying all single posts.
 *
 * @package storefront
 */

get_header(); ?>

    <div class="welcome_box">
    	<div class="container">
            <div class="col-sm-12" >

    		<?php
    		while ( have_posts() ) :
    			the_post();

    			do_action( 'storefront_single_post_before' );

    			get_template_part( 'content', 'single' );

    			do_action( 'storefront_single_post_after' );

    		endwhile; // End of the loop.
    		?>

            </div>
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
do_action( 'storefront_sidebar' );
get_footer();