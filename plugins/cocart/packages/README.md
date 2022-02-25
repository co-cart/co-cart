# External Packages

This directory holds Composer packages containing functionality developed outside of CoCart core.

## Developing new packages

To create a package and/or feature plugin for CoCart, you can base your plugin on the example package here:

https://github.com/co-cart/cocart-example-package

Packages require a Package class which `inits` the package and returns version information. This is shown in the example package above.

## Publishing a package

Your package should be published to Packagist. For example:

https://packagist.org/packages/co-cart/cocart-example-package

Or made available via your own private composer repository.

The package name in this case is `co-cart/cocart-example-package`.

## Installing Composer

You need Composer to use the packages. If you don't have it installed, go and check how to [install Composer](https://github.com/woocommerce/woocommerce/wiki/How-to-set-up-WooCommerce-development-environment) and then continue here.

## Including packages in core

Edit `composer.json` in the root directory and add the package and package version under the "require" section. For example:

```json
{
	"name": "co-cart/co-cart",
	"description": "CoCart is a REST API for WooCommerce. It focuses on the front-end of the store to manage the shopping cart allowing developers to build a headless store.",
	"homepage": "https://cocart.xyz",
	"type": "wordpress-plugin",
	"license": "GPL-3.0-or-later",
	"prefer-stable": true,
	"minimum-stability": "dev",
	"require": {
		"composer/installers": "1.9.0",
		"co-cart/cocart-example-package": "1.0.0"
	},
	...
```

Finally, you will need to tell core to load your package. Edit `src/packages.php` and add your package to the list of packages there:

```php
	protected static $packages = [
		'admin' => 'CoCart_Admin',
		'compatibility' => 'CoCart_Compatibility',
		'third-party' => 'CoCart_Third_Party',
		'example-package' => 'CoCart_Example_Package',
	];
```

## Installing packages

Once you have defined your package requirements, run

```
composer install
```

and that will install the required Composer packages.
