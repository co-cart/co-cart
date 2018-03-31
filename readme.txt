=== Cart REST API for WooCommerce ===
Contributors: sebd86
Tags: woocommerce, cart, endpoint, JSON, rest, api, rest-api
Donate link: https://www.paypal.me/CodeBreaker
Stable tag: 1.0.2
Requires at least: 4.4
Tested up to: 4.9.4
Requires PHP: 5.6
WC requires at least: 3.0.0
WC tested up to: 3.3.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Adds additional REST-API endpoints for WooCommerce to enable the ability to manage the cart.

== Description ==

WooCommerce REST API is great but it's missing one important endpoint that allows you to manage the cart.

That's were Cart REST API for WooCommerce comes in. It uses the Cart the same way Ajax requests are handled. Two added missing components to when viewing the cart contents are the product name and the main featured image URL which is normally requested after.

This saves time running additional requests.

= Built with Developers in Mind =

Cart REST API for WooCommerce is created for developers in mind and allows you to use WooCommerce's REST API to it's full potential. This also provides the option to create a full app for your WooCommerce store.

Intrigued? _I bet you are._ [See documentation](https://seb86.github.io/WooCommerce-Cart-REST-API-Docs/) on how to use the API today.

You can also [contribute](https://github.com/seb86/cart-rest-api-for-woocommerce/blob/master/CONTRIBUTING.md) to Cart REST API for WooCommerce.

Enjoy!

= Support the Plugin =

If you use the Cart REST API for WooCommerce and find it useful for your project and would like to help keep it maintained or just show some appreciation then please [donate](https://www.paypal.me/CodeBreaker).

All contributions are most appreciated and will go towards improving the API and documentation.

> As this is a free plugin I can not provide support for free. If you are in need of support, please [see support](https://github.com/seb86/cart-rest-api-for-woocommerce#support) for details.

== Installation ==

Installing "Cart REST API for WooCommerce" can be done using the following steps:

1. Go to the plugins page of WordPress by clicking 'Add New', search for "Cart REST API for WooCommerce", install and then activate.
2. Alternative Method - Download the plugin from WordPress.org, upload the `cart-rest-api-for-woocommerce` folder to your `/wp-content/plugins/` directory via FTP or upload the cart-rest-api-for-woocommerce.zip file via the plugin page of WordPress by clicking 'Add New' and selecting the zip from your local computer and then activate the plugin.

== Frequently Asked Questions ==

= How do I start to use the cart endpoint? =
All can be explained via the [documentation](https://seb86.github.io/WooCommerce-Cart-REST-API-Docs/).

== Changelog ==

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
Updated endpoint for updating, restoring and removing items due to a conflict that prevented from registering the route. Please see [documentation](https://seb86.github.io/WooCommerce-Cart-REST-API-Docs/) on how to use the endpoint.
