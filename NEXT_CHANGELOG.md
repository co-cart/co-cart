# Next Changelog for CoCart Lite

> This changelog is NOT final so take it with a grain of salt.

This next release re-organizes how CoCart is put together and allows for more open expansion possibilities.

## What's New?

* Session manager now initiates lighter for the use of CoCart's API while leaving the original initiation for the native WooCommerce intact.
* Session now logs user ID, customer ID and cart key separately. Allowing more options for the cart to be managed how you like via the REST API. (Details on this change needs to be documented.)
* Use of Namespaces has now been applied to help extend CoCart, easier to manage and identify for developers.
* Re-organized what routes are allowed to be cached i.e products API rather than prevent all CoCart routes from being cached.
* Added package information of each plugin module to WooCommerce System Status.
* New WP-CLI command `wp cocart status` shows the status of carts in session.
* New ability to set customers billing phone number while adding item to the cart.

## Improved

* Fetch total count of all carts in session once when displaying the status under "WooCommerce -> Status".
* Plugin Suggestions now returns results better the first time it's viewed.

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

## Database Changes

As we now store the customer ID as a separate unique value to the cart session. We have to update the database structure in order to save it so an upgrade will be required.

As this is a big change, until your WordPress site has processed the update for CoCart it will fallback on a legacy session handler to not disrupt your store from working.

As always it is best to back-up before any database changes are made. When proceeding with the update, the cart session will not be active again until the upgrade is complete.