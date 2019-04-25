# Changelog for CoCart

## v1.1.1 - 25th April, 2019

* Checked: Compatibility with WooCommerce v3.6.2
* Updated: Changelog. Forgot to do it in last update.

## v1.1.0 - 23rd April, 2019

* Compatible: Support for WooCommerce 3.6+

## v1.0.9 - 5th April, 2019

* Compatible: Tested up to WordPress 5.1.1
* Compatible: Tested up to WooCommerce 3.5.7
* Tweaked: Support link in plugin row.

## v1.0.8 - 18th February, 2019

* Compatible: Ready for WordPress 5.1 release. 🎊
* Added: Review link to plugins row.

## v1.0.7 - 28th January, 2019

* Tweaked: Clear cart now clears cart in session if the user is logged in. - Thanks to @elron for the patch.

## v1.0.6 - 12th November, 2018

* Changed: If the cart is empty, the response returns an empty array. - Issue #33 Feedback provided by @joshuaiz
* Improved: Updating items by adding a check to see if there is enough stock. Thanks to @DennisMatise

## v1.0.5 - 11th October, 2018

* Fixed: Variation and cart item data validation callback. - Issue #40 Thanks to @DennisMatise
* Fixed: A fatal error that caused errors not to return properly. - Issue #35 Thanks to @skunkbad
* Changed: Name of the plugin is now CoCart. The plugin slug will remain the same.

## v1.0.4 - 5th July, 2018

* Fixed: Return response for numeric thanks to @campusboy87
* Fixed: Fatal error for adding and updating items when validating the callback `is_numeric`. - Issue #30

## v1.0.3 - 22nd April, 2018

* Fixed: Syntax error for including cart controller for sites running versions of PHP lower than 7. Thanks to @Mr-AjayM for another contribution.
* Fixed: Validation of `cart_item_key` when removing, restoring or updating an item. Item keys starting with a letter were returning false. Reported by @Janie20.
* Tested up to WooCommerce v3.3.5 and up to WordPress v4.9.5

## v1.0.2 - 31st March, 2018

* Fixed: Invalid Argument Error should the cart be empty. Now returns "Cart is empty" properly. Thanks to @Mr-AjayM for the contribution.

## v1.0.1 - 2nd March, 2018

* Added: Fetch current cart item data before it is updated.
* Added: New endpoint to restore, remove and update items in cart due to a conflict that prevented from registering the route.
* Corrected: Fetching cart item key as integer to a clean string.
* Corrected: Had response messages for updating quantity backwards. Oops!
* Improved: Made sure it returns a response if the cart is empty.
* Enhanced: Added a check to see if the cart has any items before calculating totals.

## v1.0.0

* Initial version. Released on WordPress.org on 26th February, 2018
