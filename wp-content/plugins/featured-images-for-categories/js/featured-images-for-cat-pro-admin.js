jQuery(document).ready( function($) {
	var uploader_frame;
	
	$('#wpfifc_set_post_thumbnail').click(function( event ){
		event.preventDefault();
		
		if ( uploader_frame ) {
			uploader_frame.open();
			return;
		}
		 
		uploader_frame = wp.media.frames.uploader_frame = wp.media({
			title: "Set featured image",
			button: { text: 'Set featured image' },
			multiple: false
		});
		// open
		uploader_frame.on('open',function() {
			var attachment_id = $("#wpfifc_taxonomies_edit_attachment_ID_id").val();
			if( attachment_id < 1 ){
				return;
			}
			// set selection
			var selection	=	_media.frame.state().get('selection'),
				attachment	=	wp.media.attachment( id );

			// to fetch or not to fetch
			if( $.isEmptyObject(attachment.changed) )
			{
				attachment.fetch();
			}
			selection.add( attachment );
		});
			
		uploader_frame.on( 'select', function() {
			attachment = uploader_frame.state().get('selection').first().toJSON();
			// Do something with attachment.id and/or attachment.url here
			//alert(attachment.url);
			//alert( attachment.id );
			wpfifc_set_thumbnail_to_category( attachment.id );
		});
		
		uploader_frame.on( 'close', function() {
			$("#bsk_dd_metabox_upload_file_ajax_loader_ID").css("display", "none");
			$("#bsk_dd_metabox_upload_file_ID").css("display", "inline-block");
		});
		 
		// Finally, open the modal
		uploader_frame.open();
	});
	
	function wpfifc_set_thumbnail_to_category( thumbnail_id_to_set ){
		if( $('#wpfifc_taxonomies_edit_term_ID_id').length > 0 ){
			term_ID = $('#wpfifc_taxonomies_edit_term_ID_id').val();
		}
		if( thumbnail_id_to_set < 1 || term_ID < 1 ){
			return;
		}
		$("#wpfifc_taxonomies_edit_ajax_loader_ID").css("display", "inline-block");
		var data = { action: 'wpfifc_set_image', 
					 thumbnail_id: thumbnail_id_to_set,
					 term_id: term_ID,
				   };
		$.post(ajaxurl, data, function(response) {
			$("#wpfifc_taxonomies_edit_ajax_loader_ID").css("display", "none");
			if( response.indexOf('ERROR') != -1 ){
				alert( response );
				return fasle;
			}
			$("#wpfifc_taxonomies_edit_attachment_ID_id").val( thumbnail_id_to_set );
			$("#wpfifc_set_post_thumbnail").html(response);
			$("#wpfifc_remove_post_thumbnail").css("display", "inline-block");
		});
	}
	
	$("#wpfifc_remove_post_thumbnail").click(function(){
		if( $('#wpfifc_taxonomies_edit_term_ID_id').length > 0 ){
			term_ID = $('#wpfifc_taxonomies_edit_term_ID_id').val();
		}
		if( term_ID < 1 ){
			$("#wpfifc_taxonomies_edit_attachment_ID_id").val( 0 );
			$("#wpfifc_set_post_thumbnail").html('Set featured image');
			$("#wpfifc_remove_post_thumbnail").css("display", "none");
			
			return fasle;
		}
		$("#wpfifc_taxonomies_edit_ajax_loader_ID").css("display", "inline-block");
		var data = { action: 'wpfifc_set_image', 
					 thumbnail_id: -1,
					 term_id: term_ID,
				   };
		$.post(ajaxurl, data, function(response) {
			$("#wpfifc_taxonomies_edit_ajax_loader_ID").css("display", "none");
			if( response.indexOf('ERROR') != -1 ){
				alert( response );
				return fasle;
			}
			$("#wpfifc_taxonomies_edit_attachment_ID_id").val( 0 );
			$("#wpfifc_set_post_thumbnail").html('Set featured image');
			$("#wpfifc_remove_post_thumbnail").css("display", "none");
			
			return fasle;
		});
	});
	
	$(".wpfifc_admin_display_images_randomly").live("click", function(){
		var radio_value = $(this).val();
		if( radio_value == 'Yes' ){
			$(".wpfifc_admin_orderby").attr('disabled', 'disabled');
			$(".wpfifc_admin_order").attr('disabled', 'disabled');
		}else{
			$(".wpfifc_admin_orderby").removeAttr('disabled');
			$(".wpfifc_admin_order").removeAttr('disabled');
		}
	});
	
	/* tab switch */
	$(document).on( 'click', '#wpfifc_options_form_ID .nav-tab-wrapper a', function() {
		
		//alert( $(this).index() );
		$('#wpfifc_options_tab_contents section').hide();
		$('#wpfifc_options_tab_contents section').eq($(this).index()).show();
		
		$(".nav-tab").removeClass( "nav-tab-active" );
		$(this).addClass( "nav-tab-active" );
		
		return false;
	})

	if( $("#wpfifc_options_page_target_tab_ID").length > 0 ){
		var target = $("#wpfifc_options_page_target_tab_ID").val();
		
		if( target ){
			$("#" + target).click();
		}
	}
	
	if( $(".wpfifc-colour-field").length > 0 ){
	$( '.wpfifc-colour-field' ).wpColorPicker();
	}
});

function wpfifc_taxonomy_select_change( object ) {
	var select_id = object.id;
	var select_name = object.name;
	var instance_prefix = select_id.replace('wpfifc_term_widget_taxonomy', '');
	var option_val = jQuery("#" + select_id).val();
	var option_txt = jQuery("#" + select_id + " option:selected").text();
	var container_id = instance_prefix + 'wpfifc_term_widget_taxonomy_term';
	
	if (option_val < 1){
		return;
	}

	//use ajax to get all sorted
	var data = {
		action: 'wpfifcgetterms',
		taxonomy: option_val,
		prefix: instance_prefix
	};
	// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
	jQuery.post(ajaxurl, data, function(response) {
		jQuery("#" + container_id).html(response);
	});
}

