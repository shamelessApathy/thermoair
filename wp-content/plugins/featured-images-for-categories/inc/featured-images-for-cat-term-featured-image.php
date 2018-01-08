<?php


class WPFeaturedImgCategoriesTermFeaturedImage {
	
	var $_wpfifc_plugin_settings_option = '';
	var $_wpfifc_version = '';
	var $_wpfifc_plugin_folder = '';
	var $_wpfifc_plugin_page_slug = '';
	var $_wpfifc_enqueue_scripts_hook_suffix = '';
	
	var $_wpfifc_CLASS_pro = NULL;
	
	public function __construct( $args ) {
		$this->_wpfifc_plugin_settings_option = $args['plugin_settings_option'];
		$this->_wpfifc_plugin_folder = $args['wpfifc_plugin_folder'];
		$this->_wpfifc_plugin_page_slug = $args['wpfifc_plugin_page_slug'];
		$this->_wpfifc_version = $args['wpfifc_version'];
		$this->_wpfifc_CLASS_pro = $args['wpfifc_pro_instance'];
		$this->_wpfifc_enqueue_scripts_hook_suffix = $args['wpfifc_enqueue_scripts_hook_suffix'];

		//enale theme support feature iamge
		add_theme_support( 'post-thumbnails' );
		
		global $wp_version;
		
		if( version_compare( $wp_version, '4.4', '>=' ) ){
			//register temr data
			add_action( 'init', array($this, 'wpfifc_register_term_meta') );
		}
		
		//create custome field for taxonomies
		add_action( 'admin_init', array($this, 'wpfifc_taxonomies_add_term_feature_images_fields'), 998 );
		//ajax action
		add_action( 'wp_ajax_wpfifc_set_image', array($this, 'wpfifc_ajax_set_post_thumbnail') );
		
		add_action( 'admin_enqueue_scripts', array($this, 'wpfifc_enqueue_scripts_n_styles'), 998 );
	}

	

	function wpfifc_enqueue_scripts_n_styles( $hook_suffix  ){
		if ( 
			 'edit-tags.php' !== $hook_suffix && 
			 'term.php' != $hook_suffix &&
			 $hook_suffix != $this->_wpfifc_enqueue_scripts_hook_suffix ){
			return;
		}
		global $wp_version;
		
		if( function_exists( 'wp_enqueue_media' ) && version_compare( $wp_version, '3.5', '>=' ) ) {
			wp_enqueue_media();
		}
		$plugin_folder_url = plugins_url().'/'.$this->_wpfifc_plugin_folder;
		wp_enqueue_script( 'wpfifc-admin', $plugin_folder_url.'/js/featured-images-for-cat-pro-admin.js', array( 'jquery' ), $this->_wpfifc_version );
		wp_enqueue_style( 'wpfifc-admin', $plugin_folder_url.'/css/featured-images-for-cat-pro-admin.css', array(), $this->_wpfifc_version );
		wp_enqueue_style( 'thickbox' );
	}
	
	function wpfifc_register_term_meta(){
		register_meta( 'term', 'wpfifc_featured_image', array($this, 'wpfifc_sanitize_int') );
	}
	
	function wpfifc_sanitize_int( $thumbnail_id ){
		if( is_numeric( $thumbnail_id ) ){
			return $thumbnail_id;
		}
		
		return 0;
	}

	function wpfifc_taxonomies_add_term_feature_images_fields(){
		$args = array( 'public' => true );
		$output = 'objects';
		$add_taxes = get_taxonomies( $args, $output );
		
		foreach ( $add_taxes  as $add_tax ) {
			if ( $add_tax->name == 'nav_menu' || $add_tax->name == 'post_format') {
				continue;
			}
			if( $this->_wpfifc_CLASS_pro && $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
			}else{
				if ( $add_tax->name != 'category' && $add_tax->name != 'post_tag') {
					continue;
				}
			}
			add_action( $add_tax->name.'_edit_form_fields', array($this, 'wpfifc_taxonomies_edit_meta'), 10, 2 );
			
			if( $this->_wpfifc_CLASS_pro && $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
				add_filter( 'manage_edit-'.$add_tax->name.'_columns', array($this, 'manage_taxonomy_columns') );
				add_filter ('manage_'.$add_tax->name.'_custom_column', array($this, 'manage_taxonomy_custom_columns'), 10,3 );
			}
		}
	}

	//edit term page
	function wpfifc_taxonomies_edit_meta( $term ) {
 		// put the term ID into a variable
		$term_id = $term->term_id;
		
		if( $this->_wpfifc_CLASS_pro ){
			if( $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
				$taxonomies_to_dsiable_featured_images = array();
				$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
				if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
					if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
						$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
					}
				}
				
				if( is_array($taxonomies_to_dsiable_featured_images) && 
					in_array( $term->taxonomy, $taxonomies_to_dsiable_featured_images ) ){
					//nothing
				}else{
					$this->wpfifc_taxonomies_edit_meta_featured_image( $term_id );
				}
			}
			
			return;
		}
		
		//free version
		$taxonomies_to_dsiable_featured_images = array();
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
				$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			}
		}
		
		if( is_array($taxonomies_to_dsiable_featured_images) && 
			in_array( $term->taxonomy, $taxonomies_to_dsiable_featured_images ) ){
			//nothing
		}else{
			$this->wpfifc_taxonomies_edit_meta_featured_image( $term_id );
		}
	}
	
	function wpfifc_taxonomies_edit_meta_featured_image( $term_id ) {
		global $wp_version;
		
		$post = get_default_post_to_edit( 'post', true );
		$post_ID = $post->ID;
	?>
    <tr class="form-field">
        <th>Set Featured Image</th>
        <td>
            <div id="postimagediv" class="postbox" style="width:95%;" >
                <div class="inside">
                    <?php
                        $remove_container_display = "none";
                        $thumbnail_html = '';
                        $thumbnail_id = 0;
                        /*
                         * Above 4.4, we use term meta to save thumbnail id
                        */
                        if( version_compare( $wp_version, '4.4', '>=' ) ){
                            $thumbnail_id = get_term_meta( $term_id, 'wpfifc_featured_image', true );
                        }else{
                            $thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
                        }
                        
                        global $content_width, $_wp_additional_image_sizes;
                        
                        if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
                            $old_content_width = $content_width;
                            $content_width = 266;
                            if ( !isset( $_wp_additional_image_sizes['post-thumbnail'] ) )
                                $thumbnail_html = wp_get_attachment_image( $thumbnail_id, array( $content_width, $content_width ) );
                            else
                                $thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'post-thumbnail' );
                                
                            $content_width = $old_content_width;
                            
                            $remove_container_display = "inline-block";
                        }
                    ?>
                    <p class="hide-if-no-js">
                        <a title="Set featured image" href="javascript:void(0);" id="wpfifc_set_post_thumbnail">
                            <?php echo $thumbnail_html ? $thumbnail_html : 'Set featured image'; ?>
                        </a>
                    </p>
                    
                    <p class="hide-if-no-js">
                        <a href="javascript:void(0);" id="wpfifc_remove_post_thumbnail" style="display:<?php echo $remove_container_display ?>">Remove featured image</a>
                        <span id="wpfifc_taxonomies_edit_ajax_loader_ID" style="display:none;"><img src="<?php echo $this->_wpfifc_ajax_loader_img_url; ?>" /></span>
                    </p>
                </div>
                <input type="hidden" name="wpfifc_taxonomies_edit_attachment_ID" id="wpfifc_taxonomies_edit_attachment_ID_id" value="<?php echo $thumbnail_id; ?>" />
                <input type="hidden" name="wpfifc_taxonomies_edit_term_ID" id="wpfifc_taxonomies_edit_term_ID_id" value="<?php echo $term_id; ?>" />
            </div>
        </td>
    </tr>
	<?php
	}
	
	function wpfifc_ajax_set_post_thumbnail() {
		global $current_user, $wp_version;

		if ( $current_user->ID < 0 ){
			wp_die( 'ERROR: You are not allowed to do the operation.' );
		}
		if( $_POST['term_id'] < 1 ){
			wp_die( 'ERROR: Invalid term ID' );
		}
		$thumbnail_id = intval( $_POST['thumbnail_id'] );
		
		/*
		 * Above 4.4, we use term meta to save thumbnail id
		*/
		if( version_compare( $wp_version, '4.4', '>=' ) ){
			if( $thumbnail_id < 1 ){
				delete_term_meta( $_POST['term_id'], 'wpfifc_featured_image' );
				wp_die( '' );
			}
			//set term meta
			update_term_meta( $_POST['term_id'], 'wpfifc_featured_image', $thumbnail_id );

		}else{
			if( $thumbnail_id < 1 ){
				delete_option( '_wpfifc_taxonomy_term_'.$_POST['term_id'].'_thumbnail_id_' );
				wp_die( '' );
			}
			//set thumbnail id
			update_option( '_wpfifc_taxonomy_term_'.$_POST['term_id'].'_thumbnail_id_', $thumbnail_id );
		}
		
		//get html
		$thumbnail_html = '';
		
		global $content_width, $_wp_additional_image_sizes;
		if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
			$old_content_width = $content_width;
			$content_width = 266;
			if ( !isset( $_wp_additional_image_sizes['post-thumbnail'] ) )
				$thumbnail_html = wp_get_attachment_image( $thumbnail_id, array( $content_width, $content_width ) );
			else
				$thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'post-thumbnail' );
				
			$content_width = $old_content_width;
		}
							
		wp_die( $thumbnail_html );
	}
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////////// 
	// thanks to Jarel Culley for the contribution of this idea and sample code // 		
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	function manage_taxonomy_columns( $columns ) {
		global $wp_version;
		
		$current_taxonomy = $_REQUEST['taxonomy'];
		$taxonomies_to_dsiable_featured_images = array();
		$show_featured_images_on_taxonomy_management_pages = 'yes';
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
				$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			}
			if( isset($plugin_settings['show_featured_images_on_taxonomy_management_pages']) ){
				$show_featured_images_on_taxonomy_management_pages = $plugin_settings['show_featured_images_on_taxonomy_management_pages'];
			}
		}
		if( is_array($taxonomies_to_dsiable_featured_images) && 
			in_array( $current_taxonomy, $taxonomies_to_dsiable_featured_images ) ){
			return $columns;
		}
		if( $show_featured_images_on_taxonomy_management_pages == 'no' ){
			return $columns;
		}
		
		$columns['wpfifc_featured_image'] = 'Featured Image';
		
		return $columns;
	} 
	
	function manage_taxonomy_custom_columns($out, $column_name, $term_id) {
		global $wp_version;
		
		if ($column_name == 'wpfifc_featured_image') {
			/*
			 * Above 4.4, we use term meta to save thumbnail id
			*/
			$current_taxonomy = $_REQUEST['taxonomy'];
			$taxonomies_to_dsiable_featured_images = array();
			$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
			if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
				if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
					$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
				}
			}
			if( is_array($taxonomies_to_dsiable_featured_images) && 
				in_array( $current_taxonomy, $taxonomies_to_dsiable_featured_images ) ){
				return '';
			}
			
			if( version_compare( $wp_version, '4.4', '>=' ) ){
				$thumbnail_id = get_term_meta( $term_id, 'wpfifc_featured_image', true );
			}else{
				$thumbnail_id = get_option( '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_', 0 );
			}
			$image_src = wp_get_attachment_image_src($thumbnail_id, 'thumbnail');
			if( is_array($image_src) && count($image_src) > 0 ){
				return '<img src="'.$image_src[0].'" style="width:100%;"/>';
			}
		}
		
		return $out;
	}
}
