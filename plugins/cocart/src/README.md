# CoCart `src` files

## Table of contents

  * [Installing Composer](#installing-composer)
    + [Updating the autoloader class maps](#updating-the-autoloader-class-maps)
  * [Installing packages](#installing-packages)
  * [The `Core` namespace](#the-core-namespace)
  * [Defining new actions and filters](#defining-new-actions-and-filters)

This directory is home to new CoCart class files under the `CoCart` namespace using [PSR-4](https://www.php-fig.org/psr/psr-4/) file naming. This is to take full advantage of autoloading.

Ideally, all the new code for CoCart should consist of classes following the PSR-4 naming and living in this directory, and the code in [the `includes` directory](https://github.com/co-cart/co-cart/blob/dev/plugins/cocart/includes/README.md) should receive the minimum amount of changes required for bug fixing. This will not always be possible but that should be the rule of thumb.

## Installing Composer

Composer is used to generate autoload class-maps for the files here. The stable release of CoCart comes with the autoloader, however, if you're running a development version you'll need to use Composer.

If you don't have Composer installed, go and check how to [install Composer](https://github.com/woocommerce/woocommerce/wiki/How-to-set-up-WooCommerce-development-environment) and then continue here.

### Updating the autoloader class maps

If you add a class to CoCart you need to run the following to ensure it's included in the autoloader class-maps:

```
composer dump-autoload
```


## Installing packages

To install the packages CoCart requires, from the main directory run:

```
composer install
```

To update packages run:

```
composer update
```

## The `Core` namespace

While it's up to the developer to choose the appropriate namespaces for any newly created classes, and those namespaces should make sense from a semantic point of view, there's one namespace that has a special meaning: `CoCart\Core`.

Classes in `CoCart\Core` are meant to be CoCart infrastructure code. The code in this namespace is considered "internal".

What this implies for you as developer depends on what type of contribution are you making:

* **If you are working on CoCart core:** When you need to add a new class please think carefully if the class could be useful for plugins. If you really think so, add it to the appropriate namespace rooted at `CoCart`. If not, add it to the appropriate namespace but rooted at `CoCart\Core`.
  * When in doubt, always make the code internal. If an internal class is later deemed to be worth being made public, the change can be made easily (by just changing the class namespace) and nothing will break. Turning a public class into an internal class, on the other hand, is impossible since it could break existing plugins.

* **If you are a plugin developer:** You should **never** use code from the `CoCart\Core` namespace in your plugins. Doing so might cause your plugin to break in future versions of CoCart.


## Defining new actions and filters

WordPress' hooks (actions and filters) are a very powerful extensibility mechanism and it's the core tool that allows CoCart extensions to be developed. However it has been often (ab)used in the CoCart core codebase to drive internal logic, e.g. an action is triggered from within one class or function with the assumption that somewhere there's some other class or function that will handle it and continue whatever processing is supposed to happen.

In order to keep the code as easy as reasonably possible to read and maintain, **hooks shouldn't be used to drive CoCart's internal logic and processes**. If you need the services of a given class or function, please call these directly (by using dependency-injection as appropriate to get access to the desired service). **New hooks should be introduced only if they provide a valuable extension point for plugins**.

As usual, there might be reasonable exceptions to this; but please keep this rule in mind whenever you consider creating a new hook.
