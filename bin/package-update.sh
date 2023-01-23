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
	echo "$(tput -T xterm setaf "$1")$2$(tput -T xterm sgr0)"
}

# Checks if the packages folder exist and is not empty.
if [ -z "$(ls -A "plugins/cocart/packages/")" ]; then
	output 1 "./plugins/cocart/packages doesn't exist or empty!"
	output 1 "run \"composer install\" before proceed."
fi

output 3 "Cleaning up packages..."

# Remove dependencies from packages.
find ./plugins/cocart/packages -name ".distignore" -type f -delete
find ./plugins/cocart/packages -name ".editorconfig" -type f -delete
find ./plugins/cocart/packages -name ".gitattributes" -type f -delete
find ./plugins/cocart/packages -name ".gitignore" -type f -delete
find ./plugins/cocart/packages -name ".stylelintrc" -type f -delete
find ./plugins/cocart/packages -name ".stylelintignore" -type f -delete
find ./plugins/cocart/packages -name "Gruntfile.js" -type f -delete
find ./plugins/cocart/packages -name "package.json" -type f -delete
find ./plugins/cocart/packages -name "package-lock.json" -type f -delete
find ./plugins/cocart/packages -name "phpcs.xml" -type f -delete
find ./plugins/cocart/packages -name "phpunit.xml" -type f -delete
find ./plugins/cocart/packages -name "composer.json" -type f -delete
find ./plugins/cocart/packages -name "composer.lock" -type f -delete
find ./plugins/cocart/packages -name "renovate.json" -type f -delete

# Remove Source files
find ./plugins/cocart/packages -name "src" -type d -exec rm -rf {} +

# Remove POT files
find ./plugins/cocart/packages -name "languages" -type d -exec rm -rf {} +

# Remove licence files
find ./plugins/cocart/packages -name "LICENSE.*" -type f -delete
find ./plugins/cocart/packages -name "license.*" -type f -delete

# Remove readme files
find ./plugins/cocart/packages -name "README.md" -type f -delete
find ./plugins/cocart/packages -name "readme.txt" -type f -delete

# Remove uninstall files
find ./plugins/cocart/packages -name "uninstall.php" -type f -delete

output 2 "Package cleaning complete!"

# Replace text domains within packages with "cocart"
output 3 "Updating textdomains in packages..."
npm run packages:fix:textdomain

output 2 "Updated textdomains!"
output 6 "Packages updated!"

output 3 "Preparing plugin for testing..."
composer ready-build
