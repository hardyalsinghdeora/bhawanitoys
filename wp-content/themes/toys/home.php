<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Stepnie_theme
 * @since Stepnie theme 1.0
 */

get_header(); ?>
<?php ?>
<div class="container-fluid slogan">
    <div class="icon-bar">
        <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Header Social Media Area')) : ?>
        <?php endif; ?>
    </div>
    <div class="container" >
        <h1><span><?php single_post_title(); ?></span></h1>
        <?php if ( have_posts() ) : ?>

            <?php /* Start the Loop */ ?>
            <?php while ( have_posts() ) : the_post(); ?>
                <?php get_template_part( 'content', get_post_format() ); ?>
            <?php endwhile; ?>

            <?php if(is_front_page()): ?>
            <?php endif; ?>
                <?php $terms = get_terms( 'product_tag' ); ?>                
                <div class="product_container">
                    <?php if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) : ?>
                        <?php foreach ( $terms as $term ) : ?>
                        <div class="col-sm-4">
                            <div class="products"> 
                                <a href="">
                                    <img src="assets/images/banner_1.jpg" class="img-responsive w-100" />
                                    <h1 class="pressure-wash-title"><?php echo $term->name; ?></h1>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>


        <?php else : ?>

            <article id="post-0" class="post no-results not-found">
                <?php if ( current_user_can( 'edit_posts' ) ) :
                    // Show a different message to a logged-in user who can add posts.
                ?>
                    <header class="entry-header">
                        <h1 class="entry-title"><?php _e( 'No posts to display', 'theme' ); ?></h1>
                    </header>

                    <div class="entry-content">
                        <p><?php printf( __( 'Ready to publish your first post? <a href="%s">Get started here</a>.', 'stephanie' ), admin_url( 'post-new.php' ) ); ?></p>
                    </div><!-- .entry-content -->

                <?php else :
                    // Show the default message to everyone else.
                ?>

                <div class="entry-content">
                    <p><?php _e( 'Apologies, but no results were found. Perhaps searching will help find a related post.', 'stephanie' ); ?></p>
                    <?php get_search_form(); ?>
                </div><!-- .entry-content -->
            <?php endif; // end current_user_can() check ?>

            </article><!-- #post-0 -->

        <?php endif; // end have_posts() check ?>

    </div>
</div>
<?php get_footer(); ?>