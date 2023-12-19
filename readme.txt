=== CoCart - Decoupling WooCommerce Made Easy === 
Contributors: cocartforwc, sebd86, ajayghaghretiya, skunkbad, sefid-par, mattdabell, joshuaiz, dmchale, JPPdesigns, inspiredagency, darkchris, mohib007, rozaliastoilova, ashtarcommunications, albertoabruzzo, jnz31, douglasjohnson, antondrob2
Tags: woocommerce, rest-api, api, decoupled, headless, cart, products, session
Requires at least: 5.6
Requires PHP: 7.4
Tested up to: 6.3
Stable tag: 3.10.0
WC requires at least: 4.3
WC tested up to: 8.4
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Make your WooCommerce store headless with CoCart, a REST API designed for decoupling.

== Description ==

### Supercharge your WooCommerce store by going headless

**Is your store slow? Looking to decouple away from WordPress?** With [CoCart](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)'s customizable REST API for WooCommerce, you can make your store headless in days, not months, **using the frameworks and tools you love**. Unlock your stores potential for an extraordinary online shopping experience.

#### What is WooCommerce?

WooCommerce is a **flexible, open-source commerce solution** built on WordPress, empowering anyone to **sell anything, anywhere** and is **the fastest-growing eCommerce platform** on the internet.

#### Why CoCart?

Are you **agonizing trying** to develop your own REST API for WooCommerce? With CoCart, it's ready to use, customizable to your needs and more importantly, it saves **you development time**. Once installed, your WooCommerce store is ready to decouple away from WordPress, allowing you to design without limitations imposed by a WordPress theme that is harder to modify and optimize. **Utilize faster, familiar frameworks you know and love**—it's that simple.

## ✨ What do you get with the core of CoCart?

**Everything you need to try** and see if making your store headless is right for you. Promise you wont be disappointed.

* **No Blocks** - The API is designed for the purpose of decoupling. Not blocks for Gutenberg.
* **Enhanced Session Handler** - Our session handler adds support to allow our API the power it requires for any decoupled situation.
* **Basic Authentication** - No Admin API Keys required. Customers have full control, either as a guest or authenticated with their login details.
* **Domain dominance** - CORS can be an issue when decoupling. Don’t sweat the small stuff. We got you.
* **No Headless Checkout?** - Load any cart session via the native site, if you feel more comfortable using WooCommerce’s built in payment system.
* **Worried about Caching?** - The Cart API does not cache no matter what cache system you have installed for other API’s in use. Responses return fresh every time.
* **Reduced Cart Checkups** - Avoid the hassle of multiple requests to verify item and coupon validity in your cart. Our system efficiently checks stock, validates coupons, and calculates totals and fees, ensuring real-time accuracy before confirmation.
* **Need your own cart callback?** - Register custom callbacks without needing to create a whole new endpoint. Cart response returns once the callback is completed.
* **Your Inventory** - Search by Name, ID or SKU, filter and return product data you need without authentication. REST shortcuts are readily provided for your next requests.
* **Want to track your customers?** - Keep watch of all cart sessions, even the ones that are starting to expire.
* **Name Your Price Built In** - Give your customers control of the price they pay. Encourage your audience to support you with payment flexibility that widens your paying audience.

And this is just the tip of the iceberg.

★★★★★
> An excellent plugin, which makes building a headless WooCommerce experience a breeze. Easy to use, nearly zero setup time. [Harald Schneider](https://wordpress.org/support/topic/excellent-plugin-8062/)

## 🛒 Built for developers, by developers

CoCart was born out of frustration with **no existing solution on the market**. As beautiful as it functions, the API is just as flexible with you, the developer, in mind. We spend an unfathomable amount of time making the API a joy to work with.

We invest our time into fully abstracting our API so you can focus on building a headless store. Integrate with CoCart in days, not months.

★★★★★
> Amazing Plugin. I’m using it to create a react-native app with WooCommerce as back-end. This plugin is a life-saver! [Daniel Loureiro](https://wordpress.org/support/topic/amazing-plugin-1562/)

### 📦 Serious about going headless?

Try out more features and unlock your stores potential. Upgrade to complete the API with additional features that help make your store more awesome.

See what [CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) has in store.

★★★★★
> This plugin saved me tones of work and it is working amazingly! The plugin author provides fast and high quality support. Well done! [@codenroll](https://wordpress.org/support/topic/great-plugin-with-a-great-support-7/)

### 😍 Priority support

We aim to provide regular support for the CoCart plugin on the WordPress.org forums. But please understand that we do prioritize support for our paying customers. Support can also be requested with the [community on Discord](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

## 🧰 Tools and Libraries

* **[CoCart Beta Tester](https://github.com/cocart-headless/cocart-beta-tester)** allows you to easily update to pre-release versions of CoCart for testing and development purposes.
* **[CoCart VSCode](https://github.com/cocart-headless/cocart-vscode)** extension for Visual Studio Code adds snippets and autocompletion of functions, classes and hooks.
* **[CoCart Product Support Boilerplate](https://github.com/cocart-headless/cocart-product-support-boilerplate)** provides a basic boilerplate for supporting a different product types to add to the cart with validation including adding your own parameters.
* **[CoCart Cart Callback Example](https://github.com/cocart-headless/cocart-cart-callback-example)** provides you an example of registering a callback that can be triggered when updating the cart.

## 📢 Testimonials - Developers just love it

★★★★★
> Thanks for doing such a great work with this! Works exactly as expected and CoCart seems to have a nice community around it. The founder seems really devoted and that’s one of the key things for a plugin like this to live on and get the right updates in the future. We just got ourselves the lifetime subscription. [Mighty Group Agency](https://wordpress.org/support/topic/awesome-plugin-4681/)

★★★★★
> This plugin works great out of the box for adding products to the cart via API. The code is solid and functionality is as expected, thanks Sebastien! [Scott Bolinger, Creator of Holler Box](https://wordpress.org/support/topic/works-great-out-of-the-box-16/)

#### More testimonials

[See the wall of love](https://cocartapi.com/wall-of-love/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

### ☀️ Upgrading

It is recommended that anytime you want to update CoCart that you get familiar with what's changed in the release.

CoCart publishes [release notes via the changelog](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#developers).

CoCart uses Semver practices. The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes.
- *MINOR* version when you add functionality in a backwards compatible manner.
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org/)

#### 👍 Add-ons to further enhance CoCart

We also have add-ons that extend CoCart to enhance your development and your customers shopping experience.

* **[CoCart - CORS](https://wordpress.org/plugins/cocart-cors/)** enables support for CORS to allow CoCart to work across multiple domains.
* **[CoCart - JWT Authentication](https://wordpress.org/plugins/cocart-jwt-authentication/)** allows you to authenticate via a simple JWT Token.
* **[CoCart - Cart Enhanced](https://wordpress.org/plugins/cocart-get-cart-enhanced/)** enhances the data returned for the cart and the items added to it.
* and more add-ons in development.

They work with the core of CoCart already, and these add-ons of course come with support too.

### ⌨️ Join our growing community

A Discord community for developers, WordPress agencies and shop owners building the fastest and best headless WooCommerce stores with CoCart.

[Join our community](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

### Built with developers in mind

Extensible, adaptable, and open source — CoCart is created with developers in mind. If you’re interested to jump in the project, there are opportunities for developers at all levels to get involved. [Contribute to CoCart on the GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and join the party. 🎉

### 🐞 Bug reports

Bug reports for CoCart are welcomed in the [CoCart repository on GitHub](https://github.com/co-cart/co-cart). Please note that GitHub is not a support forum, and that issues that aren’t properly qualified as bugs will be closed.

### More information

* The official [CoCart API plugin](https://cocartapi.com/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) website.
* [CoCart for Developers](https://cocart.dev/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink), an official hub for resources you need to be productive with CoCart and keep track of everything that is happening with the API.
* [CoCart API Reference](https://docs.cocart.xyz/)
* [Subscribe to updates](http://eepurl.com/dKIYXE)
* Like, Follow and Star on [Facebook](https://www.facebook.com/cocartforwc/), [Twitter](https://twitter.com/cocartapi), [Instagram](https://www.instagram.com/cocartheadless/) and [GitHub](https://github.com/co-cart/co-cart)

#### 💯 Credits

This plugin is developed by [CoCart Headless, LLC](https://twitter.com/cocartheadless).

== Installation ==

= Minimum Requirements =

* WordPress v5.6
* WooCommerce v4.3
* PHP v7.4

= Recommended Requirements =

* WordPress v6.0 or higher.
* WooCommerce v7.0 or higher.
* PHP v8.0

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of CoCart, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type "CoCart" and click Search Plugins. Once you’ve found the plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking "Install Now".

= Manual installation =

The manual installation method involves downloading the plugin and uploading it to your webserver via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Upgrading =

It is recommended that anytime you want to update CoCart that you get familiar with what's changed in the release.

CoCart publishes [release notes via the changelog](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#developers).

CoCart uses Semver practices. The summary of Semver versioning is as follows:

- *MAJOR* version when you make incompatible API changes.
- *MINOR* version when you add functionality in a backwards compatible manner.
- *PATCH* version when you make backwards compatible bug fixes.

You can read more about the details of Semver at [semver.org](https://semver.org/)

== Frequently Asked Questions ==

= Is CoCart free? =

Yes! CoCart’s core features are absolutely free. [CoCart Plus completes the full cart experience!](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink)

= How do I start using CoCart? =

You will first need WooCommerce installed with the REST API enabled. Then install CoCart and follow the documentation.

> Please check the requirements listed in the [installation](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) section.

= Who should use CoCart? =

CoCart is perfect for ecommerce owners and developers who want to create an ecommerce app for mobile or a custom frontend shopping experience completely using the REST API.

= Do I need to have coding skills to use CoCart? =

As this plugin provides a REST API built for developers, you will need to have some coding knowledge to use it.

= Where can I find documentation for CoCart? =

You can find the documentation [here](https://docs.cocart.xyz/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= Can I change the formatting of values, add and change details to the responses? =

You certainly can. There are over 100+ filters available to customize to your needs.

= Why does CoCart use a custom session handler in the first place? =

If you're familiar with WooCommerce, you may be wondering why using a custom session handler at all instead of the WooCommerce default session handler? A number of reasons but the ones that really matter are.

- The default session handler only supports cookies.
- The default session handler only saves changes at the end of the request in the `shutdown` hook.
- The default session handler has no support for concurrent requests.
- The default session handler **does not support guest customers**.
- The default session handler **does not store additional data that maybe required to help you**.
- More consistent with modern web.

= Why does CoCart use a custom session table in the database? =

The default WooCommerce session table only stores the basics of a cart in session. CoCart provides additional data that maybe required to help you and other add-ons/extensions developed by CoCart or third-parties.

Such as when the cart was created. This information is only stored in the browser session.

Also the source of the cart it was last saved. For the web it will be `WooCommerce` and for your headless ecommerce `CoCart`. This lets you know which version of your store your customers are shopping from should you have both web and app versions.

= Can I have WordPress running on one domain and my headless ecommerce on another domain? =

Yes of course. You just need to enable CORS. You can do that easily with [the CORS add-on](https://wordpress.org/plugins/cocart-cors/) or you can manually enable it via the filters available [in the documentation](https://docs.cocart.xyz/#filters-api-access-cors-allow-all-cross-origin-headers).

= Can I add "WooCommerce Subscriptions" product to the cart? =

Absolutely you can. Any WooCommerce Subscriptions product can be added to the cart the same way a simple or variable product is added to the cart.

= Why can I not add the same item to the cart with a different price? =

Each item added to the cart is assigned a cart item key which is made of four key values: **Product ID**, **Variation ID**, **Variation attributes** and **Cart item data**.

The price and quantity is not taken into account to make this key.

So if you add the same item to the cart but with a different price, the cart will look up the cart item key to see if it's already added to the cart before deciding to either:
    1. Add it as a new item.
    2. Update the quantity of the previous item, in your case the price.

= Is "WooCommerce Shipping and Tax" plugin supported? =

Not at this time. "WooCommerce Shipping and Tax" ignores any REST API from allowing the ability to calculate the taxes from TaxJar except for WooCommerce Blocks and JetPack. However, [TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/) plugin is supported.

= Is "TaxJar for WooCommerce" plugin supported? =

If you have "[TaxJar for WooCommerce](https://wordpress.org/plugins/taxjar-simplified-taxes-for-woocommerce/)" v3.2.5 or above and CoCart v3.0 or above installed... then yes, it is supported.

= Is CoCart right for my business? =

CoCart’s REST API makes it possible for businesses to build a complete custom storefront. It’s API-first, enabling your business to take the shopping experience to the next level.

Made by and for developers, CoCart immediately allows you to create sophisticated experiences fast with unlimited possibilities.

With our extensive documentation and resources available, CoCart is a plug and play solution that works out of the box.

Save yourself 80% of a headache and hours of development time.

= Can I use any modern stack? =

Yes you can use your preferred tools and favorite modern technologies like [NextJS](https://nextjs.org/), [React](https://reactjs.org/), [Vue](https://vuejs.org/), [Ember](https://emberjs.com/) and more giving you endless flexibility and customization.

= Why CoCart and not WooCommerce Store API? =

Both API’s are unique for their individual purposes.

WooCommerce's Store API is designed for their [Gutenberg blocks](https://wordpress.org/plugins/woo-gutenberg-products-block/) which only requires a fixed format and is still prone to be used on native storefronts.

It also only works with *Nonces* when you are on the site so for mobile apps or headless ecommerce, you will run into issues. It is also missing a lot of valuable information that developers require to help them.

CoCart's API is designed for decoupling away from WordPress and lets you build headless ecommerce using your favorite technologies. **No Nonces, no cookies required.**

CoCart is packed full of powerful features that are completely customizable making it possible for businesses to build a complete custom storefront how they want.

No matter the type of store you are running, CoCart helps you grow.

It’s made by and for developers and immediately allows you to create sophisticated experiences fast with unlimited possibilities with it’s plug and play solution that just works out of the box.

So even if you are new to building a headless ecommerce or already have a WooCommerce store and been wanting to go headless, now is the time to start.

Don't take my word for it. Checkout the testimonials left by startups, freelancers, agencies and many more.

= Can I install/update CoCart via Composer? =

Yes. The best method would be to install/update CoCart from the GitHub repository but you can also do so via [https://wpackagist.org/](https://wpackagist.org/search?q=cart-rest-api-for-woocommerce&type=plugin)

= Does CoCart work for multi-site network? =

Yes. Just install CoCart and activate it via the network and all sites will have CoCart enabled.

= Can I enable white labelling for CoCart? =

Yes you can. You will have to edit your `wp-config.php` file to add a new constant. [See guide for details](https://cocart.dev/articles/wp-config-php/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink#white-labelling).

= Where can I report bugs? =

Report bugs on the [CoCart GitHub repository](https://github.com/co-cart/co-cart/issues). You can also notify us via the support forum – be sure to search the forums to confirm that the error has not already been reported.

= CoCart is awesome! Can I contribute? =

Yes, you can! Join in on our [GitHub repository](https://github.com/co-cart/co-cart/blob/trunk/.github/CONTRIBUTING.md) and follow the [development blog](https://cocart.dev/news/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) to stay up-to-date with everything happening in the project.

= Is CoCart translatable? =

Yes! CoCart is deployed with full translation and localization support via the ‘cart-rest-api-for-woocommerce’ text-domain.

= Where can I get help or talk other users about CoCart core? =

If you get stuck, you can ask for help in the [CoCart support forum](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/) or [join the CoCart Community on Discord](https://cocartapi.com/community/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) where you will find like minded developers who help each other out. If you are in need of priority support, it will be provided by purchasing [CoCart Plus](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) or a higher tier.

= Where can I find out more about the additional features? =

Find out all relevant [features and pricing information over on the official site](https://cocartapi.com/pricing/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink).

= My question is not listed here. Where can I find more answers? =

Check out [Frequently Asked Questions](https://cocartapi.com/faq/?utm_medium=website&utm_source=wpplugindirectory&utm_campaign=readme&utm_content=readmelink) for more.

== Screenshots ==

1. Get Products (API v2)
2. Get Individual Product (API v2)
3. Add Item to Cart (API v2)

== Contributors & Developers ==

"CoCart" has **not** yet been translated in other languages. You can [translate "CoCart" into your language](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce).

**INTERESTED IN DEVELOPMENT?**

[Browse the code](https://plugins.trac.wordpress.org/browser/cart-rest-api-for-woocommerce/), check out the [SVN repository](https://plugins.svn.wordpress.org/cart-rest-api-for-woocommerce/), or subscribe to the [development log](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/) by [RSS](https://plugins.trac.wordpress.org/log/cart-rest-api-for-woocommerce/?limit=100&mode=stop_on_copy&format=rss).

== Changelog ==

If you like CoCart and it has helped with your development, please take a moment to [provide a review](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/reviews/#new-post). It helps to keep the plugin going strong, and is greatly appreciated.

= v3.9.0 - 2nd August, 2023 =

### What's New?

* Removed WooCommerce plugin headers to prevent incompatibility warning message when using "HPOS" feature.
* Updated "What's Coming Next?" link on plugins page to inform users about v4.0
* Tested with WooCommerce v7.9

### Bug Fix

* Fixed Products API where a product has no featured image or gallery images and is unable to determine the placeholder image.

= v3.8.2 - 12th July, 2023 =

### What's New?

* Tested with WooCommerce v7.8
* Tested with WordPress v6.2

### Bug Fix

* Fixed searching products by name.

= v3.8.1 - 4th March, 2023 =

### What's New?

* Added the Authentication class as parameter to `cocart_authenticate` filter.
* Added `set_method()` function to authentication class.

### For Developers

Introduced a new filter `cocart_login_extras` to allow developers to extend the login response.

= v3.8.0 - 3rd March, 2023 =

### What's New?

* Tested: ✔️ Compatible with WooCommerce v7.4

[View the full changelog here](https://github.com/co-cart/co-cart/blob/trunk/CHANGELOG.md).

== Upgrade Notice ==

= 3.9.0 =

* Removed WooCommerce plugin headers to prevent incompatibility warning message when using "HPOS" feature.
