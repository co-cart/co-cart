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

# Remove previous CoCart build
if [ -d "../cocart" ]; then
	output 1 "Removing previous CoCart build..."
	rm -Rf "../cocart";
	output 2 "Previous CoCart build removed."
fi

output 3 "Copying CoCart build to wp-content/plugins/..."
cp -r ./plugins/cocart ./../cocart
output 2 "CoCart build copied."

output 3 "Cleaning remaining dev files..."
find ./../cocart -name ".git" -type d -exec rm -rf {} +
find ./../cocart -name "README.md" -type f -delete
output 2 "CoCart can now be activated from your WordPress dashboard."

output 4 "Changing directory to wp-content/plugins/cocart..."
cd "../cocart"

output 3 "Installing Composer..."
composer install

output 3 "Updating autoloader classmaps..."
composer dump-autoload
output 2 "Done!"

output 4 "Returning to developement folder."
cd -
output 2 "Done!"