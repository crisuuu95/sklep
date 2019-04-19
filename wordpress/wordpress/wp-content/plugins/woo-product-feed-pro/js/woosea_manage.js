jQuery(document).ready(function($) {
	var project_hash = null;
	var project_status = null;
	var get_value = null;
	var tab_value = null;

	// make sure to only check the feed status on the woosea_manage_feed page
	url = new URL(window.location.href);
	if (url.searchParams.get('page')) {
		get_value = url.searchParams.get('page');
 	}
	if (url.searchParams.get('tab')) {
		tab_value = url.searchParams.get('tab');
	}    

  	if (get_value == 'woosea_manage_feed') {
		$(document).on('ready',function(){
			myInterval = setInterval(woosea_check_perc,3000);
		});
	}

	$(".dismiss-review-notification").click(function(){
      
	        jQuery.ajax({
                	method: "POST",
                        url: ajaxurl,
                        data: { 'action': 'woosea_review_notification' }
                })

		$(".review-notification").remove();	

	});

	$(".notice-dismiss").click(function(){
		$(".license-notification").remove();	

	        jQuery.ajax({
                	method: "POST",
                        url: ajaxurl,
                        data: { 'action': 'woosea_license_notification' }
                })
	});


   	$("td[colspan=8]").find("div").parents("tr").hide();

	$('.checkbox-field').change(function(index, obj){

		if(get_value == 'woosea_manage_settings' && tab_value == 'woosea_manage_attributes'){
			var attribute_value = $(this).val();
			var attribute_name = $(this).attr('name');
			var attribute_status = $(this).prop("checked");

	                jQuery.ajax({
 		               	method: "POST",
               	         	url: ajaxurl,
                        	data: { 'action': 'woosea_add_attributes', 'attribute_name': attribute_name, 'attribute_value': attribute_value, 'active': attribute_status }
                	})
		} else if (get_value == 'woosea_manage_feed') {
    			project_hash = $(this).val();
			project_status = $(this).prop("checked");

	                jQuery.ajax({
 		               	method: "POST",
               	         	url: ajaxurl,
                        	data: { 'action': 'woosea_project_status', 'project_hash': project_hash, 'active': project_status }
                	})

         		$("table tbody").find('input[name="manage_record"]').each(function(){
				var hash = this.value;
				if(hash == project_hash){
					if (project_status == false){
						$(this).parents("tr").addClass('strikethrough');
					} else {
						$(this).parents("tr").removeClass('strikethrough');
					}
                		}
            		});
		} else {
			// Do nothing, waste of resources
		}
	});



	// Check if user would like to enable WPML support
	$('#add_wpml_support').on('change', function(){ // on change of state
   		if(this.checked){

			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_wpml', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_wpml', 'status': "off" }
                	})
		}
	})	

	// Check if user would like to enable Aelia Currency Switcher support
	$('#add_aelia_support').on('change', function(){ // on change of state
   		if(this.checked){

			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_aelia', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_aelia', 'status': "off" }
                	})
		}
	})	

	// Check if user would like to use mother image for variations
	$('#add_mother_image').on('change', function(){ // on change of state
   		if(this.checked){

			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_mother_image', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_mother_image', 'status': "off" }
                	})
		}
	})	

	// Check if user would like to enable Dynamic Remarketing
	$('#add_remarketing').on('change', function(){ // on change of state
   		if(this.checked){

			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_remarketing', 'status': "on" }
                	})
			.done(function( data ) {
				$('#remarketing').after('<tr id="adwords_conversion_id"><td colspan="2"><span>Insert your Dynamic Remarketing Conversion tracking ID:</span>&nbsp;<input type="text" class="input-field-medium" id="adwords_conv_id" name="adwords_conv_id">&nbsp;<input type="submit" id="save_conversion_id" value="Save"></td></tr>');	
			})
                	.fail(function( data ) {
                        	console.log('Failed AJAX Call :( /// Return Data: ' + data);
                	});	
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_remarketing', 'status': "off" }
                	})
			.done(function( data ) {
				$('#adwords_conversion_id').remove();	
			})
                	.fail(function( data ) {
                        	console.log('Failed AJAX Call :( /// Return Data: ' + data);
                	});	
		}
	})	

        // Add a mapping row to the table for field mappings
        jQuery("#save_conversion_id").click(function(){
                var adwords_conversion_id = $('#adwords_conv_id').val();
	        var re = /^[0-9]*$/;
                
		var woosea_valid_conversion_id=re.test(adwords_conversion_id);
                // Check for allowed characters
                if (!woosea_valid_conversion_id){
                        $('.notice').replaceWith("<div class='notice notice-error woosea-notice-conversion is-dismissible'><p>Sorry, only numbers are allowed for your Dynamic Remarketing Conversion tracking ID.</p></div>");
                        // Disable submit button too
                        $('#save_conversion_id').attr('disabled',true);
                } else {
                        $('.woosea-notice-conversion').remove();
                        $('#save_conversion_id').attr('disabled',false);

			// Now we need to save the conversion ID so we can use it in the dynamic remarketing JS
                        jQuery.ajax({
                                method: "POST",
                                url: ajaxurl,
                                data: { 'action': 'woosea_save_adwords_conversion_id', 'adwords_conversion_id': adwords_conversion_id }
                        })
                }	
	})


	// Check if user would like to add attributes
	$('#add_identifiers').on('change', function(){ // on change of state
   		if(this.checked){
			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_identifiers', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_add_identifiers', 'status': "off" }
                	})
		}
	})	


	// When an Elite feature is clicked and the license is not valid show a pop-up
	$('#json_option').on('click', function(){ // on change of state
		var license_active = $(this).closest('tr').attr("class");
		console.log(license_active);
		if(license_active == "disabled"){ 
			var popup_dialog = alert("We are sorry, The JSON LD WooCommerce bug fix is an Elite feature of this plugin. Please visit AdTribes.io to acquire a license and enable this option.");
		}
	})	

	$('#structured_vat_option').on('click', function(){ // on change of state
		var license_active = $(this).closest('tr').attr("class");
		console.log(license_active);
		if(license_active == "disabled"){ 
			var popup_dialog = alert("We are sorry, removing the VAT from the structured data prices is an Elite feature of this plugin. Please visit AdTribes.io to acquire a license and enable this option.");
		}
	})	

	$('#wpml_option').on('click', function(){ // on change of state
		var license_active = $(this).closest('tr').attr("class");
		if(license_active == "disabled"){ 
			var popup_dialog = alert("We are sorry, this featue is locked. WPML support is an Elite feature of this plugin. Please visit AdTribes.io to acquire a license and enable this option.");
		}
	})	

	$('#aelia_option').on('click', function(){ // on change of state
		var license_active = $(this).closest('tr').attr("class");
		if(license_active == "disabled"){ 
			var popup_dialog = alert("We are sorry, this feature is locked. The Aelia currency support is an Elite feature of this plugin. Please visit AdTribes.io to acquire a license and enable this option.");
		}
	})	

	$('#identifier_option').on('click', function(){ // on change of state
		var license_active = $(this).closest('tr').attr("class");
		if(license_active == "disabled"){ 
			var popup_dialog = alert("We are sorry, adding the extra fields (such as brand, GTIN, MPN etc) is an Elite feature of this plugin. Please visit AdTribes.io to acquire a license and enable this option.");
		}
	})	
	
	// Check if user would like to fix the WooCommerce structured data bug
	$('#fix_json_ld').on('change', function(){ // on change of state

   		if(this.checked){
			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_enable_structured_data', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_enable_structured_data', 'status': "off" }
                	})
		}
	})	

	// Check if user wants to display structured data prices excluding VAT
	$('#no_structured_vat').on('change', function(){ // on change of state

   		if(this.checked){
			// Checkbox is on
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_structured_vat', 'status': "on" }
                	})
		} else {
			// Checkbox is off
                	jQuery.ajax({
                        	method: "POST",
                        	url: ajaxurl,
                        	data: { 'action': 'woosea_structured_vat', 'status': "off" }
                	})
		}
	})	


	$(".actions").delegate("span", "click", function() {
   		var id=$(this).attr('id');
		var idsplit = id.split('_');
		var project_hash = idsplit[1];
		var action = idsplit[0];		

		if (action == "gear"){
    			$("tr").not(':first').click(
				function(event) {
        				var $target = $(event.target);
        				$target.closest("tr").next().find("div").parents("tr").slideDown( "slow" );                
    				}
			);
		}

		if (action == "copy"){

			var popup_dialog = confirm("Are you sure you want to copy this feed?");
			if (popup_dialog == true){
       			jQuery.ajax({
                			method: "POST",
                       	 		url: ajaxurl,
                        		data: { 'action': 'woosea_project_copy', 'project_hash': project_hash }
                		})

                        	.done(function( data ) {
					data = JSON.parse( data );
					$('#woosea_main_table').append('<tr class><td>&nbsp;</td><td colspan="5"><span>The plugin is creating a new product feed now: <b><i>"' + data.projectname + '"</i></b>. Please refresh your browser to manage the copied product feed project.</span></span></td></tr>');
				})
            		}
		}


		if (action == "trash"){

			var popup_dialog = confirm("Are you sure you want to delete this feed?");
			if (popup_dialog == true){
        			jQuery.ajax({
                			method: "POST",
                       	 		url: ajaxurl,
                        		data: { 'action': 'woosea_project_delete', 'project_hash': project_hash }
                		})
	
            			$("table tbody").find('input[name="manage_record"]').each(function(){
					var hash = this.value;
					if(hash == project_hash){
                    				$(this).parents("tr").remove();
                			}
            			});
            		}
		}

		if(action == "cancel"){

			var popup_dialog = confirm("Are you sure you want to cancel processing the feed?");
			if (popup_dialog == true){
        			jQuery.ajax({
                			method: "POST",
                       	 		url: ajaxurl,
                        		data: { 'action': 'woosea_project_cancel', 'project_hash': project_hash }
                		})
	
				// Replace status of project to stop processing
			        $("table tbody").find('input[name="manage_record"]').each(function(){
					var hash = this.value;
					if(hash == project_hash){
						$(".woo-product-feed-pro-blink_"+hash).text(function () {
                                       			$(this).addClass('woo-product-feed-pro-blink_me');
    							return $(this).text().replace("ready", "stop processing"); 
						});	
					}
            			});
			}
		}

		if (action == "refresh"){
		
			var popup_dialog = confirm("Are you sure you want to refresh the product feed?");
			if (popup_dialog == true){
        			jQuery.ajax({
                			method: "POST",
                       	 		url: ajaxurl,
                        		data: { 'action': 'woosea_project_refresh', 'project_hash': project_hash }
                		})

				// Replace status of project to processing
			        $("table tbody").find('input[name="manage_record"]').each(function(){
					var hash = this.value;
					if(hash == project_hash){
						$(".woo-product-feed-pro-blink_off_"+hash).text(function () {
                                        		$(this).addClass('woo-product-feed-pro-blink_me');
							myInterval = setInterval(woosea_check_perc,500);
							return $(this).text().replace("ready", "processing (0%)"); 
						});	
					}
            			});
			}
		}
	});

	function woosea_check_perc(){
  		// Check if we need to UP the processing percentage 
		$("table tbody").find('input[name="manage_record"]').each(function(){
       	        	var hash = this.value;

			jQuery.ajax({
                		method: "POST",
                      	 	 url: ajaxurl,
                       		 data: { 'action': 'woosea_project_processing_status', 'project_hash': hash }
               		})

             	        .done(function( data ) {
                        	data = JSON.parse( data );

				if(data.proc_perc < 100){
					return $("#woosea_proc_"+hash).text("processing ("+data.proc_perc+"%)");
				} else if(data.proc_perc == 100){
					clearInterval(myInterval);
					$("#woosea_proc_"+hash).removeClass('woo-product-feed-pro-blink_me');	
					return $("#woosea_proc_"+hash).text("ready");
				} else if(data.proc_perc == 999){
					// Do not do anything
				} else {
					clearInterval(myInterval);
					$("#woosea_proc_"+hash).removeClass('woo-product-feed-pro-blink_me');	
					return $("#woosea_proc_"+hash).text("ready");
				}
                        })
                        .fail(function( data ) {
                                console.log('Failed AJAX Call :( /// Return Data: ' + data);
                        });
		});
	}
});
