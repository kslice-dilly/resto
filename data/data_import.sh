#!/bin/bash
SQLUSER=
SQLPASSWORD=
DB=resto2

mysql -u $SQLUSER -p $SQLPASSWORD < data_import.sql
ln -s businesses.csv business.csv
mysqlimport -d --fields-enclosed-by=\" -p --fields-terminated-by=, --local --ignore-lines=1 $DB business.csv
rm business.csv

mysqlimport -d --fields-enclosed-by=\" -p --fields-terminated-by=, --local --ignore-lines=1 $DB inspections.csv
mysqlimport -d --fields-enclosed-by=\" -p --fields-terminated-by=, --local --ignore-lines=1 $DB violations.csv 
 
