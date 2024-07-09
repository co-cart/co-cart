<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/trunk/.wordpress-org/banner-772x250.jpg" alt="CoCart. Build headless stores, without building an API" /></p>

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/trunk/LICENSE.md" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-red.svg" alt="Licence">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://poser.pugx.org/co-cart/co-cart/v/stable" alt="Latest Stable Version">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg" alt="WordPress Plugin Downloads">
	</a>
</p>

> ❗ This branch is the current stable version of CoCart. If you are looking to [contribute to CoCart](https://github.com/co-cart/co-cart/blob/dev/.github/CONTRIBUTING.md), please use the "dev" branch.

Welcome to the CoCart repository on GitHub. Here you can browse the source, [look at open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aopen+is%3Aissue) and keep track of development. We recommend all developers to follow the [CoCart development blog](https://cocart.dev/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocartcore) to stay up to date about everything happening in the project. You can also [follow @cocartapi](https://twitter.com/cocartapi) on Twitter for the latest development updates.

If you are looking for documentation, head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocartcore)

[Click here to download](https://downloads.wordpress.org/plugin/cart-rest-api-for-woocommerce.zip) the latest release package of CoCart Core.

### Description

Decouple your WooCommerce store with ease with our developer friendly REST API extension.

[CoCart](https://cocartapi.com/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore) is a **customizable, open-source REST API solution** built for WooCommerce that is ready to decouple away from WordPress, allowing you to design and build without limitations imposed by a WordPress theme that is harder to modify and optimize. Our REST API handles all the server side actions so it can be utilized in **faster and familiar frameworks** that you prefer.

#### Why WooCommerce?

WooCommerce provides a **flexible, and efficient** way to build cutting-edge stores with WordPress at it's core. But it was missing a key component to decouple WooCommerce via the REST API which is where CoCart comes in.

#### Why CoCart?

Is your store slow? Looking to decouple away from WordPress? Overwhelmed with getting WooCommerce working the way you want in your own REST API endpoints?

These are the struggles of web developers trying to decouple a WooCommerce store.

With CoCart, you can overcome these challenges and get straight to utilizing a REST API that is ready for you.

* **Source of Truth** - CoCart sources the WooCommerce's Data Stores API and repeats most WooCommerce hooks to provide a wider array of support for most WooCommerce extensions out of the box.
* **No Cookie Required** - Our session handler generates a key for the user session in the WordPress database and stores session-related meta data for use on the client without the heavy load.
* **Basic Authentication** - No Admin API Keys required. Customers can login with their account either with Email+Password, Username+Password, Phone Number+Password.
* **Domain Dominance** - CORS can be an issue when decoupling so we made sure you have control of the origin.
* **Your Inventory** - Search products by Name, ID or SKU, filter and return product data you need without authentication or private data exposed. REST shortcuts are readily provided for your next requests.
* **Reduced Cart Checkups** - We have avoided the hassle of needing multiple requests to verify item and coupon validity in your cart. Our system efficiently checks stock, calculates totals and fees, ensuring real-time accuracy so your responses return up to date every time.
* **No Headless Checkout?** - Load any cart session via the native site using the generated key, if you feel more comfortable using WooCommerce’s built in payment system.
* **Need your own cart callback?** - Register custom callbacks without needing to create a whole new endpoint. Cart response returns once the callback is completed.
* **Want to track your customers?** - Keep watch of all cart sessions, even the ones that are starting to expire or have expired.
* **Name Your Price Built In** - Give your customers control of the price they pay. Encourage your audience to support you with payment flexibility that widens your paying audience.
* **Bulk Requests** - Combine many cart requests in bulk to save time.

And this is just the tip of the iceberg.

### 📦 Serious about going headless?

Try out more features and unlock your stores potential. Upgrade to complete the API with additional features that help make your store more awesome.

[See what we have in store](https://cocartapi.com/pricing/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore).

## 👍 Add-ons to further enhance CoCart

We also have add-ons that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** enables support for CORS to allow CoCart to work across multiple domains.
* **[CoCart - JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/)** allows you to authenticate via a simple JWT Token.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it.
* and more add-ons in development.

They work with the core of CoCart already, and these add-ons of course come with support too.

## 🧰 Developer Tools

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

## Need Support?

CoCart is not supported via the WooCommerce help desk as the plugin is not sold via Woo.com, the support team at Woo.com is not familiar with it and may not be able to assist.

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our [paying customers](https://cocartapi.com/pricing/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore). Support can also be requested with the [community on Discord](https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore).

## 🐞 Bug Reporting

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart/issues/new). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

## Support CoCart

Please consider starring ✨ and sharing 👍 the project repo! This helps the project get known and grow with the community. 🙏

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

[See the wall of love](https://cocartapi.com/wall-of-love/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore) for more testimonials.

---

## CoCart Channels

We have different channels at your disposal where you can find information about the CoCart project, discuss it and get involved:

[![Twitter: cocartapi](https://img.shields.io/twitter/follow/cocartapi?style=social)](https://twitter.com/cocartapi) [![CoCart Github Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to use CoCart API. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>🧰 <strong>Resources</strong>: this is the hub of all CoCart resources to help you build a headless store. <a href="https://cocart.dev/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore">Get resources!</a></li>
  <li>👪 <strong>Community</strong>: use our Discord chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cocartapi">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cocartapi">here.</a></li>
</ul>

---

## Get involved

Do you like the idea of creating a headless store with WooCommerce? Got questions or feedback? We'd love to hear from you. Come [join our community](https://cocartapi.com/community/?utm_medium=repo&utm_source=github.com&utm_campaign=readme&utm_content=cocartcore)! ❤️

CoCart also welcomes contributions. There are many ways to support the project! If you don't know where to start, this guide might help >> [How to contribute?](https://github.com/co-cart/co-cart/blob/dev/.github/CONTRIBUTING.md)

---

## Credits

Website [cocartapi.com](https://cocartapi.com) &nbsp;&middot;&nbsp;
GitHub [@co-cart](https://github.com/co-cart) &nbsp;&middot;&nbsp;
Twitter [@cocartapi](https://twitter.com/cocartapi)

---

CoCart is developed and maintained by [Sébastien Dumont](https://github.com/seb86).
Founder of [CoCart Headless, LLC](https://github.com/cocart-headless).

Website [sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)
