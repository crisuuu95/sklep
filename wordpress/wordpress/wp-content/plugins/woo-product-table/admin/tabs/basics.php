<?php
$wptf_style_file_selection_options = WPT_Product_Table::$style_form_options;


$meta_basics = get_post_meta($post->ID, 'basics', true);
//var_dump($meta_basics);
?>

<?php
    /**
     * To Get Category List of WooCommerce
     * @since 1.0.0 -10
     */
    $args = array(
        'hide_empty' => false, //False from 3.4 
        'orderby' => 'count',
        'order' => 'DESC',
    );

    //WooCommerce Product Category Object as Array
    $wptf_product_cat_object = get_terms('product_cat', $args);
    //var_dump($wptf_product_cat_object);
?>


<div class="wptf_column">
    <label class="wptf_label" for="wptf_product_slugs">Category Includes <small>(Click to choose Categories)</small></label>
    <select name="basics[product_cat_ids][]" data-name="product_cat_ids" id="wptf_product_ids" class="wptf_fullwidth wptf_data_filed_atts" multiple>
        <?php
        foreach ($wptf_product_cat_object as $category) {
            echo "<option value='{$category->term_id}' " . ( is_array($meta_basics['product_cat_ids']) && in_array($category->term_id, $meta_basics['product_cat_ids']) ? 'selected' : false ) . ">{$category->name} - {$category->slug} ({$category->count})</option>";
        }
        ?>
    </select>
</div>


<div class="wptf_column wptf_disable_column">
    <label class="wptf_label">Product ID Exclude (Separate with comma)</label>
    <input name="basics[post_exclude]" data-name="post_exclude" value="<?php echo isset( $meta_basics['post_exclude'] ) ? $meta_basics['post_exclude'] : ''; ?>" class="" type="text" placeholder="Example: 1,2,3,4">
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_product_slugs">Category Exclude <small>(Click to choose Categories)</small></label>
    <select name="basics[cat_explude][]" data-name="cat_explude" id="wptf_product_ids" class="wptf_fullwidth" multiple>
        <?php
        foreach ($wptf_product_cat_object as $category) {
            echo "<option value='{$category->term_id}' " . ( is_array($meta_basics['cat_explude']) && in_array($category->term_id, $meta_basics['cat_explude']) ? 'selected' : false ) . ">{$category->name} - {$category->slug} ({$category->count})</option>";
        }
        ?>
    </select>
</div>


<?php
    $wptf_product_ids_tag = false;
    /**
     * To Get Category List of WooCommerce
     * @since 1.0.0 -10
     */
    $args = array(
        'hide_empty' => true,
        'orderby' => 'count',
        'order' => 'DESC',
    );

    //WooCommerce Product Category Object as Array
    $wptf_product_tag_object = get_terms('product_tag', $args);
    //var_dump($wptf_product_tag_object);
?>


<div class="wptf_column">
    <label class="wptf_label" for="product_tag_ids">Tag Includes <small>(Click to choose Tags)</small></label>
    <select name="basics[product_tag_ids][]" data-name="product_tag_ids" id="product_tag_ids" class="wptf_fullwidth wptf_data_filed_atts" multiple>
        <?php
        foreach ($wptf_product_tag_object as $tags) {
            echo "<option value='{$tags->term_id}' " . ( is_array($meta_basics['product_tag_ids']) && in_array($tags->term_id, $meta_basics['product_tag_ids']) ? 'selected' : false ) . ">{$tags->name} - {$tags->slug} ({$tags->count})</option>";
        }
        ?>
    </select>
</div>

<div class="wptf_column wptf_disable_column">
    <label class="wptf_label wptf_table_ajax_action" for='wptf_table_minicart_position'>Ajax Action (Enable/Disable)</label>
    <select name="basics[ajax_action]" data-name='ajax_action' id="wptf_table_ajax_action" class="wptf_fullwidth" >
        <option value="ajax_active" <?php echo isset( $meta_basics['ajax_action'] ) && $meta_basics['ajax_action'] == 'ajax_active' ? 'selected' : false; ?>>Active Ajax (Default)</option>
        <option value="no_ajax_action" <?php echo isset( $meta_basics['ajax_action'] ) && $meta_basics['ajax_action'] == 'no_ajax_action' ? 'selected' : false; ?>>Disable Ajax Action</option>
    </select>
</div>


<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for='wptf_table_minicart_position'>Mini Cart Position</label>
    <select name="basics[minicart_position]" data-name='minicart_position' id="wptf_table_minicart_position" class="wptf_fullwidth" >
        <option value="top" <?php echo isset( $meta_basics['minicart_position'] ) && $meta_basics['minicart_position'] == 'top' ? 'selected' : false; ?>>Top (Default)</option>
        <option value="bottom" <?php echo isset( $meta_basics['minicart_position'] ) && $meta_basics['minicart_position'] == 'bottom' ? 'selected' : false; ?>>Bottom</option>
        <option value="none" <?php echo isset( $meta_basics['minicart_position'] ) && $meta_basics['minicart_position'] == 'none' ? 'selected' : false; ?>>None</option>
    </select>
</div>



<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_style_file_selection">Select Template</label>
    <select name="basics[template]" data-name="template" id="wptf_style_file_selection"  class="wptf_fullwidth" >
        <?php
        foreach ($wptf_style_file_selection_options as $key => $value) {
            echo "<option value='$key' ";
            echo isset( $meta_basics['template'] ) && $meta_basics['template'] == $key ? 'selected' : '' ;
            echo ">$value</option>";
        }
        ?>
    </select>
</div>


<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for='wptf_table_table_class'>Set a Class name for Table</label>
    <input name="basics[table_class]" value="<?php echo isset( $meta_basics['table_class'] ) ? $meta_basics['table_class'] : ''; ?>" class="" data-name="table_class" type="text" placeholder="Product's Table Class Name (Optional)" id='wptf_table_table_class'>
</div>

<div class="wptf_column">
    <label class="wptf_label" for='wptf_table_temp_number'>Temporary Number for Table</label>
    <input name="basics[temp_number]" class="wptf_data_filed_atts readonly" data-name="temp_number" type="text" placeholder="123" id='wptf_table_temp_number' value="<?php echo isset( $meta_basics['temp_number'] ) ? $meta_basics['temp_number'] : random_int(10, 300); ?>" readonly="readonly">
    <p>This is not very important, But should different number for different shortcode of your table. Mainly to identify each table.</p>
</div>


<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_add_to_cart_text">(Add to cart) Text</label>
    <input name="basics[add_to_cart_text]" class="" data-name="add_to_cart_text" type="text" value="<?php echo isset( $meta_basics['add_to_cart_text'] ) ? $meta_basics['add_to_cart_text'] : __( 'Add to cart', 'wptf_pro' ); ?>" placeholder="Example: Buy" id="wptf_table_add_to_cart_text">
    <p style="color: #006799;padding: 0;margin: 0;">Put a Space (" ") for getting default <b>Add to Cart Text</b></p>
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_added_to_cart_text">Added_to_cart (Added) Text</label>
    <input name="basics[added_to_cart_text]" class="" data-name="added_to_cart_text" type="text" value="<?php echo isset( $meta_basics['added_to_cart_text'] ) ? $meta_basics['added_to_cart_text'] : __( 'Added', 'wptf_pro' ); ?>" placeholder="Example: Added" id="wptf_table_added_to_cart_text">
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_adding_to_cart_text">Added_to_cart (Adding..) Text</label>
    <input name="basics[adding_to_cart_text]"  class="" data-name="adding_to_cart_text" type="text" value="<?php echo isset( $meta_basics['adding_to_cart_text'] ) ? $meta_basics['adding_to_cart_text'] : __( 'Adding..', 'wptf_pro' ); ?>" placeholder="Example: Adding.." id="wptf_table_added_to_cart_text">
</div>

<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_add_to_cart_selected_text">(Add to cart(Selected]) Text</label>
    <input name="basics[add_to_cart_selected_text]"  class="" data-name="add_to_cart_selected_text" type="text" value="<?php echo isset( $meta_basics['add_to_cart_selected_text'] ) ? $meta_basics['add_to_cart_selected_text'] : __( 'Add to Cart (Selected)', 'wptf_pro' ); ?>" placeholder="Example: Add to cart Selected" id="wptf_table_add_to_cart_selected_text">
</div>

<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_check_uncheck_text">(All Check/Uncheck) Text</label>
    <input name="basics[check_uncheck_text]"  class="" data-name="check_uncheck_text" type="text" value="<?php echo isset( $meta_basics['check_uncheck_text'] ) ? $meta_basics['check_uncheck_text'] : __( 'All Check/Uncheck','wptf_pro' ); ?>" placeholder="Example: All Check/Uncheck" id="wptf_table_check_uncheck_text">
</div>
<hr> 
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_author">AuthorID/UserID/VendorID<strong>(Optional)</strong></label>
    <input name="basics[author]"  class="" data-name="author" type="number" value="<?php echo isset( $meta_basics['author'] ) ? $meta_basics['author'] : ''; ?>" placeholder="Author ID/Vendor ID" id="wptf_table_author">
    <p style="color: #006394;">Only AuthorID or AuthorName field for both [AuthorID/UserID/VendorID] or [author_name/username/VendorUserName]. Don't use both.</p>
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_table_author_name">author_name/username/VendorUserName<strong>(Optional)</strong></label>
    <input name="basics[author_name]"  class="" data-name="author_name" type="text" value="<?php echo isset( $meta_basics['author_name'] ) ? $meta_basics['author_name'] : ''; ?>" placeholder="Author username/ Vendor username" id="wptf_table_author_name">
    <p style="color: #006394;">Only AuthorID or AuthorName field for both [AuthorID/UserID/VendorID] or [author_name/username/VendorUserName]. Don't use both.</p>
</div>
