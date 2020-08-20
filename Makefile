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
	@echo "  patches   - combines all DB schema patches into installation file"

doc_html:
	if [ -d ./help/html-images ]; then rm -rf ./help/html-images; fi;
	mkdir -p ./doc/html-images
	mkdir -p ./help/
	cp ./doc/images/* ./doc/html-images/
	cat ./doc/documentation.rst | sed -e 's/\.\/images/html-images/g' > ./doc/documentation.html.rst
	cd ./doc/ && rst2html --link-stylesheet --stylesheet=documentation.css ./documentation.html.rst ./help.html
	mv ./doc/help.html ./doc/html-images/ ./help/
	cp ./doc/documentation.css ./help/documentation.css
	rm -rf ./doc/html-images
	$(MAKE) doc_clean

doc_pdf:
	if [ -d ./doc/pdf-images ]; then rm -rf ./doc/pdf-images; fi;
	mkdir ./doc/pdf-images
	cp ./doc/images/* ./doc/pdf-images/
	find ./doc/images/ -type f -exec mogrify -scale '450x>' {} \;
	cat ./doc/documentation.rst | sed -e 's/\.\/images/pdf-images/g' > ./doc/documentation.pdf.rst
	cd ./doc && rst2latex --documentclass=report --graphicx-option=pdftex --stylesheet=documentation.tss documentation.pdf.rst documentation.tex
	cd ./doc && pdflatex documentation.tex documentation.pdf
	cd ./doc && pdflatex documentation.tex documentation.pdf; # needs two passes to generate table of contents
	if [ -f "./doc/documentation.pdf" ]; then mv ./doc/documentation.pdf ./; fi;
	$(MAKE) doc_clean

doc_clean:
	rm -rf ./doc/documentation.aux ./doc/documentation.log ./doc/documentation.out ./doc/documentation.tex ./doc/html-images ./doc/pdf-images ./doc/documentation.html.rst ./doc/documentation.pdf.rst ./doc/documentation.toc ./doc/missfont.log

dist:
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

clean: ;
none: ;
all: ;
install: ;

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




.PHONY: css help doc services cache patches
