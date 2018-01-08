<?php

/*
Plugin Name: Featured Images for Categories
Plugin URI: https://helpforwp.com/downloads/featured-images-for-categories/
Description: Assign a featured image to a WordPress category, tag or custom taxonomy then use these featured images via a widget area or a shortcode.
Version: 2.1.1
Author: HelpForWP
Author URI: http://HelpForWP.com

------------------------------------------------------------------------

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, 
or any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

require_once( 'featured-images-for-categories-api.php' );

global $_wpfifc_version;

$_wpfifc_version = '2.1.1';

class WPFeaturedImgCategories {
	
	var $_wpfifc_plugin_title = 'Featured Image for Categories';
	var $_wpfifc_plugin_folder = 'featured-images-for-categories';
	var $_wpfifc_plugin_page_slug = 'wpfifc_options';
	var $_wpfifc_enqueue_scripts_hook_suffix = 'settings_page_wpfifc_options';
	var $_database_version = 200;
	var $_database_option_name = '_wpfifc_taxonomy_term_database_version_';
	var $_database_term_meta_converted_opiton_name = '_wpfifc_taxonomy_term_data_converted_to_meta_';
	var $_wpfifc_ajax_loader_img_url = '';
	var $_wpfifc_plugin_settings_option = '_wpfific_plugin_settings_';
	
	var $_wpfifc_CLASS_pro = NULL;
	var $_wpfifc_CLASS_option = NULL;
	var $_wpfifc_CLASS_front = NULL;
	var $_wpfifc_CLASS_term_featured_image = NULL;
	var $_wpfifc_CLASS_term_colour = NULL;

	public function __construct() {
		
		require_once('inc/featured-images-for-categories-widget.php');
		require_once('inc/featured-images-for-categories-term-widget.php');
			
		$this->_wpfifc_ajax_loader_img_url = plugin_dir_url( __FILE__ ).'images/ajax-loader.gif';
		
		register_activation_hook( __FILE__, array($this, 'wpfifc_activate' ) );
		register_deactivation_hook( __FILE__, array($this, 'wpfifc_deactivate' ) );
		register_uninstall_hook( __FILE__,  'WPFeaturedImgCategories::wpfifc_remove_option' );
		
		//Plugin update actions
		add_action( 'init', array($this, 'wpfifc_post_action') );
		
		/*
		 * Here need to update the plugin setting
		 */
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option, '' );
		if( $plugin_settings == "" ){
			$plugin_settings = array();
			
			$plugin_settings['wpfifc_image_padding'] = 2;
			$plugin_settings['wpfifc_default_columns'] = 3;
			$plugin_settings['wpfifc_default_size'] = 'thumbnail';
			$plugin_settings['wpfifc_genesis_taxonomy'] = array();
			$plugin_settings['wpfifc_default_size'] = 'left';
			
			$wpfifc_image_padding = get_option( 'wpfifc_image_padding' );
			if( $wpfifc_image_padding ){
				$plugin_settings['wpfifc_image_padding'] = $wpfifc_image_padding;
			}
			$wpfifc_default_columns = get_option( 'wpfifc_default_columns' );
			if( $wpfifc_default_columns ){
				$plugin_settings['wpfifc_default_columns'] = $wpfifc_default_columns;
			}
			$wpfifc_default_size = get_option( 'wpfifc_default_size' );
			if( $wpfifc_default_size ){
				$plugin_settings['wpfifc_default_size'] = $wpfifc_default_size;
			}
			$wpfifc_genesis_taxonomy = get_option( 'wpfifc_genesis_taxonomy' );
			if( $wpfifc_genesis_taxonomy ){
				$plugin_settings['wpfifc_genesis_taxonomy'] = $wpfifc_genesis_taxonomy;
			}
			$wpfifc_genesis_position = get_option( 'wpfifc_genesis_position' );
			if( $wpfifc_genesis_position ){
				$plugin_settings['wpfifc_genesis_position'] = $wpfifc_genesis_position;
			}

			update_option( $this->_wpfifc_plugin_settings_option, $plugin_settings );
		}
		
		/*
		 * Check if user still user a version lower than 132
		 * If so upgrade to 132 databse format first
		*/
		$this->wpfifc_upgrade_database_132();

		//init modules
		if( file_exists( dirname(__FILE__).'/pro/featured-images-for-cat-pro.php') ){
			require_once( 'pro/featured-images-for-cat-pro.php' );
		}
		require_once( 'inc/featured-images-for-cat-options.php' );
		require_once( 'inc/featured-images-for-categories-front.php' );
		require_once( 'inc/featured-images-for-cat-term-featured-image.php' );
		if( file_exists( dirname(__FILE__).'/pro/featured-images-for-cat-term-colour.php') ){
			require_once( 'pro/featured-images-for-cat-term-colour.php' );
		}
		
		global $_wpfifc_version;
		$init_arg = array();
		$init_arg['ajax_loader_img_url'] = $this->_wpfifc_ajax_loader_img_url;
		$init_arg['plugin_settings_option'] = $this->_wpfifc_plugin_settings_option;
		$init_arg['database_option_name'] = $this->_database_option_name;
		$init_arg['database_version'] = $this->_database_version;
		$init_arg['term_meta_converted_option_name'] = $this->_database_term_meta_converted_opiton_name;
		$init_arg['wpfifc_version'] = $_wpfifc_version;
		
		if( class_exists( 'WPFeaturedImgCategoriesPro' ) ){
			$this->_wpfifc_CLASS_pro = new WPFeaturedImgCategoriesPro();
			$this->_wpfifc_plugin_title = $this->_wpfifc_CLASS_pro->_wpfifc_plugin_title;
			$this->_wpfifc_plugin_folder = $this->_wpfifc_CLASS_pro->_wpfifc_plugin_folder;
			$this->_wpfifc_plugin_page_slug = $this->_wpfifc_CLASS_pro->_wpfifc_plugin_page_slug;
			$this->_wpfifc_enqueue_scripts_hook_suffix = $this->_wpfifc_CLASS_pro->_wpfifc_enqueue_scripts_hook_suffix;
		}
		$init_arg['wpfifc_pro_instance'] = $this->_wpfifc_CLASS_pro;
		$init_arg['wpfifc_plugin_title'] = $this->_wpfifc_plugin_title;
		$init_arg['wpfifc_plugin_folder'] = $this->_wpfifc_plugin_folder;
		$init_arg['wpfifc_plugin_page_slug'] = $this->_wpfifc_plugin_page_slug;
		$init_arg['wpfifc_enqueue_scripts_hook_suffix'] = $this->_wpfifc_enqueue_scripts_hook_suffix;
		
		$this->_wpfifc_CLASS_option = new WPFeaturedImgCategoriesOptionsPro( $init_arg );
		$this->_wpfifc_CLASS_front = new WPFeaturedImgCategoriesFront( $init_arg );
		$this->_wpfifc_CLASS_term_featured_image = new WPFeaturedImgCategoriesTermFeaturedImage( $init_arg );
		if( $this->_wpfifc_CLASS_pro ){
			$this->_wpfifc_CLASS_term_colour = new WPFeaturedImgCategoriesTermColour( $init_arg );
			if( $this->_wpfifc_CLASS_pro->wpfifc_is_pro_valid() ){
				add_action( 'widgets_init', create_function( '', 'register_widget( "WPFeaturedImgCategoriesWidget" );' ) );
				add_action( 'widgets_init', create_function( '', 'register_widget( "WPFeaturedImgCategoriesTermWidget" );' ) );
			}
		}else{
			//free version
			add_action( 'widgets_init', create_function( '', 'register_widget( "WPFeaturedImgCategoriesWidget" );' ) );
			add_action( 'widgets_init', create_function( '', 'register_widget( "WPFeaturedImgCategoriesTermWidget" );' ) );
		}
	}

	
	function wpfifc_activate() {
		
		$plugin_settings = get_option( $this->_wpfifc_plugin_settings_option, '' );
		if( $plugin_settings == "" ){
			$plugin_settings = array();
			$plugin_settings['wpfifc_image_padding'] = 2;
			$plugin_settings['wpfifc_default_columns'] = 3;
			$plugin_settings['wpfifc_default_size'] = 'thumbnail';
			$plugin_settings['wpfifc_genesis_taxonomy'] = array();
			$plugin_settings['wpfifc_default_size'] = 'left';
	
			update_option( $this->_wpfifc_plugin_settings_option, $plugin_settings );
		}
		
		update_option( $this->_database_option_name, 132 );
	}
	
	
	function wpfifc_deactivate(){
		
	}
	
	function wpfifc_post_action(){
		if( isset( $_POST['wpfifc_action'] ) && strlen($_POST['wpfifc_action']) > 0 ) {
			do_action( 'wpfifc_action_' . $_POST['wpfifc_action'], $_POST );
		}
	}

	function wpfifc_remove_option() {
		/*delete_option( '_wpfific_plugin_settings_' );
		
	 	delete_option('wpfifc_post_ids_save_image');
	 
		delete_option('wpfifc_image_padding');
		delete_option('wpfifc_default_columns');	
		delete_option('wpfifc_default_size');
		delete_option('wpfifc_genesis_taxonomy');
		delete_option('wpfifc_genesis_position');
		
		//widget option
		delete_option('widget_wpfifc_widget');
		*/
		delete_option('wpfifc_license_key');
		delete_option('wpfifc_license_key_status');
		
		
		return;
	}

	function wpfifc_upgrade_database_132(){
		global $wpdb;
		
		$exist_database_version = get_option( $this->_database_option_name, 0 );
		if( $exist_database_version >= 132 ){
			return;
		}
		
		//convert old data format to new and save database version
		$sql = 'SELECT `option_id`, `option_name`, `option_value` FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%"';
		$results = $wpdb->get_results( $sql );
		if( !$results || count($results) < 1 ){
			update_option( $this->_database_option_name, 132 );
			return;
		}
		$old_option_ids = array();
		foreach( $results as $record ){
			$old_option_ids[] = $record->option_id;
			$term_id = str_replace('_wpfifc_taxonomy_term_', '', $record->option_name);
			$term_id = intval($term_id);
			$post_ID = $record->option_value;
			//check if the post still in database
			$sql_post = 'SELECT * FROM `'.$wpdb->posts.'` WHERE `ID` = '.$post_ID;
			if( !$wpdb->get_results( $sql_post ) ){
				continue;
			}
			//get thumbnail id
			$sql_postmeta = 'SELECT * FROM `'.$wpdb->postmeta.'` WHERE `post_id` = '.$post_ID.' AND `meta_key` = "_thumbnail_id"';
			$thumbnail_id_obj = $wpdb->get_results( $sql_postmeta );
			if( !$thumbnail_id_obj ){
				continue;
			}
			$thumbnail_id = $thumbnail_id_obj[0]->meta_value;
			$thumbnail_id = intval($thumbnail_id);
			if( !$thumbnail_id ){
				continue;
			}
			//write new option
			$new_option = '_wpfifc_taxonomy_term_'.$term_id.'_thumbnail_id_';
			update_option( $new_option, $thumbnail_id );
		}
		
		//remove old options
		$optons_ids_str = implode( ',', $old_option_ids );
		$optons_ids_str = trim( $optons_ids_str );
		$sql = 'DELETE FROM '.$wpdb->options.' WHERE option_id IN ('.$optons_ids_str.')';
		$wpdb->query( $sql );
		
		update_option( $this->_database_option_name, 132 );
	}
}

$wpfifc_pro_instance = new WPFeaturedImgCategories();


