=== CoCart ===
Contributors: sebd86
Tags: woocommerce, cart, endpoint, JSON, rest, api, rest-api
Donate link: https://www.paypal.me/CodeBreaker
Stable tag: 1.0.6
Requires at least: 4.4
Tested up to: 4.9.8
Requires PHP: 5.6
WC requires at least: 3.0.0
WC tested up to: 3.4.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds additional REST-API endpoints for WooCommerce to enable the ability to manage the cart.

== Description ==

WooCommerce REST API is great but it's missing one important endpoint that allows you to manage the cart.

That's were CoCart comes in. It creates requests for the cart the same way Ajax requests are handled allowing you to add, remove, restore and update items to and from the cart.

= Built with Developers in Mind =

CoCart is created for developers in mind and allows you to use WooCommerce's REST API to it's full potential. This also provides the option to create a full app for your WooCommerce store.

Intrigued? _I bet you are._ [See documentation](https://co-cart.github.io/co-cart-docs/) on how to use the API today.

You can also [contribute](https://github.com/co-cart/co-cart/blob/master/CONTRIBUTING.md) to CoCart.

Enjoy!

> #### CoCart Pro
> Want to control more? _I bet you do._
>
> - Add and Remove Coupons to Cart<br />
> - Calculate Shipping Fees<br />
> - Calculate Totals and Fees<br />
> - Support via Slack<br />
> - and possibly more features and add-ons to follow.<br />
>
> [Sign up if you are interested in CoCart Pro](http://eepurl.com/dKIYXE)

= Support the Plugin =

If you use CoCart and find it useful for your project and would like to help keep it maintained or just show some appreciation then please [donate](https://www.paypal.me/CodeBreaker).

All contributions are most appreciated and will go towards improving the API and documentation.

> As this is a free plugin I can not provide support for free. If you are in need of support, please [see support](https://github.com/co-cart/co-cart#support) for details.

== Installation ==

Installing "CoCart" can be done using the following steps:

1. Go to the plugins page of WordPress by clicking 'Add New', search for "CoCart", install and then activate.
2. Alternative Method - Download the plugin from WordPress.org, upload the `cart-rest-api-for-woocommerce` folder to your `/wp-content/plugins/` directory via FTP or upload the cart-rest-api-for-woocommerce.zip file via the plugin page of WordPress by clicking 'Add New' and selecting the zip from your local computer and then activate the plugin.

== Frequently Asked Questions ==

= How do I access the cart endpoints? =
All can be explained via the [documentation](https://co-cart.github.io/co-cart-docs/).

= Does it work with any of the official WooCommerce libraries? =
I'm afraid not. This is because the libraries require authentication which the cart does not require.

= Can I view any customers cart? =
No. Only the one in session, just as you would view the cart via the site.

== Changelog ==
= v1.0.6 - 12th November 2018 =
* Changed: If the cart is empty, the response returns an empty array. - Issue #33 Feedback provided by @joshuaiz
* Improved: Updating items by adding a check to see if there is enough stock. Thanks to @DennisMatise

= v1.0.5 - 11th October 2018 =
* Fixed: Variation and cart item data validation callback. - Issue #40 Thanks to @DennisMatise
* Fixed: A fatal error that caused errors not to return properly. - Issue #35 Thanks to @skunkbad 
* Changed: Name of the plugin is now CoCart. The plugin slug will remain the same.

= v1.0.4 - 5th July 2018 =
* Fixed: Return response for numeric thanks to @campusboy87
* Fixed: Fatal error for adding and updating items when validating the callback `is_numeric`. - Issue #30

= v1.0.3 - 22nd April 2018 =
* Fixed: Syntax error for including cart controller for sites running versions of PHP lower than 7. Thanks to Mr-AjayM for another contribution.
* Fixed: Validation of `cart_item_key` when removing, restoring or updating an item. Item keys starting with a letter were returning false. Reported by @Janie20.
* Tested up to WooCommerce v3.3.5 and up to WordPress v4.9.5

= v1.0.2 - 31st March 2018 =
* Fixed: Invalid Argument Error should the cart be empty. Now returns "Cart is empty" properly. Thanks to Mr-AjayM for the contribution.

= v1.0.1 - 2nd March, 2018 =
* Added: Fetch current cart item data before it is updated.
* Added: New endpoint to restore, remove and update items in cart due to a conflict that prevented from registering the route.
* Corrected: Fetching cart item key as integer to a clean string.
* Corrected: Had response messages for updating quantity backwards. Oops!
* Improved: Made sure it returns a response if the cart is empty.
* Enhanced: Added a check to see if the cart has any items before calculating totals.

= v1.0.0 - 26th February, 2018 =
* Initial release on WordPress.org. Enjoy!

== Screenshots ==
1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Upgrade Notice ==
See changelog for list of changes. I mainly wanted to let you know that the plugin has changed it's name to CoCart.