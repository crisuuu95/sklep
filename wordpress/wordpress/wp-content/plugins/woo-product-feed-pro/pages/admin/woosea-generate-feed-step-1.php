<?php
/**
 * Change default footer text, asking to review our plugin
 **/
function my_footer_text($default) {
    return 'If you like our <strong>WooCommerce Product Feed PRO</strong> plugin please leave us a <a href="https://wordpress.org/support/plugin/woo-product-feed-pro/reviews?rate=5#new-post" target="_blank" class="woo-product-feed-pro-ratingRequest">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Thanks in advance!';
}
add_filter('admin_footer_text', 'my_footer_text');
delete_option( 'woosea_cat_mapping' );
$license_information = get_option( 'license_information' );
$host = $_SERVER['HTTP_HOST'];

/**
 * Create notification object
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications;
$notifications_box = $notifications_obj->get_admin_notifications ( '1', 'false' );

/**
 * Update project configuration 
 */
if (array_key_exists('project_hash', $_GET)){
        $project = WooSEA_Update_Project::get_project_data(sanitize_text_field($_GET['project_hash']));
        $channel_data = WooSEA_Update_Project::get_channel_data(sanitize_text_field($_GET['channel_hash']));
        $manage_project = "yes";

        if(isset($project['WPML'])){
                if ( function_exists('icl_object_id') ) {
                        // Get WPML language
                        global $sitepress;
                        $lang = $project['WPML'];
                        $sitepress->switch_lang($lang);
                }
        }
} else {
        $project = WooSEA_Update_Project::update_project($_POST);
        $channel_data = WooSEA_Update_Project::get_channel_data(sanitize_text_field($_POST['channel_hash']));

        if(isset($project['WPML'])){
                if ( function_exists('icl_object_id') ) {
                        // Get WPML language
                        global $sitepress;
                        $lang = $project['WPML'];
                        $sitepress->switch_lang($lang);
                }
        }
}

function woosea_hierarchical_term_tree($category, $prev_mapped){
	$r = '';

    	$args = array(
        	'parent' 	=> $category,
		'hide_empty'    => false,
        	'no_found_rows' => true,
    	);

    	$next = get_terms('product_cat', $args);
	$nr_categories = count($next);
	$yo = 0;

    	if ($next) {
        	foreach ($next as $sub_category) {
			$yo++;
			$x = $sub_category->term_id;
			$woo_category = $sub_category->name;
                     	$woo_category_id = $sub_category->term_id;
                     	$mapped_category = "";
                    	$mapped_active_class = "input-field-large";
                        $woo_category = preg_replace('/&amp;/','&',$woo_category);

                    	if (array_key_exists($woo_category, $prev_mapped)){
                        	$mapped_category = $prev_mapped[$woo_category];
                             	$mapped_active_class = "input-field-large-active";
			}

			// These are main categories
			if($sub_category->parent == 0){

    				$args = array(
        				'parent' 	=> $sub_category->term_id,
					'hide_empty'    => false,
        				'no_found_rows' => true,
    				);

    				$subcat = get_terms('product_cat', $args);
				$nr_subcats = count($subcat);

				$r .= "<tr class=\"catmapping\">";
            			$r .= "<td><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value='$woo_category'>$woo_category ($sub_category->count)</td>";
				$r .= "<td><input type=\"search\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x\" value=\"$mapped_category\"></td>";
				if(($yo == $nr_categories) AND ($nr_subcats == 0)){
					$r .= "<td><span class=\"copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
				} else {
					if($nr_subcats > 0){
						$r .= "<td><span class=\"dashicons dashicons-arrow-down copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to subcategories\"></span><span class=\"dashicons dashicons-arrow-down-alt copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
					} else {
						$r .= "<td><span class=\"dashicons dashicons-arrow-down-alt copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
					}
				}
				$r .= "</tr>";
			} else {
				$r .= "<tr class=\"catmapping\">";
            			$r .= "<td><input type=\"hidden\" name=\"mappings[$x][rowCount]\" value=\"$x\"><input type=\"hidden\" name=\"mappings[$x][categoryId]\" value=\"$woo_category_id\"><input type=\"hidden\" name=\"mappings[$x][criteria]\" class=\"input-field-large\" id=\"$woo_category_id\" value='$woo_category'>-- $woo_category ($sub_category->count)</td>";
				$r .= "<td><input type=\"search\" name=\"mappings[$x][map_to_category]\" class=\"$mapped_active_class js-typeahead js-autosuggest autocomplete_$x mother_$sub_category->parent\" value=\"$mapped_category\"></td>";
				$r .= "<td><span class=\"copy_category_$x\" style=\"display: inline-block;\" title=\"Copy this category to all others\"></span></td>";
				$r .= "</tr>";
			}
			$r .= $sub_category->term_id !== 0 ? woosea_hierarchical_term_tree($sub_category->term_id, $prev_mapped) : null;
		}
    	}
    	return $r;
}
?>

<div class="wrap">
	<div class="woo-product-feed-pro-form-style-2">
		<div class="woo-product-feed-pro-form-style-2-heading">Category mapping</div>

                <div class="<?php _e($notifications_box['message_type']); ?>">
                       	<p><?php _e($notifications_box['message'], 'sample-text-domain' ); ?></p>
                </div>

              	<div class="woo-product-feed-pro-table-wrapper">
            	<div class="woo-product-feed-pro-table-left">

		<table id="woosea-ajax-mapping-table" class="woo-product-feed-pro-table" border="1">	
			<thead>
            			<tr>
                			<th>Your category <i>(Number of products)</i></th>
					<th><?php print "$channel_data[name]";?> category</th>
					<th></th>
            			</tr>
        		</thead>
       
 			<tbody class="woo-product-feed-pro-body"> 
			<?php 
			// Get already mapped categories
			$prev_mapped = array();
			if(isset($project['mappings'])){
				foreach ($project['mappings'] as $map_key => $map_value){
					if(strlen($map_value['map_to_category']) > 0){
						$map_value['criteria'] = str_replace("\\","",$map_value['criteria']);
						$prev_mapped[$map_value['criteria']] = $map_value['map_to_category'];
					}
				}
			}
			// Display mapping form
			echo woosea_hierarchical_term_tree(0,$prev_mapped);			
			?>
        		</tbody>
                             
 			<form action="" method="post">
   
			<tr>
				<td colspan="3">
                                <input type="hidden" id="channel_hash" name="channel_hash" value="<?php print "$project[channel_hash]";?>">
			  	<?php
                                	if(isset($manage_project)){
                                        ?>
                                             	<input type="hidden" name="project_update" id="project_update" value="yes" />
                                             	<input type="hidden" id="project_hash" name="project_hash" value="<?php print "$project[project_hash]";?>">
                                             	<input type="hidden" name="step" value="100">
                               			<input type="submit" value="Save mappings" />
					<?php
                                      	} else {
                                       	?>
						<input type="hidden" id="project_hash" name="project_hash" value="<?php print "$project[project_hash]";?>">
                		                <input type="hidden" name="step" value="4">
                               			<input type="submit" value="Save mappings" />
					<?php
					}
					?>
				</td>
			</tr>

			</form>

		</table>
		</div>

		<div class="woo-product-feed-pro-table-right">
				<?php
                                if($license_information['license_valid'] <> "true"){
                                ?>
				<table class="woo-product-feed-pro-table">
                                        <tr>
                                                <td><strong>Why upgrade to Elite?</strong></td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Enjoy all priviliges of our Elite features and priority support and upgrade to the Elite version of our plugin now!
                                                        <ul>
                                                                <li><strong>1.</strong> Priority support: get your feeds live faster</li>
                                                                <li><strong>2.</strong> More products approved by Google</li>
                                                                <li><strong>3.</strong> Add GTIN, brand and more fields to your store</li>
                                                                <li><strong>4.</strong> Exclude individual products from your feeds</li>
                                                                <li><strong>5.</strong> WPML support</li>
                                                                <li><strong>6.</strong> Aelia currency switcher support</li>
                                                         </ul>
                                                        <strong>
                                                        <a href="https://adtribes.io/pro-vs-elite/?utm_source=<?php print"$host";?>&utm_medium=page1&utm_campaign=why-upgrade-box" target="_blank">Upgrade to Elite here!</a>
                                                        </strong>
                                                </td>
                                        </tr>
                                </table><br/>
				<?php
				}
				?>

                                <table class="woo-product-feed-pro-table">
                                        <tr>
                                                <td><strong>We’ve got you covered!</strong></td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        Need assistance? Check out our:
                                                        <ul>
                                                                <li><strong><a href="https://adtribes.io/support/?utm_source=<?php print"$host";?>&utm_medium=page1&utm_campaign=faq" target="_blank">Frequently Asked Questions</a></strong></li>
                                                                <li><strong><a href="https://www.youtube.com/channel/UCXp1NsK-G_w0XzkfHW-NZCw" target="_blank">YouTube tutorials</a></strong></li>
                                                                <li><strong><a href="https://adtribes.io/blog/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=blog" target="_blank">Blog</a></strong></li>
                                                        </ul>
                                                        Or just reach out to us at  <strong><a href="https://wordpress.org/support/plugin/woo-product-feed-pro/" target="_blank">the support forum</a></strong> and we'll make sure your product feeds will be up-and-running within no-time.
                                                </td>
                                        </tr>
                                </table><br/>	

                                <table class="woo-product-feed-pro-table">
                                        <tr>
                                                <td><strong>Our latest blog articles</strong></td>
                                        </tr>
                                        <tr>
                                                <td>
                                                        <ul>
                                                                <li><strong>1. <a href="https://adtribes.io/setting-up-your-first-google-shopping-product-feed/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=first shopping feed" target="_blank">Create a Google Shopping feed</a></strong></li>
                                                                <li><strong>2. <a href="https://adtribes.io/how-to-create-filters-for-your-product-feed/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=how to create filters" target="_blank">How to create filters for your product feed</a></strong></li>
                                                                <li><strong>3. <a href="https://adtribes.io/how-to-create-rules/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=how to create rules" target="_blank">How to set rules for your product feed</a></strong></li>
                                                                <li><strong>4. <a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=adding fields" target="_blank">Adding GTIN, Brand, MPN and more</a></strong></li>
                                                                <li><strong>5. <a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=structured data bug" target="_blank">WooCommerce structured data markup bug</a></strong></li>
                                                                <li><strong>6. <a href="https://adtribes.io/wpml-support/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=wpml support" target="_blank">Enable WPML support</a></strong></li>

                                                                <li><strong>7. <a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=<?php print "$host";?>&utm_medium=page1&utm_campaign=aelia support" target="_blank">Enable Aelia currency switcher support</a></strong></li>

                                                        </ul>
                                                </td>
                                        </tr>
                                </table><br/>
                        </div>
        	</div>
	</div>
</div>
