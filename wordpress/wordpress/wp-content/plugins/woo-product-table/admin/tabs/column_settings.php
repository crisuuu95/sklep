<?php
$colums_disable_array = WPT_Product_Table::$colums_disable_array; //For first time only

$meta_column_array = $columns_array = get_post_meta($post->ID, 'column_array', true);
if( !$meta_column_array && empty( $meta_column_array ) ){
    $columns_array = WPT_Product_Table::$columns_array;
}
/**
array (size=27)
  'product_id' => string 'ID' (length=2)
  'serial_number' => string 'SL' (length=2)
  'thumbnails' => string 'Thumbnails' (length=10)
  'product_title' => string 'Product Title' (length=13)
  'description' => string 'Description' (length=11)
  'category' => string 'Category' (length=8)
  'tags' => string 'Tags' (length=4)
  'sku' => string 'SKU' (length=3)
  'weight' => string 'Weight(kg)' (length=10)
  'length' => string 'Length(cm)' (length=10)
  'width' => string 'Width(cm)' (length=9)
  'height' => string 'Height(cm)' (length=10)
  'rating' => string 'Rating' (length=6)
  'stock' => string 'Stock' (length=5)
  'price' => string 'Price' (length=5)
  'wishlist' => string 'Wish List' (length=9)
  'quantity' => string 'Quantity' (length=8)
  'total' => string 'Total Price' (length=11)
  'Message' => string 'Short Message' (length=13)
  'quick' => string 'Quick View' (length=10)
  'date' => string 'Date' (length=4)
  'modified_date' => string 'Modified Date' (length=13)
  'attribute' => string 'Attributes' (length=10)
  'variations' => string 'Variations' (length=10)
  'action' => string 'Action' (length=6)
  'check' => string 'Check' (length=5)
  'quoterequest' => string 'Quote Request' (length=13)
 * // if(!in_array( $keyword,$deactivated_forFree )){
 */
$deactivated_forFree = array(
    'wishlist',
    'total',
    'Message',
    'quick',
    'attribute',
    'variations',
    'check',
    'quoterequest',
);
//var_dump($columns_array);

$meta_enable_column_array = get_post_meta($post->ID, 'enabled_column_array', true);
$column_settings = get_post_meta($post->ID, 'column_settings', true);


?>
<ul id="wptf_column_sortable">
    <?php
    foreach( $columns_array as $keyword => $title ){
        if($meta_enable_column_array && !empty( $meta_enable_column_array ) && is_array( $meta_enable_column_array ) ){
            $enabled_class = $checked_attribute = '';
            if( in_array( $keyword, array_keys( $meta_enable_column_array ) ) ){
                $enabled_class = 'enabled';
                $checked_attribute = ' checked="checked"';
            }
        }else{
            $enabled_class = 'enabled';
            $checked_attribute = ' checked="checked"';
            if( in_array( $keyword, $colums_disable_array ) ){
                $enabled_class = $checked_attribute = '';
            }
        }
        $readOnly = ( $keyword == 'check' ? 'readonly' : false);
        $only_premium_class = false;
        if(in_array( $keyword,$deactivated_forFree )){
            $only_premium_class = 'only_premium_item';
        }
    ?>
    <li class="wptf_sortable_peritem <?php echo $enabled_class; ?> column_keyword_<?php echo $keyword; ?> <?php echo $only_premium_class; ?>">
        <span title="Move Handle" class="handle"></span>
        <div class="wptf_shortable_data">
            <input name="column_array[<?php echo $keyword; ?>]" data-column_title="<?php echo $title; ?>" data-keyword="<?php echo $keyword; ?>" class="colum_data_input <?php echo $keyword; ?>" type="text" value="<?php echo $title; ?>" <?php echo $readOnly; ?>>
        </div>
        <span title="Move Handle" class="handle checkbox_handle">
            <input name="enabled_column_array[<?php echo $keyword; ?>]" value="<?php echo $title; ?>" title="Active Inactive Column" class="checkbox_handle_input <?php echo $enabled_class; ?>" type="checkbox" data-column_keyword="<?php echo $keyword; ?>" <?php echo $checked_attribute; ?>>
        </span>
    </li>
    <?php
    
    }
    ?>

</ul>

<div class="tax_cf_handle_wrapper wptf_disable_column">
    <?php
    //var_dump($_GET);
    ?>
    <p class="tax_cf_message">To add <strong>Taxonomy</strong> or <strong>Custom_Field</strong> as Table Column for your Table, try from following bottom section before [Publish/Update] your post.</p>
    <div id="tax_cf_manager">
        <div class="tax_cf_manager_column tax_cf_manager_choose_column">
            <label class="wptf_label">Choose Type</label>
            <select class="taxt_cf_type">
                <option value="cf_">Custom Filed</option>
                <option value="tax_">Custom Taxonomy</option>
            </select>
        </div>
        <div class="tax_cf_manager_column tax_cf_manager_keyword_column">
            <label class="wptf_label">Keyword (only keyword of you Taxonomy or CustomField)</label>
            <input type="text" class="taxt_cf_input" placeholder="eg: color">
        </div>
        
        <div class="tax_cf_manager_column tax_cf_manager_title_column">
            <label class="wptf_label">Table Column Title/Name</label>
            <input type="text" class="taxt_cf_title" placeholder="eg: Product Color">
        </div>
    </div>
    <div>
        <a id="tax_cf_adding_button" class="button button-primary tax_cf_add_button">Add as Column</a>
    </div>
    <p class="tax_cf_suggesstion">For custom field, you can use <a href="https://wordpress.org/plugins/advanced-custom-fields/" target="_blank">Advanced Custom Fields</a> plugin 
        AND for Taxonomy, you can use <a href="https://wordpress.org/plugins/wck-custom-fields-and-custom-post-types-creator/" target="_blank">Custom Post Types and Custom Fields creator – WCK</a> plugin.
        <br>Besides there are lot of plugin available at <a href="https://wordpress.org/" target="_blank">wordpress.org</a>, Just search on WordPress archives.
    </p>
    <br style="clear: both;">
</div>
<br>
<div class="wptf_column wptf_disable_column">
    <label style="display: inline;width: inherit;" class="wptf_label wptf_column_hide_unhide_tab" for="wptf_column_hide_unhide">Hide Table Head</label>
    <input style="width: 20px;height:20px;" name="column_settings[table_head]" type="checkbox" id="wptf_column_hide_unhide" <?php echo isset($column_settings['table_head']) ? 'checked="checked"' : ''; ?>>
</div>