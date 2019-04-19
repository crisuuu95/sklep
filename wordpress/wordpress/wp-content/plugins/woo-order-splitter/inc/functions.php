<?php if ( ! defined( 'ABSPATH' ) ) exit; 


	function sanitize_wcos_data( $input ) {

		if(is_array($input)){
		
			$new_input = array();
	
			foreach ( $input as $key => $val ) {
				$new_input[ $key ] = (is_array($val)?sanitize_wcos_data($val):sanitize_text_field( $val ));
			}
			
		}else{
			$new_input = sanitize_text_field($input);
		}
		
		return $new_input;
	}	
	
	if(!function_exists('wc_os_pre')){
	function wc_os_pre($data){
			if(isset($_GET['debug'])){
				wc_os_pree($data);
			}
		}	 
	} 	
	if(!function_exists('wc_os_pree')){
	function wc_os_pree($data){
				echo '<pre>';
				print_r($data);
				echo '</pre>';	
		
		}	 
	} 
	

	class wc_os_order_splitter {
		
		/** @var original order ID. */
		public $original_order_id;
		public $processing;
		public $auto_split;
		public $exclude_items;
		public $include_items;
		public $include_items_qty;
		public $general_array;
		public $cron_in_progress;
	
		/**
		 * Fire clone_order function on clone request.
		 */
		
		function __construct() {
			
			$this->processing = true;
			$this->auto_split = false;
			$this->exclude_items = array();
			$this->include_items = array();
			$this->include_items_qty = array();
			$this->general_array = array();
			$this->cron_in_progress = false;
			
			add_action( 'plugins_loaded', array($this, 'duplicationCheck') );
			add_action( 'plugins_loaded', array($this, 'splitCheck') );
			
			
		}
		
		
				
		public function duplicationCheck() {
			
			if (isset($_GET['clone']) && $_GET['clone'] == 'yes' && isset($_GET['_wpnonce'])){// && isset($_GET['clone-session']) && $_GET['clone-session'] == date('Ymhi')) {
				
				if ( is_user_logged_in() ) {
				
					if( current_user_can('manage_woocommerce') && wc_os_order_cloning()) {
				
						add_action('init', array($this, 'clone_order'));
						
					
					} else {
						
						wp_die(__('You do not have permission to complete this action', 'woo-order-splitter'));
						
					}
					
				} else {
				
					wp_die(__('You have to be logged in to complete this action', 'woo-order-splitter'));
					
				}
				
			}
			
		}
		
		public function splitCheck() {
			
			if (isset($_GET['split']) && $_GET['split'] == 'init' && isset($_GET['_wpnonce'])){// && isset($_GET['split-session']) && $_GET['split-session'] == date('Ymhi')) {
				
				if ( is_user_logged_in() ) {
				
					if( current_user_can('manage_woocommerce') && wc_os_order_split()) {
				
						add_action('init', array($this, 'split_order'));
					
					} else {
						
						wp_die(__('You do not have permission to complete this action', 'woo-order-splitter'));
						
					}
					
				} else {
				
					wp_die(__('You have to be logged in to complete this action', 'woo-order-splitter'));
					
				}
				
			}
			
		}	
		
		
		
		
		/**
		 * Create replicated order post and initiate cloned_order_data function.
		 */
	  
						
		public function clone_order($originalorderid = null){
			
			if($this->cron_in_progress)
			return;
			//$currentUser = wp_get_current_user();
			
			$original_order = new WC_Order($originalorderid);
			
			$user_id = $original_order->get_user_id();
			
			$order_data =  array(
				'post_type'     => 'shop_order',
				'post_status'   => 'publish',
				'ping_status'   => 'closed',
				'post_author'   => $user_id,
				'post_password' => uniqid( 'order_' ),
			);
		
			$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
			
			//wc_os_pree('clone_order/order_id'. $order_id);
	
			if ( is_wp_error( $order_id ) ) {
				
				if(!$this->cron_in_progress)
				add_action( 'admin_notices', array($this, 'clone__error'));
			} else {
				$this->cloned_order_data($order_id, $originalorderid);		
			}
			
			return $order_id;
			
		}
		
		/*
			START - 
			07 January 2019
			Automatic Settings Added 
		*/
			
		public function split_order_logic($originalorderid = null){
			
			global $wc_os_settings, $wc_os_pro, $wc_os_debug;
			
			
			//$currentUser = wp_get_current_user();
			
			$split_status = get_post_meta($originalorderid, 'split_status', true);
			
			//wc_os_pree('split_order_logic-1');
			
			if($split_status){
				return;
			}
			
			//wc_os_pree('split_order_logic-2');
			
			$original_order = new WC_Order($originalorderid);
			
			$user_id = $original_order->get_user_id();
			
			//wc_os_pree($original_order);//exit;
			
			$wc_os_all_products = $wc_os_settings['wc_os_all_product'] ? true : false; //flag indicating to all products are subject to splitting
			$wc_os_products = $wc_os_settings['wc_os_products'];
			
			$wc_order_items = array();
			$wc_order_items_qty = array();
			$this->include_items_qty = array();
			
			foreach($original_order->get_items() as $item_id=>$item_data){
				//wc_os_pree($item_id);
				//wc_os_pree($item_data->get_meta_data());
				//wc_os_pree($item_data);
				$formatted_meta_data = $item_data->get_formatted_meta_data();
				$formatted_meta_data = empty($formatted_meta_data)?$item_data->get_meta_data():$formatted_meta_data;
				//_reduced_stock
				//wc_os_pree($formatted_meta_data);//exit;
				//wc_os_pree($item_meta_data);
				
				$wc_order_items[] = $item_data->get_product_id();
				$wc_order_items_qty[$item_data->get_product_id()] = $item_data->get_quantity();
				
				//wc_os_pree($wc_order_items_qty);
				
				if(!empty($formatted_meta_data)){
					$formatted_meta_data = current($formatted_meta_data);
					$formatted_meta_data = (array)$formatted_meta_data;
					//wc_os_pree($formatted_meta_data);
					if(!empty($formatted_meta_data) && !array_key_exists('key', $formatted_meta_data)){
						$formatted_meta_data = current($formatted_meta_data);
					}
					//wc_os_pree($formatted_meta_data);	
					if(!empty($formatted_meta_data) && array_key_exists('key', $formatted_meta_data)){
							
						extract($formatted_meta_data);
						$key = strtolower($key);
						//$key = str_replace(array('*data', '*current_data'), 'key', $key);
						//wc_os_pree($key);
						
						switch($key){
							case 'backordered':
							case '_reduced_stock':
								//wc_os_pree($key);
								//wc_os_pree($value);	
								//$wc_order_items_qty[$item_data->get_product_id()] = 
								$this->include_items_qty[$item_data->get_product_id()] = $value;
							break;							
						}
					
					}
				}
				
				
				//wc_os_pree($item_data->get_product_id());
			}
			//exit;
			if($wc_os_debug)
			wc_os_pree($this->include_items_qty);
			//wc_os_pree($wc_order_items_qty);exit;
			
			//wc_os_pree($wc_os_all_products);exit;
			
			if($wc_os_all_products){
				$wc_order_items_diff = array();
				$wc_order_items_matched = $wc_order_items;
			}else{
				$wc_order_items_diff = array_diff($wc_order_items, $wc_os_products);			
				$wc_order_items_diff = array_filter($wc_order_items_diff);
				
				$wc_order_items_matched = array_intersect($wc_os_products, $wc_order_items);
				$wc_order_items_matched = array_filter($wc_order_items_matched);
			}
			//wc_os_pree($wc_os_settings['wc_os_ie']);exit;
			//wc_os_pree($wc_os_products);
			//wc_os_pree($wc_order_items);
			//wc_os_pree($wc_order_items_diff);
			//wc_os_pree($wc_order_items_matched);exit;
			if(!empty($wc_os_products) && !empty($wc_order_items_diff)){
				//echo ':)';
			}
			
			if(!empty($wc_order_items) && !empty($wc_order_items_matched)){
				
				//echo $expected_orders;
				//wc_os_pree((count($wc_order_items) - count($wc_order_items_matched)));
				$n_plus_1 = (count($wc_order_items) - count($wc_order_items_matched));
				
				
				//wc_os_pree($this->auto_split);
				//wc_os_pree($wc_os_settings['wc_os_ie']);exit;
				
				if($this->auto_split){
					switch($wc_os_settings['wc_os_ie']){
						default:
						case 'default':
							//wc_os_pree($order_id);
							//
						break;	
						case 'exclusive':
						
							//wc_os_pree('exclusive1');
							
							if($n_plus_1){
								
								$this->exclude_items = array();
								$this->include_items = array();
								
								foreach($wc_order_items_matched as $item){
									$this->exclude_items[] = $item;
								}
								
								//wc_os_pree($this->exclude_items);//exit;
								
								$order_data =  array(
									'post_type'     => 'shop_order',
									'post_status'   => 'publish',
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
							
								$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								
								//wc_os_pree($order_data);exit;
								
								//wc_os_pree('order_id:'.$order_id);
						
								if ( is_wp_error( $order_id ) ) {									
									
									
									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'clone__error'));
									
									
									
								} else {
									$this->cloned_order_data($order_id, $originalorderid);	
									
									update_post_meta($order_id, 'split_status', true);
									
									
								}	
								
							}else{
								//wc_os_pree('exclusive2');
							}
							
							
							
							$this->exclude_items = array();
							
							//wc_os_pree($wc_order_items_matched);
							
							if(!empty($wc_order_items_matched)){
								foreach($wc_order_items_matched as $item){
									
									//wc_os_pree($item);
									
									$this->include_items = array();
									
									$this->include_items[] = $item;
									
									//wc_os_pree($this->include_items);//
									
									$order_data =  array(
										'post_type'     => 'shop_order',
										'post_status'   => 'publish',
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
								
									$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
									//wc_os_pree('order_id:'.$order_id);
							
									if ( is_wp_error( $order_id ) ) {
										
										if(!$this->cron_in_progress)
										add_action( 'admin_notices', array($this, 'clone__error'));
									} else {
										$this->cloned_order_data($order_id, $originalorderid);		
										
										update_post_meta($order_id, 'split_status', true);
									}	
																	
								}
							}else{
								//wc_os_pree('exclusive3');
							}
							
							//wc_os_pree('exclusive4');
							//wc_os_pree($this->include_items);exit;
							
						break;	
						case 'inclusive':

							if($n_plus_1){
								
								$this->exclude_items = array();
								$this->include_items = array();
								
								foreach($wc_order_items_matched as $item){
									$this->exclude_items[] = $item;
								}
							
								$order_data =  array(
									'post_type'     => 'shop_order',
									'post_status'   => 'publish',
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
							
								$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								//wc_os_pree('inclusive/order_id'. $order_id);
						
								if ( is_wp_error( $order_id ) ) {
									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'clone__error'));
								} else {
									$this->cloned_order_data($order_id, $originalorderid);		
									
									update_post_meta($order_id, 'split_status', true);
								}	
								
							}
							
							$this->exclude_items = array();
							$this->include_items = array();
							
							foreach($wc_order_items_matched as $item){
								
								$this->include_items[] = $item;
								
							}
								
							$order_data =  array(
								'post_type'     => 'shop_order',
								'post_status'   => 'publish',
								'ping_status'   => 'closed',
								'post_author'   => $user_id,
								'post_password' => uniqid( 'order_' ),
							);
						
							$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
							//wc_os_pree('inclusive/order_id'. $order_id);
					
							if ( is_wp_error( $order_id ) ) {
								
								if(!$this->cron_in_progress)
								add_action( 'admin_notices', array($this, 'clone__error'));
							} else {
								$this->cloned_order_data($order_id, $originalorderid);		
								
								update_post_meta($order_id, 'split_status', true);
							}	
															
						
							
						break;	
						
						
						case 'shredder':

							if($n_plus_1){
								
								$this->exclude_items = array();
								$this->include_items = array();
								
								foreach($wc_order_items_diff as $item){
									$this->exclude_items[] = $item;
								}
								
								//wc_os_pree($this->exclude_items);//exit;
								
								$order_data =  array(
									'post_type'     => 'shop_order',
									'post_status'   => 'publish',
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
							
								$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								
								//wc_os_pree('order_id:'.$order_id);
						
								if ( is_wp_error( $order_id ) ) {									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'clone__error'));
								} else {
									$this->cloned_order_data($order_id, $originalorderid);	
									
									update_post_meta($order_id, 'split_status', true);	
								}	
								
							}
							
							
							
							$this->exclude_items = array();
							
							//wc_os_pree($wc_order_items_matched);
							
							foreach($wc_order_items_diff as $item){
								
								//wc_os_pree($item);
								
								$this->include_items = array();
								
								$this->include_items[] = $item;
								
								//wc_os_pree($this->include_items);//
								
								$order_data =  array(
									'post_type'     => 'shop_order',
									'post_status'   => 'publish',
									'ping_status'   => 'closed',
									'post_author'   => $user_id,
									'post_password' => uniqid( 'order_' ),
								);
							
								$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								//wc_os_pree('order_id:'.$order_id);
						
								if ( is_wp_error( $order_id ) ) {
									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'clone__error'));
								} else {
									$this->cloned_order_data($order_id, $originalorderid);		
									
									update_post_meta($order_id, 'split_status', true);
								}	
																
							}
															
						
							
						break;		
						
						
						case 'io':
							
							if($wc_os_pro && class_exists('wc_os_bulk_order_splitter')){
								
								$classObj = new wc_os_bulk_order_splitter;
								
								$items_io = $classObj->separate_io_items($wc_order_items, $this->include_items_qty);
								
								$this->exclude_items = array();
								$this->include_items = array();
								
								$save_quantity = $this->include_items_qty;
																
																
								if($items_io['in_stock']){ //create order of in-stock items
												
												  //set items to include in order
												  $this->include_items = $items_io['in_stock']['items'];
								
												  //set quantities for items
												  $this->include_items_qty = $items_io['in_stock']['quantity'];
								
												  //create post order data
																$order_data =  array(
																	'post_type'     => 'shop_order',
																	'post_status'   => 'publish',
																	'ping_status'   => 'closed',
																	'post_author'   => $user_id,
																	'post_password' => uniqid( 'order_' ),
																);
								
												  //save order to database
																if(!$wc_os_debug)
																$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								
																if ( is_wp_error( $order_id ) ) {
								
																	if(!$this->cron_in_progress)
																	add_action( 'admin_notices', array($this, 'clone__error'));
																} else { //add data to new post
																	if(!$wc_os_debug)
																	$this->cloned_order_data($order_id, $originalorderid);
																	
																	update_post_meta($order_id, 'split_status', true);
																}
												}
								
												if($items_io['backorder']) //create order of backorder items
												{
												  //set items to include in order
												  $this->include_items = $items_io['backorder']['items'];
								
												  //set quantities for items
												  $this->include_items_qty = $items_io['backorder']['quantity'];
								
												  //create post order data
																$order_data =  array(
																	'post_type'     => 'shop_order',
																	'post_status'   => 'publish',
																	'ping_status'   => 'closed',
																	'post_author'   => $user_id,
																	'post_password' => uniqid( 'order_' ),
																);
								
												  //save order to database
																if(!$wc_os_debug)
																$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								
																if ( is_wp_error( $order_id ) ) {
								
																	if(!$this->cron_in_progress)
																	add_action( 'admin_notices', array($this, 'clone__error'));
																} else { //add data to new post
																	if(!$wc_os_debug) //save order
																	$this->cloned_order_data($order_id, $originalorderid);
																	
																	update_post_meta($order_id, 'split_status', true);
																}
												}
								
												//restore saved quantity
												$this->include_items_qty = $save_quantity;								
								
								
								/*if($wc_os_debug)
								wc_os_pree($this->include_items_qty);
								
								foreach($wc_order_items as $item){
									$product = wc_get_product($item);
									
									$_io_stock_condition = $classObj->_io_stock_condition($product);
									
									//wc_os_pree($_io_stock_condition);	
									if($_io_stock_condition){
										
										$this->include_items[] = $item;
										
										//wc_os_pree($wc_order_items_qty[$item]);
										if(array_key_exists($item, $this->include_items_qty)){
											$get_stock_slice = ($wc_order_items_qty[$item]-$this->include_items_qty[$item]);											
											$this->include_items_qty[$item] = $get_stock_slice;
										}
										
									}else{
										
									}
								}
								
								if($wc_os_debug)
								wc_os_pree($this->include_items_qty);
								//exit;
								
								if(!empty($this->include_items)){
									$order_data =  array(
										'post_type'     => 'shop_order',
										'post_status'   => 'publish',
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
								
									if(!$wc_os_debug)
									$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
							
									if ( is_wp_error( $order_id ) ) {
										
										if(!$this->cron_in_progress)
										add_action( 'admin_notices', array($this, 'clone__error'));
									} else {
										if(!$wc_os_debug)
										$this->cloned_order_data($order_id, $originalorderid);		
									}
								}
								
								$this->include_items_qty = $save_quantity;
								
								
										
								$this->exclude_items = array();
								$this->include_items = array();
								
								if($wc_os_debug)
								wc_os_pree($this->include_items_qty);
								
								if(!empty($this->include_items_qty)){
									foreach($this->include_items_qty as $item=>$qty){								
										$this->include_items[] = $item;								
										$this->include_items_qty[$item] = ($wc_order_items_qty[$item]-$qty);
									}
	
								}
								
								if($wc_os_debug)
								wc_os_pree($this->include_items_qty);//exit;
								
								if(!empty($this->include_items)){	
									$order_data =  array(
										'post_type'     => 'shop_order',
										'post_status'   => 'publish',
										'ping_status'   => 'closed',
										'post_author'   => $user_id,
										'post_password' => uniqid( 'order_' ),
									);
									
									if(!$wc_os_debug)
									$order_id = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
																
									if ( is_wp_error( $order_id ) ) {
										
										if(!$this->cron_in_progress)
										add_action( 'admin_notices', array($this, 'clone__error'));
									} else {
										if(!$wc_os_debug)
										$this->cloned_order_data($order_id, $originalorderid);		
									}										
								}*/
							
							}
						break;											
					
					}		
				}				
				


				update_post_meta($originalorderid, 'split_status', true);

			}else{
				//wc_os_pree('split_order_logic-3');
			}
			
			
			//exit;
			
			
			
		}		
		
		
		/*
			- END
			07 January 2019
			Automatic Settings Added 
		*/			
			
		
	  
		public function split_order($originalorderid = null, $wc_os_products=array()){
			
			//return;
			//pree($_POST);exit;
			
			if(!wc_os_order_split())
			return;
				
			$proceed = true;
			$wc_os_all_products = $wc_os_settings['wc_os_all_product'] ? true : false; //flag indicating to all products are subject to splitting
			
			//wc_os_pree($originalorderid);exit;
			
			if($originalorderid==0){
				$originalorderid = $_GET['order_id'];
				$this->processing = false;
			}
			
			
			//wc_os_pree($originalorderid);exit;
			
			if($originalorderid>0){
				$order_data = wc_get_order( $originalorderid );
				//pree($order_data);exit;
				if(empty($order_data))
				return;
				
				
				
				$split_qty = wc_os_order_qty_split();
				//wc_os_pree($split_qty);exit;
				
				
				$user_id = get_post_meta($originalorderid, '_customer_user', true);
				//pree($user_id);
				$split_status = get_post_meta($originalorderid, 'split_status', true);
				$qty_splitted = get_post_meta($originalorderid, 'qty_splitted', true);
				
				//wc_os_pree($split_status);
				//wc_os_pree($qty_splitted);exit;
				
				$qty_split_check = ($split_qty && !$qty_splitted);//true;
				
				//wc_os_pree($order_data->get_items());
				//wc_os_pree(count($order_data->get_items()));
				//wc_os_pree($split_status);exit;
				
				$multiple_items_check = (count($order_data->get_items())>1 && !$split_status);
				
				//wc_os_pree($qty_split_check);
				//wc_os_pree($multiple_items_check);
				//exit;
				
				if($qty_split_check || $multiple_items_check){
					
				}else{
					return;
				}
				
				
				//wc_os_pree($_POST['wc_os_ps']);
				//wc_os_pree($order_data->get_items());exit;		
				if($wc_os_all_products);
		        else{
					foreach( $order_data->get_items() as $item_key => $item_values ){
						
						if(!$proceed)
						continue;
						
						if($this->processing){
							if(in_array($item_values->get_product_id(), $wc_os_products)){
							}else{
								$proceed = false;
							}
						}				
					}
				}
				
				$valid_process = false;
				
				//pree($_POST['wc_os_ps']);exit;
								
				foreach( $order_data->get_items() as $item_key => $item_values ){
					

					if($item_values->get_product_id() && (empty($_POST['wc_os_ps']) || (!empty($_POST['wc_os_ps']) && in_array($item_key, $_POST['wc_os_ps'])))){
						//pree($item_values->get_product_id());					
						//pree($item_values->get_quantity());
						
						$qty = $item_values->get_quantity();
						
						$qty_check = ($qty_split_check && $qty>1);
						
						if($multiple_items_check || $qty_check){
						}else{
							continue;
						}
						
						
						$item = $item_values->get_data();
						$product_id = $item['product_id'];
						$variation_id = $item['variation_id'];
						//pree($item);exit;

						
						if ($variation_id != 0) {
							$product = new WC_Product_Variation($variation_id);
			
						} else {
							$product = new WC_Product($product_id);	
						}			
						//wc_os_pree($product->get_price());exit;
						$unit_price = $product->get_price();
									
						$order_data =  array(
							'post_type'     => 'shop_order',
							'post_status'   => 'publish',
							'ping_status'   => 'closed',
							'post_author'   => $user_id,
							'post_password' => uniqid( 'order_' ),
						);
						
						
						$wc_pos_order_type = get_post_meta($originalorderid, 'wc_pos_order_type', true);
						$wc_pos_order_type = ($wc_pos_order_type?$wc_pos_order_type:'online');
						
						
						if($qty_check){
							
							//wc_os_pree('split_order/order_id');wc_os_pree($qty);exit;
							
							for($q=1; $q<=$qty; $q++){
								$order_id_new = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								//wc_os_pree('split_order/order_id'. $order_id);exit;
								
								update_post_meta($order_id_new, 'wc_pos_order_type', sanitize_wcos_data($wc_pos_order_type));
								update_post_meta($order_id_new, 'qty_splitted', true);
						
								if ( is_wp_error( $order_id_new ) ) {
									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'split__error'));
								} else {
									
									$this->splitted_order_data($order_id_new, $originalorderid, $product_id, $variation_id, 1, $unit_price);		
									
									$valid_process = true;
									
									if(method_exists($this, 'ywpo_add_order_item_meta')){
										$this->ywpo_add_order_item_meta($item_key, $product);
									}
								}			
												
							}
							
						}elseif($multiple_items_check){
							
								$order_id_new = wp_insert_post( apply_filters( 'woocommerce_new_order_data', $order_data), true );
								//wc_os_pree('split_order/multiple_items_check/order_id'. $order_id);
								
								update_post_meta($order_id_new, 'wc_pos_order_type', sanitize_wcos_data($wc_pos_order_type));
								if ( is_wp_error( $order_id_new ) ) {
									
									if(!$this->cron_in_progress)
									add_action( 'admin_notices', array($this, 'split__error'));
								} else {
									$this->splitted_order_data($order_id_new, $originalorderid, $product_id, $variation_id);		
									
									$valid_process = true;
									
									update_post_meta($order_id_new, 'split_status', true);
									
									if(method_exists($this, 'ywpo_add_order_item_meta')){
										$this->ywpo_add_order_item_meta($item_key, $product);
									}


								}	
								
								global $yith_pre_order;							
								
								if($yith_pre_order && function_exists('wos_update_orders_again'))				
								wos_update_orders_again($order_id_new, $originalorderid);
						}else{
							
						}
					
						
						
						
						
						
											
					}
				}
				
				
				if($valid_process){
					
					//wc_os_pree($originalorderid);exit;
					
					if(wc_os_order_removal() && empty($_POST['wc_os_ps'])){
						//wc_os_pree($multiple_items_check);
						//wc_os_pree($originalorderid);
						//exit;
						wp_trash_post($originalorderid);
					}else{
						update_post_meta($originalorderid, 'split_status', true);
					}
					
					if(is_admin() && !$this->cron_in_progress){
						wp_redirect('edit.php?post_type=shop_order');exit;
					}
				}
				
			}
		}	
		/**
		 * Create new WC_Order and clone all exisiting data
		 */
		
		public function cloned_order_data($order_id, $originalorderid = null, $clone_order=true, $reduce_stock=false){
			
			global $yith_pre_order, $wc_os_debug;
			$order = new WC_Order($order_id);
			
			if ($originalorderid != null) {
				$this->original_order_id = $originalorderid;
			} else {
				$this->original_order_id = $_GET['order_id'];
			}
			
			
			if(!$wc_os_debug)
			update_post_meta($order_id, 'cloned_from', $this->original_order_id);
			
			$original_order = new WC_Order($this->original_order_id);
			
			$order_status = $original_order->get_status();
			
			// Check if Sequential Numbering is installed
			
			if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
				
				// Set sequential order number 
				
				$setnumber = new WC_Seq_Order_Number_Pro;
				$setnumber->set_sequential_order_number($order_id);
				
			}
			
			if(!$wc_os_debug){
			
				$this->clone_order_header($order_id);
				$this->clone_order_billing($order_id);
				$this->clone_order_shipping($order_id);
				
				$this->clone_order_shipping_items($order_id, $original_order);
				$this->clone_order_fees($order, $original_order);
				
				$this->clone_order_coupons($order, $original_order);
			
			}
			
			if($clone_order){
				$this->clone_order_items($order, $original_order);
			}elseif(method_exists($this, 'add_order_items')){
				$this->add_order_items($order);
			}
			
			if(!$wc_os_debug){
				update_post_meta( $order_id, '_payment_method', get_post_meta($this->original_order_id, '_payment_method', true) );
				update_post_meta( $order_id, '_payment_method_title', get_post_meta($this->original_order_id, '_payment_method_title', true) );
			}
			
			// Reduce Order Stock
			if($reduce_stock)
			wc_reduce_stock_levels($order_id);
			
			// POSSIBLE CHANGE? - Set status to on hold as payment is not received		
			if(!$wc_os_debug){
				$order->update_status($order_status); //('on-hold');
				$order->calculate_totals();
			}
			// Set order note of original cloned order

			if($yith_pre_order && function_exists('wos_update_orders_again'))				
			wos_update_orders_again($order_id, $this->original_order_id);

			
			$this->meta_keys_clone_from_to($order_id, $this->original_order_id);//exit;
			
			$order->add_order_note(__('Parent Order').' #'.$this->original_order_id.'');
			
			// Returns success message on clone completion
			if(!$this->cron_in_progress)
			add_action( 'admin_notices', array($this, 'clone__success'));
			//wp_redirect('edit.php?post_type=shop_order');exit;
			
		}
		
		
		public function meta_keys_clone_from_to($order_id_to=0, $order_id_from=0){
			if($order_id_from && $order_id_to){
				$order_id_to_meta = get_post_meta($order_id_to);
				$order_id_to_keys = array_keys($order_id_to_meta);
				
				$order_id_from_meta = get_post_meta($order_id_from);
				//$order_id_from_meta['wpml_language'] = array('de');
				$order_id_from_keys = array_keys($order_id_from_meta);
				
				
				
				//wc_os_pree($order_id_to_keys);
				//wc_os_pree($order_id_from_keys);
				
				$arr_diff = array_diff($order_id_from_keys, $order_id_to_keys);
				//wc_os_pree($arr_diff);
				
				if(!empty($arr_diff)){
					foreach($arr_diff as $diff_key){
						//wc_os_pree($order_id_from_meta[$diff_key]);
						if(array_key_exists($diff_key, $order_id_from_meta)){
							$diff_value = current($order_id_from_meta[$diff_key]);
							update_post_meta($order_id_to, $diff_key, $diff_value);
						}
					}
				}
				
				//exit;
				
			}			
		}
		
		public function splitted_order_data($order_id, $originalorderid = null, $product_id, $variation_id, $qty=false, $_order_total=false, $reduce_stock=false){
			
			global $wc_os_pro, $yith_pre_order;
			
			$order = new WC_Order($order_id);
			
			if ($originalorderid != null) {
				$this->original_order_id = $originalorderid;
			} else {
				$this->original_order_id = $_GET['order_id'];
			}
			
			$original_order = new WC_Order($this->original_order_id);
						
			// Check if Sequential Numbering is installed
			
			if ( class_exists( 'WC_Seq_Order_Number_Pro' ) ) {
				
				// Set sequential order number 
				
				$setnumber = new WC_Seq_Order_Number_Pro;
				$setnumber->set_sequential_order_number($order_id);
				
			}

			update_post_meta($order_id, 'splitted_from', $originalorderid);
			
			$this->clone_order_header($order_id, $_order_total);
			$this->clone_order_billing($order_id);
			$this->clone_order_shipping($order_id);
			
			if ($variation_id != 0) {
				$product = new WC_Product_Variation($variation_id);
			
			} else {
				$product = new WC_Product($product_id);	
			}	
			
			//wc_os_pree('$is_virtual');
			//wc_os_pree($product->is_virtual('yes'));exit;
			$is_virtual = ($product->is_virtual('yes'));//($product->virtual=='yes');			
			
			//wc_os_pree($is_virtual);
			
			if(!$is_virtual) //14/11/2018
			$this->clone_order_shipping_items($order_id, $original_order);
			
			$this->clone_order_fees($order, $original_order);
			
			$this->clone_order_coupons($order, $original_order);
			
			$this->splitted_order_items($order, $original_order, $product_id, $variation_id, $qty, $_order_total);
			
			update_post_meta( $order_id, '_payment_method', get_post_meta($this->original_order_id, '_payment_method', true) );
			update_post_meta( $order_id, '_payment_method_title', get_post_meta($this->original_order_id, '_payment_method_title', true) );
			
			// Reduce Order Stock
			if($reduce_stock)
			wc_reduce_stock_levels($order_id);
			
			// POSSIBLE CHANGE? - Set status to on hold as payment is not received			
			$order_status = $original_order->get_status();
			
			//wc_os_pree($order_status);
			//wc_os_pree($wc_os_pro);
			
			//$order->update_status(($this->processing?'completed':$order_status));
			if($wc_os_pro){
				//START >> 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES
				
				$classObj = new wc_os_bulk_order_splitter;
				
				$order_status_by_rule = $classObj->get_order_status_by_rule($order_id, $product_id);
				
				$order_status = ($order_status_by_rule?$order_status_by_rule:$order_status);

				//END << 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES
				
			}
			
			$order->update_status($order_status);
			//wc_os_pree($order_status);//exit;
			// Set order note of original cloned order
			
			$order->add_order_note(__('Cloned Order from').' #'.$this->original_order_id.'');
			
			$order->calculate_totals();
			$_order_total = $order->calculate_totals();
			update_post_meta( $order_id, '_order_total',  $_order_total);
			// Returns success message on clone completion
			
			if(!$this->cron_in_progress)
			add_action( 'admin_notices', array($this, 'split__success'));
			
			
		}
			
		/**
		 * Duplicate Order Header meta
		 */
		
		public function clone_order_header($order_id, $_order_total=false){
			
			
			//pree($_order_total);
			if($_order_total){
				
			}else{
				
				$_order_total = get_post_meta($this->original_order_id, '_order_total', true);
				
			}
			
			//pree($_order_total);exit;
	
			update_post_meta( $order_id, '_order_shipping', get_post_meta($this->original_order_id, '_order_shipping', true) );
			update_post_meta( $order_id, '_order_discount', get_post_meta($this->original_order_id, '_order_discount', true) );
			update_post_meta( $order_id, '_cart_discount', get_post_meta($this->original_order_id, '_cart_discount', true) );
			update_post_meta( $order_id, '_order_tax', get_post_meta($this->original_order_id, '_order_tax', true) );
			update_post_meta( $order_id, '_order_shipping_tax', get_post_meta($this->original_order_id, '_order_shipping_tax', true) );
			update_post_meta( $order_id, '_order_total',  sanitize_wcos_data($_order_total));
	
			update_post_meta( $order_id, '_order_key', 'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
			update_post_meta( $order_id, '_customer_user', get_post_meta($this->original_order_id, '_customer_user', true) );
			update_post_meta( $order_id, '_order_currency', get_post_meta($this->original_order_id, '_order_currency', true) );
			update_post_meta( $order_id, '_prices_include_tax', get_post_meta($this->original_order_id, '_prices_include_tax', true) );
			update_post_meta( $order_id, '_customer_ip_address', get_post_meta($this->original_order_id, '_customer_ip_address', true) );
			update_post_meta( $order_id, '_customer_user_agent', get_post_meta($this->original_order_id, '_customer_user_agent', true) );
			
		}
		
		/**
		 * Duplicate Order Billing meta
		 */
		
		public function clone_order_billing($order_id){
	
			update_post_meta( $order_id, '_billing_city', get_post_meta($this->original_order_id, '_billing_city', true));
			update_post_meta( $order_id, '_billing_state', get_post_meta($this->original_order_id, '_billing_state', true));
			update_post_meta( $order_id, '_billing_postcode', get_post_meta($this->original_order_id, '_billing_postcode', true));
			update_post_meta( $order_id, '_billing_email', get_post_meta($this->original_order_id, '_billing_email', true));
			update_post_meta( $order_id, '_billing_phone', get_post_meta($this->original_order_id, '_billing_phone', true));
			update_post_meta( $order_id, '_billing_address_1', get_post_meta($this->original_order_id, '_billing_address_1', true));
			update_post_meta( $order_id, '_billing_address_2', get_post_meta($this->original_order_id, '_billing_address_2', true));
			update_post_meta( $order_id, '_billing_country', get_post_meta($this->original_order_id, '_billing_country', true));
			update_post_meta( $order_id, '_billing_first_name', get_post_meta($this->original_order_id, '_billing_first_name', true));
			update_post_meta( $order_id, '_billing_last_name', get_post_meta($this->original_order_id, '_billing_last_name', true));
			update_post_meta( $order_id, '_billing_company', get_post_meta($this->original_order_id, '_billing_company', true));
			
			do_action('clone_extra_billing_fields_hook', $order_id, $this->original_order_id);
			
		}
		
		/**
		 * Duplicate Order Shipping meta
		 */
		
		public function clone_order_shipping($order_id){
	
			update_post_meta( $order_id, '_shipping_country', get_post_meta($this->original_order_id, '_shipping_country', true));
			update_post_meta( $order_id, '_shipping_first_name', get_post_meta($this->original_order_id, '_shipping_first_name', true));
			update_post_meta( $order_id, '_shipping_last_name', get_post_meta($this->original_order_id, '_shipping_last_name', true));
			update_post_meta( $order_id, '_shipping_company', get_post_meta($this->original_order_id, '_shipping_company', true));
			update_post_meta( $order_id, '_shipping_address_1', get_post_meta($this->original_order_id, '_shipping_address_1', true));
			update_post_meta( $order_id, '_shipping_address_2', get_post_meta($this->original_order_id, '_shipping_address_2', true));
			update_post_meta( $order_id, '_shipping_city', get_post_meta($this->original_order_id, '_shipping_city', true));
			update_post_meta( $order_id, '_shipping_state', get_post_meta($this->original_order_id, '_shipping_state', true));
			update_post_meta( $order_id, '_shipping_postcode', get_post_meta($this->original_order_id, '_shipping_postcode', true));
			
			do_action('clone_extra_shipping_fields_hook', $order_id, $this->original_order_id);
		
		}
		
		
		/**
		 * Duplicate Order Fees
		 */
		
		public function clone_order_fees($order, $original_order){
	
			$fee_items = $original_order->get_fees();
	 
			if (empty($fee_items)) {
				
			} else {
				
				foreach($fee_items as $fee_key => $fee_value){
					
					$fee_item  = new WC_Order_Item_Fee();
	
					$fee_item->set_props( array(
						'name'        => $fee_item->get_name(),
						'tax_class'   => $fee_value['tax_class'],
						'tax_status'  => $fee_value['tax_status'],
						'total'       => $fee_value['total'],
						'total_tax'   => $fee_value['total_tax'],
						'taxes'       => $fee_value['taxes'],
					) );
					//pree($fee_item);exit;
					$order->add_item( $fee_item );	 
					
				}
				
			}
	   
		}
		
		/**
		 * Duplicate Order Coupon
		 */
		
		public function clone_order_coupons($order, $original_order){
	
			$coupon_items = $original_order->get_used_coupons();
	
			if (empty($coupon_items)) {
				
			} else {
				
				foreach($original_order->get_items( 'coupon' ) as $coupon_key => $coupon_values){
					
					$coupon_item  = new WC_Order_Item_Coupon();
	
					$coupon_item->set_props( array(
						'name'  	   => $coupon_values['name'],
						'code'  	   => $coupon_values['code'],
						'discount'     => $coupon_values['discount'],
						'discount_tax' => $coupon_values['discount_tax'],
					) );
	
					$order->add_item( $coupon_item );	 
					
				}
				
			}
	   
		}
		

		
		/**
		 * Clone Items - v 1.3
		 */
		
		public function clone_order_items($order, $original_order){
			
			global $wc_os_pro, $yith_pre_order, $wc_os_debug;
			
			$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$order_status = $original_order->get_status();
					
			foreach($original_order->get_items() as $order_key => $values){
			
				if(!empty($this->exclude_items) && in_array($values['product_id'], $this->exclude_items)){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
					continue;
				}
				
				if(!empty($this->include_items) && !in_array($values['product_id'], $this->include_items)){ //07 January 2019 - So we can clone, slice, partially clone and/or partially split an order
					continue;
				}				
				
				//wc_os_pree($values['product_id']);
					
		
				//$order->update_status(($this->processing?'completed':$order_status));
				if($wc_os_pro){
					//START >> 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES				
					$wc_os_rules = get_option('wc_os_rules', array());
					$wc_os_rules = is_array($wc_os_rules)?$wc_os_rules:array();				
					$meta_kv = get_post_meta($values['product_id']);
					$meta_kv = (is_array($meta_kv)?$meta_kv:array());
					//wc_os_pree($wc_os_rules);
					//wc_os_pree($meta_kv);
					
					$cross_match = array_intersect_key($wc_os_rules, $meta_kv);
					//wc_os_pree('$cross_match');
					//wc_os_pree($cross_match);
					
					if(!empty($cross_match)){
						$wc_os_order_statuses = wc_get_order_statuses();
						//wc_os_pree($wc_os_order_statuses);
						$wc_os_order_statuses_keys = array_keys($wc_os_order_statuses);
						//wc_os_pree($wc_os_order_statuses_keys);
						foreach($cross_match as $mk=>$rd){
							if(!empty($rd)){
								foreach($rd as $rk=>$rv){
									if(in_array($rv, $wc_os_order_statuses_keys)){
										$order_status = $rv;
									}
								}
							}
						}
						
						
											
						if($yith_pre_order){
							
							if(array_key_exists('_ywpo_preorder', $meta_kv)){						
								$order_status = 'wc-on-hold';
								update_post_meta( $order_id, '_order_has_preorder', $meta_kv['_ywpo_preorder'][0]);
							}

						}
					}
	
					//END << 05 January 2019 - THIS SECTION IS ADDED TO CONTROL DIFFERENT ORDER STATUSES WITH PRODUCT BASED META KEYS AND VALUES
					
				}
				
				if ($values['variation_id'] != 0) {
					$product = new WC_Product_Variation($values['variation_id']);
				
				} else {
					$product = new WC_Product($values['product_id']);	
				}
				
				$product_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
				
				$item                       = new WC_Order_Item_Product();
				$item->legacy_values        = $values;
				$item->legacy_cart_item_key = $order_key;
				
				$product_qty = (array_key_exists($product_id, $this->include_items_qty)?$this->include_items_qty[$product_id]:$values['quantity']);
				
				$subtotal = ($values['line_subtotal']/$values['quantity']);
				$line_total = ($values['line_total']/$values['quantity']);
				
				
				
				$item->set_props( array(
					'quantity'     => $product_qty,//$values['quantity'],
					'variation'    => $values['variation'],
					'subtotal'     => $subtotal*$product_qty,
					'total'        => $subtotal*$product_qty,
					'subtotal_tax' => $values['line_subtotal_tax'],
					'total_tax'    => $values['line_tax'],
					'taxes'        => $values['line_tax_data'],
				) );
				
				if ( $product ) {
					$item->set_props( array(
						'name'         => $product->get_name(),
						'tax_class'    => $product->get_tax_class(),
						'product_id'   => $product_id,
						'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
					) );
				}
				
				//wc_os_pree($item);//exit;
				
				//if(array_key_exists($product_id, $this->include_items_qty))
				$item->set_backorder_meta();
				
				if($product_qty)
				$order->add_item( $item );
				else{
					//if($wc_os_debug)
					//wc_os_pree('product_qty = '.$product_qty);
				}
			 
			}
			
			//exit;
		}
		
		
		public function splitted_order_items($order, $original_order, $product_id, $variation_id, $qty=false, $total=false){
	
			 foreach($original_order->get_items() as $order_key => $values){
				
				if ($values['variation_id'] != 0) {
					$product = new WC_Product_Variation($values['variation_id']);
	
				} else {
					$product = new WC_Product($values['product_id']);	
				}
				
				$unit_price = $product->get_price();
				
				if(
						($values['variation_id'] != 0 && $variation_id==$values['variation_id'] && $product_id==$values['product_id'])
					||
						($values['variation_id'] == 0 && $product_id==$values['product_id'])
				){
				}else{
					continue;
				}
				
				
				$item                       = new WC_Order_Item_Product();
				$item->legacy_values        = $values;
				$item->legacy_cart_item_key = $order_key;
				
				//wc_os_pree($qty);
				//wc_os_pree($values);exit;
				
				if(!$unit_price)
				$unit_price = ($values['quantity']>1?$values['line_total']/$values['quantity']:$values['line_total']);
				
				$set_props = array(
					'quantity'     => ($qty?$qty:$values['quantity']),
					'variation'    => $values['variation'],					
					'subtotal_tax' => $values['line_subtotal_tax'],
					'total_tax'    => $values['line_tax'],
					'taxes'        => $values['line_tax_data'],
				);
				
				$set_props['subtotal'] = ($total?$total:$unit_price*$set_props['quantity']);
				$set_props['total'] = ($total?$total:$unit_price*$set_props['quantity']);
				
				$item->set_props( $set_props );
				
				if ( $product ) {
					$item->set_props( array(
						'name'         => $product->get_name(),
						'tax_class'    => $product->get_tax_class(),
						'product_id'   => $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id(),
						'variation_id' => $product->is_type( 'variation' ) ? $product->get_id() : 0,
					) );
				}
	
				$item->set_backorder_meta();
				
				//wc_os_pree($item);exit;
				
				$order->add_item( $item );	 
				 
			 }
		}	
		
		/**
		 * Clone success
		 */
		
		function clone__success() {
		
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Order Cloned.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
		}
		
		function split__success() {
		
			$class = 'notice notice-success is-dismissible';
			$message = __( 'Order Splitted.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	
		}	
		
		/**
		 * Clone error
		 */
		
		function merge__error() {
			$class = 'notice notice-error';
			$message = __( 'Consolidation Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
				
		function clone__error() {
			$class = 'notice notice-error';
			$message = __( 'Duplication Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
		
		
		function split__error() {
			$class = 'notice notice-error';
			$message = __( 'Split Failed an error has occurred.', 'woo-order-splitter' );
		
			printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
		}
			
		
		/**
		 * Duplicate Shipping Item Meta
		 * v1.4 - Shipping is added with order items
		 */
		
		public function clone_order_shipping_items($order_id, $original_order){
		 	
			$original_order_shipping_items = $original_order->get_items('shipping');
	
			foreach ( $original_order_shipping_items as $original_order_shipping_item ) {
	
				$item_id = wc_add_order_item( $order_id, array(
					'order_item_name'       => $original_order_shipping_item['name'],
					'order_item_type'       => 'shipping'
				) );
	
				if ( $item_id ) {
					wc_add_order_item_meta( $item_id, 'method_id', $original_order_shipping_item['method_id'] );
					wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $original_order_shipping_item['cost'] ) );
				}
	
			}
		}
		   
	}
	
	new wc_os_order_splitter;


	function wc_os_admin_menu()
	{
		global $wc_os_data;
		
		$title = str_replace('WooCommerce', 'WC', $wc_os_data['Name']);
		add_submenu_page('woocommerce', $title, $title, 'manage_woocommerce', 'wc_os_settings', 'wc_os_settings' );



	}

	function wc_os_settings(){ 



		if ( !current_user_can( 'administrator' ) )  {



			wp_die( __( 'You do not have sufficient permissions to access this page.', 'woo-order-splitter' ) );



		}



		global $wpdb; 

		

				
		include('wc_settings.php');	

		

	}
	
	
	function wc_os_plugin_links($links) { 

		global $wc_os_premium_link, $wc_os_pro;


		$settings_link = '<a href="admin.php?page=wc_os_settings">'.__('Settings', 'woo-order-splitter').'</a>';

		
		if($wc_os_pro){
			array_unshift($links, $settings_link); 
		}else{
			 
			$wc_os_premium_link = '<a href="'.$wc_os_premium_link.'" title="'.__('Go Premium', 'woo-order-splitter').'" target=_blank>'.__('Go Premium', 'woo-order-splitter').'</a>'; 
			array_unshift($links, $settings_link, $wc_os_premium_link); 
		
		}
				
		
		return $links; 
	}
	
	function wc_os_get_products(){
		$args = array(
			'post_type' => 'product',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',

			);
		$results = get_posts( $args );		
		
		return $results;
	}		
	
	function wc_os_settings_refresh(){
		global $wc_os_settings;
		$wc_os_settings = get_option('wc_os_settings', array());		
		$wc_os_settings['wc_os_products'] = (isset($wc_os_settings['wc_os_products']) && is_array($wc_os_settings['wc_os_products']))?$wc_os_settings['wc_os_products']:array();
		$wc_os_settings['wc_os_additional'] = (isset($wc_os_settings['wc_os_additional']) && is_array($wc_os_settings['wc_os_additional']))?$wc_os_settings['wc_os_additional']:array();		
		$wc_os_settings['wc_os_ie'] =(isset($wc_os_settings['wc_os_ie']) && $wc_os_settings['wc_os_ie']!=''?$wc_os_settings['wc_os_ie']:'default');
		//wc_os_pree($wc_os_settings);
	}
	
	function wc_os_crons(){
		
		
		
		global $wc_os_settings, $wc_os_debug;
		
		
		wc_os_settings_refresh();
		
		//wc_os_pree($_POST['wc_os_ps']);exit;
		//wc_os_pree(wc_os_order_split());exit;
		
		if(wc_os_order_split()){
			
			$wc_os_products = $wc_os_settings['wc_os_products'];		
			
			$wc_os_order_splitter_cron = get_option('wc_os_order_splitter_cron', array());
			$wc_os_order_splitter_cron = (is_array($wc_os_order_splitter_cron)?$wc_os_order_splitter_cron:array());
			
			global $wpdb;
			$wc_os_order_key_cron = $wpdb->get_results("SELECT p.ID FROM $wpdb->postmeta mt RIGHT JOIN $wpdb->posts p ON p.ID=mt.post_id AND p.post_type='shop_order' WHERE mt.meta_key='wc_os_order_splitter_cron'");			
			
			//wc_os_pree($wc_os_order_key_cron);exit;
			
			if($wc_os_debug)
			wc_os_pree($wc_os_order_key_cron);
			
			if(!empty($wc_os_order_key_cron)){
				foreach($wc_os_order_key_cron as $all_crons_items){
					//wc_os_pree($all_crons_items->ID);
					if(!array_key_exists($all_crons_items->ID, $wc_os_order_splitter_cron)){
						$wc_os_order_splitter_cron[$all_crons_items->ID] = true;
						
						if(!$wc_os_debug)
						delete_post_meta($all_crons_items->ID, 'wc_os_order_splitter_cron');
					}
				}
			}
				
			
			$wc_os_order_splitter_cron_clear = get_option('wc_os_order_splitter_cron_clear', array());
			$wc_os_order_splitter_cron_clear = (is_array($wc_os_order_splitter_cron_clear)?$wc_os_order_splitter_cron_clear:array());
			
			
			$wc_os_order_splitter = new wc_os_order_splitter;
			
			
			
			//wc_os_pree($wc_os_order_splitter_cron);exit;
			//$wc_os_order_splitter_cron[348] = true;
			
			//$a = 0;
						
			if(!empty($wc_os_order_splitter_cron)){
				
				
				//wc_os_pree($wc_os_order_splitter_cron);exit;
				
				if($wc_os_debug)
				wc_os_pree($wc_os_order_splitter_cron);				
				
				foreach($wc_os_order_splitter_cron as $order_id => $auto_split){
					
					$wc_os_order_splitter->cron_in_progress = true;
					
					$wc_os_order_splitter->auto_split = ($auto_split?true:false);
				
					//wc_os_pree($auto_split);exit;
					//wc_os_pree($wc_os_order_splitter->auto_split);
					//wc_os_pree($order_id);exit;
									
					if($auto_split){	
						//echo $order_id.'<br /><br />';
						
						//exit;
						//if(!$a){
							switch($wc_os_settings['wc_os_ie']){
								default:
									$wc_os_order_splitter->split_order_logic($order_id);//, $wc_os_products);
								break;
								case 'default':
									//wc_os_pree($order_id);
									//
									$wc_os_order_splitter->split_order($order_id);
								break;								
							}
							
							//$a++;
							
						//}
						//exit;
						
						//wc_os_pree('split_order_logic');
						
					}else{
						
						//wc_os_pree('wc_os_crons');exit;
						//wc_os_pree($order_id);wc_os_pree($wc_os_products);exit;
						
						//1 MARCH, 2019
						//if(!$wc_os_debug)
						//$wc_os_order_splitter->split_order($order_id, $wc_os_products);
						//else
						//wc_os_pree('split_order');
					}
					
					//if(wc_os_order_removal()){ //WE ARE CLONING SO WE HAVE TO REMOVE IT, NO NEED TO CHECK REMOVAL OPTION
						$wc_os_order_splitter_cron_clear[$order_id] = time(); //24/01/2019
					//}

					unset($wc_os_order_splitter_cron[$order_id]);
					
					$wc_os_order_splitter->cron_in_progress = false;
					
				}
				
				if(!$wc_os_debug)
				update_option('wc_os_order_splitter_cron', $wc_os_order_splitter_cron);
				else
				wc_os_pree('update_option: wc_os_order_splitter_cron');
				
				
			} 
			
			//wc_os_pree($wc_os_order_splitter_cron_clear);
			
			if(!empty($wc_os_order_splitter_cron_clear)){
				
				foreach($wc_os_order_splitter_cron_clear as $order_id => $timestamp){
					
					$mins = ((time()-$timestamp)/60);
					
					//if(wc_os_order_removal() && 
					//if($mins>=1){
						//$order_items_check = new WC_Order($order_id);
						//wc_os_pree(count($order_items_check->get_items()));
						//wc_os_pree($order_items_check->get_items());exit;
						$split_status = get_post_meta($order_id, 'split_status', true);
						//wc_os_pree($split_status);
						//exit;
						if(!$wc_os_debug){
							if($split_status && wc_os_order_removal())
							wp_trash_post($order_id); //24/01/2019
						}
						
						unset($wc_os_order_splitter_cron_clear[$order_id]);
					//}									
				}
				
				if(!$wc_os_debug)
				update_option('wc_os_order_splitter_cron_clear', $wc_os_order_splitter_cron_clear);
				else
				wc_os_pree('update_option: wc_os_order_splitter_cron_clear');
				
				
				
			}
			
			//exit;
			
		}
		
		//if($wc_os_debug)
		//exit;
							
	}

	function wc_os_settings_update(){
		
		if(isset($_GET['post_type']) && $_GET['post_type']=='shop_order')
		wc_os_crons();
		
		
		
		if(!empty($_POST) && isset($_POST['wc_os_settings'])){
			 
			global $wc_os_currency, $wc_os_settings;
			$wc_os_currency = get_woocommerce_currency_symbol();

			wc_os_settings_refresh();
			//wc_os_pree($_POST);exit;
			if ( 
				! isset( $_POST['wc_os_settings_field'] ) 
				|| ! wp_verify_nonce( $_POST['wc_os_settings_field'], 'wc_os_settings_action' ) 
			) {
			
			   _e('Sorry, your nonce did not verify.');
			   exit;
			
			} else {
			
			   // process form data
			   
			   		$wc_os_additional = (isset($_POST['wc_os_settings']['wc_os_additional']));			   
					
					$wc_os_settings_updated = wc_os_sanitize_text_or_array_field($_POST['wc_os_settings'] );
					//wc_os_pree($wc_os_settings);
					//wc_os_pree($wc_os_settings_updated);exit;
					
					$wc_os_settings_updated['wc_os_products'] = is_array($wc_os_settings_updated['wc_os_products'])?$wc_os_settings_updated['wc_os_products']:$wc_os_settings['wc_os_products'];
					
					$wc_os_settings_updated['wc_os_ie'] = isset($wc_os_settings_updated['wc_os_ie'])?$wc_os_settings_updated['wc_os_ie']:$wc_os_settings['wc_os_ie'];
					
					
					$wc_os_settings_updated['wc_os_additional'] = is_array($wc_os_settings_updated['wc_os_additional'])?$wc_os_settings_updated['wc_os_additional']:$wc_os_settings['wc_os_additional'];					
					
					/*
					if isset() has more than one variable submitted to it, 
					then all variables must return true for for isset to be true.
					So isset($a, $b) is the same as isset($a) && isset($b).
					The reason I am checking both variables is because if 
					$wc_os_settings_updated['wc_os_all_product'] is not checked, 
					it is not submitted with the form.
					Since it is on the same form as $wc_os_settings_updated['wc_os_ie'], 
					I can test to see if that field has been submitted to verify that the form is
					telling us that $wc_os_settings_updated['wc_os_all_product'] is unchecked.
					http://php.net/manual/en/function.isset.php
					*/
					$wc_os_settings_updated['wc_os_all_products'] = isset($wc_os_settings_updated['wc_os_all_product'],$wc_os_settings_updated['wc_os_ie'])?true:$wc_os_settings['wc_os_all_product']; 
					
					
					update_option('wc_os_settings', sanitize_wcos_data($wc_os_settings_updated));
			  
					//add_action( 'admin_notices', 'wc_os_admin_notice_success' );
					
					//pree($_POST);
					$wc_os_billing_off = (isset($_POST['wc_os_billing_off'])?sanitize_wcos_data($_POST['wc_os_billing_off']):($wc_os_additional?0:get_option('wc_os_billing_off', 0)));
			
					$wc_os_shipping_off = (isset($_POST['wc_os_shipping_off'])?sanitize_wcos_data($_POST['wc_os_shipping_off']):($wc_os_additional?0:get_option('wc_os_shipping_off', 0)));
			
					$wc_os_order_comments_off = (isset($_POST['wc_os_order_comments_off'])?sanitize_wcos_data($_POST['wc_os_order_comments_off']):($wc_os_additional?0:get_option('wc_os_order_comments_off', 0)));
					
					$wc_os_order_splitf_column = (isset($_POST['wc_os_order_splitf_column'])?sanitize_wcos_data($_POST['wc_os_order_splitf_column']):($wc_os_additional?0:get_option('wc_os_order_splitf_column', 0)));
					
					$wc_os_order_clonef_column = (isset($_POST['wc_os_order_clonef_column'])?sanitize_wcos_data($_POST['wc_os_order_clonef_column']):($wc_os_additional?0:get_option('wc_os_order_clonef_column', 0)));
					//pree($wc_os_order_splitf_column);

					//pree($_POST);exit;
					update_option( 'wc_os_billing_off', $wc_os_billing_off );
					update_option( 'wc_os_shipping_off', $wc_os_shipping_off );
					update_option( 'wc_os_order_comments_off', $wc_os_order_comments_off );
					update_option( 'wc_os_order_splitf_column', $wc_os_order_splitf_column );
					update_option( 'wc_os_order_clonef_column', $wc_os_order_clonef_column );
					
					//pree($wc_os_billing_off);
					//exit;
										
			   		wc_os_settings_refresh();
			   
			}
			
			
		}
		
	
		
	}
	
	add_action('admin_init', 'wc_os_settings_update');	
	
	
	
	
	function wc_os_init(){
		
		global $wc_os_currency, $wc_os_settings, $wc_os_activated;
		
		
		
		if(!$wc_os_activated)
		return;
		
		
		$wc_os_currency = get_woocommerce_currency_symbol();
		wc_os_settings_refresh();
		
		
		//wc_os_pree($wc_os_settings);
		
		//add_action('init', array($this, 'split_order'));
		
		
		
		
	}
	
	add_action('init', 'wc_os_init');	
	
	
	add_action('woocommerce_order_status_pending', 'wc_os_checkout_order_processed');
	add_action('woocommerce_order_status_on-hold', 'wc_os_checkout_order_processed');
	add_action('woocommerce_order_status_processing', 'wc_os_checkout_order_processed');
	add_action('woocommerce_order_status_completed', 'wc_os_checkout_order_processed');
	
	function wc_os_checkout_order_processed($order_id){
		//return;
		//wc_os_pree($_POST['wc_os_ps']);exit;
		//wc_os_pree($_REQUEST);exit;
		if(
				is_admin() 
			&& 
			(
					((isset($_GET['clone']) && $_GET['clone'] == 'yes') || (isset($_REQUEST['post']) && $_REQUEST['post_type']=='shop_order' && $_REQUEST['action']=='clone'))
				||
					((isset($_REQUEST['post']) && $_REQUEST['post_type']=='shop_order' && $_REQUEST['action']=='split'))
			)
		){
			return;
		}
		

		order_details_page_saved( $order_id, true );
	}
		
	function order_details_page_saved( $order_id, $order_status=false ) {
		
		$order_details_page = isset($_POST['wc_os_ps']);
		
		//wc_os_pree($order_details_page);exit;
		
		// If this is just a revision, don't send the email.
		if ( wp_is_post_revision( $order_id ))
		return;
		
		if ( !$order_status && !$order_details_page )
		return; 
			
		global $wc_os_settings;
		wc_os_settings_refresh();
		
		//wc_os_pree($_POST['wc_os_ps']);exit;
		//wc_os_pree(wc_os_order_split());
		//wc_os_pree($wc_os_settings);
		//exit;
		
		if(wc_os_order_split()){
			$wc_os_all_products = $wc_os_settings['wc_os_all_product'] ? true : false; //flag indicating to all products are subject to splitting
			$wc_os_products = $wc_os_settings['wc_os_products'];
			
			//wc_os_pree($wc_os_all_products);
			//exit;
			
			//wc_os_pree($_POST['wc_os_ps']);exit;
			
			if($order_details_page){
				$_POST['wc_os_ps'] = (!empty($_POST['wc_os_ps'])?$_POST['wc_os_ps']:array());
				$_POST['wc_os_ps'] = array_filter($_POST['wc_os_ps'], 'is_numeric');
				
			}
			//wc_os_pree($wc_os_order_splitter->cron_in_progress);exit;
			//wc_os_pree($_POST['wc_os_ps']);//exit;
			//wc_os_pree(!empty($wc_os_products) && count($wc_os_products)>1);
			
			if(
					$wc_os_all_products
				||
					(!empty($wc_os_products) && count($wc_os_products)>1) 
				||
					$order_details_page
				
			){
				//wc_os_pree($order_details_page);
				//wc_os_pree($wc_os_all_products);exit;
				
				//wc_os_pree($order_id);
				//wc_os_pree($_POST['wc_os_ps']);exit;
				
				$wc_os_order_splitter_cron = get_option('wc_os_order_splitter_cron', array());
				$wc_os_order_splitter_cron = (is_array($wc_os_order_splitter_cron)?$wc_os_order_splitter_cron:array());
				
				$wc_os_order_splitter = new wc_os_order_splitter;				
				//wc_os_pree($wc_os_settings['wc_os_ie']);exit;
				
				switch($wc_os_settings['wc_os_ie']){
					
						default:
						case 'default':
						
							//wc_os_pree('order_details_page_saved');exit;
							
							if($order_details_page)
							$wc_os_order_splitter->split_order($order_id, $wc_os_products);
							elseif(!$wc_os_order_splitter->cron_in_progress)
							$wc_os_order_splitter_cron[$order_id] = ($wc_os_all_products?true:false);
							
						break;	
						
						case 'exclusive':
						case 'inclusive':
						case 'shredder':
						case 'io':
							//$this->auto_split = true;
							//$wc_os_order_splitter->split_order_logic($order_id, $wc_os_products);
							$wc_os_order_splitter_cron[$order_id] = true;
							
						break;
						
				}

				if(!is_admin() && !$wc_os_order_splitter->cron_in_progress)	
				update_post_meta($order_id, 'wc_os_order_splitter_cron', true);
				
				//wc_os_pree($wc_os_order_splitter_cron);exit;
				
				if(!is_admin() && !$wc_os_order_splitter->cron_in_progress){
					update_option('wc_os_order_splitter_cron', $wc_os_order_splitter_cron);
				}else{
					
				}
				
				
				
			}
		}		
	
	}
	add_action( 'save_post', 'order_details_page_saved' );	
	
	function wc_os_sanitize_text_or_array_field($array_or_string) {
		if( is_string($array_or_string) ){
			$array_or_string = sanitize_text_field($array_or_string);
		}elseif( is_array($array_or_string) ){
			foreach ( $array_or_string as $key => &$value ) {
				if ( is_array( $value ) ) {
					$value = wc_os_sanitize_text_or_array_field($value);
				}
				else {
					$value = sanitize_text_field( $value );
				}
			}
		}
	
		return $array_or_string;
	}
		
		
	function wc_os_admin_scripts() {
		
		global $wc_os_css_arr;
		
		
		wp_register_style('wos-admin', plugins_url('css/admin-style.css', dirname(__FILE__)));
		
		
		wp_enqueue_style( 'wos-admin' );
		
		wp_enqueue_script(
			'wos_scripts',
			plugins_url('js/admin-scripts.js', dirname(__FILE__)),
			array('jquery'),
			time()
		);		
		$translation_array = array(
			'defined_rules_confirm' => __('Are you sure, you want delete this rule?', 'woo-order-splitter'),
			'this_url' => admin_url( 'admin.php?page=wc_os_settings' )
		);
		wp_localize_script( 'wos_scripts', 'wos_obj', $translation_array );
		
		
		
	}		
	
	add_filter( 'woocommerce_checkout_fields' , 'wc_os_override_checkout_fields' );
	
	
	
	function wc_os_override_checkout_fields( $fields ) {
	
		if(get_option('wc_os_shipping_off', 0)){
			unset($fields['shipping']['shipping_first_name']);
			unset($fields['shipping']['shipping_last_name']);
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_address_1']);
			unset($fields['shipping']['shipping_address_2']);
			unset($fields['shipping']['shipping_city']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_country']);
			unset($fields['shipping']['shipping_state']);
			unset($fields['shipping']['shipping_phone']);	
			unset($fields['shipping']['shipping_address_2']);
			unset($fields['shipping']['shipping_postcode']);
			unset($fields['shipping']['shipping_company']);
			unset($fields['shipping']['shipping_last_name']);
			unset($fields['shipping']['shipping_email']);
			unset($fields['shipping']['shipping_city']);	
		}
		
		if(get_option('wc_os_billing_off', 0)){
			unset($fields['billing']['billing_first_name']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_address_1']);
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_city']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_country']);
			unset($fields['billing']['billing_state']);
			unset($fields['billing']['billing_phone']);	
			unset($fields['billing']['billing_address_2']);
			unset($fields['billing']['billing_postcode']);
			unset($fields['billing']['billing_company']);
			unset($fields['billing']['billing_last_name']);
			unset($fields['billing']['billing_email']);
			unset($fields['billing']['billing_city']);
		}
		
		if(get_option('wc_os_order_comments_off', 0))
		unset($fields['order']['order_comments']);
		
		return $fields;
	}	
	
	function wc_os_header_scripts(){
		//global $post;
?>
	<style type="text/css">
	<?php
		if(get_option('wc_os_shipping_off', 0)){
?>
			.woocommerce-shipping-fields{
				display:none;	
			}
<?php			
		}
		if(get_option('wc_os_billing_off', 0)){
?>
			.woocommerce-billing-fields{
				display:none;	
			}
<?php			
		}
		if(get_option('wc_os_order_comments_off', 0)){
?>
			.woocommerce-additional-fields{
				display:none;
			}
<?php			
		}				
	?>
	</style>
<?php		
	}
	
	add_action('wp_head', 'wc_os_header_scripts');		
	
	function wc_os_order_cloning(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$cloning = (in_array('cloning', $wc_os_settings['wc_os_additional']));		
		
		return $cloning;
	}
	function wc_os_order_split(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$split = (!in_array('split', $wc_os_settings['wc_os_additional']));
		
		return $split;		
	}		
	function wc_os_order_removal(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$removal = (in_array('removal', $wc_os_settings['wc_os_additional']));
		
		return $removal;		
	}
	function wc_os_order_qty_split(){
		wc_os_settings_refresh();
		global $wc_os_settings;
		
		$qty_split = (in_array('qty_split', $wc_os_settings['wc_os_additional']));
		
		return $qty_split;		
	}	
	
	
	function wc_os_links($actions, $post){
		

	
	
		if ($post->post_type=='shop_order' && wc_os_order_cloning()) {
			
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
			
			$copy_link = wp_nonce_url( add_query_arg( array( 'clone' => 'yes', 'clone-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
			
			$actions = array_merge( $actions, 
				array(
					'clone' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $copy_link ), 
						__('Clone', 'woo-order-splitter')
					) 
				) 
			);
		}
	
		if ($post->post_type=='shop_order' && wc_os_order_split()) {
		
			$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $post->ID );
			
			$copy_link = wp_nonce_url( add_query_arg( array( 'split' => 'init', 'split-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
			
			$actions = array_merge( $actions, 
				array(
					'split' => sprintf( '<a href="%1$s">%2$s</a>',
						esc_url( $copy_link ), 
						__( 'Split', 'woo-order-splitter' )
					) 
				) 
			);
		}		
	
		return $actions;
				
	}
	
	add_filter( 'post_row_actions', 'wc_os_links', 10, 2 );
		
	function sv_wc_add_order_meta_box_action( $actions ) {
		global $theorder;

		global $wc_os_settings;
		
		$split_status = get_post_meta($theorder->id, 'split_status', true);
		$splitted_from = get_post_meta($theorder->id, 'splitted_from', true);	
		
		//wc_os_pree($wc_os_settings);
		//wc_os_pree(!$theorder->is_paid());
		//wc_os_pree($split_status);
		//wc_os_pree($splitted_from);
				
		// bail if the order has been paid for or this action has been run
		//! $theorder->is_paid() || 
		if ( $split_status || $splitted_from) {
			return $actions;
		}
	
	
		// add "mark printed" custom action
		//wc_os_pree(wc_os_order_split());
		if(wc_os_order_split()){
			$actions['wc_os_split_action'] = __( 'Split Order', 'woo-order-splitter' );
		}
		
		return $actions;
	}
	add_action( 'woocommerce_order_actions', 'sv_wc_add_order_meta_box_action' );	
		
	function sv_wc_process_order_meta_box_action( $order ) {
		//wc_os_pree($order->get_id());exit;
		wc_os_checkout_order_processed($order->get_id());
		
	}
	
	//add_action( 'woocommerce_order_action_wc_custom_order_action', 'sv_wc_process_order_meta_box_action' );
	
		
	add_filter( 'woocommerce_admin_order_actions', 'add_wc_os_order_status_actions_button', 100, 2 );
	function add_wc_os_order_status_actions_button( $actions, $order ) {
		// Display the button for all orders that have a 'processing' status
		$url = admin_url( 'edit.php?post_type=shop_order&order_id=' . $order->id );
		
		if ( $order->has_status( array( 'processing', 'on-hold', 'completed', 'pending' ) ) && wc_os_order_split() ) {
			
			$split_status = get_post_meta($order->id, 'split_status', true);
			$splitted_from = get_post_meta($order->id, 'splitted_from', true);		
			
			//wc_os_pree(count($order->get_items())>1);exit;
			if(!$split_status && !$splitted_from && count($order->get_items())>1){
	
				
				
				$copy_link = wp_nonce_url( add_query_arg( array( 'split' => 'init', 'split-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );
				
				// Get Order ID (compatibility all WC versions)
				$order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
				// Set the action button
				$actions['split_order'] = array(
					'url'       => $copy_link,
					'name'      => __( 'Split Order', 'woo-order-splitter' ),
					'action'    => "wc_os_split", 
				);
				
			}
		}
		
		if(wc_os_order_cloning()){
				
			$copy_link = wp_nonce_url( add_query_arg( array( 'clone' => 'yes', 'clone-session' => date('Ymhi') ), $url ), 'edit_order_nonce' );				
			$actions['clone_order'] = array(
				'url'       => $copy_link,
				'name'      => __( 'Clone Order', 'woo-order-splitter' ),
				'action'    => "wc_os_clone", 
			);	
		}
		
		
		return $actions;
	}	
	
	function wos_clone_order_notes($original_order_id=0, $order_id=0){
		
		if($original_order_id && $order_id && $original_order_id != $order_id){
			
		
			global $wpdb;		
			$comments_query = "SELECT * FROM $wpdb->comments WHERE comment_post_ID=$original_order_id ORDER BY comment_ID DESC";
			//wc_os_pree($comments_query);
			$comments_results = $wpdb->get_results($comments_query);
			//wc_os_pree($comments_results);
			if(!empty($comments_results)){
				foreach($comments_results as $comments){


					unset($comments->comment_ID);
					$comments->comment_post_ID = $order_id;
					$comments = (array)$comments;
					//wc_os_pree($comments);exit;
					if(wp_insert_comment($comments)){
						//wc_os_pree($comments->comment_post_ID.' > Ok');
					}else{
						//wc_os_pree($comments->comment_post_ID.' > Failed');
					}
				}
			}
		
			
		}
	}	
	
	function wc_os_random_color_part() {
		return str_pad( dechex( mt_rand( 0, 255 ) ), 2, '0', STR_PAD_LEFT);
	}
	
	function wc_os_random_color() {
		return wc_os_random_color_part() . wc_os_random_color_part() . wc_os_random_color_part();
	}	
	
	function wos_troubleshooting(){
		extract($_POST);
		$ret = array();
		$hex = '#'.wc_os_random_color();
		$ret['color_hex'] = $hex;
		list($r, $g, $b) = sscanf($hex, "#%02x%02x%02x");
		$ret['color'] = array('r'=>$r, 'g'=>$g, 'b'=>$b);
		$order_data = new WC_Order($order_id);
		$get_items = $order_data->get_items();
		$meta = get_post_meta($order_id);
		$wc_os_meta_keys = get_option('wc_os_meta_keys', array());
		
		$cloned_from = (isset($meta['cloned_from'])?$meta['cloned_from'][0]:false);
		$splitted_from = (isset($meta['splitted_from'])?$meta['splitted_from'][0]:false);
		
		
		$wc_os_meta_data = array(
			'parent_order' => (!$cloned_from && !$splitted_from)?'Yes':'No',
			'cloned_from' => $cloned_from?'<a target="_blank" href="'.get_edit_post_link($cloned_from).'">'.$cloned_from.'</a>':'-',
			'splitted_from' => $splitted_from?'<a target="_blank" href="'.get_edit_post_link($splitted_from).'">'.$splitted_from.'</a>':'-',
		);
				
		ob_start();
		
		wc_os_pree($wc_os_meta_data);
		
		wc_os_pree($order_data);
		
		wc_os_pree($meta);		
		
		wc_os_pree($get_items);		
		
		$ret['html'] = ob_get_contents();
		ob_end_clean();
		
		
		$ret = json_encode($ret);
		echo $ret;
		exit;
	}
	
	add_action( 'wp_ajax_wos_troubleshooting', 'wos_troubleshooting' );