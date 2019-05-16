#!/bin/bash


if [ $(id -u) -ne 0 ]; then
	echo "Please run the script as root"
	exit 1
fi

echo "Welcome!"
echo

echo "During installation the script will prompt for entering the root password account of mysql."

mysql -u root -p -e "CREATE DATABASE lado"

mysql -D lado_test -u root -p < lado_db.sql

mkdir phrase_images_related
chown www-data:www-data phrase_images_related

sudo dpkg -l | grep -E '^ii' | grep -q php.*gd && echo "php-gd module seems to be installed! Great.." || echo "Installing php-gd module..." && apt-get install php-gd -y

echo 
echo

echo "Environment created successfully, you are ready to go!!!! :)"

