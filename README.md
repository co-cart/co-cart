# WooCommerce Cart REST-API
[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://github.com/seb86/WooCommerce-Cart-REST-API/blob/master/LICENSE)
![GitHub forks](https://img.shields.io/github/forks/seb86/WooCommerce-Cart-REST-API.svg?style=flat)
[![Maintainability](https://api.codeclimate.com/v1/badges/cb9aabd1f1e93dbe2d9c/maintainability)](https://codeclimate.com/repos/5a621ca0b44b2f029600151c/maintainability)

Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, count, update and delete items from the cart.

> This project is currently in development so please be aware that there may be bugs. [See bugs](https://github.com/seb86/WooCommerce-Cart-REST-API#bugs) on how to report.

### Endpoints

The **namespace** has been set to `wc/v2` to match with the current WooCommerce REST API version so when this API is used it does not confuse developers. It also registers before WooCommerce endpoints so the routes are in alphabetical order.

* View Cart - ```/wc/v2/cart``` - **TESTING**
* Clear Cart - ```/wc/v2/cart/clear``` - **Complete**
* Count Items in Cart - ```/wc/v2/cart/count-items``` - **TESTING**
* Add Item to Cart - ```/wc/v2/cart/add``` - **NEEDS TESTING**
* Calculate Cart Totals - ```/wc/v2/cart/calculate-totals``` - **Complete**
* Get Cart Totals - ```/wc/v2/cart/totals``` - **Complete**

The following are the same for ```/wc/v2/cart/%cart_item_id%```:
* Remove Item from Cart - **NEEDS TESTING**
* Restore Item to Cart - **NEEDS TESTING**
* Update Item in Cart - **NEEDS TESTING**

### To Do
* [ ] Complete all endpoints.
* [ ] Validate each endpoints.
* [ ] Release on WordPress.org
* [ ] Create documentation.
* [ ] Get the response messages translated. POT file ready and available.

### Testing or Support Needed

Below is a list of extensions that require testing or adding support.

* [ ] Subscriptions (including Subscribe All the Things)
* [ ] Bookings
* [ ] Product Bundles
* [ ] Composite Products
* [ ] Mix and Match Products
* [ ] Name Your Price
* [ ] Product Add-ons

### Requirements
* WooCommerce v3.0.0+
* WordPress v4.4+
* Pretty permalinks in Settings > Permalinks so that the custom endpoints are supported. **Default permalinks will not work.**
* You may access the API over either HTTP or HTTPS, but HTTPS is recommended where possible.

If you use ModSecurity and see 501 Method Not Implemented errors, see [this issue](https://github.com/woocommerce/woocommerce/issues/9838) for details.

### Bugs
If you find an issue, [create an issue](https://github.com/seb86/WooCommerce-Cart-REST-API/issues?state=open). You can also send a pull request with your bug fixes and/or new features.

### Support Sébastien's Open Source Projects!
If you'd like me to keep producing free and open source software or if you use this plugin and find it useful then please consider [paying for an hour](https://www.paypal.me/CodeBreaker/100eur) of my time. I'll spend two hours on open source for each contribution.

You can find more of my Free and Open Source plugins on [GitHub](https://github.com/seb86)
