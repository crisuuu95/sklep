<?php if ( ! defined( 'ABSPATH' ) ) exit; 
/*
	Plugin Name: Woo Order Splitter
	Plugin URI: https://profiles.wordpress.org/fahadmahmood/#content-plugins
	Description: Enables you to split, consolidate, clone, your crowd/combined/bulk orders using intelligent rules.
	Version: 2.2.6
	Author: Fahad Mahmood
	Author URI: http://androidbubble.com/blog/
	Text Domain: woo-order-splitter
	Domain Path: /languages/	
	License: GPL2
	
	
	This WordPress plugin is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	any later version.
	 
	This WordPress plugin is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	 
	You should have received a copy of the GNU General Public License
	along with this WordPress plugin. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/


	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}else{
		 clearstatcache();
	}
	
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	
	$wc_os_all_plugins = get_plugins();
	$wc_os_active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
	
	if ( array_key_exists('woocommerce/woocommerce.php', $wc_os_all_plugins) && in_array('woocommerce/woocommerce.php', $wc_os_active_plugins) ) {
		
		
		
		
		global $wc_os_data, $wc_os_pro, $wc_os_activated, $wc_os_premium_link, $yith_pre_order, $wc_os_bulk_instantiated, $wc_os_debug;
		
		$wc_os_debug = isset($_GET['wc_os_debug']);
		
		$yith_pre_order = (in_array( 'yith-pre-order-for-woocommerce/init.php',  $wc_os_active_plugins) || in_array( 'yith-woocommerce-pre-order.premium/init.php',  $wc_os_active_plugins));
		
		$wc_os_activated = true;
		
		$wc_os_bulk_instantiated = false;
		
		$wc_os_premium_link = 'http://shop.androidbubbles.com/product/woo-order-splitter';		
		$wc_os_data = get_plugin_data(__FILE__);
		
		
		define( 'WCOS_PLUGIN_DIR', dirname( __FILE__ ) );
		
		$wc_os_pro_file = WCOS_PLUGIN_DIR . '/pro/wcos-pro.php';
		
		
		$wc_os_pro =  file_exists($wc_os_pro_file);
		
		require_once WCOS_PLUGIN_DIR . '/inc/functions.php';
		
		if($wc_os_pro)
		include_once($wc_os_pro_file);
		
		if(is_admin()){
			

			//if(!is_multisite())
			add_action( 'admin_menu', 'wc_os_admin_menu' );	
			//else
			//add_action('network_admin_menu', 'wc_os_menu');
			
			if(function_exists('wc_os_plugin_links')){
				$plugin = plugin_basename(__FILE__); 
				add_filter("plugin_action_links_$plugin", 'wc_os_plugin_links' );	
			}
			
			if(function_exists('wc_os_admin_scripts'))
			add_action( 'admin_enqueue_scripts', 'wc_os_admin_scripts', 99 );	
			
		}
		
	}