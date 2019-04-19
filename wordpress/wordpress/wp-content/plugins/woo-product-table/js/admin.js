/* 
 * For any important
 * As $ already included to Admin Section of WordPress, So $ is not added again
 * Already Checked, $ working here properly
 * 
 * onclick="document.getElementById('wptf_form_submition_status').value = '0';
 document.getElementById('wptf_configuration_form').submit();"
 * 
 *  onclick="document.getElementById('wptf_form_submition_status').value = '1';
 document.getElementById('wptf_configuration_form').submit();"
 * @since 1.0.0
 * @update 1.0.3
 */


(function($) {
    $(document).ready(function() {
        if(! $('body').hasClass('post-type-wpt_product_table')){
            return false;
        }
        //For select, used select2 addons of jquery
        $('.wptf_wrap select,.wptf_shortcode_gen_panel select, select#wptf_product_ids,select#product_tag_ids').select2();
        
        //code for Sortable
        $( "#wptf_column_sortable" ).sortable({
            handle:'.handle',
            beforeStop: function(){
                alert("Not available in Free version!");
                return false;
            }
        });
        $( "#wptf_column_sortable" ).disableSelection();
        
        $('.wptf_auto_select_n_copy').toggle(function() {
                $(this).select();
            },
            function() {
                //$(this).unselect();
            }
        );
        
        
        $('.wptf_copy_button_metabox').click(function(){
            var ID_SELECTOR = $(this).data('target_id');
            copyMySelectedITem(ID_SELECTOR);
        });
        //wptf_metabox_copy_content
        function copyMySelectedITem(ID_SELECTOR) {
          var copyText = document.getElementById(ID_SELECTOR);
          copyText.select();
          document.execCommand("copy");
          //alert("Copied the text: " + copyText.value);
          $('.' + ID_SELECTOR).html("Copied");
          $('.' + ID_SELECTOR).fadeIn();
          
          var myInterVal = setInterval(function(){
              $('.' + ID_SELECTOR).html("");
              $('.' + ID_SELECTOR).fadeOut();
              clearInterval(myInterVal);
          },1000);
        }
        
        /**************Admin Panel's Setting Tab Start Here For Tab****************/
        var selectLinkTab = $(".nav-tab-wrapper a.nav-tab");
        var selectTabContent = $(".tab-content");
        var tabName = window.location.hash.substr(1);
        if (tabName) {
            removingActiveClass();
            $('#' + tabName).addClass('tab-content-active');
            $('.nav-tab-wrapper a.wptf_nav_for_' + tabName).addClass('nav-tab-active');
            //console.log(tabName);
        }

        selectLinkTab.click(function(e) {
            var targetTabContent = $(this).data('tab');//getting data value from data-tab attribute
            window.location.hash = targetTabContent; //Set hash keywork in Address Bar 
            e.preventDefault(); //Than prevent for click action of hash keyword
            removingActiveClass();

            $(this).addClass('nav-tab-active');
            $('#' + targetTabContent).addClass('tab-content-active');
            console.log(targetTabContent);
            //window.location.hash = targetTabContent;
        });

        /**
         * Removing current active nav_tab and tab_content element
         * 
         * @returns {nothing}
         */
        function removingActiveClass() {
            selectLinkTab.removeClass('nav-tab-active');
            selectTabContent.removeClass('tab-content-active');
            return false;
        }

        /**************Admin Panel's Setting Tab End Here****************/
        
        
        /*********Columns , meta sorting orders and mobile checkbox controlling start here************/
        /**
         * If chose Custom Meta value than
         * Custom meta value's input field will be visible
         * Otherise, By default, It stay hidden
         */
        $("#wptf_table_sort_order_by").change(function(){
            var current_val = $(this).val();
            console.log(current_val);
            if(current_val === 'meta_value' || current_val === 'meta_value_num'){
                $("#wptf_meta_value_wrapper").fadeIn();
                $("#wptf_product_meta_value_sort").val('_sku');
            }else{
                $("#wptf_meta_value_wrapper").fadeOut();
                $("#wptf_product_meta_value_sort").val('');
            }
        });

        
        /**
         * Column Section Managing
         */

        $('#wptf_column_sortable li.wptf_sortable_peritem input.checkbox_handle_input').click(function() {
            var keyword = $(this).data('column_keyword');
            var targetLiSelector = $('#wptf_column_sortable li.wptf_sortable_peritem.column_keyword_' + keyword);
            
            
            if ($(this).prop('checked')) {
                $(this).addClass('enabled');
                targetLiSelector.addClass('enabled');
            } else {
                //Counting Column//
                var column_keyword;
                column_keyword = [];
                $('#wptf_column_sortable li.wptf_sortable_peritem.enabled .wptf_shortable_data input.colum_data_input').each(function(Index) {
                    column_keyword[Index] = $(this).data('keyword');
                });
                if (column_keyword.length < 2) {
                    alert('Minimum 1 column is required!');
                    return false;
                }

                //Counting colum End here
                
                $(this).removeClass('enabled');
                targetLiSelector.removeClass('enabled');
            }
        });

        /**
         * For Hide on Mobile
         * 
         * @param {type} param
         */
        $('#wptf_keyword_hide_mobile li.hide_on_mobile_permits input.checkbox_handle_input').click(function() {
            var keyword = $(this).data('column_keyword');
            var targetLiSelector = $('#wptf_keyword_hide_mobile li.hide_on_mobile_permits.column_keyword_' + keyword);
            if ($(this).prop('checked')) {
                $(this).addClass('enabled');
                targetLiSelector.addClass('enabled');
            } else {
                $(this).removeClass('enabled');
                targetLiSelector.removeClass('enabled');
            }
        });

        /*********Columns , meta sorting orders and mobile checkbox controlling end here************/
        
        //Adding Texonomy or Custom Field Button
        $('body').on('click','#tax_cf_adding_button',function(){
            var taxt_cf_type,taxt_cf_input,taxt_cf_title,keyword,html;
            taxt_cf_type = $('.taxt_cf_type').val();
            taxt_cf_input = $('.taxt_cf_input').val();
            taxt_cf_title = $('.taxt_cf_title').val();
            
            if(taxt_cf_input === '' || taxt_cf_title === ''){
                alert("Keyword or Column Name can't be empty");
                return false;
            }
            keyword = taxt_cf_type + taxt_cf_input;
            console.log(taxt_cf_input);
            
            html = '<li class="wptf_sortable_peritem  column_keyword_' + keyword + ' enabled">';
                html += '<span title="Move Handle" class="handle ui-sortable-handle"></span>';
                html += '<div class="wptf_shortable_data">';
                    html += '<input name="column_array[' + keyword + ']" data-column_title="' + taxt_cf_title + '" data-keyword="' + keyword + '" class="colum_data_input product_id" type="text" value="' + taxt_cf_title + '">';
                html += '</div>';
                html += '<span title="Move Handle" class="handle checkbox_handle ui-sortable-handle">';
                    html += '<input name="enabled_column_array[' + keyword + ']" value="' + taxt_cf_title + '" title="Active Inactive Column" class="checkbox_handle_input  enabled" type="checkbox" data-column_keyword="' + keyword + '" checked="checked">';
                html += '</span>';
            html += '</li>';
            $('#wptf_column_sortable').append(html);
            
        });
        
        //Deactivating Checkbox
        $('li.wptf_sortable_peritem.only_premium_item .checkbox_handle,.wptf_disable_column a#tax_cf_adding_button,.wptf_disable_column input#wptf_column_hide_unhide').click(function(e){
            e.preventDefault();
            alert("Only for Premium");
            return false;
        });
        $('.wptf_disable_column input,.wptf_disable_column select').attr('disabled','disabled');
        
        $('.only_for_premium th label.wptf_label,.wptf_disable_column label.wptf_label,li.wptf_sortable_peritem.only_premium_item').append(' <small style="color:#d00;font-weight:bold"> Premium</small>');
        //$('').append(' <small style="color:#d00;font-weight:bold"> Premium</small>');
        
        
        if(! $('body').hasClass('post-type-wpt_product_table_disable_for_free_version')){
            return false;
        }
    });
})(jQuery);
