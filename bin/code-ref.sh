#!/bin/sh

# Output colorized strings
#
# Color codes:
# 0 - black
# 1 - red
# 2 - green
# 3 - yellow
# 4 - blue
# 5 - magenta
# 6 - cian
# 7 - white
output() {
	echo "$(tput setaf "$1")$2$(tput sgr0)"
}

# Checks if the packages folder exist and is not empty.
if [ -z "$(ls -A "plugins/cocart/packages/")" ]; then
	output 1 "./plugins/cocart/packages doesn't exist or empty!"
	output 1 "run \"composer install\" before proceed."
	exit;
fi

# Remove previous CoCart code reference build.
if [ -d "plugins/cocart-ref" ]; then
	output 1 "Removing previous CoCart code reference build..."
	rm -Rf "plugins/cocart-ref";
	output 2 "Previous CoCart code reference build removed."
fi

# Remove previous CoCart code reference source.
if [ -d "plugins/cocart-ref-source" ]; then
	output 1 "Removing previous CoCart code reference source..."
	rm -Rf "plugins/cocart-ref-source";
	output 2 "Previous CoCart code reference source removed."
fi

output 3 "Copying CoCart plugin to cocart-ref..."
cp -r ./plugins/cocart ./plugins/cocart-ref
output 2 "CoCart plugin copied."

output 3 "Preparing code reference build..."
find ./plugins/cocart-ref/packages -name "admin" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "compatibility" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "session-api" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "third-party" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "packages" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "languages" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "vendors" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "vendor" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name ".git" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name ".gitignore" -type f -exec rm -rf {} +
find ./plugins/cocart-ref -name "README.md" -type f -delete
find ./plugins/cocart-ref -name "src" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "composer.json" -type f -delete
find ./plugins/cocart-ref -name "license.txt" -type f -delete
find ./plugins/cocart-ref -name "readme.txt" -type f -delete
find ./plugins/cocart-ref -name "load-package.php" -type f -delete
find ./plugins/cocart-ref -name "cart-rest-api-for-woocommerce.php" -type f -delete
find ./plugins/cocart-ref -name "uninstall.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "class-cocart-autoloader.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "class-cocart-cli.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "class-cocart-install.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "class-cocart-session-handler-legacy.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "class-cocart.php" -type f -delete
find ./plugins/cocart-ref/includes -name "cocart-background-functions.php" -type f -delete
find ./plugins/cocart-ref/includes -name "cocart-task-functions.php" -type f -delete
find ./plugins/cocart-ref/includes -name "cocart-update-functions.php" -type f -delete
find ./plugins/cocart-ref/includes/classes -name "admin" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/includes -name "cli" -type d -exec rm -rf {} +
output 3 "Code reference build complete."

output 6 "Cloning code reference build for original source."
cp -r ./plugins/cocart-ref ./plugins/cocart-ref-source
output 3 "Code reference build cloned!"

output 6 "Replacing strings for import compatibility..."
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/Abstracts\\CoCart_Cart_Extension_Callback/Abstracts_CoCart_Cart_Extension_Callback/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Abstracts\\Session/CoCart_Abstracts_Session/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Abstracts/CoCart_Abstracts/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\CoCart_Data_Exception/CoCart_Data_Exception/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Core/CoCart_Core/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Help/CoCart_Help/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Install/CoCart_Install/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Logger/CoCart_Logger/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Session\\Handler/CoCart_Session_Handler/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Session/CoCart_Session/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Status/CoCart_Status/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\ProductsAPI\\DateTime/CoCart_ProductsAPI_DateTime/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\RestApi\\Authentication/CoCart_RestApi_Authentication/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\RestApi\\CartCache/CoCart_RestApi_CartCache/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\RestApi\\Callbacks\\UpdateCart/CoCart_RestApi_Callbacks_UpdateCart/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\RestApi\\Callbacks/CoCart_RestApi_Callbacks/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\RestApi/CoCart_RestApi/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Utilities\\RateLimits/CoCart_Utilities_RateLimits/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/CoCart\\Utilities/CoCart_Utilities/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\Exception/Exception/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\PasswordHash/PasswordHash/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WP_REST_Response/WP_REST_Response/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WP_Error/WP_Error/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Background_Process/WC_Background_Process/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Cart/WC_Cart/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Cache_Helper/WC_Cache_Helper/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Customer/WC_Customer/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Data_Store/WC_Data_Store/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Product/WC_Product/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Rate_Limiter/WC_Rate_Limiter/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Session/WC_Session/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\WC_Validation/WC_Validation/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\Automattic\\WooCommerce\\Checkout\\Helpers\\ReserveStock/Automattic_WooCommerce_Checkout_Helpers_ReserveStock/g' {} \;
find ./plugins/cocart-ref/includes/ -name '*.php' -type f -exec sed -i -e 's/\\Automattic\\WooCommerce\\Utilities\\StringUtil/Automattic_WooCommerce_Utilities_StringUtil/g' {} \;
find ./plugins/cocart-ref/includes/classes -name "class-cocart-helpers.php" -type f -exec sed -i -e 's/\\Appsero\\Client/Appsero_Client/g' {} \;
output 3 "Strings replaced."

output 7 "Code reference can now be imported!"
exit;