<?php
$meta_search_n_filter =  get_post_meta($post->ID, 'search_n_filter', true);
?>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_search_box">Advance Search Box</label>
    <select data-name='search_box' id="wptf_search_box" class="wptf_fullwidth wptf_data_filed_atts" >
        
        <option value="">Show Search Box (Default)</option>
        <option value="no" selected="selected">No Search Box</option>
    </select>
</div>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_taxonomy_keywords">Taxonomy Keywords for Advance Search Box (Separate with comma[,])</label>
    <input name="search_n_filter[taxonomy_keywords]" data-name='taxonomy_keywords' id="wptf_taxonomy_keywords" value="<?php echo isset( $meta_search_n_filter['taxonomy_keywords'] ) ?$meta_search_n_filter['taxonomy_keywords'] : ''; ?>" class="wptf_fullwidth wptf_data_filed_atts" type="text" value="" placeholder="eg: product_cat,product_tag,color,size">
    <p>There are lot of <a href="https://wordpress.org/plugins/search/Taxonomy/" target="_blank">Taxonomy Creator Plugin available</a> for creating Taxonomy. For Example: You can use <a href="https://wordpress.org/plugins/wck-custom-fields-and-custom-post-types-creator/" target="_blank">Custom Post Types and Custom Fields creator – WCK</a> Plugin.</p>
</div>

<div class="wptf_column">
    <label class="wptf_label" for="wptf_filter_box">Mini Filter</label>
    <select data-name='filter_box' id="wptf_filter_box" class="wptf_fullwidth wptf_data_filed_atts" >
        
        <option value="">Filter Show(Default)</option>
        <option value="no" selected="selected">No Filter</option>
    </select>
</div>
<div class="wptf_column">
    <label class="wptf_label" for="wptf_filter">Taxonomy Keywords for Filter (Separate with comma[,])</label>
    <input name="search_n_filter[filter]" data-name='filter' id="wptf_filter" value="<?php echo isset( $meta_search_n_filter['filter'] ) ?$meta_search_n_filter['filter'] : 'product_cat,product_tag'; ?>" class="wptf_fullwidth wptf_data_filed_atts" type="text" value="" placeholder="eg: product_cat,product_tag,color,size">
    <p>There are lot of <a href="https://wordpress.org/plugins/search/Taxonomy/" target="_blank">Taxonomy Creator Plugin available</a> for creating Taxonomy. For Example: You can use <a href="https://wordpress.org/plugins/wck-custom-fields-and-custom-post-types-creator/" target="_blank">Custom Post Types and Custom Fields creator – WCK</a> Plugin.</p>
</div>

