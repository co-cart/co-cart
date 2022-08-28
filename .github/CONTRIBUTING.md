# Contributing to CoCart ✨

CoCart helps power many headless stores across the internet, and your help making it even more awesome will be greatly appreciated :)

There are many ways to contribute to the project!

- [Translating strings into your language](#translating-cocart).
- Answering questions on the various CoCart communities like the [WP.org support forums](https://wordpress.org/support/plugin/cart-rest-api-for-woocommerce/).
- Testing open [issues](https://github.com/co-cart/co-cart/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc) or [pull requests](https://github.com/co-cart/co-cart/pulls?q=is%3Apr+is%3Aopen+sort%3Aupdated-desc) and sharing your findings in a comment.
- Testing [CoCart beta versions and release candidates](https://github.com/co-cart/cocart-beta-tester). Those are announced in the [CoCart development blog](https://cocart.xyz/news/).
- Submitting fixes, improvements, and enhancements.

If you wish to contribute code, please read the information in the sections below. Then [fork](https://help.github.com/articles/fork-a-repo/) CoCart, commit your changes, and [submit a pull request](https://help.github.com/articles/using-pull-requests/) 🎉

I use the `good first issue` label to mark issues that are suitable for new contributors. You can find all the issues with this label [here](https://github.com/co-cart/co-cart/issues?q=is%3Aissue+is%3Aopen+sort%3Aupdated-desc+label%3A%22good+first+issue%22).

CoCart is licensed under the GPLv3+, and all contributions to the project will be released under the same license. You maintain copyright over any contribution you make, and by submitting a pull request, you are agreeing to release that contribution under the GPLv3+ license.

If you have questions about the process to contribute code or want to discuss details of your contribution, you can contact CoCart core developers on the #core channel in the [CoCart community Slack](https://cocart.xyz/community/).

## Getting started

- [How to set up WooCommerce development environment](https://github.com/woocommerce/woocommerce/wiki/How-to-set-up-WooCommerce-development-environment)
- [String localization guidelines](#string-localization-guidelines)

## Coding Guidelines and Development 🛠

- Ensure you stick to the [WordPress Coding Standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/)
- Ensure you use LF line endings in your code editor. Use [EditorConfig](http://editorconfig.org/) if your editor supports it so that indentation, line endings and other settings are auto configured.
- When committing, reference your issue number (#1234) and include a note about the fix.
- Ensure that your code supports the minimum supported versions of PHP and WordPress; this is shown at the top of the `readme.txt` file.
- Push the changes to your fork and submit a pull request on the master branch of the CoCart repository.
- Make sure to write good and detailed commit messages (see [this post](https://chris.beams.io/posts/git-commit/) for more on this) and follow all the applicable sections of the pull request template.
- Please avoid modifying the changelog directly or updating the .pot files. These will be updated by the CoCart team.

## Translating CoCart

CoCart can be translated in two areas. For CoCart it is recommended to translate via the [project on translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/cart-rest-api-for-woocommerce/). You can join the localization team of your language and help by translating CoCart there. [Find more about using joining a language team and using GlotPress](https://make.wordpress.org/polyglots/handbook/tools/glotpress-translate-wordpress-org/).

The alternative is to translate CoCart on [translate.cocart.xyz](https://translate.cocart.xyz/projects/cart-rest-api-for-woocommerce/)

If CoCart is already 100% translated for your language, join the team anyway! I regularly update the language files, and new strings that need translation will likely be added soon.

## String localization guidelines

 1. Use `cart-rest-api-for-woocommerce` textdomain in all strings.
 2. When using dynamic strings in printf/sprintf, if you are replacing > 1 string use numbered args. e.g. `Test %s string %s.` would be `Test %1$s string %2$s.`
 3. Use sentence case. e.g. `Some Thing` should be `Some thing`.
 4. Avoid HTML. If needed, insert the HTML using sprintf.

For more information, see WP core document [i18n for WordPress Developers](https://codex.wordpress.org/I18n_for_WordPress_Developers).
