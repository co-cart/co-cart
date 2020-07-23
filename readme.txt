=== Cart REST API for WooCommerce - CoCart Lite === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86, cocartforwc
Tags: woocommerce, cart, rest, rest-api, JSON, session
Donate link: https://www.buymeacoffee.com/sebastien
Requires at least: 5.2
Requires PHP: 7.0
Tested up to: 5.5
Stable tag: 2.4.0
WC requires at least: 4.0.0
WC tested up to: 4.3.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A REST API that handles the frontend of WooCommerce that’s Easy and Powerful.

== Description ==

### CoCart: The #1 REST API that handles the frontend of WooCommerce.

[CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) handles the shopping cart in any modern framework of your choosing. No local storing required. Powerful and developer friendly ready to build your headless store, **without building an API**.

= Why should I use CoCart? =

The question is why not! WooCommerce's REST API is only created for controlling the backend of your store. Not the API your needing for your customers who only see the frontend.

If you are wanting to build a headless WooCommerce store for your customers then CoCart is your solution.

With [the documentation](https://docs.cocart.xyz/) provided, you’ll learn how to enable the ability to add products to the cart and manage it in no time.

## Features

CoCart Lite provides the basic features to get you started.

* Add simple and variable products to the cart.
* Update items in the cart.
* Remove items from the cart.
* Restore items to the cart.
* Calculate the totals. 
* Retrieve the cart totals.
* View the carts contents.
* Retrieve the item count.
* Empty the cart.
* Logout customer.
* Supports guest customers.
* Supports basic authentication without the need to cookie authenticate.
* Supports [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/).

Included with these features are **[filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers.

* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.

### CoCart Pro
CoCart Lite is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) completes it with the following [features](https://cocart.xyz/features/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart):

* **Priority** Support for [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) users.
* Add and Remove Coupons to Cart
* Retrieve Applied Coupons
* Retrieve Coupon Discount Total
* Retrieve Cart Total Weight
* Retrieve Cross Sells
* Retrieve and Set Payment Method
* Retrieve and Set Shipping Methods
* Retrieve and Set Fees
* Calculate Shipping Fees
* Calculate Totals and Fees
* **Coming Soon** Retrieve Checkout Fields (Auditing)
* **Coming Soon** Set Cart Customer (In Development)
* **Coming Soon** Create Order (In Development)
* **Coming Soon** Return Customers Orders (Auditing)
* **Coming Soon** Return Customers Subscriptions (Auditing)
* **Coming Soon** Return Customers Downloads (Auditing)
* **Coming Soon** Return Customers Payment Methods (Auditing)
* **Coming Soon** Get and Update Customers Profile (In Development)

[Buy CoCart Pro Now](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

[View list of the WooCommerce extensions](https://cocart.xyz/woocommerce-extensions/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) that support CoCart or are supported in [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### Priority support

I aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that I do prioritize support. Communication is handled privately via direct messaging in [Slack](https://app.slack.com/client/TD85PLSMA/) and is available to people who bought [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) or paid for the [14 day priority support](https://cocart.xyz/product/14-day-priority-support/).

#### Add-ons to further enhance your cart.

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[Get Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the cart response returned with the cart totals, coupons applied, additional product details and more. - **FREE**
* **[Products](https://cocart.xyz/add-ons/products/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** provides a public and better version of WooCommerce REST API for accessing products, categories, tags, attributes and even reviews without the need to authenticate.
* **[Advanced Custom Fields](https://cocart.xyz/add-ons/advanced-custom-fields/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning all your advanced custom fields for products. - **REQUIRES COCART PRODUCTS**
* **[Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning Yoast SEO data for products, product categories and product tags. - **REQUIRES COCART PRODUCTS**
* and more add-ons in development.

They work with the FREE version of CoCart already, and these add-ons of course come with support too.

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

Yes! CoCart’s core features are absolutely free. [CoCart Pro completes the full cart experience!](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

= How do I start using CoCart? =

You will first need WooCommerce v3.6 or higher installed with the REST API enabled. Then install CoCart and follow the documentation. That’s it!

= Why should I use CoCart? =

The question is why not! WooCommerce's REST API is only created for controlling the backend of your store. Not the API your needing for your customers who only see the frontend.

If you are wanting to build a headless WooCommerce store then CoCart is your solution.

= Who should use CoCart? =

CoCart is perfect for store owners and developers. If you want to create an e-commerce app for mobile or a custom frontend shopping experience completely using the REST API, then you need to use CoCart.

= Do I need to have coding skills to use CoCart? =

As this plugin is built for developers, you will need to have some coding knowledge to use it. [Checkout the documentation](https://docs.cocart.xyz) to get some understanding.

= Where can I find documentation for CoCart? =

The documentation for CoCart can be [found here](https://docs.cocart.xyz/).

= Can I change the layout format/add/change details to the responses? =

You certainly can. Filters are available to do just that. [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided. [View the documentation](https://docs.cocart.xyz/) for more.

= Does it work with any of the official WooCommerce libraries? =

Only if you request your customers to be logged in. This is because all the official libraries require authentication which the cart does not require.

= Where can I report bugs or contribute to the project? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues).

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I ask for help? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Slack](https://cocart.xyz/community/) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by either purchasing [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) or the [14 day priority support](https://cocart.xyz/product/14-day-priority-support/).

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Contributors & Developers ==

"CoCart Lite" has **not** yet been translated. You can [translate "CoCart Lite" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

== Changelog ==

= v2.4.0 - 23rd July, 2020 =

The code base was improved to prevent errors should WooCommerce not be activated while CoCart still is.

* **NEW**: Added another note to remind users that they can activate CoCart Pro if installed but not activated.
* Fixed: Fatal error for `WC_Session` class if WooCommerce is disabled but CoCart was not.
* Improved: Newly added notes for WooCommerce Admin inbox.
* Tweaked: WooCommerce System Tools to hide options to clear WooCommerce sessions and synchronizes carts if table is empty.
* Tested: Compatible with WooCommerce v4.3.1
* Tested: Compatible with WordPress v5.5

= v2.3.1 - 18th July, 2020 =

* Corrected: Thank you note.
* Fixed: Installation/Update of CoCart.
* Fixed: Return of system status data.

= v2.3.0 - 14th July, 2020 =

This release brings an improved code base for the backend and connects with WooCommerce's Admin bar. New notes exclusively for CoCart have been created that are triggered for when the client needs them. This release also makes preparations for CoCart v3.0 and tested with WooCommerce v4.3

* **NEW**: Connected with WooCommerce Admin.
* **NEW**: Notes are provided for help, feedback and guides.
* Added: Preparations for CoCart v3.0
* Added: Plugin requirements to main plugin file header.
* Bumped: WooCommerce minimum requirement to v4.0
* De-bumped: PHP minimum requirement to v7.0 to match WooCommerce's current requirement.
* Tested: Compatible with WooCommerce v4.3
* Improved: Code base for the backend.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).

== Upgrade Notice ==

= 2.4.0 =

* Fixed: Fatal error for `WC_Session` class if WooCommerce was disabled but CoCart was not.
