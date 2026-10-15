<?php
// Set up settings defaults
register_activation_hook(__FILE__, 'twabc_set_options');
function twabc_set_options (){
	$defaults = array(
		'interval' => '5000',
		'showcontrols' => 'true',
		'showindicator' => 'true',
		'customprev' => '',
		'customnext' => '',
		'orderby' => 'menu_order',
		'order' => 'ASC',
		'category' => '',
		'before_title' => '<h4>',
		'after_title' => '</h4>',
		'before_caption' => '<p>',
		'after_caption' => '</p>',
		'image_size' => 'full',
		'id' => '',
		'twbs' => '3',
        'use_javascript_animation' => '1',
		'effect' => 'slide'
	);
	add_option('twabc_settings', $defaults);
}
// Clean up on uninstall
register_activation_hook(__FILE__, 'twabc_deactivate');
function twabc_deactivate(){
	delete_option('twabc_settings');
}


// Render the settings page
class twabc_settings_page {
	// Holds the values to be used in the fields callbacks
	private $options;
			
	// Start up
	public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
	}
			
	// Add settings page
	public function add_plugin_page() {
		add_submenu_page('edit.php?post_type=twabc', 'Settings',  'Settings', 'manage_options', 'twabc-settings', array($this,'create_admin_page'));
	}
			
	// Options page callback
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'twabc_settings' );
		if(!$this->options){
			twabc_set_options ();
			$this->options = get_option( 'twabc_settings' );
		}
		?>
		<div class="wrap">
		<h2>Advanced Bootstrap Carousel Settings</h2>
        <div class="notice notice-success is-dismissible">
        
        <h3>Advanced Bootstrap Carousel Needs Your Support</h3>
        <p class="message">It is hard to continue development and support for this plugin without contributions from users like you. If you enjoy using Advanced Bootstrap Carousel and find it useful, please consider making a donation.</p>
        <p><a href="http://www.thelogicalcoder.com/manage.php/?plugin=twabc&type=donate" target="_blank" class="button button-primary">Donate</a>&nbsp;&nbsp;<a href="http://www.thelogicalcoder.com/manage.php/?plugin=twabc&type=feedback" target="_blank" class="button button-primary">Feedback</a></p>
			
        </div>
        <h3>Shortcodes:</h3>
        <strong>Default</strong>
        <span class="shortcode"><input type="text" value="[twabc-carousel]" class="large-text code" readonly="readonly" onfocus="this.select();"></span>
        <strong>Slide with custom Interval</strong>
        <span class="shortcode"><input type="text" value="[twabc-carousel interval=&quot;900&quot;]" class="large-text code" readonly="readonly" onfocus="this.select();"></span>
        <strong>Slider with Control</strong>
        <span class="shortcode"><input type="text" value="[twabc-carousel showcontrols=&quot;true&quot;]" class="large-text code" readonly="readonly" onfocus="this.select();"></span>
        <strong>Filter Slider by Category</strong>
        <span class="shortcode"><input type="text" value="[twabc-carousel category=&quot;testing&quot;]" class="large-text code" readonly="readonly" onfocus="this.select();"></span>
        <strong>Slider With Numbered Indicator</strong>
        <span class="shortcode"><input type="text" value="[twabc-carousel showindicator=&quot;numbered&quot;]" class="large-text code" readonly="readonly" onfocus="this.select();"></span>
					 
				<form method="post" action="options.php">
				<?php
						settings_fields( 'twabc_settings' );   
						do_settings_sections( 'twabc-settings' );
						submit_button(); 
				?>
				</form>
		</div>
		<?php
	}
			
	// Register and add settings
	public function page_init() {		
		register_setting(
				'twabc_settings', // Option group
				'twabc_settings', // Option name
				array( $this, 'sanitize' ) // Sanitize
		);
		
        // Sections
		add_settings_section(
				'twabc_settings_behaviour', // ID
				'Carousel Basic Settings', // Title
				array( $this, 'twabc_settings_behaviour_header' ), // Callback
				'twabc-settings' // Page
		);
		add_settings_section(
				'twabc_settings_setup', // ID
				 'Carousel Advance Settings', // Title
				array( $this, 'twabc_settings_setup' ), // Callback
				'twabc-settings' // Page
		);
        
		// Behaviour Fields
		add_settings_field(
				'interval', // ID
				'Slide Interval (milliseconds)', // Title
				array( $this, 'interval_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section
		);
		add_settings_field(
				'showcontrols', // ID
				'Show Slide Controls?', // Title 
				array( $this, 'showcontrols_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
		add_settings_field(
				'effect', // ID
				'Slide Effect', // Title 
				array( $this, 'effect_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
		add_settings_field(
				'showindicator', // ID
				'Show Slide Indicator?', // Title 
				array( $this, 'showindicator_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
		add_settings_field(
				'orderby', // ID
				__('Order Slides By', 'twabc-settings'), // Title 
				array( $this, 'orderby_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
		add_settings_field(
				'order', // ID
				__('Ordering Direction', 'twabc-settings'), // Title 
				array( $this, 'order_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
		add_settings_field(
				'category', // ID
				__('Restrict to Category', 'twabc-settings'), // Title 
				array( $this, 'category_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_behaviour' // Section		   
		);
        
        // Carousel Setup Section
		add_settings_field(
				'twbs', // ID
				__('Twitter Bootstrap Version', 'twabc-settings'), // Title 
				array( $this, 'twbs_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_setup' // Section		   
		);
		add_settings_field(
				'image_size', // ID
				__('Image Size', 'twabc-settings'), // Title 
				array( $this, 'image_size_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_setup' // Section		   
		);
		add_settings_field(
				'use_javascript_animation', // ID
				__('Use Javascript to animate carousel?', 'twabc-settings'), // Title 
				array( $this, 'use_javascript_animation_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_setup' // Section		   
		);

        
        // Markup Section
		add_settings_field(
				'customprev', // ID
				__('Custom prev button class', 'twabc-settings'), // Title
				array( $this, 'customprev_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
		add_settings_field(
				'customnext', // ID
				__('Custom next button class', 'twabc-settings'), // Title
				array( $this, 'customnext_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
		add_settings_field(
				'before_title', // ID
				__('HTML before title', 'twabc-settings'), // Title
				array( $this, 'before_title_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
		add_settings_field(
				'after_title', // ID
				__('HTML after title', 'twabc-settings'), // Title
				array( $this, 'after_title_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
		add_settings_field(
				'before_caption', // ID
				__('HTML before caption text', 'twabc-settings'), // Title
				array( $this, 'before_caption_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
		add_settings_field(
				'after_caption', // ID
				__('HTML after caption text', 'twabc-settings'), // Title
				array( $this, 'after_caption_callback' ), // Callback
				'twabc-settings', // Page
				'twabc_settings_markup' // Section
		);
			 
	}
			
	// Sanitize each setting field as needed -  @param array $input Contains all settings fields as array keys
	public function sanitize( $input ) {
		$new_input = array();
		foreach($input as $key => $var){
			if($key == 'twbs' || $key == 'interval' || $key == 'background_images_height'){
				$new_input[$key] = absint( $input[$key] );
			} else if ($key == 'link_button_before' || $key == 'link_button_after' || $key == 'before_title' || $key == 'after_title' || $key == 'before_caption' || $key == 'after_caption'){
				$new_input[$key] = $input[$key]; // Don't sanitise these, meant to be html!
			} else { 
				$new_input[$key] = sanitize_text_field( $input[$key] );
			}
		}
		return $new_input;
	}
			
	// Print the Section text
	public function twabc_settings_behaviour_header() {
            echo '<p>'.__('Basic setup of how each Carousel will function, what controls will show and which images will be displayed.', 'twabc-settings').'</p>';
	}
	public function twabc_settings_setup() {
            echo '<p>'.__('Change the setup of the carousel - how it functions.', 'twabc-settings').'</p>';
	}
	public function twabc_settings_link_buttons_header() {
            echo '<p>'.__('Options for using a link button instead of linking the image directly.', 'twabc-settings').'</p>';
	}
	public function twabc_settings_markup_header() {
            echo '<p>'.__('Customise which CSS classes and HTML tags the Carousel uses.', 'twabc-settings').'</p>';
	}
			
	// Callback functions - print the form inputs
    // Carousel behaviour	
	public function interval_callback() {
			printf('<input type="text" id="interval" name="twabc_settings[interval]" value="%s" size="15" />',
					isset( $this->options['interval'] ) ? esc_attr( $this->options['interval']) : '');
            echo '<p class="description">'.__('How long each image shows for before it slides. Set to 0 to disable animation.', 'twabc-settings').'</p>';
	}
	public function showcaption_callback() {
		if(isset( $this->options['showcaption'] ) && $this->options['showcaption'] == 'false'){
			$twabc_showcaption_t = '';
			$twabc_showcaption_f = ' selected="selected"';
		} else {
			$twabc_showcaption_t = ' selected="selected"';
			$twabc_showcaption_f = '';
		}
		echo '<select id="showcaption" name="twabc_settings[showcaption]">
			<option value="true"'.$twabc_showcaption_t.'>'.__('Show', 'twabc-settings').'</option>
			<option value="false"'.$twabc_showcaption_f.'>'.__('Hide', 'twabc-settings').'</option>
		</select>';
	}
	public function showcontrols_callback() {
		if(isset( $this->options['showcontrols'] ) && $this->options['showcontrols'] == 'false'){
			$twabc_showcontrols_t = '';
			$twabc_showcontrols_f = ' selected="selected"';
			$twabc_showcontrols_c = '';
		} else if(isset( $this->options['showcontrols'] ) && $this->options['showcontrols'] == 'true'){
			$twabc_showcontrols_t = ' selected="selected"';
			$twabc_showcontrols_f = '';
			$twabc_showcontrols_c = '';
		} else if(isset( $this->options['showcontrols'] ) && $this->options['showcontrols'] == 'custom'){
			$twabc_showcontrols_t = '';
			$twabc_showcontrols_f = '';
			$twabc_showcontrols_c = ' selected="selected"';
		}
		echo '<select id="showcontrols" name="twabc_settings[showcontrols]">
			<option value="true"'.$twabc_showcontrols_t.'>'.__('Show', 'twabc-settings').'</option>
			<option value="false"'.$twabc_showcontrols_f.'>'.__('Hide', 'twabc-settings').'</option>
			<option value="custom"'.$twabc_showcontrols_c.'>'.__('Custom', 'twabc-settings').'</option>
		</select>';
	}
	public function effect_callback() {
		if(isset( $this->options['effect'] ) && $this->options['effect'] == 'hslide'){
			$twabc_effect_t = ' selected="selected"';
			$twabc_effect_f = '';
			$twabc_effect_c = '';
		} else if(isset( $this->options['effect'] ) && $this->options['effect'] == 'fade'){
			$twabc_effect_t = '';
			$twabc_effect_f = ' selected="selected"';
			$twabc_effect_c = '';
		} else if(isset( $this->options['effect'] ) && $this->options['effect'] == 'vslide'){
			$twabc_effect_t = '';
			$twabc_effect_f = '';
			$twabc_effect_c = ' selected="selected"';
		}
		echo '<select id="effect" name="twabc_settings[effect]">
			<option value="hslide"'.$twabc_effect_t.'>'.__('Horizontal Slide', 'twabc-settings').'</option>
			<option value="vslide"'.$twabc_effect_c.'>'.__('Vertical Slide', 'twabc-settings').'</option>
			<option value="fade"'.$twabc_effect_f.'>'.__('Fade', 'twabc-settings').'</option>
			
		</select>';
		echo '<p class="description">'.__('Sliding Effect of Image. (Eg.- Fade, Horizontal Slide, Vertical Slide)', 'twabc-settings').'</p>';
	}
	public function showindicator_callback() {
		if(isset( $this->options['showindicator'] ) && $this->options['showindicator'] == 'false'){
			$twabc_showindicator_t = '';
			$twabc_showindicator_f = ' selected="selected"';
			$twabc_showindicator_c = '';
		} else if(isset( $this->options['showindicator'] ) && $this->options['showindicator'] == 'true'){
			$twabc_showindicator_t = ' selected="selected"';
			$twabc_showindicator_f = '';
			$twabc_showindicator_c = '';
		} else if(isset( $this->options['showindicator'] ) && $this->options['showindicator'] == 'numbered'){
			$twabc_showindicator_t = '';
			$twabc_showindicator_f = '';
			$twabc_showindicator_c = ' selected="selected"';
		}
		echo '<select id="showindicator" name="twabc_settings[showindicator]">
			<option value="true"'.$twabc_showindicator_t.'>Bullet Indicators</option>
			<option value="false"'.$twabc_showindicator_f.'>Hide Indicators</option>
			<option value="numbered"'.$twabc_showindicator_c.'>Numbered Indicators</option>
		</select>';
	}
	public function orderby_callback() {
		$orderby_options = array (
			'menu_order' => __('Menu order, as set in Carousel overview page', 'twabc-settings'),
			'date' => __('Date slide was published', 'twabc-settings'),
			'rand' => __('Random ordering', 'twabc-settings'),
			'title' => __('Slide title', 'twabc-settings')	  
		);
		echo '<select id="orderby" name="twabc_settings[orderby]">';
		foreach($orderby_options as $val => $option){
			echo '<option value="'.$val.'"';
			if(isset( $this->options['orderby'] ) && $this->options['orderby'] == $val){
				echo ' selected="selected"';
			}
			echo ">$option</option>";
		}
		echo '</select>';
	}
	public function order_callback() {
		if(isset( $this->options['order'] ) && $this->options['order'] == 'DESC'){
			$twabc_showcontrols_a = '';
			$twabc_showcontrols_d = ' selected="selected"';
		} else {
			$twabc_showcontrols_a = ' selected="selected"';
			$twabc_showcontrols_d = '';
		}
		echo '<select id="order" name="twabc_settings[order]">
			<option value="ASC"'.$twabc_showcontrols_a.'>'.__('Ascending', 'twabc-settings').'</option>
			<option value="DESC"'.$twabc_showcontrols_d.'>'.__('Decending', 'twabc-settings').'</option>
		</select>';
	}
	public function category_callback() {
		$cats = get_terms('twabc_category');
		echo '<select id="orderby" name="twabc_settings[category]">
			<option value="">'.__('All Categories', 'twabc-settings').'</option>';
		foreach($cats as $cat){
			echo '<option value="'.$cat->name.'"';
			if(isset( $this->options['category'] ) && $this->options['category'] == $cat->name){
				echo ' selected="selected"';
			}
			echo ">".$cat->name."</option>";
		}
		echo '</select>';
	}
	
    // Setup Section
	public function twbs_callback() {
		if(isset( $this->options['twbs'] ) && $this->options['twbs'] == '3'){
			$twabc_twbs3 = ' selected="selected"';
			$twabc_twbs4 = '';
		} else {
			$$twabc_twbs3 = '';
			$twabc_twbs4 = ' selected="selected"';
		}
		echo '<select id="twbs" name="twabc_settings[twbs]">
			<option value="3"'.$twabc_twbs3.'>3.x (Default)</option>
			<option value="4"'.$twabc_twbs4.'>4.x </option>
		</select>';
        echo '<p class="description">'.__("Set according to which version of Bootstrap you're using.", 'twabc-settings').'</p>';
	}
	public function image_size_callback() {
		$image_sizes = get_intermediate_image_sizes();
		echo '<select id="image_size" name="twabc_settings[image_size]">
			<option value="full"';
			if(isset( $this->options['image_size'] ) && $this->options['image_size'] == 'full'){
				echo ' selected="selected"';
			}
			echo '>Full (default)</option>';
		foreach($image_sizes as $size){
			echo '<option value="'.$size.'"';
			if(isset( $this->options['image_size'] ) && $this->options['image_size'] == $size){
				echo ' selected="selected"';
			}
			echo ">".ucfirst($size)."</option>";
		}
		echo '</select>';
        echo '<p class="description">'.__("If your carousels are small, you can use a smaller image size to increase page speed.", 'twabc-settings').'</p>';
	}
	public function use_javascript_animation_callback() {
		echo '<select id="use_javascript_animation" name="twabc_settings[use_javascript_animation]">';
		echo '<option value="1"';
		if(isset( $this->options['use_javascript_animation'] ) && $this->options['use_javascript_animation'] == 1){
			echo ' selected="selected"';
		}
		echo '>Yes (default)</option>';
		echo '<option value="0"';
		if(isset( $this->options['use_javascript_animation'] ) && $this->options['use_javascript_animation'] == 0){
			echo ' selected="selected"';
		}
		echo '>No</option>';
		echo '</select>';
        echo '<p class="description">'.__("The Bootstrap Carousel is designed to work usign data-attributes. Sometimes the animation doesn't work correctly with this, so the default is to include a small portion of Javascript to fire the carousel. You can choose not to include this here.", 'twabc-settings').'</p>';
	}
	
}

if( is_admin() ){
		$twabc_settings_page = new twabc_settings_page();
}

// Add settings link on plugin page
function twabc_settings_link ($links) { 
	$settings_link = '<a href="edit.php?post_type=twabc&page=twabc-settings">'.__('Settings', 'twabc-settings').'</a>'; 
	array_unshift($links, $settings_link); 
	return $links; 
}
$twabc_plugin = TWABC_PLUGIN_BASENAME; 
add_filter("plugin_action_links_$twabc_plugin", 'twabc_settings_link' );


function twabc_msg() {

}

// Now we set that function up to execute when the admin_notices action is called
add_action( 'admin_notices', 'twabc_msg' );
