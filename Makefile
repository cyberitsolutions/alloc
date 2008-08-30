# Copyright (C) 2006, 2007, 2008 Alex Lance, Clancy Malcolm, Cybersource
# Pty. Ltd.
# 
# This file is part of the allocPSA application <info@cyber.com.au>.
# 
# allocPSA is free software: you can redistribute it and/or modify it
# under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or (at
# your option) any later version.
# 
# allocPSA is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
# FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public
# License for more details.
# 
# You should have received a copy of the GNU Affero General Public License
# along with allocPSA. If not, see <http://www.gnu.org/licenses/>.

SHELL = /bin/bash


help:
	@echo "Targets: "
	@echo "  css       - rebuild css/* after a modification to styles/*"
	@echo "  test_db   - tests db_struc/patches against running db."
	@echo "  doc_html  - makes html alloc help"
	@echo "  doc_pdf   - makes pdf alloc help"
	@echo "  doc_clean - makes pdf alloc help"
	@echo "  dist      - makes doc_html, doc_clean, and makes an alloc tarball"

doc_html:
	if [ -d ./help/images ]; then rm -rf ./help/images; fi;
	mkdir ./help/images
	cp ./help/src/images_source/* ./help/images/
	find ./help/images/ -type f -exec mogrify -format gif -scale '750x>' {} \;
	rm ./help/images/*.png
	cat ./help/src/help.txt | sed -e 's/.png/.gif/' > ./help/src/help.gif.txt
	cd ./help/src && rst2html.py --link-stylesheet --stylesheet=help.css ./help.gif.txt ./help.html
	mv ./help/src/help.html ./help/
	cp ./help/src/help.css ./help/
	$(MAKE) doc_clean

doc_pdf: 
	if [ -d ./help/src/images ]; then rm -rf ./help/src/images; fi;
	mkdir ./help/src/images
	cp ./help/src/images_source/* ./help/src/images/
	find ./help/src/images/ -type f -exec mogrify -scale '450x>' {} \;
	cd ./help/src && rst2latex.py --documentclass=report --graphicx-option=pdftex --stylesheet=help.tss help.txt help.tex
	cd ./help/src && pdflatex help.tex help.pdf
	if [ -f "./help/src/help.pdf" ]; then mv ./help/src/help.pdf ./; fi;
	$(MAKE) doc_clean

doc_clean:
	rm -rf ./help/src/help.aux ./help/src/help.log ./help/src/help.out ./help/src/help.tex ./help/src/images ./help/src/help.gif.txt

dist: 
	if [ -d ./src ]; then rm -rf ./src; fi;
	darcs get . ./src/
	rm -rf ./src/_darcs
	cd ./src && $(MAKE) doc_html; 
	cd ./src && $(MAKE) doc_clean; 
	if [ -d "./src/help/src" ]; then rm -rf ./src/help/src; fi;
	mv ./src ./allocPSA-`cat util/alloc_version`
	tar -czvf allocPSA-`cat util/alloc_version`.tgz allocPSA-`cat util/alloc_version`; 
	rm -rf ./allocPSA-`cat util/alloc_version`;

css: styles/*
	./util/make_stylesheets.py

clean: ;

test_db:
	@DB_NAME="$$(cat alloc_config.php  | grep ALLOC_DB_NAME  | sed -e 's/define("ALLOC_DB_NAME","//' | sed -e 's/");$$//')"; \
	DB_USER="$$(cat alloc_config.php  | grep ALLOC_DB_USER  | sed -e 's/define("ALLOC_DB_USER","//' | sed -e 's/");$$//')"; \
	DB_PASS="$$(cat alloc_config.php  | grep ALLOC_DB_PASS  | sed -e 's/define("ALLOC_DB_PASS","//' | sed -e 's/");$$//')"; \
	DB_HOST="$$(cat alloc_config.php  | grep ALLOC_DB_HOST  | sed -e 's/define("ALLOC_DB_HOST","//' | sed -e 's/");$$//')"; \
	[ -n "$$DB_USER" ] && DB_USER="-u $${DB_USER}"; \
	[ -n "$$DB_PASS" ] && DB_PASS="-p$${DB_PASS}"; \
	[ -n "$$DB_HOST" ] && DB_HOST="-h $${DB_HOST}"; \
	TEMP_DB="alloc_test_sql"; \
	MYSQL_CONNECT="$$DB_USER $$DB_PASS $$DB_HOST"; \
  echo test_db: mysql connect string: $${MYSQL_CONNECT}; \
	echo "drop database if exists $${TEMP_DB}" | mysql $$MYSQL_CONNECT; \
	echo "create database $${TEMP_DB}" | mysql $$MYSQL_CONNECT; \
	mysql $${MYSQL_CONNECT} $${TEMP_DB} < sql/db_structure.sql; \
	mysql $${MYSQL_CONNECT} $${TEMP_DB} < sql/db_data.sql; \
	echo "drop database if exists $${TEMP_DB}" | mysql $$MYSQL_CONNECT; \
	echo "create database $${TEMP_DB}" | mysql $$MYSQL_CONNECT; \
	mysql $${MYSQL_CONNECT} $${TEMP_DB} < sql/db_structure.sql; \
	mysqldump -d $$MYSQL_CONNECT $$TEMP_DB > sql/db_imported_structure.sql; \
	mysqldump -d $$MYSQL_CONNECT $$DB_NAME > sql/db_current_structure.sql; \
	echo "drop database if exists $${TEMP_DB}" | mysql $$MYSQL_CONNECT; \
	DIFF="$$(diff -b -I 'Host:' -I 'ENGINE=MyISAM' sql/db_current_structure.sql sql/db_imported_structure.sql)"; \
	if [ -n "$${DIFF}" ]; then \
	  echo "There are differences between the current database $$DB_NAME, and the database " \
		echo "that would be created from the sql/db_structure.sql file."; \
		echo \
	  echo "Please fix either the patch files or sql/db_structure.sql before committing."; \
		echo "diff -b sql/db_current_structure.sql sql/db_imported_structure.sql"; \
		echo "$${DIFF}"; \
		exit 1; \
		echo "test_db: failed";\
	else \
		rm -f sql/db_imported_structure.sql sql/db_current_structure.sql; \
		echo "test_db: passed";\
	fi;

none: ;
all: ;
install: ;

.PHONY: css help doc

