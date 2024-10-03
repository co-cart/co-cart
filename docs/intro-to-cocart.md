# Intro to CoCart <!-- omit in toc -->

This guide will be most useful for developers with little to no WordPress experience. This is not intended to be the most comprehensive guide to WordPress in the world, but is intended to provide some insight into how WordPress works and resources to learn more about WordPress.

## Table of Contents <!-- omit in toc -->

- [Introduction](#introduction)
- [What is REST API?](#what-is-rest-api)
- [What is Jamstack?](#what-is-jamstack)
- [What Is WordPress?](#what-is-wordpress)
- [Using WordPress as a Headless CMS](#using-wordpress-as-a-headless-cms)
  - [Setting up WordPress on your computer](#setting-up-wordpress-on-your-computer)
  - [Setting up WooCommerce](#setting-up-woocommerce)
- [How do Plugins Work with decoupled WordPress?](#how-do-plugins-work-with-decoupled-wordpress)

## Introduction

This guide will be most useful for developers that are new to working with REST API.

This guide isn't a comprehensive deep dive into REST API but should help you get a basic understanding of it and provide you with resources to dive deeper.

## What is REST API?

An API is an Application Programming Interface. REST, standing for “Representational State Transfer,” is a set of concepts for modelling and accessing your application’s data as interrelated objects and collections.

Your application can send and receive JSON data to these endpoints to query, modify and create content on your site. JSON is an open standard data format that is lightweight and human-readable, and looks like Objects do in JavaScript.

When you request content from or send content to the API, the response will also be returned in JSON. Because JSON is widely supported in many programming languages, developers can build WordPress applications in client-side JavaScript, as mobile apps, or as desktop or command line tools.

## What is Jamstack?

Jamstack is an architecture designed to make the web faster, more secure, and easier to scale. It builds on many of the tools and workflows which developers love, and which bring maximum productivity, improving flexibility, scalability, and maintainability.

Jamstack removes the need for logic to dictate the web experience. It enables a composable architecture for the web where custom logic and 3rd party services are consumed through APIs.

## What Is WordPress?

WordPress is currently the most popular CMS on the internet today, used by everyone from small DIY bloggers to small/mid-sized agencies and complex enterprises. Using the built-in REST API, WordPress can power amazing headless experiences while providing content editors and managers an extensible and familiar publishing interface.

It offers structured access to site content and settings over HTTP. Using existing patterns, developers can extend the REST API to support custom content types or create unique routes that support any use case that can be created with PHP and its associated libraries and tools.

## Using WordPress as a Headless CMS

The WordPress CMS already gives site developers a method for server-side rendering (SSR) using themes based on PHP templates, but the platform also offers a robust and extensible REST API that allows developers to create headless sites and apps using any manner of frontend technologies.

### Setting up WordPress on your computer

One of the first things you might want to do is to set up a WordPress site on your personal computer.

Hands-down recommend using a tool called [LocalWP](https://localwp.com/). It’s a desktop application that allows you to create new WordPress sites on your computer with the click of a few buttons. It takes care of configuring PHP, MySQL and basic configuration of WordPress to connect to the database. It provides one-click support for enabling XDebug, allows access to your WordPress site using WP-CLI, and more. And, it’s free.

There are some alternatives for quickly spinning up a local environment:

- [DevKinsta](https://kinsta.com/blog/install-wordpress-locally/#how-to-install-wordpress-locally-with-devkinsta)
- [Lando](https://docs.lando.dev/config/wordpress.html)
- [MAMP](https://codex.wordpress.org/Installing_WordPress_Locally_on_Your_Mac_With_MAMP)
- [XAMP](https://themeisle.com/blog/install-xampp-and-wordpress-locally/)
- [Set it all up on your own](https://wpbeaches.com/setting-up-valet-on-macos-for-local-wordpress-development/)

When setting up your WordPress environment, please check that you have the [requirements](https://wordpress.org/plugins/cart-rest-api-for-woocommerce/#installation) for CoCart.

### Setting up WooCommerce

Once you have your WordPress environment setup. The next step is to install WooCommerce and setup your store. Add products, configure shipping, taxes, payment gateway etc.

A more detailed guide for setting up WooCommerce can be found at [WooCommerce.com documentation](https://woocommerce.com/documentation/plugins/woocommerce/getting-started/).

## How do Plugins Work with decoupled WordPress?

Many WordPress plugins were created with the assumption that WordPress is the CMS as well as the presentation layer, but that’s not always the case today. With the rise of decoupled WordPress, it’s common for WordPress to be used as a CMS, but not be used for its theme layer.

WordPress REST API allows for WordPress plugins to extend so their custom data can be used in decoupled applications. This is the same with extending CoCart if your a plugin author looking to add CoCart support to your plugin, or you're using a WooCommerce extension that doesn't have built-in support for CoCart already and you want to add support, checkout the following resources:

- 

<!-- FEEDBACK -->

---

🐞 Found a mistake, or have a suggestion? [Leave feedback about this document here.](https://github.com/co-cart/co-cart/issues/new?assignees=&labels=type%3A+documentation&template=doc_feedback.md&title=Feedback+on+./docs/intro-to-cocart.md)

<!-- /FEEDBACK -->
