<?php

add_filter('plugin_action_links_' . WPT_Product_Table::getPath('PLUGIN_BASE_FILE'), 'wptf_add_action_links');
//add_filter('plugin_action_links_woo-product-table-pro/woo-product-table-pro.php', 'wptf_add_action_links');

/**
 * For showing configure or add new link on plugin page
 * It was actually an individual file, now combine at 4.1.1
 * @param type $links
 * @return type
 */
function wptf_add_action_links($links) {
    $wptf_links[] = '<a href="' . admin_url('post-new.php?post_type=wpt_product_table') . '" title="Add new Shortcode">Create Table</a>';
    $wptf_links[] = '<a href="' . admin_url('edit.php?post_type=wpt_product_table&page=woo-product-table-config') . '" title="Configure for Universal">Configure</a>';
    $wptf_links[] = '<a href="https://codeastrology.com/support/" title="CodeAstrology Support" target="_blank">Support</a>';
    //$wptf_links[] = '<a title="See FAQ - How to use." href="' . admin_url('admin.php?page=woo-product-table-faq') . '">FAQ - Shortcode</a>';
    //$links[] = '<a href="' . admin_url( 'options-general.php?page=myplugin' ) . '">Settings</a>';
    return array_merge($wptf_links, $links);
}


/**
 * Set Menu for WPT (Woo Product Table) Plugin
 * It was actually an individual file, now combine  at 4.1.1
 * 
 * @since 1.0
 * 
 * @package Woo Product Table
 */
function wptf_admin_menu() {
    //add_menu_page('WOO Product Table', 'Product Table', 'edit_theme_options', 'woo-product-table', 'wptf_shortcode_generator_page', 'dashicons-list-view',40);
    //add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
    
    add_submenu_page('edit.php?post_type=wpt_product_table', 'Configuration WPTpro', 'Configure', 'edit_theme_options', 'woo-product-table-config', 'wptf_configuration_page');
    add_submenu_page('edit.php?post_type=wpt_product_table', 'WOO Product Table', 'Product Table <span style="color:red;">old</span>', 'edit_theme_options', 'woo-product-table', 'wptf_shortcode_generator_page');
    add_submenu_page('edit.php?post_type=wpt_product_table', 'FAQ & Support page - Contact With US', 'FAQ <span style="color:#ff8921;">& Contact</span>', 'edit_theme_options', 'wptf_fac_contact_page', 'wptf_fac_support_page');
    add_submenu_page('edit.php?post_type=wpt_product_table', 'Get Pro - WPT Product Table', 'Get <strong>Pro</strong>', 'edit_theme_options', 'https://codecanyon.net/item/woo-product-table-pro/20676867');
    //add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
    /************** Old Medu backup *************
    add_menu_page('WOO Product Table', 'Product Table', 'edit_theme_options', 'woo-product-table', 'wptf_faq_page', 'dashicons-list-view');
    add_submenu_page('woo-product-table', 'WOO Product Configuration', 'Configure', 'edit_theme_options', 'woo-product-table-setting', 'wptf_configure_page');
    //************* Old Menu Backup End *************/
}
add_action('admin_menu', 'wptf_admin_menu');