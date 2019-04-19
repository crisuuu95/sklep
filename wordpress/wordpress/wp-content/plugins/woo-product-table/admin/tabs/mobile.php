<?php
$meta_mobile =  get_post_meta($post->ID, 'mobile', true);
?>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_table_mobile_responsive">Mobile Responsive</label>
    <select name="mobile[mobile_responsive]" data-name='mobile_responsive' id="wptf_table_mobile_responsive" class="wptf_fullwidth wptf_data_filed_atts" >
        <option value="mobile_responsive" <?php echo isset( $meta_mobile['mobile_responsive'] ) && $meta_mobile['mobile_responsive'] == 'mobile_responsive' ? 'selected' : ''; ?>>Default (Yes Responsive)</option>
        <option value="no_responsive" <?php echo isset( $meta_mobile['mobile_responsive'] ) && $meta_mobile['mobile_responsive'] == 'no_responsive' ? 'selected' : ''; ?>>No Responsive</option>
    </select>
    
</div>

<?php
$colums_disable_array = WPT_Product_Table::$colums_disable_array;
$colums_disable_array = array_map(function($value){
   $minus_from_disabledArray = array(
        //'thumbnails',
        //'description',
        'quick',
        'wishlist',
        'quoterequest',
        'Message',
       'attribute',
       'variations',
       'wishlist',
       'quoterequest',
        //'ssss',
    );
    return !in_array($value, $minus_from_disabledArray) ? $value : false;
}, $colums_disable_array);
$colums_disable_array = array_filter($colums_disable_array);
$colums_disable_array[] = 'thumbnails';

if( isset( $meta_mobile['disable'] ) && is_array( $meta_mobile['disable'] ) ){
    $colums_disable_array = $meta_mobile['disable'];
}elseif( isset( $meta_mobile['mobile_responsive'] ) && !isset( $meta_mobile['disable'] ) ){
    $colums_disable_array = array();
}

$meta_column_array = $columns_array = get_post_meta($post->ID, 'column_array', true); //Getting value from updated column tab
if( !$meta_column_array && empty( $meta_column_array ) ){
    $columns_array = WPT_Product_Table::$columns_array;
}
unset($columns_array['product_title']);
unset($columns_array['price']);

unset($columns_array['action']);
unset($columns_array['check']);

//var_dump($meta_mobile['disable'],$colums_disable_array,$columns_array);
?>
<ul id="wptf_keyword_hide_mobile">
    <h1 style="color: #D01040;">Hide On Mobile</h1>
    <p style="padding: 0;margin: 0;">Pleach check you column to hide from Mobile. For all type Table(Responsive or Non-Responsive).</p>
    <hr>
        <?php
    foreach( $columns_array as $keyword => $title ){
        $enabled_class = 'enabled';
        $checked_attribute = ' checked="checked"';
        if( !in_array( $keyword, $colums_disable_array ) ){
            $enabled_class = $checked_attribute = '';
        }
    ?>
    <li class="hide_on_mobile_permits <?php echo $enabled_class; ?> column_keyword_<?php echo $keyword; ?>">
        
        <div class="wptf_mobile_hide_keyword">
            <b  data-column_title="<?php echo $title; ?>" data-keyword="<?php echo $keyword; ?>" class="mobile_issue_field <?php echo $keyword; ?>" type="text" ><?php echo $title; ?></b>
        </div>
        <span title="Move Handle" class="handle checkbox_handle">
            <input  name="mobile[disable][]" value="<?php echo $keyword; ?>"  title="Active Inactive Column" class="checkbox_handle_input <?php echo $enabled_class; ?>" type="checkbox" data-column_keyword="<?php echo $keyword; ?>" <?php echo $checked_attribute; ?>>
        </span>
    </li>
    <?php

    }
    ?>

</ul>
