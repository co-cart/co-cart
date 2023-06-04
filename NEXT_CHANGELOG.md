# Next Changelog for CoCart <!-- omit in toc -->

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

## Table of Contents <!-- omit in toc -->

- [What's New?](#whats-new)
- [Authentication](#authentication)
- [Experimental](#experimental)
  - [RateLimiter for the API](#ratelimiter-for-the-api)
- [Improvements and Tweaks](#improvements-and-tweaks)
- [Security](#security)
  - [Disable WordPress Access](#disable-wordpress-access)
  - [Override Price Hijacking](#override-price-hijacking)
  - [FAQ](#faq)
    - [Wont a developer be still be able find the salt key?](#wont-a-developer-be-still-be-able-find-the-salt-key)
    - [Can I only allow specific products to be overridden?](#can-i-only-allow-specific-products-to-be-overridden)
- [Session handler](#session-handler)
- [Deprecations and Replacements](#deprecations-and-replacements)
- [Internal Changes](#internal-changes)
- [Developers](#developers)
  - [Response Headers](#response-headers)
  - [Monetary values](#monetary-values)
- [Database Changes](#database-changes)
- [Support](#support)

## What's New?

- New settings page:
  - Set the front-end site URL for product permalinks for the Products API.
  - Set a Salt Key to secure specific features from outside tampering. If salt key is already set in `wp-config.php` then that will take priority.

<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/dev/docs/images/cocart-settings.png" alt="CoCart Plugin Settings" /></p>

- [Session handler](#session-handler) now initiates lighter for the use of CoCart's API while leaving the original handling for the native WooCommerce intact for the frontend.
- Session now logs user ID, customer ID and cart key separately. Allowing more options for the cart to be managed how you like via the REST API. (Details on this change needs to be documented.)
- Use of Namespaces has now been applied to help extend CoCart, easier to manage and identify for developers.
- Re-organized what routes are allowed to be cached i.e products API rather than prevent all CoCart routes from being cached.
- Added package information of each plugin module to WooCommerce System Status.
- New WP-CLI command `wp cocart status` shows the status of carts in session.
- Enhancements made to [authentication](#authentication).
- Finally added the ability to add/update customer details to the cart with validation. [See guide for example](#).
- Added ability to set customers billing phone number while adding item to the cart.
- Added ability to request product variations to return without the parent product. - Solves [[issue 3](https://github.com/co-cart/cocart-products-api/issues/3)]
- Added ability to search products by title. - Solves issue [[issue 7](https://github.com/co-cart/cocart-products-api/issues/7)]
- Added ability to filter the fields of the endpoint you request before they return, making the response faster. - Excludes Sessions API.
- Added ability to return the fields in the cart response based on a pre-configured option as alternative to filtering the fields individually. Options: `digital`, `digital_fees`, `shipping`, `shipping_fees`, `removed_items` and `cross_sells`
- Added batch support. Feedback needed. (API v2 supported ONLY) (Details on this addition needs to be documented.)
- Added new endpoint to delete all items (only) in the cart.

## Authentication

A few enhancements have been made for authentication.

First is when the WordPress login cookies are set, they are not available until the next page reload. For CoCart specifically for returning updated carts, we need this to be available immediately so now they are forced to be available without reload. This is only to help with frameworks that have issues with or lack of support for Cookies.

Second is customers can now authenticate with their billing phone number and password. This is helpful for certain countries where the customer logins with their phone number instead of username or email address.

Third is a new [RateLimiter for the API.](#ratelimiter-for-the-api)

## Experimental

_The first feature experiment has been added in the hopes to provide more control on who/what has access to CoCart' REST API and requires your feedback._

### RateLimiter for the API

> This is optional and disabled by Default

The main purpose was to prevent abuse on endpoints from excessive calls and performance degradation on the machine running the store.

Rate limit tracking is controlled by either `USER ID` (logged in) or `IP ADDRESS` (unauthenticated requests).

It also offers standard support for running behind a proxy, load balancer, etc. This also optional and disabled by default.

By default, a maximum of 25 requests can be made within a 10-second time frame. These can be changed through an options filter.

More extensive information can be found on the [Rate Limit Guide](https://github.com/co-cart/co-cart/blob/dev/docs/rate-limit-guide.md) along with a test guide.

## Improvements and Tweaks

- Fetch total count of all carts in session once when displaying the status under "WooCommerce -> Status".
- Plugin Suggestions now returns results better the first time it's viewed. **(Needs reviewing)**
- Sub-menus in the WordPress dashboard now load faster. No redirects.
- Error responses are now softer to prevent fatal networking when a request fails.
- Monetary values improved and return all as a float value for better compatibility with JS frameworks. [See developers section for more](#developers).
- Optimized products API response and updated schema.
- All endpoints with schema now have proper schema title for proper identification.
- The callback for cart update endpoint now passes the controller class so we don't have to call it a new.
- Caching of same item added to cart no longer loses previous custom price set before and has not changed.

## Security

### Disable WordPress Access

With the new settings page added in this release you can disable access to WordPress with a simple checkbox. You can even redirect to your headless site if you use the "Front-end URL" field which is also used to re-write your permalinks.

### Override Price Hijacking

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

## Session handler

The session handler is a big part of what makes CoCart work. Without it, decoupling WooCommerce would be a problem as it relies heavily using cookies to store user session tokens that is fixed on the same origin as the WordPress installation.

Our handler had to change in order to remove that limitation and has gone through a number of changes over many releases. While most of the changes were made to help support how CoCart is handled via the REST API, it took away some of the compatibility with it for extensions, third party plugins and custom functions developed for a client who are using the public functions within the original session handler developed by WooCommerce.

After more research and testing, we found that due to the limits of the session handler in WooCommerce. Many popular extensions and third party plugins have created hacky workarounds to compensate. One piece of data we found to be used the most to help identify the user session is the WooCommerce cookie, which we had replaced with our own.

While the main goal is to make CoCart the best headless API, we understand now that we have to leave these limitations in while still making CoCart run at it's best without breaking core features in WooCommerce which are being used in other third party plugin.

So the session handler has been updated and improved to handle both WooCommerce extensions and third party plugins even better than before while still supporting CoCart for what it needs. The original WooCommerce session cookie is put back for the frontend while the CoCart API doesn't use it at all.

Instead the user session data is returned during any cart request and passes the required information to HTTP Header so it can be cached client-side.

## Deprecations and Replacements

There are many deprecations made with this release but nothing that should affect you unless you were using certain functions directly within your code. Here is a list of the most important ones.

- Legacy API that extended `wc/v2` when CoCart was only a prototype.
- Session cookie is now reverted back to original WooCommerce session cookie.
- Filter `cocart_customer_id` no longer used to override the customer ID for the session.
- Filter `cocart_cookie` no longer used as the session cookie has been reverted back to default.
- Filter `cocart_no_items_message` replaced with another filter `cocart_no_items_in_cart_message` that is shared in other endpoints.
- Function `WC()->session->use_httponly()` no longer used.
- Function `WC()->session->cocart_setcookie()` no longer used. Replaced with `cocart_setcookie()`.
- Function `WC()->session->get_cart_created()` no longer used. Replaced with `cocart_get_timestamp()`.
- Function `WC()->session->get_cart_expiration()` no longer used. Replaced with `cocart_get_timestamp()`.
- Function `WC()->session->get_cart_source()` no longer used. Replaced with `cocart_get_source()`.

## Internal Changes

- Autoload via Composer is now used to help load all modules of the plugin and specific class files without the need to manually include them.
- Moved deprecated functions to it's own file.
- Inline documentation much improved. Allows for a code reference to be generated per release for developers.

## Developers

- Introduced new filter `cocart_cart_item_extensions` to allow plugin extensions to apply additional information.
- Introduced new filter `cocart_is_allowed_to_override_price` that by default will always allow overriding the price unless stated otherwise when an item/s is added to the cart.
- Introduced new filter `cocart_cart_totals` that can be used to change the values returned.
- Introduced new filter `cocart_validate_ip` that can be used to validate if the IP address can access the API.
- Introduced new filter `cocart_api_rate_limit_options` to set the rate limit options.
- Introduced new action hook `cocart_api_rate_limit_exceeded` to allow you to include your own custom tracking usage.
- Added new parameters to filter `cocart_cart` so you can access the cart controller and requested data.
- Introduced new action hook `cocart_added_item_to_cart` that allows for additional requested data to be processed via a third party once item is added to the cart.
- Introduced new action hook `cocart_added_items_to_cart` that allows for additional requested data to be processed via a third party once items are added to the cart.

### Response Headers

Two new headers return for cart responses only. `CoCart-API-Cart-Expiring` and `CoCart-API-Cart-Expiration`. These two new headers can help developers use the timestamps of the cart in session for when it is going to expire and how long until it does expire completely.

### Monetary values

All monetary values in the cart are formatted after giving 3rd party plugins or extensions a chance to manipulate them first and all return as a _float_ value. This includes using the following filters at priority `99`.

- `cocart_cart_item_price`
- `cocart_cart_item_subtotal`
- `cocart_cart_item_subtotal_tax`
- `cocart_cart_item_total`
- `cocart_cart_item_tax`
- `cocart_cart_fee_amount`
- `cocart_cart_tax_line_amount`
- `cocart_cart_totals_taxes_total`
- `cocart_cart_totals`

Now developers have consistent format that can be used with the likes of WooCommerce's [Number](https://www.npmjs.com/package/@woocommerce/number) and [Currency](https://www.npmjs.com/package/@woocommerce/currency) modules.

## Database Changes

We now store the user ID and customer ID as a separate unique value to the cart session. We have to update the database structure in order to save it so an upgrade will be required.

This is a big change, so until your WordPress site has processed the update for CoCart it will fallback on a legacy session handler to not disrupt your store from working.

As always it is best to back-up before any database changes are made. When proceeding with the update, the cart session will not be active again until the upgrade is complete.

## Support

- In order for CoCart to continue working you need to be using WooCommerce 6.9 minimum or higher.
- No longer supporting CoCart API v1. Only bug or security fixes will be provided if any.
