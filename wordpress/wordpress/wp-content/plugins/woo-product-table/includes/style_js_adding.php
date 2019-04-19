<?php
/**
 * CSS or Style file add for FrontEnd Section. 
 * 
 * @since 1.0.0
 */
function wptf_style_js_adding(){
    //Custom CSS Style for Woo Product Table's Table (Universal-for all table) and (template-for defien-table)
    wp_enqueue_style( 'wpt-universal', WPT_Product_Table::getPath('BASE_URL') . 'css/universal.css', __FILE__, WPT_Product_Table::getVersion() );
    wp_enqueue_style( 'wpt-template-table', WPT_Product_Table::getPath('BASE_URL') . 'css/template.css', __FILE__, WPT_Product_Table::getVersion() );
    
    
    //jQuery file including. jQuery is a already registerd to WordPress
    wp_enqueue_script('jquery');
    
    ///custom JavaScript for Woo Product Table pro plugin
    wp_enqueue_script( 'wpt-custom-js', WPT_Product_Table::getPath('BASE_URL') . 'js/custom.js', __FILE__, WPT_Product_Table::getVersion(), true );
    
    
    /**
     * rtResponsive jQuery plugin including here.
     * Currently Disable
     * @since 1.5
     */
    //wp_enqueue_style( 'rtResponsiveTables', WPT_Product_Table::getPath('BASE_URL') . 'css/jquery.rtResponsiveTables.min.css', __FILE__, WPT_Product_Table::getVersion() );
    //wp_enqueue_script( 'rtResponsiveTables', WPT_Product_Table::getPath('BASE_URL') . 'js/jquery.rtResponsiveTables.min.js', __FILE__, '1.8.2', true );

    
    /**
     * Select2 CSS file including. 
     * 
     * @since 1.0.3
     */    
    wp_enqueue_style( 'select2', WPT_Product_Table::getPath('BASE_URL') . 'css/select2.min.css', __FILE__, '1.8.2' );
    
    /**
     * Select2 jQuery Plugin file including. 
     * Here added min version. But also available regular version in same directory
     * 
     * @since 1.9
     */
    wp_enqueue_script( 'select2', WPT_Product_Table::getPath('BASE_URL') . 'js/select2.min.js', __FILE__, '4.0.5', true );
}
add_action('wp_enqueue_scripts','wptf_style_js_adding',99);

/**
 * Removed from admin panel at @since 1.5
 * MS and CSS file also added to Dashboard/wp-admin
 * Because, We want to show preview as like Live Preview
 * 
 * @since 1.0.0
 */
//add_action('admin_enqueue_scripts','wptf_style_js_adding',99);