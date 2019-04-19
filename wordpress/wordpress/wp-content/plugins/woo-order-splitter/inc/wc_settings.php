<?php defined( 'ABSPATH' ) or die( __('No script kiddies please!', 'woo-order-splitter') );
	if ( !current_user_can( 'administrator' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-order-splitter' ) );
	}

	global $wc_os_data, $wc_os_pro, $wc_os_activated, $wc_os_settings, $wc_os_currency, $wc_os_premium_link;//, $wc_os_active_plugins;
	
	
	//wc_os_pree($wc_os_active_plugins);
	//wc_os_pree($wc_os_activated);
	//wc_os_pree($wc_os_settings);

?>


<div class="wrap wc_settings_div">

        



        <div class="icon32" id="icon-options-general"><br></div><h2><?php echo $wc_os_data['Name']; ?> <?php echo '('.$wc_os_data['Version'].($wc_os_pro?') Pro':')'); ?> - <?php _e("Settings","woo-order-splitter"); ?></h2> 
    
         
           
        <h2 class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active"><?php _e("Products","woo-order-splitter"); ?></a>
            
            <a class="nav-tab"><?php _e("Automatic Settings","woo-order-splitter"); ?></a>
            
            <?php //if($wc_os_pro): ?>
            <a class="nav-tab" id="wc_os_rules"><?php _e("Rules","woo-order-splitter"); ?></a>
            <a class="nav-tab" id="wc_os_meta_keys"><?php _e("Order Meta Keys","woo-order-splitter"); ?></a>
            <?php //endif; ?>
            
            <a class="nav-tab" id="wc_os_meta_keys"><?php _e("Troubleshoot","woo-order-splitter"); ?></a>
        </h2>      



<?php if(!$wc_os_activated): ?>
<div class="wc_os_notes">
<h2><?php _e("You need WooCommerce plugin to be installed and activated.","woo-order-splitter"); ?> <?php _e("Please","woo-order-splitter"); ?> <a href="plugin-install.php?s=woocommerce&tab=search&type=term" target="_blank"><?php _e("Install","woo-order-splitter"); ?></a> <?php _e("and","woo-order-splitter"); ?>/<?php _e("or","woo-order-splitter"); ?> <a href="plugins.php?plugin_status=inactive" target="_blank"><?php _e("Activate","woo-order-splitter"); ?></a> WooCommerce <?php _e("plugin to proceed","woo-order-splitter"); ?>.</h2>
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
<br />
</div>
<?php exit; endif; ?>



<form class="nav-tab-content" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<?php wp_nonce_field( 'wc_os_settings_action', 'wc_os_settings_field' ); ?>


<div class="wc_os_optional">


<h3><?php _e("Optional","woo-order-splitter"); ?></h3>

    <fieldset>

        <ul>

      

        <li <?php echo(get_option('wc_os_billing_off', 0)?'class="selected"':''); ?>>

        <input class="wc_os_checkout_options" id="wc_os_billing_off" name="wc_os_billing_off" type="checkbox" value="1" <?php echo(get_option('wc_os_billing_off', 0)?'checked="checked"':''); ?> /><label for="wc_os_billing_off"><?php _e("Billing Details","woo-order-splitter"); ?> <strong><?php _e("On","woo-order-splitter"); ?></strong>/<strong><?php _e("Off","woo-order-splitter"); ?></strong></label>

        </li>

        <li <?php echo(get_option('wc_os_shipping_off', 0)?'class="selected"':''); ?>>

        <input class="wc_os_checkout_options" id="wc_os_shipping_off" name="wc_os_shipping_off" type="checkbox" value="1" <?php echo(get_option('wc_os_shipping_off', 0)?'checked="checked"':''); ?> /><label for="wc_os_shipping_off"><?php _e("Shipping Details","woo-order-splitter"); ?> <strong><?php _e("On","woo-order-splitter"); ?></strong>/<strong><?php _e("Off","woo-order-splitter"); ?></strong></label>

        </li>

        <li <?php echo(get_option('wc_os_order_comments_off', 0)?'class="selected"':''); ?>>

        <input class="wc_os_checkout_options" id="wc_os_order_comments_off" name="wc_os_order_comments_off" type="checkbox" value="1" <?php echo(get_option('wc_os_order_comments_off', 0)?'checked="checked"':''); ?> /><label for="wc_os_order_comments_off"><?php _e("Order Comments","woo-order-splitter"); ?> <strong><?php _e("On","woo-order-splitter"); ?></strong>/<strong><?php _e("Off","woo-order-splitter"); ?></strong></label>

        </li>
        
        <li <?php echo(get_option('wc_os_order_splitf_column', 0)?'class="selected"':''); ?> <?php echo (!$wc_os_pro?'class="wc_os_premium"':''); ?>>

        <input class="wc_os_checkout_options" <?php echo (!$wc_os_pro?'disabled="disabled"':''); ?> id="wc_os_order_splitf_column" name="wc_os_order_splitf_column" type="checkbox" value="1" <?php echo(get_option('wc_os_order_splitf_column', 0)?'checked="checked"':''); ?> /><label for="wc_os_order_splitf_column"><?php _e('"Split From" Column',"woo-order-splitter"); ?> <strong><?php _e("Off","woo-order-splitter"); ?></strong>/<strong><?php _e("On","woo-order-splitter"); ?></strong></label>

        </li> 
        
        <li <?php echo(get_option('wc_os_order_clonef_column', 0)?'class="selected"':''); ?> <?php echo (!$wc_os_pro?'class="wc_os_premium"':''); ?>>

        <input class="wc_os_checkout_options" <?php echo (!$wc_os_pro?'disabled="disabled"':''); ?> id="wc_os_order_clonef_column" name="wc_os_order_clonef_column" type="checkbox" value="1" <?php echo(get_option('wc_os_order_clonef_column', 0)?'checked="checked"':''); ?> /><label for="wc_os_order_clonef_column"><?php _e('"Parent Order" Column',"woo-order-splitter"); ?> <strong><?php _e("Off","woo-order-splitter"); ?></strong>/<strong><?php _e("On","woo-order-splitter"); ?></strong></label>

        </li>        
        
        
        <li></li><li></li><li></li>

        <li><iframe width="300" height="150" src="https://www.youtube.com/embed/wjClFEeYEzo" style="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></li>          
        
        <li><iframe width="300" height="150" src="https://www.youtube.com/embed/tOT4l7_GCIw" style="" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></li>            
        <li></li>

        </ul>

    </fieldset>


</div>


<br />
<div class="wc_os_notes"></div>


<table border="0">
<tbody>
<?php	
		
		$cloning = in_array('cloning', $wc_os_settings['wc_os_additional']);

?>
<tr>
<td colspan="2"><?php _e('Order cloning is a good feature to test splitter so you can test it well.', 'woo-order-splitter'); ?></td>
</tr>
<tr>
<td><input id="wip-cloning" <?php checked($cloning); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="cloning" /></td>
<td><label for="wip-cloning"><?php _e('Enable order duplication or cloning', 'woo-order-splitter'); ?></label>
</td>
</tr>


<?php	
		
		$disable_split = in_array('split', $wc_os_settings['wc_os_additional']);

?>
<tr>
<td colspan="2"><br />
<br />
<?php _e('Splitting order is a default feature so it is activated by default. You can deactivate it if need.', 'woo-order-splitter'); ?></td>
</tr>
<tr>
<td><input id="wip-split" <?php checked($disable_split); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="split" /></td>
<td><label for="wip-split"><?php _e('Disable split order option', 'woo-order-splitter'); ?></label>
</td>
</tr>



<?php	
		
		$removal = in_array('removal', $wc_os_settings['wc_os_additional']);

?>
<tr>
<td colspan="2"><br />
<br />
<?php _e('Order removal is an optional feature so once order has been splitted so original order should be removed.', 'woo-order-splitter'); ?></td>
</tr>
<tr>
<td><input id="wip-removal" <?php checked($removal); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="removal" /></td>
<td><label for="wip-removal"><?php _e('Enable original order removal on splitting', 'woo-order-splitter'); ?></label>
</td>
</tr>


<?php	
		
		$qty_split = in_array('qty_split', $wc_os_settings['wc_os_additional']);

?>
<tr>
<td colspan="2"><br />
<br />
<?php _e('Split into multiple orders if any item ordered in quantity.', 'woo-order-splitter'); ?></td>
</tr>
<tr>
<td><input id="wip-qty-split" <?php checked($qty_split); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="qty_split" /></td>
<td><label for="wip-qty-split"><?php _e('Enable quantity based splitter', 'woo-order-splitter'); ?></label>
</td>
</tr>
<?php
		$remove_combined = in_array('remove_combined', $wc_os_settings['wc_os_additional']);
?>		

<tr>
<td colspan="2"><br />
<br />
<?php _e('Consolidated/Merged/Combined orders should be removed after action.', 'woo-order-splitter'); ?> - <a href="https://www.youtube.com/watch?v=qrZMZAuv-VU" target="_blank"><?php echo __('Watch Tutorial', 'woo-order-splitter'); ?></a></td>
</tr>
<tr>
<td><input id="wip-combine-remove" <?php checked($remove_combined); ?> type="checkbox" name="wc_os_settings[wc_os_additional][]" value="remove_combined" /></td>
<td><label for="wip-combine-remove"><?php _e('Enable original order removal on consolidation', 'woo-order-splitter'); ?></label>
</td>
</tr>


</tbody>
</table>	

<input type="hidden" name="wc_os_settings[wc_os_additional][]" value="0" />
<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>



</form>

<form class="nav-tab-content hide" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<iframe width="200" height="120" style="float:right; position:absolute; right:0" src="https://www.youtube.com/embed/tOT4l7_GCIw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
<?php wp_nonce_field( 'wc_os_settings_action', 'wc_os_settings_field' ); ?>





<br />
<div class="wc_os_notes"><h3><?php _e('Select an automatic split action:', 'woo-order-splitter'); ?></h3></div>

<div class="wc_os_ahead">
<?php
	//wc_os_pree($wc_os_settings);
?>
<ul>
<li><label for="wc_os_ie_one"><input type="radio" value="default" id="wc_os_ie_one" name="wc_os_settings[wc_os_ie]" <?php checked($wc_os_settings['wc_os_ie']=='default'); ?> /><?php _e('Default', 'woo-order-splitter'); ?> <i>(<?php _e('Perform split action if any of the following product items are found in the order. So every single item in the order will be added in a separate order.', 'woo-order-splitter'); ?>)</i></label></li>

<li><label for="wc_os_ie_two"><input type="radio" value="exclusive" id="wc_os_ie_two" name="wc_os_settings[wc_os_ie]" <?php checked($wc_os_settings['wc_os_ie']=='exclusive'); ?> /><?php _e('Exclusive', 'woo-order-splitter'); ?> <i>(<?php _e('If any of the following product items are found in the order, separate them in new orders exclusively. So each selected item will be in a separate order.', 'woo-order-splitter'); ?>)</i></label></li>

<li><label for="wc_os_ie_three"><input type="radio" value="inclusive" id="wc_os_ie_three" name="wc_os_settings[wc_os_ie]" <?php checked($wc_os_settings['wc_os_ie']=='inclusive'); ?> /><?php _e('Inclusive', 'woo-order-splitter'); ?> <i>(<?php _e('If any of the following product items are found in the order, separate them in a new order inclusively. So selected items will be grouped in another order separately.', 'woo-order-splitter'); ?>)</i></label></li>

<li><label for="wc_os_ie_four"><input type="radio" value="shredder" id="wc_os_ie_four" name="wc_os_settings[wc_os_ie]" <?php checked($wc_os_settings['wc_os_ie']=='shredder'); ?> /><?php _e('Shredder', 'woo-order-splitter'); ?> <i>(<?php _e('If any of the following product items are found in the order, group them in a new order. And other items will be separated.', 'woo-order-splitter'); ?>)</i></label></li>

<?php if(!$wc_os_pro): ?>
</ul>

<div class="wc_os_premium">
<div class="wc_os_notes wc_os_pro "><h5><?php echo __('More automatic split actions', 'woo-order-splitter').' <small>('.__('Premium Feature', 'woo-order-splitter').')</small>'; ?>:</h5>

<ul>
<?php endif; ?>

<li><label for="wc_os_ie_five"><input type="radio" <?php disabled(!$wc_os_pro); ?> value="io" id="wc_os_ie_five" name="wc_os_settings[wc_os_ie]" <?php checked($wc_os_settings['wc_os_ie']=='io' && $wc_os_pro); ?> /><?php echo __('In stock', 'woo-order-splitter').'/'.__('out of stock', 'woo-order-splitter'); ?> <i>(<?php _e('If this option is selected, plugin will separate in stock and out of stock items. So items will be grouped as in stock items in one and remaining items in other order.', 'woo-order-splitter'); ?>)</i></label></li>
</ul>

<?php if(!$wc_os_pro): ?>
</div>
</div>
<?php endif; ?>

</div>

<div class="wc_os_notes"><h3><?php _e('Select products to enable split order action automatically', 'woo-order-splitter'); ?>:</h3></div>
<p><input <?php checked($wc_os_settings['wc_os_all_product']=='all_products')?> id="wc_os_all_product" name="wc_os_settings[wc_os_all_product]" type="checkbox" value="all_products" /> <label for="wc_os_all_product"><?php _e('All products listed and all future products added.','woo-order-splitter'); ?></label></p>
<?php
	$products = wc_os_get_products();
	
	if(!empty($products)){
?>
<table border="0">
<thead>
<th><?php _e('Enable/Disable', 'woo-order-splitter'); ?></th>
<th><?php _e('Stock Status', 'woo-order-splitter'); ?></th>
<th><?php _e('Product Names', 'woo-order-splitter'); ?></th>
<th><?php _e('Actions', 'woo-order-splitter'); ?></th>
</thead>
<tbody>
<?php	
		
		foreach($products as $prod){
			$product = wc_get_product($prod->ID);
			//wc_os_pree($product);
			//wc_os_pree($product->managing_stock().' ~ '.$product->is_in_stock());

			$ticked = in_array($prod->ID, $wc_os_settings['wc_os_products']);

?>

<tr>
<td><input id="wip-<?php echo $prod->ID; ?>" <?php checked($ticked); ?> type="checkbox" name="wc_os_settings[wc_os_products][]" value="<?php echo $prod->ID; ?>" /></td>
<td><label for="wio-<?php echo $prod->ID; ?>"><?php echo $product->managing_stock()?(($product->is_in_stock() && $product->get_stock_quantity()>0)?'<span class="green"><b>'.__('In', 'woo-order-splitter').'</b></span>':'<span class="red">'.__('Out', 'woo-order-splitter').'</span>'):'<small class="faded">'.__('N/A', 'woo-order-splitter').'</small>'; ?></label></td>
<td><label for="wip-<?php echo $prod->ID; ?>"><?php echo $prod->post_title.' '.$wc_os_currency.$product->get_price(); ?></label></td>
<td><a href="<?php echo get_edit_post_link($prod->ID); ?>" target="_blank"><?php _e('Edit', 'woo-order-splitter'); ?></a> - <a href="<?php echo get_permalink($prod->ID); ?>" target="_blank"><?php _e('View', 'woo-order-splitter'); ?></a>
</td>
</tr>
<?php
		}
?>
</tbody>
</table>
<?php    
	}
?>		
<input type="hidden" name="wc_os_settings[wc_os_products][]" value="0" />

<p class="submit"><input type="submit" value="<?php _e('Save Changes', 'woo-order-splitter'); ?>" class="button button-primary" id="submit" name="submit"></p>



</form>





<?php 
if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')): 

	$classObj = new wc_os_bulk_order_splitter;
	$classObj->wc_os_rules();
	$classObj->wc_os_meta_keys();
	
else:
?>
<form class="nav-tab-content hide wc_os_rules" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">


<iframe width="460" height="215" src="https://www.youtube.com/embed/swHpd8-9H-s" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
<iframe width="460" height="215" src="https://www.youtube.com/embed/nX9ir93V-ug" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>

<div class="wc_os_notes"><a href="<?php echo $wc_os_premium_link; ?>" target="_blank"><?php _e('This is a premium feature.', 'woo-order-splitter'); ?></a></div>

</form>
<form class="nav-tab-content hide wc_os_meta_keys" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<div class="wc_os_notes"><a href="<?php echo $wc_os_premium_link; ?>" target="_blank"><?php _e('This is a premium feature.', 'woo-order-splitter'); ?></a></div>
</form>
<?php	
endif; ?>





<form class="nav-tab-content wc_os_console hide" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
<input type="text" name="wc_os_order_id" placeholder="<?php _e('Order ID', 'woo-order-splitter'); ?>" />
<input type="button" name="wc_os_order_test" value="<?php _e('Test', 'woo-order-splitter'); ?>" />

</form>

</div>

<script type="text/javascript" language="javascript">
jQuery(document).ready(function($) {
	

	
});	
</script>

<style type="text/css">
<?php echo implode('', $css_arr); ?>
	#wpfooter{
		display:none;
	}
<?php if(!$wc_os_pro): ?>

	#adminmenu li.current a.current {
		font-size: 12px !important;
		font-weight: bold !important;
		padding: 6px 0px 6px 12px !important;
	}
	#adminmenu li.current a.current,
	#adminmenu li.current a.current span:hover{
		color:#9B5C8F;
	}
	#adminmenu li.current a.current:hover,
	#adminmenu li.current a.current span{
		color:#fff;
	}	
<?php endif; ?>
	.woocommerce-message,
	.update-nag{
		display:none;
	}

</style>
