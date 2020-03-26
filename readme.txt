=== Cart REST API for WooCommerce - CoCart Lite === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86, cocartforwc
Tags: woocommerce, cart, rest, rest-api, JSON
Donate link: https://opencollective.com/cocart
Requires at least: 4.9
Requires PHP: 5.6
Tested up to: 5.3.2
Stable tag: 2.0.11
WC requires at least: 3.6.0
WC tested up to: 4.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A REST API that handles the frontend of WooCommerce that’s Easy and Powerful.

== Description ==

CoCart is a flexible, open-source solution to enabling the shopping cart via the REST API for [WooCommerce](https://wordpress.org/plugins/woocommerce/).

With [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart), running your WooCommerce store completely via the REST API is now possible. Control and manage the shopping cart with ease. Powerful options, clear responses and developer ready for any filtering required to your needs.

= Why should I use CoCart? =

WooCommerce REST API is created with developers in mind, so it can integrate with virtually any service and while it does allow developers to easily create and modify. It only allows them to scale a store so much to meet a client’s specifications without further custom development that will enable the stores customers access to the products in order to add them to the shopping cart.

If you are wanting to build a headless WooCommerce store then CoCart is your missing solution.

With [the documentation](https://docs.cocart.xyz/) provided, you’ll learn how to enable the cart for your store in no time.

## Features

CoCart provides the basic features to get you started.

* Add simple and variable products to the cart.
* Update items in the cart.
* Remove items from the cart.
* Restore items to the cart.
* Calculate the totals.
* Get the cart totals.
* View the cart contents.
* Get the item count.
* Empty the cart.
* Supports [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/).
* **NEW** Supports basic authentication without the need to cookie authenticate.

Included with these features are **[filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers.

* **[CoCart Tools](https://github.com/co-cart/cocart-tools)** provides tools to help with development testing with CoCart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.

> ### CoCart Pro
> This plugin is just the tip of the iceberg. CoCart Pro completes it with the following [features](https://cocart.xyz/features/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart):
>
> - Add and Remove Coupons to Cart<br />
> - Retrieve Applied Coupons<br />
> - Retrieve Coupon Discount Total<br />
> - Retrieve Cart Total Weight<br />
> - Retrieve Cross Sells<br />
> - Retrieve and Set Payment Method<br />
> - Retrieve and Set Shipping Methods<br />
> - Retrieve and Set Fees<br />
> - Calculate Shipping Fees<br />
> - Calculate Totals and Fees<br />
> - **NEW** Retrieve Checkout Fields (In Development)<br />
> - **NEW** Create Order (In Development)<br />
>
> [Buy CoCart Pro Now](https://cocart.xyz/pricing/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

CoCart Pro also supports:

* **[WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)**
* and more extension support in development.

#### Add-ons to further enhance your cart.

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - Get Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the cart response returned with the cart totals, coupons applied, additional product details and more. - **FREE**
* **[CoCart Products](https://cocart.xyz/add-ons/products/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** provides a public version of WooCommerce REST API for accessing products, categories, tags, attributes and 
even reviews without the need to authenticate. - **REQUIRES COCART PRO**
* **[CoCart Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning Yoast SEO data for products, product categories and product tags. - **REQUIRES COCART PRO**
* and more add-ons in development.

### Join our growing community

A Slack community for developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

[Join our community](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Built with developers in mind

Extensible, adaptable, and open source — CoCart is created with developers in mind. If you’re interested to jump in the project, there are opportunities for developers at all levels to get involved. [Contribute to CoCart on the GitHub repository](https://github.com/co-cart/co-cart) and join the party.

### Bug reports

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

### More information

* The [CoCart plugin](https://cocart.xyz/?utm_source=wordpressorg&utm_medium=wp.org&utm_campaign=readme) official website.
* The CoCart [Documentation](https://docs.cocart.xyz/)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cart_co), [Instagram](https://www.instagram.com/co_cart/) and [GitHub](https://github.com/co-cart/co-cart)

#### Credits

This plugin is created by [Sébastien Dumont](https://sebastiendumont.com).

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

= Is CoCart free? =

Yes! CoCart’s core features are absolutely free. [CoCart Pro completes the full cart experience!](https://cocart.xyz/pricing/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

= How do I start using CoCart? =

You will first need WooCommerce v3.6 or higher installed with the REST API enabled. Then install CoCart and follow the documentation. That’s it!

= Who should use CoCart? =

CoCart is perfect for store owners and developers. If you want to create an e-commerce app for mobile or a custom frontend shopping experience completely using the REST API, then you need to use CoCart.

= Do I need to have coding skills to use CoCart? =

As this plugin is built for developers you will need to have some coding knowledge to use it. [Checkout the documentation](https://docs.cocart.xyz) to get some understanding.

= Where can I find documentation for CoCart? =

The documentation for CoCart can be [found here](https://docs.cocart.xyz/).

= Can I change the layout format/add/change details to the responses? =

You certainly can. Filters are available to do just that (so long as you are using version 2.0+). [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided.

= Does it work with any of the official WooCommerce libraries? =

Only if you request your customers to be logged in first. This is because all the official libraries require authentication which the cart does not require.

= Can I view any customers cart? =

Yes but only those with administrator capabilities can and if persistent cart was left enabled. - [See documentation](https://docs.cocart.xyz/#get-customers-cart-contents) for more information.

Are you a Mobile app developer? In preparation for CoCart v2.1.0, support for storing cart data will be introduced to make it easier to access specific carts created and your feedback is needed. [Read this article for more details.](https://cocart.xyz/cocart-v2-1-0-beta-2/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

= Where can I report bugs or contribute to the project? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues).

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I ask for help? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/).

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Changelog ==

= v2.0.11 - 25th March, 2020 =

* Removed: `cocart_docs_url` filter for changing documentation link.
* Tested: Compatible with WooCommerce v4.0.x
* Updated: Getting Started page and removed `cocart_getting_started_doc_url` filter for the documentation button.
* Updated: Plugin action link for upgrading to CoCart Pro.
* Updated: Upgrade notices.

> Please temporarily deactivate CoCart and CoCart Pro (if you have it) before updating WooCommerce to version 4.0+ as there is an activation order issue I am still working on fixing. Once you have upgraded WooCommerce simply reactivate CoCart.

= v2.0.10 - 22nd March, 2020 =

* Tweaked: Refresh totals parameter is now set to `true` by default when item is updated.

= v2.0.9 - 19th March, 2020 =

* Corrected: Passed parameter to get specific customers cart.
* Tweaked: Validation of returning persistent cart.

= v2.0.8 - 6th March, 2020 =

* Dev: Added filter `cocart_return_empty_cart` to empty cart response so developers can use it as they see fit.

= v2.0.7 - 5th March, 2020 =

* Disabled: Cookie authentication REST check, only if site is secure when authenticating the basic method.
* Removed: Filter for session class handler as we need it to be untouched.
* Tested: Compatible with WooCommerce v3.9.x
* Tweaked: Use `get_current_user_id()` instead of `is_user_logged_in()` to check if user is logged in.

> The cookie check is only disabled when making a request with CoCart.

= v2.0.6 - 1st October, 2019 =

* Added: Link to translate CoCart on the plugin row.
* Tweaked: Upgrade admin notice for next release.
* Tweaked: URL to latest beta news under the plugin row.

= v2.0.5 - 14th September, 2019 =

* Added: Support for WooCommerce's authentication method.

= v2.0.4 - 26th August, 2019 =

* Added: More FAQ's to readme.txt file for the WordPress plugin directory.
* Changed: Title of the plugin in readme.txt file to improve SEO Results.
* Changed: Minimum WooCommerce version required and supported is v3.6.
* Tweaked: Upgrade link now shows always once plugin is installed, not after 1 week.
* Tweaked: Upgrade link colour changed from green to red to stand out more.

= v2.0.3 - 19th August, 2019 =

* Added: A notice under the plugin row providing information on future versions coming that require your feedback.
* Tested: Compatible with WooCommerce v3.7
* Tweaked: Admin body class for CoCart page.
* Updated: Documentation URL has changed to https://docs.cocart.xyz

= v2.0.2 - 19th July, 2019 =

* Tweaked: Updated link to getting started page if CoCart was installed via WP-CLI.

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

== Upgrade Notice ==

= 2.0.11 =

Please temporarily deactivate CoCart and CoCart Pro (if you have it) before updating WooCommerce to version 4.0+ as there is an activation order issue I am still working on fixing. Once you have upgraded WooCommerce simply reactivate CoCart.
