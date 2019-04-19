<?php
/**
 * Class for generating the actual feeds
 */
class WooSEA_Get_Products {

	private $feedback;
	public $feed_config;
	private $products = array();
	private $utm = array();
	public $utm_part;
	public $project_config;
	private $upload_dir;
	private $base;
	private $path;
	private $file;

        public function __construct() {
                $this->get_products = array();
        }

	/**
 	 * Get all product cats for a product by ID, including hierarchy
 	 * @param  int $product_id
 	 * @return array
 	 */
//	public function wc_get_product_cat_ids( $product_id ) {
//
//		$product_cats = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );
//
//		error_log(print_r($product_cats, TRUE));
//
//	        foreach ( $product_cats as $product_cat ) {
//      	        $product_cats = array_merge( $product_cats, get_ancestors( $product_cat, 'product_cat' ) );
//    	}
//        	return $product_cats;
//	}

	/**
	 * Function to add CDATA brackets to title, short_description and description attributes
	 */
	protected function woosea_append_cdata( $string ){
		return $string;
//		return "<![CDATA[ $string ]]>"; 
	}

	/**
	 * An improved function for the strip_tags
	 * Removing tags but replacing them with spaces instead of just removing them
	 */
	public function rip_tags( $string ) { 
    		// ----- remove HTML TAGs ----- 
    		$string = preg_replace ('/<[^>]*>/', ' ', $string); 
    
    		// ----- remove control characters ----- 
    		$string = str_replace("\r", '', $string);    // --- replace with empty space
    		$string = str_replace("\n", ' ', $string);   // --- replace with space
    		$string = str_replace("\t", ' ', $string);   // --- replace with space
    
    		// ----- remove multiple spaces ----- 
    		$string = trim(preg_replace('/ {2,}/', ' ', $string));
    
    		return $string; 
	}

	/**
	 * Get all approved product review comments for Google's Product Review Feeds
	 */
	public function woosea_get_reviews ( $product_data, $product ) {
		$approved_reviews = array();

            	$reviews = get_comments(array(
        		'post_id'               => $product_data['id'],
        		'comment_type'          => 'review',
        		'comment_approved'      => 1,
		));

		// Loop through all product reviews for this specific products (ternary operators)
		foreach($reviews as $review_raw){
			$review = array();
			$review['review_product_id'] = $product_data['id'];
			$review['review_product_ids'] = array(
				"gtins"	=> 	empty($product_data['gtins']) ? '' :  $product_data['gtins'],
				"mpns" 	=> 	empty($product_data['mpns']) ? '' : $product_data['mpns'],
				"skus"	=> 	empty($product_data['skus']) ? '' : $product_data['skus'],
				"brand"	=>	empty($product_data['brands']) ? '' : $product_data['brands'],	
			);
			$review['review_reviewer_image'] = empty($product_data['reviewer_image']) ? '' : $product_data['reviewer_image'];
			$review['review_ratings'] = get_comment_meta( $review_raw->comment_ID, 'rating', true);
			$review['review_review_id'] = $review_raw->comment_ID;
			$review['review_reviewer_name'] = $review_raw->comment_author;
			$review['review_reviewer_id'] = $review_raw->user_id;
			$review['review_timestamp'] = $review_raw->comment_date;
			$review['review_title'] = empty($product_data['review_title']) ? '' : $product_data['review_title'];
			$review['review_content'] = $review_raw->comment_content;
			$review['review_pros'] = empty($product_data['review_pros']) ? '' : $product_data['review_pros'];
			$review['review_cons'] = empty($product_data['review_coms']) ? '' : $product_data['review_cons'];
			$review['review_product_name'] = $product_data['title'];
			$review['review_url'] = $product_data['link'];
			$review['review_product_url'] = $product_data['link'];
			array_push($approved_reviews, $review);
		}
		$review_count = $product->get_review_count();
		$review_average = $product->get_average_rating();
		return $approved_reviews;
	}


	/**
	 * Strip unwanted UTF chars from string
	 */
	public function woosea_utf8_for_xml( $string ){
		$string = html_entity_decode($string);
    		return preg_replace ('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $string);
	}	

	/**
         * Function that will create an append with Google Analytics UTM parameters
         * Removes UTM paramaters that are left blank
	 */
	public function woosea_append_utm_code ( $feed_config, $productId, $parentId, $link ) {
		// AdTribes conversionId
		if(array_key_exists('adtribes_conversion', $feed_config)){
			$adtribesConvId = "$feed_config[project_hash]|adtribes|$productId";
		} else {
			$adtribesConvId = "";
		}	
		
		# Create Array of Google Analytics UTM codes				
		$utm = array (
			'adTribesID' => $adtribesConvId,
			'utm_source' => $feed_config['utm_source'],
			'utm_campaign' => $feed_config['utm_campaign'],
			'utm_medium' => $feed_config['utm_medium'],
			'utm_term' => $productId,
			'utm_content' => $feed_config['utm_content']
		);

		// GA tracking is disabled, so remove from array
		if(!array_key_exists('utm_on', $feed_config)){
			unset($utm['utm_source']);
			unset($utm['utm_campaign']);
			unset($utm['utm_medium']);
			unset($utm['utm_term']);
			unset($utm['utm_content']);
		}
		$utm = array_filter($utm); // Filter out empty or NULL values from UTM array		
                
		$utm_part = "";	
		foreach ($utm as $key => $value ) {
			$value = str_replace(" ", "%20", $value);
			$utm_part .= "&$key=$value";
		}

		/**
		 * Get the default WPML language
		 * As that does not have ?lang= behind the links
		 */
		if(isset($feed_config['WPML'])){
	                if ( function_exists('icl_object_id') ) {
				global $sitepress;
				$default_lang = $sitepress->get_default_language();	

				if (preg_match("/\?/i", $link)){
					$utm_part = "&".ltrim($utm_part, '&');
				} else {
					$utm_part = "?".ltrim($utm_part, '&');
				}
			}
		} else {
			# Strip first & from utm 
			if($parentId > 0){
				# Even though variation products always have parameters in the URL we still need to check and make sure they are there
				if(strpos($link, '?') !== false){
					$utm_part = "&".ltrim($utm_part, '&');
				} else {
					$utm_part = "?".ltrim($utm_part, '&');
				}
			} else {
				$utm_part = "?".ltrim($utm_part, '&');
			}
		}
		return $utm_part;
	}

       	/**
         * Converts an ordinary xml string into a CDATA string
         */
    	public function woosea_convert_to_cdata( $string ) { 
		return "<![CDATA[ $string ]]>"; 
	}

	/**
	 * Get custom attribute names for a product
	 */
	private function get_custom_attributes( $productId ) {
        	global $wpdb;
        	$list = array();

		$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$productId." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
	      	$data = $wpdb->get_results($sql);

        	if (count($data)) {
                	foreach ($data as $key => $value) {
                        	$value_display = str_replace("_", " ",$value->name);
			       	if (!preg_match("/_product_attributes/i",$value->name)){
					$list[$value->name] = ucfirst($value_display);
                        	} else {
	                                $product_attr = unserialize($value->type);

                                	foreach ($product_attr as $key => $arr_value) {
                                        	$value_display = str_replace("_", " ",$arr_value['name']);
                                        	$list[$key] = ucfirst($value_display);
                                	}	
				}
                	}
	              	return $list;
        	}
        	return false;
	}

	/**
	 * Get orders for given time period used in filters
	 */
	public function woosea_get_orders( $project_config ){
    		$query_args = array(
        		'post_type'      => wc_get_order_types(),
        		'post_status'    => array_keys( wc_get_order_statuses() ),
        		'posts_per_page' => 999999999999,
    		);
    		$all_orders      = get_posts( $query_args );

    		foreach ( $all_orders as $orders ) {		
			$order = wc_get_order( $orders-> ID);
			$order_data = $order->get_data();
			$order_date_created = $order_data['date_created']->date('Y-m-d H:i:s');

			foreach ($order->get_items() as $item_key => $item_values){
				$product_id = $item_values->get_product_id();
			}		
		}
		//return $orders_timeframe;
	}

	/**
	 * Get category path (needed for Prisjakt)
	 */
	public function woosea_get_term_parents( $id, $taxonomy, $link = false, $project_taxonomy, $nicename = false, $visited = array() ) {
		// Only add Home to the beginning of the chain when we start buildin the chain
		if(empty($visited)){
			$chain = 'Home';
		} else {
			$chain = '';
		}

		$parent = get_term( $id, $taxonomy );
		$separator = ' &gt; ';

		if( $project_taxonomy == "Prisjakt" ){
			$separator = ' / ';
		}

		if ( is_wp_error( $parent ) )
			return $parent;
		
		if($parent){
			if ($nicename){
				$name = $parent->slug;
			} else {
				$name = $parent->name;
			}

			if ($parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited, TRUE )){
				$visited[] = $parent->parent;
				$chain .= $this->woosea_get_term_parents( $parent->parent, $taxonomy, $link, $separator, $nicename, $visited );
			}
 
			if ($link){
				$chain .= $separator.$name;
			} else {
				$chain .= $separator.$name;
			}
		}	
		return $chain;
	} // End woo_get_term_parents()	

	/**
	 * Get all configured shipping zones
	 */
	public function woosea_get_shipping_zones () {
		if( class_exists( 'WC_Shipping_Zones' ) ) {
			$all_zones = WC_Shipping_Zones::get_zones();
			return $all_zones;
		}
		return false;
	}

	/**
	 * Get installment for product
	 */
	public function woosea_get_installment ($project_config, $productId){
		$installment = "";
                $currency = get_woocommerce_currency();
		if(isset($project_config['WCML'])){
			$currency = $project_config['WCML'];
		}
		$installment_months = get_post_meta($productId, '_woosea_installment_months', true);
		$installment_amount = get_post_meta($productId, '_woosea_installment_amount', true);

		if(!empty($installment_amount)){
			$installment = $installment_months.":".$installment_amount." ".$currency;
		}
		return $installment;
	}

	/**
	 * Get shipping cost for product
	 */
	public function woosea_get_shipping_cost ($class_cost_id, $project_config, $price, $tax_rates, $shipping_zones) {
        	$shipping_cost = 0;
		$shipping_arr = array();
		$zone_count = 0;
		$nr_shipping_zones = count($shipping_zones);
		$zone_details = array();
		$base_location = wc_get_base_location();
		$base_country = $base_location['country'];
		$from_currency = get_woocommerce_currency();

		// Normal shipping set-up
		$zone_count = count($shipping_arr)+1;

	        foreach ( $shipping_zones as $zone){

			// Start with a clean shipping zone
			$zone_details = array();
			$zone_details['country'] = "";

			// Start with a clean postal code
			$postal_code = array();
		
			foreach ( $zone['zone_locations'] as $zone_type ) {
		
				if ($zone_type->type == "country"){
					// This is a country shipping zone
					$zone_details['country'] = $zone_type->code;
				} elseif ($zone_type->type == "state"){
					// This is a state shipping zone, split of country
					$zone_expl = explode(":", $zone_type->code);
					$zone_details['country'] = $zone_expl[0];
					$zone_details['region'] = $zone_expl[1];
				} elseif ($zone_type->type == "postcode"){
					// Create an array of postal codes so we can loop over it later
					if ($project_config['taxonomy'] == 'google_shopping'){
						$zone_type->code = str_replace("...", "-", $zone_type->code);	
					}	
					array_push($postal_code, $zone_type->code);
				} else {
					// Unknown shipping zone type
				}
		
				// Get the g:services and g:prices, because there could be multiple services the $shipping_arr could multiply again
				// g:service = "Method title - Shipping class costs"
				// for example, g:service = "Estimated Shipping - Heavy shipping". g:price would be 180			
            	   	      	$shipping_methods     = $zone['shipping_methods'];

				foreach ($shipping_methods as $k => $v){

					if($v->enabled == "yes"){
						if(empty($zone_details['country'])){
							$zone_details['service'] = $zone['zone_name'] ." ". $v->title;					
						} else {
							$zone_details['service'] = $zone['zone_name'] ." ". $v->title ." ".$zone_details['country'];					
						}
						$taxable = $v->tax_status;

						if(isset($v->instance_settings['cost'])){
							$shipping_cost = $v->instance_settings['cost'];
							if(!$shipping_cost){
								$shipping_cost = 0;
							}

                        				// Do we need to convert the shipping costswith the Aelia Currency Switcher
                        				if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                               					if(!array_key_exists('base_currency', $project_config)){
                               	        	 			$from_currency = get_woocommerce_currency();
                               	 				} else {
                                        				$from_currency = $project_config['base_currency'];
                                				}

                                				// Get Aelia currency conversion prices
                                				$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
							}

							if($taxable == "taxable"){
								foreach ($tax_rates as $k => $w){
									if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
										$rate = (($w['rate']+100)/100);
										$shipping_cost = str_replace(",", ".", $shipping_cost);
										$shipping_cost = $shipping_cost*$rate;
										$shipping_cost = round($shipping_cost, 2);
									}
								}
							}
						}

						// CLASS SHIPPING COSTS
        		        	     	if(isset($v->instance_settings[$class_cost_id])){
							if (is_numeric($v->instance_settings[$class_cost_id])){
							$shipping_cost = $v->instance_settings[$class_cost_id];

								// Do we need to convert the shipping costswith the Aelia Currency Switcher
                                                		if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                                					if(!array_key_exists('base_currency', $project_config)){
                                        					$from_currency = get_woocommerce_currency();
                                					} else {
                                        					$from_currency = $project_config['base_currency'];
                                					}

                                                     	   		// Get Aelia currency conversion prices
                                                        		$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
                                                		}
	
								if($taxable == "taxable"){
									foreach ($tax_rates as $k => $w){

										if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
											$rate = (($w['rate']+100)/100);
											$shipping_cost = $shipping_cost*$rate;
											$shipping_cost = round($shipping_cost, 2);
										}
									}
								}
							} else {
								$shipping_cost = $v->instance_settings[$class_cost_id];
								$shipping_cost = str_replace("[qty]", "1", $shipping_cost);	
							    	$mathString = trim($shipping_cost);     // trim white spaces
								if (preg_match("/fee percent/", $mathString)){
 									$shipcost_piece = explode("+", $mathString);
									$mathString = trim($shipcost_piece[0]);
								}

    								$mathString = str_replace ('..', '.', $mathString);    // remove input mistakes from users using shipping formula's
    								$mathString = preg_replace ('[^0-9\+-\*\/\(\)]', '', $mathString);    // remove any non-numbers chars; exception for math operators
								$mathString = str_replace(array('\'', '"', ','), '', $mathString); 

								if(!empty($mathString)){
									eval("\$mathString = $mathString;");
									$shipping_cost = $mathString;
				
									if($taxable == "taxable"){
										foreach ($tax_rates as $k => $w){

											if((isset($w['shipping'])) and ($w['shipping'] == "yes")){
												$rate = (($w['rate']+100)/100);
												if(is_numeric($shipping_cost)){
													$shipping_cost = $shipping_cost*$rate;
													$shipping_cost = round($shipping_cost, 2);
												}
											}
										}
									}
								}

								// Do we need to convert the shipping costswith the Aelia Currency Switcher
                                          	     	 	if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
                                					if(!array_key_exists('base_currency', $project_config)){
                                        					$from_currency = get_woocommerce_currency();
                                					} else {
                                        					$from_currency = $project_config['base_currency'];
                                					}
                                                       	 		// Get Aelia currency conversion prices
                                                        		$shipping_cost = apply_filters('wc_aelia_cs_convert', $shipping_cost, $from_currency, $project_config['AELIA']);
                                                		}
							}
                            			}

						// FREE SHIPPING COSTS IF MINIMUM FEE REACHED
						if($v->id == "free_shipping"){
							$minimum_fee = $v->min_amount;
                                			if(!array_key_exists('base_currency', $project_config)){
                                 	      	 		$currency = get_woocommerce_currency();
                                			} else {
                                        			$currency = $project_config['base_currency'];
                                			}
 
							if(isset($project_config['WCML'])){
								$currency = $project_config['WCML'];
							}
							if(isset($project_config['AELIA'])){
								$currency = $project_config['AELIA'];
							}

							// Only Free Shipping when prodict price is over or equal to minimum order fee	
							if ($price >= $minimum_fee){
								$shipping_cost = 0;
                                				$zone_details['price'] = trim($currency." ".$shipping_cost);
							} else {
								// There are no free shipping requirements
								if($v->requires == ""){
									$shipping_cost = 0;
        	                        				$zone_details['price'] = trim($currency." ".$shipping_cost);
								} else {
									// No need to add the free shipping zone as it is not eligable
									break;
								}
							}
						}

						if($shipping_cost >= 0){
                       		       	  		$currency = get_woocommerce_currency();
							if(isset($project_config['WCML'])){
								$currency = $project_config['WCML'];
							} else {
								if(isset($project_config['AELIA'])){
									$currency = $project_config['AELIA'];
								} else {
	                       	    	   	  			if(!array_key_exists('base_currency', $project_config)){
        	                                				$currency = get_woocommerce_currency();
                	                				} else {
                        	                				$currency = $project_config['base_currency'];
									}
								}
							}
                                			$zone_details['price'] = trim($currency." ".$shipping_cost);
						}
	
						// This shipping zone has postal codes so multiply the zone details
						$nr_postals = count($postal_code);
						if ($nr_postals > 0){
							//if(!empty($shipping_cost)){
							for ($x = 0; $x <= count($postal_code); ) {
								$zone_count++;
								if(!empty($postal_code[$x])){
									$zone_details['postal_code'] = $postal_code[$x];
									$shipping_arr[$zone_count] = $zone_details;
								}
								$x++;	
							}
						//}		
						} else {
							$zone_count++;
							$shipping_arr[$zone_count] = $zone_details;
						}
					}	
				}
			}
		}
		return $shipping_arr;
	}

	/**
	 * Log queries, used for debugging errors
	 */
	public function woosea_create_query_log ( $query, $filename ) {
                $upload_dir = wp_upload_dir();

                $base = $upload_dir['basedir'];
                $path = $base . "/woo-product-feed-pro/logs";
                $file = $path . "/". $filename ."." ."log";

                // External location for downloading the file   
                $external_base = $upload_dir['baseurl'];
                $external_path = $external_base . "/woo-product-feed-pro/logs";
                $external_file = $external_path . "/" . $filename ."." ."log";

                // Check if directory in uploads exists, if not create one      
                if ( ! file_exists( $path ) ) {
                        wp_mkdir_p( $path );
                }

		// Log timestamp
		$today = "\n";
		$today .= date("F j, Y, g:i a");                 // March 10, 2001, 5:16 pm
		$today .= "\n";

                $fp = fopen($file, 'a+');
                fwrite($fp, $today);
		fwrite($fp, print_r($query, TRUE));
		fclose($fp);
	}


	/**
         * Creates XML root and header for productfeed
	 */	
	public function woosea_create_xml_feed ( $products, $feed_config, $header ) {
		$upload_dir = wp_upload_dir();

		$base = $upload_dir['basedir'];
 		$path = $base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];
	
		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
 		$external_path = $external_base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if ( ! file_exists( $path ) ) {
    			wp_mkdir_p( $path );
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if ( (file_exists( $file )) AND ($header == "true") AND ($feed_config['nr_products_processed'] == 0) ) {
			unlink ( $file );
		}	

		// Check if there is a channel feed class that we need to use
		if ($feed_config['fields'] != 'standard'){
			if (!class_exists('WooSEA_'.$feed_config['fields'])){
				require plugin_dir_path(__FILE__) . '/channels/class-'.$feed_config['fields'].'.php';
				$channel_class = "WooSEA_".$feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option ('channel_attributes', $channel_attributes, 'yes');	
			} else {
				$channel_attributes = get_option('channel_attributes');
			}
		}	

		// Some channels need their own feed config and XML namespace declarations (such as Google shopping)
		if ($feed_config['taxonomy'] == 'google_shopping'){
			$namespace = array( 'g' => 'http://base.google.com/ns/1.0' );
			if ( ($header == "true") AND ($feed_config['nr_products_processed'] == 0) ) {
			   	$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss xmlns:g="http://base.google.com/ns/1.0"></rss>');
			   	$xml->addAttribute('version', '2.0');
				$xml->addChild('channel');
				$xml->channel->addChild('title', htmlspecialchars($feed_config['projectname']));
				$xml->channel->addChild('link', site_url());
				$xml->channel->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced WooCommerce Product Feed PRO plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
				$xml->asXML($file);	
			} else {
				$xml = simplexml_load_file($file, 'SimpleXMLElement', LIBXML_NOCDATA);
				$aantal = count($products);

				if (($xml !== FALSE) AND ($aantal > 0)){
					foreach ($products as $key => $value){

						if (is_array ( $value ) ) {
							if(!empty( $value )){
								$product = $xml->channel->addChild('item');
								foreach ($value as $k => $v){
									if ($k == "g:shipping"){
										$ship = explode("||", $v);
										foreach ($ship as $kk => $vv){
											$sub_count = substr_count($vv, '##');
											$shipping = $product->addChild($k, '',htmlspecialchars($namespace['g']));
											$ship_split = explode(":", $vv);
											
											foreach($ship_split as $ship_piece){

												$piece_value = explode("##", $ship_piece);
												if (preg_match("/WOOSEA_COUNTRY/", $ship_piece)){
                                                       							$shipping_country = $shipping->addChild('g:country', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_REGION/", $ship_piece)){
                                                       							$shipping_region = $shipping->addChild('g:region', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_POSTAL_CODE/", $ship_piece)){
													$shipping_price = $shipping->addChild('g:postal_code', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_SERVICE/", $ship_piece)){
                                                       							$shipping_service = $shipping->addChild('g:service', $piece_value[1], $namespace['g']);
												} elseif (preg_match("/WOOSEA_PRICE/", $ship_piece)){
													$shipping_price = $shipping->addChild('g:price',trim($piece_value[1]),$namespace['g']);
												} else {
													// DO NOT ADD ANYTHING
												}
											}
										}
									// Fix issue with additional images for Google Shopping
									} elseif (preg_match("/g:additional_image_link/i",$k)){
                                       	               				$link = $product->addChild('g:additional_image_link', $v, $namespace['g']);
										//$product->$k = $v;
									} elseif ($k == "g:installment"){
										if(!empty($v)){
											$installment_split = explode(":", $v);
											$installment = $product->addChild($k, '', $namespace['g']);
                                                      					$installment_months = $installment->addChild('g:months', $installment_split[0], $namespace['g']);
                                                       					$installment_amount = $installment->addChild('g:amount', $installment_split[1], $namespace['g']);
										}
									} elseif ($k == "g:color" || $k == "g:size" || $k == "g:material"){
										if(!empty($v)){
											$attr_split = explode(",", $v);
											$nr_attr = count($attr_split)-1;
											$attr_value = "";											
	
											for ($x = 0; $x <= $nr_attr; $x++){
												$attr_value .= trim($attr_split[$x])."/";
											}	
											$attr_value = rtrim($attr_value,"/");	
											$product->$k = $attr_value;							
										}						
									} else {
										$product->$k = $v;
									}
								}
							}
						}	
					}
				}

				if(is_object($xml)){
					$xml->asXML($file);
				}
				unset($products);
			}
			unset($xml);
		} else {
			if ( ($header == "true") AND ($feed_config['nr_products_processed'] == 0) ) {

				if ($feed_config['name'] == "Yandex") {
					$main_currency = get_woocommerce_currency();

					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><yml_catalog></yml_catalog>');	
					$xml->addAttribute('date', date('Y-m-d H:i'));
					$shop = $xml->addChild('shop');
					$shop->addChild('name', htmlspecialchars($feed_config['projectname']));
					$shop->addChild('company', get_bloginfo());
					$shop->addChild('url', site_url());
					$shop->addChild('platform', 'WooCommerce');
					$currencies = $shop->addChild('currencies');
					$currency = $currencies->addChild('currency');
					$currency->addAttribute('id', $main_currency);
					$currency->addAttribute('rate', '1');

					$args = array(
    						'taxonomy'   => "product_cat",
					);
					$product_categories = get_terms( 'product_cat', $args );
					$count = count($product_categories);
					if ($count > 0){
						$categories = $shop->addChild('categories');

        					foreach ($product_categories as $product_category){
							$category = $categories->addChild('category', htmlspecialchars($product_category->name));
							$category->addAttribute('id', $product_category->term_id);
							if ($product_category->parent > 0){
								$category->addAttribute('parentId', $product_category->parent);

							}
        					}
					}

					$shop->addChild('agency', 'AdTribes.io');
					$shop->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Heureka.cz" || $feed_config['name'] == "Zbozi.cz") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><SHOP></SHOP>');	
					$xml->addAttribute('xmlns', 'http://www.zbozi.cz/ns/offer/1.0');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Zap.co.il") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><STORE></STORE>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->addChild('agency', 'AdTribes.io');
					$xml->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Salidzini.lv") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><root></root>');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->addChild('agency', 'AdTribes.io');
					$xml->addChild('email', 'support@adtribes.io');
					$xml->asXML($file);
				} elseif ($feed_config['name'] == "Google Product Review") {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><feed></feed>');	
					$xml->addAttribute('xmlns:vc', 'http://www.w3.org/2007/XMLSchema-versioning');
					$xml->addAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
					$xml->addAttribute('xsi:noNamespaceSchemaLocation', 'http://www.google.com/shopping/reviews/schema/product/2.2/product_reviews.xsd');
					$xml->addChild('version', '2.2');
					$aggregator = $xml->addChild('aggregator');
					$aggregator->addChild('name', 'je weet toch');
					$publisher = $xml->addChild('publisher');
					$publisher->addChild('name', 'even wat invullen nog');
					$xml->asXML($file);
				} else {
					$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><products></products>');	
					$xml->addAttribute('version', '1.0');
					$xml->addAttribute('standalone', 'yes');
					$xml->addChild('datetime', date('Y-m-d H:i:s'));
					$xml->addChild('title', htmlspecialchars($feed_config['projectname']));
					$xml->addChild('link', site_url());
					$xml->addChild('description', 'WooCommerce Product Feed PRO - This product feed is created with the free Advanced Product Feed PRO for WooCommerce plugin from AdTribes.io. For all your support questions check out our FAQ on https://www.adtribes.io or e-mail to: support@adtribes.io ');
					$xml->asXML($file);
				}
			} else {
				$xml = simplexml_load_file($file);
				$aantal = count($products);

				if ($aantal > 0){
					if ($feed_config['name'] == "Yandex") {
						$offers = $xml->shop[0]->addChild('offers');
					}

					// For ZAP template
					if ($feed_config['name'] == "Zap.co.il") {
						$productz = $xml->addChild('PRODUCTS');
					}

					// For Google Product review template
					if($feed_config['name'] == "Google Product Review"){
						$reviewz = $xml->addChild('reviews');
					}

					foreach ($products as $key => $value){

						if (is_array ( $value ) ) {
							if ($feed_config['name'] == "Yandex") {
								$product = $offers->addChild('offer');
							} elseif ($feed_config['name'] == "Heureka.cz" || $feed_config['name'] == "Zbozi.cz") {
								$product = $xml->addChild('SHOPITEM');
							} elseif ($feed_config['name'] == "Zap.co.il") {
								$product = $productz->addChild('PRODUCT');
							} elseif ($feed_config['name'] == "Salidzini.lv") {
								$product = $xml->addChild('item');
							} elseif ($feed_config['name'] == "Trovaprezzi.it") {
								$product = $xml->addChild('Offer');
							} elseif ($feed_config['name'] == "Google Product Review") {
								$product = $reviewz->addChild('review');
							} else {
								$product = $xml->addChild('product');
							}

							foreach ($value as $k => $v){

								$v = trim($v);
								$k = trim($k);	

								if(($k == "id") AND ($feed_config['name'] == "Yandex")){
									$product->addAttribute('id', trim($v));
								}
								if(($k == "available") AND ($feed_config['name'] == "Yandex")){
									if($v == "in stock"){
										$v = "true";
									} else {
										$v = "false";
									}
									$product->addAttribute('available', $v);
								}

								/**
								 * Check if a product resides in multiple categories
								 * id so, create multiple category child nodes
								 */			
								if ($k == "categories"){
									$category = $product->addChild('categories');
									$cat = explode("||",$v);							

									if (is_array ( $cat ) ) {
										foreach ($cat as $kk => $vv){
											$child = "category";
											$category->addChild("$child", htmlspecialchars($vv));
										}
									}
								} elseif ($k == "shipping"){
									$expl = "||";
									if(strpos($v, $expl)) {
										$ship = explode("||", $v);
                                                                      		foreach ($ship as $kk => $vv){
											$ship_zone = $product->addChild('shipping');
                                                                            		$ship_split = explode(":", $vv);
		
                                                                           	 	foreach($ship_split as $ship_piece){
                                                                                		$piece_value = explode("##", $ship_piece);
                                                                                 	     	if (preg_match("/WOOSEA_COUNTRY/", $ship_piece)){
                                                                                			$shipping_country = $ship_zone->addChild('country', htmlspecialchars($piece_value[1]));
                                                                              	        	} elseif (preg_match("/WOOSEA_REGION/", $ship_piece)){
                                                                               		 		$shipping_region = $ship_zone->addChild('region', htmlspecialchars($piece_value[1]));
                                                                            	         	} elseif (preg_match("/WOOSEA_POSTAL_CODE/", $ship_piece)){
                                                                               		 		$postal_code = $ship_zone->addChild('postal_code', htmlspecialchars($piece_value[1]));
                                                                              	      		} elseif (preg_match("/WOOSEA_SERVICE/", $ship_piece)){
                                                                                			$shipping_service = $ship_zone->addChild('service', htmlspecialchars($piece_value[1]));
                                                                                     		} elseif (preg_match("/WOOSEA_PRICE/", $ship_piece)){
                                                                                			$shipping_price = $ship_zone->addChild('price', htmlspecialchars($piece_value[1]));
                                                                                      		} else {
                                                                                        		// DO NOT ADD ANYTHING
                                                                                      		}
                                                                             		}
										}
									} else {
										$child = "shipping";
										$product->$k = $v;	
									}
								} elseif ($k == "category_link"){
									$category = $product->addChild('category_links');
									$cat_links = explode("||",$v);							
									if (is_array ( $cat_links ) ) {
										foreach ($cat_links as $kk => $vv){
											$child = "category_link";
											$category->addChild("$child", htmlspecialchars($vv));
										}
									}
								} elseif ($k == "categoryId"){

									if($feed_config['name'] == "Yandex"){
										$args = array(
    											'taxonomy'   => "product_cat",
										);
		
										//$category = $product->addChild('categories');
										$product_categories = get_terms( 'product_cat', $args );
										$count = count($product_categories);
										$cat = explode("||",$v);							

										if (is_array ( $cat ) ) {
											foreach ($cat as $kk => $vv){
												if ($count > 0){
        												foreach ($product_categories as $product_category){
														if($vv == $product_category->name){
															$product->addChild("$k", htmlspecialchars($product_category->term_id));
														}
													}
												}
											}
										}
									}
								} elseif (($k == "id" || $k == "available") AND ($feed_config['name'] == "Yandex")){
									// Do not add these nodes to Yandex product feeds
								} else {
									if ($feed_config['fields'] != 'standard'){
	          	                                           		$k = $this->get_alternative_key ($channel_attributes, $k);
									}
								
									if(!empty($k)){	
										$product->addChild("$k");
										$product->$k = $v;
									}
								}
							}
						}
					}	
					$xml->asXML($file);
					unset($product);
				}
				unset($products);
			}
			unset($xml);
		}
	}

	/**
         * Actual creation of CSV/TXT file
         * Returns relative and absolute file path
	 */	
	public function woosea_create_csvtxt_feed ( $products, $feed_config, $header ) {

		$upload_dir = wp_upload_dir();
		$base = $upload_dir['basedir'];
 		$path = $base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$file = $path . "/" . sanitize_file_name($feed_config['filename']) . "_tmp." . $feed_config['fileformat'];
	
		// External location for downloading the file	
		$external_base = $upload_dir['baseurl'];
 		$external_path = $external_base . "/woo-product-feed-pro/" . $feed_config['fileformat'];
        	$external_file = $external_path . "/" . sanitize_file_name($feed_config['filename']) . "." . $feed_config['fileformat'];

		// Check if directory in uploads exists, if not create one	
		if ( ! file_exists( $path ) ) {
    			wp_mkdir_p( $path );
		}

		// Check if file exists, if it does: delete it first so we can create a new updated one
		if ( (file_exists( $file )) AND ($feed_config['nr_products_processed'] == 0) AND ($header == "true") ) {
			@unlink ( $file );
		}	

		// Check if there is a channel feed class that we need to use
		if ($feed_config['fields'] != 'standard'){
			if (!class_exists('WooSEA_'.$feed_config['fields'])){
				require plugin_dir_path(__FILE__) . '/channels/class-'.$feed_config['fields'].'.php';
				$channel_class = "WooSEA_".$feed_config['fields'];
				$channel_attributes = $channel_class::get_channel_attributes();
				update_option ('channel_attributes', $channel_attributes, 'yes');	
			} else {
				$channel_attributes = get_option('channel_attributes');
			}
		}	
		
		// Append or write to file
		$fp = fopen($file, 'a+');
		
		// Set proper UTF encoding BOM for CSV files
		if($header == "true"){
			fputs( $fp, $bom = chr(0xEF) . chr(0xBB) . chr(0xBF) );
		}		

		// Write each row of the products array
		foreach ($products as $row) {

			foreach ($row as $k => $v){
				$pieces = explode ("','", $v);
				$pieces = str_replace("'", "", $pieces);

				foreach ($pieces as $k => $v){
                                        if ($feed_config['fields'] != 'standard'){
						$v = $this->get_alternative_key ($channel_attributes, $v);
					}			            

					// For CSV fileformat the keys need to get stripped of the g:
                                      	if($feed_config['fileformat'] == "csv"){
                                        	$v = str_replace("g:", "", $v);
                                     	}	

					$pieces[$k] = $v;
				}

				// Convert tab delimiter
				if($feed_config['delimiter'] == "tab"){
					$csv_delimiter = "\t";
				} else {
					$csv_delimiter = $feed_config['delimiter'];
				}

				if ($feed_config['fields'] == "google_local"){
					$tab_line = "";

					if($header == "false"){
                				// Get the store codes
						foreach ($feed_config['attributes'] as $k=>$v){
						    	if(preg_match('/\|/', $k)){ 
								$stores_local = $k;
							}
						}

						$store_ids = explode("|", $stores_local);
						if(is_array($store_ids)){
						
							foreach ($store_ids as $store_key => $store_value){
								$pieces[0] = $store_value;

								if(!empty($store_value)){
									foreach ($pieces as $t_key => $t_value){
										$tab_line .= $t_value . "$csv_delimiter";
									}
									$tab_line = rtrim($tab_line, $csv_delimiter);
									$tab_line .= PHP_EOL;
								}	
							}	
							fwrite($fp, $tab_line);
						} else {
							// Only one store code entered
							foreach ($pieces as $t_key => $t_value){
								$tab_line .= $t_value . "$csv_delimiter";
							}
							
							$tab_line = rtrim($tab_line, $csv_delimiter);
							$tab_line .= PHP_EOL;
							fwrite($fp, $tab_line);
						}
					} else {
						foreach ($pieces as $t_key => $t_value){
							$tab_line .= $t_value . "$csv_delimiter";
						}
						$tab_line = rtrim($tab_line, $csv_delimiter);
						$tab_line .= PHP_EOL;
						fwrite($fp, $tab_line);
					}
				} else {
					$tofile = fputcsv($fp, $pieces, $csv_delimiter, '"');
				}

			}
		}
		// Close the file
		fclose($fp);

		// Return external location of feed
		return $external_file;
	}

	/**
         * Get products that are eligable for adding to the file
	 */
	public function woosea_get_products ( $project_config ) {
		$nr_products_processed = $project_config['nr_products_processed'];
		$count_products = wp_count_posts('product', 'product_variation');

 		if(isset($project_config['product_variations'])){
			$count_single = wp_count_posts('product');
			$count_variation = wp_count_posts('product_variation');
			$published_single = $count_single->publish;
			$published_variation = $count_variation->publish;
			$published_products = $published_single+$published_variation;		
		} else {
			$count_single = wp_count_posts('product');
			$published_products = $count_single->publish;
		}

		$versions = array (
        		"PHP" => (float)phpversion(),
        		"Wordpress" => get_bloginfo('version'),
        		"WooCommerce" => WC()->version,
			"Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
		);

		/**
		 * Do not change these settings, they are here to prevent running into memory issues
		 */
		if ($versions['PHP'] < 5.6){
			// Old version, process a maximum of 50 products per batch
			$nr_batches = ceil($published_products/50);
		} elseif ($versions['PHP'] == 5.6){
			// Old version, process a maximum of 100 products per batch
			$nr_batches = ceil($published_products/200);
		} else {
			// Fast PHP version, process a 750 products per batch
			$nr_batches = ceil($published_products/750);

			if($published_products > 50000){
				$nr_batches = ceil($published_products/1500);
			} else {
				$nr_batches = ceil($published_products/750);
			}
		}

		// TEST BATCHES
		// $nr_batches = 5;

		$offset_step_size = ceil($published_products/$nr_batches);

		/**
		 * Check if the [attributes] array in the project_config is of expected format.
		 * For channels that have mandatory attribute fields (such as Google shopping) we need to rebuild the [attributes] array
		 * Only add fields to the file that the user selected
		 * Construct header line for CSV ans TXT files, for XML create the XML root and header
		 */
		if($project_config['fileformat'] != 'xml'){
			if($project_config['fields'] != 'standard'){
				foreach ($project_config['attributes'] as $key => $value){
					foreach($value as $k => $v){
						if(($k == "attribute") AND (strlen($v) > 0)){
                     	       				if(!isset($attr)){
								$attr = "'$v'";
							} else {
								$attr .= ",'$v'";
							}
						}
					}
				}
			} else {
				foreach( array_keys($project_config['attributes']) as $attribute_key ){
					if (!isset($attr)){
						if(strlen($attribute_key) > 0){
							$attr = "'$attribute_key'";
						}
					} else {
						if(strlen($attribute_key) > 0){
							$attr .= ",'$attribute_key'";
						}
					}			
				}
			}
			$attr = trim($attr, "'");
			$products[] = array ( $attr );
			if($nr_products_processed == 0){
				$file = $this->woosea_create_csvtxt_feed ( $products, $project_config, 'true' );
			}
		} else {
			$products[] = array ();
			$file = $this->woosea_create_xml_feed ( $products, $project_config, 'true' );
		}
		$xml_piece = "";

		// Check if we need to get just products or also product variations
		if(isset($project_config['product_variations'])){
			$post_type = array('product', 'product_variation');
		} else {
			$post_type = array('product');
		}

		// Get Orders
		// $order_timeframe = WooSEA_Get_Products::woosea_get_orders ( $project_config );

		// Switch to configured WPML lamguage
        	if(isset($project_config['WPML'])){
                	if ( function_exists('icl_object_id') ) {
				global $sitepress;
				$lang = $project_config['WPML'];
				$sitepress->switch_lang($lang);
			}
		}

		// Construct WP query
		$wp_query = array(
				'posts_per_page' => $offset_step_size,
                                'offset' => $nr_products_processed,
				'post_type' => $post_type,
				'post_status' => 'publish',
                                'fields' => 'ids',
                                'no_found_rows' => true
                );
		$prods = new WP_Query($wp_query);

		// Logging the WP query and WP query output has been disabled as there are no structural issues with the plugin

		// Log query for debugging purposes
		// $log_query = WooSEA_Get_Products::woosea_create_query_log($wp_query, "query");

		// Log the query output for debugging purposes
		// $log_query = WooSEA_Get_Products::woosea_create_query_log($prods, "query_output");

		$shipping_zones = $this->woosea_get_shipping_zones();

	        while ($prods->have_posts()) : $prods->the_post(); 
			global $product;
			$attr_line = "";
			$catname = "";	
			$catlink = "";
			$xml_product = array();

			$this->childID = get_the_ID();
            		$this->parentID = wp_get_post_parent_id($this->childID);
			$post = get_post($this->parentID);

			// When Wordpress user is an admin and runs the process of creating product feeds also products are put in the feed
			// with a status other than published. This is unwanted behaviour so we skip all products that are not on publish.
			$status = get_post_status($this->parentID);
			if($status != "publish") { continue; }

			$product_data['id'] = get_the_ID();

			// Add support for WooCommerce Multilangual - WCML
			if(isset($project_config['WCML'])){
                   		global $woocommerce_wpml;
                        	$multi_currency = $woocommerce_wpml->get_multi_currency();
                       	 	$multi_currency->prices->prices_init();
                        	$wcml_price = $multi_currency->prices->get_product_price_in_currency( $product_data['id'] , $project_config['WCML']);
			}

			$product_data['title'] = $product->get_title();
                        $product_data['title'] = $this->woosea_utf8_for_xml( $product_data['title'] );
			$product_data['mother_title'] = $product->get_title();
                        $product_data['mother_title'] = $this->woosea_utf8_for_xml( $product_data['mother_title'] );
			$product_data['sku'] = $product->get_sku();
			$product_data['sku_id'] = $product_data['id'];
			$product_data['publication_date'] = get_the_date('d-m-y G:i:s');

			// Start product visibility logic
			$product_data['exclude_from_catalog'] = "no";
			$product_data['exclude_from_search'] = "no";
			$product_data['exclude_from_all'] = "no";

			$visibility_list = wp_get_post_terms(get_the_ID(), 'product_visibility', array("fields" => "all"));
			foreach($visibility_list as $visibility_single){
				if($visibility_single->slug == "exclude-from-catalog"){
					$product_data['exclude_from_catalog'] = "yes";
				}
				if($visibility_single->slug == "exclude-from-search"){
					$product_data['exclude_from_search'] = "yes";
				} 
			}				
		
			if(($product_data['exclude_from_search'] == "yes") AND ($product_data['exclude_from_catalog'] == "yes")){
				$product_data['exclude_from_all'] = "yes";
			}
			// End product visibility logic
                       	$product_data['item_group_id'] = $this->parentID;
			
			if (!empty($product_data['sku'])){
				$product_data['sku_id'] = $product_data['sku']."_".$product_data['id'];

				if ($project_config['fields'] == "facebook_drm"){
					if($product_data['item_group_id'] > 0){
						$product_data['sku_item_group_id'] = $product_data['sku']."_".$product_data['item_group_id'];
					} else {
						$product_data['sku_item_group_id'] = $product_data['sku']."_".$product_data['id'];
					}
				}
			}

			$categories = array_unique(wc_get_product_cat_ids( $product_data['id'] ));

			// This is a category fix for Yandex, probably needed for all channels
			// When Yoast is not installed and a product is linked to multiple categories
			// The ancestor categoryId does not need to be in the feed
                        $double_categories = array(
                        	0 => "Yandex",
                              	1 => "Prisjakt.se",
                              	2 => "Pricerunner.se",
                              	3 => "Pricerunner.dk",
                     	);
                                
                     	if (in_array($project_config['name'], $double_categories, TRUE)){
				$cat_alt = array();
				$cat_terms = get_the_terms( $product_data['id'], 'product_cat' );
                       		if($cat_terms){
					foreach($cat_terms as $cat_term){
        	                		$cat_alt[] = $cat_term->term_id;
                	      		}
				}
				$categories = $cat_alt;
			}

			// Check if the Yoast plugin is installed and active
			if ( class_exists('WPSEO_Primary_Term') ){
				$product_id = $product_data['id'];
				$primary_cat_id=get_post_meta($product_id ,'_yoast_wpseo_primary_product_cat',true);
 				$category_path = $this->woosea_get_term_parents( $primary_cat_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
		
				if(!is_object($category_path)){
					$product_data['category_path'] = $category_path;	
				}	

				if(($primary_cat_id) AND ($primary_cat_id > 0)){
   					$product_cat = get_term($primary_cat_id, 'product_cat');
		
					if(isset($product_cat->name)) {
						$catname = $product_cat->name;
						$catlink = get_category_link($product_cat->term_id);
					} else {
						foreach ($categories as $key => $value){
							if (!$catname){
			                                        $product_cat = get_term($value, 'product_cat');

                        	                		if(isset($product_cat->name)) {
                                	                		$catname = $product_cat->name;
									$catlink = get_term_link($value,'product_cat');
                                	       	 		}
							} else {
		                       	                 	$product_cat = get_term($value, 'product_cat');
	                                   	     		if(isset($product_cat->name)) {
                                                			$catname_concat = $product_cat->name;
									$catlink_concat = get_term_link($value,'product_cat');
                                        			}
								$catname .= "||".$catname_concat;
								$catlink .= "||".$catlink_concat;
							}
						}
					}
				} else {
					foreach ($categories as $key => $value){
						if (!$catname){
		                                        $product_cat = get_term($value, 'product_cat');

                                        		if(isset($product_cat->name)) {
                                                		$catname = $product_cat->name;
								$catlink = get_term_link($value,'product_cat');
                                        		}
						} else {
		                                        $product_cat = get_term($value, 'product_cat');
	                                   	     	if(isset($product_cat->name)) {
                                                		$catname_concat = $product_cat->name;
								$catlink_concat = get_term_link($value,'product_cat');
                                        		}
							$catname .= "||".$catname_concat;
							$catlink .= "||".$catlink_concat;
						}
					}
				}
			} else {
				foreach ($categories as $key => $value){
					if (!$catname){
	                                        $product_cat = get_term($value, 'product_cat');

						// Check if there are mother categories
						$parent_categories = get_ancestors($product_cat->term_id, 'product_cat');
						$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );

		                		if(!is_object($category_path)){
							$product_data['category_path'] = $category_path;
						}

						foreach ($parent_categories as $category_id){
							$parent = get_term_by('id', $category_id, 'product_cat');
							$parent_name = $parent->name;
						}

                                        	if(isset($product_cat->name)) {
							$catname = $product_cat->name;
							$catlink = get_term_link($value,'product_cat');
                                        	}
					} else {
	                                        $product_cat = get_term($value, 'product_cat');
					
						$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
                                               	if(!is_object($category_path)){
							$product_data['category_path'] = $category_path;
						}	

						// Check if there are mother categories
						$parent_categories = get_ancestors($product_cat->term_id, 'product_cat');

						foreach ($parent_categories as $category_id){
							$parent = get_term_by('id', $category_id, 'product_cat');
							$parent_name = "||".$parent->name;
						}

	                                        if(isset($product_cat->name)) {
                                                	$catname_concat = $product_cat->name;
							$catlink_concat = get_term_link($value,'product_cat');
                                        	}
						$catname .= "||".$catname_concat;
						$catlink .= "||".$catlink_concat;
					}
				}
			}

			$product_data['category_link'] = $catlink;
			$product_data['raw_categories'] = $catname;
			$product_data['categories'] = $catname;
			$product_data['description'] = html_entity_decode((str_replace("\r", "", $post->post_content)), ENT_QUOTES | ENT_XML1, 'UTF-8');
			$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $post->post_excerpt)), ENT_QUOTES | ENT_XML1, 'UTF-8');

			// Strip HTML from (short) description
			$product_data['description'] = $this->rip_tags($product_data['description']);
			$product_data['short_description'] = $this->rip_tags($product_data['short_description']);

			// Strip out Visual Composer short codes
			$product_data['description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['description'] );
			$product_data['short_description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['short_description'] );

			// Strip out the non-line-brake character
			$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
			$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);

			// Strip strange UTF chars
			$product_data['description'] = $this->woosea_utf8_for_xml( $product_data['description'] );
			$product_data['short_description'] = $this->woosea_utf8_for_xml( $product_data['short_description'] );

			/**
		 	* Check of we need to add Google Analytics UTM parameters
		 	*/
			if(isset($project_config['utm_on'])){
				$utm_part = $this->woosea_append_utm_code ( $project_config, get_the_ID(), $this->parentID, get_permalink());
			} else {
				$utm_part = "";
			}

			$product_data['link'] = get_permalink()."$utm_part";
			$product_data['condition'] = ucfirst( get_post_meta( $product_data['id'], '_woosea_condition', true ) );
			if(empty($product_data['condition']) || $product_data['condition'] == "Array"){
				$product_data['condition'] = "New";
			}
			$product_data['availability'] = $this->get_stock( $this->childID );
			
			/**
			* When 'Enable stock management at product level is active
			* availability will always return out of stock, even when the stock quantity > 0
			* Therefor, we need to check the stock_status and overwrite te availability value
			*/
			$stock_status = $product->get_stock_status();
			if ($stock_status == "outofstock"){
				$product_data['availability'] = "out of stock";
			} else {
				$product_data['availability'] = "in stock";
			}

			$product_data['quantity'] = $this->clean_quantity( $this->childID, "_stock" );
			$product_data['visibility'] = $this->get_attribute_value( $this->childID,"_visibility" );
			$product_data['menu_order'] =  get_post_field( 'menu_order', $product_data['id'] );
			$product_data['currency'] = get_woocommerce_currency();
			if(isset($project_config['WCML'])){
				$product_data['currency'] = $project_config['WCML'];
			}
                        $product_data['sale_price_start_date'] = $this->get_sale_date($this->childID, "_sale_price_dates_from");
                        $product_data['sale_price_end_date'] = $this->get_sale_date($this->childID, "_sale_price_dates_to");
			$product_data['sale_price_effective_date'] = $product_data['sale_price_start_date'] ."/".$product_data['sale_price_end_date'];
			if($product_data['sale_price_effective_date'] == "/"){
				$product_data['sale_price_effective_date'] = "";
			}


			$product_data['image'] = wp_get_attachment_url($product->get_image_id());
		
			// For variable products I need to get the product gallery images of the simple mother product	
			if($product_data['item_group_id'] > 0){
				$parent_product = wc_get_product( $product_data['item_group_id'] );
				if(is_object($parent_product)){
					$gallery_ids = $parent_product->get_gallery_image_ids();
					$gal_id=1;
					foreach ($gallery_ids as $gallery_key => $gallery_value){
						$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
						$gal_id++;
					}
				}
			} else {
				$gallery_ids = $product->get_gallery_image_ids();
				$gal_id=1;
				foreach ($gallery_ids as $gallery_key => $gallery_value){
					$product_data["image_" . $gal_id] = wp_get_attachment_url($gallery_value);
					$gal_id++;
				}
			}
			$product_data['product_type'] = $product->get_type();
			$product_data['content_type'] = "product";
			if($product_data['product_type'] == "variation"){
				$product_data['content_type'] = "product_group";
			}
                        $product_data['rating_total'] = $product->get_rating_count();
                        $product_data['rating_average'] = $product->get_average_rating();
	                $product_data['shipping'] = 0;
			
			$tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
			$shipping_class_id = $product->get_shipping_class_id();
                	$shipping_class= $product->get_shipping_class();
			$class_cost_id = "class_cost_".$shipping_class_id;

			// Get product prices
			$product_data['price'] = wc_get_price_including_tax($product, array('price'=> $product->get_price()));
			// Override price when WCML price is different than the non-translated price	
			if((isset($project_config['WCML'])) AND ($product_data['price'] !== $wcml_price)){
				$product_data['price'] = $wcml_price;
			}
			$product_data['sale_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_sale_price()));
			$product_data['regular_price'] = wc_get_price_including_tax($product, array('price'=> $product->get_regular_price()));
			if($product_data['regular_price'] == $product_data['sale_price']){
				$product_data['sale_price'] = "";
			}

			if(!empty($tax_rates)){	
				foreach ($tax_rates as $tk => $tv){
					if($tv['rate'] > 0){
						$tax_rates[1]['rate'] = $tv['rate'];
					} else {
						$tax_rates[1]['rate'] = 0;
					}
				}
			} else {
				$tax_rates[1]['rate'] = 0;
			}


			if($product->get_price()){
				$product_data['price_forced'] = round(wc_get_price_excluding_tax($product,array('price'=> $product->get_price())) * (100+$tax_rates[1]['rate'])/100,2);
			}
			if($product->get_regular_price()){
				$product_data['regular_price_forced'] = round(wc_get_price_excluding_tax($product, array('price'=> $product->get_regular_price())) * (100+$tax_rates[1]['rate'])/100,2);
			}
			if($product->get_sale_price()){
				$product_data['sale_price_forced'] = round(wc_get_price_excluding_tax($product, array('price'=> $product->get_sale_price())) * (100+$tax_rates[1]['rate'])/100,2);
			}

			$product_data['net_price'] = round($product->get_price(),2);
			$product_data['net_regular_price'] = round($product->get_regular_price(),2);
			$product_data['net_sale_price'] = round($product->get_sale_price(),2);
		
			// We do not want to have 0 sale price values in the feed	
			if($product_data['net_sale_price'] == 0){
				$product_data['net_sale_price'] = "";
			}

			$price = wc_get_price_including_tax($product,array('price'=> $product->get_price()));
			if($product_data['sale_price'] > 0){
				$price = $product_data['sale_price'];
			}

			// Do we need to convert all of the above prices with the Aelia Currency Switcher
			if((isset($project_config['AELIA'])) AND (!empty($GLOBALS['woocommerce-aelia-currencyswitcher'])) AND (get_option ('add_aelia_support') == "yes")){
				if(!array_key_exists('base_currency', $project_config)){
					$from_currency = get_woocommerce_currency();
				} else {
					$from_currency = $project_config['base_currency'];
				}		

				// Get Aelia currency conversion prices
				$product_data['price'] = apply_filters('wc_aelia_cs_convert', $product_data['price'], $from_currency, $project_config['AELIA']);
				$product_data['regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price'], $from_currency, $project_config['AELIA']);
				$product_data['sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price'], $from_currency, $project_config['AELIA']);
				$product_data['price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['price_forced'], $from_currency, $project_config['AELIA']);
				$product_data['regular_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['regular_price_forced'], $from_currency, $project_config['AELIA']);
				if($product->get_sale_price()){
					$product_data['sale_price_forced'] = apply_filters('wc_aelia_cs_convert', $product_data['sale_price_forced'], $from_currency, $project_config['AELIA']);
				}	
				$product_data['net_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_price'], $from_currency, $project_config['AELIA']);
				$product_data['net_regular_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_regular_price'], $from_currency, $project_config['AELIA']);
				$product_data['net_sale_price'] = apply_filters('wc_aelia_cs_convert', $product_data['net_sale_price'], $from_currency, $project_config['AELIA']);
	
				// Get Aelia manually inserted currency prices
				if($product->is_type('simple')){
					$regular_aelia_prices = get_post_meta($product_data['id'], '_regular_currency_prices', true);
				} else {
					$regular_aelia_prices = get_post_meta($product_data['id'], 'variable_regular_currency_prices', true);
				}
				$regular_aelia_prices = trim($regular_aelia_prices, "}");
				$regular_aelia_prices = trim($regular_aelia_prices, "{");

				if(strlen($regular_aelia_prices) > 2){
					$regular_aelia_pieces = explode(",", $regular_aelia_prices);
					foreach ($regular_aelia_pieces as $rap_k => $rap_v){
						$regulars = explode(":", $rap_v);
						$reg_cur = trim($regulars[0], "\"");
						$reg_val = trim($regulars[1], "\"");
						if($reg_cur == $project_config['AELIA']){
							$product_data['regular_price'] = $reg_val;
						}
					}
				}

				if($product->is_type('simple')){
					$sale_aelia_prices = get_post_meta($product_data['id'], '_sale_currency_prices', true);
				} else {
					$sale_aelia_prices = get_post_meta($product_data['id'], 'variable_sale_currency_prices', true);
				}
				$sale_aelia_prices = trim($sale_aelia_prices, "}");
				$sale_aelia_prices = trim($sale_aelia_prices, "{");

				if(strlen($sale_aelia_prices) > 2){
					$sale_aelia_pieces = explode(",", $sale_aelia_prices);
					foreach ($sale_aelia_pieces as $sap_k => $sap_v){
						$sales = explode(":", $sap_v);
						$sale_cur = trim($sales[0], "\"");
						$sale_val = trim($sales[1], "\"");
						if($sale_cur == $project_config['AELIA']){
							$product_data['sale_price'] = $sale_val;
						}
					}
				}

			}

			// Localize the price attributes
			$product_data['price'] = wc_format_localized_price($product_data['price']);
			$product_data['regular_price'] = wc_format_localized_price($product_data['regular_price']);
			$product_data['sale_price'] = wc_format_localized_price($product_data['sale_price']);
                        if($product->get_price()){
				$product_data['price_forced'] = wc_format_localized_price($product_data['price_forced']);
			}
                        if($product->get_regular_price()){
				$product_data['regular_price_forced'] = wc_format_localized_price($product_data['regular_price_forced']);
			}
			if($product->get_sale_price()){
				$product_data['sale_price_forced'] = wc_format_localized_price($product_data['sale_price_forced']);
			}	
			$product_data['net_price'] = wc_format_localized_price($product_data['net_price']);
			$product_data['net_regular_price'] = wc_format_localized_price($product_data['net_regular_price']);
			$product_data['net_sale_price'] = wc_format_localized_price($product_data['net_sale_price']);

			foreach($project_config['attributes'] as $attr_key => $attr_arr){
				if(is_array($attr_arr)){
					if($attr_arr['attribute'] == "g:shipping"){
						$product_data['shipping'] =  $this->woosea_get_shipping_cost($class_cost_id, $project_config, $price, $tax_rates, $shipping_zones);
						$shipping_str = $product_data['shipping'];
					}
				}
			}

			if (array_key_exists('shipping', $project_config['attributes'])){
				$product_data['shipping'] =  $this->woosea_get_shipping_cost($class_cost_id, $project_config, $price, $tax_rates, $shipping_zones);
				$shipping_str = $product_data['shipping'];
			}

			// Google Dynamic Remarketing feeds require the English price notation
			if ($project_config['name'] == "Google Remarketing - DRM"){
				$thousand_separator = wc_get_price_thousand_separator();

				if($thousand_separator != ','){
					$product_data['price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['price'])));
					$product_data['regular_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price'])));
					$product_data['sale_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price'])));
					$product_data['regular_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['regular_price_forced'])));
					if($product->get_sale_price()){
						$product_data['sale_price_forced'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['sale_price_forced'])));
					}
					$product_data['net_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_price'])));
					$product_data['net_regular_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_regular_price'])));
					$product_data['net_sale_price'] = floatval(str_replace(',', '.', str_replace('.', '', $product_data['net_sale_price'])));
				}
			}

			$product_data['installment'] = $this->woosea_get_installment($project_config, $product_data['id']);
			$product_data['weight'] = ($product->get_weight()) ? $product->get_weight() : false;
                        $product_data['height'] = ($product->get_height()) ? $product->get_height() : false;
                        $product_data['length'] = ($product->get_length()) ? $product->get_length() : false;
			$product_data['width'] = ($product->get_width()) ? $product->get_width() : false;

                        // Featured Image
                        if (has_post_thumbnail($post->ID)){
                         	$image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
                            	$product_data['feature_image'] = $this->get_image_url($image[0]);
                        } else {
                           	$product_data['feature_image'] = $this->get_image_url($product_data['image']);
                        }

			/**
			 * Do we need to add Dynamic Attributes?
			 */
			$project_config['attributes_original'] = $project_config['attributes'];

			if($project_config['fields'] != 'standard'){
				//$project_config['attributes_original'] = array();
				foreach($project_config['attributes'] as $stand_key => $stand_val){
					if((isset($stand_val['mapfrom'])) AND (strlen($stand_val['mapfrom']) > 0)){
						$project_config['attributes_original'][$stand_val['mapfrom']] = "true";
					}
				}				
			}

        		$no_taxonomies = array("element_category","template_category","portfolio_category","portfolio_skills","portfolio_tags","faq_category","slide-page","yst_prominent_words","category","post_tag","nav_menu","link_category","post_format","product_type","product_visibility","product_cat","product_shipping_class","product_tag");
        		$taxonomies = get_taxonomies();
 
	      		$diff_taxonomies = array_diff($taxonomies, $no_taxonomies);

			foreach($diff_taxonomies as $taxo){
				$term_value = get_the_terms($product_data['id'], $taxo);
			
				if(is_array($term_value)){
					foreach($term_value as $term){
						$product_data[$taxo] = $term->name;
					}
				}
			}

			/**
			 * Add product tags to the product data array
			 */
			$product_tags = get_the_terms($product_data['id'], "product_tag");
			if(is_array($product_tags)){

				foreach($product_tags as $term){

					if(!array_key_exists("product_tag", $product_data)){
						$product_data["product_tag"] = array($term->name);
					} else {
			               		array_push ($product_data["product_tag"], $term->name);
					}
				}
			}
			
			/**
			 * Get Custom Attributes for Single products
			 */
			if ($product->is_type('simple')){

				$custom_attributes = $this->get_custom_attributes( $product_data['id'] );

				foreach($custom_attributes as $custom_kk => $custom_vv){
    					$custom_value = get_post_meta( $product_data['id'], $custom_kk, true );
					$new_key ="custom_attributes_" . $custom_kk;
				
					// Just to make sure product names are never empty
					if(($custom_kk == "_woosea_optimized_title") && ($custom_value == "")){
						$custom_value = $product_data['title'];
					}

					// Just to make sure the condition field is never empty
					if(($custom_kk == "_woosea_condition") && ($custom_value == "")){
						$custom_value = $product_data['condition'];
					}
					$product_data[$new_key] = $custom_value;
				}
				/**
				 * We need to check if this product has individual custom product attributes
				 */
				global $wpdb;
                		$sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$product_data['id']." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";              
				$data = $wpdb->get_results($sql);
                		if (count($data)) {
                        		foreach ($data as $key => $value) {
                                		$value_display = str_replace("_", " ",$value->name);
                                		if (preg_match("/_product_attributes/i",$value->name)){
                                        		$product_attr = unserialize($value->type);
			                                foreach ($product_attr as $key => $arr_value) {
								$new_key ="custom_attributes_" . $key;
								$product_data[$new_key] = $arr_value['value'];
                                        		}
						}
					}
				}	
			}
			/**
			 * Get Product Attributes for Single products 
			 */
			if ($product->is_type('simple')){
				$single_attributes = $product->get_attributes();

				foreach ($single_attributes as $attribute){
					$attr_name = strtolower($attribute->get_name());
					$attr_value = $product->get_attribute($attr_name);
					$product_data[$attr_name] = $attr_value;
				}
			}

                     	// Check if user would like to use the mother main image for all variation products
                      	$add_mother_image = get_option ('add_mother_image');
                      	if(($add_mother_image == "yes") AND ($product_data['item_group_id'] > 0)){
				$mother_image = wp_get_attachment_image_src( get_post_thumbnail_id( $product_data['item_group_id'] ), 'full' );
				$product_data['image'] = $mother_image[0];
                       	}

                        // Get product reviews for Google Product Review Feeds
                        // $product_data['reviews'] = $this->woosea_get_reviews( $product_data, $product );

			/**
			 * Versioned products need a seperate approach
			 * Get data for these products based on the mother products item group id 
			 */
			if( ($product_data['item_group_id'] > 0) ){

				$product_variations = new WC_Product_Variation( $product_data['id'] );
    				$variations = $product_variations->get_variation_attributes();

				$append = "";

        			$variable_description = get_post_meta( $product_data['id'], '_variation_description', true );

				/**
				 * When there is a specific description for a variation product than override the description of the mother product
				 */
				if(!empty($variable_description)){	
                        		$product_data['description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');
                        		$product_data['short_description'] = html_entity_decode((str_replace("\r", "", $variable_description)), ENT_QUOTES | ENT_XML1, 'UTF-8');

                        		// Strip HTML from (short) description
                        		$product_data['description'] = $this->rip_tags($product_data['description']);
                        		$product_data['short_description'] = $this->rip_tags($product_data['short_description']);
                        		// Strip out Visual Composer short codes
                        		$product_data['description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['description'] );
                        		$product_data['short_description'] = preg_replace( '/\[(.*?)\]/', ' ', $product_data['short_description'] );

                        		// Strip out the non-line-brake character
                        		$product_data['description'] = str_replace("&#xa0;", "", $product_data['description']);
                        		$product_data['short_description'] = str_replace("&#xa0;", "", $product_data['short_description']);
			
					// Strip unwanted UTF8 chars
					$product_data['description'] = $this->woosea_utf8_for_xml( $product_data['description'] );
					$product_data['short_description'] = $this->woosea_utf8_for_xml( $product_data['short_description'] );
				}

				/**
				 * Add the product visibility values for variations based on the simple mother product
				 */
				$product_data['exclude_from_catalog'] = "no";
				$product_data['exclude_from_search'] = "no";
				$product_data['exclude_from_all'] = "no";

				$visibility_list = wp_get_post_terms($product_data['item_group_id'], 'product_visibility', array("fields" => "all"));
				foreach($visibility_list as $visibility_single){
					if($visibility_single->slug == "exclude-from-catalog"){
						$product_data['exclude_from_catalog'] = "yes";
					}
					if($visibility_single->slug == "exclude-from-search"){
						$product_data['exclude_from_search'] = "yes";
					} 
				}				
		
				if(($product_data['exclude_from_search'] == "yes") AND ($product_data['exclude_from_catalog'] == "yes")){
					$product_data['exclude_from_all'] = "yes";
				}

				/**
				 * Although this is a product variation we also need to grap the Product attributes belonging to the simple mother product
				 */
				$mother_attributes = get_post_meta($product_data['item_group_id'], '_product_attributes');

	                      	foreach ($mother_attributes as $attribute){
					foreach($attribute as $attr){
						
						$attr_name = $attr['name'];
						$terms = get_the_terms($product_data['item_group_id'], $attr_name);

						if(is_array($terms)){

							foreach($terms as $term){
								$attr_value = $term->name;
							}
							$product_data[$attr_name] = $attr_value;		
						}
					}
				}


				/**
				 * Although this is a product variation we also need to grap the Dynamic attributes belonging to the simple mother prodict
				 */
                        	foreach($diff_taxonomies as $taxo){
                                	$term_value = get_the_terms($product_data['item_group_id'], $taxo);

                                	if(is_array($term_value)){
                                        	foreach($term_value as $term){
							$product_data[$taxo] = $term->name;
                                       		}
                                	}
                        	}


                        	/**
                         	 * Add product tags to the product data array
                         	 */
                        	$product_tags = get_the_terms($product_data['item_group_id'], "product_tag");
                        	if(is_array($product_tags)){

                                	foreach($product_tags as $term){

                                        	if(!array_key_exists("product_tag", $product_data)){
                                    	           	$product_data["product_tag"] = array($term->name);
                                        	} else {
                                                	array_push ($product_data["product_tag"], $term->name);
                                        	}
                                	}
                        	}

				// Add attribute values to the variation product names to make them unique
				foreach($variations as $kk => $vv){
					$custom_key = $kk; 

					if (isset($project_config['product_variations']) AND ($project_config['product_variations'] == "on")){
						$taxonomy = str_replace("attribute_","",$kk);
						$term = get_term_by('slug', $vv, $taxonomy); 
						
						if($term && $term->name){
							$vv = $term->name;
						}
	
						if($vv){
							$append = ucfirst($vv);
							// Prevent duplicate attribute values from being added to the product name
							if(!preg_match("/" . preg_quote($product_data['title'], '/') . "/", $append)){
								$product_data['title'] = $product_data['title']." ".$append;
							}
						}
					}
						
					$custom_key = str_replace("attribute_","",$custom_key);
					$product_data[$custom_key] = $vv;
					$append = "";
				}

        	                /**
                	         * Get Custom Attributes for this variable product
                       	  	 */
                        	$custom_attributes = $this->get_custom_attributes( $product_data['id'] );

//				if(!empty($custom_attributes)){
                  	      		foreach($custom_attributes as $custom_kk => $custom_vv){
                                		$custom_value = get_post_meta( $product_data['id'], $custom_kk, true );

						// Product variant brand is empty, grap that of the mother product
						if(($custom_kk == "_woosea_brand") && ($custom_value == "")){
                                			$custom_value = get_post_meta( $product_data['item_group_id'], $custom_kk, true );
						}

						// Product variant optimized title is empty, grap the mother product title
						if(($custom_kk == "_woosea_optimized_title") && ($custom_value == "")){
							$custom_value = $product_data['title'];
						}

						if(!is_array($custom_value)){

							$new_key ="custom_attributes_" . $custom_kk;
							// In order to make the mapping work again, replace var by product
                                	        	$new_key = str_replace("var","product",$new_key);
							if(!empty( $custom_value )){
								$product_data[$new_key] = $custom_value;
                        				}
						}
					}
//				}

                                /**
                                 * We need to check if this product has individual custom product attributes
                                 */
                                global $wpdb;
                                $sql = "SELECT meta.meta_id, meta.meta_key as name, meta.meta_value as type FROM " . $wpdb->prefix . "postmeta" . " AS meta, " . $wpdb->prefix . "posts" . " AS posts WHERE meta.post_id=".$product_data['item_group_id']." AND meta.post_id = posts.id GROUP BY meta.meta_key ORDER BY meta.meta_key ASC";
                                $data = $wpdb->get_results($sql);
                                if (count($data)) {
                                        foreach ($data as $key => $value) {
                                                $value_display = str_replace("_", " ",$value->name);
                                                if (preg_match("/_product_attributes/i",$value->name)){
                                                        $product_attr = unserialize($value->type);
                                                        foreach ($product_attr as $key => $arr_value) {
                                                                $new_key ="custom_attributes_" . $key;
                                                                $product_data[$new_key] = $arr_value['value'];
                                                        }
                                                }
                                        }
                                }

				/**
				 * We also need to make sure that we get the custom attributes belonging to the simple mother product
				 */
                        	$custom_attributes_mother = $this->get_custom_attributes( $product_data['item_group_id'] );

                        	foreach($custom_attributes_mother as $custom_kk_m => $custom_value_m){

					if(!array_key_exists($custom_kk_m, $product_data)){
						$custom_value_m = get_post_meta( $product_data['item_group_id'], $custom_kk_m, true );
						$new_key_m ="custom_attributes_" . $custom_kk_m;
						// In order to make the mapping work again, replace var by product
			                      	$new_key_m = str_replace("var","product",$new_key_m);

						if(!key_exists($new_key_m, $product_data) AND (!empty($custom_value_m))){
							if(is_array($custom_value_m)){
								// determine what to do with this later	
							} else {
								$product_data[$new_key_m] = $custom_value_m;
							}
						}
					}
                        	}

				// Get versioned product categories
				$categories = wc_get_product_cat_ids( $product_data['item_group_id'] );

                        	// This is a category fix for Yandex, probably needed for all channels
                        	// When Yoast is not installed and a product is linked to multiple categories
                        	// The ancestor categoryId does not need to be in the feed
				$double_categories = array(
					0 => "Yandex",
					1 => "Prisjakt.se",
					2 => "Pricerunner.se",
					3 => "Pricerunner.dk",
				);

				if (in_array($project_config['name'], $double_categories, TRUE)){
                                	$cat_alt = array();
                                	$cat_terms = get_the_terms( $product_data['item_group_id'], 'product_cat' );
                                	if($cat_terms){
                                        	foreach($cat_terms as $cat_term){
                                                	$cat_alt[] = $cat_term->term_id;
                                        	}
                                	}
                                	$categories = $cat_alt;
                        	}

				

				// Check if the Yoast plugin is installed and active
				if ( class_exists('WPSEO_Primary_Term') ){

					$product_id = $product_data['item_group_id'];
					$primary_cat_id=get_post_meta($product_id ,'_yoast_wpseo_primary_product_cat',true);

					if($primary_cat_id){
 		  				$product_cat = get_term($primary_cat_id, 'product_cat');
		
						if(empty($product_cat)){
						
							// No primary category was set
							foreach ($categories as $key => $value){
                                                		if (!$catname){
                                                        		$product_cat = get_term($value, 'product_cat');
                                                			$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $project_taxonomy = $project_config['taxonomy'], $link = false, $nicename = false, $visited = array() );
                                                			
					               	      		if(!is_object($category_path)){
										$product_data['category_path'] = $category_path;
									}

                                                        		if(isset($product_cat->name)) {
                                                                		$catname = $product_cat->name;
                                                                		$catlink = get_term_link($value,'product_cat');
                                                        		}
                                                		} else {
                                                        		$product_cat = get_term($value, 'product_cat');
                                                        		$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
                                               				if(!is_object($category_path)){
										$product_data['category_path'] = $category_path;
									}

									if(isset($product_cat->name)) {
                                                       		         	$catname_concat = $product_cat->name;
                                                        	        	$catlink_concat = get_term_link($value,'product_cat');
                                                        		}
                                                        		$catname .= "||".$catname_concat;
                                                        		$catlink .= "||".$catlink_concat;
                                               			}
							}
						} else {
   							$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
 	                        	              	if(!is_object($category_path)){
								$product_data['category_path'] = $category_path;
							}							

							if(isset($product_cat->name)) {
								$catname = $product_cat->name;
								$catlink = get_category_link($product_cat->term_id);
							}
						}
					} else {
						foreach ($categories as $key => $value){
                                                	if (!$catname){
                                                        	$product_cat = get_term($value, 'product_cat');
                                                		$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $project_taxonomy = $project_config['taxonomy'], $link = false, $nicename = false, $visited = array() );
                                                			
					                     	if(!is_object($category_path)){
									$product_data['category_path'] = $category_path;
								}

                                                        	if(isset($product_cat->name)) {
                                                                	$catname = $product_cat->name;
                                                                	$catlink = get_term_link($value,'product_cat');
                                                        	}
                                                	} else {
                                                        	$product_cat = get_term($value, 'product_cat');
                                                        	$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
                                               			if(!is_object($category_path)){
									$product_data['category_path'] = $category_path;
								}

								if(isset($product_cat->name)) {
                                                                	$catname_concat = $product_cat->name;
                                                                	$catlink_concat = get_term_link($value,'product_cat');
                                                        	}
                                                        	$catname .= "||".$catname_concat;
                                                        	$catlink .= "||".$catlink_concat;
                                               		}
						}
					}
					$product_data['raw_categories'] = $catname;
				} else {
					foreach ($categories as $key => $value){
	                                        if (!$catname){
                                                        $product_cat = get_term($value, 'product_cat');
						
							if($product_cat->parent > 0){
								$set_parent = $product_cat->parent;
							}

                                                		$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
                                                		if(!is_object($category_path)){
									$product_data['category_path'] = $category_path;
                                                    		}
							 	if(isset($product_cat->name)) {
                                                               		$catname = $product_cat->name;
                                                               		$catlink = get_term_link($value,'product_cat');
                                                       		}
                                                
						} else {
                                                        $product_cat = get_term($value, 'product_cat');

							if($product_cat->parent > 0){
								$category_path = $this->woosea_get_term_parents( $product_cat->term_id, 'product_cat', $link = false, $project_taxonomy = $project_config['taxonomy'], $nicename = false, $visited = array() );
                                                		if(!is_object($category_path)){
									$product_data['category_path'] = $category_path;                                                        	
								}
								if(isset($product_cat->name)) {
                                                       		       	$catname_concat = $product_cat->name;
                                                        	       	$catlink_concat = get_term_link($value,'product_cat');
                                                       		}
                                                       		$catname .= "||".$catname_concat;
                                                       		$catlink .= "||".$catlink_concat;
                                               		}
						}
					}
				}
	                        $product_data['raw_categories'] = $catname;
				$product_data['category_link'] = $catlink;
				$product_data['categories'] = $catname;
                     	} 
			/**
			 * In order to prevent XML formatting errors in Google's Merchant center
			 * we will add CDATA brackets to the title and description attributes
			 */
                        $product_data['title'] = $this->woosea_append_cdata ( $product_data['title'] );
                        $product_data['description'] = $this->woosea_append_cdata ( $product_data['description'] );
                        $product_data['short_description'] = $this->woosea_append_cdata ( $product_data['short_description'] );

			/**
			 * Check if individual products need to be excluded
			 */
			$product_data = $this->woosea_exclude_individual( $product_data );	

			/**
			 * Rules execution
			 */
			if (array_key_exists('rules2', $project_config)){
				if(is_array($product_data)){
					$product_data = $this->woocommerce_sea_rules( $project_config['rules2'], $product_data ); 
				}
			}

			/**
			 * Filter execution
			 */
			if (array_key_exists('rules', $project_config)){
				if(is_array($product_data)){
					$product_data = $this->woocommerce_sea_filters( $project_config['rules'], $product_data ); 
				}
			}

			/**
			 * Check if we need to add category taxonomy mappings (Google Shopping)
			 */
			if ((array_key_exists('mappings', $project_config)) AND ($project_config['taxonomy'] == 'google_shopping')){
				if(isset($product_data['id'])){
					$product_data = $this->woocommerce_sea_mappings( $project_config['mappings'], $product_data ); 
				}
			} elseif ((!array_key_exists('mappings', $project_config)) AND ($project_config['taxonomy'] == 'google_shopping')){
				if(isset($product_data['id'])){
					$product_data['categories'] = "";	
				}
			}

			/**
			 * When a product is a variable product we need to delete the original product from the feed, only the originals are allowed
			 */
			if(($product->is_type('variable')) AND ($product_data['item_group_id'] == 0)){
		        	$product_data = array();
                        	$product_data = null;	
			}

			/**
			 * And item_group_id is not allowed for simple products, prevent users from adding this to the feedd
			 */
			if($product->is_type('simple')){
				unset($product_data['item_group_id']);
			}

			/**
			 * When product has passed the filter rules it can continue with the rest
			 */
			if(!empty($product_data)){
				/**
				 * Determine what fields are allowed to make it to the csv and txt productfeed
				 */
			        if (($project_config['fields'] != "standard") AND (!isset($tmp_attributes))){
					$old_attributes_config = $project_config['attributes'];
                      			$tmp_attributes = array();
					foreach ($project_config['attributes'] as $key => $value){
						if(strlen($value['mapfrom']) > 0){
							$tmp_attributes[$value['mapfrom']] = "true";
						}
					}
	                      		$project_config['attributes'] = $tmp_attributes;
				}

				if(isset($old_attributes_config)){
					$identifier_positions = array();
					$loop_count = 0;

					foreach($old_attributes_config as $attr_key => $attr_value){
				
						if(!$attr_line){
							if(array_key_exists('static_value', $attr_value)){
								if(strlen($attr_value['mapfrom'])){
									$attr_line = "'".$attr_value['prefix']. "".$attr_value['mapfrom']."" .$attr_value['suffix']."'";
								} else {
									$attr_line = "''";
								}
							} else {
								if((strlen($attr_value['mapfrom'])) AND (array_key_exists($attr_value['mapfrom'], $product_data))){
									$attr_line = "'".$attr_value['prefix']. "".$product_data[$attr_value['mapfrom']]."" .$attr_value['suffix']."'";
								} else {
									$attr_line = "''";
								}
							}
						} else {
							if(array_key_exists('static_value', $attr_value)){
								$attr_line .= ",'".$attr_value['prefix']. "".$attr_value['mapfrom']."" .$attr_value['suffix']."'";
							} else {
								// Determine position of identifiers in CSV row
								if($attr_value['attribute'] == "g:brand" || $attr_value['attribute'] == "g:gtin" || $attr_value['attribute'] == "g:mpn" || $attr_value['attribute'] == "g:identifier_exists"){
									$arr_pos = array($attr_value['attribute'] => $loop_count);
									$identifier_positions = array_merge($identifier_positions, $arr_pos);	
								}

 								if (array_key_exists($attr_value['mapfrom'], $product_data)){
								
									if(is_array($product_data[$attr_value['mapfrom']])){

										if($attr_value['mapfrom'] == "product_tag"){
											$product_tag_str = "";

                                                                               		foreach ($product_data['product_tag'] as $key => $value){
                                                       	                        		$product_tag_str .= ",";
                                                                                        	$product_tag_str .= "$value";
                                                                       	     		}
                                                                        		$product_tag_str = rtrim($product_tag_str, ",");
                                                                     	      		$product_tag_str = ltrim($product_tag_str, ",");

											$attr_line .= ",'".$product_tag_str."'";
 
										} else {
                                        	                               		$shipping_str = "";
                                                                               		foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
                                                       	                        		$shipping_str .= "||";
                                                               	                     		foreach($value as $k => $v){
													if(preg_match('/[0-9]/', $v)){
														$shipping_str .= ":$attr_value[prefix]".$v."$attr_value[suffix]";
													//	$shipping_str .= ":$attr_value[prefix]".$v."$attr_value[suffix]";
                                                                                  	         	} else {
                                                                                        			$shipping_str .= ":$v";
                                                                     	                 		}
												}
                                                                       	     		}
                                                                  	      	  	$shipping_str = ltrim($shipping_str, "||");
                                                                        		$shipping_str = rtrim($shipping_str, ":");
                                                                     	      		$shipping_str = ltrim($shipping_str, ":");
                                                                             		$shipping_str = str_replace("||:", "||", $shipping_str);

											$attr_line .= ",'".$shipping_str."'";
                                                            			}	
								 	 } else {
										if(strlen($product_data[$attr_value['mapfrom']])){
                                                                        		$attr_line .= ",'".$attr_value['prefix']. " ".$product_data[$attr_value['mapfrom']]." " .$attr_value['suffix']."'";
										} else {
											$attr_line .= ",''";
										}
									}
								} else {
									$attr_line .= ",''";
								}
							}
						}
						$loop_count++;
					}
					$pieces_row = explode ("','", $attr_line);
					$pieces_row = array_map('trim', $pieces_row);

					if($project_config['fields'] == "google_shopping"){
						foreach($identifier_positions as $id_key => $id_value){
							if($id_key != "g:identifier_exists"){
								if ($pieces_row[$id_value]){
									$identifier_exists = "yes";
								}
							} else {
								$identifier_position = $id_value;
							}
						}
	
						if((isset($identifier_exists)) AND ($identifier_exists == "yes")){
							$pieces_row[$id_value] = $identifier_exists;
						} else {
							if(isset($id_value)){
								$pieces_row[$id_value] = "no";
							}
						}
					}
					$attr_line = implode("','", $pieces_row);
					$products[] = array ( $attr_line );
				} else {
					foreach( array_keys($project_config['attributes']) as $attribute_key ){
                                        	if (array_key_exists($attribute_key, $product_data)){
                                                        if(!$attr_line){
                                                                $attr_line = "'".$product_data[$attribute_key]."'";
                                                        } else {
                                                                $attr_line .= ",'".$product_data[$attribute_key]."'";
                                                        }
						}
					}
					$attr_line = trim($attr_line, "'");
					$products[] = array ( $attr_line );
				}

				/**
				 * Build an array needed for the adding Childs in the XML productfeed
				 */
				foreach( array_keys($project_config['attributes']) as $attribute_key ){
			
					if(!is_numeric($attribute_key)){

						if(!isset($old_attributes_config)){
							if(!$xml_product){
								$xml_product = array (
									$attribute_key => $product_data[$attribute_key]
								);
							} else {
								if(isset($product_data[$attribute_key])){
									$xml_product = array_merge($xml_product, array($attribute_key => $product_data[$attribute_key]));
								}
							}
						} else {
							foreach($old_attributes_config as $attr_key => $attr_value){

								$ca = 0;
								// Static attribute value was set by user
								if(array_key_exists('static_value', $attr_value)){
									if(!isset($xml_product)){
										$xml_product = array (
											$attr_value['attribute'] => "$attr_value[prefix] ". $attr_value['mapfrom'] ." $attr_value[suffix]"
										);
									} else {
										$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $attr_value['mapfrom'] ." $attr_value[suffix]";	
									}
								} elseif ($attr_value['mapfrom'] == $attribute_key){
									if(!isset($xml_product)){
										$xml_product = array (
											$attr_value['attribute'] => "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]"
										);
									} else {
										if(key_exists($attr_value['mapfrom'],$product_data)){

											if(is_array($product_data[$attr_value['mapfrom']])){
												if($attr_value['mapfrom'] == "product_tag"){
													$product_tag_str = "";

                                                        		                       		foreach ($product_data['product_tag'] as $key => $value){
                                                       	        		                		$product_tag_str .= ",";
                                                                        		                	$product_tag_str .= "$value";
                                                                       	     				}
                                                                  	      	  			$product_tag_str = ltrim($product_tag_str, ",");
                                                                        				$product_tag_str = rtrim($product_tag_str, ",");

													$xml_product[$attr_value['attribute']] = "$product_tag_str";	
 												} else {
													$shipping_str = "";
                        										foreach ($product_data[$attr_value['mapfrom']] as $key => $value){
														$shipping_str .= "||";
													
														foreach($value as $k => $v){

														if($k == "country"){
															$shipping_str .= ":WOOSEA_COUNTRY##$v";
														} elseif ($k == "region"){
															$shipping_str .= ":WOOSEA_REGION##$v";	
														} elseif ($k == "service"){
															$shipping_str .= ":WOOSEA_SERVICE##$v";
														} elseif ($k == "postal_code"){
															$shipping_str .= ":WOOSEA_POSTAL_CODE##$v";
														} elseif ($k == "price"){
															$shipping_str .= ":WOOSEA_PRICE##$attr_value[prefix] $v $attr_value[suffix]";
														} else {
															// UNKNOWN, DO NOT ADD
														}
													}
                        									}
                        									$shipping_str = ltrim($shipping_str, "||");
                        									$shipping_str = rtrim($shipping_str, ":");
                        									$shipping_str = ltrim($shipping_str, ":");
												$shipping_str = str_replace("||:", "||", $shipping_str);

												$xml_product[$attr_value['attribute']] = "$shipping_str";
												}
											} else {
												if(array_key_exists($attr_value['attribute'], $xml_product)){
													$ca = explode("_", $attr_value['mapfrom']);
													$xml_product[$attr_value['attribute']."_$ca[1]"] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
												} else {
													if(strlen($product_data[$attr_value['mapfrom']])){
														
														if(($attr_value['attribute'] == "g:link") OR ($attr_value['attribute'] == "link")){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ."$attr_value[suffix]";	
														} elseif(($attr_value['attribute'] == "g:id") OR ($attr_value['attribute'] == "id") OR ($attr_value['attribute'] == "g:item_group_id")){
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix]". $product_data[$attr_value['mapfrom']] ."$attr_value[suffix]";	
														} else {
															$xml_product[$attr_value['attribute']] = "$attr_value[prefix] ". $product_data[$attr_value['mapfrom']] ." $attr_value[suffix]";	
														}
													}
												}
											}
										}
									}
								}
							}
						}
					}
				}

				// Do we need to do some calculation on attributes for Google Shopping
				$xml_product = $this->woosea_calculate_value ( $project_config, $xml_product ); 

				foreach($xml_product as $key_product => $value_product){
					if (preg_match("/custom_attributes_attribute_/", $key_product)){
						$pieces = explode("custom_attributes_attribute_",$key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					} elseif (preg_match("/product_attributes_/", $key_product)){
						$pieces = explode("product_attributes_",$key_product);
						unset($xml_product[$key_product]);
						$xml_product[$pieces[1]] = $value_product;
					}
				}

				if(!$xml_piece){
					$xml_piece = array ($xml_product);
					unset($xml_product);
				} else {
					array_push ($xml_piece, $xml_product);
					unset($xml_product);
				}
				unset($product_data);	
			}
		endwhile;
		wp_reset_query();

		/**
		 * Update processing status of project
		 */
		//$project_updated = $this->woosea_project_update($project_config['project_hash'], $offset_step_size, $xml_piece);

		/**
		 * Write row to CSV/TXT or XML file
		 */
		if($project_config['fileformat'] != 'xml'){
			unset($products[0]);
			$file = $this->woosea_create_csvtxt_feed ( array_filter($products), $project_config, 'false' );
		} else {
			if(is_array($xml_piece)){
				$file = $this->woosea_create_xml_feed ( array_filter($xml_piece), $project_config, 'false' );
				unset($xml_piece);
			}
			unset($products);
		}

		/**
		 * Update processing status of project
		 */
		$project_updated = $this->woosea_project_update($project_config['project_hash'], $offset_step_size);

		/**
	  	 * Ready creating file, clean up our feed configuration mess now
		 */
		 delete_option('attributes_dropdown');
		 delete_option('channel_attributes');
	}

	/**
 	 * Update processing statistics of batched projects 
 	 */
	public function woosea_project_update($project_hash, $offset_step_size){
        	$feed_config = get_option( 'cron_projects' );
		$nr_projects = count ($feed_config);

		// Information for debug log
		$count_variation = wp_count_posts('product_variation');
		$count_single = wp_count_posts('product');
		$published_single = $count_single->publish;
		$published_variation = $count_variation->publish;
		$published_products = $published_single+$published_variation;

		$product_numbers = array (
      			"Single products" => $published_single,
        		"Variation products" => $published_variation,
        		"Total products" => $published_products,
			"Number projects" => count($feed_config)
		);

                $versions = array (
                        "PHP" => (float)phpversion(),
                        "Wordpress" => get_bloginfo('version'),
                        "WooCommerce" => WC()->version,
                        "Plugin" => WOOCOMMERCESEA_PLUGIN_VERSION
                );

                // Get the sales from created product feeds
		global $wpdb;
                $charset_collate = $wpdb->get_charset_collate();
                $table_name = $wpdb->prefix . 'adtribes_my_conversions';
                $order_rows = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

              	$notifications_obj = new WooSEA_Get_Admin_Notifications;
                $external_debug_file = $notifications_obj->woosea_debug_informations ($versions, $product_numbers, $order_rows, $feed_config);
		// End information for debug log

		foreach ( $feed_config as $key => $val ) {
                
			if(isset($val['product_variations'])){
		                $count_variation = wp_count_posts('product_variation');
                		$count_single = wp_count_posts('product');
				$published_single = $count_single->publish;
                		$published_variation = $count_variation->publish;
                		$published_products = $published_single+$published_variation; 
			} else {
                		$count_single = wp_count_posts('product');
				$published_products = $count_single->publish;
			}

			if ($val['project_hash'] == $project_hash){
				$nrpr = $feed_config[$key]['nr_products_processed'];
				$nr_prods_processed = $nrpr+$offset_step_size;

//				if(is_array($xml_piece)){
					// End of processing batched feed
					if($nrpr >= $feed_config[$key]['nr_products']){

						// Set counters back to 0
						$feed_config[$key]['nr_products_processed'] = 0;
					
						// Set processing status on ready
						$feed_config[$key]['running'] = "ready";
						$project_data['last_updated'] = date("d M Y H:i");
                				$upload_dir = wp_upload_dir();
                				$base = $upload_dir['basedir'];
                				$path = $base . "/woo-product-feed-pro/" . $feed_config[$key]['fileformat'];
                				$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
                				$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

						if (!copy($tmp_file, $new_file)) {
							error_log("Copy of file failed");
						}
						// END
						
						$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
						delete_option( $batch_project );

						// In 2 minutes from now check the amount of products in the feed and update the history count
						wp_schedule_single_event( time() + 120, 'woosea_update_project_stats', array($val['project_hash']) );
					} else {
						$feed_config[$key]['nr_products_processed'] = $nr_prods_processed;
						$feed_config[$key]['running'] = "processing";
		
						// Set new scheduled event for next batch in 3 seconds
						if($offset_step_size < $published_products){
        						if (! wp_next_scheduled ( 'woosea_create_batch_event', array($feed_config[$key]['project_hash']) ) ) {
								wp_schedule_single_event( time() + 2, 'woosea_create_batch_event', array($feed_config[$key]['project_hash']) );
								$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
								update_option( $batch_project, $val);
							}
						} else {
							// No batch is needed, already done processing all products

							// Set counters back to 0
							$feed_config[$key]['nr_products_processed'] = 0;
               		 				$upload_dir = wp_upload_dir();
                					$base = $upload_dir['basedir'];
                					$path = $base . "/woo-product-feed-pro/" . $feed_config[$key]['fileformat'];
                					$tmp_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "_tmp." . $feed_config[$key]['fileformat'];
                					$new_file = $path . "/" . sanitize_file_name($feed_config[$key]['filename']) . "." . $feed_config[$key]['fileformat'];

							if (!copy($tmp_file, $new_file)) {
								error_log("Copy of file failed - small file");
							}
							// END
	
							// Set processing status on ready
							$feed_config[$key]['running'] = "ready";
							$project_data['last_updated'] = date("d M Y H:i");

							$batch_project = "batch_project_".$feed_config[$key]['project_hash'];
							delete_option( $batch_project );

							// In 2 minutes from now check the amount of products in the feed and update the history count
							wp_schedule_single_event( time() + 120, 'woosea_update_project_stats', array($val['project_hash']) );
						}
					}
                	}
        	}
		$nr_projects_cron = count ( get_option ( 'cron_projects' ) );

		/**
		 * Only update the cron_project when no new project was created during the batched run otherwise the new project will be overwritten and deleted
		 */
		if ($nr_projects == $nr_projects_cron){
        		update_option( 'cron_projects', $feed_config);
		}
	}

	/**
	 * Calculate the value of an attribute
	 */
	public function woosea_calculate_value ( $project_config, $xml_product ) {
		// trim whitespaces from attribute values
		$xml_product = array_map('trim', $xml_product);

		// Check for new products in the Google Shopping feed if we need to 'calculate' the identifier_exists attribute value
	    	if(($project_config['taxonomy'] == "google_shopping") AND (isset($xml_product['g:condition'])) AND (!isset($xml_product['g:identifier_exists']))){
			$identifier_exists = "no"; // default value is no

			if (array_key_exists("g:brand", $xml_product) AND ($xml_product['g:brand'] != "")){
				// g:gtin exists and has a value
				if ((array_key_exists("g:gtin", $xml_product)) AND ($xml_product['g:gtin'] != "")){
					$identifier_exists = "yes";
				// g:mpn exists and has a value
				} elseif ((array_key_exists("g:mpn", $xml_product)) AND ($xml_product['g:mpn'] != "")){
					$identifier_exists = "yes";
				// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			} else {
				// g:gtin exists and has a value but brand is empty
				if ((array_key_exists("g:gtin", $xml_product)) AND ($xml_product['g:gtin'] != "")){
					$identifier_exists = "no";
				// g:mpn exists and has a value but brand is empty
				} elseif ((array_key_exists("g:mpn", $xml_product)) AND ($xml_product['g:mpn'] != "")){
					$identifier_exists = "no";
				// g:brand is empty and so are g:gtin and g:mpn, so no identifier exists
				} else {
					$identifier_exists = "no";
				}
			}
			$xml_product['g:identifier_exists'] = $identifier_exists;
		}
		return $xml_product;
	}

	/**
	 * Check if the channel requires unique key/field names and change when needed
	 */
	private function get_alternative_key ($channel_attributes, $original_key) {
		$alternative_key = $original_key;

		if(!empty($channel_attributes)){
			foreach ($channel_attributes as $k => $v){
				foreach ($v as $key => $value){
					if(array_key_exists("woo_suggest", $value)){				
						if ($original_key == $value['woo_suggest']){
							$alternative_key = $value['feed_name'];
						}
					}
				} 
			}
		}
		return $alternative_key;
	}	

	/**
	 * Make product quantity readable
	 */
    	public function clean_quantity( $id, $name ) {
        	$quantity = $this->get_attribute_value( $id, $name );
        	if ($quantity) {
            		return $quantity + 0;
        	}
        	return "0";
    	}

	/**
	 * Make start and end sale date readable
	 */
    	public function get_sale_date($id, $name) {
        	$date = $this->get_attribute_value($id, $name);
        	if ($date) {
            		return date("Y-m-d", $date);
        	}
        	return false;
    	}

	/**
	 * Get product stock
	 */
    	public function get_stock( $id ){
        	$status=$this->get_attribute_value($id,"_stock_status");
        	if ($status) {
            		if ($status == 'instock') {
                		return "in stock";
            		} elseif ($status == 'outofstock') {
                		return "out of stock";
            		}
        	}
        	return "out of stock";
    	}

	/**
	 * Create proper format image URL's
	 */
	public function get_image_url($image_url = ""){
        	if (!empty($image_url)) {
            		if (substr(trim($image_url), 0, 4) === "http" || substr(trim($image_url), 0,5) === "https" || substr(trim($image_url), 0, 3) === "ftp" || substr(trim($image_url), 0, 4) === "sftp") {
                		return rtrim($image_url, "/");
            		} else {
                		$base = get_site_url();
                		$image_url = $base . $image_url;
                		return rtrim($image_url, "/");
            		}
		}
        	return $image_url;
	}

	/**
     	 * Get attribute value
     	 */
    	public function get_attribute_value( $id, $name ){
        	if (strpos($name, 'attribute_pa') !== false) {
        		$taxonomy = str_replace("attribute_","",$name);
            		$meta = get_post_meta($id,$name, true);
            		$term = get_term_by('slug', $meta, $taxonomy);
            		return $term->name;
        	} else {
            		$blaat = get_post_meta($id, $name, true);
            		return get_post_meta($id, $name, true);
        	}
    	}
	/**
	 * Execute category taxonomy mappings
	 */
        private function woocommerce_sea_mappings( $project_mappings, $product_data ){
		$original_cat = $product_data['categories'];
		$original_cat = preg_replace('/&amp;/','&',$original_cat);

		$tmp_cat = "";
		$match = "false";

		foreach ($project_mappings as $pm_key => $pm_array){

			// Strip slashes
			$pm_array['criteria'] = str_replace("\\","",$pm_array['criteria']);
			$pm_array['criteria'] = str_replace("/","",$pm_array['criteria']);
			$original_cat = str_replace("\\","",$original_cat);
			$original_cat = str_replace("/","",$original_cat);

			// First check if there is a category mapping for this specific product
			if(preg_match('/'.$pm_array['criteria'].'/', $original_cat) AND (!empty($pm_array['map_to_category']))){
				$category_pieces = explode("-", $pm_array['map_to_category']);
				$tmp_cat = $category_pieces[0];
				$match = "true";
			}
		}

		if($match == "true"){
			if(array_key_exists('id', $product_data)){
				$product_data['categories'] = $tmp_cat;
			}
		} else {
			// No mapping found so make google_product_category empty
			$product_data['categories'] = "";
		}

		return $product_data;
	}

	/**
	 * Execute project rules 
	 */
        private function woocommerce_sea_rules( $project_rules2, $product_data ){
		$aantal_prods = count($product_data);
		if($aantal_prods > 0){

			foreach ($project_rules2 as $pr_key => $pr_array){

				foreach ($product_data as $pd_key => $pd_value){

					// Check is there is a rule on specific attributes
					if($pd_key == $pr_array['attribute']){

						// This is because for data manipulation the than attribute is empty
						if(!array_key_exists('than_attribute', $pr_array)){
							$pr_array['than_attribute'] = $pd_key;
						}

						// Check if a rule has been set for Google categories
						if (!empty($product_data[$pr_array['than_attribute']]) AND ($product_data[$pr_array['than_attribute']] == "google_category")){
							$pr_array['than_attribute'] = "categories";
							$category_id = explode("-", $pr_array['newvalue']);
							$pr_array['newvalue'] = $category_id[0];
						}

						// Make sure that rules on numerics are on true numerics
						if (!is_array($pd_value) AND (!preg_match('/[A-Za-z]/', $pd_value))){
							$pd_value = strtr($pd_value, ',', '.');
						}

						//$tmp_numeric = round($pd_value);
						if (((is_numeric($pd_value)) AND ($pr_array['than_attribute'] != "shipping"))){

							// Rules for numeric values
							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
									//	$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										$product_data[$pr_array['than_attribute']] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $pd_value);
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pd_value == $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "!="):
									if (($pd_value != $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = ">"):
									if (($pd_value > $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
    									break;
								case($pr_array['condition'] = ">="):
									if (($pd_value >= $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "<"):
									if (($pd_value < $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "=<"):
									if (($pd_value <= $pr_array['criteria'])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "empty"):
									if ((strlen($pd_value) < 1)){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "multiply"):
									$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
									$pd_value = strtr($pd_value, ',', '.');
									$newvalue = $pd_value*$pr_array['criteria'];
									$newvalue = round($newvalue, 2);
									$newvalue = strtr($newvalue, '.',',');			
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "divide"):
									$newvalue = ($pd_value / $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "plus"):
									$newvalue = ($pd_value + $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								case($pr_array['condition'] = "minus"):
									$newvalue = ($pd_value - $pr_array['criteria']);
									$product_data[$pr_array['attribute']] = $newvalue;
									break;
								default:
									break;
							}
						} elseif (is_array($pd_value)) {
							// For now only shipping details are in an array
							foreach ($pd_value as $k => $v){
								foreach ($v as $kk => $vv){
									// Only shipping detail rule can be on price for now
									if($kk == "price"){
										switch ($pr_array['condition']) {
											case($pr_array['condition'] = "contains"):
												if ((preg_match('/'.$pr_array['criteria'].'/', $vv))){
													$pd_value[$k]['price'] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $vv);
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "containsnot"):
												if ((!preg_match('/'.$pr_array['criteria'].'/', $vv))){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "="):
												if (($vv == $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "!="):
												if (($vv != $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = ">"):
												if (($vv > $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
    												break;
											case($pr_array['condition'] = ">="):
												if (($vv >= $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "<"):
												if (($vv < $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "=<"):
												if (($vv <= $pr_array['criteria'])){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "empty"):
												if ((strlen($vv) < 1)){
													$pd_value[$k]['price'] = $pr_array['newvalue'];
													$product_data[$pr_array['than_attribute']] = $pd_value;
												}
												break;
											case($pr_array['condition'] = "multiply"):
												// Only shipping array
												if(is_array($pd_value)){
													$pr_array['criteria'] = strtr($pr_array['criteria'], ',', '.');
													foreach ($pd_value as $ship_a_key => $shipping_arr){
														foreach($shipping_arr as $ship_key => $ship_value){
                                                                        						if($ship_key == "price"){
																$ship_pieces = explode(" ", $ship_value);
																$pd_value = strtr($ship_pieces[1], ',', '.');
                                                                        							$newvalue = $pd_value*$pr_array['criteria'];
                                                                        							$newvalue = round($newvalue, 2);
                                                                        							$newvalue = strtr($newvalue, '.',',');
																$newvalue = $ship_pieces[0]." ".$newvalue;
                                                                        							$product_data[$pr_array['than_attribute']][$ship_a_key]['price'] = $newvalue;	
															}
														}
													}
												}
												break;
											default:
												break;
										}
									}
								}
							}
						} else {
							// Rules for string values
							if (!array_key_exists('cs', $pr_array)){
								$pd_value = strtolower($pd_value);
								$pr_array['criteria'] = strtolower($pr_array['criteria']);
							}			

							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value))){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pr_array['criteria'] == "$pd_value")){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									$ship = $product_data['shipping'];
									break;
								case($pr_array['condition'] = "!="):
									if (($pr_array['criteria'] != "$pd_value")){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = ">"):
									// Use a lexical order on relational string operators
									if (($pd_value > $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = ">="):
									// Use a lexical order on relational string operators
									if (($pd_value >= $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "<"):
									// Use a lexical order on relational string operators
									if (($pd_value < $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(isset($product_data[$pr_array['than_attribute']]) AND (is_array($product_data[$pr_array['than_attribute']]))){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;
								case($pr_array['condition'] = "=<"):
									// Use a lexical order on relational string operators
									if (($pd_value <= $pr_array['criteria'])){
										// Specifically for shipping price rules
										if(is_array($product_data[$pr_array['than_attribute']])){
											$arr_size = (count($product_data[$pr_array['than_attribute']])-1);
											for ($x = 0; $x <= $arr_size; $x++) {
												$product_data[$pr_array['than_attribute']][$x]['price'] = $pr_array['newvalue'];	
											}	
										} else {
											$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
										}
									}
									break;

								case($pr_array['condition'] = "empty"):
									if(empty($product_data[$pr_array['attribute']])){
										$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
									}
									break;
								case($pr_array['condition'] = "replace"):
									$product_data[$pr_array['than_attribute']] = str_replace($pr_array['criteria'], $pr_array['newvalue'], $product_data[$pr_array['than_attribute']]);
									break;

								default:
									break;
							}
						}
					} else {
						// When a rule has been set on an attribute that is not in product_data
						// Add the newvalue to product_data
						if (!array_key_exists($pr_array['attribute'], $product_data)){
							$product_data[$pr_array['than_attribute']] = $pr_array['newvalue'];
						}
					}
				}
			}
		}
		return $product_data;
	}

	/**
	 * Function to exclude products based on individual product exclusions
	 */
	private function woosea_exclude_individual( $product_data ){
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst( get_post_meta( $product_data['id'], '_woosea_exclude_product', true ) );

		if( $product_excluded == "Yes"){
			$allowed = 0;
		}

		if ($allowed < 1){
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}

	/**
	 * Execute project filters (include / exclude) 
	 */
        private function woocommerce_sea_filters( $project_rules, $product_data ){
		$allowed = 1;

		// Check if product was already excluded from the feed
		$product_excluded = ucfirst( get_post_meta( $product_data['id'], '_woosea_exclude_product', true ) );

		if( $product_excluded == "Yes"){
			$allowed = 0;
		}

		foreach ($project_rules as $pr_key => $pr_array){
	
			if(array_key_exists($pr_array['attribute'], $product_data)){

				foreach ($product_data as $pd_key => $pd_value){
					// Check is there is a rule on specific attributes
					if(in_array($pd_key, $pr_array, TRUE)){

						if($pd_key == "price"){
							$pd_value = @number_format($pd_value,2);
						}

						if (is_numeric($pd_value)){
							// Rules for numeric values	
							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pd_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value != $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "!="):
									if (($pd_value == $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 1;
									} elseif (($pd_value == $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">"):
									if (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value <= $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
    									break;
								case($pr_array['condition'] = ">="):
									if (($pd_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "<"):
									if (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "=<"):
									if (($pd_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
								case($pr_array['condition'] = "empty"):
									if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((strlen($pd_value > 0)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								default:
									break;
							}
						} elseif (is_array($pd_value)){
						
							// Tis can either be a shipping or product_tag array
							if($pr_array['attribute'] == "product_tag"){
								if(in_array($pr_array['criteria'], $pd_value, TRUE)) {
									$v = $pr_array['criteria'];

									switch ($pr_array['condition']) {
										case($pr_array['condition'] = "contains"):
											if ((preg_match('/'.$pr_array['criteria'].'/', $v))){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "containsnot"):
											if ((!preg_match('/'.$pr_array['criteria'].'/', $v))){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "="):
											if (($v == $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {
													$allowed = 0;
												}
											} else {
												$allowed = 0;
											}
											break;
										case($pr_array['condition'] = "!="):
											if (($v != $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = ">"):
											if (($v > $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
    											break;
										case($pr_array['condition'] = ">="):
											if (($v >= $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "<"):
											if (($v < $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "=<"):
											if (($v <= $pr_array['criteria'])){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
											break;
										case($pr_array['condition'] = "empty"):
											if (strlen($v) < 1){
												if($pr_array['than'] == "include_only"){
													$allowed = 1;
												} else {	
													$allowed = 0;
												}
											}
											break;
										default:
											break;
									}
								}	
							} else {
								// For now only shipping details are in an array
								foreach ($pd_value as $k => $v){
									foreach ($v as $kk => $vv){
										// Only shipping detail rule can be on price for now
										if($kk == "price"){
											switch ($pr_array['condition']) {
												case($pr_array['condition'] = "contains"):
													if ((preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$allowed = 0;	
													}
													break;
												case($pr_array['condition'] = "containsnot"):
													if ((!preg_match('/'.$pr_array['criteria'].'/', $vv))){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "="):
													if (($vv == $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "!="):
													if (($vv != $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = ">"):
													if (($vv > $pr_array['criteria'])){
														$allowed = 0;
													}
    													break;
												case($pr_array['condition'] = ">="):
													if (($vv >= $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "<"):
													if (($vv < $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "=<"):
													if (($vv <= $pr_array['criteria'])){
														$allowed = 0;
													}
													break;
												case($pr_array['condition'] = "empty"):
													if (strlen($vv) < 1){
														$allowed = 0;
													}
													break;
												default:
													break;
											}
										}
									}
								}
							}
						} else {
							// Filters for string values

							// If case-sensitve is off than lowercase both the criteria and attribute value
							if (array_key_exists('cs', $pr_array)){
								if ($pr_array['cs'] != "on"){
									$pd_value = strtolower($pd_value);
									$pr_array['criteria'] = strtolower($pr_array['criteria']);
								}
							}				

							switch ($pr_array['condition']) {
								case($pr_array['condition'] = "contains"):
									if ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "containsnot"):
									if ((!preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((preg_match('/'.$pr_array['criteria'].'/', $pd_value)) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "="):
									if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")){
										$allowed = 1;
									}
									break;
								case($pr_array['condition'] = "!="):
									if (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "exclude")){
										$allowed = 1;
									} elseif (($pr_array['criteria'] == "$pd_value") && ($pr_array['than'] == "include_only")){
										$allowed = 0; 
									} elseif (($pr_array['criteria'] != "$pd_value") && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">"):
									// Use a lexical order on relational string operators
									if (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = ">="):
									// Use a lexical order on relational string operators
									if (($pd_value >= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "<"):
									// Use a lexical order on relational string operators
									if (($pd_value < $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "=<"):
									// Use a lexical order on relational string operators
									if (($pd_value <= $pr_array['criteria']) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif (($pd_value > $pr_array['criteria']) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								case($pr_array['condition'] = "empty"):
									if ((strlen($pd_value) < 1) && ($pr_array['than'] == "exclude")){
										$allowed = 0;
									} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "exclude")){
										$allowed = 1;
									} elseif ((strlen($pd_value) > 0) && ($pr_array['than'] == "include_only")){
										$allowed = 0;
									}
									break;
								default:
									break;
							}
						}
					}
				}
			} else {
				// A empty rule has been set on an attribute that is not in a product anyhow. Still, remove this product from the feed
				if($pr_array['condition'] == "empty"){
					if($pr_array['than'] == "exclude"){
						$allowed = 0;
					} else {
						$allowed = 1;
					}
				} elseif($pr_array['condition'] == "="){
					if($pr_array['than'] == "exclude"){
						$allowed = 1;
					} else {
						$allowed = 0;
					}
				} elseif($pr_array['condition'] == "contains"){
					if($pr_array['than'] == "exclude"){
						$allowed = 1;
					} else {
						$allowed = 0;
					}
				} else {
					if($pr_array['than'] == "exclude"){
						$allowed = 0;
					} else {
						$allowed = 1;
					}
				}
			}
		}

		if ($allowed < 1){
			$product_data = array();
			$product_data = null;
		} else {
			return $product_data;
		}
	}
}
