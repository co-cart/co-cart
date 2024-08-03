# Next Changelog for CoCart <!-- omit in toc -->

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

## Bug Fix

* REST API: View or deleting a session with the Sessions API was not accessing the session handler. No longer needs a separate load.

## Changes

* REST API: Avatars only return if requested now when using the login endpoint.
* REST API: The following endpoints for Cart API v2 now extend `CoCart_REST_Cart_V2_Controller` instead of an Cart API v1 controller: `cart/add-item`, `cart/add-items`, `cart/calculate`

## Improvements

* REST API: Only registers CoCart endpoints if requesting it. Helps performance in backend such as when using Gutenberg/Block editor as it loads many API's in the background.
* REST API: Moved more functions to utility class to help improve the complexity of the cart controller so we get better performance.
* REST API: Prevent having to check cart validity, stock and coupons on most cart endpoints other than when getting the cart to help with performance.
* REST API: Optimized how many times we calculate the totals when adding items to the cart to help with performance.
* REST API: Moved some cart validation further up before returning cart contents.
* REST API: Fallback to a wildcard if the origin has yet to be determined for CORS.
* Feature: Load cart from session now supports registered customers.
* Localization: Similar messages are now consistent with each other.

### Load Cart from Session

Originally only designed for guest customers to allow them to checkout via the native site, registered customers can now auto login and load their carts to do the same.

#### How does a registered customer load in without authenticating?

To help customers skip the process of having to login again, we use two data points to validate with that can only be accessed if the user was logged in via the REST API to begin with. This then allows the WordPress site setup as though they had gone through the login process and loads their shopping cart.

The two data points required are the cart key which for the customer logged in via the REST API will be their user ID. And the cart hash which represents the last data change of the cart. By using the two together, the customer is able to transfer from the headless version of the store to the native store.

Simply provide these two parameters with the data point values on any page and that's it.

`https://your.store/?cocart-load-cart={cart_key}&c_hash={cart_hash}`

#### Developers

* Introduced new filter `cocart_load_cart_redirect_home` allows you to change where to redirect should loading the cart fail.
* Added parameters for filter `cocart_add_to_cart_sold_individually_quantity`.
* Added the request object as parameter for filter `cocart_allow_origin`.
* Added the product object to filters `cocart_cart_item_price` and `cocart_cart_item_quantity`.

#### Deprecations

The following filters are no longer used:

* `cocart_load_cart_override`
* `cocart_load_cart`
* `cocart_merge_cart_content`
* `cocart_cart_loaded_successful_message`
* `cocart_use_cookie_monster`
* `cocart_filter_request_data`
