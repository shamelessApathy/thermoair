<?php
// Enable shortcodes in text widgets
add_filter('widget_text','do_shortcode');

//* Start the engine
require_once( get_template_directory() . '/lib/init.php' );

//* Child theme (do not remove)
define( 'CHILD_THEME_NAME', 'Optimal Theme', 'optimal' );
define( 'CHILD_THEME_URL', 'http://my.studiopress.com/themes/optimal/' );
	wp_enqueue_style( 'brian-changes', "get_template_uri() . '../brian.css'", array(), true );
//* Enqueue Lato Google font
add_action( 'wp_enqueue_scripts', 'genesis_sample_google_fonts' );
function genesis_sample_google_fonts() {
	wp_enqueue_style( 'google-font', '//fonts.googleapis.com/css?family=Oswald:400,300|Open+Sans', array(), PARENT_THEME_VERSION );
}

//* Add HTML5 markup structure
add_theme_support( 'html5' );

//* Add viewport meta tag for mobile browsers
add_theme_support( 'genesis-responsive-viewport' );

//* Add support for custom background
add_theme_support( 'custom-background' );

// Create additional color style options
add_theme_support( 'genesis-style-selector', array(
	'optimal-black' 		=>	__( 'Black', 'optimal' ),
	'optimal-blue' 			=>	__( 'Blue', 'optimal' ), 
	'optimal-dark-blue' 	=>	__( 'Dark Blue', 'optimal' ),
	'optimal-dark-gray' 	=> 	__( 'Dark Gray', 'optimal' ),	
	'optimal-green' 		=> 	__( 'Green', 'optimal' ),
	'optimal-light-orange' 	=> 	__( 'Light Orange', 'optimal' ),
	'optimal-orange' 		=> 	__( 'Orange', 'optimal' ), 
	'optimal-pink' 			=> 	__( 'Pink', 'optimal' ),
	'optimal-purple' 		=> 	__( 'Purple', 'optimal' ), 
	'optimal-red' 			=> 	__( 'Red', 'optimal' ),
	'optimal-silver' 		=> 	__( 'Silver', 'silver' ),	 
) );

// Add support for custom header
add_theme_support( 'genesis-custom-header', array(
	'width' => 360,
	'height' => 164
) );

// Add new image sizes 
add_image_size( 'featured-img', 630, 320, TRUE );
add_image_size( 'featured-page', 341, 173, TRUE );
add_image_size( 'portfolio-thumbnail', 264, 200, TRUE );

// Add support for structural wraps
add_theme_support( 'genesis-structural-wraps', array( 'header', 'nav', 'subnav', 'inner', 'footer-widgets', 'footer' ) );

// Reposition the Secondary Navigation
remove_action( 'genesis_after_header', 'genesis_do_subnav' ) ;
add_action( 'genesis_before_header', 'genesis_do_subnav' );

// Before Header Wrap
add_action( 'genesis_before_header', 'before_header_wrap' );
function before_header_wrap() {
	echo '<div class="head-wrap">';
}

// Reposition the Primary Navigation
remove_action( 'genesis_after_header', 'genesis_do_nav' ) ;
add_action( 'genesis_after_header', 'genesis_do_nav' );

// After Header Wrap
add_action( 'genesis_after_header', 'after_header_wrap' );
function after_header_wrap() {
	echo '</div>';
}

// Customize search form input box text
add_filter( 'genesis_search_text', 'custom_search_text' );
function custom_search_text($text) {
    return esc_attr( 'Search...' );
}

add_action( 'admin_menu', 'optimal_theme_settings_init', 15 ); 
/** 
 * This is a necessary go-between to get our scripts and boxes loaded 
 * on the theme settings page only, and not the rest of the admin 
 */ 
function optimal_theme_settings_init() { 
    global $_genesis_admin_settings; 
     
    add_action( 'load-' . $_genesis_admin_settings->pagehook, 'optimal_add_portfolio_settings_box', 20 ); 
} 

// Add Portfolio Settings box to Genesis Theme Settings 
function optimal_add_portfolio_settings_box() { 
    global $_genesis_admin_settings; 
     
    add_meta_box( 'genesis-theme-settings-optimal-portfolio', __( 'Portfolio Page Settings', 'optimal' ), 'optimal_theme_settings_portfolio',     $_genesis_admin_settings->pagehook, 'main' ); 
}  
	
/** 
 * Adds Portfolio Options to Genesis Theme Settings Page
 */ 	
function optimal_theme_settings_portfolio() { ?>

	<p><?php _e("Display which category:", 'genesis'); ?>
	<?php wp_dropdown_categories(array('selected' => genesis_get_option('optimal_portfolio_cat'), 'name' => GENESIS_SETTINGS_FIELD.'[optimal_portfolio_cat]', 'orderby' => 'Name' , 'hierarchical' => 1, 'show_option_all' => __("All Categories", 'genesis'), 'hide_empty' => '0' )); ?></p>
	
	<p><?php _e("Exclude the following Category IDs:", 'genesis'); ?><br />
	<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_cat_exclude]" value="<?php echo esc_attr( genesis_get_option('optimal_portfolio_cat_exclude') ); ?>" size="40" /><br />
	<small><strong><?php _e("Comma separated - 1,2,3 for example", 'genesis'); ?></strong></small></p>
	
	<p><?php _e('Number of Posts to Show', 'genesis'); ?>:
	<input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_cat_num]" value="<?php echo esc_attr( genesis_option('optimal_portfolio_cat_num') ); ?>" size="2" /></p>
	
	<p><span class="description"><?php _e('<b>NOTE:</b> The Portfolio Page displays the "Portfolio Page" image size plus the excerpt or full content as selected below.', 'optimal'); ?></span></p>
	
	<p><?php _e("Select one of the following:", 'genesis'); ?>
	<select name="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_content]">
		<option style="padding-right:10px;" value="full" <?php selected('full', genesis_get_option('optimal_portfolio_content')); ?>><?php _e("Display post content", 'genesis'); ?></option>
		<option style="padding-right:10px;" value="excerpts" <?php selected('excerpts', genesis_get_option('optimal_portfolio_content')); ?>><?php _e("Display post excerpts", 'genesis'); ?></option>
	</select></p>
	
	<p><label for="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_content_archive_limit]"><?php _e('Limit content to', 'genesis'); ?></label> <input type="text" name="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_content_archive_limit]" id="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_content_archive_limit]" value="<?php echo esc_attr( genesis_option('optimal_portfolio_content_archive_limit') ); ?>" size="3" /> <label for="<?php echo GENESIS_SETTINGS_FIELD; ?>[optimal_portfolio_content_archive_limit]"><?php _e('characters', 'genesis'); ?></label></p>
	
	<p><span class="description"><?php _e('<b>NOTE:</b> Using this option will limit the text and strip all formatting from the text displayed. To use this option, choose "Display post content" in the select box above.', 'genesis'); ?></span></p>
<?php
}	

//* Add support for 3-column footer widgets
add_theme_support( 'genesis-footer-widgets', 4 );

// Register widget areas
genesis_register_sidebar( array(
	'id'			=> 'slider',
	'name'			=> __( 'Slider', 'optimal' ),
	'description'	=> __( 'This is the slider section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'welcome',
	'name'			=> __( 'Welcome', 'optimal' ),
	'description'	=> __( 'This is the welcome section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-sidebar',
	'name'			=> __( 'Home Feature Sidebar', 'optimal' ),
	'description'	=> __( 'This is the home feature sidebar of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-1',
	'name'			=> __( 'Home Feature #1', 'optimal' ),
	'description'	=> __( 'This is the first column in the middle section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-feature-2',
	'name'			=> __( 'Home Feature #2', 'optimal' ),
	'description'	=> __( 'This is the second column in the middle section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-bottom-sidebar',
	'name'			=> __( 'Home Bottom Sidebar', 'optimal' ),
	'description'	=> __( 'This is the left sidebar at the bottom section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-featured-posts',
	'name'			=> __( 'Home Featured Posts', 'optimal' ),
	'description'	=> __( 'This is the posts column at the bottom section of the homepage.', 'optimal' ),
) );
genesis_register_sidebar( array(
	'id'			=> 'home-bottom-message',
	'name'			=> __( 'Home Bottom Message', 'optimal' ),
	'description'	=> __( 'This is the bottom section of the homepage right before the footer.', 'optimal' ),
) );