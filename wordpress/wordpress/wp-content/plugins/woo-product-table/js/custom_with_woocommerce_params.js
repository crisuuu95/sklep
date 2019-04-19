/* 
 * This is only backup file.
 * It's depreciated now
 * Only for Fronend Section
 * @since 1.0.0
 */


(function($) {
    $(document).ready(function() {
        console.log(woocommerce_params);
        if ( typeof woocommerce_params === 'undefined' ){
            return false;
            //woocommerce_params //wc_add_to_cart_params
        }
        /**
         * Enable select2 for Search Box
         *
        $('.select2').select2({
            minimumResultsForSearch: -1
        });
        */
        //$('.select2,.wptf_varition_section select').select2();
        function Initialize(){
            //$('.wptf_varition_section select').select2();
            /*
             if($('.wptf_varition_section select').length) {
                    $('.wptf_varition_section select').select2({
                        allowClear: true,
                        //width: '100%'
                    });
            }
            */
        }
        //Initialize();
        var ajax_url = woocommerce_params.ajax_url;
        

        load_wptf_cart(ajax_url, 'no');
        function load_wptf_cart(ajax_url,true_false){
            $.ajax({
                type: 'POST',
                url: ajax_url,// + get_data,
                data: {
                    action:     'wptf_cart_auto_load',
                },
                success: function(data) {
                    if(data.search('Your cart is empty.') < 200){
                        $('.tables_cart_message_box').html(data);
                    }
                    if(true_false === 'yes'){
                        $('.tables_cart_message_box').html(data);
                    }

                },
                error: function() {
                },
            });
        }
        
        
        $('body').on('click','a.remove',function(){
            
            var setFewSeconds = setInterval(function(){
                load_wptf_cart(ajax_url, 'yes');
                clearInterval(setFewSeconds);
            },3000);           
        });
        
        /**
         * Cart Auto Load
         *
        setInterval(function(){
                load_wptf_cart(ajax_url, 'no');
            },1000)
        */
        
        $('body').on('click', '.wptf_product_table_wrapper .wptf_thumbnails img', function() {
        //$('.wptf_product_table_wrapper .wptf_thumbnails img').click(function() {
            var image_source, image_array_count, final_image_url, product_title;
            image_source = $(this).attr('srcset');
            image_source = image_source.split(' ');

            image_array_count = image_source.length - 2;
            final_image_url = image_source[image_array_count];
            product_title = $(this).closest('tr').data('title');
            console.log(product_title);
            var html = '<div id="wptf_thumbs_popup" class="wptf_thumbs_popup"><div class="wptf_popup_image_wrapper"><span title="Close" id="wptf_popup_close">&times;</span><h2 class="wptf_wrapper_title">' + product_title + '</h2><div class="wptf_thums_inside">';
            html += '<img class="wptf_popup_image" src="' + final_image_url + '">';
            html += '</div></div></div>';
            if ($('body').append(html)) {
                $('#wptf_thumbs_popup').fadeIn('slow');
                var height = $('#wptf_thumbs_popup').height();
                var height_wrapper = $('.wptf_popup_image_wrapper').innerHeight();
                var request_top = (height - height_wrapper) / 2;
                //$('.wptf_popup_image_wrapper').css('margin-top',height_wrapper + 'px');

            }
        });
        $('body').on('click', '#wptf_thumbs_popup span#wptf_popup_close', function() {
            //$.addClass('saiful');
            $('#wptf_thumbs_popup').remove();
        });


        $('a.button.wptf_woo_add_cart_button.outofstock_add_to_cart_button.disabled').click(function(e) {
            //$('body').on('click','a.outofstock_add_to_cart_button.button.disabled',function(e){    
            e.preventDefault();
            alert('Sorry! Out of Stock!');
            return false;
        });


        $('body').on('click', 'a.wptf_variation_product.single_add_to_cart_button.button.enabled, a.add_to_cart_button.wptf_woo_add_cart_button', function(e) {
            e.preventDefault();
            var thisButton = $(this);
            //Adding disable and Loading class
            thisButton.addClass('disabled');
            thisButton.addClass('loading');

            var product_id = $(this).data('product_id');
            
            var temp_number = $(this).closest('.wptf_action_' + product_id).data('temp_number');
            
            var quantity = $('#table_id_' + temp_number + ' table#wptf_table .wptf_row_product_id_' + product_id + ' .wptf_quantity .quantity input.input-text.qty.text').val();
            var custom_message = $('#table_id_' + temp_number + ' table#wptf_table .wptf_row_product_id_' + product_id + ' .wptf_Message input.message').val();
            var variation_id = $(this).data('variation_id');

            if(!quantity){
                quantity = 1;
            }
            
            var get_data = $(this).attr('href') + '&quantity=' + quantity;//$(this).data('quantity');
            //console.log($('#table_id_' + temp_number + ' table#wptf_table .wptf_row_product_id_' + product_id + ' .wptf_quantity .quantity input').val());
            console.log(get_data);
            $.ajax({
                type: 'POST',
                url: ajax_url,// + get_data,
                data: {
                    action:     'wptf_ajax_add_to_cart',
                    variation:  $(this).data('variation'),
                    variation_id:   variation_id,
                    product_id: product_id,
                    quantity:   quantity,
                    custom_message: custom_message,
                },
                success: function(response) {

                    setFragmentsRefresh( response );
                    
                    load_wptf_cart(ajax_url, 'yes');
                    thisButton.removeClass('disabled');
                    thisButton.removeClass('loading');
                    thisButton.addClass('added');
                },
                error: function() {
                    alert('Failed - Unable to add by ajax');
                },
            });

        });


        $('body').on('click', 'a.wptf_variation_product.single_add_to_cart_button.button.disabled', function(e) {
            e.preventDefault();
            alert("Choose Variation First");
            return false;

        });
        //Alert of out of stock 

        $('body').on('click', 'a.wptf_woo_add_cart_button.button.disabled.loading', function(e) {
            e.preventDefault();
            alert("Adding in Progress");
            return false;

        });


        $('body').on('change','td.data_product_variations .wptf_varition_section',function() {
            var target_action_id = $(this).data('product_id');
            var temp_number = $(this).data('temp_number');
            var target_class = '.wptf_action_' + target_action_id;
            //Please choose right combination.//Message
            var targetRightCombinationMsg = $('#table_id_' + temp_number).data('right_combination_message');


            /**
             * Finally targetPriceSelectorTd has removed becuase we have creaed a new function
             * for targetting any TD of selected Table.
             * This function is targetTD(td_name)
             * @type @call;$
             */
            //var targetPriceSelectorTd = $('#table_id_' + temp_number + ' #price_value_id_' + target_action_id);

            var targetThumbs = $('#table_id_' + temp_number + ' #product_id_' + target_action_id + ' td.wptf_thumbnails img');
            var variations_data = $(this).closest(target_class).data('product_variations');
            var messageSelector = $(this).children('div.wptf_message');
            var addToCartSelector = $(this).parent('td.wptf_action').children('a.wptf_variation_product.single_add_to_cart_button');
            //Checkbox Selector
            var checkBoxSelector = $('.wptf_check_temp_' + temp_number + '_pr_' + target_action_id);

            /**
             * Targetting Indivisual TD Element from Targeted Table. Our Targeted Table will come by temp_number
             * As we have used temp_number and target_action_id in inside function, So this function obvisoulsy shoud
             * declear after to these variable.
             * 
             * @param {String} td_name Actually it will be column names keyword. Suppose, we want to rarget .wptf_price td, than we will use only price as perameter.
             * @returns {$}
             */
            function targetTD(td_name) {
                var targetElement = $('#table_id_' + temp_number + ' #product_id_' + target_action_id + ' td.wptf_' + td_name);
                return targetElement;
            }
            
            /**
             * Set Variations value to the targetted column's td
             * 
             * @param {type} target_td_name suppose: weight,description,serial_number,thumbnails etc
             * @param {type} gotten_value Suppose: variations description from targatted Object
             * @returns {undefined}
             */
            function setValueToTargetTD_IfAvailable(target_td_name, gotten_value){
                //var varitions_description = targetAttributeObject.variation_description;
                if (gotten_value !== "") {
                    targetTD(target_td_name).html(gotten_value);
                }
            }
            
            /**
             * set value for without condition
             * 
             * @param {type} target_td_name for any td
             * @param {type} gotten_value Any valy
             * @returns {undefined}
             */
            function setValueToTargetTD(target_td_name, gotten_value){
                targetTD(target_td_name).html(gotten_value);
            }
            /**
             * 
             * @param {type} target_td_name suppose: weight,description,serial_number,thumbnails etc
             * @param {type} datas_name getting data value from data-something attribute. example: <td data-product_description='This is sample'> s</td>
             * @returns {undefined}
             */
            function getValueFromOldTD(target_td_name, datas_name){
                //Getting back Old Product Description from data-product_description attribute, which is set 
                var product_descrition_old = targetTD(target_td_name).data(datas_name);
                targetTD(target_td_name).html(product_descrition_old);
            }

            var current = {};
            var additionalAddToCartUrl = '';
            $(this).children('select').each(function() {
                var attribute_name = $(this).data('attribute_name');
                var attribute_value = $(this).val();
                current[attribute_name] = attribute_value;
                additionalAddToCartUrl += '&' + attribute_name + '=' + attribute_value;
            });

            var targetVariationIndex = 'not_found';
            variations_data.forEach(function(attributesObject, objectNumber) {
                console.log(attributesObject.attributes);
                console.log(current);
                if (JSON.stringify(current) === JSON.stringify(attributesObject.attributes)) {
                    targetVariationIndex = parseInt(objectNumber);
                }
                //targetVariationIndex = parseInt(objectNumber);
            });
            //console.log(variations_data);
            var wptMessageText = false;
            if (targetVariationIndex !== 'not_found') {
                var targetAttributeObject = variations_data[targetVariationIndex];
                //console.log(targetAttributeObject);
                additionalAddToCartUrl += '&variation_id=' + targetAttributeObject.variation_id;
                //Link Adding
                additionalAddToCartUrl = addToCartSelector.data('add_to_cart_url') + additionalAddToCartUrl;
                addToCartSelector.attr('href', additionalAddToCartUrl);

                //Class adding/Removing to add to cart button
                if (targetAttributeObject.is_in_stock) {
                    disbale_enable_class();
                } else {
                    enable_disable_class();
                }

                //Set variation Array to addToCart Button
                //addToCartSelector targetAttributeObject.attributes
                addToCartSelector.attr('data-variation', JSON.stringify(targetAttributeObject.attributes));
                addToCartSelector.attr('data-variation_id', targetAttributeObject.variation_id);

                //console.log(targetAttributeObject);
                //Set stock Message
                if (targetAttributeObject.availability_html === "") {
                    wptMessageText = '<p class="stock in-stock">In stock</p>';
                } else {
                    wptMessageText = targetAttributeObject.availability_html;
                    //console.log(targetAttributeObject.is_in_stock); //targetAttributeObject.is_purchasable
                }
                //Setup Price Live
                //wptMessageText += targetAttributeObject.price_html;
                //targetPriceSelectorTd.html(targetAttributeObject.price_html);
                //targetTD('price').html(targetAttributeObject.price_html);
                setValueToTargetTD_IfAvailable('price', targetAttributeObject.price_html);

                //Set Image Live for Thumbs
                targetThumbs.attr('src', targetAttributeObject.image.gallery_thumbnail_src);
                targetThumbs.attr('srcset', targetAttributeObject.image.srcset);

                //Set SKU live based on Variations
                setValueToTargetTD_IfAvailable('sku', targetAttributeObject.sku);
                //targetTD('sku').html(targetAttributeObject.sku);
                
                //Set Total Price display_price
                var targetQty = $('#table_id_' + temp_number + ' #product_id_' + target_action_id + ' td.wptf_quantity .quantity input.input-text.qty.text').val();
                if(!targetQty){
                    targetQty = 1;
                }
                var targetQtyCurrency = targetTD('total').data('currency');
                var targetPriceDecimalSeparator = targetTD('total').data('price_decimal_separator');
                var targetPriceThousandlSeparator = targetTD('total').data('thousand_separator');
                var targetNumbersPoint = targetTD('total').data('number_of_decimal');
                var totalPrice = parseFloat(targetQty) * parseFloat(targetAttributeObject.display_price);
                totalPrice = totalPrice.toFixed(targetNumbersPoint);
                var totalPriceHtml = '<strong>' + targetQtyCurrency + ' ' + totalPrice.replace(".",targetPriceDecimalSeparator) + '</strong>';

                setValueToTargetTD_IfAvailable('total',totalPriceHtml);
                targetTD('total').attr('data-price', targetAttributeObject.display_price);
                targetTD('total').addClass('total_general');
                
                //Set Description live based on Varitions's Description
                
                setValueToTargetTD_IfAvailable('description', targetAttributeObject.variation_description);
                /*
                var varitions_description = targetAttributeObject.variation_description;
                if (varitions_description !== "") {
                    targetTD('description').html(targetAttributeObject.variation_description);
                }
                */
                
                
                //var oldBackupWeight = targetTD('wptf_weight').attr('data-weight_backup');
                
                //Set Live Weight //weight_html
                //targetTD('weight').html(targetAttributeObject.weight);
                /**
                 * Set weight based on Variations
                 */
                var finalWeightVal = targetAttributeObject.weight * targetQty;
                finalWeightVal = finalWeightVal.toFixed(2);
                if(finalWeightVal === 'NaN'){
                    finalWeightVal = '';
                }
               targetTD('weight').attr('data-weight',targetAttributeObject.weight);
               //console.log(targetTD('wptf_weight'));
                //Set Weight,Height,Lenght,Width
                setValueToTargetTD_IfAvailable('weight', finalWeightVal);
                setValueToTargetTD_IfAvailable('height', targetAttributeObject.dimensions.height);
                setValueToTargetTD_IfAvailable('length', targetAttributeObject.dimensions.length);
                setValueToTargetTD_IfAvailable('width', targetAttributeObject.dimensions.width);
                
                
                //SEt Width height Live
                //console.log(targetAttributeObject);


            } else {
                addToCartSelector.attr('data-variation', false);
                addToCartSelector.attr('data-variation_id', false);

                wptMessageText = '<p class="wptf_warning warning">' + targetRightCombinationMsg + '</p>'; //Please choose right combination. //Message will come from targatted tables data attribute //Mainly for WPML issues
                //messageSelector.html('<p class="wptf_warning warning"></p>');

                //Class adding/Removing to add to cart button
                enable_disable_class();

                //Reset Price Data from old Price, what was First time
                getValueFromOldTD('price', 'price_html');
                getValueFromOldTD('sku', 'sku');
                setValueToTargetTD('total', '');
                targetTD('total').attr('data-price', '');
                targetTD('total').removeClass('total_general');

                //Getting back Old Product Description from data-product_description attribute, which is set 
                getValueFromOldTD('description', 'product_description');
                //getValueFromOldTD(targatted_td_name,datas_name);
                /**
                var product_descrition_old = targetTD('description').data('product_description');
                targetTD('description').html(product_descrition_old);
                */
                
                var oldBackupWeight = targetTD('weight').attr('data-weight_backup');
                targetTD('weight').attr('data-weight',oldBackupWeight);
                var oldWeightVal = oldBackupWeight * targetQty;
                //Getting Back Old Weight,Lenght,Width,Height
                setValueToTargetTD_IfAvailable('weight', oldWeightVal);
                //getValueFromOldTD('weight', 'weight');
                getValueFromOldTD('length', 'length');
                getValueFromOldTD('width', 'width');
                getValueFromOldTD('height', 'height');
            }

            //Set HTML Message to define div/box
            messageSelector.html(wptMessageText);


            function enable_disable_class() {
                addToCartSelector.removeClass('enabled');
                addToCartSelector.addClass('disabled');


                checkBoxSelector.removeClass('enabled');
                checkBoxSelector.addClass('disabled');


            }
            function disbale_enable_class() {
                addToCartSelector.removeClass('disabled');
                addToCartSelector.addClass('enabled');


                checkBoxSelector.removeClass('disabled');
                checkBoxSelector.addClass('enabled');
            }

        });


        /**
         * Working for Checkbox of our Table
         */
        $('body').on('click', 'input.wptf_tabel_checkbox.wptf_td_checkbox.disabled', function(e) {
            e.preventDefault();
            alert("Sorry, Please choose right combination.");
            return false;
        });


       


        $('a.button.add_to_cart_all_selected').click(function() {
            var temp_number = $(this).data('temp_number');

            //Add Looading and Disable class 
            var currentAllSelectedButtonSelector = $('#table_id_' + temp_number + ' a.button.add_to_cart_all_selected');
            currentAllSelectedButtonSelector.addClass('disabled');
            currentAllSelectedButtonSelector.addClass('loading');

            var add_cart_text = $('#table_id_' + temp_number).data('add_to_cart');

            //Getting Data from all selected checkbox
            var products_data = {};
            var itemAmount = 0;
            $('#table_id_' + temp_number + ' input.enabled.wptf_tabel_checkbox.wptf_td_checkbox:checked').each(function() {
                var product_id = $(this).data('product_id');
                var currentAddToCartSelector = $('#table_id_' + temp_number + ' #product_id_' + product_id + ' .wptf_action a.wptf_woo_add_cart_button');
                var currentCustomMessage = $('#table_id_' + temp_number + ' #product_id_' + product_id + ' .wptf_Message input.message').val();
                var currentVariaionId = currentAddToCartSelector.data('variation_id');
                var currentVariaion = currentAddToCartSelector.data('variation');
                var currentQantity = $('#table_id_' + temp_number + ' table#wptf_table .wptf_row_product_id_' + product_id + ' .wptf_quantity .quantity input.input-text.qty.text').val();
                products_data[product_id] = {
                    product_id: product_id, 
                    quantity: currentQantity, 
                    variation_id: currentVariaionId, 
                    variation: currentVariaion,
                    custom_message: currentCustomMessage,
                };

                //itemAmount += currentQantity;//To get Item Amount with Quantity
                itemAmount++;
                //console.log('#table_id_'+temp_number);
            });

            //Return false for if no data
            if (itemAmount < 1) {
                currentAllSelectedButtonSelector.removeClass('disabled');
                currentAllSelectedButtonSelector.removeClass('loading');
                alert('Please Choose items.');
                return false;
            }
            $.ajax({
                type: 'POST',
                url: ajax_url,
                data: {
                    action: 'wptf_ajax_mulitple_add_to_cart',
                    products: products_data,
                },
                success: function( response ) {
                    setFragmentsRefresh( response );
                    load_wptf_cart(ajax_url, 'yes');
                    
                    currentAllSelectedButtonSelector.removeClass('disabled');
                    currentAllSelectedButtonSelector.removeClass('loading');
                    currentAllSelectedButtonSelector.html(add_cart_text + ' [ ' + itemAmount + ' Added ]');

                },
                error: function() {
                    alert('Failed');
                },
            });
        });
        
        /**
         * 
         * @param {type} response
         * @returns {undefined}
         */
        function setFragmentsRefresh( response ){
            if(response !== 'undefined'){
                    var fragments = response.fragments;
                    //var cart_hash = response.cart_hash;
                    
                    // Replace fragments
                    if ( fragments ) {
                        $.each( fragments, function( key, value ) {
                            $( key ).replaceWith( value );
                        });
                    }
                    
                    var cartCountSelector =$('#wptf_table').data('cart_count_selector');
                    console.log(cartCountSelector);
                    if( cartCountSelector !== '' ){
                        setCartCount( cartCountSelector );
                        //setCartCount( 'span.av-cart-counter.av-active-counter' );
                    }
                    
                    //$('span.av-cart-counter.av-active-counter').html(33);
                }
        }
        
        /**
         * This function will not use every theme, Need to work, if any theme's cart count not working based on Woocommerce default minicart
         * We have set a system, so you able to work to these theme.
         * Just you have to use select of cart count's element.
         * 
         * @param {type} selectorClassOrID
         * @returns {undefined}
         */
        function setCartCount( selectorClassOrID ){

            //Action wptf_cart_info_details
            $.ajax({
                type: 'POST',
                url: ajax_url,// + get_data,
                data: {
                    action:     'wptf_cart_info_details',
                },
                success: function(data) {
                    $( selectorClassOrID ).html( data );

                },
                error: function() {
                },
            });
        }
        
        
        
        
        
        
        
        
        /**
         * Search Box Query and Scripting Here
         * @since 1.9
         * @date 9.6.2018 d.m.y
         */
        
        $( 'body' ).on('click','button.wptf_query_search_button,button.wptf_load_more', function(){
            var loadingText = 'Loading...';
            var searchText = 'Search';
            var loadMoreText = 'Load More';
            var thisButton = $(this);
            var actionType = $(this).data('type');
            var load_type = $(this).data('load_type');
            
            thisButton.html(loadingText);

            var temp_number = $(this).data('temp_number');
            var targetTable = $('#table_id_' + temp_number + ' table#wptf_table');
            var targetTableArgs = targetTable.data( 'data_json' );
            var targetTableBody = $('#table_id_' + temp_number + ' table#wptf_table tbody');
            var pageNumber = targetTable.attr( 'data-page_number' );
            if( actionType === 'query' ){
                pageNumber = 1;
            }

            var key,value;
            var directkey = {};
            $('#search_box_' + temp_number + ' .search_single_direct .query_box_direct_value').each(function(){
                
                key = $(this).data('key');
                value = $(this).val();
                directkey[key] = value;
            });
            var texonomies = {};
            value = false;
            $('#search_box_' + temp_number + ' p.search_select.query').each(function(){
                
                key = $(this).data('key');
                var value = [];var tempSerial = 0;
                $('#' + key + '_' + temp_number + ' input.texonomy_check_box').each(function(Index){
                    if($(this).is(':checked')){
                        value[tempSerial] = $(this).val();
                        tempSerial++;
                    }
                });
                
                
                texonomies[key] = value;
            });
            
            
            
            //Display Loading on before load
            targetTableBody.prepend("<div class='table_row_loader'>Laoding..</div>");
            $.ajax({
                type: 'POST',
                url: ajax_url,// + get_data,
                data: {
                    action:         'wptf_query_table_load_by_args',
                    temp_number:    temp_number,
                    directkey:      directkey,
                    targetTableArgs:targetTableArgs, 
                    texonomies:     texonomies,
                    pageNumber:     pageNumber,
                    load_type:     load_type,
                },
                success: function(data) {
                    $('.table_row_loader').remove();
                    if( actionType === 'query' ){
                        $('#wptf_load_more_wrapper_' + temp_number).remove();
                        targetTableBody.html( data );
                        targetTable.after('<div id="wptf_load_more_wrapper_' + temp_number + '" class="wptf_load_more_wrapper"><button data-temp_number="' + temp_number + '" data-type="load_more" class="button wptf_load_more">Load More</button></div>');
                        thisButton.html(searchText);
                    }
                    if( actionType === 'load_more' ){
                        if(data !== 'Product Not found'){
                            targetTableBody.append( data );
                            thisButton.html(loadMoreText);
                            
                            //Actually If you Already Filter, Than table will load with Filtered.
                            filterTableRow(temp_number);
                        }else{
                            $('#wptf_load_more_wrapper_' + temp_number).remove();
                            alert("There is no more products based on current Query.");
                        }
                        
                    }
                    console.log(pageNumber);
                    pageNumber++; //Page Number Increasing 1 Plus
                    targetTable.attr('data-page_number',pageNumber);
                    //Initialize();
                },
                error: function() {
                    alert("Error On Ajax Query Load. Please check console.");
                    console.log('Error Here');
                },
            });
        });
        
        /**
         * Handleling Filter Features
         */
        $('body').on('change','select.filter_select',function(){
            var temp_number = $(this).data('temp_number');
            filterTableRow(temp_number);
            
        });
        
        $('body').on('click','a.wptf_filter_reset',function(e){
            e.preventDefault();
            var temp_number = $(this).data('temp_number');
            $('#table_id_' + temp_number + ' select.filter_select').each(function(){
                $(this).children().first().attr('selected','selected');
            });
            filterTableRow(temp_number);
        });
        
         $('body').on('click', 'input.wptf_check_universal,input.enabled.wptf_tabel_checkbox.wptf_td_checkbox', function() { //wptf_td_checkbox
            var temp_number = $(this).data('temp_number');
            var checkbox_type = $(this).data('type'); //universal_checkbox
            if (checkbox_type === 'universal_checkbox') {
                $('#table_id_' + temp_number + ' input.enabled.wptf_tabel_checkbox.wptf_td_checkbox').prop('checked', this.checked); //.wptf_td_checkbox
                $('input#wptf_check_uncheck_column_' + temp_number).prop('checked', this.checked);
                $('input#wptf_check_uncheck_button_' + temp_number).prop('checked', this.checked);
            }
            var temp_number = $(this).data('temp_number');
            updateCheckBoxCount(temp_number);
        });
        
        function filterTableRow(temp_number){
            
            //Uncheck All for each Change of Filter
            uncheckAllCheck(temp_number);
            //$('#table_id_' + temp_number + ' input:checkbox').attr('checked',false);
            /**
             * Uncheck All, If any change on filter button
             * @version 2.0
             */
            
            var ClassArray =[];
            var serial = 0;
            $('#table_id_' + temp_number + ' .wptf_filter_wrapper select.filter_select').each(function(){
                var currentClass = $(this).val();
                
                if(currentClass !==''){
                    //console.log(currentClass);
                    ClassArray[serial] = '.' + currentClass;
                    serial++;
                }
                
                //console.log($(this).val());
                //$('table_id_' + temp_number ).hide();
            });
            var finalClassSelctor = '.filter_row' + ClassArray.join(''); //Test will keep
            
            var hideAbleClass = '#table_id_' + temp_number + ' table tr.wptf_row';//wptf_row #table_id_282
            
            /*/**********Addinge class for Hiding Row**************
            $('.HHHHHHHHHHHHH_saiful_islam_kahn').removeClass('HHHHHHHHHHHHH_saiful_islam_kahn');
            $(hideAbleClass).not(finalClassSelctor).addClass('HHHHHHHHHHHHH_saiful_islam_kahn');
            //****************/
           
           
            $(hideAbleClass + ' td.wptf_check input.enabled.wptf_tabel_checkbox').removeClass('wptf_td_checkbox');
            $(hideAbleClass).css('display','none');
            
            $(finalClassSelctor).fadeIn();
            $(finalClassSelctor + ' td.wptf_check input.enabled.wptf_tabel_checkbox').addClass('wptf_td_checkbox');
            
            /**
             * Updating Check Founting Here
             */
            updateCheckBoxCount(temp_number);
        }
        
        function updateCheckBoxCount(temp_number){
            
            var add_cart_text = $('#table_id_' + temp_number).data('add_to_cart');
            var currentAllSelectedButtonSelector = $('#table_id_' + temp_number + ' a.button.add_to_cart_all_selected');
            var itemAmount = 0;
            $('#table_id_' + temp_number + ' input.enabled.wptf_tabel_checkbox:checked').each(function() { //wptf_td_checkbox
                itemAmount++;//To get Item Amount
            });
            var itemText = 'Items';
            if (itemAmount === 1 || itemAmount === 0) {
                itemText = 'Item';
            }
            currentAllSelectedButtonSelector.html( add_cart_text + ' [ ' + itemAmount + ' ' + itemText + ' ]');
        }
        function uncheckAllCheck(temp_number){
            $('#table_id_' + temp_number + ' input:checkbox').attr('checked',false);
        }
    });
})(jQuery);
