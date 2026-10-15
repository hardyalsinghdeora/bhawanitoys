<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<title><?php bloginfo('name'); ?> | <?php wp_title(); ?></title>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2.0">
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php wp_head(); ?> 
</head>
  
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php do_action( 'storefront_before_site' ); ?>
<!-- A grey horizontal navbar that becomes vertical on small screens -->
<div class="header">
    <?php do_action( 'storefront_before_header' ); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-8">
                <br>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
                    <div class="pull-left"><h1 class="main-title"><?php bloginfo('name'); ?> 
                        <span><?php bloginfo('description'); ?></span></h1></div>
                    <div class="pull-left"><img src="<?php header_image(); ?>" width="100%" alt=""></div>
                </a>
            </div>
            <div class="col-sm-4">
                <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar('Header Cart Area')) : ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <nav class="nav-menu">
                <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => 'div','container_class' => 'collapse navbar-collapse in', 'container_id' => 'navbar', 'menu_class' => 'nav navbar-nav', 'items_wrap' => '<ul class="nav navbar-nav">%3$s</ul>', ) ); ?>
                <div class="clearfix"></div>
            </nav>
            <div class="clearfix"></div>        
        </div>
        <div class="clearfix"></div>        
    </div>
    <?php do_action( 'storefront_before_content' ); ?>
</div>
<div class="banner">
    <div class="container-fluid">
        <div class="row">
            <?php if(is_front_page()){
                echo do_shortcode( '[twabc-carousel category="home"]' );
            // } else {
            //     $slug = get_post_field( 'post_name', get_post() );
            //     echo do_shortcode( '[twabc-carousel category="'.$slug.'"]' );
            } ?>
        </div>
    </div>
</div>

<?php if ( is_front_page() ) : ?>
    <div class="product_container">
        <div class="col-sm-12">
            <?php //WordPress loop for custom post type
			$parentCategory = get_term_by('name', 'Toys', 'product_cat');
			$product_cat_ID = $parentCategory->term_id;
			$args = array(
				'hierarchical' => 1,
			    'show_option_none' => '',
			    'hide_empty' => 0,
			    'parent' => $product_cat_ID,
			    'taxonomy' => 'product_cat'
			);
			$categories = get_categories($args);
            // var_dump($categories);exit;
            foreach ($categories as $category) {
                    if (function_exists('get_wp_term_image')){
            		    $image_url = get_wp_term_image($category->term_id);
					    //It will give category/term image url 
	                }
                    $category_link = get_category_link( $category->term_id );
                    ?> 
                    <div class="col-sm-4 col-xs-6">
                        <div class="products">
                            <a href="<?php echo esc_url( $category_link ); ?>">
                                <img src="<?php echo $image_url; ?>" width="100%" />
                            	<h1><?php echo $category->name; ?></h1></a>
                        </div>
                    </div>
            <?php } ?>
        </div>
        <div class="clearfix"></div>
    </div>
<?php endif; ?>
<div class="welcome_box">
    <div class="container">
        <div class="col-sm-12" >