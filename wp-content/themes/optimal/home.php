<?php

add_action( 'genesis_meta', 'optimal_home_genesis_meta' );
/**
 * Add widget support for homepage. If no widgets active, display the default loop.
 *
 */
function optimal_home_genesis_meta() {

	if ( is_active_sidebar( 'slider' ) || is_active_sidebar( 'welcome' ) || is_active_sidebar( 'home-feature-sidebar' ) || is_active_sidebar( 'home-feature-1' ) || is_active_sidebar( 'home-feature-2' ) || is_active_sidebar( 'home-bottom-sidebar' ) || is_active_sidebar( 'home-featured-posts' ) || is_active_sidebar( 'home-bottom-message' ) ) {

		remove_action( 'genesis_loop', 'genesis_do_loop' );
		add_action( 'genesis_after_header', 'optimal_home_loop_helper_top' );
		add_action( 'genesis_after_header', 'optimal_home_loop_helper_middle' );
		add_action( 'genesis_after_header', 'optimal_home_loop_helper_bottom' );
		add_action( 'genesis_after_header', 'optimal_home_loop_helper_bottom_message' );
		add_filter( 'genesis_pre_get_option_site_layout', '__genesis_return_full_width_content' );

	}
}

/**
 * Display widget content for "slider" and "welcome" sections.
 *
 */
function optimal_home_loop_helper_top() {

		genesis_widget_area( 'slider', array(
			'before' => '<div class="slider"><div class="wrap">',
			'after' => '</div></div>',
		) );
		
		genesis_widget_area( 'welcome', array(
			'before' => '<div class="welcome"><div class="wrap">',
			'after' => '</div></div>',
		) );

}


/**
 * Display widget content for "Home Features" sections.
 *
 */
function optimal_home_loop_helper_middle() {

		echo '<div class="home-features"><div class="wrap">';
		
		genesis_widget_area( 'home-feature-sidebar', array(
			'before' => '<div class="home-feature-sidebar">',
			'after' => '</div>',
		) );
		
		echo '<div class="home-feature-section">';
		
		genesis_widget_area( 'home-feature-1', array(
			'before' => '<div class="home-feature-1">',
			'after' => '</div>',
		) );	
		
		genesis_widget_area( 'home-feature-2', array(
			'before' => '<div class="home-feature-2">',
			'after' => '</div>',
		) );	
		
		echo '</div><!-- end .home-feature-section --></div><!-- end .wrap --></div><!-- end .home-features -->';
		
}


/**
 * Display widget content for "home bottom sidebar", and "home featured posts" sections.
 *
 */
function optimal_home_loop_helper_bottom() {
	
	 if ( is_active_sidebar( 'home-bottom-sidebar' ) || is_active_sidebar( 'home-featured-posts' ) ) {
	
			echo '<div class="home-bottom"><div class="wrap">';
	
			genesis_widget_area( 'home-bottom-sidebar', array(
				'before' => '<div class="home-bottom-sidebar">',
				'after' => '</div>',
			) );
			
			genesis_widget_area( 'home-featured-posts', array(
				'before' => '<div class="home-featured-posts">',
				'after' => '</div>',
			) );
			
			echo '</div><!-- .wrap --></div><!-- end .home-bottom -->';
			
	}

}

/**
 * Display widget content for "home bottom message" section.
 *
 */
function optimal_home_loop_helper_bottom_message() {
		
		genesis_widget_area( 'home-bottom-message', array(
			'before' => '<div class="home-bottom-message"><div class="wrap">',
			'after' => '</div></div>',
		) );
		
}

genesis();