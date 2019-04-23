=== CoCart === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86
Tags: woocommerce, cart, endpoint, JSON, rest, api, rest-api
Donate link: https://sebdumont.xyz/donate/
Requires at least: 4.9.8
Requires PHP: 5.6
Tested up to: 5.1.1
Stable tag: 1.1.0
WC requires at least: 3.0.0
WC tested up to: 3.6.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A REST-API for WooCommerce that enables the ability to add, view, update and delete items from the cart.

== Description ==

[WooCommerce](https://wordpress.org/plugins/woocommerce/) REST API is great but it's missing one important endpoint that allows you to manage the cart.

That's were [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) comes in. It creates requests for the cart the same way requests via direct URL or Ajax are handled allowing you to add, remove, restore and update items to and from the cart.

= Built with Developers in Mind =

CoCart is created for developers in mind and allows you to use WooCommerce's REST API to it's full potential. This also provides the option to create a full app for your WooCommerce store.

Intrigued? _I bet you are._ [See documentation](https://co-cart.github.io/co-cart-docs/) on how to use CoCart today.

You can also [contribute](https://github.com/co-cart/co-cart/blob/master/CONTRIBUTING.md) to CoCart.

Enjoy!

> #### CoCart Pro
> Want to control more? _I bet you do._
>
> - Add and Remove Coupons to Cart<br />
> - Get and Set Shipping Methods<br />
> - Calculate Shipping Fees<br />
> - Calculate Totals and Fees<br />
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

= v1.1.0 - 23rd April, 2019 =

* Compatible: Support for WooCommerce 3.6+

= v1.0.9 - 5th April, 2019 =

* Compatible: Tested up to WordPress 5.1.1
* Compatible: Tested up to WooCommerce 3.5.7
* Tweaked: Support link in plugin row.

= v1.0.8 - 18th February, 2019 =

* Compatible: Ready for WordPress 5.1 release. 🎊
* Added: Review link to plugins row.

= v1.0.7 - 28th January, 2019 =

* Tweaked: Clear cart now clears cart in session if the user is logged in. - Thanks to @elron for the patch.

= v1.0.6 - 12th November, 2018 =

* Changed: If the cart is empty, the response returns an empty array. - Issue #33 Feedback provided by @joshuaiz
* Improved: Updating items by adding a check to see if there is enough stock. Thanks to @DennisMatise

= v1.0.5 - 11th October, 2018 =

* Fixed: Variation and cart item data validation callback. - Issue #40 Thanks to @DennisMatise
* Fixed: A fatal error that caused errors not to return properly. - Issue #35 Thanks to @skunkbad 
* Changed: Name of the plugin is now CoCart. The plugin slug will remain the same.

= v1.0.4 - 5th July, 2018 =

* Fixed: Return response for numeric thanks to @campusboy87
* Fixed: Fatal error for adding and updating items when validating the callback `is_numeric`. - Issue #30

= v1.0.3 - 22nd April, 2018 =

* Fixed: Syntax error for including cart controller for sites running versions of PHP lower than 7. Thanks to Mr-AjayM for another contribution.
* Fixed: Validation of `cart_item_key` when removing, restoring or updating an item. Item keys starting with a letter were returning false. Reported by @Janie20.
* Tested up to WooCommerce v3.3.5 and up to WordPress v4.9.5

= v1.0.2 - 31st March, 2018 =

* Fixed: Invalid Argument Error should the cart be empty. Now returns "Cart is empty" properly. Thanks to Mr-AjayM for the contribution.

= v1.0.1 - 2nd March, 2018 =

* Added: Fetch current cart item data before it is updated.
* Added: New endpoint to restore, remove and update items in cart due to a conflict that prevented from registering the route.
* Corrected: Fetching cart item key as integer to a clean string.
* Corrected: Had response messages for updating quantity backwards. Oops!
* Improved: Made sure it returns a response if the cart is empty.
* Enhanced: Added a check to see if the cart has any items before calculating totals.

= v1.0.0 - 26th February, 2018 =

* Initial release on WordPress.org