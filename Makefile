# Copyright (C) 2006-2011 Alex Lance, Clancy Malcolm, Cyber IT Solutions
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
	@echo "  css       - rebuild css/* after a modification to css/src/*"
	@echo "  doc_html  - makes html alloc help"
	@echo "  doc_pdf   - makes pdf alloc help"
	@echo "  doc_clean - makes pdf alloc help"
	@echo "  dist      - makes doc_html, doc_clean, and makes an alloc tarball"
	@echo "  cache     - copies/concatenates all the javascript and css files to a cache directory"
	@echo "  test      - final tests that must pass before a tarball can be built"
	@echo "  patches   - combines all DB schema patches into installation file"

doc_html:
	if [ -d ./help/images ]; then rm -rf ./help/images; fi;
	mkdir ./help/images
	cp ./help/src/images_source/* ./help/images/
	find ./help/images/ -type f -exec mogrify -format gif -scale '750x>' {} \;
	rm ./help/images/*.png
	cat ./help/src/help.txt | sed -e 's/.png/.gif/' > ./help/src/help.gif.txt
	cd ./help/src && rst2html --link-stylesheet --stylesheet=help.css ./help.gif.txt ./help.html
	mv ./help/src/help.html ./help/
	cp ./help/src/help.css ./help/
	$(MAKE) doc_clean

doc_pdf: 
	if [ -d ./help/src/images ]; then rm -rf ./help/src/images; fi;
	mkdir ./help/src/images
	cp ./help/src/images_source/* ./help/src/images/
	find ./help/src/images/ -type f -exec mogrify -scale '450x>' {} \;
	cd ./help/src && rst2latex --documentclass=report --graphicx-option=pdftex --stylesheet=help.tss help.txt help.tex
	cd ./help/src && pdflatex help.tex help.pdf
	cd ./help/src && pdflatex help.tex help.pdf; # needs two passes to generate table of contents
	if [ -f "./help/src/help.pdf" ]; then mv ./help/src/help.pdf ./; fi;
	$(MAKE) doc_clean

doc_clean:
	rm -rf ./help/src/help.aux ./help/src/help.log ./help/src/help.out ./help/src/help.tex ./help/src/images ./help/src/help.gif.txt ./help/src/help.toc ./help/src/missfont.log

dist: test
	if [ -d ./src ]; then rm -rf ./src; fi;
	git clone . ./src/
	rm -rf ./src/.git
	cd ./src && $(MAKE) doc_html; 
	cd ./src && $(MAKE) doc_clean; 
	cd ./src && $(MAKE) cache;
	cd ./src && $(MAKE) patches;
	if [ -d "./src/help/src" ]; then rm -rf ./src/help/src; fi;
	mv ./src ./allocPSA-`cat util/alloc_version`
	tar -czvf allocPSA-`cat util/alloc_version`.tgz allocPSA-`cat util/alloc_version`; 
	rm -rf ./allocPSA-`cat util/alloc_version`;

css: css/src/*
	./util/make_stylesheets.py
	$(MAKE) cache

clean: ;
none: ;
all: ;
install: ;

# Currently only tests Python.  Requires pyflakes and pylint.
test:
	pyflakes bin/alloc
	find -iname '*.py' -exec pyflakes {} +
	PYTHONPATH=$$PYTHONPATH:./bin/alloccli                  \
	# E501 is line lenth, this is best to ignore because 79 chars long is too short.#
	# E241 is whitespace after a ',' or ':'
	find -iname '*.py' -exec flake8 --ignore=E501,E241 --count --benchmark {} +

cache:
	rm -rf cache_`cat util/alloc_version`
	mkdir cache_`cat util/alloc_version`
	cp css/*.css cache_`cat util/alloc_version`/
	for i in javascript/*; do\
	  cat $$i >> cache_`cat util/alloc_version`/javascript.js;\
	done

patches:
	rm -f ./installation/db_patches.sql
	for i in ./patches/*; do \
	echo "INSERT INTO patchLog (patchName, patchDesc, patchDate) VALUES ('`basename $$i`','','1970-01-01 10:00:00');" \
	>> installation/db_patches.sql; done;
	num_tables=$(grep -i "CREATE TABLE" installation/db_structure.sql | wc -l)
	echo "INSERT INTO config (name, type, value) VALUES ('install_data','array',\
	'a:1:{s:10:\"num_tables\";i:$$(grep -i "CREATE TABLE" installation/db_structure.sql | wc -l);}');" \
	>> installation/db_patches.sql;




.PHONY: css help doc services test cache patches
