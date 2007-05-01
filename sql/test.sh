#!/bin/bash

set -x 
echo "drop database if exists alloc_test_sql" | mysql -u root
echo "create database alloc_test_sql" | mysql -u root
mysql -u root alloc_test_sql < db_structure.sql
mysql -u root alloc_test_sql < db_data.sql


