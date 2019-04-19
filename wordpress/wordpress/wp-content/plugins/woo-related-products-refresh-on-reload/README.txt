=== Plugin Name ===
Contributors: eboxnet
Donate link: http://eboxnet.com
Tags: woocommerce,related products,randrom related products,disable related products,set number, related products slider,slider, related products by tag
Requires at least: 4.0
Tested up to: 4.9.5
Stable tag: 4.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

 Woo Related Products...Your Woocommerce related products baker (now including a slider).


== Description ==

 You can now display fresh, random Woocommerce related products on every single product page load (in a slider or not) based on current product's category,product tags or product attributes.
 Starting from version 3.2 you can display related products in posts, pages and sidebar widgets and exclude categories.
 
 Using the new shortcode [woo-related] for product pages 
 or [woo-related id='XX'] / [woo-related product-id='XX' show-title='no']  for posts,pages and widgets.

You can exclude taxonomies using the the option field in settings page.

 Shortcode accepts id, title and number.

 Shortcode examples:
  [woo-related id='15'] 
  will display related products based on Product ID 15.
  [woo-related id='15' title='no'] 
  same as above but will hide Related Products H2 title, for sidebar ie you can use widget title.
  [woo-related id='15' title='no' number='1'] 
  same as above but will return only 1 product.
  [woo-related] 
  will use current product's ID. To be used on product pages only.

= Important in Version 3.2 =
- Added option to exclude taxonomies.
- Added a shortcode.
- Plugin will adds support for shortcodes in sidebar // add_filter('widget_text','do_shortcode');

= Important in Version 3.0 =
- Get related products by WooCommerce Product Attributes (ie color, size, texture etc).

= Important in Version 2.3 =
- New slider ( Owl Carousel ), if previous slider didn't work for your theme please try the new one.
  
= Woo Related Products can help you : =
- Display real related products (using a slider or not).
- Set related product's heading text (you can use HTML).
- Set the number of related products you want to display or Disable them.
- Set Category or Tag based related products.
- Display related products using Flexslider.
- Translate Related Products Heading Text.
- Exclude Taxonomies from your related products.
- Use a shortcode to add related products to posts/pages and widgets.

= Requirements =
- [WooCommerce Plugin](https://wordpress.org/plugins/woocommerce/)

= Plugin Setup =
- Install The plugin - visit istallation tab for more info
- Use plugin's option page to set up the plugin - located as a submenu inside WooCommerce menu -

= Version 3.2 includes =
- The abillity to exclude taxonomies.
- [woo-related] Shortcode.
- Added a couple CSS classes to make styling easier.

= Version 3.1 includes =
- Translations.
- Improvements.

= Version 3.0 includes =
- Compatibility improvements.
- Bug fixes.

= Version 2.0 includes =
- A responsive slider for your related products.
- Since V2.0 Options page is a submebu inside WooCommerce menu tab.
- Improved Code for faster display.
- Multi categories and tags support.
- Bug Fixes.

= Support =
- Feel free to contact me by email or even better use the support section here in wordpress.org and i will get back to you asap.

== Changelog ==

= 3.3.2 7/19/2018 =
*Fix - Fixed number of products for slider.

= 3.3.1 6/29/2018 =
*Fix - Function rename.

= 3.3.0 3/21/2017 =
*Fix - Conflicts.
*Dev - Related products will not include out of stock items.

= 3.2.8 10/01/2017 =
*Fix - Relate by Attribute.
*Tweak - Moved action out of the Class.

= 3.2.5 08/29/2017 =
*Dev - Shortcode Refactor.

= 3.2.2 08/29/2017 =
*Tweak - Slider HTML edits.

= 3.2.1 08/28/2017 =
*Tweak - Undefined variable fix.

= 3.2.0 08/27/2017 =
* Dev - Added the abillity to exclude taxonomies.
* Dev - Added [woo-related] shortcode.
* Tweak - Added woo-related-products-container CSS class to main div.
* Tweak - Added woo-related-shortcode CSS class to shortcode's main div.

= 3.1.0 07/25/2017 =
* Dev - Added the abillity to translate default H2.

= 3.0.5 07/12/2017 =
* Tweak - Added CSS class to H2

= 3.0.5 07/08/2017 =
* Dev - WooCommerce 3.x functions.

= 3.0.0 01/23/2017 =
* Dev - Relate Products by Product Attributes.
* Tweak - Add Style rules.

= 2.3.0 11/22/2016 =
* Tweak - Remove / Edit Functions.
* Tweak - Remove / Add Style rules.
* Dev - Add Owl-Carousel (boosts compatibility).
* Dev - Remove Flexslider.

= 2.2.3 11/11/2016 =
* Dev - New options to control slider navigation and autoplay.
* Tweak - Enable flexslider directionNav (previus - next buttons).

= 2.2.0 11/11/2016 =
* Fix - Final fix to make smooth the transition from 1.x to 2.x

= 2.1.5 11/11/2016 =
* Fix - Fix to avoid re post of plugin's options after upgrading to 2.x [Check forum post] (https://wordpress.org/support/topic/woo-related-products-version-2-0-x/)

= 2.1.1 11/10/2016 =
* Tweak - Conditional include of libraries.

= 2.1 11/10/2016 =
* Fix - Slider ul fix for a few themes.

= 2.0.1 11/10/2016 =
* Tweak - Option for 3 related products.

= 2.0 11/10/2016 =
* Dev - Related products Slider.
* Dev - Multi category & tags support.
* Tweak - Improve Code.
* Tweak - Exclude current product from related products.
* Fix - Error for products with no category or tag.

= 1.0 - 1.9 =
* Fix - Bug Fixes.
* Dev - Add more options.
* Dev - Initial Release.

== Installation ==

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of Woo Related Products, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “Woo Related Products” and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading Woo Related Products plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Related Products Block Position ==

= Move related product = 
Woo Related Products plugin use WordPress hooks to display related products in product's page, 
if you need to move related products block you can remove the action and add it again using a different hook or priority.
This is extremly helpfull if you code your own theme/child theme.

To remove related products block you can use
remove_action('woocommerce_after_single_product', 'wrprrdisplay');
in your theme's function.php file.

If you want to add it again you can do something like this 
add_action('woocommerce_after_single_product', 'wrprrdisplay', 55); 
or add_action('ANY-OTHER-HOOK', 'wrprrdisplay', PRIORITY);

Check GitHub for all [single product's page actions](https://github.com/woocommerce/woocommerce/blob/master/templates/content-single-product.php).


== Demo ==

Click here for [DEMO](http://woorelated.eboxnet.com).