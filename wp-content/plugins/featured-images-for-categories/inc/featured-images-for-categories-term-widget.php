<?php
	class WPFeaturedImgCategoriesTermWidget extends WP_Widget {
	
		/**
		 * Register widget with WordPress.
		 */
		public function __construct() {
			parent::__construct(
				'wpfifc_term_widget', // Base ID
				'Featured Images for Categories - Term', // Name
				array( 'description' => 'Display the featured images for a specific term from a category tag or any custom taxonomy in a widget area', ) // Args
			);
		}
	
		/**
		 * Front-end display of widget.
		 *
		 * @see WP_Widget::widget()
		 *
		 * @param array $args     Widget arguments.
		 * @param array $instance Saved values from database.
		 */
		public function widget( $args, $instance ) {
			global $wp_version;
		
			$is_above_44 = false;
			if( version_compare( $wp_version, '4.4', '>=' ) ){
				$is_above_44 = true;
			}
			//get taxonomies that want featured_images to be disabled
			$plugin_settings = get_option( 'settings_page_wpfifc_options' );
			
			echo $args['before_widget'];
			
			$taxonomy = $instance['wpfifc_term_widget_taxonomy'];
			$taxonomy_term = $instance['wpfifc_term_widget_taxonomy_term'];
			$imagesize = $instance['wpfifc_term_widget_imagesize'];
			$display_term = $instance['wpfifc_term_display_term'];
			/*echo $taxonomy.'<br />';
			echo $taxonomy_term.'<br />';
			echo $imagesize.'<br />';
			echo $display_term.'<br />';
			exit;*/
			if ( $taxonomy == '' || !$taxonomy_term ){
				echo $args['after_widget'];
				return;
			}
			$term = get_term_by('term_id', $taxonomy_term, $taxonomy);
			if( !$term ){
				echo $args['after_widget'];
				return;
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

			$output = '<div class="FeaturedImageTaxTermWidget'.$args['widget_id'].'">'."\n";
			$images_str = '';
			if ( $is_above_44 ){
				$thumbnail_id = get_term_meta( $term->term_id, 'wpfifc_featured_image', true );
			}else{
				$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term->term_id.'_thumbnail_id_', 0 );
			}

			if ( $thumbnail_id < 1 ){
				echo $args['after_widget'];
				return;
			}
			$image = wp_get_attachment_image_src( $thumbnail_id, $imagesize );

			list($src, $width, $height) = $image;
			if ( !$src ){
				echo $args['after_widget'];
				return;
			}
			$images_str .= '<div class="FeaturedImageTaxTermImg FeaturedImageTax--item">
								<a href="'.get_term_link($term->slug, $taxonomy).'" title="'.$term->name.'" class="FeaturedImageTax--anchor">
									<img src="'.$src.'" alt="'.$term->name.'" class="FeaturedImageTax--img"/>
								</a>
							</div>'."\n";
			if( $display_term != 'no' ){
				$term_str = '<span class="FeaturedImageTaxTermSpan FeaturedImageTax--category">'.$term->name.'</span>';
			}
			if( $display_term == 'above' ){
				$output .= $term_str.$images_str;
			}else if( $display_term == 'below' ){
				$output .= $images_str.$term_str;
			}else{
				$output .= $images_str;	
			}
			$output .= '</div>'."\n";
			
			echo $output;
			
			
			echo $args['after_widget'];
		}
	
		/**
		 * Sanitize widget form values as they are saved.
		 *
		 * @see WP_Widget::update()
		 *
		 * @param array $new_instance Values just sent to be saved.
		 * @param array $old_instance Previously saved values from database.
		 *
		 * @return array Updated safe values to be saved.
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = array();
			$instance['wpfifc_term_widget_taxonomy'] = strip_tags( $new_instance['wpfifc_term_widget_taxonomy'] );
			$instance['wpfifc_term_widget_taxonomy_term'] = strip_tags( $new_instance['wpfifc_term_widget_taxonomy_term'] );
			$instance['wpfifc_term_widget_imagesize'] = strip_tags( $new_instance['wpfifc_term_widget_imagesize'] );
			$instance['wpfifc_term_display_term'] = strip_tags( $new_instance['wpfifc_term_display_term'] );
	
			return $instance;
		}
	
		/**
		 * Back-end widget form.
		 *
		 * @see WP_Widget::form()
		 *
		 * @param array $instance Previously saved values from database.
		 */
		public function form( $instance ) {
			$wpfifc_taxonomy = isset( $instance[ 'wpfifc_term_widget_taxonomy' ] ) ? $instance[ 'wpfifc_term_widget_taxonomy' ] : 'category';
			$wpfifc_taxonomy_term = isset( $instance[ 'wpfifc_term_widget_taxonomy_term' ] ) ? $instance[ 'wpfifc_term_widget_taxonomy_term' ] : '';
			$wpfifc_imagesize = isset( $instance[ 'wpfifc_term_widget_imagesize' ] ) ? $instance[ 'wpfifc_term_widget_imagesize' ] : 'thumbnail';
			$wpfifc_display_term = isset( $instance[ 'wpfifc_term_display_term' ] ) ? $instance[ 'wpfifc_term_display_term' ] : 'no';
			
			
			$args = array( 'public' => true );
			$output = 'objects';
			$all_taxes = get_taxonomies( $args, $output );
			
			$image_sizes = get_intermediate_image_sizes();
			
			//get terms of selected taxonomy
			if( $wpfifc_taxonomy ){
				$args = array(	'orderby'       => 'name', 
								'order'         => 'ASC',
								'hide_empty'	=> false );  
				$terms = get_terms( $wpfifc_taxonomy, $args );
			}
			?>
            <p>Taxonomy:<br />
                <select name="<?php echo $this->get_field_name( 'wpfifc_term_widget_taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'wpfifc_term_widget_taxonomy' ); ?>" class="wpfifc_taxonomy_select" onchange="wpfifc_taxonomy_select_change( this );" style="width:150px;">
                	<option value="">Please select taxonomy</option>
					<?php foreach( $all_taxes as $taxonomy ): ?>
                    <option value="<?php echo $taxonomy->name ?>" <?php if( $wpfifc_taxonomy == $taxonomy->name ){ echo ' selected="selected"'; } ?>><?php echo $taxonomy->name ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>Term:<br />
                <select name="<?php echo $this->get_field_name( 'wpfifc_term_widget_taxonomy_term' ); ?>" id="<?php echo $this->get_field_id( 'wpfifc_term_widget_taxonomy_term' ); ?>" style="width:150px;">
					<?php if(count($terms) > 0 ){
								foreach( $terms as $term_item ): 
					?>
                    <option value="<?php echo $term_item->term_id ?>" <?php if( $wpfifc_taxonomy_term == $term_item->term_id ){ echo ' selected="selected"'; } ?>><?php echo $term_item->name ?></option>
                    <?php 
								endforeach; 
							}
					?>
                </select>
            </p>
            <p>Image Size:<br />
               <select name="<?php echo $this->get_field_name( 'wpfifc_term_widget_imagesize' ); ?>" id="<?php echo $this->get_field_id( 'wpfifc_term_widget_imagesize' ); ?>" style="width:150px;">
               <?php foreach ($image_sizes as $size_name): ?>
               		<option value="<?php echo $size_name ?>" <?php if( $wpfifc_imagesize == $size_name ){ echo ' selected="selected"'; } ?>><?php echo $size_name ?></option>
               <?php endforeach; ?>
               </select>
            </p>
            <p>Display term?<br />
               <select name="<?php echo $this->get_field_name( 'wpfifc_term_display_term' ); ?>" id="<?php echo $this->get_field_id( 'wpfifc_term_display_term' ); ?>" style="width:150px;">
                    <option value="above"<?php if ($wpfifc_display_term == 'above') echo ' selected="selected"' ?>>above featured image</option>	
                    <option value="below"<?php if ($wpfifc_display_term == 'below') echo ' selected="selected"' ?>>below featured image</option>
                    <option value="no"<?php if ($wpfifc_display_term == 'no') echo ' selected="selected"' ?>>no</option>	
               </select>
            </p>
            <?php
		}
	} // class