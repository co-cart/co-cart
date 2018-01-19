# WooCommerce Cart REST-API
Provides additional REST-API endpoints for WooCommerce to enable the ability to add, view, count, update and delete items from the cart.

> Development on this project has started. Yeah! :smile:

### Endpoints

This is how I see the endpoints. The **namespace** has been set to `wc/v2` to match with the current WooCommerce REST API version so when this API is used it does not confuse developers. It also registers before WooCommerce endpoints so the routes are in alphabetical order.

* View Cart - ```/wc/v2/cart``` - **TESTING**
* Clear Cart - ```/wc/v2/cart/clear``` - **TESTING**
* Count Items in Cart - ```/wc/v2/cart/count-items``` - **TESTING**
* Add Item to Cart - ```/wc/v2/cart/add``` - **NEEDS TESTING**
* Update Cart - ```/wc/v2/cart/update``` - **CALLBACK FUNCTION TO CREATE**
* Remove Item from Cart - ```/wc/v2/cart/remove/%cart_item_id%``` - **CALLBACK FUNCTION TO CREATE**
* Restore Item to Cart - ```/wc/v2/cart/restore/%cart_item_id%``` - **CALLBACK FUNCTION TO CREATE**
* Update Item in Cart - ```/wc/v2/cart/update/%cart_item_id%``` - **CALLBACK FUNCTION TO CREATE**
* Calculate Cart Totals - ```/wc/v2/cart/calculate-totals``` - **CALLBACK FUNCTION TO CREATE**

### To Do
* Complete all endpoints.
* Validate each endpoints.
* Create Storefront Child-Theme using WC REST-API for demonstration.

### Testing or Support Needed

Below is a list of extensions that require testing or adding support.

* Subscriptions (including Subscribe All the Things)
* Bookings
* Product Bundles
* Composite Products
* Mix and Match Products
* Name Your Price
* Product Add-ons

### Support Sébastien's Open Source Projects!
If you'd like me to keep producing free and open source software or if you use this plugin and find it useful then please consider [paying for an hour](https://www.paypal.me/CodeBreaker/100eur) of my time. I'll spend two hours on open source for each contribution.

You can find more of my Free and Open Source plugins on [GitHub](https://github.com/seb86)
