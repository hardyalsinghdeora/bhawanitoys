<?php
/*
Plugin Name: Advanced Bootstrap Carousel
Plugin URI: https://wordpress.org/plugins/advanced-bootstrap-carousel/
Description: Advanced Bootstrap Carousel is a light weighted responsive slider plugin. This plugin provide advance and extended fetures for Bootstrap Carousel. This plugins will only run if your theme contain Twitter Bootstrap CSS and Javascript.
Version: 2.0.0
Author: Animesh
Author URI: http://www.thelogicalcoder.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: bootstrap carousel, bootstrap slider, slider, banner slider, responsive slider, bootstrap numbered indicator, bootstrap vertical slider, bootstrap fading slider

*/

define( 'TWABC_VERSION', '2.0.0' );
define( 'TWABC_PLUGIN', __FILE__ );
define( 'TWABC_PLUGIN_BASENAME', plugin_basename( TWABC_PLUGIN ) );

// Advanced Bootstrap Carousel custom post type
add_action( 'init', 'twabc_post_type');
function twabc_post_type() {

	$labels = array(
		'name'                => 'Advanced Bootstrap Carousel',
		'singular_name'       => 'Advanced Bootstrap Carousel',
		'menu_name'           => 'ABC Slider',
		'parent_item_colon'   => 'Parent:',
		'all_items'           => 'All Carousels',
		'view_item'           => 'View Carousel',
		'add_new_item'        => 'Add New Carousel',
		'add_new'             => 'Add New Carousel',
		'edit_item'           => 'Edit Carousel',
		'update_item'         => 'Update Carousel',
		'search_items'        => 'Search Carousels',
		'not_found'           => 'Carousel Not found',
		'not_found_in_trash'  => 'Carousel Not found in Trash',
	);
	$args = array(
		'labels' 				=> $labels,
		'public' 				=> true,
		'exclude_from_search' 	=> true,
		'publicly_queryable' 	=> false,
		'show_ui' 				=> true, 
		'show_in_menu' 			=> true, 
		'query_var' 			=> true,
		'rewrite' 				=> true,
		'capability_type' 		=> 'page',
		'has_archive' 			=> true, 
		'hierarchical'			=> false,
		'menu_position' 		=> 20,
        'menu_icon'           	=> 'dashicons-slides',
		'supports' 				=> array('title', 'excerpt', 'thumbnail', 'page-attributes')
	);
	register_post_type( 'twabc', $args );
}
// Create a taxonomy for the carousel post type
function twabc_taxonomies () {
	$args = array('hierarchical' => true);
	register_taxonomy( 'twabc_category', 'twabc', $args );
}

add_action( 'init', 'twabc_taxonomies', 0 );

function twabc_addImageSupport() {
	$supportedTypes = get_theme_support( 'post-thumbnails' );
	if( $supportedTypes === false ) {
		add_theme_support( 'post-thumbnails', array( 'twabc' ) );	  
		add_image_size('featured_preview', 100, 55, true);
	} elseif( is_array( $supportedTypes ) ) {
		$supportedTypes[0][] = 'twabc';
		add_theme_support( 'post-thumbnails', $supportedTypes[0] );
		add_image_size('featured_preview', 100, 55, true);
	}
}
add_action( 'after_setup_theme', 'twabc_addImageSupport');


require_once('twabc-admin-view.php');
require_once('twabc-front-view.php');
require_once('twabc-admin-settings.php');