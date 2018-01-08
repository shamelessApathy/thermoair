<?php

class WPFeaturedImgCategoriesOptionsPro{
	
	var $_wpfifc_plugin_title = '';
	var $_wpfifc_plugin_folder = '';
	var $_wpfifc_plugin_page_slug = '';
	var $_wpfifc_license_key_option = '';
	var $_wpfifc_license_key_status_option = '';
	var $_wpfifc_ajax_loader_image_url = '';
	var $_wpfifc_plugin_settings_option = '';
	var $_database_option_name = '';
	var $_database_version = '';
	
	var $_wpfifc_CLASS_pro = NULL;
	var $_wpfifc_CLASS_data_migration = NULL;
	
	public function __construct( $args ) {
		
		$this->_wpfifc_plugin_title = $args['wpfifc_plugin_title'];
		$this->_wpfifc_plugin_folder = $args['wpfifc_plugin_folder'];
		$this->_wpfifc_plugin_page_slug = $args['wpfifc_plugin_page_slug'];
		
		$this->_wpfifc_ajax_loader_image_url = $args['ajax_loader_img_url'];
		$this->_wpfifc_plugin_settings_option = $args['plugin_settings_option'];
		$this->_wpfifc_CLASS_pro = $args['wpfifc_pro_instance'];
		
		add_action( 'admin_menu', array($this, 'wpfifc_options_menu') );
		
		add_action( 'wpfifc_action_tab_main_save_settings', array($this, 'wpfifc_action_tab_main_save_settings_fun') );
		add_action( 'wpfifc_action_tab_genesis_save_settings', array($this, 'wpfifc_action_tab_genesis_save_settings_fun') );
		
		require_once( 'featured-images-for-cat-migration.php' );
		$this->_wpfifc_CLASS_data_migration = new WPFeaturedImgCategoriesMigrationPro( $args );
	}
	
	function wpfifc_options_menu() {
		
		add_options_page( $this->_wpfifc_plugin_title, 
						  $this->_wpfifc_plugin_title, 
						  'manage_options', 
						  $this->_wpfifc_plugin_page_slug, 
						  array($this, 'wpfifc_options') 
						);
	}
	
	function wpfifc_options() {
		if( ! current_user_can( 'manage_options' ) ){
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}		
		
		if( isset($_REQUEST['view']) && $_REQUEST['view'] == 'migration' ){
			$this->_wpfifc_CLASS_data_migration->wpfifc_data_migration_view();
			
			return;
		}
		
		
		$action = $_SERVER["REQUEST_URI"];
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option );
	?>
		<div class="wrap" id="wpfifc_options_form_ID">
			<img src="<?PHP echo plugins_url().'/'.$this->_wpfifc_plugin_folder; ?>/images/help-for-wordpress-small.png" align="left" />
			<h2><?php echo $this->_wpfifc_plugin_title; ?></h2>
            <h2 class="nav-tab-wrapper">
                <a class="nav-tab nav-tab-active" href="javascript:void(0);" id="wpfifc_tab_anchor_main">Main</a>
            <?php
			if( $this->_wpfifc_CLASS_pro ){
				if( $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
			?>
                    <a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_getting_started">Getting Started</a>
                    <a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_more">More</a>
                    <?php
                    if(defined('PARENT_THEME_NAME') && PARENT_THEME_NAME == 'Genesis'){
                        echo '<a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_genesis">Genesis Framework</a>';
                    }
                    ?>
            <?php
				}
			}else{
				//free version
				?>
                <a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_getting_started">Getting Started</a>
                <a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_more">More</a>
                <?php
                if(defined('PARENT_THEME_NAME') && PARENT_THEME_NAME == 'Genesis'){
                    echo '<a class="nav-tab" href="javascript:void(0);" id="wpfifc_tab_anchor_genesis">Genesis Framework</a>';
                }
			}
			?>
            </h2>
            
            <div id="wpfifc_options_tab_contents">
            	<section>
                    <?php
					if( $this->_wpfifc_CLASS_pro ){
						$this->_wpfifc_CLASS_pro->wpfifc_license_activate_form();
						if( $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
							?><form action="<?php echo $action; ?>" method="POST" id="wpfifc_settings"><?php
							$this->wpfifc_options_plugin_setting( $plugin_settings );
							$this->wpfifc_options_disable_featured_images_on_taxonomies( $plugin_settings );
							$this->wpfifc_options_show_featured_images_on_taxonomy_management_pages( $plugin_settings );
							$this->wpfifc_options_show_featured_colour_on_taxonomy_management_pages( $plugin_settings );
							global $_wpfifc_messager;
							
							$_wpfifc_messager->eddslum_plugin_option_page_update_center();
							$this->wpfifc_options_main_save_button();
							?></form><?php
						}
					}else{
						//free version
						?><form action="<?php echo $action; ?>" method="POST" id="wpfifc_settings"><?php
						$this->wpfifc_options_plugin_setting( $plugin_settings );
						$this->wpfifc_options_disable_featured_images_on_taxonomies( $plugin_settings );
						$this->wpfifc_options_main_save_button();
						?></form><?php
					}
                    ?>
                </section>
                <?php
				if( $this->_wpfifc_CLASS_pro ){
					if( $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
						$this->wpfifc_optons_getting_started_section( $plugin_settings );
						$this->wpfifc_options_more_section( $plugin_settings );
						$this->wpfifc_options_genesis_section( $plugin_settings );
					}
				}else{
					//free version
					$this->wpfifc_optons_getting_started_section( $plugin_settings );
					$this->wpfifc_options_more_section( $plugin_settings );
					$this->wpfifc_options_genesis_section( $plugin_settings );
				}
				?>
            </div>
            <?php
			$target_tab = '';
			if( isset($_POST['wpfifc_target_tab']) ){
				$target_tab = $_POST['wpfifc_target_tab'];
			}
			?>
            <input type="hidden" id="wpfifc_options_page_target_tab_ID" value="<?php echo $target_tab; ?>" />
		</div>
	<?php
	}
	
	function wpfifc_options_plugin_setting( $plugin_settings ){

		$padding = 2;
		$columns = 3;
		$saved_size = 'thumbnail';
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['wpfifc_image_padding']) ){
				$padding = $plugin_settings['wpfifc_image_padding'];
			}
			if( isset($plugin_settings['wpfifc_default_columns']) ){
				$columns = $plugin_settings['wpfifc_default_columns'];
			}
			if( isset($plugin_settings['wpfifc_default_size']) ){
				$saved_size = $plugin_settings['wpfifc_default_size'];
			}
		}
		$image_sizes = get_intermediate_image_sizes();
		?>
		<h3>Plugin Settings</h3>
        <table>
            <tr style="width:160px;">
                <td>
                    <input name="wpfifc_image_padding" type="text" id="wpfifc_image_padding_id" value="<?php echo $padding; ?>" style="width:20px;" maxlength="1" />&nbsp;&nbsp;
                </td>
                <td>Number of px to place around the image when output.</td>
            </tr>
            <tr>
                <td style="width:160px;">
                    <select name="wpfifc_default_columns" id="wpfifc_default_columns_id" style="width:150px;">
                        <option value="1"<?php if ($columns == 1) echo ' selected="selected"' ?>>1</option>	
                        <option value="2"<?php if ($columns == 2) echo ' selected="selected"' ?>>2</option>	
                        <option value="3"<?php if ($columns == 3) echo ' selected="selected"' ?>>3</option>	
                        <option value="4"<?php if ($columns == 4) echo ' selected="selected"' ?>>4</option>	
                        <option value="5"<?php if ($columns == 5) echo ' selected="selected"' ?>>5</option>	
                        <option value="6"<?php if ($columns == 6) echo ' selected="selected"' ?>>6</option>	
                    </select>
                </td>
                <td>
                    Choose the number of columns for the output. ( You can override this in the shortcode )     
                </td>
            </tr>
            <tr>
                <td style="width:160px;">
                    <select name="wpfifc_default_size" id="wpfifc_default_size_id" style="width:150px;">
                        <?php
                        foreach ($image_sizes as $size_name){
                            $selected_str = '';
                            if( $saved_size == $size_name ){ 
                                $selected_str = ' selected="selected"'; 
                            }
                            echo '<option value="'.$size_name.'"'.$selected_str.'>'.$size_name.'</option>';
                        }
						$selected_str = '';
						if( $saved_size == 'full' ){ 
							$selected_str = ' selected="selected"'; 
						}
						echo '<option value="full"'.$selected_str.'>Full Size</option>';
                        ?>
                    </select>
                </td>
                <td>
                    Choose the registered image size for the output. ( You can override this in the shortcode )     
                </td>
            </tr>
        </table>
    	<?php
	}
	
	function wpfifc_options_disable_featured_images_on_taxonomies( $plugin_settings ){
		$taxonomies_to_dsiable_featured_images = array();
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['taxonomies_to_dsiable_featured_images']) ){
				$taxonomies_to_dsiable_featured_images = $plugin_settings['taxonomies_to_dsiable_featured_images'];
			}
		}
		?>
		<h4>Disable featured images on these taxonomies</h4>
		<?php
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
			$checked_str = '';
			if( is_array($taxonomies_to_dsiable_featured_images) && in_array($add_tax->name, $taxonomies_to_dsiable_featured_images) ){
				$checked_str = ' checked="checked"';
			}
			echo '<p>
					<label>
						<input type="checkbox" name="wpfifc_taxonomies_to_disable_featured_images[]" value="'.$add_tax->name.'"'.$checked_str.'>'.$add_tax->label.'
					</label>
				  </p>';
		}
	}
	
	function wpfifc_options_show_featured_images_on_taxonomy_management_pages( $plugin_settings ){
		$show_featured_images_on_taxonomy_management_pages = 'yes';
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['show_featured_images_on_taxonomy_management_pages']) ){
				$show_featured_images_on_taxonomy_management_pages = $plugin_settings['show_featured_images_on_taxonomy_management_pages'];
			}
		}
		?>
		<h4>Show featured images on taxonomy management pages</h4>
		<?php
        $yes_checked = '';
        $no_checked = '';
        if( $show_featured_images_on_taxonomy_management_pages == 'yes' ){
            $yes_checked = ' checked="checked"';
        }else if( $show_featured_images_on_taxonomy_management_pages == 'no' ){
            $no_checked = ' checked="checked"';
        }
        ?>
        <p>
            <label><input type="radio" name="wpfifc_show_featured_images_on_taxonomy_management_pages" value="yes" <?php echo $yes_checked; ?>/>Yes</label>
            <label style="margin-left:20px;"><input type="radio" name="wpfifc_show_featured_images_on_taxonomy_management_pages" value="no" <?php echo $no_checked; ?>/>No</label>
        </p>
        <?php
	}
	
	function wpfifc_options_show_featured_colour_on_taxonomy_management_pages( $plugin_settings ){
		global $wp_version;
		
		if( version_compare( $wp_version, '4.4', '<' ) ){
			return;
		}
		$show_featured_colour_on_taxonomy_management_pages = 'yes';
		if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
			if( isset($plugin_settings['show_featured_colour_on_taxonomy_management_pages']) ){
				$show_featured_colour_on_taxonomy_management_pages = $plugin_settings['show_featured_colour_on_taxonomy_management_pages'];
			}
		}
		?>
		<h4>Show featured colour on taxonomy management pages</h4>
		<?php
		$yes_checked = '';
		$no_checked = '';
		if( $show_featured_colour_on_taxonomy_management_pages == 'yes' ){
			$yes_checked = ' checked="checked"';
		}else if( $show_featured_colour_on_taxonomy_management_pages == 'no' ){
			$no_checked = ' checked="checked"';
		}
		?>
		<p>
			<label><input type="radio" name="wpfifc_show_featured_colour_on_taxonomy_management_pages" value="yes" <?php echo $yes_checked; ?>/>Yes</label>
			<label style="margin-left:20px;"><input type="radio" name="wpfifc_show_featured_colour_on_taxonomy_management_pages" value="no" <?php echo $no_checked; ?>/>No</label>
		</p>
			<?php
	}
	
	function wpfifc_options_main_save_button(){
		$nonce = wp_create_nonce( '-wpfifc-tab-main-save-settings-' );
		?>
		<p>
			<input type="hidden" name="wpfifc_action" id="wpfifc_action_4_tab_main_form_ID" value="tab_main_save_settings" />
			<input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
			<input type="submit" class="button-primary" value="Save Settings" id="wpfifc_tab_main_save_settings_btn_ID" />
		</p>
        <?php
	}
	
	function wpfifc_optons_getting_started_section( $plugin_settings ){
		?>
		<section>
		<h3>Quick Start Guide</h3>
		<p>Once activated this plugin will add the ability to assign a featured image to WordPress categories, tags and custom taxonomies ( custom taxonomies are Pro version only)</p>
		<p>Visit the <a href="<?PHP echo admin_url( 'edit-tags.php?taxonomy=category' ); ?>">Category page here</a> in your dashboard to see the new featured images option for each category.</p>
		<p>You can assign a featured image to a category ( tag etc.. ) when editing it (ie it has to be created first then you edit it), simply edit the category and click 'Set featured image'.</p>
		<p>The plugin also supports setting a Featured Colour for each category or tag (Pro version only).</p>
		<p>If you would to access more features including working with Custom Taxonomies <a href="https://helpforwp.com/downloads/featured-images-for-categories/?utm_source=WordPress%20Admin&utm_medium=PluginSettingsPage&utm_campaign=Featured%20Images%20for%20Categories" target="_blank">visit our site to learn more about the Pro version of Featured Images for Categories.</a></p>
		<h4>Display featured images with a shortcode</h4>
		<p>To display featured images for categories or tags on a page or post in your WordPress site use this shortcode. All shortcode options are available in all versions of the plugin.</p>
		<p>
			[FeaturedImagesCat taxonomy='category' columns='3']
		</p>
		<p>There are a lot of options for shortcodes, visit the <i>More</i> tab here for details.</a></p>
		<h4>Display featured images with a widget</h4>
		<p>Visit the <a href="<?PHP echo admin_url( 'widgets.php' ); ?>">Widget section</a> of your WordPress Dashboard and you'll see two new widgets: "Featured Images for Categories" & "Featured Images for Categories - Term"</p>
		<p>
		These two widgets allow you to display a full list of categories or one specific term from a category. Visit the <a target="_blank" href="http://helpforwp.com/plugins/featured-images-for-categories/?utm_source=WordPress%20Admin&utm_medium=PluginSettingsPage&utm_campaign=Featured%20Images%20for%20Categories">plugin documentation page here</a> to view more details on the use of these widgets.</p> 
		</p>

		<?php
			//global $_wpfifc_messager;
			
			//$_wpfifc_messager->eddslum_plugin_option_page_update_center();
		?>
		</section>
    	<?php
	}
	
	function wpfifc_options_more_section( $plugin_settings ){
		?>
        <section>
	    <h3>Shortcode guide</h3>
	    <p>Use these shortcodes in your content to display groups of featured category images.</p>
	    
	    <p>The shortcode [FeaturedImagesCat] allows you to easily display featured images in your WordPress content</p>
	    
	    <pre>
	    [FeaturedImagesCat taxonomy='category' columns='3']    
	    </pre>
	    
	    <P>The shortcode accepts these arguments to further control the output:
		    
		    <ul style="list-style: square;padding-left: 20px;">
			    <li>taxonomy="the name of your taxonomy" (eg taxonomy='category') *</li>
			    <li>columns="number of columns to display" (eg columns='3')</li>
			    <li>imagesize="the name of a registered image size in your theme" (eg imagesize='medium')</li>
				<li>randomly="true or false" (eg randomly='true')</li>
				<li>showCatName="true or false" (eg showCatName='true')</li>
				<li>showCatDesc="true or false" (eg showCatDesc='true')</li>
				<li>include="comma separated list of post IDs" (eg include='2,4,6,23')</li>
				<li>childrenonly="comma separated list of post IDs" (eg childrenonly='1,4,65')</li>
				<li>hideempty="0 or 1" (eg hideempty='1' will not show categories with 0 posts)</li>
		    </ul>
		    
	    </P> 
	    <p>* WordPress Custom Taxonomies are support in the <a href="http://helpforwp.com/plugins/featured-images-for-categories/?utm_source=WordPress%20Admin&utm_medium=PluginSettingsPage&utm_campaign=Featured%20Images%20for%20Categories" target="_blank">Pro version of this plugin.</a>   
	    <p><a href="http://helpforwp.com/plugins/featured-images-for-categories/?utm_source=WordPress%20Admin&utm_medium=PluginSettingsPage&utm_campaign=Featured%20Images%20for%20Categories" target="_blank">Full documentation is available here on HelpForWP.com</a></P>   
        </section>
        <?php
	}
	
	function wpfifc_options_genesis_section( $plugin_settings ){
		if( !defined('PARENT_THEME_NAME') || PARENT_THEME_NAME != 'Genesis'){
			return;
		}
		$action = $_SERVER["REQUEST_URI"];
		?>
		<section>
        	<form action="<?php echo $action; ?>" method="POST" id="wpfifc_settings">
			<h4>Genesis Framework Settings</h4>
			<p>These settings are available because you're running a Genesis Framework child theme.</p>
			<table>
				<tr>
					<td>Display featured images on category, tag and custom taxonomy archive pages, choose where you would like to enable this feature.</td>
				</tr>
				<tr>
					<td>
				<?php
					$args = array( 'public' => true );
					$output = 'objects';
					$all_taxes = get_taxonomies( $args, $output );
					
					$saved_genesis_taxonomies = array();
					$saved_genesis_postion = 'left';
					if( $plugin_settings && is_array($plugin_settings) && count($plugin_settings) > 0 ){
						if( isset($plugin_settings['wpfifc_genesis_taxonomy']) ){
							$saved_genesis_taxonomies = $plugin_settings['wpfifc_genesis_taxonomy'];
						}
						if( isset($plugin_settings['wpfifc_genesis_position']) ){
							$saved_genesis_postion = $plugin_settings['wpfifc_genesis_position'];
						}
					}
					
					foreach( $all_taxes as $taxonomy ):
						if ( $taxonomy->name == 'nav_menu' || $taxonomy->name == 'post_format') {
							continue;
						} 
						if( $this->_wpfifc_CLASS_pro && $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
						}else{
							if ( $taxonomy->name != 'category' && $taxonomy->name != 'post_tag') {
								continue;
							}
						}
						$checked_str = '';
						if (is_array($saved_genesis_taxonomies) && count($saved_genesis_taxonomies) > 0){
							if(in_array($taxonomy->name, $saved_genesis_taxonomies)){
								$checked_str = ' checked="checked"';
							}
						}
					?>
					<label>
                    	<input type="checkbox" name="wpfifc_genesis_taxonomy[]" value="<?php echo $taxonomy->name ?>" <?php echo $checked_str; ?> />&nbsp;<?php echo $taxonomy->name ?>
                    </label>
					<br />
					<?php 
					endforeach;
					?>
					</td>
				</tr>
				<tr>
					<td><br />Set position</td>
				</tr>
				<tr>
					<td>
						<select name="wpfifc_genesis_position" id="wpfifc_genesis_position_id" style="width:150px;">
							<option value="left"<?php if ($saved_genesis_postion == 'left') echo ' selected="selected"' ?>>left</option>	
							<option value="right"<?php if ($saved_genesis_postion == 'right') echo ' selected="selected"' ?>>right</option>	
						</select>
				   </td>
				</tr>            
			</table>
            <p>
			<?php
			$nonce = wp_create_nonce( '-wpfifc-tab-genesis-save-settings-' );
			?>
			<input type="hidden" name="wpfifc_action" id="wpfifc_action_4_tab_genesis_form_ID" value="tab_genesis_save_settings" />
			<input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
            <input type="hidden" name="wpfifc_target_tab" value="wpfifc_tab_anchor_genesis" />
			<input type="submit" class="button-primary" value="Save Settings" id="wpfifc_tab_genesis_save_settings_btn_ID" />
		</p>
		</section>
		<?php
	}
	
	function wpfifc_action_tab_main_save_settings_fun(){
		if( !wp_verify_nonce( $_POST['nonce'], '-wpfifc-tab-main-save-settings-' ) ) {
			wp_die( 'Invalid nonce' );
		}
		if( !current_user_can( 'manage_options' ) ){
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		
		$wpfifc_image_padding = trim($_POST['wpfifc_image_padding']);
		$wpfifc_default_columns = trim($_POST['wpfifc_default_columns']);
		$wpfifc_default_size = trim($_POST['wpfifc_default_size']);
		
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option, '' );
		$plugin_settings['wpfifc_image_padding'] = $wpfifc_image_padding;
		$plugin_settings['wpfifc_default_columns'] = $wpfifc_default_columns;
		$plugin_settings['wpfifc_default_size'] = $wpfifc_default_size;
		$plugin_settings['taxonomies_to_dsiable_featured_images'] = array();
		if( isset($_POST['wpfifc_taxonomies_to_disable_featured_images']) ){
			$plugin_settings['taxonomies_to_dsiable_featured_images'] = $_POST['wpfifc_taxonomies_to_disable_featured_images'];
		}
		$plugin_settings['show_featured_images_on_taxonomy_management_pages'] = 'yes';
		if( isset($_POST['wpfifc_show_featured_images_on_taxonomy_management_pages']) ){
			$plugin_settings['show_featured_images_on_taxonomy_management_pages'] = $_POST['wpfifc_show_featured_images_on_taxonomy_management_pages'];
		}
		
		$plugin_settings['show_featured_colour_on_taxonomy_management_pages'] = 'yes';
		if( isset($_POST['wpfifc_show_featured_colour_on_taxonomy_management_pages']) ){
			$plugin_settings['show_featured_colour_on_taxonomy_management_pages'] = $_POST['wpfifc_show_featured_colour_on_taxonomy_management_pages'];
		}
		
		update_option( $this->_wpfifc_plugin_settings_option, $plugin_settings );
	}
	
	function wpfifc_action_tab_genesis_save_settings_fun(){
		if( !wp_verify_nonce( $_POST['nonce'], '-wpfifc-tab-genesis-save-settings-' ) ) {
			wp_die( 'Invalid nonce' );
		}
		if( !current_user_can( 'manage_options' ) ){
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		$main_tab_settings['wpfifc_genesis_taxonomy'] = array();
		if( isset($_POST['wpfifc_genesis_taxonomy']) ){
			$main_tab_settings['wpfifc_genesis_taxonomy'] = $_POST['wpfifc_genesis_taxonomy'];
		}
		$main_tab_settings['wpfifc_genesis_position'] = $_POST['wpfifc_genesis_position'];
		
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option, '' );
		$plugin_settings['wpfifc_genesis_taxonomy'] = $main_tab_settings['wpfifc_genesis_taxonomy'];
		$plugin_settings['wpfifc_genesis_position'] = $main_tab_settings['wpfifc_genesis_position'];
		
		update_option( $this->_wpfifc_plugin_settings_option, $plugin_settings );
	}
}


