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

if [ ! -d "plugins/cocart/packages/" ]; then
	output 1 "./plugins/cocart/packages doesn't exist!"
	output 1 "run \"composer install\" before proceed."
fi

# Remove previous CoCart ref build
if [ -d "plugins/cocart-ref" ]; then
	output 1 "Removing previous CoCart ref build..."
	rm -Rf "plugins/cocart-ref";
	output 2 "Previous CoCart ref build removed."
fi

output 3 "Copying CoCart build to cocart-ref..."
cp -r ./plugins/cocart ./plugins/cocart-ref
output 2 "CoCart build copied."

output 3 "Preparing ref build..."
find ./plugins/cocart-ref/packages -name "admin" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "compatibility" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "session-api" -type d -exec rm -rf {} +
find ./plugins/cocart-ref/packages -name "third-party" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "packages" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "languages" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "vendors" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "vendor" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name ".git" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "README.md" -type f -delete
find ./plugins/cocart-ref -name "src" -type d -exec rm -rf {} +
find ./plugins/cocart-ref -name "composer.json" -type f -delete
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

output 2 "Code Reference can now import this build."
