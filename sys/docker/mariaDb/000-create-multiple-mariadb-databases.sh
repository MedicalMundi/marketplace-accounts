#!/bin/bash

set -e
set -u

function create_database() {
	local database=$1
	echo "  Creating Database '$database' for '$MYSQL_USER'"
	mariadb --user root \
		--password="$MYSQL_ROOT_PASSWORD" \
		--execute='CREATE DATABASE IF NOT EXISTS `'$database'`;'

	mariadb --user root \
    		--password="$MYSQL_ROOT_PASSWORD" \
    		--execute='GRANT ALL PRIVILEGES ON `'$database'`.* TO `'$MYSQL_USER'`@`%`;'

	mariadb --user root \
			--password="$MYSQL_ROOT_PASSWORD" \
    		--execute='SHOW GRANTS  FOR `'$MYSQL_USER'`;'
}

if [ -n "$MYSQL_MULTIPLE_DATABASES" ]; then
	echo "Multiple database creation requested: $MYSQL_MULTIPLE_DATABASES"
	for db in $(echo $MYSQL_MULTIPLE_DATABASES | tr ',' ' '); do
		create_database $db
	done
	echo "Multiple databases created"
fi
