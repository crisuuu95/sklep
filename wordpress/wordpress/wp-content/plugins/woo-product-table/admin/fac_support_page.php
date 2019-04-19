<?php

/**
 * Faq and contact us page
 * 
 * @since 4.25
 */
function wptf_fac_support_page(){
?>
<div class="wrap wptf_wrap wptf_configure_page">
        <h2 class="plugin_name">Contact & Support</h2>
        <div id="wptf_configuration_form" class="wptf_leftside">
            
            
            <div style="text-align:center;" class="fieldwrap wptf_result_footer">
                
                <img style="margin: 13px auto;max-width: 100%;" src="<?php echo WPTF_BASE_URL; ?>images/cover_image.jpg">
                <hr>
                <div class="wptf_faq_support_link_collection">
                    <a href="https://codecanyon.net/user/codeastrology/portfolio" target="_blank">CodeAstrology Portfolios</a>
                    <a href="https://codecanyon.net/user/codeastrology" target="_blank">CodeAstrology Profile</a>
                    <a href="https://codeastrology.com/support/" target="_blank">CodeAstrology Support</a>
                    <a href="https://codeastrology.com" target="_blank">CodeAstrology.com</a>

                </div>
                <a href="mailto:codersaiful@gmail.com">We are Freelancer</a>
                <hr style="clear: both;">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/D67b_0tQ-z8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                
                
                <br style="clear: both;">
                <a href="https://codecanyon.net/item/woo-product-table-pro/20676867" target="_blank"><img style="margin: 13px auto;max-width: 100%;" src="<?php echo WPTF_BASE_URL; ?>images/cover_image_pro.jpg"></a>
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


    </style>
<?php
}