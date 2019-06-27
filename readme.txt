=== CoCart === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86
Tags: woocommerce, cart, endpoint, JSON, rest, api, rest-api
Donate link: https://sebdumont.xyz/donate/
Requires at least: 4.9.8
Requires PHP: 5.6
Tested up to: 5.2.2
Stable tag: 2.0.0-rc.1
WC requires at least: 3.0.0
WC tested up to: 3.6.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Control the cart via the REST-API for WooCommerce.

== Description ==

[WooCommerce](https://wordpress.org/plugins/woocommerce/) REST API is great but it's missing one important endpoint that allows you to manage the cart.

That's were [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) comes in. It creates requests for the cart the same way requests via direct URL or Ajax are handled allowing you to add, remove, restore and update items to and from the cart.

= Built with Developers in Mind =

CoCart is created for developers in mind and allows you to use WooCommerce's REST API to it's full potential. This also provides the option to create a full app for your WooCommerce store.

It also works well with official WooCommerce extensions such as:

* Bookings
* Name Your Price
* Points and Rewards
* Pre-Orders
* Product Add-ons

Intrigued? _I bet you are._ [See documentation](https://co-cart.github.io/co-cart-docs/) on how to use CoCart today.

Enjoy!

> #### CoCart Pro
> Want to control more? _I bet you do._
>
> - Add and Remove Coupons to Cart<br />
> - Get Applied Coupons<br />
> - Get Coupon Discount Total<br />
> - Get Cart Total Weight<br />
> - Get Cross Sells<br />
> - Get and Set Shipping Methods<br />
> - Get and Set Tax Fees<br />
> - Calculate Shipping Fees<br />
> - Calculate Totals and Fees<br />
> - Calculate Total and Shipping Tax Fees<br />
> - Support via Slack<br />
> - and possibly more features and add-ons to follow.<br />
>
> [Sign up if you are interested in CoCart Pro](http://eepurl.com/dKIYXE)

= Need Support? =

At this time I can **NOT** provide support. [See support](https://github.com/co-cart/co-cart#-support) for details. If you [post a ticket](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) via the community support forum, a member of the community maybe able to help you.

== Installation ==

= Minimum Requirements =

Visit the [WooCommerce server requirements documentation](https://docs.woocommerce.com/document/server-requirements/) for a detailed list of server requirements.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CoCart, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "CoCart" and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= How do I access the cart endpoints? =

All can be explained via the API [documentation](https://co-cart.github.io/co-cart-docs/).

= Does it work with any of the official WooCommerce libraries? =

I'm afraid not. This is because the libraries require authentication which the cart does not require.

= Can I view any customers cart? =

Yes but only those with administrator capabilities can and if persistent cart was left enabled. - [See documentation](https://co-cart.github.io/co-cart-docs/) for more information.

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in the community support forum or preferably on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues).

= Where can I ask for help? =

Please reach out via the official [support forum on WordPress.org](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/).

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Upgrade Notice ==

v2.0.0 is backwards compatible so you can still use the current API. See https://cocart.xyz/cocart-v2-preview/ for more information on the new API.

== Changelog ==

= v2.0.0 - ?? ??, 2019 =

* NEW: REST API namespace. CoCart is now an individual API and is no longer nested with WooCommerce's core REST API.
* NEW: Check to see if the cart is set before falling back to the cart in session if one exists.
* NEW: Get a specific customers cart via their customer ID number. - See documentation for details.
* NEW: Product title also returns besides just the product name when getting the cart.
* NEW: Product price also returns when getting the cart.
* Changed: Filter and Action Hook names in new API. - See documentation for details.
* Improved: Complexity of functions for better performance and usage.
* Tweaked: Added checking for items already in the cart.
* Tweaked: Responses for adding, updating, removing and restoring items to return whole cart if requested.
* Tweaked: Totals can now return once calculated if requested.
* Tweaked: Totals now return from session and can be returned pre-formatted if requested. - See documentation for details.
* Dev: Added action hooks for getting cart, cart is cleared, item added, item removed and item restored.
* Dev: Added filter to allow additional checks before the item is added to the cart.
* Dev: Added filter to apply additional data to return when cart is returned.
* Dev: Added filter to change the size of the thumbnail returned.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).
