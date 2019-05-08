#!/bin/bash


if [ $(id -u) -ne 0 ]; then
	echo "Please run the script as root"
	exit 1
fi

echo "Welcome!"
echo

echo "During installation the script will prompt for entering the root password account of mysql."

mysql -u root -p -e "CREATE DATABASE lado_test"

mysql -D lado_test -u root -p < lado_db2.sql

mkdir phrase_images_related
chown www-data:www-data phrase_images_related
