=== CoCart - Headless REST API for WooCommerce ===
Contributors: cocartforwc, sebd86
Tags: woocommerce, rest-api, decoupled, headless, cart
Requires at least: 6.7
Requires PHP: 8.2
Tested up to: 6.9
Stable tag: 4.8.3
WC requires at least: 9.0
WC tested up to: 10.7
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

A developer-first REST API to decouple WooCommerce on the frontend to help build modern and scalable storefronts. Fast, secure, customizable, easy.

== Description ==

Stop struggling with debugging cart sessions, broken cart flows and cache issues.

**CoCart: A developer-first REST API for Headless WooCommerce**

[CoCart](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) handles the hard parts so you can focus on your store, products and customers. Take advantage of our SDK's to quickly setup and integrate with **Astro**, **Next.js**, **React**, **Vue**, or any modern framework to build your headless storefront — gaining complete control over the customer experience, independent of WordPress.

## Quick Start

```bash
# Add an item to the cart
curl -X POST https://your-store.com/wp-json/cocart/v2/cart/add-item \
  -H "Content-Type: application/json" \
  -d '{"id": "123", "quantity": 1}'
```

See the [full documentation](https://cocartapi.com/docs/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=firsttime) for all available endpoints and [create a sandbox](https://cocartapi.com/try-free-demo/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=firsttime) to try it out.

If you use Raycast, [install our documentation extension](https://www.raycast.com/cocart_headless/cocart-docs) for quick easy access.

## Official SDKs

Get started in your language of choice. Each SDK handles authentication, session management, and cart operations out of the box including currency formatting and timezone for dates.

| SDK | Language | Repository |
|-----|----------|------------|
| **cocart-js** | TypeScript / JavaScript | [GitHub](https://github.com/cocart-headless/cocart-js) |
| **cocart-php** | PHP | [GitHub](https://github.com/cocart-headless/cocart-php) |
| **cocart-python** | Python | [GitHub](https://github.com/cocart-headless/cocart-python) |
| **cocart-go** | Go | [GitHub](https://github.com/cocart-headless/cocart-go) |

## 💬 Loved by developers worldwide

★★★★★
> "An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time." - [Harald Schneider](https://wordpress.org/support/topic/excellent-plugin-8062/)

★★★★★
> "This plugin works great out of the box for adding products to the cart via API. The code is solid and functionality is as expected, thanks Sebastien!" - [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

★★★★★
> "This plugin saved me tons of work and it is working amazingly! The plugin author provides fast and high-quality support. Well done!" - [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

#### Why 1,000+ developers choose CoCart

**🚀 Core cart functionality (FREE)**
* ✅ **Zero learning curve** - Built on WooCommerce Data Stores with familiar hooks, ensuring broad plugin compatibility.
* 🔐 **Session management** - Cookie-less, database-stored sessions. Handle concurrent users without breaking a sweat.
* 🛒 **Essential cart operations** - Add, remove, update items and calculate totals in simple API calls.

**💻 Developer experience that doesn't suck**
* 🔑 **Authentication that makes sense** - Email, username, or phone login. No admin API keys to juggle.
* 🌍 **CORS just works** - First-party CORS support means your frontend connects instantly, no configuration hell.
* 🧩 **Extendable Callbacks** - Add your own logic without writing new API routes.
* 📦 **Bulk Cart Requests** - Combine multiple API calls into one for better performance.
* 📊 **Cart Insights** - Monitor all cart sessions, including those nearing expiration or already expired.

**🎯 WooCommerce compatibility, guaranteed**
* 🛠 **Native checkout support** - Load any cart session into WooCommerce's checkout. Your payment gateways work seamlessly.
* 🔎 **Product search** - Query by name, SKU, or ID — authenticated or not — with flexible filtering.
* 💸 **Name Your Price support** - Donation-based pricing with built-in flexibility.

**🛍️ Premium Features**

For more powerful features and enterprise-level control — upgrade to **[CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)** and complete your headless store.

* 🎫 **Coupon Management** - Apply discounts, promo codes, and boost conversions.
* 🚢 **Shipping Calculations** - Real-time shipping rates and method selection.
* 💰 **Cart Fees** - Add handling fees, rush charges, or custom pricing logic.
* 🥪 **Advanced Batch API** - Process multiple cart operations in a single request for lightning speed.
* 🕒 **Rate Limiting** - Prevent API abuse and maintain high performance under load.
* 🧾 **Checkout** - Complete an order and take payment using any supported gateways by WooCommerce. (Coming Soon)
* 💲 **Subscription Support** - Complete new subscriptions or renewals automatically or manually. (Coming Soon)

#### 👍 Add-ons to further enhance CoCart

We also have add-ons that extend CoCart to enhance your development and your customers’ shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** enables support for CORS to allow CoCart to work across multiple domains.
* **[CoCart - Rate Limiting](https://wordpress.org/plugins/cocart-rate-limiting)** enables the rate limiting feature for CoCart Plus or higher.
* **[CoCart - JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication)** allows you to authenticate via a simple JWT Token.

These add-ons of course come with support too.

★★★★★
> "Thanks for doing such great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that's one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription." - [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

[See our wall of love](https://cocartapi.com/wall-of-love/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for more developer testimonials.

### 💜 Need Support?

We aim to provide regular support for the CoCart plugin via [our Discord community server](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink). Please understand that we do prioritize support for our [paying customers](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

### ⌨️ Join our growing community

On Discord, we have a community of developers, WordPress agencies, and shop owners building the fastest and best headless WooCommerce stores with CoCart.

Come and [join our community](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

## 🧰 Developer Tools

* **[CoCart OpenAPI Specs](https://github.com/cocart-headless/cocart-openapi)** — OpenAPI definitions for CoCart REST API endpoints. Use them to generate client libraries, test with Yaak/Postman/Insomnia, or integrate with any tool that supports the OpenAPI standard.
* **[CoCart VSCode](https://github.com/cocart-headless/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes, and hooks.
* **[CoCart Cart Callback Example](https://github.com/cocart-headless/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

### More information

* [Website](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).
* [Documentation](https://cocartapi.com/docs/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [X/Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/cocartheadless/) and [GitHub](https://github.com/co-cart/co-cart)

#### 💯 Credits

This plugin is developed and maintained by [Sébastien Dumont](https://twitter.com/sebd86).
Founder of [CoCart Headless, LLC](https://twitter.com/cocartheadless).

== Installation ==

= Minimum Requirements =

* WordPress v6.7
* WooCommerce v9.0
* PHP v8.2

= Recommended Requirements =

* WordPress v6.7 or higher.
* WooCommerce v10.0 or higher.
* PHP v8.3 or higher.

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CoCart, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "CoCart" and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

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

CoCart provides a frontend API for WooCommerce, enabling headless eCommerce development. Instead of being locked into WordPress themes, you can build lightning-fast storefronts with modern frameworks like React, Vue, Next.js, or any technology you prefer.

= Who should use CoCart? =

**Developers** who are tired of working on slow performance WooCommerce storefronts and want the freedom to build with modern frameworks. Perfect for:
- Frontend developers building headless eCommerce stores
- Agencies creating high-performance client sites
- Mobile app developers needing eCommerce APIs
- Anyone wanting infinite customization

= Will my existing WooCommerce plugins still work? =

Plugins that modify backend functionality (payment gateways, shipping methods, tax calculations, inventory management) continue to work. Plugins that only modify the PHP frontend (themes, shortcodes, widget-based rendering) won't apply to the REST API layer.

= What is the source of truth? =

CoCart sources the WooCommerce’s Data Stores API and repeats most WooCommerce hooks to provide a wider array of support for most WooCommerce extensions out of the box.

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it on the sites you want to use CoCart.

= Can I have WordPress running on one domain and my headless eCommerce on another domain? =

Absolutely. That is what CoCart is mainly developed for. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can manually enable it via the filters available [in the documentation](https://cocartapi.com/docs/#filters-api-access-cors-allow-all-cross-origin-headers).

= How do I set up CoCart? =

You will first need WooCommerce installed and set up to your configurations. Then install CoCart, activate and you're ready to start using the REST API following the API Reference provided.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Why use CoCart if the WooCommerce API already exists? =

The WooCommerce REST API is primarily for store management (orders, products, coupons, etc.) and is designed for authenticated admins/apps. It isn’t built for customer cart flows.

CoCart fills this gap. It's specifically built for frontend cart handling where you want a seamless shopping experience that mimics traditional WooCommerce behavior but via API.

It’s optimized, extendable, and built for performance in decoupled setups.

= Why use CoCart and not WooCommerce’s Store API? =

To better answer this question in detail, please read [our comparison article](https://cocartapi.com/cocart-vs-woocommerces-store-api/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Can we support SSO? =

CoCart itself doesn’t use cookie-based authentication — it supports Basic and JWT Authentication (via add-on), which are stateless and frontend-friendly. So when a user logs in through your headless site/app, they're authenticated for API requests — including cart operations.

= Can we call other WP APIs alongside CoCart? =

Definitely. CoCart doesn't block or replace any part of the WP or WC APIs — it works alongside them. Once authenticated, your headless site/app can access any available endpoint, whether from CoCart, WooCommerce, or custom APIs you've built.

= Do I need to have coding skills to use CoCart? =

**Yes, CoCart is built for developers.** If you can make HTTP requests and work with JSON responses, you're ready to use CoCart.

**Skill level needed:**
- Basic understanding of REST APIs
- Experience with JavaScript, React, Vue, or your preferred frontend framework
- Familiarity with HTTP requests (GET, POST, etc.)

**No WordPress development experience required!** CoCart abstracts away WordPress complexity, giving you clean, predictable API responses.

= Where can I find documentation for CoCart? =

You can find the documentation [here](https://cocartapi.com/docs/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Can I change the formatting of values, add and change details to the responses? =

You certainly can. There are over 200+ filters available to customize to your needs.

= Why does CoCart use a custom session handler and table in the database? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler **does not support guest customers**.
- The default session handler **does not store additional data that may be required to help you**.
- The default session handler **does not allow support for POS capability**.
- More consistent with modern web.

= Is "WooCommerce Shipping and Tax" plugin supported? =

No. "WooCommerce Shipping and Tax" ignores any custom REST APIs from allowing the ability to calculate the taxes from TaxJar except for WooCommerce Blocks and JetPack. We don't recommend it. However, [TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "[TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/)" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Can I use any modern stack? =

Yes, you can use your preferred tools and favorite modern technologies like [Astro](https://astro.build/), [NextJS](https://nextjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/), [Ember](https://emberjs.com/) and more giving you endless flexibility and customization.

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the [Discord community server](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) in the #bug-report channel – be sure to search the forum to confirm that the error has not already been reported.

= CoCart is awesome! How can I follow? =

You can follow the [development blog](https://cocartapi.com/blog/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) to stay up-to-date with everything happening in the project. Announcements are also shared in the [Discord community server](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I get help or talk other users about CoCart? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Discord](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by purchasing [CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) or a higher tier.

= Where can I find out more about the additional features? =

Find out all relevant [features and pricing information over on the official site](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= My question is not listed here. Where can I find more answers? =

Check out [Frequently Asked Questions](https://cocartapi.com/faq/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for more.

== Contributors & Developers ==

You can help [translate "CoCart" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code on GitHub](https://github.com/co-cart/co-cart/tree/development/), or follow the [CoCart development blog](https://cocartapi.com/blog/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for the latest development updates. You can also follow [@cocartapi](https://twitter.com/cocartapi) on Twitter to stay up to date about everything happening with CoCart.

**Please share your experience**

We’d love to hear what you have to say. [Share your experience](https://testimonial.to/cocart) and help others discover CoCart. It helps to keep the plugin going strong, and is greatly appreciated.

== Changelog ==

📢 Only bug and security updates will be provided here on WordPress dot ORG. Our [premium versions](https://cocartapi.com?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) are more optimized for performance and enhanced with more features.

This is a community edition of the core of CoCart. Response time for support is slower than paying customers. Thank you for your understanding.

> Plugin name change showing before and after below.

**Before**: Headless eCommerce API for Developers
**Now**: CoCart - Headless REST API for WooCommerce

= v4.9.0 - [DATE] =

### What's New?

> [!IMPORTANT]
> PHP version 8.2 is the minimum requirement to install and use CoCart. PHP 7.4, 8.0, and 8.1 are all past end-of-life/security support so to help manage development resources to maintain CoCart, it will no longer be tested on these versions. Running CoCart on PHP 8.2 not only ensures you are secure but it also improves the performance of the CoCart API. Thank you for your understanding.

* REST API: Added ETag support for all cart endpoints (GET/POST/PUT/DELETE) enabling conditional requests with `If-None-Match` header for immediate use.
* REST API: Added ETag support for product endpoints (products, categories, tags, attributes, reviews).
* REST API: Added `CoCart-Cache` response header indicating cache status: `HIT`, `MISS`, or `SKIP`.
* REST API: Added `_skip_cache` query parameter to bypass caching for individual requests.
* REST API: Added `stale-while-revalidate` directive to cacheable routes (products) for improved performance.
* REST API: Added check for `X-HTTP-Method-Override` header if it exists during authentication.
* WordPress Dashboard: New integrations page to provide more control over which supported plugin is running when making a CoCart request.

### Bug Fixes

* REST API: Slugs and permalinks with non-ASCII characters (e.g., Chinese, Arabic) are now returned decoded instead of URL-encoded across all product and cart endpoints.
* REST API: Authentication not determining user in time when `rest_url_prefix` filter is used causing several cloned guest sessions with cart items.
* REST API: Fixed fatal error when adding an invalid product to cart via v2 controller due to missing `WP_Error` check after product validation.
* REST API: Corrected `Last-Modified` header in GMT per HTTP spec with more robust date parsing.

#### PHP 8.2+ Fixes

* REST API: Fixed warnings for undefined array keys `cart_item_data` and `cart_item_key` in v1 add item controller.
* Session: Fixed warning for undefined array key `total` in session handler cart hash generation.
* Plugin: Fixed warnings for undefined array key `count` when database queries return no results in cart counting functions.
* Plugin: Fixed warning for undefined variable `$per_page` in admin plugin search.
* Plugin: Fixed deprecated `${var}` string interpolation syntax in plugin update screen.

### Change

* Session: Removed the max expiration exceed limit of 30 days to allow adjusting the lifetimes via the `cocart_cart_expiring` and `cocart_cart_expiration` filters as needed.

> Dev note: You will still be warned "Keeping sessions for longer than X days can cause performance issues and larger session tables."

### Improvements

* REST API: Slight performance increase by preventing admin content from loading in the background for every REST request made.
* Session: Adjusted `get_session()` using `wp_cache_set()` instead of `wp_cache_add()`, which caused stale persistent object cache entries to never be overwritten.
* Session: Adjusted `get_session()` and `update_cart()` using an uninitialized expiration value on frontend requests, preventing sessions from being cached after a database read.
* Session: Adjusted `save_data()` writing to object cache with a potentially negative TTL when session expiration was stale or unset.
* Session: Adjusted `update_cart()` not syncing the object cache after a database write, causing subsequent reads to return stale cached data.
* Session: Added `get_cache_expiration()` helper to reliably resolve cache expiration across both REST API and frontend contexts.
* Session: When an authenticated customer provides a guest `cart_key`, their existing cart items are now **merged** with the guest cart rather than replaced by it. Guest items not already present in the user's cart are added; the user's existing items are always preserved. Administrators and shop managers are excluded from this behaviour by design — their sessions are never auto-merged.

### Developers

* Logger: Log entries now include a source line identifying the calling class, method, file, and line number for easier debugging.
* Introduced new filter `cocart_etag_cart_routes` to customize which cart routes support ETag.
* Introduced new filter `cocart_etag_product_routes` to customize which product routes support ETag.
* Introduced new filter `cocart_etag_routes` to add ETag support for third-party plugin routes.
* Introduced new filter `cocart_cache_max_age` to customize cache duration (default: 1 hour).
* Introduced new filter `cocart_stale_while_revalidate` to customize stale-while-revalidate duration (default: 24 hours).

### Compatibility

* Tested with WordPress 7.0
* Tested with WooCommerce v10.7

= v4.8.3 - 26th January, 2026 =

### Bug Fixes

* REST API: Updating a customer address after one is placed would not update.
* REST API: Return error responses correctly so all headers return.

### Changes

* Plugin: Updated broken external links throughout the plugin.

= v4.8.2 - 20th January, 2026 =

### Improvements

* Plugin: WordPress plugin checker helped resolve a few PHP code standards.

### Compatibility

* Tested with WordPress 6.9
* Tested with WooCommerce v10.4

= v4.8.1 - 24th November, 2025 =

### Bug Fixes

* REST API: Updating a customer address after one is placed would not update.
* REST API: No customer data, no applied coupons or removed items in session caused undefined errors.
* REST API: Customer data was not converting correctly to return in the Session API.
* REST API: The product object was not passed correctly in the Session API for items.
* REST API: Damaged or empty cart sessions was failing in the Session API.

### Improvements

* Plugin: WordPress plugin checker helped resolve a few database issues to keep up with security practices.

### Compatibility

* Tested with WooCommerce v10.3
* Added support for the next CoCart Plus update.

= v4.8.0 - 22nd September, 2025 =

### What's New?

* Authentication: Enhanced login endpoint with improved permissions control via the new hooks mentioned below.

### Improvements

* Authentication: Removed priority order so our JWT Auth integration can run earlier in the process.
* Authentication: Refactored `get_ip_address` for better trusted proxy support and better IP address detection with additional headers.
* REST-API: Session handler now loads during the login endpoint operations.
* Security: Item keys are now restricted to 32 characters maximum for better validation.
* User Management: Refactored `is_user_customer` function to support additional user roles beyond just customers.

### Developers

* Logging: Added informational logs for IP address detection and proxy handling.
* Introduced a new filter `cocart_login_permission_callback` allows additional authentication checks after basic authorization for the login endpoint.
* Introduced a new filter `cocart_login_secure_auth_methods` determines which authentication methods should skip additional auth checks.
* Introduced a new filter `cocart_login_query_parameters` allows plugins to add additional parameters to the login endpoint.
* Introduced a new filter `cocart_trusted_proxies` allows adding trusted proxy IPs/CIDR for secure IP detection.
* Introduced a new filter `cocart_ip_headers` allows customization of headers used for IP address detection.
* Introduced a new hook `cocart_login_permission_granted` that triggers when login permission is granted for the login endpoint.

### Compatibility

* Tested with WooCommerce v10.2

= v4.7.0 - 8th August, 2025 =

### What's New?

* Authentication: Added support for authenticating via JSON request body with clear indication for the login endpoint (API v2 ONLY).

### Improvements

* Authentication: Internal refactor to return `WP_Error` consistently from permission checks.
* REST API: Login (API v2 ONLY) Explicit added query params for `username` and `password`.

= v4.6.4 - 6th August, 2025 =

### Bug Fixes

* REST API: Fixes both the product review and rating count.
* Feature: Fixed "Load Cart from Session" from destroying sessions once loaded due to session improvements made in WC v10.

### Improvements

* Plugin: Ensure that dependent plugins can be installed/activated if the plugin is installed in a different folder name.
* Feature: "Load Cart from Session" improved session data checking.
* Session handler: Reduced duplicate session calls and optimized `update_session_timestamp()` database query.
* Session handler: Restored `persistent_cart_update` compatibility for WooCommerce v10; only active for versions lower than v10.1.
* Session handler: Overrode `session_exists()` and `delete_session()` to use CoCart's session table.
* Load Cart: Switched from `$_REQUEST` to `$_GET` and removed the priority for `load_cart_action`.
* Load Cart: Re-enabled `initialize_cart_session()` and stopped destroying cookies when loading carts.

### Deprecated

* Action hook `cocart_load_cart_override` is no longer used.

= v4.6.3 - 27th July, 2025 =

### Bug Fix

* REST API: Fixes identifying namespace and routes in the WordPress REST API Index if not set should they already be filtered out.

= v4.6.2 - 25th July, 2025 =

### Bug Fix

* REST API: Removing an item stays removed. [Solves issue #534](https://github.com/co-cart/co-cart/issues/534)

> WooCommerce v10 caused a cache issue due to a change in the many times session data is handled.

### Improvements

* Plugin: Session handler optimized - New sessions created first, then auth users if no cart requested.
* Plugin: Session handler - Removed the need to set cart hash at the start.
* Plugin: Session handler - Added a warning log for when the session data must have really screwed up.
* Plugin: Session handler - Added max expiration exceed limit to 30 days to avoid performance issues and the session table growing too large.
* REST API: Check REST request is CoCart before maybe loading cart or filtering served requests.
* REST API: Fixed deprecated functions still called in Products API.
* REST API: Authentication and CORS optimized to parse data less allowing for a faster response.
* REST API: Moved global headers to be filtered in `rest_pre_serve_request` instead of `CoCart_Response` which is not used for Products API.
* Plugin: Moved the cart cache to load once WooCommerce has loaded instead of only during the REST API.

> Developer note: Cart cache allows for items with custom pricing to be calculated on the native site and not just via the REST API to keep consistent with calculations.

### Requirements

* WooCommerce v9 minimum is now required for CoCart but for best performance recommend using v10+

### Compatibility

* Tested with WooCommerce v10.0.4

= v4.6.1 - 21st July, 2025 =

### Bug Fixes

* REST API: Fixed `undefined array key` errors with cart session when cart is empty. [Solves Issue #533](https://github.com/co-cart/co-cart/issues/533)
* REST API: Fixed removing an item using the update endpoint when it thinks quantity value is not numeric.

### Compatibility

* Tested with WooCommerce v10.0.3

= v4.6.0 - 26th June, 2025 =

This release is a compatibility release for the next WooCommerce release.

### What's new?

* WordPress dot ORG: Added a Playground blueprint.

### Changes

* Plugin: Branding for CoCart has been updated.
* Plugin: Styling for CoCart pages have been improved and more consistent on all pages by reducing conflicts with WordPress and WooCommerce styling.

### Improvements

* REST API: Basic authentication is detected much better.
* REST API: Authentication failures now has debug logs.
* WordPress Dashboard: Semantic markup overhaul for better screen reader interpretation.
* WordPress Dashboard: Setup wizard and Support pages have been updated.

### Compatibility

* Tested with WooCommerce v10.0

= v4.5.0 - 31st May, 2025 =

This release will most likely be the last update released on the WordPress plugin directory with anything NEW added.

### What's New?

* REST API: Products can now be filtered to return only products by brand names.

### Bug Fix

* REST API: Added missing option for allowing to order products by random. [Solves issue #516](https://github.com/co-cart/co-cart/issues/516)

### Plugin Details

* Plugin: Updated links for documentation.
* WordPress Dashboard: Updated link for upgrade page.
* WordPress Dashboard: Plugin action links are added after now, not before.

### Compatibility

* Tested with WooCommerce v9.9

= v4.4.0 - 16th May, 2025 =

This release focuses on supporting such tools like ManageWP, MainWP, Blogvault etc.

### Changes

* WordPress Dashboard: Database updates now run automatically if needed. [Resolves issue #511](https://github.com/co-cart/co-cart/issues/511)
* WordPress Dashboard: Sessions now transfer automatically for new installs.
* Session: Cart session expiration's are now matching the default expiration WooCommerce set for better compatibility and abandoned cart support.
* Session: Cart session expiration for logged in users renew daily and expire in a week. This is to keep carts persistent for logged in users.

> Note: The session expiration's can still be filtered back to the previous values but that would mean it would match the expiration for logged in users.

### Third Party Support

* Plugin: LiteSpeed Cache will now exclude CoCart from being cached. [Commit](https://github.com/co-cart/co-cart/commit/683b4d31b940862b463e2e1a45c8c3c9908a5f47)

### Developers

* Filter `cocart_cart_expiring` added parameter `is_user_logged_in()` to allow the expiration for logged in users to be filtered.
* Filter `cocart_cart_expiration` added parameter `is_user_logged_in()` to allow the expiration for logged in users to be filtered.

### Internal

* Improved the logger. [Commit](https://github.com/co-cart/co-cart/commit/32ee652ababfe94a501ff6fd84bff1829c140bf8)
* Added logs for database update procedure. [Commit](https://github.com/co-cart/co-cart/commit/4bb641005ad01fab405e5ba0200407631e06115c)

### Deprecations

* Filter `cocart_log_entry_name` no longer used.
* Filter `cocart_log_entry_version` no longer used.
* Filter `cocart_log_entry_source` no longer used.
* Filter `cocart_setup_wizard_store_save_next_step_override` no longer used.

= v4.3.30 - 27th April, 2025 =

### Bug Fix

* WordPress Dashboard: Fix plugin update warning for core plugin. [Solves issue #506](https://github.com/co-cart/co-cart/issues/506)

### Improvement

* REST API: Variation attribute data is now sanitized. Labels are converted to names (e.g. Size to pa_size), and values are cleaned.

### Compatibility

* Tested with WordPress v6.8

= v4.3.29 - 10th April, 2025 =

### Bug Fix

* REST API: Package details would not return but showed fine in shipping meta.

### Improvements

* REST API: Optimized fetching the cart in all Cart API endpoints.
* REST API: Shipping now fully respects the shipping settings.

> Dev note: Meaning if you have requested that the customer provides the shipping address first before shipping is calculated, then no shipping methods will return until it's provided.

### Compatibility

* Tested with WooCommerce v9.8

= v4.3.28 - 6th April, 2025 =

### Bug Fix

* REST API: Fixed unidentified item key when adding grouped products.

= v4.3.27 - 3rd April, 2025 =

### Bug Fix

* REST API: Undone a change to fix any WooCommerce cookies from setting with the Cart API. Related to fixing persistent cart back in November last year.

= v4.3.26 - 1st April, 2025 =

### Bug Fix

* REST API: Fixed critical error when adding an item and asking to return the item details. [Solves issue #509](https://github.com/co-cart/co-cart/issues/509)

### Improvements

* REST API: Corrected and added missing schema information for Cart API v1.
* WordPress Dashboard: Tweaked plugin screen modal for listing untested plugins.

### Requirement change

* WordPress 6.3 is the new minimum version required.

= v4.3.25 - 17th March, 2025 =

### Bug Fixes

* Plugin: Failed to activate fully when network activated due to how admin notices where set. - [Bug Report](https://wordpress.org/support/topic/critical-error-upon-activate/)
* WordPress Dashboard: Plugin suggestions was not letting you press the "Install Now" button.

= v4.3.24 - 10th March, 2025 =

### Bug Fix

* Fixed a few typo's in the session handler.

### Improvements

* WordPress Dashboard: Improved detection of a suggested plugin hosted on WordPress dot ORG and from a third party.
* WP-CLI: Update command now asks for confirmation before proceeding.

= v4.3.23 - 3rd March, 2025 =

### Bug Fixes

* Authentication: Changed access for setting an authentication error from protected to public. Allowing other authenticators to not fail when an error does occur.
* WP-CLI: When updating the plugin, we don't need to include the install class again.

### Improvements

* Database: Simply modified the structure for columns that were `BIGINT UNSIGNED` to `bigint(20) unsigned`.
* Session handler: Guest carts will now have a prefix `t_` before the cart key provided. This matches with WooCommerce session handler where it maybe used by 3rd party plugins or web host configurations to identify if the session is for a guest user.

> Dev note: This affects only new guest sessions.

### Compatibility

* Tested with WooCommerce v9.7

= v4.3.22 - 26th February, 2025 =

### Corrections

* REST API: Schema corrections for cart endpoint.
* REST API: Schema corrections for items endpoint to match cart schema.

### For Developers

* Moved filter `cocart_get_customer_{field}` after value instead of using it only when there is no value returned for a customers field. Replace `{field}` with the section prefix followed by the field name. e.g. `billing_country`

> Developer note: This allows you to then alter values such as the billing country. See example.

`
add_filter( 'cocart_get_customer_billing_country', function( $value ) {
	if ( WC()->countries->country_exists( $value ) ) {
		return WC()->countries->get_countries()[ $value ];
	}

	return $value;
}, 10, 1);
`

* Introduced new filter `cocart_get_after_customer_{field-type}_fields` that allows you to change the customer fields after they returned. Replace `{field-type}` with either `billing` or `shipping` for the fields to alter.

= v4.3.21 - 20th February, 2025 =

### Improvement

* REST API: Added `no-store` as part of the `Cache-Control` header for guest users.

= v4.3.20 - 8th February, 2025 =

### Bug Fix

* REST API: Fixed product reviews not returning.

= v4.3.19 - 6th February, 2025 =

### Bug Fix

* REST API: Fixed setting a customers shipping address line 1 and 2.

= v4.3.18 - 22nd January, 2025 =

### General

* Updated link to Next Changelog for coming future major release. (v5.0)
* Improved SASS to CSS conversion.

### Compatibility

* Tested with WooCommerce v9.6

= v4.3.17 - 14th January, 2025 =

### Bug Fix

* REST API: Stock status was incorrectly queried for Products API and now checks available stock statuses before filtering.

### Improvements

* REST API: Version of CoCart only returns in the returned headers when debug is enabled now.
* REST API: `WP_DEBUG` is made sure it is defined before returning extras for developers in the store response.

### Compatibility

* Tested with WooCommerce v9.5

[View the full changelog here](https://github.com/co-cart/co-cart/blob/trunk/CHANGELOG.md).

== Security Policy ==

Full details of the CoCart Security Policy can be found on [cocartapi.com/security-policy/](https://cocartapi.com/security-policy/).

The community edition will **no longer get further updates** to optimize or add anything new except security patches (if necessary).

Want to stay ahead of security updates, unlock the latest features, and get priority support? **[Upgrade to CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=securitypolicy)** — actively maintained, regularly updated, and backed by dedicated support.

= Supported Versions =

| Version | Supported |
|---------| --------- |
| 4.9.x   | Yes       |
| 4.8.x   | Yes       |
| 4.7.x   | Yes       |
| 4.6.x   | Yes       |
| 4.5.x   | Yes       |
| 4.4.x   | Yes       |
| 4.3.x   | No        |
| 4.2.x   | No        |
| 4.1.x   | No        |
| 4.0.x   | No        |
| < 4.0.0 | No        |

== Upgrade Notice ==

= 4.9.0 =

PHP version 8.2 is the minimum requirement to install and use CoCart.