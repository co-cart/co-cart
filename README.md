<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/master/.wordpress-org/banner-772x250.jpg" alt="CoCart. Build headless stores, without building an API" /></p>

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/master/LICENSE.md" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-red.svg" alt="Licence">
	</a>
	<a href="https://wordpress.org/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=5.5+-+5.7&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
	</a>
	<a href="https://woocommerce.com/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=4.3+-+5.5&color=96588A&style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce Versions">
	</a>
	<a href="https://www.php.net/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=7.0+-+8.0&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://poser.pugx.org/co-cart/co-cart/v/stable" alt="Latest Stable Version">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg" alt="WordPress Plugin Downloads">
	</a>
</p>

<p align="center">
	<a href="#the-api">The API</a>
	&nbsp;|&nbsp;
	<a href="#cocart-pro">CoCart Pro</a>
	&nbsp;|&nbsp;
	<a href="#add-ons">Add-ons</a>
	&nbsp;|&nbsp;
	<a href="#developers">Developers</a>
	&nbsp;|&nbsp;
	<a href="#testimonials">Testimonials</a>
	&nbsp;|&nbsp;
	<a href="#credits">Credits</a>
</p>

<br>

Welcome to the CoCart Lite repository on GitHub. Here you can browse the source, [look at open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aopen+is%3Aissue) and keep track of development. We recommend all developers to follow the [CoCart development blog](https://cocart.dev/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) to stay up to date about everything happening in the project. You can also [follow @cocartapi](https://twitter.com/cocartapi) on Twitter for the latest development updates.

If you are looking for documentation, head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)

[Click here to download](https://downloads.wordpress.org/plugin/cart-rest-api-for-woocommerce.zip) the latest release package of CoCart Lite.

<br>

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

Also included is the ability to access products, product categories, product tags, product attributes and 
even reviews without the need to authenticate.

* Return all published products.
* Return an individual product by product ID / SKU ID.
* Return an individual variable product and all it’s variations in one request.
* Return all product categories.
* Return all product tags.
* Return all product attributes.
* Return a product attribute terms.
* Return all product reviews.
* Return an individual product review.
* Create a product review.

As an added bonus for administrators or shop managers, CoCart Lite also provides the capabilities to:

* Get Carts in Session.
* Get details of a cart in session.
* View items added in a cart in session.
* Delete a Cart in Session.

## Features

CoCart Lite also provides built in features to:

* **NEW**: Override price for item added to cart.
* **NEW**: Attach customers email address while adding an item to the cart. (Useful for abandoned cart situations.)
* Load a cart in session via the web.
* Supports guest customers.
* Supports basic authentication including the use of email as username.
* Supports [authentication via WooCommerce's method](https://cocart.xyz/authenticating-with-woocommerce-heres-how-you-can-do-it/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart).
* Supports multi-sites.
* Does not cache API so responses are fast.
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
* **Coming Soon** Retrieve Checkout Fields (More details on that soon)
* **Coming Soon** Set Cart Customer (In Development)
* **Coming Soon** Create Order (In Development)

For logged in customers:

* **Coming Soon** Return Customers Orders
* **Coming Soon** Return Customers Subscriptions
* **Coming Soon** Return Customers Downloads (Auditing)
* **Coming Soon** Return Customers Payment Methods
* **Coming Soon** Get and Update Customers Profile (In Development)

[Buy CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)

## Add-ons

We also have **[add-ons](https://cocart.xyz/add-ons/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)** that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** simply filters the session cookie to allow CoCart to work across multiple domains. - **FREE**
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it. - **FREE**
* **[Advanced Custom Fields](https://cocart.xyz/add-ons/advanced-custom-fields/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** extends the products API by returning all your advanced custom fields for products.
* **[Yoast SEO](https://cocart.xyz/add-ons/yoast-seo/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** extends the products API by returning Yoast SEO data for products, product categories and product tags.
* and more add-ons in development.

They work with the FREE version of CoCart Lite already, and these add-ons of course come with support too.

## Developers

CoCart Lite is full of **[filters](https://docs.cocart.xyz/#filters?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** and **[action hooks](https://docs.cocart.xyz/#actions?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** for developers to use as they please. It's your store so tinker how you please.

Here are a few other resources you find helpful.

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart Lite from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart - Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

## Need Support?

CoCart Lite is released freely and openly. Feedback or ideas and approaches to solving limitations in CoCart Lite is greatly appreciated.

CoCart Lite is not supported via the [WooCommerce Helpdesk](https://woocommerce.com/). As the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

If you are in need of support, please [purchase CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart).

## Bug Reporting

If you think you have found a bug in the plugin, please [open a new issue](https://github.com/co-cart/co-cart/issues/new) and I will do my best to help you out.

## Support CoCart

If you or your company use CoCart Lite or appreciate the work I’m doing in open source, please consider donating via one of the links available on right hand side under "**Sponsor this project**" or [purchasing CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart) where you not just get the full cart experience but also support me directly so I can continue maintaining CoCart and keep evolving the project.

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

[![Twitter: cocartapi](https://img.shields.io/twitter/follow/cocartapi?style=social)](https://twitter.com/cocartapi) [![CoCart Github Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to use CoCart API. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>👪 <strong>Community</strong>: use our Slack chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cocartapi">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cocartapi">here.</a></li>
</ul>

---

## Get involved

Do you like the idea of creating a headless ecommerce with WooCommerce? Got questions or feedback? We'd love to hear from you. Come join our [community](https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)! ❤️

CoCart Lite also welcomes contributions. There are many ways to support the project (and get free swag)! If you don't know where to start, this guide might help >> [How to contribute?](https://github.com/co-cart/co-cart/blob/master/.github/CONTRIBUTING.md)

---

## Credits

CoCart Lite is developed and maintained by [Sébastien Dumont](https://github.com/seb86).

---

[sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)

<p align="center">
    <img src="https://raw.githubusercontent.com/seb86/my-open-source-readme-template/master/a-sebastien-dumont-production.png" width="353">
</p>
