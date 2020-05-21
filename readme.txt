=== Cart REST API for WooCommerce - CoCart Lite === 
Author URI: https://sebastiendumont.com
Plugin URI: https://cocart.xyz
Contributors: sebd86, cocartforwc
Tags: woocommerce, cart, rest, rest-api, JSON
Donate link: https://opencollective.com/cocart
Requires at least: 5.0
Requires PHP: 7.0
Tested up to: 5.4.1
Stable tag: 2.0.13
WC requires at least: 3.6.0
WC tested up to: 4.2.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

A REST API that handles the frontend of WooCommerce that’s Easy and Powerful.

== Description ==

CoCart is a flexible, open-source solution to enabling the shopping cart via the REST API for [WooCommerce](https://wordpress.org/plugins/woocommerce/).

With [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart), running your WooCommerce store completely via the REST API is now possible. Control and manage the shopping cart with ease. Powerful options, clear responses and developer ready for any filtering required to your needs.

= Why should I use CoCart? =

The question is why not! WooCommerce's REST API is only created for controlling the backend of your store. Not the API your needing for your customers who only see the frontend.

If you are wanting to build a headless WooCommerce store then CoCart is your solution.

With [the documentation](https://docs.cocart.xyz/) provided, you’ll learn how to enable the cart for your store in no time.

## Features

CoCart provides the basic features to get you started.

* **NEW**: Cart support for guest customers.
* **NEW**: Logout customer.
* Add simple and variable products to the cart.
* Update items in the cart.
* Remove items from the cart.
* Restore items to the cart.
* Calculate the totals. 
* Retrieve the cart totals.
* View the cart contents.
* Retrieve the item count.
* Empty the cart.
* Supports [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/).
* Supports basic authentication without the need to cookie authenticate.

Included with these features are **[filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers.

* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
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
> - **Coming Soon** Retrieve Checkout Fields (In Development)<br />
> - **Coming Soon** Create Order (In Development)<br />
>
> [Buy CoCart Pro Now](https://cocart.xyz/pricing/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

CoCart Pro also supports:

* **[WooCommerce Subscriptions](https://woocommerce.com/products/woocommerce-subscriptions/)**
* and more extension support in development.

#### Add-ons to further enhance your cart.

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - Get Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the cart response returned with the cart totals, coupons applied, additional product details and more. - **FREE**
* **[CoCart Products](https://cocart.xyz/add-ons/products/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** provides a public version of WooCommerce REST API for accessing products, categories, tags, attributes and even reviews without the need to authenticate.
* **[CoCart Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning Yoast SEO data for products, product categories and product tags. - **REQUIRES COCART PRODUCTS**
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

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Slack](https://cocart.xyz/community/) where you will find like minded developers who help each other out.

== Screenshots ==

1. Empty Cart
2. Viewing the carts content without product thumbnail.
3. Viewing the carts content with product thumbnail.

== Contributors & Developers ==

"CoCart Lite" has **not** yet been translated. You can [translate "CoCart Lite" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

== Changelog ==

= v2.1.2 - ?? May, 2020 =

* NEW: Added support for guest customers.
* NEW: Carts in session are stored in a new database table.
* NEW: Carts are in sync across the web store and your headless store.
* NEW: Added plugin details to **WooCommerce System Status**.
* NEW: Added `uninstall.php` file to delete table and options.
* NEW: Able to transfer a cart from your headless store to the web.
* NEW: Added handlers to improve product validation and extended support for other product types.
* NEW: Can now add items to cart using a products SKU ID.
* NEW: When an item is updated, removed or restored... the cart totals are re-calculated.
* NEW: Added option to logout customer.
* NEW: Variable products are now validated and can find variation ID from attributes if not already set.
* NEW: Prevent password protected products from being added to the cart.
* Changed: Parameter used to set cart key. Previously `id` now `cart_key`. The `id` used as fallback if still used.
* Removed: Parameter to re-calculate totals once item was updated.
* Tweaked: Clear carts debug tool now clears saved carts as well.
* Tweaked: Products that are no longer purchasable and are already in the cart are removed from the cart.
* Tweaked: Stop redirect to getting started page if plugin was activated and was already installed before.
* Tweaked: Prevent redirect to getting started page if multiple plugins activated at once.
* Dev: Clear all carts stored in session via the Tools section of **WooCommerce System Status**.
* Dev: Synchronize carts over to CoCart's session table in the database via the Tools section of **WooCommerce System Status**.
* Dev: Cart expiration can be filtered if the default 30 days is not to your liking.
* Dev: Generated customer ID can be filtered before storing cart in the database and creates a cookie on the customer's device.
* Dev: Added filter `cocart_add_to_cart_validation` to allow plugin developers to pass their own validation before item is added to the cart.
* Dev: Added filters to override the product name `cocart_product_name` and product title `cocart_product_title` when getting the cart contents.
* Dev: Added filter `cocart_item_thumbnail_src` to override the source URL of the product thumbnail when getting the cart contents.
* Dev: Added filter `cocart_add_to_cart_quantity` to override the quantity when adding an item.
* Dev: Added filter `cocart_add_cart_item_data` so other plugins can pass cart item data when adding an item.
* Dev: Added filters so the returned response messages can be changed.
* Dev: Added conditional filter for returning a cart item.
* Dev: Added hook `cocart_user_switched` to allow something to happen if a user has switched.
* Dev: Added hook `cocart_load_cart` to manipulate the merged cart before it set in session.
* Dev: Added hook `cocart_load_cart_override` to manipulate the overriding cart before it set in session.
* Dev: Added hook `cocart_item_added_updated_in_cart` for when an item was added again but updated in cart.
* Dev: Added a new class that handles logging errors.
* Dev: Added filters to admin notices to extend the length of time they hide.
* Dev: Added filter to override cookie check for authenticated users.
* Tested: Compatible with WooCommerce v4.2.x

> This update replaces WooCommerce core session handler with CoCart's. 100% compatible.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).

== Upgrade Notice ==

= 2.1.2 =

Woohoo! Support for guest customers is here! See changelog for full details before updating!