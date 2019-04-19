<?php

/**
 * Configuration Page's Form and Form Submition
 * 
 * @since 1.0.0
 */
function wptf_shortcode_generator_page() {
    //Checking WooCommerce plugin installed or not
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        echo '<br style="clear: both !important;"><h2 class="no_woocommerce_message">Sorry, WooCommerce is not Active. Please Check</h2>';
        echo <<<EOF
<p class="highlight">
    <span>As a <strong><a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a> </strong> Add-ons plugin, First you have to confirm that, <strong>WooCommerce </strong>is <a href="https://wordpress.org/plugins/woocommerce/#installation">installed</a> in your site. Also confirm that you have few Products available in your Store. See <a href="https://wordpress.org/plugins/woocommerce/#installation">How to install WooCommerce</a>. And <a href="https://docs.woocommerce.com/document/managing-products/">add products</a> to your Store. If everything properly setup, Use <strong>Woo Product Table pro</strong>'s <a href="https://codex.wordpress.org/Shortcode">shortcode</a>.</span>
</p>            
EOF;
        die();
    }

    /* Removed at V1.0.4 no need this file
      if (isset($_POST['wptf_form_submition_status'])) {
      if ($_POST['wptf_form_submition_status'] == 1) {
      WPT_Product_Table::updateOption($_POST['wptf_form_array']);
      //var_dump($_POST['wptf_form_array']);
      } else {
      WPT_Product_Table::reset();
      //wp_create_nonce('<b>Reset:</b> All information has changed to default value.');
      }
      //var_dump($_POST);
      global $wp;
      echo '<script>window.location = "' . $wp->request . '";</script>';
      }
     */
    ?>
    <div class="wrap wptf_wrap wptf_configure_page">
        <h2 class="plugin_name"><?php echo WPT_Product_Table::getName(); ?> <span class="plugin_version">v <?php echo WPT_Product_Table::getVersion(); ?></span> <a href="<?php echo admin_url('admin.php?page=woo-product-table-config'); ?>" style="font-size: 15px;">Go to <b>Configure Page</b></a></h2>
        <hr>
        <h1>Shortcode Generator <small style="color: #d00;">not recommended</small> | Click <a href="<?php echo admin_url(); ?>post-new.php?post_type=wpt_product_table">Add New Shortcode </a></h1>
        <p class="wptf_warning_message"><b style="color: #d00;">Warning</b> Don't use this Generator<br/>We have updated our Shortcode System. If you want to keep store your shortcode data and If you want to manage your table by tiny shortcode, Use our new System. So Click <a href="<?php echo admin_url(); ?>post-new.php?post_type=wpt_product_table">Add New Shortcode </a>. We will remove this old shortcode Generator in next Version. </p>
        <div id="wptf_configuration_form" class="wptf_leftside">
            <!-- <form action="" method="post" id="wptf_configuration_form"> -->
                <!-- <input type="hidden" name="wptf_form_submition_status" id="wptf_form_submition_status">  -->

            <?php
            
            //As we creating shortcode section as custom_post_type, so we have used global $post Variable inside all table
            //But if we run old shortcode generator, we need a $post object, that's why, I am just creating this Temp Object
            //if( isset( $_GET['page'] ) && $_GET['page'] == 'woo-product-table' ){ //Actually No need checking condition, because this page will only open for old generator
                class WPTF_Temp_POST{
                    public $ID = 0;
                }
                $post = new WPTF_Temp_POST(); //This $post will use only as Temp to all Tab's page, Not a real $post Object.
            //}
            
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
                <hr>For more customization, <a href="<?php echo admin_url('edit.php?post_type=wpt_product_table&page=woo-product-table-config'); ?>" style="font-size: 22px;">Go to <b>Configure Page</b></a>
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
            <!-- </form> -->


        </div>
        <!-- Right Side start here -->
        <?php include __DIR__ . '/includes/right_side.php'; ?> 
        
    </div>
    <style>
        .tab-content{display: none;}
        .tab-content.tab-content-active{display: block;}
        .wptf_leftside,.wptf_rightside{float: left;}
        .wptf_leftside{
            width: 75%;
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
    <script>
        jQuery(document).ready(function() {
            //alert(window.location.hash.substr(1));
            /**************Admin Panel's Setting Tab Start Here For Tab****************/
            var selectLinkTab = jQuery(".nav-tab-wrapper a.nav-tab");
            var selectTabContent = jQuery(".tab-content");
            var tabName = window.location.hash.substr(1);
            if (tabName) {
                removingActiveClass();
                jQuery('#' + tabName).addClass('tab-content-active');
                jQuery('.nav-tab-wrapper a.wptf_nav_for_' + tabName).addClass('nav-tab-active');
                //console.log(tabName);
            }

            selectLinkTab.click(function(e) {
                var targetTabContent = jQuery(this).data('tab');//getting data value from data-tab attribute
                window.location.hash = targetTabContent; //Set hash keywork in Address Bar 
                e.preventDefault(); //Than prevent for click action of hash keyword
                removingActiveClass();

                jQuery(this).addClass('nav-tab-active');
                jQuery('#' + targetTabContent).addClass('tab-content-active');
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
        });
    </script>

    <?php
}

/**
 * WPT FAQ Page Function
 * Added Description, Available Attribute List
 * @deprecated since 2.7 2.7_15
 */
function wptf_faq_page() {
    ?>
    <div class="wrap wptf_wrap wptf_fag_page">
        <h1>Welcome to <span style="color: #04b0db;">WOO Product Table <sup><b>v: </b><?php echo WPT_Product_Table::getVersion(); ?></sup></span></h1>
        <div class="card">
            <h2 class="title">Shortcode</h2>
            <p><input value="<?php
                //global $shortCodeText; //Globalize ShortcodeText Variable. Otherwise, this will not work for these file.
                echo esc_textarea("[" . WPT_Product_Table::$shortCode . " title='All Products' classes='' table_class='' product_cat_ids='' column='' product_cat_slugs='' sort='ASC' min_price='' max_price='' description_length='']");
                ?>" class="regular-text wptf_code wptf_fullwidth" type="text" readonly="readonly"></p>
            <p class="wptf_ctrl_c"></p>
            <!--
            <p><code>[wpt-shop title='All Products' class='' table_class='' product_cat_ids='' product_cat_slugs='' short='asc' min_price='' max_price='']</code></p>
            -->
        </div>
        <div class="card">
            <h2 class="title">Description</h2>
            <p>WooCommerce all products display as a table in one page by shortcode. Fully responsive and mobile friendly. Easily customizable - color,background,title,text color etc.</p>
        </div>
        <div class="card">
            <h2 class="title">Available Attribute</h2>
            <p>
                There are few attribute available to this plugin.Such:
            <ul>
                <li><code>title</code>: Display Table's Title</li>
                <li><code>classes</code>: Define Table wrapper class. You can set custom class for your Table Wrapper.</li>
                <li><code>table_class</code>: Define Table class. You can set custom class for your Table.</li>
                <li><code>sort</code>: Only available two shorting. Such: 'asc','desc'</li>
                <li><code>product_cat_ids</code>: Products Category IDs with comma. Such: '1,2,3,4'</li>
                <li><code>product_cat_slugs</code>: Products Category SLUGs with comma. Such: 'mobile,computer,shirt,video'</li>
                <li><code>min_price</code>: To set Minimum price for your Product Query.</li>
                <li><code>max_price</code>: To set Maximum price for your Product Query.</li>
                <li><code>template</code>: Available template - (default,blue,green,gray,light_gray,black_n_white) You able to change Template based on shortcode. Not compolsory.</li>
                <li><code>column</code>: Collunm Also changeable by shortcode. use as comma. such: (serial,product_title,product_description,price,quantity,action,description_length)</li>
                <li><code>description_length</code>: Product Description Length based on Carecter amount</li>
            </ul>
            </p>
        </div>

        <div class="card">
            <h2 class="title">How to use?</h2>
            <p>Easilly able to add shortcode by button. Go to your Page Editor or Add new page. Click on "Add Product Table" Button. See Screenshot bellow:</p>
            <p>
                <img src="<?php echo WPT_Product_Table::getPath('BASE_URL'); ?>images/tutorial.jpg">
                <?php
                //WPT_Product_Table::getInstance();
                //var_dump();
                ?>
            </p>
        </div>

        <div class="card">
            <h2 class="title">Available Filter Hooks <sup style="color: #04b0db;">Advance User only</sup></h2>
            <p>
                This section only for <b>Advance User</b>. Don't touch it, if you have no idea.
            <ul>
                <li><code>apply_filters('wptf_product_not_found', 'Product Not found'); </code> // For Not found Message </li>
                <li><code>apply_filters('wptf_before_table_wrapper',''); </code> // Add Value to Before Table Wrapper Div tag </li>
                <li><code>apply_filters( 'wptf_before_table', '' ); </code> // Before Table Tag </li>
                <li><code>apply_filters( 'wptf_after_table_wrapper', '' ); </code> // Apply Filter Just After Table Wrapper div tag </li>
                <li><code>apply_filters( 'wptf_after_table', '' ); </code> // Apply Filter Just After Table tag </li>
                <li><code>apply_filters( 'wptf_change_wrapper_id', 'wptf_table_wrapper' ); </code> // To Change Table Wrapper ID </li>
                <li><code>apply_filters( 'wptf_change_table_id', 'wptf_table' ); </code> // To change Table Tag ID </li>
            </ul>
            </p>
        </div>
    </div>


    <?php
}
