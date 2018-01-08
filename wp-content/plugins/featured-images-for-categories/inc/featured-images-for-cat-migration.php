<?php

class WPFeaturedImgCategoriesMigrationPro{
	
	var $_wpfifc_ajax_loader_image_url = '';
	var $_wpfifc_plugin_settings_option = '';
	var $_wpfifc_plugin_title = '';
	var $_wpfifc_plugin_folder = '';
	var $_wpfifc_plugin_page_slug = '';
	var $_database_option_name = '';
	var $_database_version = '';
	var $_database_term_meta_converted_opiton_name = '';
	
	public function __construct( $args ) {
		$this->_wpfifc_ajax_loader_image_url = $args['ajax_loader_img_url'];
		$this->_wpfifc_plugin_settings_option = $args['plugin_settings_option'];
		$this->_wpfifc_plugin_title = $args['wpfifc_plugin_title'];
		$this->_wpfifc_plugin_folder = $args['wpfifc_plugin_folder'];
		$this->_wpfifc_plugin_page_slug = $args['wpfifc_plugin_page_slug'];
		$this->_database_option_name = $args['database_option_name'];
		$this->_database_version = $args['database_version'];
		$this->_database_term_meta_converted_opiton_name = $args['term_meta_converted_option_name'];
		
		$dababase_version = get_option( $this->_database_option_name, 0 );
		if( $dababase_version < 200 ){
			add_action( 'admin_notices', array($this, 'wpfifc_data_migration_notice_fun') );
		}
		
		add_action( 'wpfifc_action_term_data_convert_to_meta', array($this, 'wpfifc_action_term_data_convert_to_meta_fun') );
		add_action( 'wpfifc_action_term_data_delete', array($this, 'wpfifc_action_term_data_delete_fun') );
	}
	
	function wpfifc_data_migration_notice_fun() {
		global $wp_version, $wpdb;
		
		if( version_compare( $wp_version, '4.4', '<' ) ){
			return;
		}
		
		//check if there is old data saved
		$sql = 'SELECT `option_id`, `option_name`, `option_value` FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%_thumbnail_id_"';
		$results = $wpdb->get_results( $sql );
		if( !$results || count($results) < 1 ){
			update_option( $this->_database_option_name, $this->_database_version );
			return;
		}
		
		//don't show at itself
		if( isset($_REQUEST['page']) && $_REQUEST['page'] == $this->_wpfifc_plugin_page_slug &&
			isset($_REQUEST['view']) && $_REQUEST['view'] == 'migration' ){
			
			return;
		}
		$link = admin_url( 'options-general.php?page='.$this->_wpfifc_plugin_page_slug.'&view=migration' );
		?>
		<div class="updated">
			<p>Featured Images for categories needs to <a href="<?php echo $link; ?>">update how its data is stored</a></p>
		</div>
		<?php
	}
	
	function wpfifc_data_migration_view(){
		global $wp_version, $wpdb;
		
		if( version_compare( $wp_version, '4.4', '<' ) ){
			return;
		}
		
		//check if there is old data saved
		$sql = 'SELECT `option_id`, `option_name`, `option_value` FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%_thumbnail_id_"';
		$results = $wpdb->get_results( $sql );
		if( !$results || count($results) < 1 ){
			update_option( $this->_database_option_name, $this->_database_version );
			return;
		}
		
		$action = $_SERVER["REQUEST_URI"];
		$database_term_data_converted_to_meta_opiton_name = get_option( $this->_database_term_meta_converted_opiton_name, '' );
		
		$wpfifc_action = 'term_data_convert_to_meta';
		$button_value = 'Start Migration';
		$is_delete_view = false;
		if( $database_term_data_converted_to_meta_opiton_name ){
			$wpfifc_action = 'term_data_delete';
			$button_value = 'Delete legacy data';
			$is_delete_view = true;
			
			$action = remove_query_arg( 'view', $action);
		}
	?>
		<div class="wrap" id="wpfifc_options_form_ID">
        	<form action="<?php echo $action; ?>" method="POST" id="wpfifc_settings">
			<img src="<?PHP echo plugins_url().'/'.$this->_wpfifc_plugin_folder; ?>/images/help-for-wordpress-small.png" align="left" />
			<h2><?php echo $this->_wpfifc_plugin_title; ?></h2>
            <h3 style="margin-top:40px;">Featured Images for categories data migration</h3>
            <?php if( $is_delete_view ){ ?>
            <p style="margin-top:20px;font-size:12px;font-weight:bold;">Your data has been migrated, you may delete legacy data after your testing.</p>
            <?php }else{ ?>
            <p style="margin-top:20px;font-size:12px;font-weight:bold;">Please backup your WordPress database before running this update</p>
            <p>WordPress 4.4 and newer supports taxonomy meta data. This migration process will update your WordPress database, to store featured image data in this new format.</p>
            <?php } ?>
            <p style="margin-top:20px;">
                <?php
                $nonce = wp_create_nonce( '-wpfifc-data-migration-upgrade-nonce-' );
                ?>
                <input type="hidden" name="wpfifc_action" value="<?php echo $wpfifc_action; ?>" />
                <input type="hidden" name="nonce" value="<?php echo $nonce; ?>" />
                <input type="submit" class="button-primary" value="<?php echo $button_value; ?>" id="wpfifc_data_migration_upgrade_btn_ID" />
            </p>
            </form>
		</div>
	<?php
	}
	
	function wpfifc_action_term_data_convert_to_meta_fun(){
		global $wpdb;
		
		if( !wp_verify_nonce( $_POST['nonce'], '-wpfifc-data-migration-upgrade-nonce-' ) ) {
			wp_die( 'Invalid nonce' );
		}
		if( !current_user_can( 'manage_options' ) ){
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		//check if there is old data saved
		$sql = 'SELECT `option_id`, `option_name`, `option_value` FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%_thumbnail_id_"';
		$results = $wpdb->get_results( $sql );
		if( !$results || count($results) < 1 ){
			return;
		}
		
		foreach( $results as $option_val_obj ){
			$term_id_str = str_replace( '_wpfifc_taxonomy_term_', '', $option_val_obj->option_name );
			$term_id_str = str_replace( '_thumbnail_id_', '', $term_id_str );
			$term_id = $term_id_str + 0;
			
			//set term meta
			update_term_meta( $term_id, 'wpfifc_featured_image', $option_val_obj->option_value + 0 );
		}
		
		update_option( $this->_database_term_meta_converted_opiton_name, 'yes' );
	}
	
	function wpfifc_action_term_data_delete_fun(){
		global $wpdb;
		
		if( !wp_verify_nonce( $_POST['nonce'], '-wpfifc-data-migration-upgrade-nonce-' ) ) {
			wp_die( 'Invalid nonce' );
		}
		if( !current_user_can( 'manage_options' ) ){
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		$sql = 'DELETE FROM `'.$wpdb->options.'` WHERE `option_name` LIKE "_wpfifc_taxonomy_term_%_thumbnail_id_"';
		$wpdb->query( $sql );
		
		update_option( $this->_database_option_name, $this->_database_version );
		delete_option( $this->_database_term_meta_converted_opiton_name );
	}
	
}


