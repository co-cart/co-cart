<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/trunk/.wordpress-org/banner-772x250.jpg" alt="CoCart. Build headless stores, without building an API" /></p>

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/trunk/LICENSE.md" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-red.svg" alt="Licence">
	</a>
	<a href="https://wordpress.org/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=5.6+-+6.2&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
	</a>
	<a href="https://woocommerce.com/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=4.3+-+7.8&color=96588A&style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce Versions">
	</a>
	<a href="https://www.php.net/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=7.3+-+8.0&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
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

> ❗ This branch is the current stable version of CoCart. If you are looking to [contribute to CoCart](https://github.com/co-cart/co-cart/blob/dev/.github/CONTRIBUTING.md), please use the "dev" branch.

Welcome to the CoCart Lite repository on GitHub. Here you can browse the source, [look at open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aopen+is%3Aissue) and keep track of development. We recommend all developers to follow the [CoCart development blog](https://cocart.dev/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) to stay up to date about everything happening in the project. You can also [follow @cocartapi](https://twitter.com/cocartapi) on Twitter for the latest development updates.

If you are looking for documentation, head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)

[Click here to download](https://downloads.wordpress.org/plugin/cart-rest-api-for-woocommerce.zip) the latest release package of CoCart Lite.

## ✨ Core Features

CoCart's core features provides everything you need to use with any modern framework you desire.

* Override the price for simple or variable products added to cart.
* Attach customers email address while adding an item to the cart. **Useful for abandoned cart situations.**
* Load a cart in session via the web. **Useful if you don't have a headless checkout and want to use native checkout.**
* Supports guest customers.
* Supports **basic authentication** including the use of email as username.
* Supports multi-sites.
* Does not cache API so responses are fast.
* Works across multiple domains, CORS ready **so you can have multiple front-ends connected to one backend**.
* Can be white-labelled for your clients.

## The API

CoCart is optimized for performance and designed for developers that provides support out-of-the-box experience that manages the cart sessions for both guest and registered customers without the need of being on the same origin as the WordPress installation.

🛒 Cart API

The cart is the main feature of CoCart that provides the ability to add, update, remove or even restore items individually or in bulk and more.

The flow is simple and returns an updated cart response every time with the totals calculated and stock checks done for you, making it easier to simply update your UX/UI with the results.

🛍️ Products API

Products can be accessed from your store to display how you like by using the queries to help filter by product categories, tags, attributes and much more all without the need to authenticate with WooCommerce REST API Keys.

All the information you need about a product and it’s conditions to help you with your UX/UI development is all provided ready for you.

➕ Extras

Additional API's are provided to help with your user actions as well as debugging.

 - Get store information.
 - Login the user. **Required if you are using the JWT Authentication Addon**
 - Logout the user.
 - Empty the cart.

🧮 Sessions API

Administrators have the capabilities to:

 - View all carts in session.
 - Get details of a cart in session.
 - View items in a cart session.

## CoCart Pro

The core of CoCart is just the tip of the iceberg. [CoCart Pro](https://cocart.xyz/pro/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) enhances the headless experience with these additional [features](https://cocart.xyz/features/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart):

* Add and Remove Coupons to/from Cart
* Retrieve Coupon Discount Total
* Retrieve and Set Payment Method
* Retrieve and Set Shipping Methods
* Retrieve and Set Fees
* Calculate Shipping Fees
* Calculate Totals and Fees

More features are in development and will be available soon.

[Buy CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)

## Add-ons

We also have add-ons that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** simply filters the session cookie to allow CoCart to work across multiple domains. - **FREE**
* **[CoCart - JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/)** allows you to authenticate via a simple JWT Token. - **FREE**
* and more add-ons in development.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it. - **FREE**

They work with the FREE version of CoCart already, and these add-ons of course come with support too.

## Developers

CoCart Lite is full of **[filters](https://docs.cocart.xyz/#filters?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** and **[action hooks](https://docs.cocart.xyz/#actions?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)** for developers to use as they please. It's your store so tinker how you please.

Here are a few other resources you find helpful.

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart Lite from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.

## Need Support?

CoCart Lite is released freely and openly. Feedback or ideas and approaches to solving limitations in CoCart Lite is greatly appreciated.

CoCart Lite is not supported via the WooCommerce help desk as the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

If you are in need of support, please [purchase CoCart Pro](https://cocart.xyz/pro/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart).

## Bug Reporting

If you think you have found a bug in the plugin, please [open a new issue](https://github.com/co-cart/co-cart/issues/new) and I will do my best to help you out.

## Support CoCart

Please consider starring ✨ and sharing 👍 the project repo! This helps the project getting known and grow with the community. 🙏

Thank you for your support! 🙌

## Testimonials

> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time.
>
> Harald Schneider ⭐️⭐️⭐️⭐️⭐️

> What can I say this thing has it all. It is the “Missing WooCommerce REST API plugin” without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.
>
> **[Joel Pierre](https://github.com/joelpierre)** – JPPdesigns Web design & Development ⭐️⭐️⭐️⭐️⭐️

> This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.
>
> Thank you very much Sébastien for sharing this extension, you’ve saved me a lot of time.
>
> **Allan Pooley** – Little and Big ⭐️⭐️⭐️⭐️⭐️

> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.
>
> **MightyGroup** – Rikard Kling ⭐️⭐️⭐️⭐️⭐️

[See the wall of love](https://cocart.xyz/wall-of-love/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart) for more testimonials.

---

## CoCart Channels

We have different channels at your disposal where you can find information about the CoCart project, discuss it and get involved:

[![Twitter: cocartapi](https://img.shields.io/twitter/follow/cocartapi?style=social)](https://twitter.com/cocartapi) [![CoCart Github Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to use CoCart API. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>🧰 <strong>Resources</strong>: this is the hub of all CoCart resources to help you build a headless store. <a href="https://cocart.dev/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart">Get resources!</a></li>
  <li>👪 <strong>Community</strong>: use our Discord chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cocartapi">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cocartapi">here.</a></li>
</ul>

---

## Get involved

Do you like the idea of creating a headless e-commerce with WooCommerce? Got questions or feedback? We'd love to hear from you. Come join our [community](https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)! ❤️

CoCart Lite also welcomes contributions. There are many ways to support the project! If you don't know where to start, this guide might help >> [How to contribute?](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md)

---

## Credits

CoCart Lite is developed and maintained by [Sébastien Dumont](https://github.com/seb86).

Founder of CoCart - [Sébastien Dumont](https://github.com/seb86).

---

Website [sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)

<p align="center">
    <img src="https://raw.githubusercontent.com/seb86/my-open-source-readme-template/master/a-sebastien-dumont-production.png" width="353">
</p>
