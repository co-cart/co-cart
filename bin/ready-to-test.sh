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
if [ -z "$(ls -la "plugins/cocart/packages/")" ]; then
	output 1 "./plugins/cocart/packages doesn't exist or empty!"
	output 1 "run \"composer install\" before proceed."
	exit;
fi

# Remove previous CoCart build
if [ -d "../cocart" ]; then
	output 1 "Removing previous CoCart build..."
	rm -Rf "../cocart";
	output 2 "Previous CoCart build removed."
fi

output 3 "Copying CoCart build to wp-content/plugins/..."
cp -r ./plugins/cocart ./../cocart
output 2 "CoCart build copied."

output 4 "Changing directory to wp-content/plugins/cocart..."
cd "../cocart"

output 3 "Installing Composer..."
composer install --no-autoloader
composer require appsero/client

output 3 "Creating autoloader..."
composer prep-autoload
composer dump-autoload

output 3 "Cleaning remaining dev files..."
find ./../cocart -name ".git" -type d -exec rm -rf {} +
find ./../cocart -name ".github" -type d -exec rm -rf {} +
find ./../cocart -name "README.md" -type f -delete
output 2 "Done!"

output 4 "Returning to developement folder."
cd -
output 2 "CoCart can now be activated from your WordPress dashboard."
