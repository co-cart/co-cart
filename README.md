# CoCart
[![WP Plugin Page](https://img.shields.io/badge/WordPress-%E2%86%92-lightgrey.svg?style=flat-square)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)
[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://github.com/co-cart/co-cart/blob/master/LICENSE.md)
[![GitHub forks](https://img.shields.io/github/forks/co-cart/co-cart.svg?style=flat)](https://github.com/co-cart/co-cart/network)
[![WordPress.org downloads](https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)
[![WordPress.org rating](https://img.shields.io/wordpress/plugin/r/cart-rest-api-for-woocommerce.svg)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#reviews)
[![Tweet](https://img.shields.io/twitter/url/http/shields.io.svg?style=social)](https://twitter.com/intent/tweet?text=Enable%20the%20ability%20to%20add,%20view,%20count,%20update%20and%20delete%20items%20from%20the%20cart%20using%20CoCart.%20—&url=https://wordpress.org/plugins/cart-rest-api-for-woocommerce//&via=sebd86&hashtags=WordPress,CoCart)

**Contributors:** sebd86  
**Tags:** woocommerce, cart, endpoint, JSON, rest, api, REST API  
**Requires at least:** 4.4  
**Tested up to:** 5.0.3  
**Requires PHP:** 5.6  
**WC requires at least:** 3.2.0  
**WC tested up to:** 3.5.4  
**Stable tag:** 2.0.0  
**License:** GPL v2 or later  

Provides additional REST API endpoints for WooCommerce to enable the ability to add, view, count, update and delete items from the cart.


## 🔔 Overview

CoCart, also written as co-cart, is a REST API for WooCommerce. Accessing the cart via the REST API was highly requested by mobile and app developers and was missing from the core of WooCommerce.

So I built it. Tada!

It allows you to use WooCommerce’s REST API to its full potential providing the option to create a full web or mobile app 📱 for your store powered by WooCommerce.


### Is This Free?

Yes, it's free. But here's what you should _really_ care about:

* The code adheres to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards) and follows best practices and conventions.

> At this time, none of the official WooCommerce library wrappers can be used with this REST API as they all require authentication which makes it difficult to use along with the other official REST API endpoints that WooCommerce provides.


### What's the Catch?

This is a non-commercial plugin. As such:

* Development time for it is effectively being donated and is, therefore, limited.
* Support inquiries may not be answered in a timely manner.
* Critical issues may not be resolved promptly.

If you have a customization/integration requirement then I'd love to [hear from you](mailto:mailme@sebastiendumont.com)!

Please understand that this repository is not a place to seek help with configuration-related issues. Use it to report bugs or propose improvements.

## 📘 Guide

#### 📖 Documentation

> Documentation for CoCart is a working progress.

Documentation for [CoCart](https://co-cart.github.io/co-cart-docs/)<br>
The official [WooCommerce REST API Documentation](https://woocommerce.github.io/woocommerce-rest-api-docs/)


#### ✅ Requirements

To use this plugin you will need:

* PHP v5.6+ (Recommend PHP v7.0+)
* WordPress v4.4+
* WooCommerce v3.0.0+
* Pretty permalinks in Settings > Permalinks so that the custom endpoints are supported. **Default permalinks will not work.**
* You may access the API over either HTTP or HTTPS, but HTTPS is recommended where possible.


#### 💽 Installation

###### Manual
1. Download a `.zip` file with the [latest version](https://github.com/co-cart/co-cart/releases).
2. Go to **WordPress Admin > Plugins > Add New**.
3. Click **Upload Plugin** at the top.
4. **Choose File** and select the `.zip` file you downloaded in **Step 1**.
5. Click **Install Now** and **Activate** the plugin.

###### Automatic
1. Go to **WordPress Admin > Plugins > Add New**.
2. Search for **CoCart**
3. Click **Install Now** on the plugin and **Activate** the plugin.


### Usage

To view the cart endpoint, go to `yourdomainname.xyz/wp-json/wc/v2/cart/`

See [documentation](#-documentation) on how to use all endpoints.


## 🚀 CoCart Pro
Want to control more? _I bet you do._

* Add and Remove Coupons to Cart<br />
* Calculate Shipping Fees<br />
* Calculate Totals and Fees<br />
* Support via Slack<br />
* and possibly more features and add-ons to follow.<br />

[Sign up if you are interested in CoCart Pro](http://eepurl.com/dKIYXE)


## ⭐ Support

CoCart is released freely and openly. Feedback or ideas and approaches to solving limitations in CoCart is greatly appreciated.

CoCart is not supported via the [WooCommerce Helpdesk](https://woocommerce.com/). As the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

At present, I **do not offer a dedicated, premium support channel** for CoCart but will soon. Please understand this is a non-commercial plugin. As such:

* Development time for it is effectively being donated and is, therefore, limited.
* Support inquiries may not be answered in a timely manner.
* Critical issues may not be resolved promptly.

#### 📝 Reporting Issues

If you think you have found a bug in the plugin, a problem with the documentation, or want to see a new feature added, please [open a new issue](https://github.com/co-cart/co-cart/issues/new) and I will do my best to help you out.


## Contribute

If you or your company use CoCart or appreciate the work I’m doing in open source, please consider supporting me directly so I can continue maintaining it and keep evolving the project. It's pretty clear that software actually costs something, and even though it may be offered freely, somebody is paying the cost.

You'll be helping to ensure I can spend the time not just fixing bugs, adding features, releasing new versions, but also keeping the project afloat. Any contribution you make is a big help and is greatly appreciated.

Please also consider starring ✨ and sharing 👍 the repo! This helps the project getting known and grow with the community. 🙏

If you want to do a one-time donation, you can donate to:
- [My PayPal](https://www.paypal.me/codebreaker)
- [BuyMeACoffee.com](https://www.buymeacoffee.com/sebastien)

<!--
Need to work on how to support monthly donations. Once I have figured it out, share details here.
-->
If you have special requirements for a sponsorship, you can [email me](mailto:mailme@sebastiendumont.com) and we can talk.

<!--
Uncomment this part once the project has a least one supporter.
[See all my amazing supports](#supporters) 🌟
-->

If you would like to contribute code to this project then please follow these [contribution guidelines](https://github.com/co-cart/co-cart/blob/master/CONTRIBUTING.md).

Thank you for your support! 🙌

<!--
## Supporters

> No supporters yet! 🔒
-->

---


##### License

CoCart is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).


##### Credits

CoCart is developed and maintained by [Sébastien Dumont](https://github.com/seb86).

---

<p align="center">
	<img src="https://raw.githubusercontent.com/seb86/my-open-source-readme-template/master/a-sebastien-dumont-production.png" width="353">
</p>
