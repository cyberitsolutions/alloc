
DELETE FROM history;
ALTER TABLE history ADD the_args varchar(255) default NULL AFTER the_place;

