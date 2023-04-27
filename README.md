<p align="center"><img src="https://raw.githubusercontent.com/co-cart/co-cart/trunk/.wordpress-org/banner-772x250.jpg" alt="CoCart. Build headless stores, without building an API" /></p>

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/trunk/LICENSE.md" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-blue.svg" alt="Licence">
	</a>
	<a href="https://wordpress.org/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=5.6+-+6.2&color=blue&style=flat-square&logo=wordpress&logoColor=white" alt="WordPress Versions">
	</a>
	<a href="https://woocommerce.com/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=6.9+-+7.6&color=96588A&style=flat-square&logo=woocommerce&logoColor=white" alt="WooCommerce Versions">
	</a>
	<a href="https://www.php.net/" target="_blank">
		<img src="https://img.shields.io/static/v1?label=&message=7.4+-+8.0&color=777bb4&style=flat-square&logo=php&logoColor=white" alt="PHP Versions">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://poser.pugx.org/co-cart/co-cart/v/stable" alt="Latest Stable Version">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://img.shields.io/wordpress/plugin/dt/cart-rest-api-for-woocommerce.svg" alt="WordPress Plugin Downloads">
	</a>
	<a href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">
		<img src="https://img.shields.io/wordpress/plugin/r/cart-rest-api-for-woocommerce.svg" alt="WordPress.org rating">
	</a>
</p>

⚠️ This is the development branch for future version of CoCart. For current stable branch [browse trunk](https://github.com/co-cart/co-cart/tree/trunk). ⚠️

Welcome to the CoCart repository on GitHub. Here you can browse the source of the plugin and packages used in the development of the core of CoCart plugin. You can [look at open issues](https://github.com/co-cart/co-cart/issues?q=is%3Aopen+is%3Aissue), contribute code and keep track of ongoing development.

We recommend all developers to follow the [CoCart development blog](https://cocart.dev/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart) to stay up to date about everything happening in the project. You can also [follow @cocartapi](https://twitter.com/cocartapi) on Twitter for the latest development updates.

If you are looking for documentation, head over here: [https://docs.cocart.xyz](https://docs.cocart.xyz/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=cocart)

[Click here to download](https://downloads.wordpress.org/plugin/cart-rest-api-for-woocommerce.zip) the latest release package of CoCart.

<br>

## What does it provide?

CoCart provides support for managing the user session, alternative options for doing this task do exist; however, their usage can be limited to applications of the same origin as the WordPress installation. This is due to WooCommerce using cookies to store user session tokens.

CoCart provides the utilities to change this behavior during any cart request and passes the required information to HTTP Header so it can be cached client-side. The use of an HTTP Authorization header is optional allowing users to shop as a guest.

## Quick Start

📢 This repo is not package ready and must be **built** in order to activate the plugin.

### Step 1

Clone the repo to your WordPress development `wp-content/plugins` folder. Don't forget the folder name `"cocart-dev"` at the end of the command.

```
git clone https://github.com/co-cart/co-cart.git cocart-dev
```

### Step 2

Then go into the cloned folder `cd cocart-dev` and proceed with the following commands.

```
composer install
npm install
composer ready-build
```

Now you will have another folder `cocart` within your plugins folder. This makes CoCart package ready. You will then be able to activate it from your `WordPress Dashboard > Plugins`.

If you have made changes to the core of CoCart and want to test those changes locally, simply run `composer ready-build` and CoCart will be packaged together for you again.

## Updating CoCart Packages

As CoCart is now built modular, to keep up to date with all the development changes in all the default set modules you will need to pull them from their individual repositories.

```
composer update
composer ready-build
```

This will pull the `master` branch of each package which is the development branch unless a tag is specified instead.

## External Packages

[Are you looking to build your own package?](https://github.com/co-cart/co-cart/blob/dev/plugins/cocart/packages/README.md)

## For Developers

Here are a few other resources you may find helpful.

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

## Support

This repository is not suitable for support. Please don't use our issue tracker for support requests, but for core CoCart issues only. Support can take place through the appropriate channels:

* [Our community forum on wp.org](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/) which is available for all CoCart users.
* [Our community Slack chat room](https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart) on the **#support** channel.
* [Our community group on Facebook](https://www.facebook.com/groups/cocart/)
* [Our community on Reddit](https://www.reddit.com/r/cocartheadless/)

Support requests in issues on this repository will be closed on sight.

> CoCart is not supported via the WooCommerce help desk as the plugin is not sold via WooCommerce.com, the support team at WooCommerce.com is not familiar with it and may not be able to assist.

## Roadmap

Check out the [roadmap](https://cocart.dev/roadmap/) to get informed of the latest features released and the upcoming ones.

---

## CoCart Channels

We have different channels at your disposal where you can find information about the CoCart project, discuss it and get involved:

[![Twitter: cocartapi](https://img.shields.io/twitter/follow/cocartapi?style=social)](https://twitter.com/cocartapi) [![CoCart Github Stars](https://img.shields.io/github/stars/co-cart/co-cart?style=social)](https://github.com/co-cart/co-cart)

<ul>
  <li>📖 <strong>Docs</strong>: this is the place to learn how to use CoCart API. <a href="https://docs.cocart.xyz/#getting-started">Get started!</a></li>
  <li>🧰 <strong>Resources</strong>: this is the hub of all CoCart resources to help you build a headless store. <a href="https://cocart.dev/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart">Get resources!</a></li>
  <li>👪 <strong>Community</strong>: use our Slack chat room to share any doubts, feedback and meet great people. This is your place too to share <a href="https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart">how are you planning to use CoCart!</a></li>
  <li>🐞 <strong>GitHub</strong>: we use GitHub for bugs and pull requests, doubts are solved with the community.</li>
  <li>🐦 <strong>Social media</strong>: a more informal place to interact with CoCart users, reach out to us on <a href="https://twitter.com/cocartapi">Twitter.</a></li>
  <li>💌 <strong>Newsletter</strong>: do you want to receive the latest plugin updates and news? Subscribe <a href="https://twitter.com/cocartapi">here.</a></li>
</ul>

---

## Contributing to CoCart

If you have a patch or have stumbled upon an issue with CoCart (Core), you can contribute this back to the code. Please read our [contributor guidelines](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) for more information how you can do this.

### Join the Community

Do you like the idea of creating a headless e-commerce with WooCommerce? Have questions or feedback? We'd love to hear from you. Come join the CoCart [community](https://cocart.xyz/community/?utm_medium=gh&utm_source=github&utm_campaign=readme&utm_content=cocart)! ❤️

---

## Credits

CoCart is developed and maintained by [Sébastien Dumont](https://github.com/seb86).

Founder of CoCart - [Sébastien Dumont](https://github.com/seb86).

---

Website [sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
Twitter [@sebd86](https://twitter.com/sebd86)

<p align="center">
    <img src="https://raw.githubusercontent.com/seb86/my-open-source-readme-template/master/a-sebastien-dumont-production.png" width="353">
</p>
