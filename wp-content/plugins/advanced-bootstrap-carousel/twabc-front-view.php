<?php
// Shortcode
function twabc_shortcode($atts, $content = null) {
		// Set default shortcode attributes
	$options = get_option( 'twabc_settings' );
	if(!$options){
		twabc_set_options ();
		$options = get_option( 'twabc_settings' );
	}
	$options['id'] = '';

	// Parse incomming $atts into an array and merge it with $defaults
	$atts = shortcode_atts($options, $atts);

	return twabc_frontend($atts);
}
add_shortcode('twabc-carousel', 'twabc_shortcode');

// Display carousel
function twabc_frontend($atts){

	// Build the attributes
	$id = rand(0, 999); // use a random ID so that the CSS IDs work with multiple on one page
	$args = array(
		'post_type' => 'twabc',
		'posts_per_page' => '-1',
		'orderby' => $atts['orderby'],
		'order' => $atts['order']
	);
	if($atts['category'] != ''){
		$args['twabc_category'] = $atts['category'];
	}
	if(!isset($atts['image_size'])) $atts['image_size'] = 'full';
	if(!isset($atts['use_javascript_animation'])) $atts['use_javascript_animation'] = '1';
	if($atts['id'] != ''){
		$args['p'] = $atts['id'];
	}

	// Collect the carousel content. Needs printing in two loops later (bullets and content)
	$loop = new WP_Query( $args );
	$images = array();
	$output = '';
	while ( $loop->have_posts() ) {
		$loop->the_post();
		if ( '' != get_the_post_thumbnail(get_the_ID(), $atts['image_size']) ) {
			$post_id = get_the_ID();
			$title = get_the_title();
			$content = get_the_excerpt();
			
			$image_src = wp_get_attachment_image_src(get_post_thumbnail_id(), $atts['image_size']);
			$image_src_thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), 'featured_preview');
			$image_src = $image_src[0];
			$image_src_thumb = $image_src_thumb[0];
			$images[] = array('post_id' => $post_id, 'title' => $title, 'content' => $content, 'img_src' => $image_src);
		}
	}
	// Check we actually have something to show
	if(count($images) > 0){
		ob_start();
		?>
		<div id="twabc_<?php echo $id; ?>" class="carousel slide <?php if($atts['effect'] === 'fade') {echo "carousel-fade"; } else if($atts['effect'] === 'vslide') {echo "vertical-slider"; } ?> <?php if($atts['showindicator'] === 'numbered') {echo "carousel-indicator-numbered"; } ?>" <?php if($atts['use_javascript_animation'] == '0'){ echo ' data-ride="carousel"'; } ?> data-interval="<?php echo $atts['interval']; ?>">
			
			<?php 
			if($atts['twbs'] == '3'){ 
				require_once('twabc-front-view-3.php');
			}
			else if($atts['twbs'] == '4'){ 
				require_once('twabc-front-view-4.php');
			}
			?>

		</div>

        <?php // Javascript animation fallback
        if($atts['use_javascript_animation'] == '1'){ ?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#twabc_<?php echo $id; ?>').carousel({
					interval: <?php echo $atts['interval']; ?>
				});
			});
		</script>
        <?php }

        // Collect the output
		$output = ob_get_contents();
		ob_end_clean();
	}
	
	// Restore original Post Data
	wp_reset_postdata();  
	
	return $output;
}

