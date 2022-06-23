# Next Changelog for CoCart Lite

> This changelog is NOT final so take it with a grain of salt.

This next release re-organizes how CoCart is put together and allows for more open expansion possibilities.

## What's New?

* Session manager now initiates lighter for the use of CoCart's API while leaving the original initiation for the native WooCommerce intact.
* Session now logs customer ID and cart key separately. Allowing more options for the cart to be managed how you like. (Details on this change needs to be documented.)
* Use of Namespaces have now been applied to help extend CoCart easy to manage and identify for developers.
* Re-organized what routes are allowed to be cached i.e products API rather than prevent all CoCart routes from being cached.

## Deprecated

* Legacy API that extended `wc/v2` when CoCart was only a prototype.
* Filter `cocart_customer_id` no longer used to override the customer ID for the session.

## Database Changes

As we now store the customer ID as a separate unique value to the cart session. We have to update the database structure in order to save it so an upgrade will be required.

As this is a big change, until your WordPress site has processed the update for CoCart it will fallback on a legacy session handler to not disrupt your store from working.

As always it is best to back-up before any database changes are made. When proceeding with the update, the cart session will not be active again until the upgrade is complete.