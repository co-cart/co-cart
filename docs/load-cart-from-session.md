# Load Cart from Session <!-- omit in toc -->

> This feature is designed to support guest customers **only** and is not part of the REST API.

## Table of Contents <!-- omit in toc -->

- [Why would I use this feature?](#why-would-i-use-this-feature)
- [Why does the feature support guest customers only?](#why-does-the-feature-support-guest-customers-only)
- [What if the user is logged in on the native site?](#what-if-the-user-is-logged-in-on-the-native-site)
- [Can I merge cart items?](#can-i-merge-cart-items)
- [Can I sync back a cart session from a logged in user to a guest user?](#can-i-sync-back-a-cart-session-from-a-logged-in-user-to-a-guest-user)
- [How do I use this feature?](#how-do-i-use-this-feature)

Load Cart from Session is simply designed to transfer the cart session over to the web version of your store (frontend).

## Why would I use this feature?

Let's say for example your guest user (customer) is shopping via your headless store phone app and would like to continue shopping on their pc or laptop. That device will not be able to track where that user left off because they are a guess, so their session is fixed only on the phone app.

However, with modern browsers, you can share and send links between devices. This allows the user to open the store via the browser and send it over to their other device.

This feature is also handy if you don't yet have a checkout system in your app and want to use the native checkout page.

## Why does the feature support guest customers only?

Because customers who are registered on your store have an account that keeps track of the cart session so the cart key is not required to identify the session as it's already taken care of in the background. This makes it easy for the cart session to load on the native storefront or your decoupled site when your customers are logged in.

## What if the user is logged in on the native site?

If you are concerned if the users details are transferred, the answer is no. It does not matter if your user is logged in or not already. Only the cart data (items, coupons, fees and selected shipping option) will be transferred.

## Can I merge cart items?

If a user is logged in via the native storefront then WooCommerce will merge any items in the cart together with the items from the loaded cart session.

## Can I sync back a cart session from a logged in user to a guest user?

If the user was logged in on the native storefront at the time the cart session was loaded then no you can't go sync back. This is because that cart session has now transferred hands and is assigned to that user. Any changes made to the cart session assigned to the user is not going to sync to the guest cart session.

However, a feature to sync cart sessions is in the works.

## How do I use this feature?

To load a cart from session on your native storefront, you must use the properties below to query. You can query any page you prefer your users to land on as the cart is loaded in the background but we recommend the cart page.

### Properties <!-- omit in toc -->

| Property           | Type   | Description                                                                                                                     |
| ------------------ | ------ | ------------------------------------------------------------------------------------------------------------------------------- |
| `cocart-load-cart` | string | Set the cart key of the cart you wish to load. _mandatory_                                        |
| `notify`           | bool   | Set as true to notify customers once arrived on the native storefront. _Default is false_ |
| `keep-cart`        | bool   | Set as false to merge cart data. _Default is true_                                                |

Example: https://example.com/cart/?cocart-load-cart=bbfa8e97ac9cff4c861d62a109e83bb6

> If you are merging two cart sessions together and they both contain the same item, that item will not change. It will not increase or decrease the quantity either.

<!-- FEEDBACK -->

---

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./docs/load-cart-from-session.md)

<!-- /FEEDBACK -->
