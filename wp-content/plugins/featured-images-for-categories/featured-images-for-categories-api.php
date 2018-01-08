<?php

function fifc_get_tax_thumbnail( $category_id, $taxonomy, $image_size = 'thumbnail', &$err = ''){
	$err = '';
	if( $category_id < 1 ){
		$err = 'A category id is required';
		return '';
	}
	if( strlen($taxonomy) < 1 ){
		$err = 'A taxonomy is required';
		return '';
	}
	
	//check if the taxonomy disabled featured images
	$taxonomies_to_dsiable_featured_images = array();
	$plugin_settings = get_option( '_wpfific_plugin_settings_' );
	if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
		if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
			$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			if( is_array($taxonomies_to_dsiable_featured_images) && 
				in_array( $taxonomy, $taxonomies_to_dsiable_featured_images ) ){
				$err = 'Featured images for this taxonomy has been disabled';
				return '';
			}
		}
	}
	
		
	$term_obj = get_term_by( 'id', $category_id, $taxonomy);
	if( !$term_obj ){
		$err = 'Invalid category id or taxonomy';
		return '';
	}
	//check if the image_size exit
	$systme_image_sizes = get_intermediate_image_sizes();
	if ( $image_size != 'thumbnail' && $image_size != 'full' && !in_array( $image_size, $systme_image_sizes) ){
		$image_size = 'thumbnail';
	}
	
	//get thumbnail
	/*
	 * Above 4.4, we use term meta to save thumbnail id
	*/
	global $wp_version;
	if( version_compare( $wp_version, '4.4', '>=' ) ){
		$thumbnail_id = get_term_meta( $category_id, 'wpfifc_featured_image', true );
	}else{
		$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$category_id.'_thumbnail_id_', 0 );
	}
	if ( $thumbnail_id < 1 ){
		$err = 'A category id is required or the category hasn\'t been assigned featured image.';
		return '';
	}
	$image = wp_get_attachment_image_src( $thumbnail_id, $image_size );
	
	list($src, $width, $height) = $image;
	if ( $src ){
		return $src;
	}
	
	$err = 'Invalid featured image';
	return '';
}

function fifc_the_tax_thumbnail( $category_id, $taxonomy, $image_size = 'thumbnail' ){
	$err_ret = '';
	$image_url = fifc_get_tax_thumbnail( $category_id, $taxonomy, $image_size, $err_ret);
	if( $err_ret || $image_url == ''){
		echo $err_ret;
		return '';
	}
	
	//check if the taxonomy disabled featured images
	$taxonomies_to_dsiable_featured_images = array();
	$plugin_settings = get_option( '_wpfific_plugin_settings_' );
	if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
		if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
			$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			if( is_array($taxonomies_to_dsiable_featured_images) && 
				in_array( $taxonomy, $taxonomies_to_dsiable_featured_images ) ){
				$err = 'Featured images on this taxonomy has been disabled';
				return '';
			}
		}
	}
	
	
	$term_obj = get_term_by( 'id', $category_id, $taxonomy);
	if( !$term_obj ){
		echo 'Invalid category id or taxonomy';
		return;
	}
	
	echo '<a href="'.get_term_link($term_obj->slug, $taxonomy).'" title="'.$term_obj->name.'">';
	echo '<img src="'.$image_url.'" class="FeaturedImageTax"/>';
	echo '</a>';
}

function fifc_get_tax_colour( $category_id, $taxonomy, $hash = false ){
	if( !file_exists( dirname(__FILE__).'/pro/featured-images-for-cat-pro.php') ){
		$err = 'Only available in Pro version';
		return '';
	}
	$err = '';
	if( $category_id < 1 ){
		$err = 'A category id is required';
		return '';
	}
	if( strlen($taxonomy) < 1 ){
		$err = 'A taxonomy is required';
		return '';
	}
	$term_obj = get_term_by( 'id', $category_id, $taxonomy);
	if( !$term_obj ){
		$err = 'Invalid category id or taxonomy';
		return '';
	}
	
	$colour = get_term_meta( $category_id, 'wpfifc_colour', true );
    $colour = ltrim( $colour, '#' );
	$colour = preg_match( '/([A-Fa-f0-9]{3}){1,2}$/', $colour ) ? $colour : '';

    return $hash && $colour ? "#{$colour}" : $colour;
}
