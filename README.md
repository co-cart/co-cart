<h1 align="center">CoCart Lite <a href="https://github.com/co-cart/co-cart/releases/latest/"><img src="https://img.shields.io/static/v1?goVersion=&message=v3.0.0-rc.3&label=&color=9a6fc4&style=flat-square"></a></h1>

<p align="center"><a href="https://cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart" target="_blank">CoCart</a> is a RESTful API made for <a href="https://woocommerce.com" target="_blank">WooCommerce</a>.</p>
<p>It focuses on <strong>the front-end</strong> of the store helping you to <strong>manage shopping carts</strong> and allows developers to build a headless store in any framework of their choosing.</p>
<p>No local storing required. A powerful RESTful API that offers an integration to build your headless store with ease.</p>

<p align="center">
	<a href="https://wordpress.org/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=5.4+-+5.7&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
	</a>
	<a href="https://woocommerce.com/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=4.3+-+5.2&color=96588A&style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce Versions">
	</a>
	<a href="https://www.php.net/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=7.0+-+8.0&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://poser.pugx.org/co-cart/co-cart/v/stable" alt="Latest Stable Version">
	</a>
	<a href="https://scrutinizer-ci.com/g/co-cart/co-cart/" target="_blank">
		<img src="https://scrutinizer-ci.com/g/co-cart/co-cart/badges/quality-score.png?b=master" alt="Quality Score on Master branch" />
	</a>
</p>

<p align="center">
	<a href="#the-api">The API</a>
	&nbsp;|&nbsp;
	<a href="#cocart-pro">CoCart Pro</a>
	&nbsp;|&nbsp;
	<a href="#add-ons">Add-ons</a>
	&nbsp;|&nbsp;
	<a href="#download">Download</a>
	&nbsp;|&nbsp;
	<a href="#developers">Developers</a>
	&nbsp;|&nbsp;
	<a href="#testimonials">Testimonials</a>
	&nbsp;|&nbsp;
	<a href="#credits">Credits</a>
	&nbsp;|&nbsp;
	<a href="#license">License</a>
</p>

<br>

<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/master/.github/Logo-1024x534.png.webp" alt="CoCart" /></p>

## Looking for documentation?

Head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)

## The API

CoCart Lite provides the basic API needs to get you started.

* Get store information.
* Add simple, variable and grouped products to the cart.
* Get customers cart.
* Get customers cart contents.
* Update items in the cart.
* Remove items from the cart.
* Restore items to the cart.
* Re-calculate the totals.
* Retrieve the cart totals.
* Retrieve the number of items in cart or items removed from it.
* Empty the cart.
* Login the customer/user.
* Logout the customer/user.

As an added bonus for administrators or shop managers, CoCart Lite also provides the capabilities to:

* Get Carts in Session.
* Get details of a cart in session.
* View items added in a cart in session.
* Delete a Cart in Session.

## Features

CoCart also provides built in features to:

* Load a cart in session via the web.
* Supports guest customers.
* Supports basic authentication including the use of email as username.
* Supports [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart).
* Supports multi-sites.
* Works across multiple domains, CORS ready (so you can have multiple frontends connected to one backend).
* Allows you to filter CoCart to be white-labelled.

## CoCart Pro

CoCart Lite is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) completes it with the following [features](https://cocart.xyz/features/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart):

* **Plugin Updates** for 1 year.
* **Priority Support** for [CoCart Pro](https://cocart.xyz/pro/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) users via Slack.
* Add and Remove Coupons to/from Cart
* Retrieve Applied Coupons
* Retrieve Coupon Discount Total
* Retrieve Cart Total Weight
* Retrieve Cross Sells
* Retrieve and Set Payment Method
* Retrieve and Set Shipping Methods
* Retrieve and Set Fees
* Calculate Shipping Fees
* Calculate Totals and Fees

Features that will be available in the future:

* **Coming Soon** Remove All Coupons from Cart
* **Coming Soon** Register Customers
* **Coming Soon** Retrieve Checkout Fields
* **Coming Soon** Set Cart Customer (In Development)
* **Coming Soon** Create Order (In Development)

For logged in customers:

* **Coming Soon** Return Customers Orders
* **Coming Soon** Return Customers Subscriptions
* **Coming Soon** Return Customers Downloads (Auditing)
* **Coming Soon** Return Customers Payment Methods (Auditing)
* **Coming Soon** Get and Update Customers Profile (In Development)

[Buy CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)

## Add-ons

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin. - **FREE**
* **[CORS](https://wordpress.org/plugins/cocart-cors/)** simply filters the session cookie to allow CoCart to work across multiple domains. - **FREE**
* **[Get Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the cart response returned with the cart totals, coupons applied, additional product details and more. One response for all. - **FREE**
* **[Products](https://cocart.xyz/add-ons/products/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** provides a public and better version of WooCommerce REST API for accessing products, categories, tags, attributes and even reviews without the need to authenticate.
* **[Advanced Custom Fields](https://cocart.xyz/add-ons/advanced-custom-fields/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning all your advanced custom fields for products. - **REQUIRES COCART PRODUCTS**
* **[Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** extends CoCart Products add-on by returning Yoast SEO data for products, product categories and product tags. - **REQUIRES COCART PRODUCTS**
* and more add-ons in development.

They work with the FREE version of CoCart already, and these add-ons of course come with support too.

## Download

[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg)](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/)

[Click here to download](https://downloads.wordpress.org/plugin/cart-rest-api-for-woocommerce.zip) the latest release package of CoCart.

## Developers

CoCart is full of **[filters](https://docs.cocart.xyz/#filters?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** and **[action hooks](https://docs.cocart.xyz/#hooks?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** for developers to use as they please. It's your store so tinker how you please.

* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart from the GitHub repo.

## Need Support?

CoCart is released freely and openly. Feedback or ideas and approaches to solving limitations in CoCart is greatly appreciated.

CoCart is not supported via the [WooCommerce Helpdesk](https://woocommerce.com/). As the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

If you are in need of support, please [purchase CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart).

## Bug Reporting

If you think you have found a bug in the plugin, please [open a new issue](https://github.com/co-cart/co-cart/issues/new) and I will do my best to help you out.

## Support CoCart

If you or your company use CoCart or appreciate the work I’m doing in open source, please consider donating via one of the links available on right hand side under "**Sponsor this project**" or [purchasing CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart) where you not just get the full cart experience but also support me directly so I can continue maintaining CoCart and keep evolving the project.

Please also consider starring ✨ and sharing 👍 the project repo! This helps the project getting known and grow with the community. 🙏

Thank you for your support! 🙌

## Testimonials

What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.

**[Joel Pierre](https://github.com/joelpierre)** – JPPdesigns Web design & Development ⭐️⭐️⭐️⭐️⭐️

***

This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.

Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.

**Allan Pooley** – Little and Big ⭐️⭐️⭐️⭐️⭐️

***

Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.

**MightyGroup** – Rikard Kling ⭐️⭐️⭐️⭐️⭐️

[See our wall of love](https://cocart.xyz/wall-of-love/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart) for more testimonials.

---

## CoCart Channels

We have different channels at your disposal where you can find information about the CoCart project, discuss it and get involved:

[![Twitter: cart_co](https://img.shields.io/twitter/follow/cart_co?style=social)](https://twitter.com/cart_co) [![CoCart Github Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to build amazing sites with CoCart. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>👪 <strong>Community</strong>: use our Slack chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocart.xyz/community/">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cart_co">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cart_co">here.</a></li>
</ul>

## Get involved

Do you like the idea of creating a headless ecommerce with WooCommerce? Got questions or feedback? We'd love to hear from you. Come join our [community](https://cocart.xyz/community/)! ❤️

CoCart also welcomes contributions. There are many ways to support the project (and get free swag)! If you don't know where to start, this guide might help >> [How to contribute?](https://github.com/co-cart/co-cart/blob/master/.github/CONTRIBUTING.md).

---

## Contributors

This project exists thanks to all the people who contribute. [[Contribute](https://github.com/co-cart/co-cart/blob/master/.github/CONTRIBUTING.md)].
<a href="https://github.com/co-cart/co-cart/graphs/contributors"><img src="https://opencollective.com/cocart/contributors.svg?width=890&button=false" /></a>

## License

[![License](https://img.shields.io/badge/license-GPL--3.0%2B-red.svg)](https://github.com/co-cart/co-cart/blob/master/LICENSE.md)

CoCart is released under [GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html).

## Credits

CoCart is developed and maintained by [Sébastien Dumont](https://github.com/seb86).

---

[sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)

<p align="center">
    <img src="https://raw.githubusercontent.com/seb86/my-open-source-readme-template/master/a-sebastien-dumont-production.png" width="353">
</p>
