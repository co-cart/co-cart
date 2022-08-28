# External Packages

This directory holds Composer packages containing functionality developed outside of CoCart core.

## Developing new packages

To create a package and/or feature plugin for CoCart, you can base your plugin on the example package here:

https://github.com/co-cart/cocart-example-package

Packages require a Package class which `init` the package and returns version information. This is shown in the example package above.

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
	"description": "Customizable REST API for WooCommerce that lets you build headless ecommerce using your favorite technologies.",
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

Next, if your package contains user translatable strings you'll need to edit `bin/package-update.sh` and instruct it to change your package textdomain to the `cart-rest-api-for-woocommerce` textdomain. For example:

```
find ./packages/cocart-example-package -iname '*.php' -exec sed -i.bak -e "s/, 'cocart-example-package'/, 'cart-rest-api-for-woocommerce'/g" {} \;
```

```php
	protected static $packages = [
		'admin' => '\\CoCart\\Admin\\Package',
		'compatibility' => '\\CoCart\\Compatibility\\Package',
		'third-party' => '\\CoCart\\ThirdParty\\Package',
		'example-package' => '\\CoCart\\ExamplePackage\\Package',
	];
```

## Installing packages

Once you have defined your package requirements, run

```
composer install
```

and that will install the required Composer packages.

### Using packages

To use something from a package, you have to declare it at the top of the file before any other instruction, and then use it in the code. For example:

```php
use CoCart\ExamplePackage\ExampleClass;

// other code...

$class = new ExampleClass();
```

If you need to rule out conflicts, you can alias it:

```php
use CoCart\ExamplePackage\ExampleClass as Example_Class_Alias;

// other code...

$class = new Example_Class_Alias();
```
