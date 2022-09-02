# Next Changelog for CoCart

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

## What's New?

* New settings page:
 * * Set the front-end site URL for product permalinks for the Products API.
 * * Set a Salt Key to secure specific features from outside tampering. If salt key is already set in `wp-config.php` then that will take priority.
* Session manager now initiates lighter for the use of CoCart's API while leaving the original initiation for the native WooCommerce intact for the frontend.
* Session now logs user ID, customer ID and cart key separately. Allowing more options for the cart to be managed how you like via the REST API. (Details on this change needs to be documented.)
* Use of Namespaces has now been applied to help extend CoCart, easier to manage and identify for developers.
* Re-organized what routes are allowed to be cached i.e products API rather than prevent all CoCart routes from being cached.
* Added package information of each plugin module to WooCommerce System Status.
* New WP-CLI command `wp cocart status` shows the status of carts in session.
* New ability to set customers billing phone number while adding item to the cart.

## Authentication

When the WordPress login cookies are set, they are not available until the next page reload. For CoCart specifically for returning updated carts, we need this to be available immediately so now they are forced to be available without reload. This is only to help with frameworks that have issues with or lack of support for Cookies.

* [RateLimiter for the API.](#ratelimiter-for-the-api)

## Experimental

_The first feature experiment has been added in the hopes to provide more control on who/what has access to CoCart' REST API and requires your feedback._

### RateLimiter for the API.

**Disabled by Default**

It's designed to prevent abuse of endpoints from excessive calls and performance degradation on the machine running the store.

It is unauthenticated, so rate limits are keyed by either USER ID (logged in) or IP ADDRESS (guest user), and standard support for running behind a proxy, load balancer, etc. for unauthenticated users can be enabled.

By default, a maximum of 25 requests can be made within a 10-second time frame. These can be changed through an options filter.

More extensive information can be found on the [Rate Limit Guide](https://github.com/co-cart/co-cart/blob/dev/docs/rate-limit-guide.md) along with a test guide.

## Improved

* Fetch total count of all carts in session once when displaying the status under "WooCommerce -> Status".
* Plugin Suggestions now returns results better the first time it's viewed.
* Sub-menus in the WordPress dashboard now load faster. No redirects.

## Security

This is new and is needed to help prevent session hijacking for someone tech-savvy in manipulating the API.

Since adding the most requested option to override the price of the item, there hasn't been an option to disable it or prevent any bad use of the API should someone with knowledge manipulate the item in the cart to set the price outside of the stores initial design.

So if you are not wanting the price override option used at all for any product, you can disable it via a new filter introduced in this release like so.

```php
add_filter( 'cocart_is_allowed_to_override_price', function() {
    return false;
});
```

To help with hijacking the price, a salt key can be set via the new settings page or by defining a new constant `COCART_SALT_KEY` in your `wp-config.php` file. This can be anything you wish it to be as long as it's not rememberable. It will be encrypted later with **md5** when validated. Once a salt key is set, any request to add item/s to the cart with a new price CoCart will check if the salt key was also passed along. If the salt key does not match then the price will remain the same.

### FAQ

- [Wont a developer be still be able find the salt key?](#wont-a-developer-be-still-be-able-find-the-salt-key)
- [Can I only allow specific products to be overridden?](#can-i-only-allow-specific-products-to-be-overridden)
#### Wont a developer be still be able find the salt key?

Possibly. It all depends on how well you have minified your code to hide the fact that you are allowing price override. This is only a means to help slow down the possibility of a session hijack.

#### Can I only allow specific products to be overridden?

Yes of course. With the new filter introduced, you can run it through a loop of product ID's and return the statement as true for them only and return false for every other product your not checking.

```php
add_filter( 'cocart_is_allowed_to_override_price', 'only_override_these_product_prices', 10, 1 );
function only_override_these_product_prices( $cart_item ) {
    if ( in_array( $cart_item, array( '24', '784', '451' ) ) ) {
        return true;
    }

    return false;
}
```

## Deprecations and Replacements

* Legacy API that extended `wc/v2` when CoCart was only a prototype.
* Session cookie is now reverted back to original WooCommerce session cookie.
* Filter `cocart_customer_id` no longer used to override the customer ID for the session.
* Filter `cocart_cookie` no longer used.
* Function `WC()->session->use_httponly()` no longer used.
* Function `WC()->session->cocart_setcookie()` no longer used. Replaced with `cocart_setcookie()`.
* Function `WC()->session->get_cart_created()` no longer used. Replaced with `cocart_get_timestamp()`.
* Function `WC()->session->get_cart_expiration()` no longer used. Replaced with `cocart_get_timestamp()`.
* Function `WC()->session->get_cart_source()` no longer used. Replaced with `cocart_get_source()`.

## Internal Changes

* Autoload via Composer is now used to help load all modules of the plugin and specific class files without the need to manually include them.
* Moved deprecated functions to it's own file.
* Inline documentation much improved. Allows for a code reference to be generated per release for developers.

## Developers

Introduced new filter `cocart_is_allowed_to_override_price` that by default will always allow overriding the price unless stated otherwise when an item/s is added to the cart.
Introduced new filter `cocart_validate_ip` that can be used to validate if the IP address can access the API.
Introduced new filter `cocart_api_rate_limit_options` to set the rate limit options.
Introduced new action hook `cocart_api_rate_limit_exceeded` to allow you to include your own custom tracking usage.

## Database Changes

As we now store the user ID and customer ID as a separate unique value to the cart session. We have to update the database structure in order to save it so an upgrade will be required.

As this is a big change, until your WordPress site has processed the update for CoCart it will fallback on a legacy session handler to not disrupt your store from working.

As always it is best to back-up before any database changes are made. When proceeding with the update, the cart session will not be active again until the upgrade is complete.

## Support

No longer supporting API v1. Only bug or security fixes will be provided if any.