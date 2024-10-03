# CoCart Handbook <!-- omit in toc -->

The CoCart API is written entirely in PHP, and reuses a lot of the existing cart logic (and filter hooks) within WooCommerce. As a result, many plugins that were originally built for the shortcode-based cart will continue to work with the CoCart API that consume it.

If your plugin is making heavy use of PHP templates, or utilizes hooks included in core templates of WooCommerce, you will probably need to use some of our own filter hooks to match the same data result in the CoCart API.

This handbook will help you with what you need.

## Table of Contents <!-- omit in toc -->

- [Introduction](#introduction)
- [API Reference](#api-reference)
- [Plugin Features](#plugin-features)
- [Third party developers](#third-party-developers)
- [Developer Resources](#developer-resources)
  - [Tools](#tools)
  - [Articles](#articles)
  - [Tutorials](#tutorials)

## Introduction

New to CoCart or the REST API in general. [Read our introduction](intro-to-cocart.md) to CoCart to help you get started.

## API Reference

- [Cart API](API/cart.md)
- [Products API](API/products.md)
- [Sessions API](API/sessions.md)

## Plugin Features

- [Load Cart from Session](load-cart-from-session.md)
- [Rate Limiting Guide](rate-limit-guide.md)
- [Override Item Price](override-item-price.md)

## Third party developers

> Are you a third-party developer? The following documents explain how to extend the CoCart plugin with your custom extension.

* Hooks
 * * [Actions](https://coderef.cocart.dev/reference/hooks/)
 * * [Filters](https://coderef.cocart.dev/reference/hooks/)
* REST API
 * [Adding product validation for a custom product type](#)
 * [Adding a custom callback for updating the cart](#)
 * [Extending details for each cart item](#)
 * [Extending the cart response](#)

## Developer Resources

### Tools

### Articles

The following posts from [cocart.dev](https://cocart.dev) provide deeper insights into CoCart's development.

### Tutorials

The following tutorials from [cocart.dev](https://cocart.dev) help you with extending various parts of CoCart. Our [code reference](https://coderef.cocart.dev/) will also provide you with a catalogue of classes, functions, hooks and methods that you can use.

* [Convert cart to order using WC REST API](#)

<!-- FEEDBACK -->

---

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./docs/README.md)

<!-- /FEEDBACK -->
