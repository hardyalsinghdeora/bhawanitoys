<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * @package WordPress
 * @subpackage Stepnie_theme
 * @since Stepnie theme 1.0
 */
get_header(); ?>
    <div class="col-sm-12" >
        <div class="welcome-box">
			<h1><span><?php _e( 'Not Found', 'stepnie' ); ?></span></h1>
			<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'stepnie' ); ?></p>
			<?php get_search_form(); ?>
		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_footer(); ?>