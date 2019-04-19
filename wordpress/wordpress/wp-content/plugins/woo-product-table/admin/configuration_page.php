<?php

/**
 * Executing selected item for options
 * 
 * @since 2.4 
 */
function wptf_selected( $keyword, $gotten_value ){
    $current_config_value = get_option('wptf_configure_options');
    echo ( isset( $current_config_value[$keyword] ) && $current_config_value[$keyword] == $gotten_value ? 'selected' : false );
}

/**
 * For Configuration Page
 * 
 * @since 2.4
 */
function wptf_configuration_page(){
    
    if( isset( $_POST['data'] ) && isset( $_POST['reset_button'] ) ){
        //Reset 
        $value = WPT_Product_Table::$default;
        //var_dump($value);
        update_option( 'wptf_configure_options', $value );
       
    }else if( isset( $_POST['data'] ) && isset( $_POST['configure_submit'] ) ){
        //configure_submit
        $value = ( is_array( $_POST['data'] ) ? $_POST['data'] : false );
         //Update Maintenace for Free Version, So that always keep default value
        //since 1.4 (04-12-18)
        $default = get_option('wptf_configure_options');
        $value['thumbs_image_size'] = $default['thumbs_image_size'];
        $value['all_selected_direct_checkout'] = $default['all_selected_direct_checkout'];
        $value['product_direct_checkout'] = $default['product_direct_checkout'];
        $value['instant_search_filter'] = $default['instant_search_filter'];
        $value['instant_search_text'] = $default['instant_search_text'];
        $value['disable_cat_tag_link'] = $default['disable_cat_tag_link'];
        $value['disable_product_link'] = $default['disable_product_link'];
        $value['load_more_text'] = $default['load_more_text'];
        $value['search_keyword_text'] = $default['search_keyword_text'];
        $value['quick_view_btn_text'] = $default['quick_view_btn_text'];
        $value['search_button_text'] = $default['search_button_text'];
        $value['item'] = $default['item'];
        $value['items'] = $default['items'];
        $value['search_box_title'] = $default['search_box_title'];
        $value['search_box_searchkeyword'] = $default['search_box_searchkeyword'];
        $value['search_box_orderby'] = $default['search_box_orderby'];
        $value['search_box_order'] = $default['search_box_order'];
        $value['table_in_stock'] = $default['table_in_stock'];
        $value['default_quantity'] = $default['default_quantity'];
        $value['mcart_cart'] = $default['mcart_cart'];
        $value['mcart_view_cart'] = $default['mcart_view_cart'];
        $value['mcart_checkout'] = $default['mcart_checkout'];
        $value['mcart_price'] = $default['mcart_price'];
        $value['mcart_subtotla'] = $default['mcart_subtotla'];
        $value['mcart_view_title'] = $default['mcart_view_title'];
        $value['mcart_empty_now'] = $default['mcart_empty_now'];
        $value['right_combination_message'] = $default['right_combination_message'];
        $value['right_combination_message_alt'] = $default['right_combination_message_alt'];
        $value['select_all_items_message'] = $default['select_all_items_message'];
        $value['no_more_query_message'] = $default['no_more_query_message'];
        $value['adding_in_progress'] = $default['adding_in_progress'];
        $value['no_right_combination'] = $default['no_right_combination'];
        $value['type_your_message'] = $default['type_your_message'];
        $value['loading_more_text'] = $default['loading_more_text'];
        $value['yith_browse_list'] = $default['yith_browse_list'];
        $value['yith_add_to_quote_text'] = $default['yith_add_to_quote_text'];
        $value['yith_add_to_quote_adding'] = $default['yith_add_to_quote_adding'];
        $value['yith_add_to_quote_added'] = $default['yith_add_to_quote_added'];
        $value['filter_reset_button'] = $default['filter_reset_button'];
        
        
        $value['table_out_of_stock'] = $default['table_out_of_stock'];
        $value['out_of_stock_message'] = $default['out_of_stock_message'];
        $value['sorry_out_of_stock'] = $default['sorry_out_of_stock'];
        $value['popup_notice'] = $default['popup_notice'];
        update_option( 'wptf_configure_options', $value);
    }
    $current_config_value = get_option('wptf_configure_options');

    //var_dump($current_config_value);
    ?>
<div class="wrap wptf_wrap wptf_configure_page">
        <h2 class="plugin_name"><?php echo WPT_Product_Table::getName(); ?> <span class="plugin_version">v <?php echo WPT_Product_Table::getVersion(); ?></span> <a href="<?php echo admin_url('edit.php?post_type=wpt_product_table'); ?>" style="font-size: 15px;">Go to <b>All Shortcode Page</b></a></h2>
        <hr>
        <h1>Table Configuration</h1>
        <div id="wptf_configuration_form" class="wptf_leftside">
            <div style="padding-top: 15px;padding-bottom: 15px;" class="fieldwrap wptf_result_footer">
                <form action="" method="POST">
                    <input name="data[plugin_version]" type="hidden" value="<?php echo WPT_Product_Table::getVersion(); ?>" style="">
                    <input name="data[plugin_name]" type="hidden" value="<?php echo WPT_Product_Table::getName(); ?>" style="">
                    <span class="configure_section_title">Basic Settings</span>
                    <table class="wptf_config_form">
                        <tbody>
                            <tr>
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_custom_add_to_cart">Add to Cart Icon</label></th>
                                    <td>
                                        <select name="data[custom_add_to_cart]" id="wptf_table_custom_add_to_cart" class="wptf_fullwidth" >
                                            <option value="add_cart_no_icon" <?php wptf_selected('custom_add_to_cart', 'add_cart_no_icon');?>>No Icon</option>
                                            <option value="add_cart_only_icon" <?php wptf_selected('custom_add_to_cart', 'add_cart_only_icon');?>>Only Icon</option>
                                            <option value="add_cart_left_icon" <?php wptf_selected('custom_add_to_cart', 'add_cart_left_icon');?>>Left Icon and Text</option>
                                            <option value="add_cart_right_icon" <?php wptf_selected('custom_add_to_cart', 'add_cart_right_icon');?>>Text and Right Icon</option>
                                        </select>

                                    </td>
                                 </div>
                            </tr>
                            <tr>
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_sort_mini_filter">Mini Filter Sorting</label></th>
                                    <td>
                                        <select name="data[sort_mini_filter]" id="wptf_table_sort_mini_filter" class="wptf_fullwidth" >
                                            <option value="0" <?php wptf_selected('sort_mini_filter', '0');?>>None</option>
                                            <option value="ASC" <?php wptf_selected('sort_mini_filter', 'ASC');?>>Ascending</option>
                                            <option value="DESC" <?php wptf_selected('sort_mini_filter', 'DESC');?>>Descending</option>
                                        </select>

                                    </td>
                                 </div>
                            </tr>
                            <tr>
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_sort_searchbox_filter">Search Box's Taxonomy Sorting</label></th>
                                    <td>
                                        <select name="data[sort_searchbox_filter]" id="wptf_table_sort_mini_filter" class="wptf_fullwidth" >
                                            <option value="0" <?php wptf_selected('sort_searchbox_filter', '0');?>>None</option>
                                            <option value="ASC" <?php wptf_selected('sort_searchbox_filter', 'ASC');?>>Ascending</option>
                                            <option value="DESC" <?php wptf_selected('sort_searchbox_filter', 'DESC');?>>Descending</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">

                                <th><label class="wptf_label" for="wptf_table_thumbs_image_size">Thumbs Image Size <small>[Only Int]</small></label></th>
                                <td>
                                    <input name="data[thumbs_image_size]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['thumbs_image_size']; ?>" id="wptf_table_thumbs_image_size" type="number" placeholder="Thumbnail size. eg: 56" min="16" max="" pattern="[0-9]*" inputmode="numeric">
                                </td>

                            </tr>
                            
                            <tr class="only_for_premium"> 
                                <!-- New at Version: 3.9 -->
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_popup_notice">Popup Notice [New]</label></th>
                                    <td>
                                       <select name="data[popup_notice]" id="wptf_table_popup_notice" class="wptf_fullwidth" >
                                            <option value="1" <?php wptf_selected('popup_notice', '1');?>>Enable</option>
                                            <option value="0" <?php wptf_selected('popup_notice', '0');?>>Disable</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            
                            
                            <tr> 
                                <!-- New at Version: 3.1 -->
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_thumbs_lightbox">Thumbs Image LightBox</label></th>
                                    <td>
                                       <select name="data[thumbs_lightbox]" id="wptf_table_thumbs_lightbox" class="wptf_fullwidth" >
                                            <option value="1" <?php wptf_selected('thumbs_lightbox', '1');?>>Enable</option>
                                            <option value="0" <?php wptf_selected('thumbs_lightbox', '0');?>>Disable</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label class="wptf_label" for="wptf_table_disable_product_link">Disable Product Link</label></th>
                                    <td>
                                        <select name="data[disable_product_link]" id="wptf_table_disable_product_link" class="wptf_fullwidth" >
                                            <option value="1" <?php wptf_selected('disable_product_link', '1');?>>Yes</option>
                                            <option value="0" <?php wptf_selected('disable_product_link', '0');?>>No</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr> 
                                <div class="wptf_column">
                                    <th>  <label class="wptf_label" for="wptf_table_product_link_target">Product Link Open Type</label>
                                    <td>
                                        <select name="data[product_link_target]" id="wptf_table_disable_product_link" class="wptf_fullwidth" >
                                            <option value="_blank" <?php wptf_selected('product_link_target', '_blank');?>>New Tab</option>
                                            <option value="_self" <?php wptf_selected('product_link_target', '_self');?>>Self Tab</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_all_selected_direct_checkout">Direct Checkout Page[for Add to cart Selected]</label></th>
                                    <td>
                                        <select name="data[all_selected_direct_checkout]" id="wptf_table_all_selected_direct_checkout" class="wptf_fullwidth" >
                                            <option value="no" <?php wptf_selected('all_selected_direct_checkout', 'no');?>>No</option>
                                            <option value="yes" <?php wptf_selected('all_selected_direct_checkout', 'yes');?>>Yes</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label class="wptf_label" for="wptf_table_product_direct_checkout">Enable Quick Buy Button [Direct Checkout Page for each product]</label></th>
                                    <td>
                                        <select name="data[product_direct_checkout]" id="wptf_table_product_direct_checkout" class="wptf_fullwidth" >
                                            <option value="no" <?php wptf_selected('product_direct_checkout', 'no');?>>No</option>
                                            <option value="yes" <?php wptf_selected('product_direct_checkout', 'yes');?>>Yes</option>
                                        </select>
                                        <p style="color: #0071a1;padding: 0;margin: 0;">Direct going to Checkout Page just after Added to cart for each product</p>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_disable_cat_tag_link">Disable <strong>[Categories and Tags]</strong> Link</label> </th>
                                    <td>
                                        <select name="data[disable_cat_tag_link]" id="wptf_table_disable_product_link" class="wptf_fullwidth" >
                                            <option value="1" <?php wptf_selected('disable_cat_tag_link', '1');?>>Yes</option>
                                            <option value="0" <?php wptf_selected('disable_cat_tag_link', '0');?>>No</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <tr> 
                                <div class="wptf_column">
                                    <th> <label class="wptf_label" for="wptf_table_disable_loading_more">Disable <b>[Load More]</b> Button</label></th>
                                    <td>
                                        <select name="data[disable_loading_more]" id="wptf_table_disable_loading_more" class="wptf_fullwidth" >
                                            <option value="load_more_hidden" <?php wptf_selected('disable_loading_more', 'load_more_hidden');?>>Yes</option>
                                            <option value="normal" <?php wptf_selected('disable_loading_more', 'normal');?>>No</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label class="wptf_label" for="wptf_table_instant_search_filter">Instant Search Filter</label></th>
                                    <td>
                                       <select name="data[instant_search_filter]" id="wptf_table_instant_search_filter" class="wptf_fullwidth" >
                                            <option value="1" <?php wptf_selected('instant_search_filter', '1');?>>Yes</option>
                                            <option value="0" <?php wptf_selected('instant_search_filter', '0');?>>No</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            <!-- Removed from 3.8 Version
                            <tr> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_default_quantity" class="wptf_label">Default Quantity| Eg: 1</label></th>
                                    <td>
                                      <input name="data[default_quantity]" class="wptf_data_filed_atts" value="<?php 
                                        // echo $current_config_value['default_quantity']; //Removed from 3.8
                                      ?>" id="wptf_table_default_quantity" type="number" placeholder="Default Quantity Input here. eg: 1" min="0" max="" pattern="[0-9]*" inputmode="numeric">
                                    </td>
                                 </div>
                            </tr>
                            -->
                        </tbody>
                    </table>
                    <span class="configure_section_title">Label Text</span>
                    <table class="wptf_config_form">
                        <tbody>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_load_more_text" class="wptf_label"><b>[Load More]</b> - Button Text</label></th>
                                    <td>
                                      <input name="data[load_more_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['load_more_text']; ?>" id="wptf_table_load_more_text" type="text" placeholder="Load More text write here">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th>   <label for="wptf_table_search_button_text" class="wptf_label"><b>[Search]</b> - Button Text</label></th>
                                    <td>
                                       <input name="data[search_button_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_button_text']; ?>" id="wptf_table_search_button_textt" type="text" placeholder="Search text write here">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_search_keyword_text" class="wptf_label"><b>[Search Keyword]</b> - Text</label></th>
                                    <td>
                                        <input name="data[search_keyword_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_keyword_text']; ?>" id="wptf_table_search_button_textt" type="text" placeholder="Search Keyword">
                                    </td>
                                 </div>
                            </tr>
                            
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_loading_more_text" class="wptf_label"><b>[Loading..]</b> - Button Text</label></th>
                                    <td>
                                      <input name="data[loading_more_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['loading_more_text']; ?>" id="wptf_table_loading_more_text" type="text" placeholder="Loading.. text write here"> 
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_instant_search_textt" class="wptf_label"><b>[Instance Search]</b> - Text</label></th>
                                    <td>
                                      <input name="data[instant_search_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['instant_search_text']; ?>" id="wptf_table_instant_search_text" type="text" placeholder="Instance Search text write here"> 
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_filter_text" class="wptf_label"><b>[Filter]</b> - Text of Filter</label></th>
                                    <td>
                                        <input name="data[filter_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['filter_text']; ?>" id="wptf_table_filter_text" type="text" placeholder="eg: Filter">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_filter_reset_button" class="wptf_label"><b>[Reset]</b> - Button Text of Filter</label></th>
                                    <td>
                                       <input name="data[filter_reset_button]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['filter_reset_button']; ?>" id="wptf_table_filter_reset_button" type="text" placeholder="eg: Reset">
                                    </td>
                                 </div>
                            </tr>
                           
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_item" class="wptf_label">Item [for Singular]</label></th>
                                    <td>
                                     <input name="data[item]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['item']; ?>" id="wptf_table_item" type="text" placeholder="Item | for All selected Button">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_item" class="wptf_label">Item [for Plural]</label></th>
                                    <td>
                                     <input name="data[items]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['items']; ?>" id="wptf_table_item" type="text" placeholder="Item | for All selected Button">
                                    </td>
                                 </div>
                            </tr>
                            
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_item" class="wptf_label">Add to Cart all selected's [Added] Text</label></th>
                                    <td>
                                        <input name="data[add2cart_all_added_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['add2cart_all_added_text']; ?>" id="wptf_table_item" type="text" placeholder="Added text for [Add to cart Selected]">
                                    </td>
                                </div>

                            </tr>
                            
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_search_box_title" class="wptf_label">Search Box title</label></th>
                                    <td>
                                     <input name="data[search_box_title]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_box_title']; ?>" id="wptf_table_search_box_title" type="text" placeholder="Search Box title">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_search_box_searchkeyword" class="wptf_label">Search Keyword text</label></th>
                                    <td>
                                     <input name="data[search_box_searchkeyword]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_box_searchkeyword']; ?>" id="wptf_table_search_box_searchkeyword" type="text" placeholder="Search Keyword text">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_search_box_orderby" class="wptf_label">SearchBox Order By text</label></label></th>
                                    <td>
                                        <input name="data[search_box_orderby]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_box_orderby']; ?>" id="wptf_table_search_box_orderby" type="text" placeholder="Order By text">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_search_box_order" class="wptf_label">SearchBox Order text</label></label></th>
                                    <td>
                                        <input name="data[search_box_order]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['search_box_order']; ?>" id="wptf_table_search_box_title" type="text" placeholder="Order text">
                                    </td>
                                 </div>
                            </tr>
                        </tbody>
                    </table>
                    <span class="configure_section_title">External Plugin's <span style="color: orange; font-size: 18px;">[YITH]</span> </span>
                    <table class="wptf_config_form external_plugin">
                        <tbody>
                             <tr class="only_for_premium"> 
                                <!-- Quick View Button Text -->
                                <div class="wptf_column">
                                    <th><label for="wptf_table_quick_view_btn_text" class="wptf_label"><b>[Quick View]</b> - Button Text</label></th>
                                    <td>
                                      <input name="data[quick_view_btn_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['quick_view_btn_text']; ?>" id="wptf_table_quick_view_btn_text" type="text" placeholder="eg: Quick View">
                                      <p style="color: #005082;padding: 0;margin: 0;">Only for <a target="_blank" href="https://wordpress.org/plugins/yith-woocommerce-quick-view/">YITH WooCommerce Quick View</a> Plugin</p>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label class="wptf_label" for="wptf_table_yith_product_type">Quote Request Plugin's Type</label></th>
                                    <td>
                                       <select name="data[yith_product_type]" id="wptf_table_instant_search_filter" class="wptf_fullwidth" >
                                            <option value="free" <?php wptf_selected('yith_product_type', 'free');?>>Free</option>
                                            <option value="premium" <?php wptf_selected('yith_product_type', 'premium');?>>Premium</option>
                                        </select>
                                    </td>
                                 </div>
                            </tr>
                            
                             <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_yith_browse_list" class="wptf_label"><b>[Browse Quote list]</b> - text </label></th>
                                    <td>
                                      <input name="data[yith_browse_list]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['yith_browse_list']; ?>" id="wptf_table_yith_add_to_quote_text" type="text" placeholder="Browse the list - text write here">
                                      <span style="color: red;">Only for Premium YITH Quote Request Plugin</span>
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th><label for="wptf_table_yith_add_to_quote_text" class="wptf_label"><b>[Add to Quote]</b> - button text</label></th>
                                    <td>
                                       <input name="data[yith_add_to_quote_text]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['yith_add_to_quote_text']; ?>" id="wptf_table_yith_add_to_quote_text" type="text" placeholder="Add to Quote text write here">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_yith_add_to_quote_adding" class="wptf_label"><b>[Quote Adding]</b> - text</label></th>
                                    <td>
                                      <input name="data[yith_add_to_quote_adding]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['yith_add_to_quote_adding']; ?>" id="wptf_table_yith_add_to_quote_adding" type="text" placeholder="Adding text write here">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium"> 
                                <div class="wptf_column">
                                    <th> <label for="wptf_table_yith_add_to_quote_added" class="wptf_label"><b>[Quote Added]</b> - text</label></th>
                                    <td>
                                     <input name="data[yith_add_to_quote_added]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['yith_add_to_quote_added']; ?>" id="wptf_table_yith_add_to_quote_added" type="text" placeholder="Quote Added text write here">
                                    </td>
                                 </div>
                            </tr>
                        </tbody>
                    </table>
                    
                    <span class="configure_section_title">Table's Default Content <small style="color: orange; font-size: 12px;">Since 3.3</small></span>
                    <table class="wptf_config_form">
                        <tbody>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_table_in_stock" class="wptf_label">[In Stock] for Table Column</label></th>
                                    <td>
                                        <input name="data[table_in_stock]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['table_in_stock']; ?>" id="wptf_table_table_in_stock" type="text" placeholder="In Stock">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_table_out_of_stock" class="wptf_label">[Out of Stock] for Table Column</label></th>
                                    <td>
                                        <input name="data[table_out_of_stock]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['table_out_of_stock']; ?>" id="wptf_table_table_out_of_stock" type="text" placeholder="Out of Stock">
                                    </td>
                                 </div>
                            </tr>
                            
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_table_on_back_order" class="wptf_label">[On Back Order] for Table Column</label></th>
                                    <td>
                                        <input name="data[table_on_back_order]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['table_on_back_order']; ?>" id="wptf_table_table_on_back_order" type="text" placeholder="On Back Order">
                                    </td>
                                 </div>
                            </tr>
                            
                        </tbody>
                    </table>
                    
                    <!-- Here was Table of MiniCart's default content. We have keep backup to backup_configuration.php -->
                    
                    <span class="configure_section_title">All Messages</span>
                    <table class="wptf_config_form wptf_all_messages">
                        <tbody>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_right_combination_message" class="wptf_label">Variations [Not available] Message</label></th>
                                    <td> 
                                        <input name="data[right_combination_message]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['right_combination_message']; ?>" id="wptf_table_right_combination_message" type="text" placeholder="Not Available">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_right_combination_message_alt" class="wptf_label">[Product variations is not set Properly. May be: price is not inputted. may be: Out of Stock.] Message</label></th>
                                    <td>    
                                        <input name="data[right_combination_message_alt]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['right_combination_message_alt']; ?>" id="wptf_table_right_combination_message_alt" type="text" placeholder="Product variations is not set Properly. May be: price is not inputted. may be: Out of Stock.">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_select_all_items_message" class="wptf_label">[Please select all items.] Message</label></th>
                                    <td>    
                                        <input name="data[select_all_items_message]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['select_all_items_message']; ?>" id="wptf_table_select_all_items_message" type="text" placeholder="Please select all items.">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_out_of_stock_message" class="wptf_label">[Out of Stock] Message</label></th>
                                    <td>    
                                        <input name="data[out_of_stock_message]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['out_of_stock_message']; ?>" id="wptf_table_out_of_stock_message" type="text" placeholder="Out of Stock">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_no_more_query_message" class="wptf_label">[There is no more products based on current Query.] Message</label></th>
                                    <td>    
                                        <input name="data[no_more_query_message]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['no_more_query_message']; ?>" id="wptf_table_out_of_stock_message" type="text" placeholder="There is no more products based on current Query.">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_out_of_stock_message" class="wptf_label">[ Adding in Progress ] Message</label></th>
                                    <td>    
                                        <input name="data[adding_in_progress]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['adding_in_progress']; ?>" id="wptf_table_out_of_stock_message" type="text" placeholder="Adding in Progress">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_out_of_stock_message" class="wptf_label">[ No Right Combination ] Message</label></th>
                                    <td>    
                                        <input name="data[no_right_combination]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['no_right_combination']; ?>" id="wptf_table_out_of_stock_message" type="text" placeholder="No Right Combination">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_sorry_plz_right_combination" class="wptf_label">[ Sorry, Please choose right combination. ] Message</label></th>
                                    <td>    
                                        <input name="data[sorry_plz_right_combination]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['sorry_plz_right_combination']; ?>" id="wptf_table_sorry_plz_right_combination" type="text" placeholder="Sorry, Please choose right combination.">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                <div class="wptf_column">
                                    <th><label for="wptf_table_out_of_stock_message" class="wptf_label">[ Sorry! Out of Stock! ] Message</label></th>
                                    <td>    
                                        <input name="data[sorry_out_of_stock]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['sorry_out_of_stock']; ?>" id="wptf_table_out_of_stock_message" type="text" placeholder="Sorry! Out of Stock!">
                                    </td>
                                 </div>
                            </tr>
                            <tr class="only_for_premium">
                                 <!-- New Added at Version 3.1 -->
                                <div class="wptf_column">
                                    <th><label for="wptf_table_type_your_message" class="wptf_label">[Type your Message.] Message</label></th>
                                    <td>    
                                        <input name="data[type_your_message]" class="wptf_data_filed_atts" value="<?php echo $current_config_value['type_your_message']; ?>" id="wptf_table_type_your_message" type="text" placeholder="Type your Message.">
                                    </td>
                                 </div>
                            </tr>
                        </tbody>
                    </table>
                    <button type="submit" name="configure_submit" class="button-primary primary button btn-info">Submit</button>
                    <button type="submit" name="reset_button" class="button">Reset</button>
                    
                </form>
            </div>
            
            
            
        </div>
        <!-- Right Side start here -->
        <?php include __DIR__ . '/includes/right_side.php'; ?> 
    </div>

    <style>
        .tab-content{display: none;}
        .tab-content.tab-content-active{display: block;}
        .wptf_leftside,.wptf_rightside{float: left;}
        .wptf_leftside{
            width: 75%;overflow:hidden;
        }
        .break_space_large{display: block;visibility: hidden;height: 25px;background: transparent;}
        .break_space,.break_space_medium{display: block;visibility: hidden;height: 15px;background: transparent;}
        .break_space_small{display: block;visibility: hidden;height: 5px;background: transparent;}
        .wptf_rightside{width: 25%;}
        @media only screen and (max-width: 800px){
            .wptf_leftside{width: 100%;}
            .wptf_rightside{display: none !important;}
        }
        /*****For Column Moveable Item*******/
        ul#wptf_column_sortable li>span.handle{
            background-image: url('<?php echo WPTF_BASE_URL . 'images/move.png'; ?>');
        }
    </style>
    <?php
}
