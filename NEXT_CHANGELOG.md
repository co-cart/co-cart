# Next Changelog for CoCart <!-- omit in toc -->

📢 This changelog is **NOT** final so take it with a grain of salt. Feedback from users while in beta will also help determine the final changelog of the release.

## Table of Contents <!-- omit in toc -->

- [What's New?](#whats-new)
- [Improvements and Tweaks](#improvements-and-tweaks)
- [Security](#security)
  - [Disable WordPress Access](#disable-wordpress-access)
- [API Access](#api-access)
  - [FAQ](#faq)
    - [Why is there a checkbox to enable this feature?](#why-is-there-a-checkbox-to-enable-this-feature)
    - [Wont a developer be still be able find the salt key?](#wont-a-developer-be-still-be-able-find-the-salt-key)
- [Internal Changes](#internal-changes)
- [Developers](#developers)
  - [Monetary values](#monetary-values)
- [Support](#support)

## What's New?

- New settings page:
  - Set the front-end site URL for product permalinks for the Products API.
  - Disable WordPress Access if enabled. Users who are not administrators cannot access the WordPress site and are redirected to "Front-end site URL" instead.
  - Set an Access Token to protect the API's from any unauthorized access unless provided in the headers.
  - Set a Salt Key to secure specific features from outside tampering. If salt key is already set in `wp-config.php` then that will take priority.
  - Set default configurations for the default behaviour when accessing the cart, products and sessions API.

<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/dev/docs/images/cocart-settings.png" alt="CoCart Plugin Settings" /></p>

- Use of Namespaces has now been applied to help extend CoCart, easier to manage and identify for developers.
- Re-organized what routes are allowed to be cached i.e products API rather than prevent all CoCart routes from being cached.
- Added package information of each plugin module to WooCommerce System Status.
- New WP-CLI command `wp cocart status` shows the status of carts in session.
- Added ability to request product variations to return without the parent product. - Solves [[issue 3](https://github.com/co-cart/cocart-products-api/issues/3)]
- Added ability to filter the fields of the endpoint you request before they return, making the response faster.
- Added ability to return the fields in the cart response based on a pre-configured option as alternative to filtering the fields individually. Options: `mini`, `digital`, `digital_fees`, `shipping`, `shipping_fees`, `removed_items` and `cross_sells`
- Added new endpoint to delete all items (only) in the cart.
- Added new endpoint to generate cart key.

## Improvements and Tweaks

- Fetch total count of all carts in session once when displaying the status under "WooCommerce -> Status".
- Monetary values improved and return all as a float value for better compatibility with JS frameworks. [See developers section for more](#developers).
- Caching of same item added to cart no longer loses previous custom price set before and has not changed.

## Security

### Disable WordPress Access

With the new settings page added in this release you can disable access to WordPress with a simple checkbox. You can even redirect to your headless site if you use the "Front-end URL" field which is also used to re-write your permalinks.

By default, both the cart and checkout pages are still accessible to support the feature "Load cart from session" and you can filter the accessible pages using `cocart_accessible_page_ids`.

## API Access

Even though CoCart is an API with public access, outside access can still be an issue. Now you can force the API to be accessible with an "Access Token" which protects the API's from any unauthorized access unless the access token is provided in the headers.

This will help prevent spamming multiple guest carts created if someone has the know how, saving you bandwidth and data resource.

The access token can be generated via the settings page with a confirmation window.

### FAQ

#### Why is there a checkbox to enable this feature?

The reason is so that users have the option of requiring the access token when in production and blocking access when any request is made directly in the browser or REST API tool like Postman without it.

In the future, any SDK's CoCart provides will require the access token. Validating the token requirement will be done within the SDK but the require option still allows users control outside the SDK.

To help with hijacking the price, a salt key can be set via the new settings page or by defining a new constant `COCART_SALT_KEY` in your `wp-config.php` file. This can be anything you wish it to be as long as it's not memorable. It will be encrypted later with **md5** when validated. Once a salt key is set, any request to add item/s to the cart with a new price CoCart will check if the salt key was also passed along. If the salt key does not match then the price will remain the same.

#### Wont a developer be still be able find the salt key?

Possibly. It all depends on how well you have minified your code to hide the fact that you are allowing price override. This is only a means to help slow down the possibility of a session hijack.

## Internal Changes

- Autoload via Composer is now used to help load all modules of the plugin and specific class files without the need to manually include them.
- Inline documentation much improved. Allows for a code reference to be generated per release for developers.

## Developers

- Introduced new filter `cocart_cart_item_extensions` to allow plugin extensions to apply additional information.
- Introduced new filter `cocart_cart_totals` that can be used to change the values returned.
- Introduced new filter `cocart_accessible_page_ids` to allow you to set the page ID's that are still accessible when you block access to WordPress.
- Introduced new filter `cocart_after_get_cart` to allow you to modify the cart contents after it has calculated totals.
- Introduced new action hook `cocart_added_item_to_cart` that allows for additional requested data to be processed via a third party once item is added to the cart.
- Introduced new action hook `cocart_added_items_to_cart` that allows for additional requested data to be processed via a third party once items are added to the cart.

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

## Support

- In order for CoCart to continue working you need to be using WooCommerce 9.0 minimum or higher.
- No longer supporting CoCart API v1. Only bug or security fixes will be provided if any.
