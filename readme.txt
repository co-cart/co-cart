=== CoCart === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86
Tags: woocommerce, cart, rest, rest-api, JSON
Donate link: https://sebdumont.xyz/donate/
Requires at least: 4.9.8
Requires PHP: 5.6
Tested up to: 5.2.2
Stable tag: 2.0.1
WC requires at least: 3.0.0
WC tested up to: 3.6.5
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Control the cart via the REST-API for WooCommerce.

== Description ==

[WooCommerce](https://wordpress.org/plugins/woocommerce/) REST API is great but it's missing one important ability and that is to manage the shopping cart.

That's were [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) comes in. Just as a customer would interact with an online store in their browser, CoCart allows you to get the cart contents including totals, add, remove, restore and update items to and from the shopping cart via the REST API.

= Built with Developers in Mind =

Exclusively for WooCommerce. With CoCart, running your store completely via the REST API is now complete. Control and manage the shopping cart with ease. Powerful options, clear responses and developer ready for any filtering required to your needs.

CoCart also works well with official WooCommerce extensions such as:

* Bookings
* Name Your Price
* Points and Rewards
* Pre-Orders
* Product Add-ons

More extensions will be supported in CoCart Pro starting with Subscriptions.

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

### More information

* [Visit the CoCart website](https://cocart.xyz/?utm_source=wordpressorg&utm_medium=wp.org&utm_campaign=readme).
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* [Follow on Twitter](https://twitter.com/cart_co)
* [Follow on Instagram](https://www.instagram.com/co_cart/)
* [GitHub](https://github.com/co-cart/co-cart)

This plugin is created and maintained by [Sébastien Dumont](https://sebastiendumont.com).

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

= Where can I find documentation for CoCart? =

The documentation for CoCart can be found [here](https://co-cart.github.io/co-cart-docs/).

= Can I change the layout format/add/change details to the responses? =

You certainly can. Filters are available to do just that (so long as you are using version 2.0+). [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided.

= Does it work with any of the official WooCommerce libraries? =

Only if you request your customers to be logged in first. This is because all the libraries require authentication which the cart does not require.

= Can I view any customers cart? =

Yes but only those with administrator capabilities can and if persistent cart was left enabled. - [See documentation](https://co-cart.github.io/co-cart-docs/#get-customers-cart-contents) for more information.

= Where can I report bugs or contribute to the project? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues).

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/).

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Upgrade Notice ==

v2.0.0 is backwards compatible so you can still use the current API. See https://cocart.xyz/cocart-v2-preview/ for more information on the new API.

== Changelog ==

= v2.0.1 - 18th July, 2019 =

* Tweaked: `get_cart_contents_count()` is now called static.
* Tweaked: Added check for cart totals to make sure they are set before falling back to cart totals in session.
* Dev: Added filter `cocart_update_item` for the response when updating an item.
* Dev: Tweaked CoCart page in the WordPress dashboard to support sections.

= v2.0.0 - 3rd July, 2019 =

* NEW: REST API namespace. CoCart is now an individual API and is no longer nested with WooCommerce's core REST API.
* NEW: Check to see if the cart is set before falling back to the cart in session if one exists.
* NEW: Get a specific customers cart via their customer ID number. - See documentation for details.
* NEW: Product title also returns besides just the product name when getting the cart.
* NEW: Product price also returns when getting the cart.
* Changed: Filter and Action Hook names in new API. - See documentation for details.
* Improved: Complexity of functions for better performance and usage.
* Tweaked: Added checking for items already in the cart.
* Tweaked: Check if cart is empty before removing an item.
* Tweaked: Responses for adding, updating, removing and restoring items to return whole cart if requested.
* Tweaked: Responses for updating items to return the quantity of item along with message.
* Tweaked: Totals can now return once calculated if requested.
* Tweaked: Totals now return from session and can be returned pre-formatted if requested. - See documentation for details.
* Tweaked: New option to refresh cart totals once item has been added or updated.
* Dev: Added action hooks for getting cart, cart is cleared, item added, item removed and item restored.
* Dev: Added filter to allow additional checks before the item is added to the cart.
* Dev: Added filter to apply additional data to return when cart is returned.
* Dev: Added filter to change the size of the thumbnail returned.
* Dev: Added new option to return cart raw if requested.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).
