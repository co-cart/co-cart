=== CoCart - Decoupling Made Easy for WooCommerce ===
Contributors: cocartforwc, sebd86
Tags: woocommerce, rest-api, decoupled, headless, cart
Requires at least: 5.6
Requires PHP: 7.4
Tested up to: 6.6
Stable tag: 4.3.6
WC requires at least: 7.0
WC tested up to: 9.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Make your WooCommerce store headless with CoCart, a REST API designed for decoupling.

== Description ==

CoCart's mission is **decoupling your WooCommerce store** with our developer friendly REST API extension.

[CoCart](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) is a **customizable, open-source REST API solution** built for WooCommerce that is ready to decouple away from WordPress, allowing you to design and build without limitations imposed by a WordPress theme that is harder to modify and optimize. Our REST API handles all the server side actions so it can be utilized in **faster and familiar frameworks** that you prefer.

CoCart Free contains everything you need to get started.

#### Why WooCommerce?

WooCommerce provides a **flexible, and efficient** way to build cutting-edge stores with WordPress at it's core. If you are looking to provide a great store for your customers that is fast, decoupling your store is a no brainer, but accessing the front side via the REST API was missing which is where CoCart comes in.

#### Why CoCart?

Is your store slow? Looking to decouple away from WordPress? Overwhelmed with getting WooCommerce working the way you want in your own REST API endpoints?

These are the struggles web developers have trying to decouple a WooCommerce store and with CoCart, you can overcome these challenges and get straight to utilizing a REST API that is ready for you.

* **Source of Truth** - CoCart sources the WooCommerce's Data Stores API and repeats most WooCommerce hooks to provide a wider array of support for most WooCommerce extensions out of the box.
* **No Cookie Required** - Our session handler generates a key for the user session in the WordPress database and stores session-related meta data for use on the client without the heavy load.
* **Basic Authentication** - No Admin API Keys required. Customers can login with their account either with Email+Password, Username+Password, Phone Number+Password.
* **Domain Dominance** - CORS can be an issue when decoupling so we made sure you have control of the origin.
* **Your Inventory** - Search products by Name, ID or SKU, filter and return product data you need without authentication or private data exposed. REST shortcuts are readily provided for your next requests.
* **Reduced Cart Checkups** - We have avoided the hassle of needing multiple requests to verify item and coupon validity in your cart. Our system efficiently checks stock, calculates totals and fees, ensuring real-time accuracy so your responses return up to date every time.
* **No Headless Checkout?** - Load any cart session via the native site using the generated key, if you feel more comfortable using WooCommerce’s built in payment system.
* **Need your own cart callback?** - Register custom callbacks without needing to create a whole new endpoint. Cart response returns once the callback is completed.
* **Want to track your customers?** - Keep watch of all cart sessions, even the ones that are starting to expire or have expired.
* **Name Your Price Built In** - Give your customers control of the price they pay. Encourage your audience to support you with payment flexibility that widens your paying audience.
* **Bulk Requests** - Combine many cart requests in bulk to save time.

And this is just the tip of the iceberg.

★★★★★
> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time. [Harald Schneider](https://wordpress.org/support/topic/excellent-plugin-8062/)

## 🛒 Built for developers, by developers

CoCart was born out of frustration with **no existing solution on the market**. As beautiful as it functions, the API is just as flexible with you, the developer in mind making the API a joy to work with so you can focus on building a headless store in days, not months.

★★★★★
> Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

### 📦 Serious about going headless?

Unlock your stores full potential. Upgrade to complete the API with additional features that help you develop your store further.

[See what else we have in store](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

★★★★★
> This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

### 💜 Need Support?

We aim to provide regular support for the CoCart plugin via [our Discord community server](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink). Please understand that we do prioritize support for our [paying customers](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

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

On Discord we have a community of developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

Come and [join our community](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

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
* WooCommerce v7.0
* PHP v7.4

= Recommended Requirements =

* WordPress v6.0 or higher.
* WooCommerce v9.0 or higher.
* PHP v8.0 or higher.

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

= What does CoCart do? =

CoCart provides a REST API that is ready to decouple WooCommerce away from WordPress. It allows you to search products by Name, ID or SKU, filter and return product data you need without authentication, manage cart sessions for customers and avoids the hassle of needing multiple requests to verify item and coupon validity in your cart. It also efficiently checks stock, calculates totals and fees, ensuring real-time accuracy so your responses return up to date every time.

CoCart sources the WooCommerce’s Data Stores API and repeats most WooCommerce hooks to provide a wider array of support for most WooCommerce extensions out of the box.

= Who should use CoCart? =

Pretty much everyone, who wants a faster ecommerce store to improve their business. WooCommerce runs multiple requests for multiple steps. We avoid the hassle of needing multiple requests for these steps and process them all together.

CoCart is perfect for ecommerce owners and developers who want to create an ecommerce app for mobile or a custom frontend shopping experience completely using the REST API.

= Are there any limitations? =

CoCart is designed with developers in mind allowing for complete control to customize or add support for a plugin to work with CoCart. CoCart does it's best to work out of the box but if there is a compatibility issue with a plugin that you would like to work with CoCart. We would be happy to hear about it.

= What is the source of truth? =

CoCart sources the WooCommerce’s Data Stores API and repeats most WooCommerce hooks to provide a wider array of support for most WooCommerce extensions out of the box.

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it on the sites you want to use CoCart.

= Can I have WordPress running on one domain and my headless ecommerce on another domain? =

Absolutely. That is what CoCart is mainly developed for. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can manually enable it via the filters available [in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

= Will CoCart interfere with other plugins? =

The majority of plugins are not REST API specific so it shouldn't. However, while we allow the source of truth for compatibility, there maybe a WooCommerce extension that returns data via an action hook that the REST API cannot understand during a specific action and may fail the response.

If that does happen, simply report the situation with as much detail as possible on our [GitHub repository](https://github.com/co-cart/co-cart/issues) and we will try our best to find a solution.

= How do I set up CoCart? =

You will first need WooCommerce installed and set up to your configurations. Then install CoCart, activate and your ready to start using the REST API following the API Reference provided.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Why use CoCart and not WooCommerce’s Store API? =

WooCommerce’s Store API is designed for the Gutenberg blocks which only requires a fixed format and is still prone to be used on native storefronts. It also lacks validation on the server end which you will need and not every extensions yet works with it out of the box. A lot of valuable information and abilities that developers require to help them are also unavailable and if you try to use the Store API for headless you will have issues managing the cart sessions.

CoCart's API is designed for decoupling away from WordPress with ease. It's a plug and play solution that just works out of the box. Also, improvements are always made to CoCart to ensure you get the best decoupled experience.

= Do I need to have coding skills to use CoCart? =

As this plugin provides a REST API built for developers, you will need to have some coding knowledge to use it.

= Where can I find documentation for CoCart? =

You can find the documentation [here](https://docs.cocart.xyz/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Can I change the formatting of values, add and change details to the responses? =

You certainly can. There are over 100+ filters available to customize to your needs.

= Why does CoCart use a custom session handler and table in the database? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler **does not support guest customers**.
- The default session handler **does not store additional data that maybe required to help you**.
- The default session handler **does not allow support for POS capability**.
- More consistent with modern web.

= Is "WooCommerce Shipping and Tax" plugin supported? =

No. "WooCommerce Shipping and Tax" ignores any custom REST API's from allowing the ability to calculate the taxes from TaxJar except for WooCommerce Blocks and JetPack. We don't recommend it. However, [TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "[TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/)" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Can I use any modern stack? =

Yes you can use your preferred tools and favorite modern technologies like [Astro](https://astro.build/) ,[NextJS](https://nextjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/), [Ember](https://emberjs.com/) and more giving you endless flexibility and customization.

= Can I install/update CoCart via Composer? =

Yes. The best method would be to install/update CoCart from the GitHub repository but you can also do so via [https://wpackagist.org/](https://wpackagist.org/search?q=cart-rest-api-for-woocommerce&type=plugin)

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the [Discord community server](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) in the #bug-report channel – be sure to search the forum to confirm that the error has not already been reported.

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

You can help [translate "CoCart" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code on GitHub](https://github.com/co-cart/co-cart/tree/development/), or follow the [CoCart development blog](https://cocart.dev/news/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for the latest development updates. You can also follow [@cocartapi](https://twitter.com/cocartapi) on Twitter to stay up to date about everything happening with CoCart.

**Please share your experience**

We’d love to hear what you have to say. [Share your experience](https://testimonial.to/cocart) and help others discover CoCart. It helps to keep the plugin going strong, and is greatly appreciated.

== Changelog ==

= v4.3.7 - 22nd October, 2024 =

### Bug Fixes

* REST API: Fixed rounding issue due to decimal separator being different with the cart total for some odd reason with WooCommerce.
* REST API: Fixed accessing correct schema for viewing a single session.
* Plugin: Properly deprecated `is_rest_api_request()` function within the authentication class.

### Changes

* WordPress Dashboard: Updated Setup Wizard Ready Step verbiage.
* WordPress Dashboard: Bumped upgrade notice from showing after 4 weeks newly installed to 6 weeks.
* WordPress Dashboard: No longer call Gravatar.
* Plugin: Translation URL has changed to <https://translate.cocartapi.com>

### Improvements

* REST API: Moved some cart validation further up before returning cart contents.
* REST API: Reset the item key when adding item again as it may have been manipulated by adding cart item data via code or plugin.
* REST API: Only update cart item quantity if quantity is different when requested.
* REST API: Check cached price against price with tax mode not just the product price set.
* WordPress Dashboard: Adjusted notices to get cached for the current site should it be a multisite.
* WordPress Dashboard: No longer reset upgrade notice each time CoCart updates.
* Plugin: PHPStan used to help with correcting errors and inconsistencies.

= v4.3.6 - 23rd August, 2024 =

### Bug Fixes

* REST API: View or deleting a session with the Sessions API was not accessing the session handler. No longer needs a separate load.
* Session Handler: Fixed merging of cart from guest.

= v4.3.5 - 9th August, 2024 =

### Bug Fix

* REST API: Changed priority for sending headers from `0` to `1` to help with CORS.

= v4.3.4 - 3rd August, 2024 =

### Bug Fix

* REST API: Fixed an issue with CORS not returning header `access-control-allow-origin`.

= v4.3.3 - 24th July, 2024 =

### Corrections

* Autoload for classes in the backend corrected to new locations.
* Clean up task that is scheduled was looking for the session handler in the wrong place.
* Fixed `update_session_timestamp()` function from failing in session handler.

### Improvements

* REST API: Price of product is now consistent in the Cart API (v2 ONLY). If your store was setup with no decimals the price would not return fully. [Solves issue #429](https://github.com/co-cart/co-cart/issues/429)
* REST API: Value of weight was returning in the wrong format. By returning as a string you get the true value without needing to round it up yourself.
* WordPress Dashboard: Updated add-on update watcher.

= v4.3.2 - 19th July, 2024 =

### 🌋 Hot Fix

This release fixes 3 known issues that were reported. [issue #425](https://github.com/co-cart/co-cart/issues/425), [issue #426](https://github.com/co-cart/co-cart/issues/426), [issue #427](https://github.com/co-cart/co-cart/issues/427) that has been affected since version 4.2 of CoCart.

It was due to the optimizations made to allow CoCart to perform better. Unfortunately it had some unexpected side affects that were not picked up during testing. For that I am sorry. If you haven't rolled back to before 4.2 then this patch is highly recommended.

Previous patch releases will also be updated to prevent further sites from experiencing these issues for those who like to update to specific version of the plugin.

A hard lesson was learned here and hope you haven't lost trust in the plugin. I appreciate your feedback and support during this process.

Please don't forget to backup your site before updating.

### Corrections

* Plugin: Typo caused the WP-CLI commands to not register and crash when used with composer.
* REST API: The data exception class was oddly not loading when updating an item. Autoloader was not picking it up. Now it is required.
* REST API: Cart would empty but the subtotal would not reset.

### Improvements

* REST API: Ensure we have calculated totals before we get an item or update an item so we can identify them.

= v4.3.0 - 17th July, 2024 =

### What's New?

In this release we have added a plugin update prevention system as a safety measure. For the moment it will detect for compatibility with minor releases while we are making adjustments but it's designed mostly for detecting major changes. All CoCart add-ons that we release will now check for CoCart's requirements and will help you decide to update or not until your ready to do so.

* Plugin: Added plugin headers to be used for detecting CoCart add-ons or plugins that support CoCart.
* WordPress Dashboard: Auto-updates are disabled should a CoCart add-on active have not tested with the latest release available.
* WordPress Dashboard: Update now link for CoCart opens up a modal listing none tested plugins with a confirmation.

### Improvements

* REST API: Ensure we have calculated totals before we restore the requested item so we can identify them.

### For Developers

> These filters are for site admins more than anything.

* Introduced filter `cocart_in_plugin_update_message` allows you to change the upgrade notice.
* Introduced filter `cocart_get_plugins_with_header` allows you to get the plugins that have a valid value for a specific header.
* Introduced filter `cocart_get_plugins_for_cocart` allows you to get plugins which "maybe" are for CoCart.

### Compatibility

* Tested with WordPress v6.6
* Tested with WooCommerce v9.1

= v4.2.2 - 12th July, 2024 =

### Reverting

We are reverting a change for destroying a session. Previous change causes a conflict with identifying the correct column with our session table and causes the cart not to clear.

= v4.2.1 - 11th July, 2024 =

### Hot Fix

When loading a cart from session a deprecated function was still triggered. It's now been removed to prevent failing.

= v4.2.0 - 11th July, 2024 =

In this release we have optimized our session handler making this release more compatibility with third party plugins. We also no longer use cookies as a backup for headless. This should also help with the confusion of needing to pass along the session cookie or reading the cookie to extract the cart key and help with user switching much better. A cart key is provided in both the cart response and returned headers. Saving the cart key in your own cookie or local storage is fairly straight forward.

> We advise that you update on staging or local to check if you have used any of our experimental functions and filters that were added to the session handler to see if any have been deprecated. You can also see the list of deprecations below. If you have any questions about this update please contact us.

### What's New?

* REST API: Can now request a guest cart via a requested header `cocart-api-cart-key`.

### Improvements

* Session Handler: Improved identifying cart without the need of cookies.
* Session Handler: Added new function `is_user_customer()` to check the user role is a customer when authenticated before migrating cart from guest.
* Authentication: Validation for invalid details was taking too long and failed to return an error message.
* REST API: Updating the customer details in cart will now take additional billing and shipping fields as meta data. Validation is required by the developer using filter `cocart_update_customer_fields`.
* REST API: Sanitized and formatted customer email address and phone number.
* REST API: Formatted customer postcode if validated.
* REST API: Product image sizes are now fetched using utility products class function `get_product_image_sizes()`. Cuts down on the filter `cocart_products_image_sizes` being in multiple places.
* REST API: Currency in cart API v2 now returns `currency_symbol_pos` and the currency symbol will now return based on the set currency without lookup.
* REST API: Improved headers returned and added nocache headers on authenticated requests.
* REST API: Simplified returning the cart key to the headers.
* REST API: Loading of the REST API optimized.
* Plugin: Moved `is_rest_api_request()` function to the main class so it can be utilized outside of the plugin.
* Plugin: Localization improvements.
* Plugin: Updated plugin review notice.
* Plugin: Code files organized better.

### Deprecations

* Removed the need to support web host "Pantheon" by filtering the cookie name.
* Removed our session abstract `CoCart_Session` that extended `WC_Session`. Any remaining functions have moved to our refreshed session handler.
* Function `CoCart_Session_Handler::destroy_cookie()` no longer used.
* Function `CoCart_Session_Handler::cocart_setcookie()` no longer used.
* Function `CoCart_Session_Handler::get_cart()` no longer used.
* Filter `cocart_cookie` no longer used. Use `woocommerce_cookie` instead.
* Filter `cocart_cookie_httponly` no longer used.
* Filter `cocart_cookie_supported` no longer used.
* Filter `cocart_set_cookie_options` no longer used.
* Filter `cocart_cart_use_secure_cookie` no longer used. Use `wc_session_use_secure_cookie` instead.
* Filter `cocart_is_cart_data_valid` no longer used.
* Returned headers `X-CoCart-API-Timestamp` and `X-CoCart-API-Version` no longer used.

### Developers

* Introduced new action hook `cocart_after_session_saved_data` fires after the session is saved.
* Introduced new filter `cocart_send_nocache_headers` to decide if nocache headers are sent.
* Some functions from the cart and products API v2 have been moved to there own utility class so they can be utilized outside of the plugin.
* Added utility function to check for coupon exists.
* Moved two functions from CoCart Plus to be utilized outside of the plugin.

### Compatibility

* Tested with WordPress v6.6
* Tested with WooCommerce v9.0

= v4.1.1 - 14th June, 2024 =

### Bug Fix

* Uncaught error with no featured image for a variation of a variable product. [Solves issue 416](https://github.com/co-cart/co-cart/issues/416)

= v4.1.0 - 6th June, 2024 =

In this release we are adding some quality of life improvements.

### What's New?

* REST API: Added new cart callback that allows you to set the customers billing details. [See guide on how to use](https://cocart.dev/guide/how-to-add-customer-details-to-the-cart/).
* REST API: Basic Authentication now accepts a customers billing phone number as their username. Password is still required when authenticating.
* REST API: Added the ability to set the customers billing phone number while adding item/s to cart.

### Improvements

* Plugin: Added more inline documentation for action hooks and filters.
* Plugin: Should the session not be initialized when called, it now fails safely.
* REST API: Authentication now detectable by authorization headers `HTTP_AUTHORIZATION`, `REDIRECT_HTTP_AUTHORIZATION` or `getallheaders()` function.
* REST API: Re-calculating cart totals has moved to the abstract cart callback so it can be shared.
* REST API: Setting a custom price for an item will now return that price for the item not just update the subtotals and totals.
* REST API: When adding an item to cart with a custom price, checks if the product allows it to change. Set via filter `cocart_does_product_allow_price_change`.
* REST API: Stock details now return for variations in the Products API (V2 Only). Schema updated to match.
* REST API: Added headers `CoCart-API-Cart-Expiring` and `CoCart-API-Cart-Expiration` to be exposed with CORS.
* REST API: Browser cache has been improved.

### Bug Fixes

* REST API: Most product endpoints for API v2 where suddenly not registering since v4.0.

### Deprecations

* Removed the legacy API that CoCart started with.
* Removed support for stores running lower than WooCommerce version 4.5
* User switching removed. Never worked 100%. Mainly added for internal debugging purposes.
* No longer use `cocart_override_cart_item` filter. Recommend using `cocart_item_added_to_cart` action hook instead.
* No longer user `cocart_cart_updated` hook. Replaced with `cocart_update_cart_before_totals` hook.

### Developers

* Introduced new filter `cocart_auth_header` that allows you to change the authorization header.
* Introduced new filter `cocart_set_customer_id` that allows you to set the customer ID before initialized.
* Introduced new filter `cocart_available_shipping_packages` that allows you to alter the shipping packages returned.
* Introduced new filter `cocart_does_product_allow_price_change` that allows you to deny all custom prices or on specific items.
* Introduced new filter `cocart_update_customer_fields` that allows for additional customer fields to be validated and added if supported.
* Introduced new action hook `cocart_after_item_added_to_cart` that allows for additional requested data to be processed once item has added to the cart.
* Introduced new action hook `cocart_after_items_added_to_cart` that allows for additional requested data to be processed once items are added to the cart.
* Introduced new action hook `cocart_update_cart_before_totals` fires before the cart has updated via a callback.
* Introduced new action hook `cocart_update_cart_after_totals` fires after the cart has updated via a callback.

* Added the request object and the cart object as parameters for filter `cocart_cart`. No longer use `$from_session` parameter.

### Compatibility

* Tested with WooCommerce v8.9

= v4.0.2 - 17th May, 2024 =

### Bug Fixes

* REST API: Reverted a change that broke the ability to clear the cart. Was falsely notify it cleared when it did not.
* WordPress Dashboard: WooCommerce System Status was not echoing CoCart tips correctly.

### Improvements

* REST API: Products API, Schema, added properties for reviews section in both API v1 and API v2.

= v4.0.1 - 15th May, 2024 =

### Bug Fix

* REST API: Class `ReserveStock` not found when adding products to cart.

> Developer note: A line was unintentionally removed that calls the class for use.

= v4.0.0 - 13th May, 2024 =

In this release, we are happy to provide some of the various improvements made through out the plugin that were from the originally planned v4 release. These improvements are backwards compatible but one change is not. See the developer note for details.

> Developer note: This release requires the quantity parameter to pass the value as a string for both adding items or updating items. If you are not new to CoCart then please update your code to account for this change.

[Find out more about what’s new in CoCart 4.0 in our release post!](https://cocart.dev/cocart-4-0-released-now-with-cart-batch-support-and-more/)

Hope you enjoy this release.

### What's New?

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

[View the full changelog here](https://github.com/co-cart/co-cart/blob/trunk/CHANGELOG.md).

== Upgrade Notice ==

= 4.3.6 =

Session Handler: Fixed merging of cart from guest.

= 4.3.5 =

REST API: Changed priority for sending headers from `0` to `1` to help with CORS.

= 4.3.4 =

Fixed an issue with CORS not returning header `access-control-allow-origin`.

= 4.3.3 =

Price of product is now consistent in the Cart API if store has no decimals.

= 4.3.2 =

Fixed 3 issues reported that affected CoCart since v4.2.

= 4.3.0 =

We have added a plugin update prevention system as a safety measure. See changelog for more.

= 4.2.2 =

We are reverting a change for destroying a session. See changelog for more.

= v4.2.1 =

Fix: When loading a cart from session a deprecated function was still triggered. It's now been removed to prevent failing.

= v4.2.0 =

Improvement: Optimized the session handler to be more compatibility with third party plugins. See changelog for more.