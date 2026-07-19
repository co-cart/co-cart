# WARNING! DO NOT PUT CUSTOM TRANSLATIONS HERE!

CoCart Community will delete all custom translations placed in this directory.

## Where to put my translations for CoCart Community?

Put your custom CoCart Community translations in your WordPress language directory, located at: `WP_LANG_DIR . "/cart-rest-api-for-woocommerce/{$textdomain}-{$locale}.mo";`

## How do I translate CoCart Community?

If you want to help translate CoCart Community, please visit our [GlotPress](https://translate.cocartapi.com/projects/cart-rest-api-for-woocommerce/). There you can select the language to translate. If a language is not listed the please request it.

If CoCart Community is already 100% translated for your language, join anyway! The language files are regularly updated with new strings that need translation and will likely be added soon.

## String localization guidelines

 1. Use `cocart-core` textdomain in all strings.
 2. When using dynamic strings in printf/sprintf, if you are replacing > 1 string use numbered args. e.g. `Test %s string %s.` would be `Test %1$s string %2$s.`
 3. Use sentence case. e.g. `Some Thing` should be `Some thing`.
 4. Avoid HTML. If needed, insert the HTML using sprintf.

For more information, see WP core document [i18n for WordPress Developers](https://codex.wordpress.org/I18n_for_WordPress_Developers).
