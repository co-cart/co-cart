=== Headless WooCommerce powered by CoCart === 
Contributors: sebd86, cocartforwc, ajayghaghretiya, skunkbad, sefid-par, mattdabell, joshuaiz, dmchale, JPPdesigns, inspiredagency, darkchris, mohib007, ashtarcommunications
Tags: woocommerce, cart, rest-api, decoupled, headless, session
Donate link: https://www.buymeacoffee.com/sebastien
Requires at least: 5.5
Requires PHP: 7.3
Tested up to: 5.8
Stable tag: 3.0.15
WC requires at least: 4.3
WC tested up to: 5.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

RESTful API that focuses on the front-end of the store with an integration to build your headless store with ease.

== Description ==

### CoCart: The #1 RESTful API that handles the front-end of WooCommerce.

[CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) focuses on the front-end of the store helping you to manage shopping carts and allows developers to build a headless store in any framework of their choosing. No local storing required.

A powerful RESTful API that offers an integration to build your headless store with ease.

WooCommerce does not nativley come with cart support via the REST API so if you have been struggerling to build a headless store. This is the plugin you've been looking for.

Don't take my word for it. Checkout the testimonials left by startups, freelancers, agencies and many more.

## Testimonials - Developers love it

CoCart, in my opinion, is the best frontend REST API for WooCommerce. Don’t just take my word for it though:

Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

---

This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

---

Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription. [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

---

This plugin works great out of the box for adding products to the Woo cart via API. The code is solid and functionality is as expected, thanks Sebastien! [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

#### More testimonials

[See our wall of love](https://cocart.xyz/wall-of-love/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

## The API

CoCart Lite provides the basic API needs to get you started.

* Get store information.
* Add simple, variable and grouped products to the cart.
* Get customers cart.
* Get customers cart contents.
* Update items in the cart.
* Remove items from the cart.
* Restore items to the cart.
* Re-calculate the totals.
* Retrieve the cart totals.
* Retrieve the number of items in cart or items removed from it.
* Empty the cart.
* Login the customer/user.
* Logout the customer/user.

Also included is the ability to access products, product categories, product tags, product attributes and 
even reviews without the need to authenticate.

* Return all published products.
* Return an individual product by product ID / SKU ID.
* Return an individual variable product and all it’s variations in one request.
* Return all product categories.
* Return all product tags.
* Return all product attributes.
* Return a product attribute terms.
* Return all product reviews.
* Return an individual product review.
* Create a product review.

As an added bonus for administrators or shop managers, CoCart Lite also provides the capabilities to:

* Get Carts in Session.
* Get details of a cart in session.
* View items added in a cart in session.
* Delete a Cart in Session.

## Features

CoCart also provides built in features to:

* **NEW**: Override price for item added to cart.
* **NEW**: Attach customers email address while adding an item to the cart. (Useful for abandoned cart situations.)
* Load a cart in session via the web.
* Support guest customers.
* Supports basic authentication including the use of email as username.
* Support [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).
* Supports multi-sites.
* Does not cache so responses are fast.
* Works across multiple domains, CORS ready (so you can have multiple frontends connected to one backend).
* Allows you to filter CoCart to be white-labelled.

Included with these features are **[filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers to customise API responses or change how CoCart operates.

## Tools and Libraries

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

### CoCart Pro

CoCart Lite is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) completes it with the following [features](https://cocart.xyz/features/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart):

* **Plugin Updates** for 1 year.
* **Priority Support** for [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) users via Slack.
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

Features that will be available in the future:

* **Coming Soon** Remove All Coupons from Cart
* **Coming Soon** Register Customers
* **Coming Soon** Retrieve Checkout Fields (More details on that soon)
* **Coming Soon** Set Cart Customer (In Development)
* **Coming Soon** Create Order (In Development)

For logged in customers:

* **Coming Soon** Return Orders
* **Coming Soon** Return Subscriptions
* **Coming Soon** Return Downloads (Auditing)
* **Coming Soon** Return Payment Methods
* **Coming Soon** Get and Update Profile (In Development)

[Buy CoCart Pro Now](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

[View list of the WooCommerce extensions](https://cocart.xyz/woocommerce-extensions/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) that support CoCart or are supported in [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### Priority support

I aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that I do prioritize support. Communication is handled privately via direct messaging in [Slack](https://app.slack.com/client/TD85PLSMA/) and is available to people who bought [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) or paid for the [14 day priority support](https://cocart.xyz/product/14-day-priority-support/).

#### Add-ons to further enhance your cart.

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** simply filters the session cookie to allow CoCart to work across multiple domains.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it.
* **[Advanced Custom Fields](https://cocart.xyz/add-ons/advanced-custom-fields/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends the products API by returning all your advanced custom fields for products.
* **[Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends the products API by returning Yoast SEO data for products, product categories and product tags.
* and more add-ons in development.

They work with the FREE version of CoCart already, and these add-ons of course come with support too.

### Join our growing community

A Slack community for developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

[Join our community](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Built with developers in mind

Extensible, adaptable, and open source — CoCart is created with developers in mind. If you’re interested to jump in the project, there are opportunities for developers at all levels to get involved. [Contribute to CoCart on the GitHub repository](https://github.com/co-cart/co-cart/blob/master/.github/CONTRIBUTING.md) and join the party. 🎉

Check out [open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aissue+is%3Aopen) and join the [#core channel](https://cocart.slack.com/messages/C014C4581NE) on Slack. If you don’t have a Slack account yet, you can sign up at [https://cocart.xyz/community/](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### Bug reports

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

### More information

* The [CoCart plugin](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) official website.
* The CoCart [Documentation](https://docs.cocart.xyz/)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/co_cart/) and [GitHub](https://github.com/co-cart/co-cart)

#### Credits

This plugin is created by [Sébastien Dumont](https://sebastiendumont.com/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

== Installation ==

= Minimum Requirements =

* WordPress v5.5
* WooCommerce v4.3
* PHP v7.3

= Recommended Requirements =

* WordPress v5.6 or higher.
* WooCommerce v5.2 or higher.
* PHP v7.4

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

You will first need WooCommerce installed with the REST API enabled. Then install CoCart and follow the documentation.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Why should I use CoCart? =

The question is why not! WooCommerce's REST API is only created for controlling the backend of your store. It does not nativley provide an API for controlling the frontend.

CoCart handles your customers cart for you ready to use in any modern framework of your choosing. No local storing required. It's **Powerful** and **Developer** friendly ready to build your headless store the way you want, **without the need to build an API**.

If you are wanting to build a headless store powered by WooCommerce, then CoCart is your solution. But don't take my word for it. [Checkout the testimonials left by others](https://cocart.xyz/wall-of-love/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

With [the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) provided, you’ll see how to add products to a cart and allow your customers to manage it in no time at all.

= Who should use CoCart? =

CoCart is perfect for store owners and developers. If you want to create an e-commerce app for mobile or a custom frontend shopping experience completely using the REST API, then CoCart is for you.

= Do I need to have coding skills to use CoCart? =

As this plugin is built for developers, you will need to have some coding knowledge to use it. [Checkout the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) to get some understanding.

= Where can I find documentation for CoCart? =

You can find the documentation for CoCart on the [CoCart REST API Docs](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= Can I change the layout format/add/change details to the responses? =

You certainly can. Filters are available to do just that. [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided. [View the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) for more.

= Why does CoCart use a custom session handler in the first place? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler does not support guest customers.
- The default session handler does not store additional data that maybe required to help you.
- More consistent with modern web.

= Why does CoCart use a custom session table in the database? =

The default WooCommerce session table only stores the basics of a cart in session. CoCart provides additional data that maybe required to help you and other add-ons/extensions developed by CoCart or third-parties.

Such as when the cart was created. This information is only stored in the browser session.

Also the source of the cart it was last saved. For the web it will be `WooCommerce` and for your headlesss store `CoCart`. This lets you know which version of your store your customers are shopping from should you have both web and app versions.

= Can I have WordPress running on one domain and my headless store on another domain? =

Yes of course. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can [manually enable it via the filters in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

=  Is "WooCommerce Shipping and Tax" plugin supported? =

Not at this time. "WooCommerce Shipping and Tax" ignore any REST API from allowing the ability to calculate the taxes from TaxJar. Code has been contributed to the plugin that will allow third-party plugins enable this ability and awaiting feedback.

However, TaxJar for WooCommerce plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "TaxJar for WooCommerce" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Why CoCart and not WooCommerce Store API? =

WooCommerce Store API is limited and designed mainly to focus on their [Gutenberg blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) they have developed in React. CoCart is designed to focus on decoupling WooCommerce so you can use any framework to allow your store to be headless.

Also, after tweaking WooCommerce Store API to work for decoupled purposes (which CoCart does not require), your still using the default session handler which does not support guest customers. CoCart uses it's own session handler which does support guest customers.

= Do you have a JavaScript Library? =

Yes we do. You can [find it here](https://www.npmjs.com/package/@cocart/cocart-rest-api). It doesn't require authentication for guest customers. It supports CommonJS (CJS) and ECMAScript Modules (ESM). Requests are made with [Axios library](https://github.com/axios/axios) with [support to promises](https://github.com/axios/axios#promises).

= Can I install/update CoCart via Composer? =

Yes. The best method would be to install/update CoCart from the GitHub repository but you can also do so via [https://wpackagist.org/](https://wpackagist.org/search?q=cart-rest-api-for-woocommerce&type=plugin)

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it via the network and all sites will have CoCart enabled.

= Can I enable white labelling for CoCart? =

Yes you can. You will have to edit your `wp-config.php` file to add a new constant. [Details can be found in the documentation](https://cocart.dev/articles/wp-config-php/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart#white-labelling).

= Does CoCart work with the Dokan plugin? =

Yes. The only feature you wont be able to use are coupons if you have [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart). This is because Dokan disables the use of coupons in WooCommerce. All other features are compatible.

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the support forum – be sure to search the forums to confirm that the error has not already been reported.

= CoCart is awesome! Can I contribute? =

Yes, you can! Join in on our [GitHub repository](https://github.com/co-cart/co-cart/blob/master/.github/CONTRIBUTING.md) and follow the [development blog](https://cocart.dev/news/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) to stay up-to-date with everything happening in the project.

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I get help or talk other users about CoCart core? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Slack](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by either purchasing [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) or the [14 day priority support](https://cocart.xyz/product/14-day-priority-support/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= Where can I find out about the pricing of CoCart Pro? =

Find out all relevant [pricing information over on the official site](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= My question is not listed here. Where can I find more answers? =

Check out [Frequently Asked Questions](https://cocart.xyz/faq/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) for more.

== Screenshots ==

1. Empty Cart (API v1)
2. Cart with Item (API v1)

== Contributors & Developers ==

"CoCart Lite" has **not** yet been translated in other languages. You can [translate "CoCart Lite" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

== Changelog ==

If you like CoCart, please take a moment to [provide a review](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/#new-post). It helps to keep the plugin going strong, and is greatly appreciated.

= v3.1.0 - ?? October, 2021 =

## What's New?

* Setup wizard introduced to help identify if the store is new and prepare the environment for headless setup.
* Introduced a new Cart API route that allows developers to add custom callbacks to update the cart for any possibility. - [See example](https://github.com/co-cart/cocart-cart-callback-example).
* CoCart Products add-on now merged with the core and introducing API v2 with a new option to view single products by SKU and many improved tweaks.
* No cache control added to help prevent CoCart from being cached at all so results return quicker.
* Added: Ability to set the customers billing email address while adding item/s to cart. Great for capturing email addresses for cart abandonment.
* Added: Ability to return only requested fields for the cart response before fetching data. Just like GraphQL. Powerful speed performance.
* Added: Ability to set the price of the item you add to the cart with new cart cache system. - Simple Products and Variations ONLY!

> ⚠️ If you have been using CoCart Products add-on, make sure you have the latest version of it installed before updating CoCart to prevent crashing your site. Otherwise best to deactivate the add-on first. Subscription support will remain in CoCart Products add-on until next CoCart Pro update. ⚠️

## Plugin Suggestions

* Added: [Flexible Shipping](https://wordpress.org/plugins/flexible-shipping/)
* Added: [TaxJar for WooCommerce](http://www.taxjar.com/woocommerce-sales-tax-plugin/)
* Removed: CoCart Products Add-on now the products API is merged with core of CoCart.
* Optimized: Results for better performance and cached once a day.

## Bug Fixes

* Coupons duplicating on each load.
* `$item_key` not passed in `validate_item_quantity()` function to validate the quantity allowed for the item.
* Redirect to the "Getting Started" page should no longer happen on every activation.
* Plugin review notice dismiss action.
* Requesting `OPTIONS` for any endpoint to return arguments and schema.
* Log time.

## Enhancments

* Deprecated: Upgrade Warning notice.
* Shipping rates now return meta data if any. Thanks to [@gabrielandujar](https://github.com/gabrielandujar) for contributing.
* Stock check improved when adding item by checking the remaining stock instead.
* Load Cart from Session to allow registered customers to merge a guest cart. - Thanks to [@ashtarcommunications](https://github.com/ashtarcommunications) for contributing.
* Should table creation fail during install, ask user if they have privileges to do so.

## Tweaks

* Cron job for cleanup sessions and removed WooCommerce cron job for cleanup sessions as it is not needed.
* Session abstract now extends `WC_Session` abstract for plugin compatibility for those that strong types.
* Session handler by adding `get_session()` function for plugin compatibility.
* Uninstall: Will reschedule WooCommerce cron job for cleanup sessions.

## Compatibility and Requirements

* Added more compatibility for next update of CoCart Pro.
* Minimum requirement for WordPress is now v5.5
* Tested: ✔️ Compatible with WooCommerce v5.9
* Tested: ✔️ Compatible with WordPress v5.9

## For Developers

* Introduced new filter `cocart_secure_registered_users` to disable security check for using a registered users ID as the cart key.
* Introduced new filter `cocart_override_cart_item` to override cart item for anything extra.
* Introduced new filter `cocart_variable_empty_price` to provide a custom price range for variable products should none exist yet.
* Introduced new filter `cocart_get_price_range` to alter the price range for variable products.
* Introduced new filter `cocart_products_add_to_cart_rest_url` for quick easy direct access to POST item to cart for other product types.
* Introduced new filter `cocart_add_item_query_parameters` to allow developers to extend the query parameters for adding an item.
* Introduced new filter `cocart_add_items_query_parameters` to allow developers to extend the query parameters for adding items.
* Introduced new filter `cocart_cart_query_parameters` to allow developers to extend the query parameters for getting the cart.

= v3.0.15 - 8th November, 2021 =

* Added: Recommended requirements to the installation section of the readme.txt file.
* Added: Support for RTL.
* Fixed: Undefined function for `wp_get_environment_type()` introduced in WordPress 5.5 should the site be running a lower version of WordPress. Reported by [Mohib Salahuddin Ayubi](https://profiles.wordpress.org/mohib007/).
* Fixed: JS bug identifying the parent node for plugin suggestions page.
* Tested: ✔️ Compatible with WooCommerce v5.8

= v3.0.14 - 16th October, 2021 =

* Fixed: Undefined index: `cart` that rarely happens. Reported by [@AlceoMazza](https://github.com/AlceoMazza)

= v3.0.13 - 15th October, 2021 =

**🔥 This is a HOTFIX!**

* Fixed: Fatal error when `$session->save_data()` is called in [JetPack WooCommerce Analytics](https://jetpack.com/support/woocommerce-analytics/) and [WooCommerce Amazon Pay](https://wordpress.org/plugins/woocommerce-gateway-amazon-payments-advanced/) payment gateway.

= v3.0.12 - 2nd August, 2021 =

* Fixed: Validate item quantity by passing missing parameters.
* Fixed: Default package title for [WooCommerce Advanced Shipping Packages](https://woocommerce.com/products/woocommerce-advanced-shipping-packages/) extension.
* Dev: Improved performance in the WordPress dashboard.

= v3.0.11 - 29th July, 2021 =

* Fixed: Product ID not returning as integer once validated instead of a string. 🙈 Thanks to [Christian Kormos](https://profiles.wordpress.org/darkchris/) for reporting the issue.

> Dev note: This will help solve issues with filters using the `$product_id` parameter when a product is added to cart.

= v3.0.10 - 22nd July, 2021 =

* Dev: Stopped custom upgrade notice from being called on plugin page when major update is available for those using WordPress 5.5 or greater. [See article](https://make.wordpress.org/plugins/2021/01/26/reminder-plugins-must-not-interfere-with-updates/)!
* Dev: Package file added for better composer packaging support.

= v3.0.9 - 21st July, 2021 =

* Corrected: Sanitize only. Some functions were escaped when not needed to.

= v3.0.8 - 18th July, 2021 =

* Dev: Plugin package tweaks for future CoCart project.

= v3.0.7 - 14th July, 2021 =

**🔒 This is a SECURTIY FIX!**

> This release brings in a number of fixes to secure the plugin and keep up with WordPress code standards. It is highly recommended that you update to this release.

* Fixed: Escaping HTML from requested cart key.
* Fixed: Sanitized username and password with basic authentication.
* Fixed: Yoda conditions.
* Fixed: Localization for translators.
* Updated: Getting started page with new lowest price to upgrade to CoCart Pro.
* Dev: Documented parameter comments for functions that were missing.
* Dev: Change the use of `date()` function to `gmdate()` function instead.
* Dev: Change the use of `strip_tags()` function to `wp_strip_all_tags()` function instead.

= v3.0.6 - 25th June, 2021 =

* Fixed: Validation of a variation added to cart should the parent ID be used. Thanks to [Brandan King](https://profiles.wordpress.org/inspiredagency/) for reporting the issue.

= v3.0.5 - 28th May, 2021 =

* Tweaked: Adding an item/items now includes the cart parameters so things like the featured image can return if left as the default setting instead of not showing at all when not set.

= v3.0.4 - 19th May, 2021 =

* Fixed: Return error if no matching variation is found.
* Fixed: Validation of empty totals forcing false error message to return.
* Fixed: Empty `backorders` and `featured_image` now returns in cart response. Keeps cart response structure consistent.
* Fixed: Coupon HTML formatting returned in cart response.
* Fixed: Error response when attempting to view a single item that is not in the cart.
* Tweaked: When items in cart are checked for remaining stock, only the first error notice is returned per item.
* Tweaked: Updating, Removing or Restoring an item now includes the cart parameters so things like the featured image can return if left as the default setting instead of not showing at all when not set.
* Tweaked: Localization for validation error messages.
* Tweaked: `cocart_price_no_html()` function to decode HTML so currency symbol returns correctly.

= v3.0.3 - 15th May, 2021 =

* Fixed: Cart hash now returns in cart response after adding first item.
* Dev: Code tweaks and inline doc improvements.
* Dev: Added new helper function to detect CoCart Pro is activated.

= v3.0.2 - 12th May, 2021 =

**🔥 This is a HOTFIX!**

* Fixed: Error when product image is not set. Now fails safely by returning the product placeholder image instead.
* Fixed: Totals total returned value was returning the currency symbol decoded for certain countries so it looked like the total was an incorrect value.
* Tweaked: Convert monetary values given before returning.

= v3.0.1 - 12th May, 2021 =

**🔥 This is a HOTFIX!**

* Fixed: Uncaught error when the cart hash is not generated and saved in session cookie.
* Fixed: Some validation errors not returning when attempting to add item to cart.
* Tweaked: When adding an item and fails. Any remaining error notice that WooCommerce normally returns on the frontend is converted to throw an exception.

= v3.0.0 - 10th May, 2021 =

[See blog post for release notes](https://cocart.xyz/cocart-v3-release-notes/).

### What's New with CoCart v3?

* 🥇 **NEW**: API v2 with new routes to help with the flow.
* 💯 **NEW**: Better cart response based on the experimental free add-on "[Get Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)".
* ⛓️ **NEW**: Carts can sync for guest customers between app and web once "Load Cart from Session" feature has been used.
* 🔑 **NEW**: Basic Authentication now built in with the ability to authenticate via email instead of username. 🥳
* 🔒 **NEW**: Each route can be forced to check if the user (meaning only a logged in user) has permission to use the API. This requires the use of a new filter. [See article for more information](https://cocart.xyz/force-api-permissions/).
* 🔎 **NEW**: Browse and Search CoCart add-ons or supported extensions from the plugin install page.
 * * Search suggestions are added at the bottom of the plugin search results on the first page only if matching key words match.
 * * New plugin install section "CoCart" displays information on available add-ons or supported extensions with compatibility information and links to more details.
* 🧾 **NEW**: Support for [TaxJar for WooCommerce](http://www.taxjar.com/woocommerce-sales-tax-plugin/) plugin if you have **v3.2.5** or above.
* 🚢 **NEW**: Support for [WooCommerce Advanced Shipping Packages](https://woocommerce.com/products/woocommerce-advanced-shipping-packages/) extension.
* 🎁 **NEW**: Support for [WooCommerce Free Gift Coupons](https://woocommerce.com/products/free-gift-coupons/) extension.
* 🗝️ **NEW**: Support for [JWT Auth plugin](https://wordpress.org/plugins/jwt-auth/) by Useful Team.
* 🌗 **NEW**: Compatible with [WP-GraphQL WooCommerce](https://github.com/wp-graphql/wp-graphql-woocommerce) add-on.
* Tweaked: Session data now handled by new abstract to gain more control over it.
* Tweaked: Cart key now returns in the cart response the first time round. 🥳
* Tweaked: The loading of the session handler for better initialization by filtering it outside the action hook `woocommerce_loaded`.
* Tweaked: Loading a cart from session is now prevented if a user is already logged in.
* Tweaked: Loading a cart from session is now prevented if a user is not logged in and the cart key requested matches any registered user.
* Tweaked: Cart session now stores when the cart was created, it's source and hash.
* Tweaked: WooCommerce System Status Tools are made available even if `COCART_WHITE_LABEL` is set to true.
* Deprecated: Redirect to cart after using load cart from session.
* Tested: ✔️ Compatible with WooCommerce v5.3
* Dev: 🐸 **NEW** Update database manually for CoCart via WP-CLI.
* Dev: 🐸 **NEW** Get the current version of CoCart via WP-CLI.
* Dev: Forked `get_customer_unique_id()` from WooCommerce session handler for backwards compatibility. Introduced to help with unit tests in WooCommerce since version 5.3. Not needed for CoCart.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).

== Upgrade Notice ==

= 3.1 =

If you have been using CoCart Products add-on, make sure you have the latest version of it installed before updating CoCart to prevent crashing your site. Otherwise best to deactivate the add-on first.lopers. See changelog for details.
