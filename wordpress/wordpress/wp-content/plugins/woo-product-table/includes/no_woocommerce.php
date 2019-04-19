<?php
add_shortcode('Product_Table','wptf_if_no_woocommerce');

function wptf_if_no_woocommerce(){
    echo '<a title="Tell us: if need Help" href="mailto:codersaiful@gmail.com" style="color: #d00;padding: 10px;">[WOO Product Table] WooCommerce not Active/Installed</a>';
}