# Changelog for CoCart

## v2.0.0
* NEW: REST API endpoint. CoCart is now an individual API and is no longer nested with WooCommerce core REST API.
* NEW: A check to see if the cart is empty and fallback to the cart in session if one exists.
* NEW: Get a specific customers cart via their customer ID number. Only works if persistent cart was left enabled in WooCommerce.
* Added: New filter to allow additional checks before the item is added to the cart.
* Changed: Filter and Action Hook names. See documentation for details.
* Improved: Checking for items already in the cart.

## v1.0.6
* Changed: If the cart is empty, the response returns an empty array. - Issue #33 Feedback provided by @joshuaiz
* Improved: Updating items by adding a check to see if there is enough stock. Thanks to @DennisMatise

## v1.0.5
* Fixed: Variation and cart item data validation callback. - Issue #40 Thanks to @DennisMatise
* Fixed: A fatal error that caused errors not to return properly. - Issue #35 Thanks to @skunkbad
* Changed: Name of the plugin is now CoCart. The plugin slug will remain the same.

## v1.0.4
* Fixed: Return response for numeric thanks to @campusboy87
* Fixed: Fatal error for adding and updating items when validating the callback `is_numeric`. - Issue #30

## v1.0.3
* Fixed: Syntax error for including cart controller for sites running versions of PHP lower than 7. Thanks to @Mr-AjayM for another contribution.
* Fixed: Validation of `cart_item_key` when removing, restoring or updating an item. Item keys starting with a letter were returning false. Reported by @Janie20.
* Tested up to WooCommerce v3.3.5 and up to WordPress v4.9.5

## v1.0.2
* Fixed: Invalid Argument Error should the cart be empty. Now returns "Cart is empty" properly. Thanks to @Mr-AjayM for the contribution.

## v1.0.1
* Added: Fetch current cart item data before it is updated.
* Added: New endpoint to restore, remove and update items in cart due to a conflict that prevented from registering the route.
* Corrected: Fetching cart item key as integer to a clean string.
* Corrected: Had response messages for updating quantity backwards. Oops!
* Improved: Made sure it returns a response if the cart is empty.
* Enhanced: Added a check to see if the cart has any items before calculating totals.

## v1.0.0
* Initial version.
