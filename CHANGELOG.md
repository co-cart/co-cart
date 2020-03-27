# Changelog for CoCart

## v2.0.12 - 27th March, 2020

* Tested: Compatible with WordPress 5.4
* Added: Upgrade notice message on plugins upgrade screen.

## v2.0.11 - 25th March, 2020

* Removed: `cocart_docs_url` filter for changing documentation link.
* Tested: Compatible with WooCommerce v4.0.x
* Updated: Getting Started page and removed `cocart_getting_started_doc_url` filter for the documentation button.
* Updated: Plugin action link for upgrading to CoCart Pro.
* Updated: Upgrade notices.

> Please temporarily deactivate CoCart and CoCart Pro if you have it before updating WooCommerce to version 4.0+ as there is an activation order issue I am still working on fixing. Once you have upgraded WooCommerce simply reactivate CoCart.

## v2.0.10 - 22nd March, 2020

* Tweaked: Refresh totals parameter is now set to `true` by default when item is updated.

## v2.0.9 - 19th March, 2020

* Corrected: Passed parameter to get specific customers cart.
* Tweaked: Validation of returning persistent cart.

## v2.0.8 - 6th March, 2020

* Dev: Added filter `cocart_return_empty_cart` to empty cart response so developers can use it as they see fit.

## v2.0.7 - 5th March, 2020

* Disabled: Cookie authentication REST check, only if site is secure when authenticating the basic method.
* Removed: Filter for session class handler as we need it to be untouched.
* Tested: Compatible with WooCommerce v3.9.x
* Tweaked: Use `get_current_user_id()` instead of `is_user_logged_in()` to check if user is logged in.

> The cookie check is only disabled when making a request with CoCart.

## v2.0.6 - 1st October, 2019

* Added: Link to translate CoCart on the plugin row.
* Tweaked: Upgrade admin notice for next release.
* Tweaked: URL to latest beta news under the plugin row.

## v2.0.5 - 14th September, 2019

* Added: Support for WooCommerce's authentication method.

## v2.0.4 - 26th August, 2019

* Added: More FAQ's to readme.txt file for the WordPress plugin directory.
* Changed: Title of the plugin in readme.txt file to improve SEO Results.
* Changed: Minimum WooCommerce version required and supported is v3.6.
* Tweaked: Upgrade link now shows always once plugin is installed, not after 1 week.
* Tweaked: Upgrade link colour changed from green to red to stand out more.

## v2.0.3 - 19th August, 2019

* Added: A notice under the plugin row providing information on future versions coming that require your feedback.
* Tested: Compatible with WooCommerce v3.7
* Tweaked: Admin body class for CoCart page.
* Updated: Documentation URL has changed to <https://docs.cocart.xyz>

## v2.0.2 - 19th July, 2019

* Tweaked: Updated link to getting started page if CoCart was installed via WP-CLI.

## v2.0.1 - 18th July, 2019

* Tweaked: `get_cart_contents_count()` is now called static.
* Tweaked: Added check for cart totals to make sure they are set before falling back to cart totals in session.
* Dev: Added filter `cocart_update_item` for the response when updating an item.
* Dev: Tweaked CoCart page in the WordPress dashboard to support sections.

## v2.0.0 - 3rd July, 2019

* NEW: REST API namespace. CoCart is now an individual API and is no longer nested with WooCommerce's core REST API.
* NEW: Check to see if the cart is set before falling back to the cart in session if one exists.
* NEW: Get a specific customers cart via their customer ID number. - See documentation for details.
* NEW: Product title also returns besides just the product name when getting the cart.
* NEW: Product price also returns when getting the cart.
* Changed: Filter and Action Hook names in new API. - See documentation for details.
* Improved: Complexity of functions for better performance and usage.
* Tweaked: Added checking for items already in the cart.
* Tweaked: Check if cart is empty before removing an item.
* Tweaked: Responses for adding, updating, removing and restoring items to return whole cart if requested.
* Tweaked: Responses for updating items to return the quantity of item along with message.
* Tweaked: Totals can now return once calculated if requested.
* Tweaked: Totals now return from session and can be returned pre-formatted if requested. - See documentation for details.
* Tweaked: New option to refresh cart totals once item has been added or updated.
* Dev: Added action hooks for getting cart, cart is cleared, item added, item removed and item restored.
* Dev: Added filter to allow additional checks before the item is added to the cart.
* Dev: Added filter to apply additional data to return when cart is returned.
* Dev: Added filter to change the size of the thumbnail returned.
* Dev: Added new option to return cart raw if requested.

## v1.2.3 - 7th June, 2019

* Added: Upgrade warning notice in preparation for CoCart v2 release.

## v1.2.2 - 30th May, 2019

* Fix: Plugin would fail to install date and version for future updates.
* Fix: Plugin would fail to redirect to Getting Started page once activated.

> Both of these failed due to reverting a change in the last update to fix the API from crashing.

## v1.2.1 - 21st May, 2019

* HOTFIX: Reverted change for including classes so **WC_VERSION** constant was defined first.

## v1.2.0 - 20th May, 2019

* NEW: Add Getting Started page to introduce users to view the documentation once installed.
* NEW: Plugin review notice appears after the first week of use.
* Tweaked: Improved code base of the plugin, **NOT** the REST-API.

## v1.1.2 - 17th May, 2019

* Tweaked: Allow removing of items via update logic if quantity is zero. Thanks to [@SHoogland](https://github.com/SHoogland)

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
