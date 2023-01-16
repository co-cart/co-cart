=== CoCart - Headless ecommerce === 
Contributors: cocartforwc, sebd86, ajayghaghretiya, skunkbad, sefid-par, mattdabell, joshuaiz, dmchale, JPPdesigns, inspiredagency, darkchris, mohib007, rozaliastoilova, ashtarcommunications, albertoabruzzo, jnz31, douglasjohnson, antondrob2
Tags: woocommerce, rest-api, api, decoupled, headless, cart, products, session
Requires at least: 5.6
Requires PHP: 7.3
Tested up to: 6.1
Stable tag: 3.7.11
WC requires at least: 4.3
WC tested up to: 7.3
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Customizable REST API that lets you build headless ecommerce without limits powered by WooCommerce.

== Description ==

**Important**

Updates for the CoCart plugin on WordPress.org starting on **5th October 2022** will not be getting any further major updates for a long while in order to focus on the paid CoCart Pro version of the plugin. Only minor fixes for bugs will be updated. Don't worry, you'll still be able to use this plugin forever.

Also while API v1 can still be used it will no longer be supported.

### CoCart: The #1 ecommerce RESTful API built for WooCommerce that scales for headless development.

Take your **WooCommerce** business to the next level with **headless ecommerce** using CoCart.

[CoCart](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)'s customizable REST API for WooCommerce allows you to separate your CMS from your presentation layer, while developers can use the frameworks and tools they love.

## 🥪 The API

CoCart provides support for managing the user session, alternative options for doing this task do exist; however, their usage can be limited to applications of the same origin as the WordPress installation. This is due to WooCommerce using cookies to store user session tokens.

CoCart provides the utilities to change this behavior during any cart request and passes the required information to HTTP Header so it can be cached client-side. The use of an HTTP Authorization header is optional allowing users to shop as a guest.

#### 🛒 Cart API

Add **simple, variable** and **grouped products** to the cart by **product ID** or **SKU ID**, update cart items individually or in bulk and more. The flow is simple and returns an updated cart response every time with all the totals calculated and stock checks done for you making it easier to simply update your **UX/UI**.

#### 🛍️ Products API

Access products from your store to display how you like including a number of queries to help you filter by product categories, tags, attributes and more. You can even get posted reviews all without the need to authenticate. All the information you need about a product and it's conditions to help you with your UX/UI development is all provided ready for you.

#### ➕ Extras

* Get store information.
* Login the customer/user.
* Logout the customer/user.
* Empty the cart.

As an added bonus for administrators, CoCart also provides the capabilities to:

* Get Carts in Session.
* Get details of a cart in session.
* View items added in a cart in session.
* Delete a Cart in Session.

## ✨ Features

CoCart also provides built in features to:

* **NEW**: Override the price for simple or variable products added to cart.
* **NEW**: Attach customers email address while adding an item to the cart. **Useful for abandoned cart situations.**
* Load a cart in session via the web. **Useful if you don't have a headless checkout and want to use the native checkout.**
* Supports **guest customers**.
* Supports **basic authentication** including the use of email as the username.
* Support [authentication via WooCommerce's method](https://cocart.dev/authenticating-with-woocommerce-heres-how-you-can-do-it/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).
* Supports multi-site network.
* Does **NOT Cache** the API so responses are **fresh** every time.
* Works across multiple domains, CORS ready! **So you can have multiple frontends connected to one backend.**
* Allows you to filter CoCart to be white-labelled.

Included with these features are **over 100+ [filters](https://docs.cocart.xyz/#filters)** and **[action hooks](https://docs.cocart.xyz/#hooks)** for developers to customize API responses or change how CoCart operates with our extensive documentation.

## 🧰 Tools and Libraries

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to easily update to prerelease versions of CoCart for testing and development purposes.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product types to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Official Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

### 📦 CoCart Pro

CoCart is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) extends with the following [features](https://cocart.xyz/features/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart):

* **Plugin Updates** for 1 year.
* **Priority Support** for [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) users via Slack.
* Add and Remove Coupons to Cart
* Retrieve Coupon Discount Total
* Retrieve and Set Payment Method
* Retrieve and Set Shipping Methods
* Retrieve and Set Fees
* Calculate Shipping Fees
* Calculate Totals and Fees

Features that will be available in the future:

* Remove all coupons from cart
* Register Customers
* Retrieve checkout fields (More details on that soon)
* Set cart customer (In Development)
* Create Order (In Development)

For a logged in customer:

* Return Customers Orders
* Return Customers Subscriptions
* Return Customers Downloads (Auditing)
* Return Customers Saved Payment Methods
* Get and Update Customers Profile (In Development)

[Buy CoCart Pro Now](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Extensions supported

[View list of the WooCommerce extensions](https://cocart.xyz/woocommerce-extensions/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) that support CoCart or are supported in [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

## 📢 Testimonials - Developers love it

★★★★★
> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time. [Harald Schneider](https://wordpress.org/support/topic/excellent-plugin-8062/)

★★★★★
> Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

★★★★★
> This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

★★★★★
> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription. [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

★★★★★
> This plugin works great out of the box for adding products to the cart via API. The code is solid and functionality is as expected, thanks Sebastien! [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

#### More testimonials

[See the wall of love](https://cocart.xyz/wall-of-love/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### 😍 Priority support

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our premium customers. Communication is handled one-on-one via direct messaging in [Slack](https://app.slack.com/client/TD85PLSMA/) and is available to people who purchased [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

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

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** simply filters the session cookie to allow CoCart to work across multiple domains.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it.
* **[Advanced Custom Fields](https://cocart.xyz/add-ons/advanced-custom-fields/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)** extends the products API by returning all your advanced custom fields for products.
* and more add-ons in development.

They work with the FREE version of CoCart already, and these add-ons of course come with support too.

### ⌨️ Join our growing community

A Slack community for developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

[Join our community](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

### Built with developers in mind

Extensible, adaptable, and open source — CoCart is created with developers in mind. If you’re interested to jump in the project, there are opportunities for developers at all levels to get involved. [Contribute to CoCart on the GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and join the party. 🎉

Check out [open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aissue+is%3Aopen) and join the [#core channel](https://cocart.slack.com/messages/C014C4581NE) on Slack. If you don’t have a Slack account yet, you can sign up at [https://cocart.xyz/community/](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

### 🐞 Bug reports

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

### More information

* The [CoCart plugin](https://cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) official website.
* [CoCart for Developers](https://cocart.dev/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) official hub for resources you need to be productive with CoCart and keep track of everything that is happening with the plugin.
* [CoCart API Reference](https://docs.cocart.xyz/)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/co_cart/) and [GitHub](https://github.com/co-cart/co-cart)

#### 💯 Credits

This plugin is created by [Sébastien Dumont](https://twitter.com/sebd86).

== Installation ==

= Minimum Requirements =

* WordPress v5.6
* WooCommerce v4.3
* PHP v7.3

= Recommended Requirements =

* WordPress v6.0 or higher.
* WooCommerce v7.0 or higher.
* PHP v7.4

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

Yes! CoCart’s core features are absolutely free. [CoCart Pro completes the full cart experience!](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart)

= How do I start using CoCart? =

You will first need WooCommerce installed with the REST API enabled. Then install CoCart and follow the documentation.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Who should use CoCart? =

CoCart is perfect for ecommerce owners and developers who want to create an ecommerce app for mobile or a custom frontend shopping experience completely using the REST API.

= Do I need to have coding skills to use CoCart? =

As this plugin provides a REST API built for developers, you will need to have some coding knowledge to use it. [Checkout the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) to get some understanding.

= Where can I find documentation for CoCart? =

You can find the documentation for CoCart on the [CoCart REST API Docs](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= Can I change the layout format/add/change details to the responses? =

You certainly can. There are over 100+ filters available to do just that. [Checkout the tweaks plugin](https://github.com/co-cart/co-cart-tweaks) to view or maybe use the examples provided. [View the documentation](https://docs.cocart.xyz/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) for more.

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

Yes of course. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can [manually enable it via the filters in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

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

CoCart's API is designed for decoupling away from WordPress and lets you build headless ecommerce using your favorite technologies. **No Nonces, no cookies.**

CoCart is packed full of powerful features that are completely customizable making it possible for businesses to build a complete custom storefront how they want.

No matter the type of store you are running, CoCart helps you grow.

It’s made by and for developers and immediately allows you to create sophisticated experiences fast with unlimited possibilities with it’s plug and play solution that just works out of the box.

So even if you are new to building a headless ecommerce or already have a WooCommerce store and been wanting to go headless, nows the time to start.

Don't take my word for it. Checkout the testimonials left by startups, freelancers, agencies and many more.

= Do you have a JavaScript Library? =

Yes we do. You can [find it here](https://www.npmjs.com/package/@cocart/cocart-rest-api). It doesn't require authentication for guest customers. It supports CommonJS (CJS) and ECMAScript Modules (ESM). Requests are made with [Axios library](https://github.com/axios/axios) with [support to promises](https://github.com/axios/axios#promises).

= Can I install/update CoCart via Composer? =

Yes. The best method would be to install/update CoCart from the GitHub repository but you can also do so via [https://wpackagist.org/](https://wpackagist.org/search?q=cart-rest-api-for-woocommerce&type=plugin)

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it via the network and all sites will have CoCart enabled.

= Can I enable white labelling for CoCart? =

Yes you can. You will have to edit your `wp-config.php` file to add a new constant. [Details can be found in the documentation](https://cocart.dev/articles/wp-config-php/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart#white-labelling).

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the support forum – be sure to search the forums to confirm that the error has not already been reported.

= CoCart is awesome! Can I contribute? =

Yes, you can! Join in on our [GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and follow the [development blog](https://cocart.dev/news/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) to stay up-to-date with everything happening in the project.

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I get help or talk other users about CoCart core? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Slack](https://cocart.xyz/community/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by either purchasing [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= Where can I find out about the pricing of CoCart Pro? =

Find out all relevant [pricing information over on the official site](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

= My question is not listed here. Where can I find more answers? =

Check out [Frequently Asked Questions](https://cocart.xyz/faq/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart) for more.

== Screenshots ==

1. Get Products (API v2)
2. Get Individual Product (API v2)
3. Add Item to Cart (API v2)

== Contributors & Developers ==

"CoCart" has **not** yet been translated in other languages. You can [translate "CoCart" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

== Changelog ==

If you like CoCart, please take a moment to [provide a review](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/#new-post). It helps to keep the plugin going strong, and is greatly appreciated.

= v3.7.11 - 16th January, 2023 =

### What's New

* Improved compatibility with PHP 8.1+
* Tested: ✔️ Compatible with WooCommerce v7.3

= v3.7.10 - 30th December, 2022 =

### What's New

* Tested: ✔️ Compatible with WooCommerce v7.2
* Tested: ✔️ Compatible with WordPress v6.1

### Bug Fix

* Fixed viewing an individual session that has coupons.

= v3.7.9 - 4th November, 2022 =

### Bug Fixes

* Fixed item custom price not being applied from cart cache when loaded from session.
* Fixed a uncaught `array_merge()` fatal error where a **null** value was given instead of an **array**.

= v3.7.8 - 29th October, 2022 =

### What's New

* Tested: ✔️ Compatible with WooCommerce v7.0

### Tweaks

* Improved getting request parameters for delete method endpoints.
* Reordered some filtering when passing data via parameters.

### Bug Fixes

* Fixed a undefined array key warning related to use of `apply_filters_deprecated`. Reported by [@douglasjohnson](https://profiles.wordpress.org/douglasjohnson/) [Bug Report](https://wordpress.org/support/topic/undefined-array-key-warning-realted-to-use-of-apply_filters_deprecated/)
* Fixed a fatal error when returning removed items that no longer exists. Now it's removed from the cart completely should the item not be found. Reported by [@antondrob2](https://profiles.wordpress.org/antondrob2/) [Bug Report](https://wordpress.org/support/topic/php-fatal-error-uncaught-error-17/)

= v3.7.7 - 20th October, 2022 =

### Enhancement

Moved item validation further up to identify sooner if the product no longer exists when attempting to update an item's quantity. [issue #356](https://github.com/co-cart/co-cart/issues/356)

= v3.7.6 - 23rd September, 2022 =

### Bug Fixes

* Fixed an issue were on a rare occasion, the product data is somehow not there when updating an item in cart. [issue #355](https://github.com/co-cart/co-cart/issues/355)
* Fixed an issue were you add more than one item to the cart with a custom price and then increase the quantity of one of those items after. All other items with a custom price would reset to the original price.

= v3.7.5 - 14th September, 2022 =

### What's New

* Tested: ✔️ Compatible with WooCommerce v6.9

### Bug Fixes

* Fixed undefined value for querying products via review ratings.
* Fixed issue with identifying screen ID when using the "Setup Wizard" with WooCommerce 6.9+

= v3.7.4 - 13th July, 2022 =

This minor release is related to Yoast SEO support.

### Tweaks

* Unlocked a change made in **v3.4.0** by un-registering the rest field `yoast_head` for the Products API.

Originally it was to keep the JSON response valid because a bug at the time was causing the response to not return correctly. It was also to increase the performance of the response as Yoast SEO returns the same data twice just in a different format. Now the issue appears to be gone and recent feedback suggested this should be left on by default.

Other improvements for supporting third party plugins are in the works.

If you want to discuss supporting a third party plugin, [start a discussion](https://github.com/co-cart/co-cart/discussions) on the CoCart GitHub repository.

= v3.7.3 - 23rd June, 2022 =

### What's New

* Added `get_session_data()` function to the session handler. Some plugins appear to be accessing it (though I don't recommend it).

= v3.7.2 - 20th June, 2022 =

### Improvements

* Adjusted WooCommerce detection when installing CoCart on a completely fresh WordPress install. Related to [[issue #341](https://github.com/co-cart/co-cart/issues/341)]
* Removed "Turn off CoCart" button from admin notice as the plugin already deactivates if WooCommerce not detected.
* Prevent plugin action links from showing if CoCart is not active.
* Tested: ✔️ Compatible with WooCommerce v6.6

= v3.7.1 - 13th June, 2022 =

### What's New

* 🚀 You can now limit the results set to products assigned a specific category or tag via their slug names instead of ID.

Example of limiting products via category and tag. `wp-json/cocart/v2/products/?category=accessories&tag=hats`

> There was some confusion with this as the documentation said (query by ID) but the API schema said (query by slug). Now you can do either. This adjustment affects both API versions.

= v3.7.0 - 31st May, 2022 =

### What's New

* Improved: CoCart does not proceed with any installation when activated unless WooCommerce is active first. Solves [[issue #341](https://github.com/co-cart/co-cart/issues/341)]
* Tested: ✔️ Compatible with WooCommerce v6.5
* Tested: ✔️ Compatible with WordPress v6.0

= v3.6.3 - 11th May, 2022 =

**🔥 This is a HOTFIX!**

### Bug Fix

* Undone change made to `cocart_prepare_money_response()` function. Another WC extension using the filter `cocart_cart_item_price` confused me and was overriding the format returned.

> This reverts partially back to v3.6.1

= v3.6.2 - 10th May, 2022 =

### Improvements

* Improved `cocart_prepare_money_response()` function. Cleans up string values better.
* Additional decimals gone for item price.

### Tweaks

* Item price and subtotal now returns correct money response.

= v3.6.1 - 6th May, 2022 =

### Bug Fixes

* Fixed calling `update_plugin_suggestions()` function the non-static method. For WordPress Dashboard > Plugins > Add New.
* Fixed undefined `$variations` for `get_variations()` function. For Products API v2 thanks to [@jnz31](https://github.com/jnz31)
* Improved `get_connected_products()` function to validate product ID's before returning. For Products API v2. Solves [[issue #336](https://github.com/co-cart/co-cart/issues/336)]

= v3.6.0 - 24th April, 2022 =

### What's New?

* Added support to prevent CoCart from being cached with [WP Super Cache](https://wordpress.org/plugins/wp-super-cache/) plugin.
* Added support to prevent CoCart from being cached with specific web hosts like [Pantheon](https://pantheon.io/docs/cache-control).

### For Developers

* Introduced new filter `cocart_send_cache_control_patterns` that allows you to control which routes will not be cached in the browser.

= v3.5.0 - 21st April, 2022 =

### What's New?

* Improved: Plugin suggestions now fetches data from a JSON file and is cached once a week.
* Tweak: Quality of life update for Cart API v1. Should item added to cart not have an image it will fallback to the placeholder image.
* Tested: ✔️ Compatible with WooCommerce v6.4

### Bug Fix

* Fixed Products API v2 Schema for Images.

> Related to a change made in v3.2.0

= v3.4.1 - 4th April, 2022 =

### Bug Fix

* Fixed: An uncaught undefined function `add_meta_query` which allows you to query products by meta. Thanks to [@jnz31](https://wordpress.org/support/topic/uncaught-error-call-to-undefined-method-cocart_products_v2_controlleradd_meta/) for reporting the error.

> Dev note: I'm an idiot for not finding this issue sooner. The function `add_meta_query` was not committed when the products API add-on was merged with the core of CoCart. 🤦‍♂️ Please accept my apologies for the issue caused. 🙏

### Deprecated & Replacement

* Deprecated use of `wc_get_min_max_price_meta_query` function. Although it was *deprecated* in WooCommerce since **v3.6** there was never a replacement provided and it was still working. Now the function has just been copied into a new function `cocart_get_min_max_price_meta_query` and will no longer provide the debug warning. It can be improved in the future if needed.

= v3.4.0 - 28th March, 2022 =

### What's New?

* Tweak: Unregistered rest field `yoast_head` from the Products API to keep the JSON response valid and increase performance.

> The rest field `yoast_head_json` still remains.

= v3.3.0 - 24th March, 2022 =

### What's New?

* Enhancement: Appends the cart query (Load Cart from Session) to the checkout URL so when a user proceeds to the native checkout page from the native cart, it forces to load that same cart. - **Guest Customers ONLY**

> This was added due to some circumstances the cart failed to load then after on the checkout page via normal means.

### Tweaks

All custom headers introduced by CoCart with `X-` prefixes (no longer a recommended practice) now have a replacement. Please use the new headers listed below instead.

> 📢 All current `X-` prefixed headers will be removed in a future release of CoCart.

| Previous Header        | New Header           |
| ---------------------- | -------------------- |
| X-CoCart-API           | CoCart-API-Cart-Key  |
| X-CoCart-API-Timestamp | CoCart-Timestamp     |
| X-CoCart-API-Version   | CoCart-Version       |

### For Developers

* Introduced new filter `cocart_use_cookie_monster` to prevent destroying a previous guest cart and cookie before loading a new one via Load Cart from Session. Thanks to [Alberto Abruzzo](https://github.com/AlbertoAbruzzo) for contributing further feedback.

> Dev note: Helps should you find the web browser is displaying the "Cookie was rejected because it is already expired." in the console log and the cart did not load again on refresh despite the session still being valid.

= v3.2.0 - 17th March, 2022 =

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

[View the full changelog here](https://github.com/co-cart/co-cart/blob/trunk/CHANGELOG.md).

== Upgrade Notice ==

= 3.7.11 =

* Improved compatibility with PHP 8.1+
