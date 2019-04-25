# CoCart

[![WordPress Plugin Page](https://img.shields.io/badge/WordPress-%E2%86%92-lightgrey.svg?style=flat-square)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)
[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/cart-rest-api-for-woocommerce.svg?style=flat)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/) 
[![WordPress Tested Up To](https://img.shields.io/wordpress/v/cart-rest-api-for-woocommerce.svg?style=flat)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)
[![WordPress Plugin Rating](https://img.shields.io/wordpress/plugin/r/cart-rest-api-for-woocommerce.svg)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#reviews)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)
[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://github.com/co-cart/co-cart/blob/master/LICENSE.md)

**Contributors:** sebd86  
**Donate link:** https://sebdumont.xyz/donate/  
**Tags:** woocommerce, cart, endpoint, JSON, rest, api, REST API  
**Requires at least:** 4.9.8  
**Requires PHP:** 5.6  
**Tested up to:** 5.1.1  
**WC requires at least:** 3.0.0  
**WC tested up to:** 3.6.2  
**Stable tag:** 1.1.1  
**License:** GPL v2 or later  

A REST-API for WooCommerce that enables the ability to add, view, update and delete items from the cart.

## 🔔 Overview

CoCart, also written as co-cart, is a REST API for WooCommerce. Accessing the cart via the REST API was highly requested by mobile and app developers and was missing from the core of WooCommerce.

So I built it. Tada!

It allows you to use WooCommerce’s REST API to its full potential providing the option to create a full web or mobile app 📱 for your store powered by WooCommerce.

### Is This Free?

Yes, it's free. But here's what you should _really_ care about:

* The code adheres to the [WordPress Coding Standards](https://codex.wordpress.org/WordPress_Coding_Standards) and follows best practices and conventions.
* The project is experimental at this time.

> At this time, none of the official WooCommerce library wrappers can be used with this REST API as they all require authentication which makes it difficult to use along with the other official REST API endpoints that WooCommerce provides.

### What's the Catch?

This is a non-commercial plugin. As such:

* Development time for it is effectively being donated and is, therefore, limited.
* Support inquiries may not be answered in a timely manner.
* Critical issues may not be resolved promptly.

If you have a customization/integration requirement then I'd love to [hear from you](mailto:mailme@sebastiendumont.com)!

Please understand that this repository is not a place to seek help with configuration-related issues. Use it to report bugs or propose improvements.

## 📘 Guide

### 📖 Documentation

[View documentation for CoCart](https://co-cart.github.io/co-cart-docs/). Documentation currently only has examples for using with _cURL_.

#### ✅ Requirements

To use this plugin you will need:

* PHP v5.6 minimum (Recommend PHP v7+)
* WordPress v4.9.8 minimum
* WooCommerce v3.0.0+
* Pretty permalinks in Settings > Permalinks so that the custom endpoints are supported. **Default permalinks will not work.**
* You may access the API over either HTTP or HTTPS, but HTTPS is recommended where possible.

#### 💽 Installation

##### Manual

1. Download a `.zip` file with the [latest version](https://github.com/co-cart/co-cart/releases).
2. Go to **WordPress Admin > Plugins > Add New**.
3. Click **Upload Plugin** at the top.
4. **Choose File** and select the `.zip` file you downloaded in **Step 1**.
5. Click **Install Now** and **Activate** the plugin.

##### Automatic

1. Go to **WordPress Admin > Plugins > Add New**.
2. Search for **CoCart**
3. Click **Install Now** on the plugin and **Activate** the plugin.

### Usage

To view the cart endpoint, go to `yourdomainname.xyz/wp-json/wc/v2/cart/`

See [documentation](#-documentation) on how to use all endpoints.

## ⭐ Support

CoCart is released freely and openly. Feedback or ideas and approaches to solving limitations in CoCart is greatly appreciated.

CoCart is not supported via the [WooCommerce Helpdesk](https://woocommerce.com/). As the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

At present, I **do not offer a dedicated, premium support channel** for CoCart but will soon. Please understand this is a non-commercial plugin. As such:

* Development time for it is effectively being donated and is, therefore, limited.
* Support inquiries may not be answered in a timely manner.
* Critical issues may not be resolved promptly.

### 📝 Reporting Issues

If you think you have found a bug in the plugin, a problem with the documentation, or want to see a new feature added, please [open a new issue](https://github.com/co-cart/co-cart/issues/new) and I will do my best to help you out.

## Contribute

If you or your company use CoCart or appreciate the work I’m doing in open source, please consider supporting me directly so I can continue maintaining it and keep evolving the project.

You'll be helping to ensure I can spend the time not just fixing bugs, adding features, releasing new versions, but also keeping the project afloat. Any contribution you make is a big help and is greatly appreciated.

Please also consider starring ✨ and sharing 👍 the project repo! This helps the project getting known and grow with the community. 🙏

I accept one-time donations and monthly via [BuyMeACoffee.com](https://www.buymeacoffee.com/sebastien)

* [My PayPal](https://www.paypal.me/codebreaker)
* [BuyMeACoffee.com](https://www.buymeacoffee.com/sebastien)
* Bitcoin (BTC): `3L4cU7VJsXBFckstfJdP2moaNhTHzVDkKQ`
* Ethereum (ETH): `0xc6a3C18cf11f5307bFa11F8BCBD51F355b6431cB`
* Litecoin (LTC): `MNNy3xBK8sM8t1YUA2iAwdi9wRvZp9yRoi`

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
