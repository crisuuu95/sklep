<?php
$domain = $_SERVER['HTTP_HOST'];
$plugin_settings = get_option( 'plugin_settings' );
$license_information = get_option( 'license_information' );
$host = $_SERVER['HTTP_HOST'];

$elite_disable = "enabled";
if($license_information['license_valid'] == "false"){
	$elite_disable = "disabled";
}

if(empty($license_information['license_email'])){
	$license_information['license_email'] = "";
}
if(empty($license_information['license_key'])){
	$license_information['license_key'] = "";
}

$versions = array (
	"PHP" => (float)phpversion(),
	"Wordpress" => get_bloginfo('version'),
	"WooCommerce" => WC()->version,
	"WooCommerce Product Feed PRO" => WOOCOMMERCESEA_PLUGIN_VERSION
);

/**
 * Create notification object and get message and message type as WooCommerce is inactive
 * also set variable allowed on 0 to disable submit button on step 1 of configuration
 */
$notifications_obj = new WooSEA_Get_Admin_Notifications;
if (!in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
        $notifications_box = $notifications_obj->get_admin_notifications ( '9', 'false' );
} else {
        $notifications_box = $notifications_obj->get_admin_notifications ( '14', 'false' );
}

if ($versions['PHP'] < 5.6){
        $notifications_box = $notifications_obj->get_admin_notifications ( '11', 'false' );
	$php_validation = "False";
} else {
	$php_validation = "True";
}

if ($versions['WooCommerce'] < 3){
        $notifications_box = $notifications_obj->get_admin_notifications ( '13', 'false' );
}

if (!wp_next_scheduled( 'woosea_cron_hook' ) ) {
	$notifications_box = $notifications_obj->get_admin_notifications ( '12', 'false' );
}

if(array_key_exists('notice', $license_information)){
	if($license_information['notice'] == "true"){
		$notifications_box['message_type'] = $license_information['message_type'];
		$notifications_box['message'] = $license_information['message'];
	}
}

/**
 * Change default footer text, asking to review our plugin
 **/
function my_footer_text($default) {
    return 'If you like our <strong>WooCommerce Product Feed PRO</strong> plugin please leave us a <a href="https://wordpress.org/support/plugin/woo-product-feed-pro/reviews?rate=5#new-post" target="_blank" class="woo-product-feed-pro-ratingRequest">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating. Thanks in advance!';
}
add_filter('admin_footer_text', 'my_footer_text');

                	
//we check if the page is visited by click on the tabs or on the menu button.
//then we get the active tab.
$active_tab = "woosea_manage_settings";
$header_text = "Plugin settings";
if(isset($_GET["tab"])) {
	if($_GET["tab"] == "woosea_manage_settings"){
        	$active_tab = "woosea_manage_settings";
		$header_text = "Plugin settings";
	} elseif ($_GET["tab"] == "woosea_system_check"){
        	$active_tab = "woosea_system_check";
		$header_text = "Plugin systems check";
	} elseif ($_GET["tab"] == "woosea_license_check"){
        	$active_tab = "woosea_license_check";
		$header_text = "License";
     	} else {
             	$active_tab = "woosea_manage_attributes";
		$header_text = "Attribute settings";
		$license_information['message'] = "This plugin, by default, only shows a limit amount of custom attributes in the configuration and filter/rule drop-downs. We have done so for performance reasons. You can however add missing custom attributes by enabling them below. After enabling a custom attribute it shows in the drop-downs during configuration so you can use them for your product feeds.";
	}
}
?>	

<div class="wrap">
 
        <div class="woo-product-feed-pro-form-style-2">
                <tbody class="woo-product-feed-pro-body">
                        <div class="woo-product-feed-pro-form-style-2-heading">
				<span>
				<?php
					print "$header_text";
				?>
				</span>
			</div>
      
			<?php
			if(array_key_exists('message', $license_information)){
			?>
			<div class="<?php _e($license_information['message_type']); ?>">
                                <p><?php _e($license_information['message'], 'sample-text-domain' ); ?></p>
                        </div>
			<?php
			}
			?>


        	    	<!-- wordpress provides the styling for tabs. -->
			<h2 class="nav-tab-wrapper">
                		<!-- when tab buttons are clicked we jump back to the same page but with a new parameter that represents the clicked tab. accordingly we make it active -->
                		<a href="?page=woosea_manage_settings&tab=woosea_manage_settings" class="nav-tab <?php if($active_tab == 'woosea_manage_settings'){echo 'nav-tab-active';} ?> "><?php _e('Plugin settings', 'sandbox'); ?></a>
                		<a href="?page=woosea_manage_settings&tab=woosea_manage_attributes" class="nav-tab <?php if($active_tab == 'woosea_manage_attributes'){echo 'nav-tab-active';} ?>"><?php _e('Attribute settings', 'sandbox'); ?></a>
                		<a href="?page=woosea_manage_settings&tab=woosea_system_check" class="nav-tab <?php if($active_tab == 'woosea_system_check'){echo 'nav-tab-active';} ?>"><?php _e('Plugin systems check', 'sandbox'); ?></a>
                		<a href="?page=woosea_manage_settings&tab=woosea_license_check" class="nav-tab <?php if($active_tab == 'woosea_license_check'){echo 'nav-tab-active';} ?>"><?php _e('License', 'sandbox'); ?></a>
	  		</h2>

			<div class="woo-product-feed-pro-table-wrapper">
				<div class="woo-product-feed-pro-table-left">
					<?php
					if($active_tab == "woosea_manage_settings"){
					?>

			       		<table class="woo-product-feed-pro-table">
                                                <tr><td><strong>Plugin setting</strong></td><td><strong>Off / On</strong></td></tr>

						<form action="" method="post">
						<tr class="<?php print"$elite_disable";?>" id="json_option">
							<td>
								<span>Increase the number of products that will be approved in Google's Merchant Center:<br/>
								This option will fix WooCommerce's (JSON-LD) structured data bug and add extra structured data elements to your pages (<a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_content=structured data bug" target="_blank">Read more about this)</a></span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
								<?php
								$structured_data_fix = get_option ('structured_data_fix');
 	                                                       	if($structured_data_fix == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"fix_json_ld\" name=\"fix_json_ld\" class=\"checkbox-field\" checked $elite_disable>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"fix_json_ld\" name=\"fix_json_ld\" class=\"checkbox-field\" $elite_disable>";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>

						<tr class="<?php print"$elite_disable";?>" id="structured_vat_option">
							<td>
								<span>Exclude TAX from structured data prices</span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
								<?php
								$structured_vat = get_option ('structured_vat');
 	                                                       	if($structured_vat == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"no_structured_vat\" name=\"no_structured_vat\" class=\"checkbox-field\" checked $elite_disable>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"no_structured_vat\" name=\"no_structured_vat\" class=\"checkbox-field\" $elite_disable>";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>

						<tr class="<?php print"$elite_disable";?>" id="identifier_option">
							<td>
								<span>Add GTIN, MPN, UPC, EAN, Product condition, Optimised title, Installment, Unit measure and Brand attributes to your store: (<a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_content=adding fields" target="_blank">Read more about this)</a></span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
                                                        	<?php
								$add_unique_identifiers = get_option ('add_unique_identifiers');
                                                        	if($add_unique_identifiers == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"add_identifiers\" name=\"add_identifiers\" class=\"checkbox-field\" checked $elite_disable>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"add_identifiers\" name=\"add_identifiers\" class=\"checkbox-field\" $elite_disable>";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>
		
						<tr class="<?php print"$elite_disable";?>" id="wpml_option">
							<td>
								<span>Enable WPML support: (<a href="https://adtribes.io/wpml-support/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_content=wpml support" target="_blank">Read more about this)</a></span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
                                                        	<?php
								$add_wpml_support = get_option ('add_wpml_support');
                                                        	if($add_wpml_support == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"add_wpml_support\" name=\"add_wpml_support\" class=\"checkbox-field\" checked $elite_disable>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"add_wpml_support\" name=\"add_wpml_support\" class=\"checkbox-field\" $elite_disable>";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>

						<tr class="<?php print"$elite_disable";?>" id="aelia_option">
							<td>
								<span>Enable Aelia Currency Switcher support: (<a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_content=aelia support" target="_blank">Read more about this)</a></span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
                                                        	<?php
								$add_aelia_support = get_option ('add_aelia_support');
                                                        	if($add_aelia_support == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"add_aelia_support\" name=\"add_aeli_support\" class=\"checkbox-field\" checked $elite_disable>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"add_aelia_support\" name=\"add_aeli_support\" class=\"checkbox-field\" $elite_disable>";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>
						<tr>
							<td>
								<span>Use mother main image for variations</span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
                                                        	<?php
								$add_mother_image = get_option ('add_mother_image');
                                                        	if($add_mother_image == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"add_mother_image\" name=\"add_mother_image\" class=\"checkbox-field\" checked>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"add_mother_image\" name=\"add_mother_image\" class=\"checkbox-field\">";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>

						<tr id="remarketing">
							<td>
								<span>Enable Google Dynamic Remarketing:</span>
							</td>
							<td>
                                                		<label class="woo-product-feed-pro-switch">
                                                        	<?php
								$add_remarketing = get_option ('add_remarketing');
                                                        	if($add_remarketing == "yes"){
                                                                	print "<input type=\"checkbox\" id=\"add_remarketing\" name=\"add_remarketing\" class=\"checkbox-field\" checked>";
							 	} else {
                                                                	print "<input type=\"checkbox\" id=\"add_remarketing\" name=\"add_remarketing\" class=\"checkbox-field\">";
                                                        	}
                                                        	?>
                                                        	<div class="woo-product-feed-pro-slider round"></div>
                                                		</label>
							</td>
						</tr>
						<?php
                                                if($add_remarketing == "yes"){
							$adwords_conversion_id = get_option('woosea_adwords_conversion_id');

							print "<tr id=\"adwords_conversion_id\"><td colspan=\"2\"><span>Insert your Dynamic Remarketing Conversion tracking ID:</span>&nbsp;<input type=\"text\" class=\"input-field-medium\" id=\"adwords_conv_id\" name=\"adwords_conv_id\" value=\"$adwords_conversion_id\">&nbsp;<input type=\"submit\" id=\"save_conversion_id\" value=\"Save\"></td></tr>";	
						}
						?>
						</form>
					</table>
					<?php
					} elseif ($active_tab == "woosea_license_check"){
					?>
                                        <table class="woo-product-feed-pro-table">
                                                <tr>
                                                        <td>
                                                                <span>License e-mail:</span>
                                                        </td>
                                                        <td>
                                                                <input type="text" class="input-field-large" id="license-email" name="license-email" value="<?php print "$license_information[license_email]";?>">
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <td>
                                                                <span>License key:</span>
                                                        </td>
                                                        <td>
                                                                <input type="text" class="input-field-large" id="license-key" name="license-key" value="<?php print "$license_information[license_key]";?>">
                                                        </td>
                                                </tr>
                                                <tr>
                                                        <td colspan="2"><i>Please note that leaving your license details you allow us to automatically validate your license once a day.</i></td>
                                                </tr>
                                                <tr>
                                                        <td colspan="2">
							<?php
							if($license_information['license_valid'] <> "true"){
							?>
                                                                <input type="submit" id="checklicense" value="Activate license">
							<?php
							} else {
							?>
                                                                <input type="submit" id="checklicense" value="License already active">
							<?php
							}
							?>
                                                        </td>
                                                </tr>

                                        </table>
					<?php
					} elseif ($active_tab == "woosea_system_check"){
						// Check if the product feed directory is writeable
						$upload_dir = wp_upload_dir();
						$external_base = $upload_dir['basedir'];
                				$external_path = $external_base . "/woo-product-feed-pro/";
						
						if (is_writable($external_path)) {
							$directory_perm = "True";
						} else {
							$directory_perm = "False";
						}

						// Check if the cron is enabled
						if (!wp_next_scheduled( 'woosea_cron_hook' ) ) {
							$cron_enabled = "False";
						} else {
							$cron_enabled = "True";
						}

						print "<table class=\"woo-product-feed-pro-table\">";
						print "<tr><td><strong>System check</strong></td><td><strong>Status</strong></td></tr>";
						print "<tr><td>WP-Cron enabled</td><td>$cron_enabled</td></tr>";
						print "<tr><td>PHP-version sufficient</td><td>$php_validation ($versions[PHP])</td></tr>";
						print "<tr><td>Product feed directory writable</td><td>$directory_perm</td></tr>";
						print "<tr><td colspan=\"2\">&nbsp;</td></tr>";
						print "</table>";

					} else {
					?>
					<table class="woo-product-feed-pro-table">
						<?php
						if(!get_option( 'woosea_extra_attributes' )){
							$extra_attributes = array();
						} else {
							$extra_attributes = get_option( 'woosea_extra_attributes' );
						}

					       	global $wpdb;
        					$list = array();
        					$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id = posts.id AND posts.post_type LIKE '%product%'
GROUP BY meta.meta_key ORDER BY meta.meta_key ASC;";
        					$data = $wpdb->get_results($sql);

					        if (count($data)) {
                					foreach ($data as $key => $value) {

                        					if (!preg_match("/_product_attributes/i",$value->name)){
                                					$value_display = str_replace("_", " ",$value->name);
                                					$list["custom_attributes_" . $value->name] = ucfirst($value_display);
                        					} else {
                                					$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id = posts.id AND posts.post_type LIKE '%product%' AND meta.meta_key='_product_attributes';";
                                					$data = $wpdb->get_results($sql);
                                					if (count($data)) {
                                        					foreach ($data as $key => $value) {
                                                					$product_attr = unserialize($value->type);
                                                					if(!empty($product_attr)){
												foreach ($product_attr as $key => $arr_value) {
                                                        						$value_display = str_replace("_", "",$arr_value['name']);
                                                        						$list["custom_attributes_" . $key] = ucfirst($value_display);
                                                						}
											}
                                        					}
                                					}
                        					}
                					}
        					}
						print "<tr><td><strong>Attribute name</strong></td><td><strong>On / Off</strong></td></tr>";

						foreach ($list as $key => $value){
							// Trim spaces before and after			
							$value = trim($value);	
	
							if(in_array($value, $extra_attributes,TRUE)){
								$checked = "checked";
							} else {
								$checked = "";
							}

							print "<tr id=\"$key\"><td><span>$value</span></td>";
							print "<td>";
							?>
                                                                <label class="woo-product-feed-pro-switch">
                                                                <input type="hidden" name="manage_attribute" value="<?php print "$key";?>"><input type="checkbox" id="attribute_active" name="<?php print "$value";?>" class="checkbox-field" value="<?php print "$key";?>" <?php print "$checked";?>>
								<div class="woo-product-feed-pro-slider round"></div>
                                                                </label>
							<?php
							print "</td>";
							print "</tr>";
						}
						?>
					</table>
					<?php
					}
					?>
				</div>

				<div class="woo-product-feed-pro-table-right">

				<?php
                                if((empty($license_information['license_valid'])) OR ($license_information['license_valid'] <> "true")){
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
                                                        <a href="https://adtribes.io/pro-vs-elite/?utm_source=<?php print"$host";?>&utm_medium=manage-settings&utm_campaign=why-upgrade-box" target="_blank">Upgrade to Elite here!</a>
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
                                                                <li><strong><a href="https://adtribes.io/support/?utm_source=<?php print"$host";?>&utm_medium=manage-settings&utm_campaign=faq" target="_blank">Frequently Asked Questions</a></strong></li>
                                                                <li><strong><a href="https://www.youtube.com/channel/UCXp1NsK-G_w0XzkfHW-NZCw" target="_blank">YouTube tutorials</a></strong></li>
                                                                <li><strong><a href="https://adtribes.io/blog/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=blog" target="_blank">Blog</a></strong></li>
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
                                                                <li><strong>1. <a href="https://adtribes.io/setting-up-your-first-google-shopping-product-feed/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=first shopping feed" target="_blank">Create a Google Shopping feed</a></strong></li>
                                                                <li><strong>2. <a href="https://adtribes.io/how-to-create-filters-for-your-product-feed/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=how to create filters" target="_blank">How to create filters for your product feed</a></strong></li>
                                                                <li><strong>3. <a href="https://adtribes.io/how-to-create-rules/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=how to create rules" target="_blank">How to set rules for your product feed</a></strong></li>
                                                                <li><strong>4. <a href="https://adtribes.io/add-gtin-mpn-upc-ean-product-condition-optimised-title-and-brand-attributes/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=adding fields" target="_blank">Adding GTIN, Brand, MPN and more</a></strong></li>
                                                                <li><strong>5. <a href="https://adtribes.io/woocommerce-structured-data-bug/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=structured data bug" target="_blank">WooCommerce structured data markup bug</a></strong></li>
                                                                <li><strong>6. <a href="https://adtribes.io/wpml-support/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=wpml support" target="_blank">Enable WPML support</a></strong></li>

                                                                <li><strong>7. <a href="https://adtribes.io/aelia-currency-switcher-feature/?utm_source=<?php print "$host";?>&utm_medium=manage-settings&utm_campaign=aelia support" target="_blank">Enable Aelia currency switcher support</a></strong></li>
						    </ul>
                                                </td>
                                        </tr>
                                </table><br/>
		
				</div>
			</div>
		</tbody>
	</div>
</div>
