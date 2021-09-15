# Changelog for CoCart Lite

## v3.1.0 - ?? September, 2021 (DATE SUBJECT TO CHANGE)

* **NEW**: Setup wizard introduced to help identify if the store is new and prepare the environment for headless setup.
* **NEW**: Cart API route introduced that allows developers to add custom callbacks to update the cart for any possibility. - [See example](https://github.com/co-cart/cocart-cart-callback-example).
* **NEW**: CoCart Products add-on now merged introducing API v2 with a new option to view single products by SKU and many improved tweaks.
* **NEW**: [Flexiable Shipping](https://wordpress.org/plugins/flexible-shipping/) added as plugin suggestion.
* **NEW**: No cache control added to help prevent CoCart from being cached at all so results return quicker.
* **NEW**: Should table creation fail during install, ask user if they have privileges to do so.
* **NEW**: Ability to set the customers billing email address while adding item/s to cart. Great for capturing email addresses for cart abandonment.
* **NEW**: Ability to return only requested fields for the cart response before fetching data. Just like GraphQL. Powerful speed performance.
* **NEW**: Ability to set the price of the item you add to the cart with new cart cache system. - Simple Products and Variations ONLY!
* Deprecated: Upgrade Warning notice.
* Enhanced: Shipping rates now return meta data if any. Thanks to [@gabrielandujar](https://github.com/gabrielandujar) for contributing.
* Enhanced: Stock check improved when adding item by checking the remaining stock instead.
* Enhanced: Load Cart from Session to allow registered customers to merge a guest cart. - Thanks to [@ashtarcommunications](https://github.com/ashtarcommunications) for contributing.
* Fixed: Coupons duplicating on each load.
* Fixed: Redirect to the "Getting Started" page should no longer happen on every activation.
* Fixed: Plugin review notice dismiss action.
* Tweaked: Cron job for cleanup sessions and removed WooCommerce cron job for cleanup sessions as it is not needed.
* Tweaked: Session abstract now extends `WC_Session` abstract for plugin compatibility for those that strong types.
* Tweaked: Session handler by adding `get_session()` function for plugin compatibility.
* Removed: CoCart Products Add-on as a plugin suggestion now the products API is merged with core of CoCart.
* Dev: Introduced new filter `cocart_secure_registered_users` to disable security check for using a registered users ID as the cart key.
* Dev: Introduced new filter `cocart_override_cart_item` to override cart item for anything extra.
* Dev: Introduced new filter `cocart_variable_empty_price` to provide a custom price range for variable products should none exist yet.
* Dev: Introduced new filter `cocart_get_price_range` to alter the price range for variable products.
* Dev: Introduced new filter `cocart_products_add_to_cart_rest_url` for quick easy direct access to POST item to cart for other product types.
* Dev: Added more compatibility for next update of CoCart Pro.
* Dev: Minimum requirement for WordPress is now v5.5
* Uninstall: Will reschedule WooCommerce cron job for cleanup sessions.

> ⚠️ If you have been using CoCart Products add-on, make sure you have the latest version of it installed before updating CoCart to prevent crashing your site. Otherwise best to deactivate the add-on first. Subscription support will remain in CoCart Products add-on until next CoCart Pro update. ⚠️

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
* Tested: ✔️ Compatible with WooCommerce v5.3
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
* Dev: Introduced `cocart_cart` filter for modifiying the cart response in any capacity.

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
* Dev: Introduced `cocart_cart_item_title` filter allows you to change the product title. The title normaly returns the same as the product name but variable products return the title differently.
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
* Tested: ✔️ Compatible with WooCommerce v5.2

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
* Tested: ✔️ Compatible with WooCommerce v5.1
* Tested: ✔️ Compatible with WordPress v5.7

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
