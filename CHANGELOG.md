# Changelog for CoCart

## v4.2.0 - 11th July, 2024

In this release we have optimized our backwards compatibility with the session handler. As our session handler has to accommodate both native and headless support we originally forked the session handler to see what we needed to keep everything functional without breaking the core of WooCommerce. Now we have reviewed and noted down the changes made over time and we are happy to provide a refreshed version of our session handler that now provides only what we need while leaving everything else in the original session handler alone. Meaning now our session handler extends the WooCommerce session handler, making this release more compatibility with third party plugins and the new WooCommerce cart and checkout blocks.

We also no longer use cookies as a backup for headless. This should also help with the confusion of needing to pass along the session cookie or reading the cookie to extract the cart key and help with user switching much better. A cart key is provided in both the cart response and returned headers. Saving the cart key in your own cookie or local storage is fairly straight forward.

> We advise that you update on staging or local to check if you have used any of our experimental functions and filters that were added to the session handler to see if any have been deprecated. You can also see the list of deprecations below. If you have any questions about this update please contact us.

### What's New?

* Can now request a cart session via a requested header `cocart-api-cart-key`.

### Improvements

* Improved session handling for headless.
* Reverted back to WooCommerce cookie name which also deprecates filter `cocart_cookie`.
* Moved `is_rest_api_request()` function to the main class so it can be utilized outside of the plugin.
* Session Handler: Added new function `is_user_customer()` to check the user role is a customer when authenticated before migrating cart from guest.
* REST API: Updating the customer details in cart will now take additional billing and shipping fields as meta data. Validation is required by the developer using filter `cocart_update_customer_fields`.
* REST API: Sanitized and formatted customer email address and phone number.
* REST API: Formatted customer postcode if validated.
* REST API: Product image sizes are now fetched using utility products class function `get_product_image_sizes()`. Cuts down on the filter `cocart_products_image_sizes` being in multiple places.
* REST API: Currency in cart API v2 now returns `currency_symbol_pos` and the currency symbol will now return based on the set currency without lookup.
* REST API: Improved headers returned and added nocache headers on authenticated requests.
* REST API: Simplified sending the cart key to the header.
* REST API: Loading of the REST API optimized.
* Plugin: Localization improvements.
* Plugin: Code files organized better.
* Plugin: Updated plugin review notice.

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
* Added utility check for coupon exists.

### Compatibility

* Tested with WooCommerce v9.0

## v4.1.1 - 14th June, 2024

### Bug Fix

* Uncaught error with no featured image for a variation of a variable product. [Solves issue 416](https://github.com/co-cart/co-cart/issues/416)

## v4.1.0 - 6th June, 2024

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

## v4.0.2 - 17th May, 2024

### Bug Fixes

* REST API: Reverted a change that broke the ability to clear the cart. Was falsely notify it cleared when it did not.
* WordPress Dashboard: WooCommerce System Status was not echoing CoCart tips correctly.

### Improvements

* REST API: Products API, Schema, added properties for reviews section in both API v1 and API v2.

## v4.0.1 - 15th May, 2024

### Bug Fix

* REST API: Class `ReserveStock` not found when adding products to cart.

> Developer note: A line was unintentionally removed that calls the class for use.

## v4.0.0 - 13th May, 2024

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
* REST API: Cart schema tweaks for API v2.
* REST API: Cart and Product schema are now cached for performance for API v2.
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

## v3.12.0 - 18th March, 2024

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

## v3.11.2 - 1st March, 2024

### Bug Fix

* Fixed: PHP warning for `array_values()` when filtering the fields to return for the Cart API.

### Improvement

* Corrected a spelling error with plugin review notice.

## v3.11.1 - 27th February, 2024

### Bug Fix

* Fixed: Passing arguments for `cocart_do_deprecated_filter` incorrectly.

## v3.11.0 - 23rd February, 2024

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

## v3.10.9 - 22nd February, 2024

### Improvements

* Uses less memory.
* Sends headers with `send_headers()` instead of `header()`

### Compatibility

* Removed "DONOTCACHEPAGE" constant.

## v3.10.8 - 21st February, 2024

### Improvements

* WordPress Dashboard: Plugin suggestions now returns results much better the first time they are viewed.
* Store API: Only returns the version of the plugin, routes and link to documentation if "WP_DEBUG" is true.
* REST API: Deprecated action hooks and filters return messages if actually triggered.

### Compatibility

* Tested up to PHP v8.3.0

## v3.10.7 - 20th February, 2024

### For Developers

* Introduced new filter `cocart_products_get_related_products_exclude_ids` to exclude products from related products.

### Improvements

* Added missing arguments for Products API when viewing the OPTIONS.

### Bug Fix

* Fixed: Calling `retry()` non-static for plugin suggestions when searching.

### Compatibility

* Tested with WooCommerce v8.6

## v3.10.6 - 15th February, 2024

### Bug Fix

* Fixed: `$old_cart_key` is undefined in session handler.

## v3.10.5 - 8th February, 2024

### Bug Fix

* Fixed: Blank submenu pages from registering when activated on a multisite.

## v3.10.4 - 31st January, 2024

### What's New?

* Added support for CoCart Plus.
* Updated WooCommerce Notes.

### Bug Fixes

* Fixed: PHP Deprecation for implicit conversion from float to int.
* Fixed: PHP Deprecation for creation of dynamic property.

### Compatibility

* Tested up to PHP v8.2.10

## v3.10.3 - 19th January, 2024

### Bug Fix

* Fixed: Fatal error with `version_compare()` when activating the plugin with the [TaxJar plugin](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) active with PHP 8.1 or plus.

## v3.10.2 - 15th January, 2024

### What's new?

* Removed the need to disable cookie authentication check since it's ignored anyway.

### Bug Fixes

* Fixed a typo in the Setup Wizard.
* Products API - Default Catalogue Visibility parameter was missing.

### Compatibility

* Tested with WooCommerce v8.5

## v3.10.1 - 20th December, 2023

Forgot to update WordPress tested up to tag and a little CSS tweak.

## v3.10.0 - 19th December, 2023

### What's New?

* Added WordPress Playground notice. [commit 912ebb2](https://github.com/co-cart/co-cart/commit/912ebb24cf096de12aaa6c2aeeab9c59bf4dff5a)
* Added new admin support page. [commit 2f64980](https://github.com/co-cart/co-cart/commit/2f649804f1be685eba07a6afbeeaa08f7a28acc4)
* Added new help tab available on any CoCart admin page. [commit 9970ce8](https://github.com/co-cart/co-cart/commit/9970ce86afb8bd6a9ba4019eb7cadb9a96c00992)
* Filtered the WordPress REST API Index to hide CoCart namespaces and routes unless you have debug enabled. This helps a little with anyone trying to lookup what REST API's you have outside your store setup. [commit 45723d9](https://github.com/co-cart/co-cart/commit/45723d97422b498dbf8c252ea95c0b5029f7e437)
* Updated license.txt [commit f6b0acb](https://github.com/co-cart/co-cart/commit/f6b0acb07190bd45d6e8b8371a78a2f1102e4dba)

## Bug Fixes

* Fixed undefined `cart_cached` if price change feature not used. [commit fb472fc](https://github.com/co-cart/co-cart/commit/fb472fc6bb5b1d87eaf46d724207458e0e00e045)
* Fixed Authentication failing to identify current user if authentication is not provided. [commit f6fb7a4](https://github.com/co-cart/co-cart/commit/f6fb7a4eb809a80144e75c33b4cc35435663661d)
* Fixed PHP Deprecated: str_replace(): Passing null to parameter 2 (PHP 8.1) [commit 4ebeafd](https://github.com/co-cart/co-cart/commit/4ebeafdb4910248eef16b11bf50c44d019549c89)

## Improvements

* Setup Wizard no longer blocks access to the WordPress dashboard. [commit 00a158e](https://github.com/co-cart/co-cart/commit/00a158ee038550f2d24bdef6184fdc97f203ecc1)
* Moved validation earlier to check if we are on a CoCart page before displaying admin notices.
* Updated from product name to business name in Setup Wizard page. [commit eede727](https://github.com/co-cart/co-cart/commit/eede727188784d373346d992d164d07591b6bd67)
* Removed link to deprecated project. [commit ab973ca](https://github.com/co-cart/co-cart/commit/ab973ca5591bc7bb3d699ed0d62f48683ffd47d1)
* Improved the explanation of "Multiple Domain" option in Setup Wizard. [commit e54e31b](https://github.com/co-cart/co-cart/commit/e54e31b5aef0475afcd365fadc934ec2d9fc7100)
* Rewrote the admin menu system for a much better page management. [commit 543642e](https://github.com/co-cart/co-cart/commit/543642eba06c7f27a2766025c5395a97556555fb)
* Simplified the admin notices when the database requires updating and has updated with a dismissible action. [commit 8d995e0](https://github.com/co-cart/co-cart/commit/8d995e05ce86fc0df5c0fe54521febed094e778d)
* When database has updated, the notice is unset. This prevents the admin notice from showing again even without dismissing the admin notice first on next page load. [commit c90e320](https://github.com/co-cart/co-cart/commit/c90e320581616cedfa43833b27fbbb054fd3c918)
* There will be no sessions retrieved while WordPress setup is due. [commit fce7910](https://github.com/co-cart/co-cart/commit/fce7910d8849633dc6e55acaa005f3e43d0d4646)

## Deprecations

* Removed "Getting Started" page. [commit ea397e4](https://github.com/co-cart/co-cart/commit/ea397e4bf5a7ec8f1b59a73e5723185e9993b666)
* Removed "Upgrade" page. [commit 5f9f48c](https://github.com/co-cart/co-cart/commit/5f9f48c9d48fcef135013700f138aae53d98f96e)

#### Requirements and Compatibility

* Bumped PHP requirement to v7.4
* Tested with WordPress v6.4
* Tested with WooCommerce v8.4

## v3.9.0 - 2nd August, 2023

### What's New?

* Removed WooCommerce plugin headers to prevent incompatibility warning message when using "HPOS" feature.
* Updated "What's Coming Next?" link on plugins page to inform users about v4.0

### Bug Fix

* Fixed Products API where a product has no featured image or gallery images and is unable to determine the placeholder image. [Solves issue 12](https://github.com/co-cart/cocart-products-api/issues/12)

### Compatibility

* Tested with WooCommerce v7.9

## v3.8.2 - 12th July, 2023

### Bug Fix

* Fixed searching products by name.

### Compatibility

* Tested with WooCommerce v7.8
* Tested with WordPress v6.2

## v3.8.1 - 4th March, 2023

### What's New?

* Added the Authentication class as parameter to `cocart_authenticate` filter.
* Added `set_method()` function to authentication class.

### For Developers

Introduced a new filter `cocart_login_extras` to allow developers to extend the login response.

## v3.8.0 - 3rd March, 2023

### Compatibility

* Tested with WooCommerce v7.4

### For Developers

Introduced a new hook `cocart_cart_loaded` which could be used to trigger a webhook once a cart has been loaded from session.

## v3.7.11 - 16th January, 2023

### Enhancement

* Improved compatibility with PHP 8.1+

### Compatibility

* Tested with WooCommerce v7.3

## v3.7.10 - 30th December, 2022

### Compatibility

* Tested with WooCommerce v7.2
* Tested with WordPress v6.1

### Bug Fix

* Fixed viewing an individual session that has coupons.

## v3.7.9 - 4th November, 2022

### Bug Fixes

* Fixed item custom price not being applied from cart cache when loaded from session.
* Fixed a uncaught `array_merge()` fatal error where a **null** value was given instead of an **array**.

## v3.7.8 - 29th October, 2022

### Enhancements

* Improved getting request parameters for delete method endpoints.
* Reordered some filtering when passing data via parameters.

### Bug Fixes

* Fixed a undefined array key warning related to use of `apply_filters_deprecated`. Reported by [@douglasjohnson](https://profiles.wordpress.org/douglasjohnson/) [Bug Report](https://wordpress.org/support/topic/undefined-array-key-warning-realted-to-use-of-apply_filters_deprecated/)
* Fixed a fatal error when returning removed items that no longer exists. Now it's removed from the cart completely should the item not be found. Reported by [@antondrob2](https://profiles.wordpress.org/antondrob2/) [Bug Report](https://wordpress.org/support/topic/php-fatal-error-uncaught-error-17/)

### Compatibility

* Tested with WooCommerce v7.0

## v3.7.7 - 20th October, 2022

### Enhancement

* Moved item validation further up to identify sooner if the product no longer exists when attempting to update an item's quantity. [issue #356](https://github.com/co-cart/co-cart/issues/356)

## v3.7.6 - 23rd September, 2022

### Bug Fixes

* Fixed an issue were on a rare occasion, the product data is somehow not there when updating an item in cart. [issue #355](https://github.com/co-cart/co-cart/issues/355)
* Fixed an issue were you add more than one item to the cart with a custom price and then increase the quantity of one of those items after. All other items with a custom price would reset to the original price.

## v3.7.5 - 14th September, 2022

### Bug Fixes

* Fixed undefined value for querying products via review ratings.
* Fixed issue with identifying screen ID when using the "Setup Wizard" with WooCommerce 6.9+

### Compatibility

* Tested with WooCommerce v6.9

## v3.7.4 - 13th July, 2022

This minor release is related to Yoast SEO support.

### Tweaks

* Unlocked a change made in **v3.4.0** by un-registering the rest field `yoast_head` for the Products API.

Originally it was to keep the JSON response valid because a bug at the time was causing the response to not return correctly. It was also to increase the performance of the response as Yoast SEO returns the same data twice just in a different format. Now the issue appears to be gone and recent feedback suggested this should be left on by default.

Other improvements for supporting third party plugins are in the works.

If you want to discuss supporting a third party plugin, [start a discussion](https://github.com/co-cart/co-cart/discussions) on the CoCart GitHub repository.

## v3.7.3 - 23rd June, 2022

### What's New

* Added `get_session_data()` function to the session handler. Some plugins appear to be accessing it (though I don't recommend it).

## v3.7.2 - 20th June, 2022

### Improvements

* Adjusted WooCommerce detection when installing CoCart on a completely fresh WordPress install. Related to [[issue #341](https://github.com/co-cart/co-cart/issues/341)]
* Removed "Turn off CoCart" button from admin notice as the plugin already deactivates if WooCommerce not detected.
* Prevent plugin action links from showing if CoCart is not active.

### Compatibility

* Tested with WooCommerce v6.6

## v3.7.1 - 13th June, 2022

### What's New

* 🚀 You can now limit the results set to products assigned a specific category or tag via their slug names instead of ID.

Example of limiting products via category and tag. `wp-json/cocart/v2/products/?category=accessories&tag=hats`

> There was some confusion with this as the documentation said (query by ID) but the API schema said (query by slug). Now you can do either. This adjustment affects both API versions.

## v3.7.0 - 31st May, 2022

### What's New

* Improved: CoCart does not proceed with any installation when activated unless WooCommerce is active first. Solves [[issue #341](https://github.com/co-cart/co-cart/issues/341)]

### Compatibility

* Tested with WooCommerce v6.5
* Tested with WordPress v6.0

## v3.6.3 - 11th May, 2022

**🔥 This is a HOTFIX!**

### Bug Fix

* Undone change made to `cocart_prepare_money_response()` function. Another WC extension using the filter `cocart_cart_item_price` confused me and was overriding the format returned.

> This reverts partially back to v3.6.1

## v3.6.2 - 10th May, 2022

### Improvements

* Improved `cocart_prepare_money_response()` function. Cleans up string values better.
* Additional decimals gone for item price.

### Tweaks

* Item price and subtotal now returns correct money response.

## v3.6.1 - 6th May, 2022

### Bug Fixes

* Fixed calling `update_plugin_suggestions()` function the non-static method. For WordPress Dashboard > Plugins > Add New.
* Fixed undefined `$variations` for `get_variations()` function. For Products API v2 thanks to [@jnz31](https://github.com/jnz31)
* Improved `get_connected_products()` function to validate product ID's before returning. For Products API v2. Solves [[issue #336](https://github.com/co-cart/co-cart/issues/336)]

## v3.6.0 - 24th April, 2022

### What's New?

* Added support to prevent CoCart from being cached with [WP Super Cache](https://wordpress.org/plugins/wp-super-cache/) plugin.
* Added support to prevent CoCart from being cached with specific web hosts like [Pantheon](https://pantheon.io/docs/cache-control).

### For Developers

* Introduced new filter `cocart_send_cache_control_patterns` that allows you to control which routes will not be cached in the browser.

## v3.5.0 - 21st April, 2022

### What's New?

* Improved: Plugin suggestions now fetches data from a JSON file and is cached once a week.
* Tweak: Quality of life update for Cart API v1. Should item added to cart not have an image it will fallback to the placeholder image.

### Compatibility

* Tested with WooCommerce v6.4

### Bug Fix

* Fixed Products API v2 Schema for Images.

> Related to a change made in v3.2.0

## v3.4.1 - 4th April, 2022

### Bug Fix

* Fixed: An uncaught undefined function `add_meta_query` which allows you to query products by meta. Thanks to [@jnz31](https://wordpress.org/support/topic/uncaught-error-call-to-undefined-method-cocart_products_v2_controlleradd_meta/) for reporting the error.

> Dev note: I'm an idiot for not finding this issue sooner. The function `add_meta_query` was not committed when the products API add-on was merged with the core of CoCart. 🤦‍♂️ Please accept my apologies for the issue caused. 🙏

### Deprecated & Replacement

* Deprecated use of `wc_get_min_max_price_meta_query` function. Although it was *deprecated* in WooCommerce since **v3.6** there was never a replacement provided and it was still working. Now the function has just been copied into a new function `cocart_get_min_max_price_meta_query` and will no longer provide the debug warning. It can be improved in the future if needed.

## v3.4.0 - 28th March, 2022

### What's New?

* Tweak: Unregistered rest field `yoast_head` from the Products API to keep the JSON response valid and increase performance.

> The rest field `yoast_head_json` still remains.

## v3.3.0 - 24th March, 2022

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

## v3.2.0 - 17th March, 2022

### What's New?

* Enhancement: Moved products array to it's own object and returned pagination information in the response. - **Products API v2 ONLY!**

> Dev note: A small break but a good one thanks to the feedback from **[Alberto Abruzzo](https://github.com/AlbertoAbruzzo)**. This only affects when accessing all products with or without arguments set. Just need to access the array of products from an object not just from the response. What's also great about this enhancement is that any arguments set will also be appended to the pagination links making it easy for developers.

### Bug Fix

* Fixed: Plugin review notice reappearing even after it has been dismissed.

### Deprecated

* Support for WooCommerce less than version 4.8 or legacy versions of WooCommerce Admin before it was packaged with the core of WooCommerce.

### Enhancement

* Better detection of WooCommerce Admin. Now checks if the feature is enabled.

### For Developers

* Introduced new filter `cocart_prevent_wc_admin_note_created` to prevent WooCommerce Admin notes from being created.

## v3.1.2 - 10th March, 2022

### Bug Fixes

* Fixed an Undefined index: Items for shipping packages in the cart response. Caused the JSON response to not return valid even if the response status was `200`.
* Fixed a fatal error. Uncaught Error: Class `CoCart_Session_Handler` when cron job runs in the background.
* Fixed Yoda conditions.
* Removed calculating totals once cart has been loaded from session as it caused the cart not to show.

### Tweaks

* Cleaning up expired carts function has changed to a global task function. This also fixes the cron job error mentioned above.
* Added more translation notes to clarify meaning of placeholders.

## v3.1.1 - 2nd March, 2022

**🔥 This is a HOTFIX!**

### Bug Fix

* When updating an individual item in cart, the product data is not passed when validating the quantity and was causing a fatal error. [[issue #319](https://github.com/co-cart/co-cart/issues/319)]

> Developer note: This is because an improvement was made when adding items to the cart using the same function that is used to validate the quantity and I forgot to update the parameters for when it's used to update an item. My bad.

## v3.1.0 - 28th February, 2022

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
* Tested with WooCommerce v6.2
* Tested with WordPress v5.9

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

## v3.0.17 - 3rd December, 2021

### Bug Fixes

* Unable to remove items due to validation issue for certain edge cases. Reported by [Rozaliya Stoilova](https://github.com/rozalia) [Issue 287](https://github.com/co-cart/co-cart/issues/287)
* Uncaught Error: Call to undefined function `get_current_screen()`. Reported by [Tommie Lagerroos](https://github.com/lagerroos) for [Frontity](https://frontity.org/) compatibility.
* Loading of RTL stylesheet if `SCRIPT_DEBUG` is not enabled.

### Improvements

* Getting a single item with `cart/item` route now includes the `cart` route parameters so you can use all available.
* Validation of item key used to remove, update or restore an item.
* Weight does not forcefully round up the value. Shows the correct weight based on the quantity of item in cart. The weight is normalised unifying to "kg" then converted to the wanted unit set by the store settings. Reported by Miguel Peixe Aldeias.

### Tweaks

* Moved `backorders` and `cart_item_data` into the `get_item()` function instead so it returns data when `return_item` is set to true. Data was missing as it was outside this function. Reduced duplicate code in the process. 👍 Issue reported by [Christian Grosskop](https://github.com/fatheaddrummer) [Issue 288](https://github.com/co-cart/co-cart/issues/288)
* The `cart/item` route now extends the `cart` route for better code management.
* Filter `cocart_cart_item_key_required_message` now passes the correct status for the second parameter.

### Compatibility

* Tested with WooCommerce v5.9

### For Developers

* Introduced new filter `cocart_quantity_minimum_requirement` to specify minimum quantity requirement if not `1`.

## v3.0.16 - 15th November, 2021

> 📢 This release is broken. Please DO NOT use it.

* Fixed: Loading of RTL stylesheet if SCRIPT_DEBUG is not enabled.
* Fixed: Can no longer remove item by updating quantity to zero.
* Fixed: Returning error responses when updating an item fails.

### Compatibility

* Tested with WooCommerce v5.9

## v3.0.15 - 8th November, 2021

### What's New?

* Added: Recommended requirements to the installation section of the readme.txt file.
* Added: Support for RTL.

### Bug Fixes

* Fixed: Undefined function for `wp_get_environment_type()` introduced in WordPress 5.5 should the site be running a lower version of WordPress. Reported by [Mohib Salahuddin Ayubi](https://profiles.wordpress.org/mohib007/).
* Fixed: JS bug identifying the parent node for plugin suggestions page.

### Compatibility

* Tested with WooCommerce v5.8

### Recommended Requirements

* WordPress v5.6 or higher.
* WooCommerce v5.2 or higher.
* PHP v7.4

## v3.0.14 - 18th October, 2021

### Bug Fix

* Fixed: Undefined index: `cart` that rarely happens. Reported by [@AlceoMazza](https://github.com/AlceoMazza)

## v3.0.13 - 15th October, 2021

**🔥 This is a HOTFIX!**

* Fixed: Fatal error when `$session->save_data()` is called in [JetPack WooCommerce Analytics](https://jetpack.com/support/woocommerce-analytics/) and [WooCommerce Amazon Pay](https://wordpress.org/plugins/woocommerce-gateway-amazon-payments-advanced/) payment gateway.

## v3.0.12 - 2nd August, 2021

* Fixed: Validate item quantity by passing missing parameters.
* Fixed: Default package title for [WooCommerce Advanced Shipping Packages](https://woocommerce.com/products/woocommerce-advanced-shipping-packages/) extension.
* Dev: Improved performance in the WordPress dashboard.

## v3.0.11 - 29th July, 2021

* Fixed: Product ID not returning as integer once validated instead of a string. 🙈 Thanks to [Christian Kormos](https://profiles.wordpress.org/darkchris/) for reporting the issue.

> Dev note: This will help solve issues with filters using the `$product_id` parameter when a product is added to cart.

## v3.0.10 - 22nd July, 2021

* Dev: Stopped custom upgrade notice from being called on plugin page when major update is available for those using WordPress 5.5 or greater. [See article](https://make.wordpress.org/plugins/2021/01/26/reminder-plugins-must-not-interfere-with-updates/)!
* Dev: Package file added for better composer packaging support.

## v3.0.9 - 21st July, 2021

* Corrected: Sanitize only. Some functions were escaped when not needed to.

## v3.0.8 - 18th July, 2021

* Dev: Plugin package tweaks for future CoCart project.

## v3.0.7 - 14th July, 2021

**🔒 This is a SECURITY FIX!**

> This release brings in a number of fixes to secure the plugin and keep up with WordPress code standards. It is highly recommended that you update to this release.

* Fixed: Escaping HTML from requested cart key.
* Fixed: Sanitized username and password with basic authentication.
* Fixed: Yoda conditions.
* Fixed: Localization for translators.
* Updated: Getting started page with new lowest price to upgrade to CoCart Pro.
* Dev: Documented parameter comments for functions that were missing.
* Dev: Change the use of `date()` function to `gmdate()` function instead.
* Dev: Change the use of `strip_tags()` function to `wp_strip_all_tags()` function instead.

## v3.0.6 - 25th June, 2021

* Fixed: Validation of a variation added to cart should the parent ID be used. Thanks to [Brandan King](https://profiles.wordpress.org/inspiredagency/) for reporting the issue.

## v3.0.5 - 28th May, 2021

* Tweaked: Adding an item now includes the cart parameters so things like the featured image can return if left as the default setting instead of not showing at all when not set.

## v3.0.4 - 19th May, 2021

* Fixed: Return error if no matching variation is found.
* Fixed: Validation of empty totals forcing false error message to return.
* Fixed: Empty `backorders` and `featured_image` now returns in cart response. Keeps cart response structure consistent.
* Fixed: Coupon HTML formatting returned in cart response.
* Fixed: Error response when attempting to view a single item that is not in the cart.
* Tweaked: When items in cart are checked for remaining stock, only the first error notice is returned per item.
* Tweaked: Updating, Removing or Restoring an item now includes the cart parameters so things like the featured image can return if left as the default setting instead of not showing at all when not set.
* Tweaked: Localization for validation error messages.
* Tweaked: `cocart_price_no_html()` function to decode HTML so currency symbol returns correctly.

## v3.0.3 - 15th May, 2021

* Fixed: Cart hash now returns in cart response after adding first item.
* Dev: Code tweaks and inline doc improvements.
* Dev: Added new helper function to detect CoCart Pro is activated.

## v3.0.2 - 12th May, 2021

**🔥 This is a HOTFIX!**

* Fixed: Error when product image is not set. Now fails safely by returning the product placeholder image instead.
* Fixed: Totals total returned value was returning the currency symbol decoded for certain countries so it looked like the total was an incorrect value.
* Tweaked: Convert monetary values given before returning.

## v3.0.1 - 12th May, 2021

**🔥 This is a HOTFIX!**

* Fixed: Uncaught error when the cart hash is not generated and saved in session cookie.
* Fixed: Some validation errors not returning when attempting to add item to cart.
* Tweaked: When adding an item and fails. Any remaining error notice that WooCommerce normally returns on the frontend is converted to throw an exception.

## v3.0.0 - 10th May, 2021

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
* Tested with WooCommerce v5.3
* Dev: 🐸 **NEW** Update database manually for CoCart via WP-CLI.
* Dev: 🐸 **NEW** Get the current version of CoCart via WP-CLI.
* Dev: Forked `get_customer_unique_id()` from WooCommerce session handler for backwards compatibility. Introduced to help with unit tests in WooCommerce since version 5.3. Not needed for CoCart.

### For developers

This major release brings a lot more support for developers including those who create extensions for WooCommerce.

* Dev: ☄️ New shared functions that can be used to develop your own extension to CoCart or support CoCart.
* Dev: Introduced `cocart_cart_source` filter for filtering the source of the cart created. Default is `cocart` if created via **CoCart** else `woocommerce` if created via **WooCommerce**.
* Dev: Introduced `cocart_store_index` filter for filtering the API store index data.
* Dev: Introduced `cocart_store_address` filter for filtering the store address.
* Dev: Introduced `cocart_routes` filter for filtering the CoCart routes returned.
* Dev: Introduced `cocart_filter_request_data` filter for filtering additional requested data including file uploads when adding an item\s.
* Dev: Introduced `cocart_before_get_cart` filter for modifying the cart data in any capcity before the cart response is returned.
* Dev: Introduced `cocart_cart_item_data` filter allows you to filter any additional cart item data returned when getting the cart items.
* Dev: Introduced `cocart_shipping_package_details_array` filter for filtering package details listed per package.
* Dev: Introduced `cocart_shipping_package_name` filter for renaming the package name.
* Dev: Introduced `cocart_cart` filter for modifying the cart response in any capacity.

----

The following filters are for returning cross sells in the cart.

* Dev: Introduced `cocart_cross_sells_orderby` filter for filtering the orderby in which cross sells return.
* Dev: Introduced `cocart_cross_sells_order` filter for filtering the order in which cross sells return.
* Dev: Introduced `cocart_cross_sells_total` filter for filtering the total amount of cross sells to return.

----

The following affect adding simple or variable products to the cart should a WooCommerce extension validate products by form post only.

By setting this filter `cocart_skip_woocommerce_item_validation` to true, products will be added to the cart without fault as we have already passed validation within CoCart.

#### Filters

* Dev: Introduced `cocart_skip_woocommerce_item_validation` filter allows you to add the item to the cart without validating the item again using WooCommerce internal functions.
* Dev: Introduced `cocart_add_cart_item` filter matches `woocommerce_add_cart_item` filter.
* Dev: Introduced `cocart_cart_contents_changed` filter matches `woocommerce_cart_contents_changed` filter.

#### Action Hooks

* Dev: Introduced `cocart_add_to_cart` action hook matches `woocommerce_add_to_cart` action hook.

----

The following filters affect adding bundled/grouped products to the cart.

* Dev: Introduced `cocart_add_items_to_cart_handler` filter allows you to set the product type so the correct add to cart handler for bundled/grouped products is used.
* Dev: Introduced `cocart_add_items_to_cart_handler_{product-type}` filter allows you to introduce your own add to cart handler for bundled/grouped products.

----

The following filters match filters used in WooCommerce templates for manipulating what is displayed in the cart. Parameters are equally the same so returning the same results is easy.

* Dev: Introduced `cocart_cart_item_name` filter matches `woocommerce_cart_item_name`.
* Dev: Introduced `cocart_cart_item_title` filter allows you to change the product title. The title normally returns the same as the product name but variable products return the title differently.
* Dev: Introduced `cocart_cart_item_price` filter matches `woocommerce_cart_item_price`.
* Dev: Introduced `cocart_cart_item_quantity` filter matches `woocommerce_cart_item_quantity`.
* Dev: Introduced `cocart_cart_item_subtotal` filter matches `woocommerce_cart_item_subtotal`.

----

The following filters are for checking if a user has permission to use a route assigned to the method.

* Dev: Introduced `cocart_api_permission_check_get` filter allows you to block use of any API route that uses the **GET** method.
* Dev: Introduced `cocart_api_permission_check_post` filter allows you to block use of any API route that uses the **POST** method.
* Dev: Introduced `cocart_api_permission_check_put` filter allows you to block use of any API route that uses the **PUT** method.
* Dev: Introduced `cocart_api_permission_check_delete` filter allows you to block use of any API route that uses the **DELETE** method.
* Dev: Introduced `cocart_api_permission_check_options` filter allows you to block use of any API route that uses the **OPTION** method.

----

The following filters affect how CoCart operates.

* Dev: Introduced `cocart_show_plugin_search` filter allows you to disable the plugin search suggestions.
* Dev: Introduced `cocart_enable_auto_update_db` filter allows you to have the database automatically update when CoCart provides an update.
* Dev: Introduced `cocart_disable_load_cart` filter allows you to disable "Load Cart from Session" feature.
* Dev: Introduced `cocart_rest_api_get_rest_namespaces` filter allows you to decide which namespaces load.
* Dev: Introduced `cocart_upload_dir` filter allows you to change where files are uploaded using CoCart.
* Dev: Introduced `cocart_show_admin_notice` filter allows you to disable all CoCart admin notices. - _Please know that doing so will prevent any manual update actions required if disabled, unless you have `cocart_enable_auto_update_db` enabled._
* Dev: Introduced `cocart_wc_navigation` filter to move back CoCart to WordPress admin navigation instead of WooCommerce admin navigation.

----

The following filters match filters used in WooCommerce add to cart success notice once product is added to cart. Parameters are equally the same so returning the same results is easy.

* Dev: Introduced `cocart_add_to_cart_qty_html` filter matches `woocommerce_add_to_cart_qty_html` allows you to change the quantity html returned.
* Dev: Introduced `cocart_add_to_cart_item_name_in_quotes` filter matches `woocommerce_add_to_cart_item_name_in_quotes` allows you to change the formatting of the item name in quotes.
* Dev: Introduced `cocart_add_to_cart_message_html` filter matches `wc_add_to_cart_message_html` allows you to change the add to cart message based on the products and quantity.

## v2.9.3 - 14th April, 2021

* Fixed: Cart not clearing when you specify a cart key. Set the cart empty instead of using WooCommerce internal function `WC()->cart->empty()` as it was not consistent.
* Dev: Introduced two new action hooks before and after clearing the cart. `cocart_before_cart_emptied` and `cocart_cart_emptied`.
* Tested with WooCommerce v5.2

## v2.9.2 - 31st March, 2021

**🔥 This is a HOTFIX!**

* Fixed: An issue with the `woocommerce_cart_hash` cookie setting to **null**.

> This was due to the last patch "v2.9.1" introducing the `samesite` attribute with the default value to "None". It is now set to "Lax" as the default value.

## v2.9.1 - 21st March, 2021

* Fixed: Merge guest customers cart with a registered customers cart once authenticated.
* Dev: Triggers saved cart after authentication and updates user activity.
* Dev: When cookie is set, it now adds `samesite` attribute to **None**. Introduces new filter `cocart_cookie_samesite` to override default option. - Requires **PHP 7.3** or above.

> Thanks to [Joel](https://github.com/joelpierre) for reporting the merge issue and help test it.

## v2.9.0 - 18th March, 2021

* **NEW**: Now supports multi-sites. 🥳
* Dev: **NEW** Added database version during install in preparation for updating to CoCart v3.
* Tested with WooCommerce v5.1
* Tested with WordPress v5.7

> 📢 Important patch in preparation for CoCart v3. If you have large stores and you update CoCart directly to v3 ignoring this patch, then the database upgrade will run automatically in the background without notice. So please update in order.

## v2.8.4 - 9th January, 2021

* Corrected: Action hook `cocart_item_quantity_changed` not being called after updating a cart item's quantity. Thanks to [@pauld8](https://github.com/pauld8)
* Enhanced: Cart now returns with filterable `WP_REST_Response` function instead.

## v2.8.3 - 20th December, 2020

* Enhanced: CoCart now welcomes users when the plugin is activated on a multi-site network.
* Fixed: Headers already sent when filtering `rest_pre_serve_request`. Appears to only cause issues when you have `WP_DEBUG` enabled locally.
* Fixed: A few errors were not returning the status error in the correct format.

> Last update for CoCart Lite v2 ❄️

## v2.8.2 - 16th December, 2020

* Enhanced: 📦 Load chosen shipping method when loading cart from session via the web.
* Enhanced: 📦 Load cart fees when loading cart from session via the web.
* Dev: **NEW** filter `cocart_load_cart_query_name` to allow developers add more white labelling when loading the cart via the web.
* Improved: `uninstall.php` to delete WooCommerce Admin notes when uninstalling for those who are using WooCommerce v4.8 or greater.

## v2.8.1 - 10th December, 2020

* Added: Support for Pantheon.io so it no longer caches for guest customers on the frontend and prevent the cart from appearing empty.

> This release introduces support for third party starting with web host.

## v2.8.0 - 9th December, 2020

* Enhanced: 📦 Load chosen shipping method when loading cart from session.
* Tested with WooCommerce v4.8 and WooCommerce Admin v1.7
* Tested with WordPress v5.6

### Minimum requirement changes

* WordPress now needs to be v5.3 minimum.
* WooCommerce now needs to be v4.3 minimum.

> Support for CoCart Lite will not be provided for sites running any lower than these minimum requirements.

## v2.7.4 - 18th November, 2020

* Enhanced: 🤯 **Access-Control-Expose-Headers** to allow `X-CoCart-API` to be exposed allowing frameworks like **React** to fetch them.
* Tested with WooCommerce v4.7

> This is a community release by [@saulable](https://github.com/saulable)

## v2.7.3 - 8th November, 2020

**🔥 This is a HOTFIX!**

* Fixed: Warning of missing domain key from `setcookie()` options. Thanks to [@pauld8](https://github.com/pauld8)
* Fixed: Carts already in session still updates even if the cart is now empty.

## v2.7.2 - 8th November, 2020

* Changed: Default expiration length for a cart to expire. Previously _30_ days, now only _7_ days.
* Enhanced: Use first gallery image for thumbnail if featured image is not set.
* Enhanced: Added back the use of _httponly_ when setting `cocart_setcookie` for the frontend. Defaults: true for frontend, false via REST API.
* Enhanced: Prevents variations that are not purchasable from passing validation when added to the cart should the variation ID not be set.
* Fixed: Logger was not passing the full log entry just the message.
* Improved: Variation ID now returns in cart response based on variation attributes if not set.
* Improved: Saving carts for guest customers in the database no longer creates additional empty carts. Only 1 cart per customer. 😄🎉
* Improved: WooCommerce System Status to show how many carts are going to expire soon and out of how many in session. ✅
* Dev: Deprecated filter `cocart_empty_cart_expiration`. Not needed any more.
* Dev: Info log for user switch no longer records if a user ID is not set.
* Dev: New filter `cocart_is_cart_data_valid` let's you validate the cart data before a cart is saved in session.
* Dev: New filter `cocart_set_cookie_options` allows you to filter the cookie flags, which enables setting of _samesite_. 🏁 - Only for those who use **PHP 7.3** or above.
* Dev: New filter `cocart_cookie_httponly` on the _httponly_ param on `cocart_setcookie()` with all the function parameters passed through.

> Certain links that take you to "cocart.xyz" or "docs.cocart.xyz" now contain basic site info. This includes the following: PHP Version, WordPress Version, WooCommerce Version, CoCart Version, Days plugin active, debug mode, memory limit, user language, multisite and environment type.

## v2.7.1 - 30th October, 2020

**🔥 This is a HOTFIX!**

* Fixed: Incorrect validation for `variation` and `cart_item_data` fields when adding a product to the cart.

## v2.7.0 - 27th October, 2020

* **NEW** - Added the cart key via the headers. Look for `X-CoCart-API`
* Enhanced: Variable validation by removing parameters not used.
* Enhanced: REST API parameters sanitized and validated.
* Fixed: Undefined class constant `E_WC_ADMIN_NOTE_MARKETING` for those who are using WooCommerce lower than version `4.3.0`. Thanks to [@dmchale](https://github.com/dmchale)
* Fixed: If stock not available when updating item, return error. Thanks to [@pauld8](https://github.com/pauld8)
* Fixed: Product ID validation should the ID not be present. Also returns correct product ID should SKU ID be used instead.
* Tested: Compatible with WooCommerce v4.6
* Dev: New filter `cocart_is_rest_api_request` to allow developers run their own API check-up.
* Dev: New filter `cocart_return_default_response` that if set to false will allow you to return a completely new filtered response using `cocart_****_response`. You replace `****` with the API route e.g: `cocart_cart_response` or `cart_add_item_response`.

> New response has been applied to all API routes excluding the following: `logout`, `count-items`, `totals`.
> Plugin name has been renamed from "CoCart" to "CoCart Lite".

## v2.6.3 - 23rd September, 2020

* Fixed: WooCommerce admin note for `6 things you can do with CoCart Products` not showing.
* Improved: Checking of admin note requirements before creating any notes.

## v2.6.2 - 16th September, 2020

> 📢 This minor release corrects some of the API error response codes misused. 👍

* Tweaked: Check for package version of CoCart instead of the version of CoCart Pro so users can install higher versions of the core of CoCart when CoCart Pro is active. This allows users to test pre-releases or newer versions when they become available. 😄
* Tested: Compatible with WooCommerce v4.5.2

## v2.6.1 - 9th September, 2020

* Enhanced: Plugin upgrade notice on the plugin page.
* Removed: Unused CSS.

## v2.6.0 - 8th September, 2020

* **NEW**: Added backwards compatibility for when `set_customer_session_cookie()` function is called.
* **NEW**: Site environment is now checked before plugin activates.
* **NEW**: Plugin will not activate if CoCart Pro _v1.1.0_ or above is installed.
* Enhanced: Plugin to be better optimized for future releases of WooCommerce and to allow it to be packaged with CoCart Pro so user's will not require CoCart Lite if Pro is installed.
* Fixed: Return product data if missing once item is added to cart to prevent undefined index.
* Improved: `uninstall.php` file and now will only clear plugin data and remove the database table if `COCART_REMOVE_ALL_DATA` constant is set to true in user's `wp-config.php`. This is to prevent data loss when deleting the plugin from the backend and to ensure only the site owner can perform this action.
* Improved: Handling of admin menu by moving to it's own class.
* Tweaked: Admin notices to not redirect only to the plugins page once notice is dismissed. Will redirect to the current admin page.
* Dev: Introduced the ability to white label the plugin. Meaning hide **CoCart** from the backend. (Admin menu, plugin links, plugin notices including WC admin inbox notices and WC system status information). All you have to do is set a constant `COCART_WHITE_LABEL` to true in user's `wp-config.php` file.
* Dev: New filter `cocart_authenticate` to override the determined user ID when authenticating. **NOTE** This will only be active if not already authenticated.
* Tested: Compatible with WooCommerce v4.5

## v2.5.1 - 18th August, 2020

* Fixed: **'Access-Control-Allow-Origin'** header response when it request's credentials with a wildcard `(*)` value.
* Dev: Added filter `cocart_allow_origin` to set the origin header for added layer of security when you go into production.
* Tested: Compatible with WooCommerce v4.4

> This is a community release by [@mattdabell](https://github.com/mattdabell)

## v2.5.0 - 10th August, 2020

This minor release adds support for the recent changes made to the REST API in the coming WordPress 5.5 to allow CoCart to still work as it is a public REST API.

No changes have been made to the API it self. Just made sure that each route has the correct permission call back applied.

If you are still using the legacy API and have updated to WordPress 5.5, then you will no longer be able to use it as it will no longer register.

## v2.4.0 - 23rd July, 2020

The code base was improved to prevent errors should WooCommerce not be activated while CoCart still is.

* **NEW**: Added another note to remind users that they can activate CoCart Pro if installed but not activated.
* Fixed: Fatal error for `WC_Session` class if WooCommerce is disabled but CoCart was not.
* Improved: Newly added notes for WooCommerce Admin inbox.
* Tweaked: WooCommerce System Tools to hide options to clear WooCommerce sessions and synchronizes carts if table is empty.
* Tested: Compatible with WooCommerce v4.3.1
* Tested: Compatible with WordPress v5.5

## v2.3.1 - 18th July, 2020

* Corrected: Thank you note.
* Fixed: Installation/Update of CoCart.
* Fixed: Return of system status data.

## v2.3.0 - 14th July, 2020

This release brings an improved code base for the backend and connects with WooCommerce's Admin bar. New notes exclusively for CoCart have been created that are triggered for when the client needs them. This release also makes preparations for CoCart v3.0 and tested with WooCommerce v4.3

* **NEW**: Connected with WooCommerce Admin.
* **NEW**: Notes are provided for help, feedback and guides.
* Added: Preparations for CoCart v3.0
* Added: Plugin requirements to main plugin file header.
* Bumped: WooCommerce minimum requirement to v4.0
* De-bumped: PHP minimum requirement to v7.0 to match WooCommerce's current requirement.
* Tested: Compatible with WooCommerce v4.3
* Improved: Code base for the backend.

## v2.2.1 - 26th June, 2020

* Tweaked: Optimized load cart from session when checking if cart is in session. PR [#125](https://github.com/co-cart/co-cart/pull/125)

> This is a community release by [@yordivd](https://github.com/yordivd)

## v2.2.0 - 22nd June, 2020

* NEW: Support for allowing all cross origin header requests to pass. Requires `cocart_disable_all_cors` filter set to false to enable.
* NEW: Returned response after adding an item now returns product name, title and price just like the cart.
* Tweaked: Improved validation for a variable product to return the product name correctly if variation attributes are missing.
* Tweaked: Made sure that we check if we are making a request for CoCart API only.
* Tweaked: CoCart logger will only log if `WP_DEBUG` is also set true.
* Dev: New filters added for returning additional item data once added to cart.

## v2.1.7 - 16th June, 2020

* 🔥 Fix: Too few arguments to function `init_session_cookie()`, 0 passed.

## v2.1.6 - 13th June, 2020

* Fixed: Return of error response for invalid variation data.
* Disabled: Use of `sanitize_variation_data` function. Used for wrong purpose.

## v2.1.5 - 12th June, 2020

* 🔥 Fix: Filtered `nonce_user_logged_out` returned value for frontend that was causing users logged out to fail verification. 🤦‍♂

## v2.1.4 - 11th June, 2020

* 🔥 Fix: Call to undefined method `init_session_cookie()` in session handler causing checkout on the frontend to fail. 🤦‍♂

## v2.1.3 - 6th June, 2020

* Changed: Renamed `has_cart` function to `has_session` to prevent issues with other plugins that call `WC()->session->has_session()`.
* Tweaked: CoCart logger now checks if `WC_Logger` class exists, corrected `cocart_logging` filter and passed `$type` and `$plugin` variables.

## v2.1.2 (a.k.a the real version 2.1) - 4th June, 2020

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
* NEW: Prevent CoCart from being cached with [WP REST API Cache plugin](https://wordpress.org/plugins/wp-rest-api-cache/).
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
* Dev: Added filter `cocart_update_cart_validation` to allow plugin developers to pass their own validation before item is updated in the cart.
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
* Tested: Compatible with WooCommerce v4.2

> This update replaces WooCommerce core session handler with CoCart's. 100% backwards compatible.

## v2.1.1 - 10th May, 2020

**🔥 This is a HOTFIX!**

* Fixed: Critical uncaught error when returning the totals once calculated.
* Fixed: Critical uncaught error when uninstalling to drop the database table.

## v2.1.0 - 8th May, 2020

* NEW: Added support for guest customers.
* NEW: Carts in session are stored in a new database table.
* NEW: Added plugin details to **WooCommerce System Status**.
* NEW: Added `uninstall.php` file to delete table and options.
* NEW: Able to transfer a cart from your headless store to the web.
* NEW: Added handlers to improve product validation and extended support for other product types.
* NEW: Can now add items to cart using a products SKU ID.
* NEW: When an item is updated, removed or restored... the cart totals are re-calculated.
* NEW: Added option to logout customer.
* Removed: Parameter to re-calculate totals once item was updated.
* Tweaked: Products that are no longer purchasable and are already in the cart are removed from the cart.
* Tweaked: Stop redirect to getting started page if plugin was activated and was already installed before.
* Tweaked: Prevent redirect to getting started page if multiple plugins activated at once.
* Dev: Clear all carts stored in session via the Tools section of **WooCommerce System Status**.
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
* Tested: Compatible with WooCommerce v4.1

## v2.0.13 - 13th April, 2020

* Filtered: `woocommerce_stock_amount` to validate as a float value.
* Changed: Quantity value type from _integer_ to _float_ to allow quantity to be used for weighing fruit for example when adding or updating a product.
* Dev: Added filter for sold individual products quantity to be overridden. - `cocart_add_to_cart_sold_individually_quantity`

> This is a community release by [@metemaddar](https://github.com/metemaddar)

## v2.0.12 - 27th March, 2020

* Tested: Compatible with WordPress 5.4
* Added: Upgrade notice message on plugins upgrade screen.

## v2.0.11 - 25th March, 2020

* Removed: `cocart_docs_url` filter for changing documentation link.
* Tested: Compatible with WooCommerce v4.0.x
* Updated: Getting Started page and removed `cocart_getting_started_doc_url` filter for the documentation button.
* Updated: Plugin action link for upgrading to CoCart Pro.
* Updated: Upgrade notices.

> Please temporarily deactivate CoCart and CoCart Pro if you have it before updating WooCommerce to version 4.0+ as there is an activation order issue I am still working on fixing. Once you have upgraded WooCommerce simply reactivate CoCart.

## v2.0.10 - 22nd March, 2020

* Tweaked: Refresh totals parameter is now set to `true` by default when item is updated.

## v2.0.9 - 19th March, 2020

* Corrected: Passed parameter to get specific customers cart.
* Tweaked: Validation of returning persistent cart.

## v2.0.8 - 6th March, 2020

* Dev: Added filter `cocart_return_empty_cart` to empty cart response so developers can use it as they see fit.

## v2.0.7 - 5th March, 2020

* Disabled: Cookie authentication REST check, only if site is secure when authenticating the basic method.
* Removed: Filter for session class handler as we need it to be untouched.
* Tested: Compatible with WooCommerce v3.9.x
* Tweaked: Use `get_current_user_id()` instead of `is_user_logged_in()` to check if user is logged in.

> The cookie check is only disabled when making a request with CoCart.

## v2.0.6 - 1st October, 2019

* Added: Link to translate CoCart on the plugin row.
* Tweaked: Upgrade admin notice for next release.
* Tweaked: URL to latest beta news under the plugin row.

## v2.0.5 - 14th September, 2019

* Added: Support for WooCommerce's authentication method.

## v2.0.4 - 26th August, 2019

* Added: More FAQ's to readme.txt file for the WordPress plugin directory.
* Changed: Title of the plugin in readme.txt file to improve SEO Results.
* Changed: Minimum WooCommerce version required and supported is v3.6.
* Tweaked: Upgrade link now shows always once plugin is installed, not after 1 week.
* Tweaked: Upgrade link colour changed from green to red to stand out more.

## v2.0.3 - 19th August, 2019

* Added: A notice under the plugin row providing information on future versions coming that require your feedback.
* Tested: Compatible with WooCommerce v3.7
* Tweaked: Admin body class for CoCart page.
* Updated: Documentation URL has changed to <https://docs.cocart.xyz>

## v2.0.2 - 19th July, 2019

* Tweaked: Updated link to getting started page if CoCart was installed via WP-CLI.

## v2.0.1 - 18th July, 2019

* Tweaked: `get_cart_contents_count()` is now called static.
* Tweaked: Added check for cart totals to make sure they are set before falling back to cart totals in session.
* Dev: Added filter `cocart_update_item` for the response when updating an item.
* Dev: Tweaked CoCart page in the WordPress dashboard to support sections.

## v2.0.0 - 3rd July, 2019

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

## v1.2.3 - 7th June, 2019

* Added: Upgrade warning notice in preparation for CoCart v2 release.

## v1.2.2 - 30th May, 2019

* Fix: Plugin would fail to install date and version for future updates.
* Fix: Plugin would fail to redirect to Getting Started page once activated.

> Both of these failed due to reverting a change in the last update to fix the API from crashing.

## v1.2.1 - 21st May, 2019

* HOTFIX: Reverted change for including classes so **WC_VERSION** constant was defined first.

## v1.2.0 - 20th May, 2019

* NEW: Add Getting Started page to introduce users to view the documentation once installed.
* NEW: Plugin review notice appears after the first week of use.
* Tweaked: Improved code base of the plugin, **NOT** the REST-API.

## v1.1.2 - 17th May, 2019

* Tweaked: Allow removing of items via update logic if quantity is zero. Thanks to [@SHoogland](https://github.com/SHoogland)

## v1.1.1 - 25th April, 2019

* Checked: Compatibility with WooCommerce v3.6.2
* Updated: Changelog. Forgot to do it in last update.

## v1.1.0 - 23rd April, 2019

* Compatible: Support for WooCommerce 3.6+

## v1.0.9 - 5th April, 2019

* Compatible: Tested up to WordPress 5.1.1
* Compatible: Tested up to WooCommerce 3.5.7
* Tweaked: Support link in plugin row.

## v1.0.8 - 18th February, 2019

* Compatible: Ready for WordPress 5.1 release. 🎊
* Added: Review link to plugins row.

## v1.0.7 - 28th January, 2019

* Tweaked: Clear cart now clears cart in session if the user is logged in. - Thanks to [@elron](https://github.com/elron) for the patch.

## v1.0.6 - 12th November, 2018

* Changed: If the cart is empty, the response returns an empty array. - Issue #33 Feedback provided by [@joshuaiz](https://github.com/joshuaiz)
* Improved: Updating items by adding a check to see if there is enough stock. Thanks to @DennisMatise

## v1.0.5 - 11th October, 2018

* Fixed: Variation and cart item data validation callback. - Issue #40 Thanks to @DennisMatise
* Fixed: A fatal error that caused errors not to return properly. - Issue #35 Thanks to [@skunkbad](https://github.com/skunkbad)
* Changed: Name of the plugin is now CoCart. The plugin slug will remain the same.

## v1.0.4 - 5th July, 2018

* Fixed: Return response for numeric thanks to [@campusboy87](https://github.com/campusboy87)
* Fixed: Fatal error for adding and updating items when validating the callback `is_numeric`. - Issue #30

## v1.0.3 - 22nd April, 2018

* Fixed: Syntax error for including cart controller for sites running versions of PHP lower than 7. Thanks to @Mr-AjayM for another contribution.
* Fixed: Validation of `cart_item_key` when removing, restoring or updating an item. Item keys starting with a letter were returning false. Reported by @Janie20.
* Tested up to WooCommerce v3.3.5 and up to WordPress v4.9.5

## v1.0.2 - 31st March, 2018

* Fixed: Invalid Argument Error should the cart be empty. Now returns "Cart is empty" properly. Thanks to @Mr-AjayM for the contribution.

## v1.0.1 - 2nd March, 2018

* Added: Fetch current cart item data before it is updated.
* Added: New endpoint to restore, remove and update items in cart due to a conflict that prevented from registering the route.
* Corrected: Fetching cart item key as integer to a clean string.
* Corrected: Had response messages for updating quantity backwards. Oops!
* Improved: Made sure it returns a response if the cart is empty.
* Enhanced: Added a check to see if the cart has any items before calculating totals.

## v1.0.0

* Initial version. Released on WordPress.org on 26th February, 2018
