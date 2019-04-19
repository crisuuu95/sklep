<?php
$meta_conditions =  get_post_meta($post->ID, 'conditions', true);
?>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_table_shorting">Sorting/Order</label>
    <select name="conditions[sort]" data-name='sort' id="wptf_table_shorting" class="wptf_fullwidth wptf_data_filed_atts" >
        
        <option value="ASC" <?php echo isset( $meta_conditions['sort'] ) && $meta_conditions['sort'] == 'ASC' ? 'selected' : ''; ?>>ASCENDING (Default)</option>
        <option value="DESC" <?php echo isset( $meta_conditions['sort'] ) && $meta_conditions['sort'] == 'DESC' ? 'selected' : ''; ?>>DESCENDING</option>
        <option value="random" <?php echo isset( $meta_conditions['sort'] ) && $meta_conditions['sort'] == 'random' ? 'selected' : ''; ?>>Random</option>
    </select>
</div>


<div class="wptf_column">
    <label class="wptf_label" for="wptf_table_sort_order_by">Order By</label>
    <select name="conditions[sort_order_by]" data-name='sort_order_by' id="wptf_table_sort_order_by" class="wptf_fullwidth wptf_data_filed_atts" >
        <option value="name" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'name' ? 'selected' : ''; ?>>Name (Default)</option>
        <option value="menu_order" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'menu_order' ? 'selected' : ''; ?>>Menu Order</option> <!-- default menu_order -->
        
        <option value="meta_value" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'meta_value' ? 'selected' : ''; ?>>Custom Meta Value</option>
        <option value="meta_value_num" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'meta_value_num' ? 'selected' : ''; ?>>Custom Meta Number (if numeric data)</option>
        <option value="date" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'date' ? 'selected' : ''; ?>>Date</option>
        
        <option value="ID" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'ID' ? 'selected' : ''; ?>>ID</option>
        <option value="author" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'author' ? 'selected' : ''; ?>>Author</option>
        <option value="title" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'title' ? 'selected' : ''; ?>>Product Title</option>
        
        <option value="type" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'type' ? 'selected' : ''; ?>>Type</option>
        
        <option value="modified" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'modified' ? 'selected' : ''; ?>>Modified</option>
        <option value="parent" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'parent' ? 'selected' : ''; ?>>Parent</option>
        <option value="rand" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'rand' ? 'selected' : ''; ?>>Rand</option>
        <option value="comment_count" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'comment_count' ? 'selected' : ''; ?>>Reviews/Comment Count</option>
        <option value="relevance" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'relevance' ? 'selected' : ''; ?>>Relevance</option> 
        <option value="none" <?php echo isset( $meta_conditions['sort_order_by'] ) && $meta_conditions['sort_order_by'] == 'none' ? 'selected' : ''; ?>>None</option>
    </select>
</div>
<div style="display: none;" class="wptf_column" id="wptf_meta_value_wrapper">
    <label class="wptf_label" for="wptf_product_meta_value_sort">Meta Value for [Custom Meta Value] of <b>Custom Meta Value</b></label>
    <input name="conditions[meta_value_sort]" value="<?php echo isset( $meta_conditions['meta_value_sort'] ) ?$meta_conditions['meta_value_sort'] : ''; ?>" data-name='meta_value_sort' id="wptf_product_meta_value_sort" class="wptf_fullwidth wptf_data_filed_atts" type="text"  name="wptf_form_array[meta_value_sort]">
    <p style="color: #00aef0;">Type your Right meta value here. EG: '_sku', there should now and space</p>
</div>



<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_product_min_price">Set Minimum Price</label>
    <input name="conditions[min_price]" data-name='min_price' value="<?php echo isset( $meta_conditions['min_price'] ) ?$meta_conditions['min_price'] : ''; ?>" id="wptf_product_min_price" class="wptf_fullwidth " type="number"  name="wptf_form_array[wptf_product_min_price]" pattern="[0-9]*">
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_product_max_price">Set Maximum Price</label>
    <input name="conditions[max_price]" data-name='max_price' value="<?php echo isset( $meta_conditions['max_price'] ) ?$meta_conditions['max_price'] : ''; ?>" id="wptf_product_max_price" class="wptf_fullwidth" type="number"  name="wptf_form_array[wptf_product_max_price]" pattern="[0-9]*">
</div>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_table_description_type">Description Type</label>
    <select name="conditions[description_type]" data-name='description_type' id="wptf_table_description_type" class="wptf_fullwidth wptf_data_filed_atts" >
        <option value="short_description" <?php echo isset( $meta_conditions['description_type'] ) && $meta_conditions['description_type'] == 'short_description' ? 'selected' : ''; ?>>Short Description</option><!-- Default Value -->
        <option value="description" <?php echo isset( $meta_conditions['description_type'] ) && $meta_conditions['description_type'] == 'description' ? 'selected' : ''; ?>>Long Description</option>
    </select>
    <p style="color: #0087be;">Here was <b>description_lenght</b>, But from 3.6, We have removed <b>description_lenght</b>.</p>
</div>

<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_only_stock">Only Stock Product</label>
    <select name="conditions[only_stock]" data-name='only_stock' id="wptf_table_only_stock" class="wptf_fullwidth" >
        <option value="no" <?php echo isset( $meta_conditions['only_stock'] ) && $meta_conditions['only_stock'] == 'no' ? 'selected' : ''; ?>>All Product</option>
        <option value="yes" <?php echo isset( $meta_conditions['only_stock'] ) && $meta_conditions['only_stock'] == 'yes' ? 'selected' : ''; ?>>Yes (Only Stock)</option>
    </select>
</div>


<div class="wptf_column">
    <label class="wptf_label" for="wptf_posts_per_page">Post Limit/Per Load Limit</label>
    <input name="conditions[posts_per_page]" data-name='posts_per_page' value="<?php echo isset( $meta_conditions['posts_per_page'] ) ?$meta_conditions['posts_per_page'] : '20'; ?>" id="wptf_posts_per_page" class="wptf_fullwidth wptf_data_filed_atts" type="number"  name="" pattern="[0-9]*" placeholder="Eg: 50 (for display 20 products" value="20">
</div>

