<?php


/**
 * Set Menu for WPT (Woo Product Table) Plugin
 * It was actually an individual file, now combine  at 4.1.1
 * 
 * @since 1.0
 * 
 * @package Woo Product Table
 */
function wptf_menu_special_for_free() {
    add_submenu_page('edit.php?post_type=wpt_product_table', 'WOO Product Table', '<stong style="color:red;">Notice</stong>', 'edit_theme_options', 'woo-product-notice', 'wptf_shortcode_notice_page');
    
    
}
add_action('admin_menu', 'wptf_menu_special_for_free');

function wptf_shortcode_notice_page(){
?>
<div class="wrap wptf_wrap wptf_configure_page">
        <h2 class="plugin_name">Notice</h2>
        <div id="wptf_configuration_form" class="wptf_leftside">
            
            
            <div style="text-align:center;" class="fieldwrap wptf_result_footer">
                <h2 class="wptf_notice_message">You have activated <a href="#">Woo Product Table Pro</a>. That's why, Your Free version's Menu as well as Option has disabled.</h2>
                <img style="margin: 13px auto;max-width: 100%;" src="<?php echo WPTF_BASE_URL; ?>images/cover_image.jpg">
                <hr>
                <div class="wptf_faq_support_link_collection">
                    <a href="https://codecanyon.net/user/codeastrology/portfolio" target="_blank">CodeAstrology Portfolios</a>
                    <a href="https://codecanyon.net/user/codeastrology" target="_blank">CodeAstrology Profile</a>
                    <a href="https://codeastrology.com/support/" target="_blank">CodeAstrology Support</a>
                    <a href="https://codeastrology.com" target="_blank">CodeAstrology.com</a>

                </div>
                <a href="mailto:codersaiful@gmail.com">We are Freelancer</a>
                <h3>Video Tutorial Coming Soon ...</h3>
                <br style="clear: both;">
            </div>
            <!-- </form> -->

            <br style="clear: both;">
        </div>
        <!-- Right Side start here -->
        <?php include __DIR__ . '/includes/right_side.php'; ?> 
        
</div>
<style>
.wptf_leftside,.wptf_rightside{float: left;min-height: 500px;}
.wptf_leftside{
    width: 65%;
}
.wptf_rightside{width: 32%; margin-top: -42px;}
.wptf_faq_support_link_collection a {
    text-decoration: none;
    background: #2a3950;
    padding: 3px 6px;
    cursor: pointer;
    display: inline-block;
    color: #a3d5e0;
    border-radius: 5px;
    transition: all 1s;
    margin: 1px;
}
.wptf_faq_support_link_collection a:hover {
    background: #a3d5e0;
    padding: 3px 8px;
    color: #2a3950;
    border-radius: 8px;
}
@media only screen and (max-width: 800px){
    .wptf_leftside.wptf_rightside{float: none !important;width: 100%;}
}
h2.wptf_notice_message {
    border-bottom: 5px solid red;
    padding: 20px;
    background: #e2ff00;
    font-size: 2em;
    line-height: 2em;
    color: #3e0000;
}

    </style>
<?php
}