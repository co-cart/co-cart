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
if [ -z "$(ls -la "plugins/build/packages/")" ]; then
	output 1 "./plugins/build/packages folder doesn't exist. Development build failed!"
	output 1 "run \"composer cocart-install\" then \"composer prep-release-build\". Don't run this script directly."
	exit;
fi

output 3 "Cleaning up packages..."

# Remove GitHub folders from packages.
find ./plugins/build/packages -name ".git" -type d -exec rm -rf {} +
find ./plugins/build/packages -name ".github" -type d -exec rm -rf {} +

# Remove dependencies from packages.
find ./plugins/build/packages -name ".distignore" -type f -delete
find ./plugins/build/packages -name ".editorconfig" -type f -delete
find ./plugins/build/packages -name ".gitattributes" -type f -delete
find ./plugins/build/packages -name ".gitignore" -type f -delete
find ./plugins/build/packages -name ".jshintrc" -type f -delete
find ./plugins/build/packages -name ".stylelintrc" -type f -delete
find ./plugins/build/packages -name ".stylelintignore" -type f -delete
find ./plugins/build/packages -name "Gruntfile.js" -type f -delete
find ./plugins/build/packages -name "package.json" -type f -delete
find ./plugins/build/packages -name "package-lock.json" -type f -delete
find ./plugins/build/packages -name "phpcs.xml" -type f -delete
find ./plugins/build/packages -name "phpunit.xml" -type f -delete
find ./plugins/build/packages -name "composer.json" -type f -delete
find ./plugins/build/packages -name "composer.lock" -type f -delete
find ./plugins/build/packages -name "renovate.json" -type f -delete

# Remove Source files
find ./plugins/build/packages -name "src" -type d -exec rm -rf {} +

# Remove POT files
find ./plugins/build/packages -name "languages" -type d -exec rm -rf {} +

# Remove licence files
find ./plugins/build/packages -name "LICENSE.*" -type f -delete
find ./plugins/build/packages -name "license.*" -type f -delete

# Remove readme files
find ./plugins/build/packages -name "README.md" -type f -delete
find ./plugins/build/packages -name "readme.txt" -type f -delete

# Remove uninstall files
find ./plugins/build/packages -name "uninstall.php" -type f -delete

output 2 "Package cleaning complete!"
