<?php

class WPFeaturedImgCategoriesFront {
	
	var $_wpfifc_plugin_settings_option = '';
	var $_wpfifc_plugin_page_slug = '';
	
	var $_wpfifc_CLASS_pro = NULL;
	
	public function __construct( $args ) {
		
		$this->_wpfifc_plugin_settings_option = $args['plugin_settings_option'];
		$this->_wpfifc_plugin_page_slug = $args['wpfifc_plugin_page_slug'];
		$this->_wpfifc_CLASS_pro = $args['wpfifc_pro_instance'];
		
		//shortcodes
		add_shortcode('FeaturedImagesCat', array($this, 'wpfifc_front_show') );
		add_action( 'genesis_before_loop', array($this, 'genesis_show_taxonomy_image'), 12 );
	}

	function wpfifc_front_show( $atts ){
		global $wp_version;
		
		$is_above_44 = false;
		if( version_compare( $wp_version, '4.4', '>=' ) ){
			$is_above_44 = true;
		}
		if( $this->_wpfifc_CLASS_pro ){
			//pro version
			if( !$this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
				$setting_page = admin_url('options-general.php?page='.$this->_wpfifc_plugin_page_slug);
				$output = '<div class="FeaturedImageTax">'."\n";
				$output .= '<p>Please go to <a href="'.$setting_page.'">plugin setting page</a> activate the your license first.</p>';
				$output .= '</div>';
				
				return $output;
			}
		}
		
		//get taxonomies that want featured_images to be disabled
		$taxonomies_to_dsiable_featured_images = array();
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
				$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			}
		}
		
		extract( shortcode_atts( array(
							  'taxonomy' => '',
							  'columns' => 0,
							  'imagesize' => '',
							  'orderby' => 'name',
							  'order' => 'ASC',
							  'hideempty' => 0,
							  'showcatname' => 0,
							  'showcatdesc' => 0,
							  'include' => '',
							  'parentcatsonly' => 0,
							  'childrenonly' => '',
							  'noinlinestyle' => 0,
							  'randomly' => 0), 
						$atts )
			   );
		$show_cat_name = false;
		$show_cat_desc = false;
		if( $showcatname && is_string($showcatname) ){
			$show_cat_name = $showcatname == 'true' ? true : false;
		}else if( is_bool($showcatname) ){
			$show_cat_name = $showcatname;
		}
		$show_cat_desc = false;
		if( $showcatdesc && is_string($showcatdesc) ){
			$show_cat_desc = $showcatdesc == 'true' ? true : false;
		}else if( is_bool($showcatdesc) ){
			$show_cat_desc = $showcatdesc;
		}
		$include_array = array();
		if( $include && is_string($include) ){
			$include_array = explode(',', $include);
			foreach($include_array as $key => $include_term_id){
				$include_term_id = intval($include_term_id);
				if( is_int($include_term_id) == false ){
					unset($include_array[$key]);
				}
				$include_array[$key] = $include_term_id;
			}
		}
		//check if only show parent categories
		$show_parent_cat_only = false;
		if( $parentcatsonly && is_string($parentcatsonly) ){
			$show_parent_cat_only = $parentcatsonly == 'true' ? true : false;
		}else if( is_bool($parentcatsonly) ){
			$show_parent_cat_only = $parentcatsonly;
		}
		//show the child categories only of the included category ids.
		$childrenonly_array = array();
		if( $childrenonly && is_string($childrenonly) ){
			$childrenonly_array = explode(',', $childrenonly);
			foreach($childrenonly_array as $key => $childrenonly_term_id){
				$childrenonly_term_id = intval($childrenonly_term_id);
				if( is_int($childrenonly_term_id) == false ){
					unset($childrenonly_array[$key]);
				}
				$childrenonly_array[$key] = $childrenonly_term_id;
			}
		}
		//don't output inline style
		$no_inline_style = false;
		if( $noinlinestyle && is_string($noinlinestyle) ){
			$no_inline_style = $noinlinestyle == 'true' ? true : false;
		}else if( is_bool($noinlinestyle) ){
			$no_inline_style = $noinlinestyle;
		}
		
		//show images randomly
		$show_images_randomly = false;
		if( $randomly && is_string($randomly) ){
			$show_images_randomly = $randomly == 'true' ? true : false;
		}else if( is_bool($randomly) ){
			$show_images_randomly = $randomly;
		}
		
		if ( $taxonomy == '' || in_array( $taxonomy, $taxonomies_to_dsiable_featured_images) ){
			$output = '<p>Invalid taxonomy, or you have disabled featured image from this taxonomy.</p>';
			return $output;
		}
		$taxonomy_obj = get_taxonomy( $taxonomy );
		if ( !$taxonomy_obj ){
			$output = '<p>Invalid taxonomy.</p>';
			return $output;
		}
		$orderby = strtolower($orderby);
		if($orderby != 'name' && $orderby != 'slug' && $orderby != 'id' && $orderby != 'count'){
			$orderby != 'name';
		}
		$order = strtoupper($order);
		if($order != 'ASC' && $order != 'DESC'){
			$order = 'ASC';
		}
		$hideempty = intval($hideempty);
		if($hideempty !== 0 && $hideempty !== 1){
			$hideempty = 0;
		}
		//get terms
		$taxonomy_terms = array();
		if( count($include_array) > 0 ){ //if a user has all three of these options we should give priority to include first and ignore the others
			$show_parent_cat_only = false;
			$taxonomy_terms = get_terms( $taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hideempty, 'include' => $include_array) );
			if ( !$taxonomy_terms || count($taxonomy_terms) < 1 ){
				//$output = '<p>No term('.implode(',', $include_array).') exist in the taxonomy('.$taxonomy.').</p>';
				return;
			}
			//re-order terms by include ids order
			$temp_terms_array = array();
			$taxonomy_terms_id_as_key = array();
			foreach($taxonomy_terms as $key => $term_obj){
				$taxonomy_terms_id_as_key[$term_obj->term_id] = $term_obj;
			}
			foreach($include_array as $include_term_id){
				$temp_terms_array[$include_term_id] = $taxonomy_terms_id_as_key[$include_term_id];
			}
			$taxonomy_terms = $temp_terms_array;
		}else if( $show_parent_cat_only ){ //If a user has parentcatsonly and childrenonly then we should use parentcatsonly and ignore childrenonly.
			$taxonomy_terms = get_terms( $taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hideempty) );
			if ( !$taxonomy_terms || count($taxonomy_terms) < 1 ){
				//$output = '<p>No term exist in the taxonomy('.$taxonomy.').</p>';
				return;
			}
		}else if( count($childrenonly_array) > 0 ){
			$show_parent_cat_only = false;
			foreach( $childrenonly_array as $parent_term_id ){
				$child_terms = get_terms( $taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hideempty, 'parent' => $parent_term_id) );
				if ( !$child_terms || count($child_terms) < 1 ){
					continue;
				}
				$taxonomy_terms = array_merge($taxonomy_terms, $child_terms);
			}
		}else{
			$taxonomy_terms = get_terms( $taxonomy, array('orderby' => $orderby, 'order' => $order, 'hide_empty' => $hideempty) );
			if ( !$taxonomy_terms || count($taxonomy_terms) < 1 ){
				//$output = '<p>No term exist in the taxonomy('.$taxonomy.').</p>';
				return;
			}
		}

		//check imagesize
		if ( $imagesize == '' ){
			if( isset($plugin_settings['wpfifc_default_size']) ){
				$imagesize = $plugin_settings['wpfifc_default_size'];
			}
		}
		if ( $imagesize == '' ){
			$imagesize = 'thumbnail';
		}
		
		$image_sizes = get_intermediate_image_sizes();
		if ( $imagesize != 'thumbnail' && $imagesize != 'full' && !in_array( $imagesize, $image_sizes) ){
			$imagesize = 'thumbnail';
		}
		//get padding
		$padding = 2;
		if( isset($plugin_settings['wpfifc_image_padding']) ){
			$padding = $plugin_settings['wpfifc_image_padding'];
		}
		//get columns
		if ( $columns == 0 ){
			$columns = 3; 
			if( isset($plugin_settings['wpfifc_default_columns']) ){
				$columns = $plugin_settings['wpfifc_default_columns'];
			}	
		}
		//caculate column width
		$column_width = floor(100 / $columns);
		
		//show images randomly
		if( $show_images_randomly ){
			shuffle($taxonomy_terms);
		}
		
		$output = '<div class="FeaturedImageTax">'."\n";
		$images_str = '';
		$column_item = 0;
		foreach( $taxonomy_terms as $term ){
			if( $show_parent_cat_only && $term->parent != 0 ){
				continue;
			}
			$term_id = $term->term_id;
			
			/*
			 * Above 4.4, we use term meta to save thumbnail id
			*/
			if( $is_above_44 ){
				$thumbnail_id = get_term_meta( $term_id, 'wpfifc_featured_image', true );
			}else{
				$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
			}

			$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );

			list($src, $width, $height) = $image;
			if ( $src ){
				$padding_str = $padding ? $padding.'px;' : '0;';
				if( $no_inline_style ){
					$images_str .= '<div class="FeaturedImageTax--item">
										<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'" class="FeaturedImageTax--anchor">
											<img src="'.$src.'" alt="'.$term->name.'" class="FeaturedImageTax--img"/>
										</a>';
				}else{
				$images_str .= '<div style="width:'.$column_width.'%; text-align:center;float:left;" class="FeaturedImageTax--item">
									<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'" class="FeaturedImageTax--anchor">
										<img src="'.$src.'" alt="'.$term->name.'" style="padding:'.$padding_str.'" class="FeaturedImageTax--img"/>
									</a>';
				}
				
				if( $show_cat_name ){
					$images_str .= '
									<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'" class="FeaturedImageTax--anchor">
										<h2 class="FeaturedImageCat FeaturedImageTax--category">'.$term->name.'</h2>
									</a>';
				}
				if( $show_cat_desc ){					
					$images_str .= '<div class="FeaturedImageCatDesc FeaturedImageTax--description">'.$term->description.'</div>';
				}					
				$images_str .= '</div>'."\n";
				$column_item++;
				if ( $column_item >= $columns ){
					$column_item = 0;
					$images_str .= "\n".'<div style="clear:both;"></div>'."\n";
				}
			}
		}
		$output .= $images_str;
		$output .= '</div>'."\n";
		
		return $output;
	}
	
	
	function genesis_show_taxonomy_image(){
		global $wp_version;
		
		if(!defined('PARENT_THEME_NAME') || PARENT_THEME_NAME != 'Genesis'){
			return;
		}
		global $wp_query;

		if (!is_category() && !is_tag() && !is_tax()){
			return;
		}
	
		if (get_query_var( 'paged' ) >= 2){
			return;
		}
		$taxonomy = '';
		if(is_category()){
			$taxonomy = 'category';
		}
		if(is_tag()){
			$taxonomy = 'post_tag';
		}
		if(is_tax()){
			$taxonomy = get_query_var('taxonomy');
		}
		$saved_genesis_taxonomies = array();
		$saved_genesis_postion = 'left';
		$imagesize = '';
		
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['wpfifc_genesis_taxonomy']) ){
				$saved_genesis_taxonomies = $plugin_settings['wpfifc_genesis_taxonomy'];
			}
			if( isset($plugin_settings['wpfifc_genesis_position']) ){
				$saved_genesis_postion = $plugin_settings['wpfifc_genesis_position'];
			}
			if( isset($plugin_settings['wpfifc_default_size']) ){
				$imagesize = $plugin_settings['wpfifc_default_size'];
			}
		}
		if ( $imagesize == '' ){
			$imagesize = 'thumbnail';
		}
		if (!is_array($saved_genesis_taxonomies) || count($saved_genesis_taxonomies) < 1){
			return;
		}
		if(!in_array($taxonomy, $saved_genesis_taxonomies)){
			return;
		}

		$term = is_tax() ? get_term_by('slug', get_query_var('term'), get_query_var('taxonomy')) : $wp_query->get_queried_object();
		if( !$term ){
			return;
		}
		$term_id = $term->term_id;
		if( version_compare( $wp_version, '4.4', '>=' ) ){
			$thumbnail_id = get_term_meta( $term_id, 'wpfifc_featured_image', true );
		}else{
			$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
		}
		if( $thumbnail_id < 1 ){
			return;
		}
		$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );
		
		list($src, $width, $height) = $image;
		if ( $src ){
			echo '<img src="'.$src.'" style="float:'.$saved_genesis_postion.';" class="FeaturedImageTax"/>';
		}
		
		return;
	}
}

