// JavaScript Document
jQuery(document).ready(function($){

		$('.woo_inst_checkout_options').on('click', function(){
			if($(this).is(':checked')){
				$(this).parent().addClass('selected');
			}else{
				$(this).parent().removeClass('selected');
			}
		});
		

		
		$('select[name="wc_order_action"]').on('change', function(){

			var obj_wrapper = $('#order_line_items');
			if(obj_wrapper.length>0){
				obj_wrapper.removeClass('wc_os_selection')
				obj_wrapper.find('tr').removeClass('selected');
				obj_wrapper.find('input[name^="wc_os_ps"]').remove();
				$('.woocommerce_order_items_wrapper .wc_os_split_selection').remove();


				if($(this).val()=='wc_os_split_action'){
					obj_wrapper.addClass('wc_os_selection')
					obj_wrapper.find('tr').addClass('selected');					
					$('.woocommerce_order_items_wrapper').prepend('<div class="wc_os_split_selection"><iframe src="https://www.youtube.com/embed/wjClFEeYEzo" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe></div>');				
					
					$.each(obj_wrapper.find('tr'), function(){
						$(this).find('td').eq(0).append('<input type="hidden" name="wc_os_ps[]" value="'+$(this).data('order_item_id')+'" />');
					});
					
				}
			}
		});
		

		
		$('#order_line_items tr').on('click', function(){
			if($(this).find('input[name^="wc_os_ps"]').length>0){
				$(this).find('input[name^="wc_os_ps"]').remove();
				$(this).removeClass('selected');
			}else{
				$(this).find('td').eq(0).append('<input type="hidden" name="wc_os_ps[]" value="'+$(this).data('order_item_id')+'" />');
				$(this).addClass('selected');
			}
		});		
		
		if($('select[name="wc_order_action"]').length>0)
		$('select[name="wc_order_action"]').change();
		
		$('.wc-os-defined-rules').on('click', 'ol > li > a', function(){
			var ask = confirm(wos_obj.defined_rules_confirm);
			if(ask){
				var elem = $(this).parents().eq(0);
				var data = {
					'action': 'wos_rules_action',
					'key': $(this).parents().eq(0).data('key')
				};
				
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					//alert('Got this from the server: ' + response);
					elem.remove();
				});
								
				
			}
		});
		
		
		function parse_query_string(query) {
		  var vars = query.split("&");
		  var query_string = {};
		  for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split("=");
			// If first entry with this name
			if (typeof query_string[pair[0]] === "undefined") {
			  query_string[pair[0]] = decodeURIComponent(pair[1]);
			  // If second entry with this name
			} else if (typeof query_string[pair[0]] === "string") {
			  var arr = [query_string[pair[0]], decodeURIComponent(pair[1])];
			  query_string[pair[0]] = arr;
			  // If third or later entry with this name
			} else {
			  query_string[pair[0]].push(decodeURIComponent(pair[1]));
			}
		  }
		  return query_string;
		}		

		$('.wc_settings_div a.nav-tab').click(function(){
			$(this).siblings().removeClass('nav-tab-active');
			$(this).addClass('nav-tab-active');
			$('.nav-tab-content').hide();
			$('.nav-tab-content').eq($(this).index()).show();
			window.history.replaceState('', '', wos_obj.this_url+'&t='+$(this).index());			
			
		});				
		
		var query = window.location.search.substring(1);
		var qs = parse_query_string(query);		
		
		if(typeof(qs.t)!='undefined'){
			$('.wc_settings_div a.nav-tab').eq(qs.t).click();
			
		}
		if($('.wc_settings_div').length>0)
		$('.wc_settings_div').show();		
		
		$('.wc_os_console input[name="wc_os_order_test"]').on('click', function(){
			
			var order_id = $('input[name="wc_os_order_id"]');
			if(order_id.val()!=''){
				var data = {
					'action': 'wos_troubleshooting',
					'order_id': $.trim(order_id.val())
				};				
				// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
				$.post(ajaxurl, data, function(response) {
					
					if($('.wc_os_console ul').length==0)
					$('.wc_os_console').append('<ul></ul>');
					
					//alert('Got this from the server: ' + response);
					var resp = $.parseJSON(response);
					

					$('.wc_os_console ul').prepend('<li style="background-color:rgba('+resp.color.r+','+resp.color.g+','+resp.color.b+',0.05);"></li>');
					$('.wc_os_console ul li').eq(0).html(resp.html);
				});
								
				
			}
		});	
			
		//if 'All products' checked, disable checking individual products
		$('#wc_os_all_product').on('change', function(){
			$('[name="wc_os_settings\[wc_os_products\]\[\]"]').attr('disabled', this.checked ? 'disabled' : null);
		});
		
		//if a certain actions is selected, enable 'All products' checkbox, else disable
		$('[name="wc_os_settings\[wc_os_ie\]"]').on('change', function(){
			split_action_toggle(this.value);
		});
		
		//initialize split action
		$('[name="wc_os_settings\[wc_os_ie\]"]:checked').trigger('change');
		
		//functions
		function split_action_toggle(selected_value){
			//console.log(selected_value);
			switch(selected_value)
			{
			  case 'default':
			  case 'exclusive':
			  case 'io':
				$('#wc_os_all_product').attr('disabled', null);
				$('#wc_os_all_product').trigger('change');
				break;
			  default:
				$('#wc_os_all_product').attr('disabled', 'disabled');
				$('[name="wc_os_settings\[wc_os_products\]\[\]"]').attr('disabled', null);
			}
		}
		
});		