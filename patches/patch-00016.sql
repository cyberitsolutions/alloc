-- Create new table patchLog to track database changes that have been applied to this database.
-- CREATE TABLE patchLog (
-- patchLogID int(11) NOT NULL auto_increment,
-- patchName varchar(255) NOT NULL,
-- patchDesc text,
-- patchDate timestamp(14) NOT NULL,
-- PRIMARY KEY  (patchLogID)
-- ) TYPE=ISAM PACK_KEYS=1;

-- Insert a dummy record so that previous patches don't attempt to get applied.
-- insert into patchLog values (1, "patch-16.sql","","2006-11-12 18:04:46");

