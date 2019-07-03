#!/usr/bin/env bash

basedir=$( dirname $( readlink -f ${BASH_SOURCE[0]} ) )

psql -c "CREATE USER phpunit WITH PASSWORD 'phpunit';" -U postgres
psql -c "DROP DATABASE IF EXISTS faqoff_test" -U postgres
psql -c "CREATE DATABASE faqoff_test OWNER phpunit" -U postgres

php ${basedir}/travis-install.php
php ${basedir}/create-test-database.php
