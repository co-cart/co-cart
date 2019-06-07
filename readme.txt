=== CoCart === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86
Tags: woocommerce, cart, endpoint, JSON, rest, api, rest-api
Donate link: https://sebdumont.xyz/donate/
Requires at least: 4.9.8
Requires PHP: 5.6
Tested up to: 5.2.1
Stable tag: 1.2.3
WC requires at least: 3.0.0
WC tested up to: 3.6.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A REST-API for WooCommerce that enables the ability to add, view, update and delete items from the cart.

== Description ==

[WooCommerce](https://wordpress.org/plugins/woocommerce/) REST API is great but it's missing one important endpoint that allows you to manage the cart.

That's were [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) comes in. It creates requests for the cart the same way requests via direct URL or Ajax are handled allowing you to add, remove, restore and update items to and from the cart.

= Built with Developers in Mind =

CoCart is created for developers in mind and allows you to use WooCommerce's REST API to it's full potential. This also provides the option to create a full app for your WooCommerce store.

It also works well with official WooCommerce extensions such as:

* Bookings
* Name Your Price
* Pre-Orders
* Product Add-ons

Intrigued? _I bet you are._ [See documentation](https://co-cart.github.io/co-cart-docs/) on how to use CoCart today.

You can also [contribute](https://github.com/co-cart/co-cart/blob/master/CONTRIBUTING.md) to CoCart.

Enjoy!

> #### CoCart Pro
> Want to control more? _I bet you do._
>
> - Add and Remove Coupons to Cart<br />
> - Get Applied Coupons<br />
> - Get Coupon Discount Total<br />
> - Get Cart Total Weight<br />
> - Get Cross Sales<br />
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

No. Only the one in session, just as you would view the cart via the site.

= Where can I report bugs or contribute to the project? =

Bugs can be reported either in the community support forum or preferably on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues).

= Where can I ask for help? =

Please reach out via the official [support forum on WordPress.org](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/).

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Upgrade Notice ==

See changelog for list of changes.

== Changelog ==

= v1.2.3 - 7th June, 2019 =

* Added: Upgrade warning notice in prepartion for CoCart v2 release.

= v1.2.2 - 30th May, 2019 =

* Fix: Plugin would fail to install date and version for future updates.
* Fix: Plugin would fail to redirect to Getting Started page once activated.

> Both of these failed due to reverting a change in the last update to fix the API from crashing.

= v1.2.1 - 21st May, 2019 =

* HOTFIX: Reverted change for including classes so **WC_VERSION** constant was defined first.

= v1.2.0 - 20th May, 2019 =

* NEW: Add Getting Started page to introduce users to view the documentation once installed.
* NEW: Plugin review notice appears after the first week of use.
* Tweaked: Improved code base of the plugin, **NOT** the REST-API.

= v1.1.2 - 17th May, 2019 =

* Tweaked: Allow removing of items via update logic if quantity is zero. Thanks to [@SHoogland](https://github.com/SHoogland)

= v1.1.1 - 25th April, 2019 =

* Checked: Compatibility with WooCommerce v3.6.2
* Updated: Changelog. Forgot to do it in last update.

= v1.1.0 - 23rd April, 2019 =

* Compatible: Support for WooCommerce 3.6+

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).
