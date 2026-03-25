#!/usr/bin/env bash

if [[ ${RUN_UNIT_TESTS} == 0 ]]; then
	exit 0
fi

if [ $# -lt 3 ]; then
	echo "usage: $0 <db-name> <db-user> <db-pass> [db-host] [wp-version] [wc-version]"
	exit 1
fi

DB_NAME=$1
DB_USER=$2
DB_PASS=$3
DB_HOST=${4-localhost}
WP_VERSION=${5-latest}
WC_VERSION=${6-latest}

TESTS_LIB_DIR="${WP_TESTS_DIR-/tmp/wordpress-tests-lib}"
TESTS_WP_DIR="${WP_CORE_DIR-/tmp/wordpress}"
# TESTS_WC_DIR= # WCS expects the woocommerce repo inside the parent folder of this plugin.

PLUGINS_DIR=$(cd ".."; pwd)

echo -e "TESTS_LIB_DIR:\n"
echo -e "$TESTS_LIB_DIR\n"
echo -e "Plugins dirname:\n"
echo -e "$PLUGINS_DIR\n"
echo -e "TESTS_WP_DIR:\n"
echo -e "$TESTS_WP_DIR\n"

download() {
	if command -v curl > /dev/null; then
		curl -s "$1" > "$2";
	elif command -v wget > /dev/null; then
		wget -nv -O "$2" "$1"
	else
		echo "Error: curl or wget is required to download files."
		exit 1
	fi
}

version_gt() {
	test "$(printf '%s\n' "$@" | sort -V | head -n 1)" != "$1";
}

if [ -z $WC_VERSION ] || [ $WC_VERSION == 'latest' ]; then
	echo "WooCommerce version not provided. Fetching latest tag from org..."
	download https://api.wordpress.org/plugins/info/1.0/woocommerce.json ~/woocommerce.json
	WC_VERSION=$(grep -o '"version":"[^"]*' ~/woocommerce.json | sed 's/"version":"//')
	echo "WooCommerce version fetched: $WC_VERSION"
fi

# Only need to check normal versions. Betas and rcs are not supported.
if [[ $WP_VERSION =~ ^[0-9]+(\.[0-9]+)*$ ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	# http serves a single offer, whereas https serves multiple. we only want one
	download http://api.wordpress.org/core/version-check/1.7/ ~/wp-latest.json
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' ~/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' ~/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"

	rm ~/wp-latest.json
fi

set -e

install_wp() {
	echo "Creating WordPress directory: $TESTS_WP_DIR"
	if [ -d $TESTS_WP_DIR ]; then
		rm -rf $TESTS_WP_DIR
	fi

	mkdir -p $TESTS_WP_DIR

	# If the version is nightly covert to the latest version.
	if [[ $WP_VERSION == 'nightly' ]]; then
		WP_VERSION='latest'
		echo "Warning: Nightly WP_VERSION builds are not supported. Using latest version instead."
	fi

	if [ $WP_VERSION == 'latest' ]; then
		local TAR_FILE='https://wordpress.org/latest.tar.gz'
	else
		local TAR_FILE="https://wordpress.org/wordpress-$WP_VERSION.tar.gz"
	fi

	download $TAR_FILE ~/wordpress.tar.gz
	tar --strip-components=1 -zxmf ~/wordpress.tar.gz -C $TESTS_WP_DIR

	download https://raw.github.com/markoheijnen/wp-mysqli/master/db.php $TESTS_WP_DIR/wp-content/db.php

	if [ ! -d $TESTS_WP_DIR/wp-content/uploads ]; then
		mkdir -p $TESTS_WP_DIR/wp-content/uploads
	fi
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	echo "Creating test suite directory: $TESTS_LIB_DIR"
	if [ -d $TESTS_LIB_DIR ]; then
		rm -rf $TESTS_LIB_DIR
	fi

	# set up testing suite if it doesn't yet exist
	if [ ! -d $TESTS_LIB_DIR ]; then
		# set up testing suite
		mkdir -p $TESTS_LIB_DIR
		svn co --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/ $TESTS_LIB_DIR/includes
		svn co --quiet --ignore-externals https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/ $TESTS_LIB_DIR/data
	fi

	cd $TESTS_LIB_DIR

	if [ ! -f wp-tests-config.php ]; then
		download https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php "$TESTS_LIB_DIR"/wp-tests-config.php
		sed $ioption "s:dirname( __FILE__ ) . '/src/':'$TESTS_WP_DIR/':" "$TESTS_LIB_DIR"/wp-tests-config.php
		sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" "$TESTS_LIB_DIR"/wp-tests-config.php
		sed $ioption "s/yourusernamehere/$DB_USER/" "$TESTS_LIB_DIR"/wp-tests-config.php
		sed $ioption "s/yourpasswordhere/$DB_PASS/" "$TESTS_LIB_DIR"/wp-tests-config.php
		sed $ioption "s|localhost|${DB_HOST}|" "$TESTS_LIB_DIR"/wp-tests-config.php
	fi

}

install_db() {
	# parse DB_HOST for port or socket references
	if [[ "$DB_HOST" == :* ]]; then
		# It's a socket, e.g. ':/path/to/socket'.
		local DB_HOSTNAME=""
		local DB_SOCK_OR_PORT=$(echo "$DB_HOST" | cut -d':' -f2-)
	elif [[ "$DB_HOST" == *:* ]]; then
		# It's a URL, e.g. 'localhost:3306' or 'http://localhost:3306'.
		local DB_HOSTNAME=$(echo "$DB_HOST" | rev | cut -d':' -f2- | rev) # everything before the last colon
		local DB_SOCK_OR_PORT=$(echo "$DB_HOST" | rev | cut -d':' -f1 | rev) # everything after the last colon
	else
		# It's a hostname, e.g. 'localhost'.
		local DB_HOSTNAME=$DB_HOST
		local DB_SOCK_OR_PORT=""
	fi

	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	# Drop target database if already present.
	echo "drop database if exists ${DB_NAME}" | mysql --user=${DB_USER} --password=${DB_PASS}${EXTRA}

	# Create database.
	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA || true

	# Get the current PHP version
	php_version=$(php -r 'echo PHP_VERSION;')

	# For PHP version 7.0, change the default mysql authentication plugin to "mysql_native_password"
	# as "caching_sha2_password" is not supported
	# This is not needed if using MySQL server from LocalWP (and it will fail)
	if [[ "$php_version" == *"7.0"* ]]; then

		# Update mysql configuration file.
		echo "[mysqld]" | sudo tee -a /etc/mysql/my.cnf
		echo "default_authentication_plugin=mysql_native_password" | sudo tee -a /etc/mysql/my.cnf

		echo "Restarting MYSQL server..."
		sudo systemctl restart mysql.service

		# Update root user.
		echo "ALTER USER '${DB_USER}'@'$DB_HOSTNAME' IDENTIFIED WITH mysql_native_password BY '${DB_PASS}'" | mysql --user=${DB_USER} --password=${DB_PASS}

		# Debug statement
		# echo "SELECT @@GLOBAL.default_authentication_plugin" | mysql --user=${DB_USER} --password=${DB_PASS}
	fi
}

install_woocommerce() {

	cd $PLUGINS_DIR

	# Clean up old woocommerce folder.
	if [ -e woocommerce ]; then
			rm -rf woocommerce
		fi

	if [ ! -d woocommerce ]; then

		if [ -e wc-tmp ]; then
		rm -rf wc-tmp
	fi

	if [ -d wc-tmp-monorepo ]; then
		rm -rf wc-tmp-monorepo
	fi

	# Fallback if latest tag doesn't exist.
	if [ $(git -c 'versionsort.suffix=-' ls-remote --exit-code --refs --tags --sort='version:refname' https://github.com/woocommerce/woocommerce $WC_VERSION) ]; then
		latestTag=$WC_VERSION
	else
		# Get latest tags from remote
		latestTag=$(git -c 'versionsort.suffix=-' ls-remote --exit-code --refs --tags --sort='version:refname' https://github.com/woocommerce/woocommerce '[0-9]*.[0-9]*.[0-9]*'  | sed '/-/d' | tail --lines=1 | cut -d '/' -f3)
	fi

	git clone --depth=1 --branch=$latestTag https://github.com/woocommerce/woocommerce wc-tmp-monorepo
	ln -s "$PLUGINS_DIR/wc-tmp-monorepo/plugins/woocommerce" "$PLUGINS_DIR/woocommerce"
	cd wc-tmp-monorepo/plugins/woocommerce

	composer install --no-dev --no-scripts --optimize-autoloader

	fi
}

install_wp
install_test_suite
install_db
install_woocommerce
