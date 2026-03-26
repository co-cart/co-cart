# Next Changelog for CoCart Core <!-- omit in toc -->

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

> Documentation for breaking changes and new features can be found at https://docs.cocartapi.com - once loaded, select "preview" from the top left dropdown menu.

## What's new?

* REST API: All `GET` endpoints now support batching. Batching was only enabled to select `POST`, `PUT`, `PATCH`, `DELETE` cart endpoints. Now you can fetch any endpoint (except logout) in a batch request.
* REST API: Products can now be filtered to include `include_types` or exclude `exclude_types` by multiple types simultaneously using the new parameters. Note: `include_types` takes precedence over `type` parameter should both be used.
* REST API: Products can now be filtered to return virtual products by the boolean `virtual` parameter.
* REST API: Products now returns any applicable brands. (API v2 Only)
* REST API: Products now returns the global unique id. (API v2 Only)
* REST API: New endpoint `cocart/v2/products/brands` to get product brands like categories.
* REST API: New endpoint `cocart/v2/products/reviews/mine` to return product reviews only by the current user. Requires authentication.
* REST API: Product variations in cart can now be updated using the same update item endpoint `cocart/v2/cart/item/{item_key}`.
* REST API: New header returns for Cart API `Cart-Hash`. This can help your applications identify if anything has changed in the cart before using the data in the response.
* REST API: New POST method for the cart to create an empty cart for guest customers. (Recommend if not using the available SDK's)

> Developer note: Cart creation is normally done the moment the first item is added to the cart as it has data to save to session. But some users are confused with creating a cart for guest customers. This route can help create an empty cart, storing just the cart key and return it in the response. See the quick start guide in the documentation for more information on how to use the cart key for a guest customer.

* Plugin: New WP-CLI command `wp cocart status` shows the status of carts in session.
* Plugin: New WP-CLI command `wp cocart sessions` shows the details of each session and allows you to check if they exist.
* Plugin: Updates are provided from us for all supported CoCart plugins.
* Plugin: Will deactivate legacy core version if one found installed and active. If you try to activate legacy version while the new core version is active it will deactivate. Recommend deleting.

> Developer note: If you have the `COCART_REMOVE_ALL_DATA` constant set to true. Recommend setting it to false before uninstalling the legacy version from your WordPress dashboard to prevent any issues.

* Plugin: Integrity check of all files.

> Developer note: Know if the version of CoCart is official and has not been tampered with. This is just the first iteration introduced.
> You will never see the warning messages if all is in order, but if you do alter any files within the plugin yourself directly. You will be notified in your WordPress dashboard and in your error log that something has changed and does not match.

### Cache & Performance

* REST API: ETag support for cache validation on product and cart endpoints. Returns `304 Not Modified` when content hasn't changed, reducing bandwidth and improving response times.
* REST API: Cart routes support ETag headers across all methods (GET, POST, PUT, DELETE) for immediate cache use.
* REST API: Schema is now cached to allow short-circuiting for WordPress to process data faster.
* REST API: Product variation data cached by slug for faster lookups.
* REST API: Cart routes now provide a real expiration header for proper HTTP cache support.
* REST API: Experimental `Vary: Accept-Encoding` header support for servers that don't configure it.
* REST API: `no-store` added to `Cache-Control` header for guest users.
* REST API: Products can now be fetched by slug via a dedicated endpoint.
* REST API: `X-HTTP-Method-Override` header support for environments that restrict HTTP methods.

## Breaking Changes

* REST API: Avatars only return if requested now when using the login endpoint.
* REST API: Store API now returns array of CoCart versions installed not just the core version.
* REST API: Product meta will not return by default. To improve security and prevent PII from exposure, meta must now be whitelisted instead using the new filter `cocart_products_allowed_meta_keys`.
* REST API: Variation data for items is moved above totals when fetching the cart.
* REST API: The quantity parameter when adding an item now accepts both a `numeric` or an `array` value allowing to extend support for other product types that are a container of other grouped products.
* REST API: When updating an item in cart, the quantity parameter is no longer required. Allowing to change just the variation of a variable product to keep the current quantity already requested.
* REST API: Cart item `quantity` object now returns `minimum`, `maximum`, `multiple_of` and `editable` instead of `min_purchase` and `max_purchase`. These values are stock-aware and respect WooCommerce quantity input step settings.
* REST API: Product `purchase_quantity` object now returns `minimum`, `maximum` and `multiple_of` instead of `min_purchase` and `max_purchase`.
* REST API: Product Categories changed `image_src` from a single thumbnail to return all image sizes available. Schema updated to match.
* REST API: Product reviews was updated to support better query parameters. Affects both API versions. Schema updated to match.
* REST API: The response class `CoCart_Response` is deprecated. New utility response classes have been created for better utilization.
* Feature: Load Cart from Session rewritten. See details below.
* REST API: Function `return_cart_contents()` renamed to `prepare_object_for_response()` for WP REST Controller compatibility.
* REST API: Function `get_cart()` renamed to `get_items()` for WP REST Controller compatibility.
* REST API: Function `get_cart_item()` renamed to `get_item_from_cart()`.
* REST API: Function `get_items()` renamed to `get_items_in_cart()`.
* REST API: Variable `$add_to_cart_handler` renamed to `$product_type`.
* REST API: `$variation_id` and `$variation` parameters replaced with `$request` in variation handling.
* REST API: Cart expiration timestamp headers (`Cart-Expiring`, `Cart-Expiration`) only show when debugging. The default expiration header now uses the cart expiry timestamp instead for better cache support.
* REST API: Product attributes response structure rewritten.
* REST API: API v2 taxonomy controllers reorganized to use new shared abstract controller instead of extending API v1.
* Plugin: Text domain a.k.a the plugin slug, has changed from `cart-rest-api-for-woocommerce` to `cocart-core`. This affects any translations including custom. If you did a custom translation you will need to rename the text domain to match.

The following returned headers have also been renamed. Better for security reasons.

| Previous Header            | New Header           |
| -------------------------- | -------------------- |
| CoCart-API-Cart-Key        | Cart-Key             |

> Headers below only show if WP_DEBUG is true.

| Previous Header            | New Header           |
| -------------------------- | -------------------- |
| CoCart-Timestamp           | Timestamp            |
| CoCart-API-Cart-Expiring   | Cart-Expiring        |
| CoCart-API-Cart-Expiration | Cart-Expiration      |

The following action hooks have changed.

| Action Hook                         | Change                                           |
| ----------------------------------- | ------------------------------------------------ |
| `cocart_item_restored`              | Added the request object as the first parameter. |
| `cocart_item_removed`               | Added the request object as the first parameter. |
| `cocart_item_added_updated_in_cart` | Moved the request object parameter to be first.  |
| `cocart_item_added_to_cart`         | Moved the request object parameter to be first.  |

## Changes

* REST API: The main cart controller `CoCart_REST_Cart_V2_Controller` for API v2 now extends a new abstract controller `CoCart_REST_Cart_Controller` for the cart.

> Developer note: This allows better extending the cart API rather than the whole cart controller.

* REST API: The following endpoints for Cart API v2: `cart/add-item`, `cart/add-items`, `cart/calculate` now extend `CoCart_REST_Cart_V2_Controller` instead of the Cart API v1 controller.

> Developer note: This allows us to deprecate API v1 in the future.

* REST API: New product reviews posted are set to status `hold` by default.
* REST API: All controllers now use new abstract controllers compatible with WordPress REST API abstracts.
* REST API: New shared taxonomy abstract controller for better code reuse.
* REST API: New abstract controllers for products API.
* REST API: Controllers now use a pagination utility class.
* REST API: Logout controller disconnected from extending API v1.
* REST API: `filter_request_data` function undeprecated and rewritten in the abstract class.
* REST API: Cart-key permission check moved to the abstract Cart Controller so all cart endpoints enforce it via inheritance.
* Plugin: WC Admin Notes changed to run once daily instead of every load.
* WordPress Dashboard: Style adjustments.

## Improvements

* Security: Unauthenticated access to registered user carts is now always enforced. The `cocart_secure_registered_users` filter can no longer disable this check.
* Session: Disabled WooCommerce persistent cart preventing stale user meta from overriding cart data.
* REST API: Redone the process of adding items to the cart for a smoother flow, filtering, validation and compatibility with any WooCommerce extension.
* REST API: Only registers CoCart endpoints if requesting it. Helps performance in backend such as when using Gutenberg/Block editor as it loads many API's in the background.
* REST API: Now hides routes from the index of controllers and returns as an error for added security.
* REST API: Registering the endpoints have been improved and allows the namespace to register with your own brand (so long as you have the whitelabel add-on installed).
* REST API: Moved more functions and filters to utility class to help improve the complexity of the cart controller so we get better performance.
* REST API: Prevent having to check cart validity, stock and coupons on most cart endpoints other than when getting the cart to help with performance.
* REST API: Optimized how many times we calculate the totals when adding items to the cart to help with performance.
* REST API: Optimized shipping data, added validation and support for recurring carts for subscriptions.
* REST API: Rewritten the products and cart response to allow better field filtering with WordPress REST API global parameter `_fields` with nesting support.
* REST API: Fallback to a wildcard if the origin has yet to be determined for CORS.
* REST API: Override sale and regular price too so the set price is what is shown even if the prices are originally lower.
* REST API: Improved updating customer details allowing the use of custom checkout fields and any that are required.
* REST API: Meta data is fetched for any additional checkout fields registered, if any.
* Feature: Load cart from session now supports registered customers.
* Localization: Similar messages are now consistent with each other.
* Plugin: We now manage cache related calls under our own cache helper utility to not conflict with any WooCommerce cache calls happening in the background.
* WordPress Dashboard: CoCart is prevented from running in the backend should the REST API server be called by another plugin.

### Session Handler

* Session: Fixed `save_data()` writing to object cache with a potentially negative TTL when session expiration was stale or unset.
* Session: Fixed `get_session()` using `wp_cache_set()` instead of `wp_cache_add()`, which caused stale persistent object cache entries to never be overwritten.
* Session: Fixed `get_session()` and `update_cart()` using an uninitialized expiration value on frontend requests, preventing sessions from being cached after a database read.
* Session: Fixed `update_cart()` not syncing the object cache after a database write, causing subsequent reads to return stale cached data.
* Session: Session handler optimized and now stores the cart key for better optimization and consistency.

### Other Improvements

* REST API: Optimized `find_product_in_cart` function.
* REST API: Decoded product slugs and permalinks with non-ASCII characters.
* REST API: Improved updating customer details with better decoding of values and validation of required fields.
* REST API: Cart routes now provide a real expiration header for proper cache support.
* REST API: Improved quantity limits when adding items to cart.
* Plugin: Added detection for multisite before displaying plugin messages.
* Plugin: Added check for sites hosted on WordPress.com.
* Plugin: Improved plugin suggestions — prevents failure due to WordPress.org errors.
* Compatibility: LiteSpeed Cache detection now uses `is_rest_api_request` instead of hardcoded check.
* Compatibility: JWT Auth plugin routes are no longer hardcoded.

### Load Cart from Session

Originally only designed for guest customers to allow them to checkout via the native site, registered customers can now auto login and load their carts to do the same without exposing login details.

#### How does a registered customer load in without authenticating?

To help customers skip the process of having to login again, we use two data points to validate with that can only be accessed if the user was logged in via the REST API to begin with. This then allows the WordPress site setup as though they had gone through the login process and loads their shopping cart.

The two data points required are the cart key which for the customer logged in via the REST API will be their user ID and the cart hash which represents the last data change of the cart. By using the two together, the customer is able to transfer from the headless version of the store to the native store.

Simply provide these two parameters with the data point values on any page and that's it.

`https://your.store/?cocart-load-cart={cart_key}&c_hash={cart_hash}`

> Developer note: By default, both the cart and checkout pages are still accessible to support the feature "Load cart from Session".

## Bug Fixes

* Fixed authentication not determining user in time when `rest_url_prefix` filter is used, causing several cloned guest sessions with cart items.
* Fixed shipping address not setting due to minor flaw.
* Fixed product variation attribute names with special characters not decoding correctly.
* Fixed rounding issue due to decimal separator differences with WooCommerce.
* Fixed undefined `$parent_product`, `$customer_id`, and other undefined property issues.
* Fixed preventing the index of CoCart from exposing all routes available.
* Fixed validating fields to return error notices correctly.

#### Developers

##### New Actions

* Introduced new hook `cocart_cart_created` that fires once a cart is created.
* Introduced new hook `cocart_item_updated` that fires once an item has updated in cart.
* Introduced new hook `cocart_set_requested_cart` that fires before the session is finally set.

##### New Filters

* Introduced new filter `cocart_load_cart_from_session` allows you to decide if the cart should load user meta when initialized.
* Introduced new filter `cocart_load_cart_redirect_home` allows you to change where to redirect should loading the cart fail.
* Introduced new filter `cocart_cross_sell_item_thumbnail_src` that allows you to change the thumbnail source for a cross sell item.
* Introduced new filter `cocart_http_allowed_safe_ports` that allows you to control the list of ports considered safe for accessing the API.
* Introduced new filter `cocart_allowed_http_origins` that allows you to change the origin types allowed for HTTP requests.
* Introduced new filter `cocart_rest_response` to be used as a final straw for changing the response based on the request made.
* Introduced new filter `cocart_wp_frontend_url` that allows you to control where to redirect users when visiting your WordPress site if you have disabled access to it.
* Introduced new filter `cocart_wp_disable_access` to disable access to WordPress.
* Introduced new filter `cocart_wp_accessible_page_ids` to allow you to set the page ID's that are still accessible when you disable access to WordPress.
* Introduced new filter `cocart_rest_should_load_namespace` to determine whether a namespace should be loaded.
* Introduced new filter `cocart_products_allowed_meta_keys` allows you to specify the allowed meta keys for the product.
* Introduced new filter `cocart_product_insert_review_status` allows you to change the status to `approved`. Other values set via filter will automatically reset to `hold`.
* Introduced new filter `cocart_product_quantity_editable` allows you to control whether a cart item's quantity can be changed.
* Introduced new filter `cocart_quantity_multiple_of` allows you to control the quantity step (multiple of) for a product.
* Introduced new filter `cocart_quantity_limit` allows you to adjust the maximum quantity limit for a product after stock considerations.
* Introduced new filter `cocart_cacheable_route_patterns` to control which route patterns support ETag caching.
* Introduced new filter `cocart_version_header_name` to customize the CoCart version response header name.
* Introduced new filter `cocart_cache_header_name` to customize the cache response header name.
* Introduced new filter `cocart_admin_menu_shortcuts` to customize admin menu shortcuts.
* Introduced new filter `cocart_rest_v1_product_schema` for version-specific product schema filtering (API v1).
* Introduced new filter `cocart_rest_v2_product_schema` for version-specific product schema filtering (API v2).

##### New parameters

* Added the request object as a parameter for action hooks `cocart_before_cart_emptied`, `cocart_cart_emptied`, `cocart_cart_cleared`.
* Added the request object as a parameter for filters `cocart_allow_origin`, `cocart_cart_item_quantity`, `cocart_add_to_cart_quantity` and `cocart_cart_item_data`.
* Added parameters for filter `cocart_add_to_cart_sold_individually_quantity`.
* Added the cart class as a parameter for filter `cocart_shipping_package_name`.
* Added the request object as a parameter for filter `cocart_cross_sells`.
* Added `$message` parameter to `cocart_deprecated_function`.
* Added `$request` as parameter for `add_item_to_cart`.
* Added `$cart` parameter to allow passing in the cart instance if already retrieved to avoid stale cart data.

##### Breaking changes

* Filter `cocart_cart_item_quantity` has changed the order of the `$cart_item` and `$item_key` parameter for consistency with other filters.

##### New Functions

* Introduced new function `cocart_get_frontend_url()` to get the frontend URL.
* Introduced new function `cocart_is_wp_disabled_access()` to check if WordPress has been disabled access.
* Introduced new function `cocart_get_permalink()` to return the permalink for a page/post/product where the frontend URL maybe replaced.
* Introduced new utility class `CoCart_REST_Utilities_Monetary_Formatting` for monetary value formatting.
* Introduced new utility class `CoCart_REST_Utilities_Pagination` for pagination support.
* Introduced new utility cart helper functions: `get_cart_totals`, `get_cart_hashes`, `get_item_basic`.
* Introduced new helper function `is_ip_private`.
* Introduced new function `get_cache_header_name()` for retrieving the filterable cache header name.

#### Deprecation's

* Function `cocart_prepare_money_response()` is replaced with function `cocart_format_money()`.
* Abstract class `CoCart_REST_Terms_Controller` replaced with abstract class `CoCart_REST_Taxonomy_Terms_Controller` instead.
* `CoCart_Utilities_Cart_Helpers::get_remaining_stock_for_product()` is replaced with `CoCart_Utilities_Quantity_Limits::get_remaining_stock_for_product()`.
* `cocart_format_money` replaced with `format_money` in monetary utility class.
* `remove_exposed_product_meta` deprecated from using the `cocart_products_ignore_private_meta_keys` filter.

* Filter `cocart_{$object_type}_schema` deprecated in favor of version-specific `cocart_rest_v{version}_{object_type}_schema` filters.
* Filter `cocart_secure_registered_users` deprecated for security — unauthenticated access to registered user carts is now always blocked.

The following filters are no longer used:

* `cocart_load_cart`
* `cocart_merge_cart_content`
* `cocart_cart_loaded_successful_message`
* `cocart_use_cookie_monster`
* `cocart_filter_request_data`
* `cocart_products_ignore_private_meta_keys`
* `cocart_return_default_response`
* `cocart_{$endpoint}_response`
* `cocart_add_to_cart_quantity`

The following hooks are no longer used:

* `cocart_add_to_cart`
