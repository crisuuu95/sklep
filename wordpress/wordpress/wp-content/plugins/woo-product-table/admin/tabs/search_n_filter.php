<?php
$meta_search_n_filter =  get_post_meta($post->ID, 'search_n_filter', true);
?>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_search_box">Advance Search Box</label>
    <select name="search_n_filter[search_box]" data-name='search_box' id="wptf_search_box" class="wptf_fullwidth " >
        <option value="no" <?php echo isset( $meta_search_n_filter['search_box'] ) && $meta_search_n_filter['search_box'] == 'no' ? 'selected' : ''; ?>>No Search Box</option>
        <option value="yes" <?php echo isset( $meta_search_n_filter['search_box'] ) && $meta_search_n_filter['search_box'] == 'yes' ? 'selected' : ''; ?>>Show Search Box</option>
    </select>
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_taxonomy_keywords">Taxonomy Keywords for Advance Search Box (Separate with comma[,])</label>
    <input name="search_n_filter[taxonomy_keywords]" data-name='taxonomy_keywords' id="wptf_taxonomy_keywords" value="<?php echo isset( $meta_search_n_filter['taxonomy_keywords'] ) ?$meta_search_n_filter['taxonomy_keywords'] : 'product_cat,product_tag'; ?>" class="wptf_fullwidth" type="text" value="" placeholder="eg: product_cat,product_tag,color,size">
    <p>There are lot of <a href="https://wordpress.org/plugins/search/Taxonomy/" target="_blank">Taxonomy Creator Plugin available</a> for creating Taxonomy. For Example: You can use <a href="https://wordpress.org/plugins/wck-custom-fields-and-custom-post-types-creator/" target="_blank">Custom Post Types and Custom Fields creator – WCK</a> Plugin.</p>
</div>

<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_filter_box">Mini Filter</label>
    <select name="search_n_filter[filter_box]" data-name='filter_box' id="wptf_filter_box" class="wptf_fullwidth" >
        <option value="no" <?php echo isset( $meta_search_n_filter['filter_box'] ) && $meta_search_n_filter['filter_box'] == 'no' ? 'selected' : ''; ?>>No Filter</option>
        <option value="yes" <?php echo isset( $meta_search_n_filter['filter_box'] ) && $meta_search_n_filter['filter_box'] == 'yes' ? 'selected' : ''; ?>>Filter Show</option>
    </select>
</div>
<div class="wptf_column wptf_disable_column">
    <label class="wptf_label" for="wptf_filter">Taxonomy Keywords for Filter (Separate with comma[,])</label>
    <input name="search_n_filter[filter]" data-name='filter' id="wptf_filter" value="<?php echo isset( $meta_search_n_filter['filter'] ) ?$meta_search_n_filter['filter'] : 'product_cat,product_tag'; ?>" class="wptf_fullwidth" type="text" value="" placeholder="eg: product_cat,product_tag,color,size">
    <p>There are lot of <a href="https://wordpress.org/plugins/search/Taxonomy/" target="_blank">Taxonomy Creator Plugin available</a> for creating Taxonomy. For Example: You can use <a href="https://wordpress.org/plugins/wck-custom-fields-and-custom-post-types-creator/" target="_blank">Custom Post Types and Custom Fields creator – WCK</a> Plugin.</p>
</div>
