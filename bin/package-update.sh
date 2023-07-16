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
	output 1 "run \"composer install\" before you proceed."
	exit;
fi

output 4 "Copying packages into CoCart..."
if [ -d "packages/" ]; then
	cp -R ./packages/* ./plugins/cocart/packages/
	#rm -rf ./packages
fi

# Replace text domains within "cocart/packages"
output 3 "Updating textdomains in packages..."
npm run packages:fix:textdomain

output 2 "Updated textdomains!"
output 6 "Packages updated!"

output 3 "Preparing plugin for testing..."
composer prep-test-build
