<?php get_header(); ?>
    <h1><span><?php single_post_title(); ?></span></h1>
    <?php if ( have_posts() ) : ?>
        <?php /* Start the Loop */ ?>
        <?php while ( have_posts() ) : the_post(); ?>
            <?php the_content(); ?>
        <?php endwhile; ?>
    <?php endif; // end have_posts() check ?>
<?php get_footer(); ?>