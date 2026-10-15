<?php
 

    }
 
 
/**
 * Stepnie theme functions and definitions
 *
 * @package WordPress
 * @subpackage Stepnie_theme
 * @since Stepnie theme 1.0
 */


/**
 * Dequeue the Storefront Parent theme core CSS
 */
function sf_child_theme_dequeue_style() {
    wp_dequeue_style( 'storefront-style' );
    wp_dequeue_style( 'storefront-woocommerce-style' );
}

/**
 * Add feature image
 */
add_theme_support( 'post-thumbnails' ); 
add_action('after_setup_theme','add_custom_sizes');
function add_custom_sizes() {
    add_image_size( 'banner-image', 1024,320 );
}

/**
 * Render shortcode in widget
 */
add_filter('widget_text', 'do_shortcode');

function my_shortcode_function() {
    $i = '<p>Hello World!</p>';
    return $i;
}
add_shortcode('my-shortcode', 'my_shortcode_function');


/**
 * Register menu's
 */
register_nav_menus( array(
	'primary' => __( 'primary menu', 'theme_menu' ),
	'footer_menu'   => __( 'footer menu', 'theme_menu' ),
));

/**
 * Add styles and script
 */
add_action( 'wp_enqueue_scripts', 'custom_enqueue_scripts' );
function custom_enqueue_scripts() {
	// Loads our main stylesheet.
	wp_enqueue_style( 'theme-style', get_stylesheet_uri() ); // echo get_stylesheet_directory_uri();
    wp_enqueue_script('jquery');
	wp_enqueue_script( 'bootstrap-js', get_stylesheet_directory_uri() . '/assets/js/bootstrap.min.js', array( 'jquery' ), '20131205', true );
}

/**
 * Custom Header For Theme 
 */
add_action( 'init', 'theme_header_image' );
function theme_header_image(){
	$defaults = array(
	    'default-image' => '',
	    'random-default' => true,
	    'width' => 200,
	    'height' => 200,
	    'flex-height' => true,
	    'flex-width' => true,
	    'default-text-color' => '#666666',
	    'header-text' => true,
	    'uploads' => true,
	    'wp-head-callback' => '',
	    'admin-head-callback' => '',
	    'admin-preview-callback' => '',
	);
	global $wp_version;

	if (version_compare($wp_version, '3.4', '>='))
	    add_theme_support('custom-header', $defaults);
	else
	    add_custom_image_header($defaults);

	/* To add background image
	  

	$bg_args = array(
		'default-color'          => '',
		'default-image'          => '',
		'wp-head-callback'       => '_custom_background_cb',
		'admin-head-callback'    => '',
		'admin-preview-callback' => ''
	);
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {
		add_theme_support( 'custom-background', $bg_args );
	} else {
		add_custom_background( $bg_args );
	}
	*/
}

/**
 * Add link to Menu
 */
function add_link_atts($atts) {
    $atts['class'] = "nav-link";
    return $atts;
}
add_filter( 'nav_menu_link_attributes', 'add_link_atts');

/**
 * Register widget's
 */
add_action( 'widgets_init', 'theme_resister_widgets' );
function theme_resister_widgets() {
	register_sidebar( array(
		'name'          => __( 'Header Contact Area', 'header_contact_area' ),
		'id'            => 'contact_icon',
		'description'   => __( 'Appears in the header right section.', 'theme' ),
		'before_widget' => '<div id="%1$s" class="call_us_at">',
		'after_widget'  => '</div>',
		'before_title'  => '<span style="display: none">',
		'after_title'   => '</span>',
	) );
    register_sidebar( array(
        'name'          => __( 'Header Cart Area', 'header_cart_area' ),
        'id'            => 'cart_icon',
        'description'   => __( 'Appears in the header section after menu.', 'theme' ),
        'before_widget' => '<div id="%1$s" class="top-icons %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span style="display: none">',
        'after_title'   => '</span>',
    ) );
    register_sidebar( array(
        'name'          => __( 'Footer Copytight Area', 'theme' ),
        'id'            => 'copyright_text',
        'description'   => __( 'Appears in bottom section for copyright.', 'theme' ),
        'before_widget' => '<div id="%1$s" class="copyright %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<span style="display: none">',
        'after_title'   => '</span>',
    ) );
}

/**
 * Register Custom Post Type : News
 */
add_action( 'init', 'stephanie_custom_post_init' );
function stephanie_custom_post_init() {
    $args = array(
        'label' => 'Services',
        'public' => true,
        'show_ui' => true,
        'capability_type' => 'post',
        'hierarchical' => true,
        //'exclude_from_search' => true,
        'rewrite' => array('slug' => 'services'),
        'query_var' => true,
        'menu_icon' => 'dashicons-clipboard',
        'taxonomies' => array(
            'category',
            //'post_tag'
        ),
        'supports' => array(
            'title',
            'editor',
            'thumbnail',
            //'excerpt',
            //'trackbacks',
            //'custom-fields',
            //'comments',
            //'revisions',
            //'author',
            //'page-attributes',
        )
    );
    register_post_type( 'services', $args );
}


// Custom Meta data
function theme_add_custom_metabox() {
    add_meta_box( 'banner_code', 'Add shortcode for banner', 'theme_banner_area' );
    add_meta_box( 'form_code', 'Add shortcode for Form', 'theme_form_area' );
//    add_meta_box( 'banner_code', 'Add shortcode for banner', 'theme_banner_area' );
//    add_meta_box( 'form_code', 'Add shortcode for Form', 'theme_form_area' );
}

function theme_banner_area(){
    global $post;
    $values = get_post_custom( $post->ID );
    // We'll use this nonce field later on when saving.
    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
    $text = isset( $values['banner_shortcode'] ) ? esc_attr( $values['banner_shortcode'][0] ) : '';
    echo '<input type="text" name="banner_shortcode" id="banner_shortcode" value="'.$text.'"  placeholder="Add banner shortcode" style="width: 100%"/>';
}

function theme_form_area(){
    global $post;
    $values = get_post_custom( $post->ID );
    $text = isset( $values['fomm_shortcode'] ) ? esc_attr( $values['fomm_shortcode'][0] ) : '';
    // We'll use this nonce field later on when saving.
    wp_nonce_field( 'my_meta_box_nonce', 'meta_box_nonce' );
    echo '<input type="text" name="fomm_shortcode" id="fomm_shortcode" value="'.$text.'"  placeholder="Add banner shortcode" style="width: 100%"/>';
}
function theme_meta_box_save( $post_id )
{
    // Bail if we're doing an auto save
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    // if our nonce isn't there, or we can't verify it, bail
    if( !isset( $_POST['meta_box_nonce'] ) || !wp_verify_nonce( $_POST['meta_box_nonce'], 'my_meta_box_nonce' ) ) return;

    // if our current user can't edit this post, bail
    if( !current_user_can( 'edit_post' ) ) return;

    // now we can actually save the data
    $allowed = array(
        'a' => array( // on allow a tags
            'href' => array() // and those anchors can only have href attribute
        )
    );

    // Make sure your data is set before trying to save it
    if( isset( $_POST['banner_shortcode'] ) )
        update_post_meta( $post_id, 'banner_shortcode', wp_kses( $_POST['banner_shortcode'], $allowed ) );
    // Make sure your data is set before trying to save it
    if( isset( $_POST['fomm_shortcode'] ) )
        update_post_meta( $post_id, 'fomm_shortcode', wp_kses( $_POST['fomm_shortcode'], $allowed ) );
}

add_action( 'add_meta_boxes', 'theme_add_custom_metabox' );

add_action( 'save_post', 'theme_meta_box_save' );

remove_action( 'woocommerce_after_shop_loop_item',  'woocommerce_template_loop_add_to_cart');

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart');

//.storefront-sorting opening after products loop
remove_action( 'woocommerce_after_shop_loop','storefront_sorting_wrapper',9 );
//.storefront-sorting closing after products loop
remove_action( 'woocommerce_after_shop_loop','storefront_sorting_wrapper_close',31 ); 
//.storefront-sorting opening before products loop
remove_action( 'woocommerce_before_shop_loop','storefront_sorting_wrapper',9 );
//.storefront-sorting closing before products loop
remove_action( 'woocommerce_before_shop_loop','storefront_sorting_wrapper_close',31 ); 
