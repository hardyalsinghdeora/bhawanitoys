<?php
function twabc_get_featured_image($post_ID) {  
	$post_thumbnail_id = get_post_thumbnail_id($post_ID);  
	if ($post_thumbnail_id) {  
		$post_thumbnail_img = wp_get_attachment_image_src($post_thumbnail_id, 'featured_preview');  
		return $post_thumbnail_img[0];  
	}  
}
function twabc_columns_head($defaults) {  
	$defaults['featured_image'] = 'Carousel Image'; 
	$defaults['category'] = 'Carousel Category';
	return $defaults;  
}  
function twabc_columns_content($column_name, $post_ID) {  
	if ($column_name == 'featured_image') {  
		$post_featured_image = twabc_get_featured_image($post_ID);  
		if ($post_featured_image) {  
			echo '<a href="'.get_edit_post_link($post_ID).'"><img src="' . $post_featured_image . '" alt="" style="max-width:100%;" /></a>';  
		}  
	}
	if ($column_name == 'category') {
		$post_categories = get_the_terms($post_ID, 'twabc_category');
		if ($post_categories) {
			$output = '';
			foreach($post_categories as $cat){
				$output .= $cat->name.', ';
			}
			echo trim($output, ', ');
		} else {
			echo 'No categories';
		}
	}
}
add_filter('manage_twabc_posts_columns', 'twabc_columns_head');  
add_action('manage_twabc_posts_custom_column', 'twabc_columns_content', 10, 2);