# CoCart Plugin

**Important**

Updates for the CoCart plugin on WordPress.org starting on **5th October 2022** will not be getting any further major updates for a long while in order to focus on the paid CoCart Pro version of the plugin. Only minor fixes for bugs will be updated. Don't worry, you'll still be able to use this plugin forever.

Also while API v1 can still be used it will no longer be supported.

**The #1 ecommerce RESTful API built for WooCommerce that scales for headless development.**

## The API

CoCart provides support for managing the user session, alternative options for doing this task do exist; however, their usage can be limited to applications of the same origin as the WordPress installation. This is due to WooCommerce using cookies to store user session tokens.

CoCart provides the utilities to change this behavior during any cart request and passes the required information to HTTP Header so it can be cached client-side. The use of an HTTP Authorization header is optional allowing users to shop as a guest.

#### Cart API

Add **simple, variable** and **grouped products** to the cart by **product ID** or **SKU ID**, update cart items individually or in bulk and more. The flow is simple and returns an updated cart response every time with all the totals calculated and stock checks done for you making it easier to simply update your **UX/UI**.

#### Products API

Access products from your store to display how you like including a number of queries to help you filter by product categories, tags, attributes and more. You can even get posted reviews all without the need to authenticate. All the information you need about a product and it's conditions to help you with your UX/UI development is all provided ready for you.

---

## For Developers

Here are a few other resources you may find helpful.

* **[CoCart Beta Tester](https://github.com/co-cart/cocart-beta-tester)** allows you to test with bleeding edge versions of CoCart Core from the GitHub repo.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Carts in Session](https://github.com/co-cart/cocart-carts-in-session)** allows you to view all the carts in session via the WordPress admin.
* **[CoCart Product Support Boilerplate](https://github.com/co-cart/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product type to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.
* **[CoCart Tweaks](https://github.com/co-cart/co-cart-tweaks)** provides a starting point for developers to tweak CoCart to their needs.
* **[Node.js Library](https://www.npmjs.com/package/@cocart/cocart-rest-api)** provides a JavaScript wrapper supporting CommonJS (CJS) and ECMAScript Modules (ESM).

## Priority support

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our premium customers. Communication is handled one-on-one via direct messaging in [Slack](https://app.slack.com/client/TD85PLSMA/) and is available to people who purchased [CoCart Pro](https://cocart.xyz/pro/?utm_medium=wp.org&utm_source=wordpressorg&utm_campaign=readme&utm_content=cocart).

## Privacy Policy 

CoCart uses [Appsero](https://appsero.com) SDK to collect some telemetry data upon user's confirmation. This helps us to troubleshoot problems faster & make product improvements.

Appsero SDK **does not gather any data by default.** The SDK only starts gathering basic telemetry data **when a user allows it via the admin notice**. We collect the data to ensure a great user experience for all our users. 

Integrating Appsero SDK **DOES NOT IMMEDIATELY** start gathering data, **without confirmation from users in any case.**

Learn more about how [Appsero collects and uses this data](https://appsero.com/privacy-policy/).

---

### More information

* The [CoCart plugin](https://cocart.xyz/) official website.
* The CoCart [Documentation](https://docs.cocart.xyz/)
* [Join the CoCart community](https://cocart.xyz/community/).
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/co_cart/) and [GitHub](https://github.com/co-cart/co-cart)

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./plugins/cocart/README.md)
