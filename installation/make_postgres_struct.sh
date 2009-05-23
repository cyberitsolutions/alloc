cat db_structure.sql | sed -e 's/integer NOT NULL auto_increment/serial/gi' | sed -e 's/ENGINE=.*/;/g' | grep -vi 'DROP TABLE IF EXISTS' | sed -e 's/datetime /TIMESTAMP WITHOUT TIME ZONE /gi' > db_structure.sql.postgres

