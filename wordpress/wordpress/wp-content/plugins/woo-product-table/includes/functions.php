<?php

/**
 * Generate Product's Attribute
 * 
 * @global type $product Default global product variable, it will only work inside loop
 * @param type $attributes Array
 * @return string 
 */
function wptf_additions_data_attribute( $attributes = false ){
    global $product;
    $html = false;
    if( $attributes && is_array( $attributes ) && count( $attributes ) > 0 ){
        foreach ( $attributes as $attribute ) :
        $html .= "<div class='wptf_each_attribute_wrapper'>";
            $html .= "<label>" . wc_attribute_label( $attribute->get_name() ) . "</label>";
            
            $values = array();

            if ( $attribute->is_taxonomy() ) {
                    $attribute_taxonomy = $attribute->get_taxonomy_object();
                    $attribute_values = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'all' ) );

                    foreach ( $attribute_values as $attribute_value ) {
                            $value_name = esc_html( $attribute_value->name );

                            if ( $attribute_taxonomy->attribute_public ) {
                                    $values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $attribute->get_name() ) ) . '" rel="tag">' . $value_name . '</a>';
                            } else {
                                    $values[] = $value_name;
                            }
                    }
            } else {
                    $values = $attribute->get_options();

                    foreach ( $values as &$value ) {
                            $value = make_clickable( esc_html( $value ) );
                    }
            }

	$html .= apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );
            
        $html .= '</div>';
        endforeach;
    }
    return $html;
}

/**
 * Checking Value for Select option tag
 * Used in shortcode.php file actually
 * 
 * @param type $got_value
 * @param type $this_value
 * @return type String
 */
function wptf_check_sortOrder($got_value = false, $this_value = 'nothing'){
    return $got_value == $this_value ? 'selected' : ''; 
}

/**
 * To get Final Columns List as Array, where will unavailable default disable_column
 * 
 * @return Array 
 */
function wptf_default_columns_array(){
    $column_array = WPT_Product_Table::$columns_array;
    /**
     * To this disable array, Only available keywords of Column Keyword Array
     * 
     */
    $disable_column_keyword = WPT_Product_Table::$colums_disable_array;
    foreach($disable_column_keyword as $value){
        unset($column_array[$value]);
    }
    return $column_array;//array_keys( $column_keyword );
}

/**
 * We used this function to get default keywords array from default columns array
 * 
 * @return Array Only Keys of Column Array
 * @since 3.6
 */
function wptf_default_columns_keys_array(){
    return array_keys( wptf_default_columns_array() );
}

/**
 * We used this function to get default values array from default columns array
 * 
 * @return Array Only values of Column Array
 * @since 3.6
 */
function wptf_default_columns_values_array(){
    return array_values( wptf_default_columns_array() );
}

/**
 * Taxonomy column generator
 * clue is: tax_
 * 
 * @param type $item_key
 * @return String
 */
function wptf_taxonomy_column_generator( $item_key ){
    $key = 'tax_';
    $len = strlen( $key );
    $check_key = substr( $item_key, 0, $len );
    if( $check_key == $key ){
        return $item_key;
    }
}

/**
 * Custom Fields column generator
 * clue is: cf_
 * 
 * @param type $item_key
 * @return String
 */
function wptf_customfileds_column_generator( $item_key ){
    $key = 'cf_';
    $len = strlen( $key );
    $check_key = substr( $item_key, 0, $len );
    if( $check_key == $key ){
        return $item_key;
    }
}

/**
 * Making new String/description based on word Limit.
 * 
 * @param String $string
 * @param Integer $word_limit
 * @return String
 */
function wptf_limit_words($string = '', $word_limit = 10){
    $words = explode(" ",$string);
    
    $output = implode(" ",array_splice($words,0,$word_limit));
    if( count($words) > $word_limit ){
       $output .= $output . '...'; 
    }
    return $output;
}

/**
 * Go generate as Array from 
 * 
 * @param Array $string Obviously should be an Array, Otherwise, it will generate false.
 * @param Array $default_array Actually if not fount a real String, and if we want to return and default value, than we can set here. 
 * @return Array This function will generate comman string to Array
 */
function wptf_explode_string_to_array($string,$default_array = false) {
    $final_array = false;
    if ($string && is_string($string)) {
        $string = rtrim($string, ', ');
        $final_array = explode(',', $string);
    } else {
        if(is_array( $default_array ) ){
        $final_array = $default_array;
        }
    }
    return $final_array;
}

/**
 * Generate each row data for product table. This function will only use for once place.
 * I mean: in shortcode.php file normally.
 * But if anybody want to use any others where, you have to know about $table_column_keywords and $wptf_each_row
 * both should be Array, Although I didn't used condition for $wptf_each_row Array to this function. 
 * So used: based on your own risk.
 * 
 * @param Array $table_column_keywords
 * @param Array $wptf_each_row
 * @return String_Variable
 */
function wptf_generate_each_row_data($table_column_keywords = false, $wptf_each_row = false) {
    $final_row_data = false;
    if (is_array($table_column_keywords) && count($table_column_keywords) > 0) {
        foreach ($table_column_keywords as $each_keyword) {
            $final_row_data .= ( isset($wptf_each_row[$each_keyword]) ? $wptf_each_row[$each_keyword] : false );
        }
    }
    return $final_row_data;
}

/**Generaed a Array for $wptf_permitted_td 
 * We will use this array to confirm display Table body's TD inside of Table
 * 
 * @since 1.0.4
 * @date 27/04/2018
 * @param Array $table_column_keywords
 * @return Array/False
 */
function wptf_define_permitted_td_array( $table_column_keywords = false ){
    
    $wptf_permitted_td = false;
    if( $table_column_keywords && is_array( $table_column_keywords ) && count($table_column_keywords) > 0 ){
        foreach($table_column_keywords as $each_keyword){
            $wptf_permitted_td[$each_keyword] = true;
        }
    }
    //var_dump($wptf_permitted_td);
    return $wptf_permitted_td;
}

/**
 * Generating <options>VAriation Atribute</option> for Product Variation
 * CAn be removed later.
 * 
 * @param type $current_single_attribute
 * @return string
 */
function wptf_array_to_option_atrribute( $current_single_attribute = false ){
    $html = '<option value>None</option>';
    if( is_array( $current_single_attribute ) && count( $current_single_attribute ) ){
        foreach( $current_single_attribute as $wptf_pr_attributes ){
        $html .= "<option value='{$wptf_pr_attributes}'>" . ucwords($wptf_pr_attributes) . "</option>";
        }
    }
    return $html;
}

/**
 * For Variable product, Variation's attribute will generate to select tag
 * 
 * @param Array $attributes
 * @param Int $product_id
 * @param Int $temp_number
 * @return string HTML Select tag will generate from Attribute
 */
function wptf_variations_attribute_to_select( $attributes , $product_id = false, $default_attributes = false, $temp_number = false){
    $html = false;
    
    //var_dump($attributes);
    //var_dump($default_attributes);
    
    $html .= "<div class='wptf_varition_section' data-product_id='{$product_id}'  data-temp_number='{$temp_number}'>";
    //var_dump($total_attributes);
    foreach( $attributes as $attribute_key_name=>$options ){

        $label = wc_attribute_label( $attribute_key_name );
        $attribute_name = wc_variation_attribute_name( $attribute_key_name );
        $only_attribute = str_replace( 'attribute_', '', $attribute_name);
        
        $default_value = !isset( $default_attributes[$only_attribute] ) ? false : $default_attributes[$only_attribute]; //Set in 3.9.0
        
        $html .= "<select data-product_id='{$product_id}' data-attribute_name='{$attribute_name}' placeholder='{$label}'>";
        $html .= "<option value='0'>" . $label . "</option>";
        foreach( $options as $option ){
            $html .= "<option value='" . esc_attr( $option ) . "' " . ( $default_value == $option ? 'selected' : '' ) . ">" . ucwords($option) . "</option>";
        }
        $html .= "</select>";
        
    }
    $html .= "<div class='wptf_message wptf_message_{$product_id}'></div>";
    $html .= '</div>';

    return $html;
}

/**
 * Actually Its very simple function. If founded Variable - A Array, 
 * Than We want to return a class, Otherwise nothing.
 * V1.0.4 currently not used. Can be used later again.
 * 
 * @deprecated since 1.0.4 1.0.4_10_5.5.2018
 * @param Array $target_array
 * @param String $return_class
 * @return String
 */
function wptf_is_array_class($target_array = false, $return_class = ''){
    if( is_array( $target_array ) && count( $target_array ) > 0 ){
        return $return_class;
    }
}

/**
 * Getting unit amount with unint sign. Suppose: Kg, inc, cm etc
 * woocommerce has default wp_options for weight,height etc's unit.
 * Example: for weight, woocommerce_weight_unit
 * 
 * @param string $target_unit Such as: weight, height, lenght, width
 * @param int $value Can be any number. It also can be floating point number. Normally decimal
 * @return string If get unit and value is gater than o, than it will generate string, otheriwse false
 */
function wptf_get_value_with_woocommerce_unit( $target_unit, $value ){
    $get_unit = get_option( 'woocommerce_' . $target_unit . '_unit' );
    //var_dump($get_unit);
    return ( is_numeric( $value ) && $value > 0 ? $value . ' ' . $get_unit : false );
}


function wptf_adding_body_class( $cass ) {

    global $post,$shortCodeText;

    if( isset($post->post_content) && has_shortcode( $post->post_content, $shortCodeText ) ) {
        $cass[] = 'wptf_pro_table_body';
        $cass[] = 'wptf_pro_table';
        $cass[] = 'woocommerce';
    }
    return $cass;
}
add_filter( 'body_class', 'wptf_adding_body_class' );