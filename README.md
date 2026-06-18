<p align="center"><img src="https://raw.githubusercontent.com/cocart-headless/.github/refs/heads/main/profile/banner.png" alt="CoCart — Headless REST API for WooCommerce" /></p>

<p align="center">
	<a href="https://github.com/co-cart/co-cart/blob/trunk/license.txt" target="_blank">
		<img src="https://img.shields.io/badge/license-GPL--3.0%2B-red.svg" alt="Licence">
	</a>
	<img src="https://img.shields.io/badge/WordPress-6.7+-blue.svg" alt="WordPress 6.7+">
	<img src="https://img.shields.io/badge/WooCommerce-9.0+-7f54b3.svg" alt="WooCommerce 9.0+">
	<img src="https://img.shields.io/badge/PHP-8.2+-777BB4.svg" alt="PHP 8.2+">
	<a href="https://github.com/co-cart/co-cart">
		<img src="https://img.shields.io/github/stars/co-cart/co-cart?style=social" alt="GitHub Stars">
	</a>
</p>

# CoCart — Community Edition

A developer-first REST API to decouple WooCommerce on the frontend.

Stop struggling with debugging cart sessions, broken cart flows and cache issues.

CoCart handles the hard parts so you can focus on your store, products and customers. Take advantage of our SDK's to quickly setup and integrate with **Astro**, **Next.js**, **React**, **Vue**, or any modern framework to build your headless storefront — gaining complete control over the customer experience, independent of WordPress.

[Documentation](https://cocartapi.com/docs/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) · [Try the REST API](https://cocartapi.com/try-free-demo/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) · [Compare Editions](#compare-editions) · [Upgrade](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) · [SDK](#official-sdks)

---

> [!IMPORTANT]
> **v4.9.0 is the final major release of CoCart Community Edition.**
> Going forward, Community Edition will receive security patches only — no new features or performance optimizations.
> New development continues in [**CoCart Starter & CoCart Plus**](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) and higher tiers.
> Community Edition remains a great way to evaluate CoCart before purchasing a license.

## Why Developers Choose CoCart

* ✅ **Source of truth** — Built on WooCommerce Data Stores and mirrors most WooCommerce hooks, ensuring broad compatibility from the start.
* 🔐 **Session management** — Lightweight, cookie-less session keys stored in the database. Concurrency-safe.
* 🔑 **Flexible authentication** — Log in via email, username, or phone. No admin API keys required. Optional JWT via [official add-on](https://github.com/cocart-headless/cocart-jwt-authentication).
* 🌍 **CORS control** — First-party CORS so cross-domain frontends "just work".
* 🛒 **Real-time cart management** — Validate items/coupons, calculate totals in a single call.
* 🔎 **Product search** — Query by name, SKU, or ID — authenticated or not — with flexible filters.
* 🧩 **Extendable callbacks** — Add your own logic with custom callbacks. No new endpoints needed.
* 📊 **Cart Insights** — Monitor all cart sessions, including those nearing expiration or already expired.
* 🛠 **Works with Woo Checkout** — Load any cart session into WooCommerce's native checkout.
* 💸 **Name Your Price** — Support donation-based pricing with built-in flexibility.
* 📦 **Bulk Cart Requests** — Combine multiple API calls into one for better performance.

## Quick Start

```bash
# Add an item to the cart
curl -X POST https://your-store.com/wp-json/cocart/v2/cart/add-item \
  -H "Content-Type: application/json" \
  -d '{"id": "123", "quantity": 1}'
```

See the [full documentation](https://cocartapi.com/docs/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) for all available endpoints and [create a sandbox](https://cocartapi.com/try-free-demo/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) to try it out.

If you use Raycast, install our documentation extension for quick easy access.

<a title="Install cocart-docs Raycast Extension" href="https://www.raycast.com/cocart_headless/cocart-docs"><img src="https://www.raycast.com/cocart_headless/cocart-docs/install_button@2x.png?v=1.1" height="64" alt="Install CoCart Docs Raycast extension" style="height: 64px;"></a>

## Official SDKs

Get started in your language of choice. Each SDK handles authentication, session management, and cart operations out of the box including currency formatting and timezone for dates.

| SDK | Language | Repository |
|-----|----------|------------|
| **cocart-js** | TypeScript / JavaScript | [GitHub](https://github.com/cocart-headless/cocart-js) |
| **cocart-php** | PHP | [GitHub](https://github.com/cocart-headless/cocart-php) |
| **cocart-python** | Python | [GitHub](https://github.com/cocart-headless/cocart-python) |
| **cocart-go** | Go | [GitHub](https://github.com/cocart-headless/cocart-go) |

Want another language? [Make a request](https://cocartapi.com/suggest-a-feature/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition)

## Compare Editions

| Feature | Community (Free) | Starter | Plus |
|---------|:---:|:---:|:---:|
| Cart API (add, update, remove items) | ✅ | ✅ | ✅ |
| Product search & filtering | ✅ | ✅ | ✅ |
| Session management | ✅ | ✅ | ✅ |
| Authentication (Basic, JWT) | ✅ | ✅ | ✅ |
| Performance optimizations | — | ✅ | ✅ |
| Official support | — | ✅ | ✅ |
| Coupon management | — | — | ✅ |
| Shipping calculations | — | — | ✅ |
| Cart fees | — | — | ✅ |
| Advanced Batch API | — | — | ✅ |
| Rate limiting | — | — | ✅ |
| Checkout & Subscriptions | — | — | Coming Soon |

👉 [View full pricing & features](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition)

## CoCart Starter

CoCart Starter contains the same features and support as the Community Edition but is optimized for performance and provides improvements to support CoCart Plus and additional add-ons.

If you've evaluated CoCart Community Edition and need more powerful features and support for production, Basic is the natural next step.

👉 [Get CoCart Starter](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition)

## Upgrade to CoCart Plus

For more powerful features and enterprise-level control — upgrade to **[CoCart Plus](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition)** and complete your headless store.

* 🎫 **Coupon Management** — Reward customers and boost conversions.
* 🚢 **Shipping Options & Rate Calculation** — Let customers choose from your available shipping methods.
* 💰 **Cart Fees** — Add custom fees for any purpose (handling, rush, etc.).
* 🥪 **Advanced Batch API** — Handle multiple cart actions in a single request.
* 🕒 **Rate Limiting** — Prevent abuse and maintain high performance with granular API control.
* 🧾 **Checkout** — Complete an order and take payment using any supported gateways by WooCommerce. (Coming Soon)
* 💲 **Subscription Support** — Complete new subscriptions or renewals automatically or manually. (Coming Soon)

## Add-ons

We also have add-ons that extend CoCart to enhance your development and your customers' shopping experience.

| Add-on             | Repository | Requirements |
|--------------------|:----------:|:------------:|
| **CORS** - Enables CoCart to work across multiple domains. | [GitHub](https://github.com/cocart-headless/cocart-cors) | - |
| **Rate Limiting** - Control and prevent abuse from excessive calls. | [GitHub](https://github.com/cocart-headless/cocart-rate-limiting) | CoCart Plus or higher |
| **JWT Authentication** - Authenticate via a simple JWT Token. | [GitHub](https://github.com/cocart-headless/cocart-jwt-authentication) | - |

These add-ons of course come with support too.

## Need Support?

Community Edition support is provided via [our Discord community server](https://cocartapi.com/community/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition). Priority support is available to [paying customers](https://cocartapi.com/pricing/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition).

## Developer Tools

* **[CoCart OpenAPI Specs](https://github.com/cocart-headless/cocart-openapi)** — OpenAPI definitions for CoCart REST API endpoints. Use them to generate client libraries, test with Yaak/Postman/Insomnia, or integrate with any tool that supports the OpenAPI standard.
* **[CoCart VSCode](https://github.com/co-cart/cocart-vscode)** extension for Visual Studio Code adds snippets and auto-completion of functions, classes and hooks.
* **[CoCart Cart Callback Example](https://github.com/co-cart/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

## Bug Reporting

Bug reports for CoCart Community Edition are welcomed in [this repository on GitHub](https://github.com/co-cart/co-cart/issues/new). Please note that GitHub is not a support forum, and that issues that aren't properly qualified as bugs will be closed.

## Testimonials

> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time.
>
> Harald Schneider ⭐️⭐️⭐️⭐️⭐️

> What can I say this thing has it all. It is the "Missing WooCommerce REST API plugin" without it I was managing users cart myself in weird and wonderful but hacky ways. NOT GOOD and so vulnerable. Then I stumbled upon CoCart and with the help of Seb I got it working how I needed it and he has been supporting me with even the smallest of queries. Really appreciate your work and continued support Seb.
>
> **[Joel Pierre](https://github.com/joelpierre)** – JPPdesigns Web design & Development ⭐️⭐️⭐️⭐️⭐️

> This plugin was critical to achieve my project of building a headless / decoupled WooCommerce store. I wanted to provide my clients with a CMS to manage their store, but wanted to build the front-end in React. I was able to fetch content over the WooCommerce REST API, but otherwise would not have been able to fetch the cart, and add & remove items if not for this plugin.
>
> Thank you very much Sébastien for sharing this extension, you've saved me a lot of time.
>
> **Allan Pooley** – Little and Big ⭐️⭐️⭐️⭐️⭐️

> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that's one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription.
>
> **MightyGroup** – Rikard Kling ⭐️⭐️⭐️⭐️⭐️

[See the wall of love](https://cocartapi.com/wall-of-love/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) for more testimonials. We'd also love to hear what you have to say. [Share your thoughts](https://testimonial.to/cocart) and help others discover if CoCart is for them.

## Get Involved

Do you like the idea of creating a headless store with WooCommerce? Got questions or feedback? We'd love to hear from you. Come [join our community](https://cocartapi.com/community/?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition)!

## Credits

Website [cocartapi.com](https://cocartapi.com?utm_medium=github.com&utm_source=repository&utm_campaign=readme&utm_content=communityedition) &nbsp;&middot;&nbsp;
GitHub [@co-cart](https://github.com/co-cart) &nbsp;&middot;&nbsp;
X/Twitter [@cocartapi](https://twitter.com/cocartapi)
[Facebook](https://www.facebook.com/cocartforwc/) &nbsp;&middot;&nbsp;
[Instagram](https://www.instagram.com/cocartheadless/)

---

CoCart API is developed and maintained by [Sébastien Dumont](https://github.com/seb86).
Founder of [CoCart Headless, LLC](https://github.com/cocart-headless).

Website [sebastiendumont.com](https://sebastiendumont.com) &nbsp;&middot;&nbsp;
GitHub [@seb86](https://github.com/seb86) &nbsp;&middot;&nbsp;
X/Twitter [@sebd86](https://twitter.com/sebd86)
