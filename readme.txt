=== CoCart - Decoupling Made Easy for WooCommerce ===
Contributors: cocartforwc, sebd86, ajayghaghretiya, skunkbad, sefid-par, mattdabell, joshuaiz, dmchale, JPPdesigns, inspiredagency, darkchris, mohib007, rozaliastoilova, ashtarcommunications, albertoabruzzo, jnz31, douglasjohnson, antondrob2
Tags: woocommerce, rest-api, decoupled, headless, cart
Requires at least: 5.6
Requires PHP: 7.4
Tested up to: 6.5
Stable tag: 4.0.0
WC requires at least: 4.3
WC tested up to: 8.8
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Make your WooCommerce store headless with CoCart, a REST API designed for decoupling.

== Description ==

### Speed up your WooCommerce store by going headless

**Is your store slow? Looking to decouple away from WordPress?** [CoCart](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) saves you countless hours of developing your own REST API to decouple WooCommerce. It provides a developer-friendly, out the box solution that is customizable to your requirements allowing you to build a headless store without limitations imposed by a WordPress theme—it's that simple.

#### What is WooCommerce?

WooCommerce is a **flexible, open-source commerce solution** built on WordPress, empowering anyone to **sell anything, anywhere** and is **the fastest-growing eCommerce platform** on the internet.

#### Why CoCart?

With CoCart, we have already done the hard part for you. The API. Once installed, your WooCommerce store is ready to decouple away from WordPress, allowing you to design without limitations imposed by a WordPress theme that is harder to modify and optimize. **Utilize faster, familiar frameworks you know and love**—it's that simple.

## ✨ What do you get with the core of CoCart?

**Everything you need to try** and see if making your store headless is right for you.

* **No Blocks** - The API is designed for the purpose of decoupling. Not blocks for Gutenberg.
* **Enhanced Session Handler** - Our session handler provides the support needed for any decoupled situation.
* **Basic Authentication** - No Admin API Keys required. Customers have full control, either as a guest or authenticated with their login details.
* **Domain dominance** - CORS can be an issue when decoupling. Don’t sweat the small stuff. We got you.
* **No Headless Checkout?** - Load any cart session via the native site, if you feel more comfortable using WooCommerce’s built in payment system.
* **Worried about Caching?** - The Cart API does not cache no matter what cache system you have installed for other API’s in use. Responses return fresh every time.
* **Reduced Cart Checkups** - Avoid the hassle of multiple requests to verify item and coupon validity in your cart. Our system efficiently checks stock, validates coupons, and calculates totals and fees, ensuring real-time accuracy before confirmation.
* **Need your own cart callback?** - Register custom callbacks without needing to create a whole new endpoint. Cart response returns once the callback is completed.
* **Your Inventory** - Search by Name, ID or SKU, filter and return product data you need without authentication. REST shortcuts are readily provided for your next requests.
* **Want to track your customers?** - Keep watch of all cart sessions, even the ones that are starting to expire.
* **Name Your Price Built In** - Give your customers control of the price they pay. Encourage your audience to support you with payment flexibility that widens your paying audience.
* **Bulk Requests** - Combine many cart requests in bulk to save time.

And this is just the tip of the iceberg.

★★★★★
> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time. [Harald Schneider](https://wordpress.org/support/topic/excellent-plugin-8062/)

## 🛒 Built for developers, by developers

CoCart was born out of frustration with **no existing solution on the market**. As beautiful as it functions, the API is just as flexible with you, the developer, in mind. We spend an unfathomable amount of time making the API a joy to work with.

We invest our time into fully abstracting our API so you can focus on building a headless store. Integrate with CoCart in days, not months.

★★★★★
> Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

### 📦 Serious about going headless?

Try out more features and unlock your stores potential. Upgrade to complete the API with additional features that help make your store more awesome.

[See what we have in store](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

★★★★★
> This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

### 😍 Priority support

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our paying customers. Support can also be requested with the [community on Discord](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

## 🧰 Developer Tools

* **[CoCart Beta Tester](https://github.com/cocart-headless/cocart-beta-tester)** allows you to easily update to pre-release versions of CoCart for testing and development purposes.
* **[CoCart VSCode](https://github.com/cocart-headless/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Product Support Boilerplate](https://github.com/cocart-headless/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product types to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/cocart-headless/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

## 📢 Testimonials - Developers just love it

★★★★★
> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription. [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

★★★★★
> This plugin works great out of the box for adding products to the cart via API. The code is solid and functionality is as expected, thanks Sebastien! [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

#### More testimonials

[See the wall of love](https://cocartapi.com/wall-of-love/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

### ☀️ Upgrading

It is recommended that anytime you want to update CoCart that you get familiar with what's changed in the release.

CoCart publishes [release notes via the changelog](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#developers).

CoCart uses Semver practices. The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes.
- *MINOR* version when you add functionality in a backwards compatible manner.
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org/)

#### 👍 Add-ons to further enhance CoCart

We also have add-ons that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** enables support for CORS to allow CoCart to work across multiple domains.
* **[CoCart - JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/)** allows you to authenticate via a simple JWT Token.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it.
* and more add-ons in development.

They work with the core of CoCart already, and these add-ons of course come with support too.

### ⌨️ Join our growing community

A Discord community for developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

[Join our community](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

### Built with developers in mind

Extensible, adaptable, and open source — CoCart is created with developers in mind. If you’re interested to jump in the project, there are opportunities for developers at all levels to get involved. [Contribute to CoCart on the GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and join the party. 🎉

### 🐞 Bug reports

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

### More information

* The official [CoCart API plugin](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) website.
* [CoCart for Developers](https://cocart.dev/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink), an official hub for resources you need to be productive with CoCart and keep track of everything that is happening with the API.
* [CoCart API Reference](https://docs.cocart.xyz/)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/cocartheadless/) and [GitHub](https://github.com/co-cart/co-cart)

#### 💯 Credits

This plugin is developed and maintained by [Sébastien Dumont](https://twitter.com/sebd86).
Founder of [CoCart Headless, LLC](https://twitter.com/cocartheadless).

== Installation ==

= Minimum Requirements =

* WordPress v5.6
* WooCommerce v4.3
* PHP v7.4

= Recommended Requirements =

* WordPress v6.0 or higher.
* WooCommerce v7.0 or higher.
* PHP v8.0

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CoCart, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "CoCart" and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Upgrading =

It is recommended that anytime you want to update CoCart that you get familiar with what's changed in the release.

CoCart publishes [release notes via the changelog](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#developers).

CoCart uses Semver practices. The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes.
- *MINOR* version when you add functionality in a backwards compatible manner.
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org/)

== Frequently Asked Questions ==

= Is CoCart free? =

Yes! CoCart’s core features are absolutely free. [CoCart Plus completes the full cart experience!](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

= How do I start using CoCart? =

You will first need WooCommerce installed with the REST API enabled. Then install CoCart and follow the documentation.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Who should use CoCart? =

CoCart is perfect for ecommerce owners and developers who want to create an ecommerce app for mobile or a custom frontend shopping experience completely using the REST API.

= Do I need to have coding skills to use CoCart? =

As this plugin provides a REST API built for developers, you will need to have some coding knowledge to use it.

= Where can I find documentation for CoCart? =

You can find the documentation [here](https://docs.cocart.xyz/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Can I change the formatting of values, add and change details to the responses? =

You certainly can. There are over 100+ filters available to customize to your needs.

= Why does CoCart use a custom session handler in the first place? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler **does not support guest customers**.
- The default session handler **does not store additional data that maybe required to help you**.
- More consistent with modern web.

= Why does CoCart use a custom session table in the database? =

The default WooCommerce session table only stores the basics of a cart in session. CoCart provides additional data that maybe required to help you and other add-ons/extensions developed by CoCart or third-parties.

Such as when the cart was created. This information is only stored in the browser session.

Also the source of the cart it was last saved. For the web it will be `WooCommerce` and for your headless ecommerce `CoCart`. This lets you know which version of your store your customers are shopping from should you have both web and app versions.

= Can I have WordPress running on one domain and my headless ecommerce on another domain? =

Yes of course. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can manually enable it via the filters available [in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

= Can I add "WooCommerce Subscriptions" product to the cart? =

Absolutely you can. Any WooCommerce Subscriptions product can be added to the cart the same way a simple or variable product is added to the cart.

= Why can I not add the same item to the cart with a different price? =

Each item added to the cart is assigned a cart item key which is made of four key values: **Product ID**, **Variation ID**, **Variation attributes** and **Cart item data**.

The price and quantity is not taken into account to make this key.

So if you add the same item to the cart but with a different price, the cart will look up the cart item key to see if it's already added to the cart before deciding to either:
    1. Add it as a new item.
    2. Update the quantity of the previous item, in your case the price.

= Is "WooCommerce Shipping and Tax" plugin supported? =

Not at this time. "WooCommerce Shipping and Tax" ignores any REST API from allowing the ability to calculate the taxes from TaxJar except for WooCommerce Blocks and JetPack. However, [TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "[TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/)" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Is CoCart right for my business? =

CoCart’s REST API makes it possible for businesses to build a complete custom storefront. It’s API-first, enabling your business to take the shopping experience to the next level.

Made by and for developers, CoCart immediately allows you to create sophisticated experiences fast with unlimited possibilities.

With our extensive documentation and resources available, CoCart is a plug and play solution that works out of the box.

Save yourself 80% of a headache and hours of development time.

= Can I use any modern stack? =

Yes you can use your preferred tools and favorite modern technologies like [NextJS](https://nextjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/), [Ember](https://emberjs.com/) and more giving you endless flexibility and customization.

= Why CoCart and not WooCommerce Store API? =

Both API’s are unique for their individual purposes.

WooCommerce's Store API is designed for their [Gutenberg blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) which only requires a fixed format and is still prone to be used on native storefronts.

It also only works with *Nonces* when you are on the site so for mobile apps or headless ecommerce, you will run into issues. It is also missing a lot of valuable information that developers require to help them.

CoCart's API is designed for decoupling away from WordPress and lets you build headless ecommerce using your favorite technologies. **No Nonces, no cookies required.**

CoCart is packed full of powerful features that are completely customizable making it possible for businesses to build a complete custom storefront how they want.

No matter the type of store you are running, CoCart helps you grow.

It’s made by and for developers and immediately allows you to create sophisticated experiences fast with unlimited possibilities with it’s plug and play solution that just works out of the box.

So even if you are new to building a headless ecommerce or already have a WooCommerce store and been wanting to go headless, now is the time to start.

Don't take my word for it. Checkout the testimonials left by startups, freelancers, agencies and many more.

= Can I install/update CoCart via Composer? =

Yes. The best method would be to install/update CoCart from the GitHub repository but you can also do so via [https://wpackagist.org/](https://wpackagist.org/search?q=cart-rest-api-for-woocommerce&type=plugin)

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it via the network and all sites will have CoCart enabled.

= Can I enable white labelling for CoCart? =

Yes you can. You will have to edit your `wp-config.php` file to add a new constant. [See guide for details](https://cocart.dev/articles/wp-config-php/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink#white-labelling).

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the support forum – be sure to search the forums to confirm that the error has not already been reported.

= CoCart is awesome! Can I contribute? =

Yes, you can! Join in on our [GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and follow the [development blog](https://cocart.dev/news/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) to stay up-to-date with everything happening in the project.

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I get help or talk other users about CoCart core? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Discord](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by purchasing [CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) or a higher tier.

= Where can I find out more about the additional features? =

Find out all relevant [features and pricing information over on the official site](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= My question is not listed here. Where can I find more answers? =

Check out [Frequently Asked Questions](https://cocartapi.com/faq/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for more.

== Screenshots ==

1. Get Products (API v2)
2. Get Individual Product (API v2)
3. Add Item to Cart (API v2)

== Contributors & Developers ==

"CoCart" has **not** yet been translated in other languages. You can [translate "CoCart" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

**Please review your experience**

If you like CoCart and it has helped with your development, please take a moment to [provide a review](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/#new-post). It helps to keep the plugin going strong, and is greatly appreciated.

== Changelog ==

= v4.0.0 - 13th May, 2024 =

### What's New?

In this release, you’ll find various improvements made through out the plugin. [Find out more about what’s new in CoCart 4.0 in our release post!](https://cocart.dev/cocart-4-0-released-now-with-cart-batch-support-and-more/)

>> With this release we are happy to provide some of the improvements from the originally planned v4 release. These improvements are backwards compatible. Hope you enjoy them.

* REST API: Added batch support for cart endpoints listed below. (API v2 supported ONLY) [See article for batch usage](https://make.wordpress.org/core/2020/11/20/rest-api-batch-framework-in-wordpress-5-6/).
 * * Add item/s to cart.
 * * Clear cart.
 * * Remove item.
 * * Restore item.
 * * Update item.
 * * Update cart.

### Bug Fixes

* Plugin: Fixed various text localization issues.
* REST API: `Access-Control-Allow-Credentials` being outputted as 1 instead of true. [Solves issue 410](https://github.com/co-cart/co-cart/issues/410). Thanks to [@SebastianLamprecht](https://github.com/SebastianLamprecht) for reporting it.
* REST API: Update cart requests no longer fails and continues to the next item if an item in cart no longer exists.
* REST API: Products API schema has been completed for v1.
* REST API: Products API schema has been corrected for v2.
* WordPress Dashboard: Plugin suggestions now lists CoCart JWT Authentication add-on.

### Improvements

* REST API: Now checks if the request is a preflight request.
* REST API: Error responses are now softer to prevent fatal networking when a request fails.
* REST API: Callback for cart update now passes the cart controller class so we don't have to call it a new.
* REST API: Cart schema tweaks.
* REST API: Cart and Product schema are now cached for performance.
* Plugin: Added more inline documentation for action hooks and filters.
* Plugin: Improved database queries.
* Plugin: Updated to latest WordPress Code Standards.
* WordPress Dashboard: Added CoCart add-on auto updates watcher.
* WP-CLI: Updating CoCart via command will now remove update database notice.

### Developers

* REST API: Two new headers return for cart responses only. `CoCart-API-Cart-Expiring` and `CoCart-API-Cart-Expiration`.

> These two new headers can help developers use the timestamps of the cart in session for when it is going to expire and how long until it does expire completely.

* REST API: Error tracking is returned with the error responses when `WP_DEBUG` is set to true to help with any debugging.
* REST API: Class aliases have been added to API v2 controllers after changing the class names for consistency.

### Compatibility

* Tested with WooCommerce v8.8

= v3.12.0 - 18th March, 2024 =

### Security Patch

📢 This release solves a validation issue for both versions of the Products API when accessing an individual product. It is important that you update to this release asap to keep your store secure.

### Bug Fixes

* Corrected: Products API v1 Schema for weight object.
* Added: Missing Products API v1 Schema for Image sizes.
* Fixed: Schema product type options to match with parameters.
* Fixed: Products API returning custom attributes with special characters incorrectly. [Solves issue 401](https://github.com/co-cart/co-cart/issues/401)
* Fixed: Some requested data was not sanitized.

### Compatibility

* Tested with WordPress v6.5

= v3.11.2 - 1st March, 2024 =

### Bug Fix

* Fixed: PHP warning for `array_values()` when filtering the fields to return for the Cart API.

### Improvement

* Corrected a spelling error with plugin review notice.

= v3.11.1 - 27th February, 2024 =

### Bug Fix

* Fixed: Passing arguments for `cocart_do_deprecated_filter` incorrectly.

= v3.11.0 - 23rd February, 2024 =

### What's New?

* Products API: Gets all registered product taxonomies.
* Products API: Added support to query `product_variations` by attribute slugs.
* Products API: Product meta data is now filterable. (API v2 ONLY) See below for notes.

### For Developers

* Products API: Filter introduced `cocart_products_ignore_private_meta_keys` allows you to prevent meta data for the product to return. (API v2 ONLY)

> This can be useful should a 3rd party plugin expose private data that should not be made available to the public.
> For example a plugin that is designed to use web push notifications and exposes an email address.

* Products API: Filter introduced `cocart_products_get_safe_meta_data` to control what product meta is returned. (API v2 ONLY)

### Improvements

* Cart API: Small performance improvement returning the items.
* Products API: Set date query column to `post_date` if `after` and `before` date query is set.
* Products API: Added missing date `after` and date `before` arguments.
* Products API: Added sanitize callbacks missing from a few arguments.
* Products API: Set default `orderby` to date should WooCommerce not have a default catalogue value set.

= v3.10.9 - 22nd February, 2024 =

### Improvements

* Uses less memory.
* Sends headers with `send_headers()` instead of `header()`

### Compatibility

* Removed "DONOTCACHEPAGE" constant.

= v3.10.8 - 21st February, 2024 =

### Improvements

* WordPress Dashboard: Plugin suggestions now returns results much better the first time they are viewed.
* Store API: Only returns the version of the plugin, routes and link to documentation if "WP_DEBUG" is true.
* REST API: Deprecated action hooks and filters return messages if actually triggered.

### Compatibility

* Tested up to PHP v8.3.0

= v3.10.7 - 20th February, 2024 =

### For Developers

* Introduced new filter `cocart_products_get_related_products_exclude_ids` to exclude products from related products.

### Improvements

* Added missing arguments for Products API when viewing the OPTIONS.

### Bug Fix

* Fixed: Calling `retry()` non-static for plugin suggestions when searching.

### Compatibility

* Tested with WooCommerce v8.6

= v3.10.6 - 15th February, 2024 =

### Bug Fix

* Fixed: `$old_cart_key` is undefined in session handler.

= v3.10.5 - 8th February, 2024 =

### Bug Fix

* Fixed: Blank submenu pages from registering when activated on a multisite.

= v3.10.4 - 31st January, 2024 =

### What's New?

* Added support for CoCart Plus.
* Updated WooCommerce Notes.

### Bug Fixes

* Fixed: PHP Deprecation for implicit conversion from float to int.
* Fixed: PHP Deprecation for creation of dynamic property.

### Compatibility

* Tested up to PHP v8.2.10

= v3.10.3 - 19th January, 2024 =

### Bug Fix

* Fixed: Fatal error with `version_compare()` when activating the plugin with the [TaxJar plugin](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) active with PHP 8.1 or plus.

= v3.10.2 - 15th January, 2024 =

### What's new?

* Removed the need to disable cookie authentication check since it's ignored anyway.

### Bug Fixes

* Fixed a typo in the Setup Wizard.
* Products API - Default Catalogue Visibility parameter was missing.

### Compatibility

* Tested with WooCommerce v8.5

= v3.10.1 - 20th December, 2023 =

Forgot to update WordPress tested up to tag and a little CSS tweak.

= v3.10.0 - 19th December, 2023 =

### What's New?

* Added WordPress Playground notice. [commit 912ebb2](https://github.com/co-cart/co-cart/commit/912ebb24cf096de12aaa6c2aeeab9c59bf4dff5a)
* Added new admin support page. [commit 2f64980](https://github.com/co-cart/co-cart/commit/2f649804f1be685eba07a6afbeeaa08f7a28acc4)
* Added new help tab available on any CoCart admin page. [commit 9970ce8](https://github.com/co-cart/co-cart/commit/9970ce86afb8bd6a9ba4019eb7cadb9a96c00992)
* Filtered the WordPress REST API Index to hide CoCart namespaces and routes unless you have debug enabled. This helps a little with anyone trying to lookup what REST API's you have outside your store setup. [commit 45723d9](https://github.com/co-cart/co-cart/commit/45723d97422b498dbf8c252ea95c0b5029f7e437)
* Updated license.txt [commit f6b0acb](https://github.com/co-cart/co-cart/commit/f6b0acb07190bd45d6e8b8371a78a2f1102e4dba)

### Bug Fixes

* Fixed undefined `cart_cached` if price change feature not used. [commit fb472fc](https://github.com/co-cart/co-cart/commit/fb472fc6bb5b1d87eaf46d724207458e0e00e045)
* Fixed Authentication failing to identify current user if authentication is not provided. [commit f6fb7a4](https://github.com/co-cart/co-cart/commit/f6fb7a4eb809a80144e75c33b4cc35435663661d)
* Fixed PHP Deprecated: str_replace(): Passing null to parameter 2 (PHP 8.1) [commit 4ebeafd](https://github.com/co-cart/co-cart/commit/4ebeafdb4910248eef16b11bf50c44d019549c89)

### Improvements

* Setup Wizard no longer blocks access to the WordPress dashboard. [commit 00a158e](https://github.com/co-cart/co-cart/commit/00a158ee038550f2d24bdef6184fdc97f203ecc1)
* Moved validation earlier to check if we are on a CoCart page before displaying admin notices.
* Updated from product name to business name in Setup Wizard page. [commit eede727](https://github.com/co-cart/co-cart/commit/eede727188784d373346d992d164d07591b6bd67)
* Removed link to deprecated project. [commit ab973ca](https://github.com/co-cart/co-cart/commit/ab973ca5591bc7bb3d699ed0d62f48683ffd47d1)
* Improved the explanation of "Multiple Domain" option in Setup Wizard. [commit e54e31b](https://github.com/co-cart/co-cart/commit/e54e31b5aef0475afcd365fadc934ec2d9fc7100)
* Rewrote the admin menu system for a much better page management. [commit 543642e](https://github.com/co-cart/co-cart/commit/543642eba06c7f27a2766025c5395a97556555fb)
* Simplified the admin notices when the database requires updating and has updated with a dismissible action. [commit 8d995e0](https://github.com/co-cart/co-cart/commit/8d995e05ce86fc0df5c0fe54521febed094e778d)
* When database has updated, the notice is unset. This prevents the admin notice from showing again even without dismissing the admin notice first on next page load. [commit c90e320](https://github.com/co-cart/co-cart/commit/c90e320581616cedfa43833b27fbbb054fd3c918)
* There will be no sessions retrieved while WordPress setup is due. [commit fce7910](https://github.com/co-cart/co-cart/commit/fce7910d8849633dc6e55acaa005f3e43d0d4646)

### Deprecations

* Removed "Getting Started" page. [commit ea397e4](https://github.com/co-cart/co-cart/commit/ea397e4bf5a7ec8f1b59a73e5723185e9993b666)
* Removed "Upgrade" page. [commit 5f9f48c](https://github.com/co-cart/co-cart/commit/5f9f48c9d48fcef135013700f138aae53d98f96e)

### Requirements and Compatibility

* Bumped PHP requirement to v7.4
* Tested with WordPress v6.4
* Tested with WooCommerce v8.4

= v3.9.0 - 2nd August, 2023 =

### What's New?

* Removed WooCommerce plugin headers to prevent incompatibility warning message when using "HPOS" feature.
* Updated "What's Coming Next?" link on plugins page to inform users about v4.0

### Bug Fix

* Fixed Products API where a product has no featured image or gallery images and is unable to determine the placeholder image. [Solves issue 12](https://github.com/co-cart/cocart-products-api/issues/12)

### Compatibility

* Tested with WooCommerce v7.9

[View the full changelog here](https://github.com/co-cart/co-cart/blob/trunk/CHANGELOG.md).

== Upgrade Notice ==

= 4.0.0 =

Added batch support for cart endpoints. See changelog for more.