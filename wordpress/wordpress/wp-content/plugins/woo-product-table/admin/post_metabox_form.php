<div id="wptf_configuration_form" class="wptf_shortcode_gen_panel">
    <!-- <form action="" method="post" id="wptf_configuration_form"> -->
        <!-- <input type="hidden" name="wptf_form_submition_status" id="wptf_form_submition_status">  -->

    <?php
    /**
     * Tab Maintenace. Table will be come from [tabs] folder based on $tab_array
     * this $tab_arry will define, how much tab and tab content
     */
    $tab_array = array(
        'column_settings' => "Column",
        'basics' => 'Basics',
        'conditions' => 'Conditions',
        'mobile' => 'Mobile Issue',
        'search_n_filter' => 'Search Box And Filter',
            //'text_n_display' => 'Display Setting', 
            //'shortcode' => 'ShortCode'
    );

    echo '<nav class="nav-tab-wrapper">';
    $active_nav = 'nav-tab-active';
    foreach ($tab_array as $nav => $title) {
        echo "<a href='#{$nav}' data-tab='{$nav}' class='wptf_nav_for_{$nav} nav-tab {$active_nav}'>{$title}</a>";
        $active_nav = false;
    }
    echo '</nav>';


    //Now start for Tab Content
    $active_tab_content = 'tab-content-active';
    foreach ($tab_array as $tab => $title) {
        echo '<div class="tab-content ' . $active_tab_content . '" id="' . $tab . '">';
        echo '<div class="fieldwrap">';
        $tab_file_of_admin = WPTF_BASE_DIR . 'admin/tabs/' . $tab . '.php';
        //var_dump($tab);
        if (is_file($tab_file_of_admin)) {
            include $tab_file_of_admin; //WPTF_BASE_DIR . 'admin/tabs/' . $tab . '.php';
        } else {
            echo '<h2>' . $tab . '.php file is not found in tabs folder</h2>';
        }
        echo '</div>'; //End of .fieldwrap
        echo '</div>'; //End of Tab content div
        $active_tab_content = false; //Active tab content only for First
    }
    ?>


    <!-- 
    <hr>




    <div class="fieldwrap wptf_result_footer">

        <div class="wptf_shotcode_gererator_buttor_wrapper">
            <button title="Generate Minified Shortcode" data-shortcode_type='minified' class="button_for_generate_shortcode wptf_g_s_button button-primary primary button btn-info">Generate Shortcode <small>[recommended]</small></button>
            <button title="Generate Full Shortcode" data-shortcode_type='normal' class="button_for_generate_shortcode wptf_g_s_button primary button btn-info">Generate Full Shortcode</button>

            <br>
            <p>Before Copy your code, Please Check all Tabs.</p>
        </div>

        <div class="shortcode_output">
            <textarea id="wptf_output_of_shortcode" placeholder="Your Generated shortcode will display here. Click on [Generate Shortcode] button."></textarea>
        </div>
        <hr>For more customization, <a href="<?php echo admin_url('admin.php?page=woo-product-table-config'); ?>" style="font-size: 22px;">Go to <b>Configure Page</b></a>
        <script>
            jQuery(document).ready(function() {

                jQuery('#wptf_output_of_shortcode').toggle(function() {
                    jQuery(this).select();
                    }, function() {
                    jQuery(this).unselect();
                });

                /**
                 * If chose Custom Meta value than
                 * Custom meta value's input field will be visible
                 * Otherise, By default, It stay hidden
                 */
                jQuery("#wptf_table_sort_order_by").change(function(){
                    var current_val = jQuery(this).val();
                    console.log(current_val);
                    if(current_val === 'meta_value' || current_val === 'meta_value_num'){
                        jQuery("#wptf_meta_value_wrapper").fadeIn();
                        jQuery("#wptf_product_meta_value_sort").val('_sku');
                    }else{
                        jQuery("#wptf_meta_value_wrapper").fadeOut();
                        jQuery("#wptf_product_meta_value_sort").val('');
                    }
                });

                /**
                 * ShortCode Generator
                 * @type type
                 * @si@since 1.0.4
                 * @date: 4/5/2018 [D/M/Y]
                 */
                jQuery('.button_for_generate_shortcode.wptf_g_s_button').click(function(e) {
                    e.preventDefault();
                    var shortcode_type = jQuery(this).data('shortcode_type');
                    generateShortcode(shortcode_type);

                    function generateShortcode(shorcode_type = 'normal') {

                        //Column Tab start Start
                        var column_keyword, column_title;
                        column_keyword = [];
                        column_title = [];
                        jQuery('#wptf_column_sortable li.wptf_sortable_peritem.enabled .wptf_shortable_data input.colum_data_input').each(function(Index) {
                            column_keyword[Index] = jQuery(this).data('keyword');
                            column_title[Index] = jQuery(this).val();

                        });
                        if (column_keyword.length < 1) {
                            alert('Please choose minimum 1 Item from [Column] tab.');
                            return false;
                        }
                        column_keyword_values = column_keyword.join(',');
                        column_title_values = column_title.join(',');
                        //Column Tab End Here

                        //Mobile issue tab start here
                        var mobile_hide = [];
                        jQuery('#wptf_keyword_hide_mobile li.hide_on_mobile_permits.enabled .wptf_mobile_hide_keyword b.mobile_issue_field').each(function(Index) {
                            mobile_hide[Index] = jQuery(this).data('keyword');

                        });
                        mobile_hide_values = mobile_hide.join(',');
                        //Mobile issue tab end here

                        //Basics and Condition Tab start here
                        var data_name, data_value, data_array = [], minified_data_array = [], serial_minified = 0;
                        jQuery('.wptf_data_filed_atts').each(function(Index) {

                            data_name = jQuery(this).data('name');
                            data_value = jQuery(this).val();
                            if (Array.isArray(data_value)) {
                                data_value = data_value.join(',');
                            }
                            if (data_value === null) {
                                data_value = '';
                            }
                            data_array[Index] = data_name + "='" + data_value + "'";//[data_name, data_value];
                            if (data_value !== '') {
                                minified_data_array[serial_minified] = data_name + "='" + data_value + "'";
                                serial_minified++;
                            }
                            //data_array[Index] = data_name"='" + data_value + "'";
                            //console.log(data_name);
                            //console.log(data_value);
                        });
                        //saiful_putting_value
                        var aditional_shortcode_part;
                        if (shorcode_type === 'minified') {
                            aditional_shortcode_part = minified_data_array.join(' ');
                        } else {
                            aditional_shortcode_part = data_array.join(' ');
                        }
                        //Basics and Condition Tab End Here



                        var finalShortCode = "[Product_Table column_keyword='" + column_keyword_values + "' column_title='" + column_title_values + "' mobile_hide='" + mobile_hide_values + "' " + aditional_shortcode_part + "]"; //title= '' product_cat_ids= '16,26' cat_explude='' post_exclude='' only_stock='0' short= '' template= 'business' min_price= '' max_price= '' description_length= '' table_class='saiful_table_class' add_to_cart_text='Buy Now'
                        jQuery('#wptf_output_of_shortcode').text(finalShortCode);
                    }
                });

                jQuery('#wptf_column_sortable li.wptf_sortable_peritem input.checkbox_handle_input').click(function() {
                    var keyword = jQuery(this).data('column_keyword');
                    var targetLiSelector = jQuery('#wptf_column_sortable li.wptf_sortable_peritem.column_keyword_' + keyword);
                    if (jQuery(this).prop('checked')) {
                        jQuery(this).addClass('enabled');
                        targetLiSelector.addClass('enabled');
                    } else {
                        jQuery(this).removeClass('enabled');
                        targetLiSelector.removeClass('enabled');
                    }
                });

                /**
                 * For Hide on Mobile
                 * 
                 * @param {type} param
                 */
                jQuery('#wptf_keyword_hide_mobile li.hide_on_mobile_permits input.checkbox_handle_input').click(function() {
                    var keyword = jQuery(this).data('column_keyword');
                    var targetLiSelector = jQuery('#wptf_keyword_hide_mobile li.hide_on_mobile_permits.column_keyword_' + keyword);
                    if (jQuery(this).prop('checked')) {
                        jQuery(this).addClass('enabled');
                        targetLiSelector.addClass('enabled');
                    } else {
                        jQuery(this).removeClass('enabled');
                        targetLiSelector.removeClass('enabled');
                    }
                });


                /********ShortCode Generaot End Here****
                 * colum_keyword[] = jQuery(this).data('column_title');
                 colum_title[] = jQuery(this).val();
                 ********/
            });
        </script>                 

    </div>
    -- </form> -->


</div>

<style>
/*****For Column Moveable Item*******/
ul#wptf_column_sortable li>span.handle{
    background-image: url('<?php echo WPTF_BASE_URL . 'images/move.png'; ?>');
}
</style>
