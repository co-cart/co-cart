# Changelog for CoCart Lite

## v2.8.1 - 10th December, 2020

* Added: Support for Pantheon.io so it no longer caches for guest customers.

> This release introduces support for third party starting with web host.

## v2.8.0 - 9th December, 2020

* Enhanced: 📦 Load chosen shipping method when loading cart from session for the web.
* Tested: ✔️ Compatible with WooCommerce v4.8 and WooCommerce Admin v1.7
* Tested: ✔️ Compatible with WordPress v5.6

### Minimum requirement changes

* WordPress now needs to be v5.3 minimum.
* WooCommerce now needs to be v4.3 minimum.

> Support for CoCart Lite will not be provided for sites running any lower than these minimum requirements.

## v2.7.4 - 18th November, 2020

* Enhanced: 🤯 **Access-Control-Expose-Headers** to allow `X-COCART-API` to be exposed allowing frameworks like **React** to fetch them.
* Tested: ✔️ Compatible with WooCommerce v4.7

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
