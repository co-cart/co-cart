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

# Checks if the "packages" folder exists.
if [ -z "$(ls -la "packages/")" ]; then
	output 1 "./packages folder doesn't exist or is empty!"
	output 1 "run \"composer install\" before proceed."
	exit;
fi

output 3 "Cleaning up packages..."

# Remove GitHub folders from packages.
find ./packages -name ".git" -type d -exec rm -rf {} +
find ./packages -name ".github" -type d -exec rm -rf {} +

# Remove dependencies from packages.
find ./packages -name ".distignore" -type f -delete
find ./packages -name ".editorconfig" -type f -delete
find ./packages -name ".gitattributes" -type f -delete
find ./packages -name ".gitignore" -type f -delete
find ./packages -name ".jshintrc" -type f -delete
find ./packages -name ".stylelintrc" -type f -delete
find ./packages -name ".stylelintignore" -type f -delete
find ./packages -name "Gruntfile.js" -type f -delete
find ./packages -name "package.json" -type f -delete
find ./packages -name "package-lock.json" -type f -delete
find ./packages -name "phpcs.xml" -type f -delete
find ./packages -name "phpunit.xml" -type f -delete
find ./packages -name "composer.json" -type f -delete
find ./packages -name "composer.lock" -type f -delete
find ./packages -name "renovate.json" -type f -delete

# Remove Source files
find ./packages -name "src" -type d -exec rm -rf {} +

# Remove POT files
find ./packages -name "languages" -type d -exec rm -rf {} +

# Remove licence files
find ./packages -name "LICENSE.*" -type f -delete
find ./packages -name "license.*" -type f -delete

# Remove readme files
find ./packages -name "README.md" -type f -delete
find ./packages -name "readme.txt" -type f -delete

# Remove uninstall files
find ./packages -name "uninstall.php" -type f -delete

output 2 "Package cleaning complete!"

output 4 "Moving packages into CoCart..."
if [ -d "packages/" ]; then
	cp -R ./packages/* ./plugins/cocart/packages/
	rm -rf ./packages
fi

# Replace text domains within packages with "cocart"
output 3 "Updating textdomains in packages..."
npm run packages:fix:textdomain

output 2 "Updated textdomains!"
output 6 "Packages updated!"

output 3 "Preparing plugin for build..."
composer ready-build
