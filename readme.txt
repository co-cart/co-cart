=== CoCart - Headless ecommerce === 
Contributors: cocartforwc, sebd86, ajayghaghretiya, skunkbad, sefid-par, mattdabell, joshuaiz, dmchale, JPPdesigns, inspiredagency, darkchris, mohib007, rozaliastoilova, ashtarcommunications, albertoabruzzo
Tags: woocommerce, cart, rest-api, decoupled, headless, session, api, json, http
Donate link: https://www.buymeacoffee.com/sebastien
Requires at least: 5.6
Requires PHP: 7.3
Tested up to: 5.9
Stable tag: 3.2.0
WC requires at least: 4.3
WC tested up to: 6.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Customizable REST API that lets you build headless ecommerce without limits powered by WooCommerce.

== Description ==

### CoCart is #1 ecommerce RESTful API built for WooCommerce that scales for headless development.

Take your WooCommerce business to the next level with headless ecommerce.

Get started fast with [CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)'s customizable REST API for WooCommerce and build headless ecommerce using your favorite technologies.

## Is CoCart right for my business?

CoCart’s REST API makes it possible for businesses to build a complete custom storefront. It’s API-first, enabling your business to take the shopping experience to the next level.

Made by and for developers, CoCart immediately allows you to create sophisticated experiences fast with unlimited possibilities.

With our extensive documentation and resources available, CoCart is a plug and play solution that works out of the box.

Save yourself 80% of a headache and hours of development time.

## Modern stack

Use your preferred tools and favorite modern technologies like [NextJS](https://nextjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/), [Ember](https://emberjs.com/) and more giving you endless flexibility and customization.

## Why CoCart?

WooCommerce does not natively come with a products API that does not require authorization or a cart API in general. So even if you are new to building a headless ecommerce or already have a WooCommerce store and been meaning to go headless, nows the time to start.

Don't take my word for it. Checkout the testimonials left by startups, freelancers, agencies and many more.

## Testimonials - Developers love it

> Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

---

> This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

---

> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription. [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

---

> This plugin works great out of the box for adding products to the cart via API. The code is solid and functionality is as expected, thanks Sebastien! [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

#### More testimonials

[See our wall of love](https://cocart.xyz/wall-of-love/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

## The API

CoCart Lite provides the basic needs to help you get started.

* Get store information.
* Add simple, variable and grouped products to the cart by product ID / SKU ID.
* Get customers cart.
* Get customers cart contents.
* Update items in the cart both individually or in bulk.
* Remove items from the cart.
* Restore items to the cart.
* Re-calculate the totals.
* Retrieve the cart totals.
* Retrieve the number of items in cart or items removed from it.
* Empty the cart.
* Login the customer/user.
* Logout the customer/user.

Also included is a specially designed API to access products, product categories, product tags, product attributes and even reviews without the need to authenticate.

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

All the information you need about a product and conditions to help you with UX/UI development is all provided.

As an added bonus for administrators or shop managers, CoCart Lite also provides the capabilities to:

* Get Carts in Session.
* Get details of a cart in session.
* View items added in a cart in session.
* Delete a Cart in Session.

## Features

CoCart also provides built in features to:

* **NEW**: Override price for simple or variable products added to cart.
* **NEW**: Attach customers email address while adding an item to the cart. (Useful for abandoned cart situations.)
* Load a cart in session via the web. (Useful if you don't have a headless checkout and want to use native checkout.)
* Support guest customers.
* Supports basic authentication including the use of email as username.
* Support [authentication via WooCommerce's method](https://cocart.dev/authenticating-with-woocommerce-heres-how-you-can-do-it/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).
* Supports multi-sites.
* Does not cache so responses are fast.
* Works across multiple domains, CORS ready (so you can have multiple frontends connected to one backend).
* Allows you to filter CoCart to be white-labelled.

Included with these features are over 100+ **[filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers to customize API responses or change how CoCart operates with our extensive documentation.

## Tools and Libraries

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to easily update to prerelease versions of CoCart Lite for testing and development purposes.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product types to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Official Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

### CoCart Pro

CoCart Lite is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) extends with the following [features](https://cocart.xyz/features/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart):

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

* **Coming Soon** Remove all Coupons from Cart
* **Coming Soon** Register Customers
* **Coming Soon** Retrieve checkout fields (More details on that soon)
* **Coming Soon** Set cart customer (In Development)
* **Coming Soon** Create Order (In Development)

For a logged in customer:

* **Coming Soon** Return Orders
* **Coming Soon** Return Subscriptions
* **Coming Soon** Return Downloads (Auditing)
* **Coming Soon** Return Saved Payment Methods (Auditing)
* **Coming Soon** Get and Update Profile (In Development)

[Buy CoCart Pro Now](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

[View list of the WooCommerce extensions](https://cocart.xyz/woocommerce-extensions/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) that support CoCart or are supported in [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### Priority support

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our premium customers. Communication is handled one-on-one via direct messaging in [Slack](https://app.slack.com/client/TD85PLSMA/) and is available to people who purchased [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

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

* WordPress v5.6
* WooCommerce v4.3
* PHP v7.3

= Recommended Requirements =

* WordPress v5.8 or higher.
* WooCommerce v5.2 or higher.
* PHP v7.4

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CoCart, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "CoCart" and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Upgrading =

It is recommended that anytime you want to update CoCart that you get familiar with what's changed in the release.

CoCart uses Semver practices. The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes.
- *MINOR* version when you add functionality in a backwards compatible manner.
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org/)

== Frequently Asked Questions ==

= Is CoCart free? =

Yes! CoCart’s core features are absolutely free. [CoCart Pro completes the full cart experience!](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

= How do I start using CoCart? =

You will first need WooCommerce installed with the REST API enabled. Then install CoCart and follow the documentation.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Why should I use CoCart? =

The question is why not! WooCommerce's REST API is only created for controlling the backend of your store. It does not natively provide an API for controlling the frontend.

Being able to access your products and put them on display without authorization is a breeze and saves you the headache trying figure out how to also authorize a returning customer to view their cart.

CoCart is **Powerful** and **Developer** friendly ready to build your headless ecommerce the way you want, **without the need to build an API**.

If you are wanting to build headless ecommerce while still powered by WooCommerce, then CoCart is your solution. But don't take my word for it. [Checkout the testimonials left by others](https://cocart.xyz/wall-of-love/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

With [the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) provided, you’ll see how easy it is from showing your products to adding them to the cart in no time at all.

= Who should use CoCart? =

CoCart is perfect for ecommerce owners and developers. If you want to create an ecommerce app for mobile or a custom frontend shopping experience completely using the REST API, then CoCart is for you.

= Do I need to have coding skills to use CoCart? =

As this plugin provides a REST API built for developers, you will need to have some coding knowledge to use it. [Checkout the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) to get some understanding.

= Where can I find documentation for CoCart? =

You can find the documentation for CoCart on the [CoCart REST API Docs](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= Can I change the layout format/add/change details to the responses? =

You certainly can. Filters are available to do just that. [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided. [View the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) for more.

= Why does CoCart use a custom session handler in the first place? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler **does not support guest customers**.
- The default session handler does not store additional data that maybe required to help you.
- More consistent with modern web.

= Why does CoCart use a custom session table in the database? =

The default WooCommerce session table only stores the basics of a cart in session. CoCart provides additional data that maybe required to help you and other add-ons/extensions developed by CoCart or third-parties.

Such as when the cart was created. This information is only stored in the browser session.

Also the source of the cart it was last saved. For the web it will be `WooCommerce` and for your headless ecommerce `CoCart`. This lets you know which version of your store your customers are shopping from should you have both web and app versions.

= Can I have WordPress running on one domain and my headless ecommerce on another domain? =

Yes of course. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can [manually enable it via the filters in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

=  Is "WooCommerce Shipping and Tax" plugin supported? =

Not at this time. "WooCommerce Shipping and Tax" ignores any REST API from allowing the ability to calculate the taxes from TaxJar. Code has been contributed to the plugin that will allow third-party plugins enable this ability and awaiting feedback.

However, TaxJar for WooCommerce plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "TaxJar for WooCommerce" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Why CoCart and not WooCommerce Store API? =

WooCommerce Store API is limited and designed mainly to focus on their [Gutenberg blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) they have developed in React. CoCart is designed to focus on decoupling WooCommerce so you can use any framework to allow your store to be headless.

Also, after tweaking WooCommerce Store API to work for decoupled purposes (which CoCart does not require), your still using the default session handler which **does not support guest customers**. CoCart uses it's own session handler which does support guest customers.

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

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Slack](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by either purchasing [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

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

= v3.2.0 - 15th March, 2022 =

### What's New?

* Enhancement: Moved products array to it's own object and returned pagination information in the response. - **Products API v2 ONLY!**
* Tested: ✔️ Compatible with WooCommerce v6.3

> Dev note: A small break but a good one thanks to the feedback from **[Alberto Abruzzo](https://profiles.wordpress.org/albertoabruzzo/)**. This only affects when accessing all products with or without arguments set. Just need to access the array of products from an object not just from the response. What's also great about this enhancement is that any arguments set will also be appended to the pagination links making it easy for developers.

### Bug Fix

* Fixed: Plugin review notice reappearing even after it has been dismissed.

### Deprecated

* Support for WooCommerce less than version 4.8 or legacy versions of WooCommerce Admin before it was packaged with the core of WooCommerce.

### Enhancements

* Better detection of WooCommerce Admin. Now checks if the feature is enabled.

### For Developers

* Introduced new filter `cocart_prevent_wc_admin_note_created` to prevent WooCommerce Admin notes from being created.

= v3.1.2 - 10th March, 2022 =

### Bug Fixes

* Fixed an Undefined index: Items for shipping packages in the cart response. Caused the JSON response to not return valid even if the response status was `200`.
* Fixed a fatal error. Uncaught Error: Class `CoCart_Session_Handler` when cron job runs in the background.
* Fixed Yoda conditions.
* Removed calculating totals once cart has been loaded from session as it caused the cart not to show.

### Tweaks

* Cleaning up expired carts function has changed to a global task function. This also fixes the cron job error mentioned above.
* Added more translation notes to clarify meaning of placeholders.

= v3.1.1 - 2nd March, 2022 =

**🔥 This is a HOTFIX!**

### Bug Fix

* When updating an individual item in cart, the product data is not passed when validating the quantity and was causing a fatal error. [[issue #319](https://github.com/co-cart/co-cart/issues/319)]

> Developer note: This is because an improvement was made when adding items to the cart using the same function that is used to validate the quantity and I forgot to update the parameters for when it's used to update an item. My bad.

= v3.1.0 - 28th February, 2022 =

### What's New?

* Setup wizard introduced to help identify if the store is new and prepare the environment for headless setup.
* Introduced a new Cart API route that allows developers to add custom callbacks to update the cart for any possibility. - [See example](https://github.com/co-cart/cocart-cart-callback-example).
* CoCart Products add-on now merged with the core and introduces API v2 with a new option to view single products by SKU and many improved tweaks to the response.
* No cache control added to help prevent CoCart from being cached at all so results return quicker.
* Added the ability to set the customers billing email address while adding item/s to cart. Great for capturing email addresses for cart abandonment.
* Added the ability to return only requested fields for the cart response before fetching data. Similar to GraphQL. Powerful speed performance if you don't want everything.
* Added the ability to set the price of the item you add to the cart with new cart cache system. - Simple Products and Variations ONLY!
* Added the ability to update the quantity of items in the cart in bulk using the new update callback API.
* Prevented certain routes from initializing the session and cart as they are not needed. Small performance boost.
* Timestamp of each REST API request is returned in the response headers. `X-CoCart-API-Timestamp`
* Plugin version of CoCart is returned in the response headers. `X-CoCart-API-Version`
* Added to the login response the users avatar URLS and email address.
* Added Schema to the following cart routes: item and items.
* Added Schema to the following other routes: login, sessions, session and store.

> ⚠️ If you have been using CoCart Products add-on, make sure you have the latest version of it installed before updating CoCart to prevent crashing your site. Otherwise best to deactivate the add-on first. Subscription support will remain in CoCart Products add-on until next CoCart Pro update. ⚠️

### Plugin Suggestions

* Added [Flexible Shipping](https://wordpress.org/plugins/flexible-shipping/)
* Added [TaxJar for WooCommerce](http://www.taxjar.com/woocommerce-sales-tax-plugin/)
* Added [Follow Up Emails](https://woocommerce.com/products/follow-up-emails/) - **Still requires testing with**
* Removed CoCart Products Add-on now the products API is merged with core of CoCart.
* Optimized the results for better performance and cached once a day.

### Bug Fixes

* Coupons duplicating on each REST API request.
* `$item_key` was not passed in `validate_item_quantity()` function to validate the quantity allowed for the item.
* Redirect to the "Getting Started" page should no longer happen on every activation.
* Plugin review notice dismiss action.
* Requesting `OPTIONS` for any endpoint to return arguments and schema.
* Log time for error logs recorded.
* Fixed any undefined index for loading a cart for guest customers.
* Fixed an attempt trying to access array offset on value of type float.
* Clearing the cart now **100%** clears.
* The use of WooCommerce API consumer key and consumer secret for authentication is now working again. Changed the priority of authentication to allow WooCommerce to check authentication first.
* Detection of [WooCommerce Advanced Shipping Packages](https://woocommerce.com/products/woocommerce-advanced-shipping-packages/) extension.

### Deprecated & Replacements

* Function `get_store_currency()` is replaced with a global function `cocart_get_store_currency()`.
* Function `prepare_money_response()` is replaced with a global function `cocart_prepare_money_response()`.
* Function `wc_deprecated_hook()` is replaced with our version of that function `cocart_deprecated_hook()`.
* Function `is_ajax()` ìs replaced with `wp_doing_ajax()`.
* Timezone `get_option( 'timezone_string' )` is replaced with `wp_timezone_string()` function to return proper timezone string on the store route.
* Replaced `wc_rest_prepare_date_response()` function with `cocart_prepare_date_response()` function.

### Enhancements

* Deprecated the upgrade warning notice. Dev note: Just keep an eye for major updates on [CoCart.dev](https://cocart.dev)
* Shipping rates now return meta data if any. Thanks to [@gabrielandujar](https://github.com/gabrielandujar) for contributing.
* Stock check improved when adding item by checking the remaining stock instead.
* Load Cart from Session to allow registered customers to merge a guest cart. - Thanks to [@ashtarcommunications](https://github.com/ashtarcommunications) for contributing.
* Should CoCart session table creation fail during install, ask user if they have privileges to do so.
* Removed items (if any) now returns in the cart response even if the cart is empty.
* Exposed WordPress headers for product route support.
* To help support the ability to set a custom price for an item once added, the totals are recalculated before the cart response returns so it is up to date on the first callback.
* Allow count items endpoint to return `0` if no items are in the cart.
* Re-worked session endpoint to get data from the session and not the cart object.

### Tweaks

> 📢 Warning: Some tweaks have been made in this release that will introduce breaking changes to the API response so please review the changelog and test on a staging environment before updating on production.

* CoCart cron job for cleanup sessions improved.
* Removed WooCommerce cron job for cleanup sessions as it is not needed.
* Session abstract now extends `WC_Session` abstract for plugin compatibility for those that strong types.
* Added `get_session()` function for plugin compatibility to session handler.
* When you uninstall CoCart, the original WooCommerce cron job for cleanup sessions will be rescheduled.
* Notice for when item is removed now returns in the first response.
* Added notice for when item is restored.
* Cross sell prices now returns with formatted decimals.
* Cart tax total now returns with formatted decimals.
* Removed last raw WooCommerce cart data `tax_data` object from under cart items as the `totals` object provides a better data for each item.
* Item price in the cart now returns unformatted to be consistent with other monetary values such as taxes and totals.
* Shipping cost now returns unformatted with formatted decimals to be consistent with other monetary values such as taxes and totals.
* Shipping tax now returns as a `string` not `object` with just the tax cost unformatted with formatted decimals to be consistent with other monetary values such as taxes and totals.
* Moved validating product up so it can be validated first and allows us to pass the product object when validate the quantity.

### Compatibility and Requirements

* Added more compatibility for next update of CoCart Pro.
* Minimum requirement for WordPress is now v5.6
* Tested: ✔️ Compatible with WooCommerce v6.2
* Tested: ✔️ Compatible with WordPress v5.9

### For Developers

* Introduced new filter `cocart_secure_registered_users` to disable security check for using a registered users ID as the cart key.
* Introduced new filter `cocart_override_cart_item` to override cart item for anything extra.
* Introduced new filter `cocart_products_variable_empty_price` to provide a custom price range for variable products should none exist yet.
* Introduced new filter `cocart_products_get_price_range` to alter the price range for variable products.
* Introduced new filter `cocart_products_add_to_cart_rest_url` for quick easy direct access to POST item to cart for other product types.
* Introduced new filter `cocart_add_item_query_parameters` to allow developers to extend the query parameters for adding an item.
* Introduced new filter `cocart_add_items_query_parameters` to allow developers to extend the query parameters for adding items.
* Introduced new filter `cocart_cart_query_parameters` to allow developers to extend the query parameters for getting the cart.
* Introduced new filter `cocart_cart_item_restored_title` to allow developers to change the title of the product restored for the notice.
* Introduced new filter `cocart_cart_item_restored_message` to allow developers to change the message of the restored item notice.
* Introduced new filter `cocart_update_cart_validation` to allow developers to change the validation for updating a specific item in the cart.
* Introduced new action `cocart_cart_updated` to allow developers to hook in once the cart has updated.
* Introduced new filter `cocart_cart_item_subtotal_tax` to allow developers to change the item subtotal tax.
* Introduced new filter `cocart_cart_item_total` to allow developers to change the item total.
* Introduced new filter `cocart_cart_item_tax` to allow developers to change the item tax.
* Introduced new filter `cocart_prepare_money_disable_decimals` that allows you to disable the decimals used when returning the monetary value.
* Introduced new filter `cocart_quantity_maximum_allowed` that allows control over the maximum quantity a customer is able to add said item to the cart.
* Introduced new filter `cocart_product_not_enough_stock_message` that allows you to change the message about product not having enough stock.
* Added `$product` object as a parameter for `cocart_quantity_minimum_requirement` filter so you have more control on which products we want to alter the minimum requirement if not all.

> The following filters are affected on Products API v2 ONLY should you have used the filters for API v1!

* Renamed filter `cocart_category_thumbnail` to `cocart_products_category_thumbnail`.
* Renamed filter `cocart_category_thumbnail_size` to `cocart_products_category_thumbnail_size`.
* Renamed filter `cocart_category_thumbnail_src` to `cocart_products_category_thumbnail_src`.

[View the full changelog here](https://github.com/co-cart/co-cart/blob/master/CHANGELOG.md).

== Upgrade Notice ==

= 3.2.0 =

Warning: A small tweak has been made in this release that will break the main products response so please review the changelog and test on a staging environment before updating on production.