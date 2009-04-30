
-- Move personID to the top of the person table.
alter table person change personID personID int(11) NOT NULL AFTER username;

-- This also changes the default value of username from '' to NULL
alter table person change username username varchar(32) NOT NULL AFTER personID;

-- fix up key for autitItem table
alter table auditItem change auditItemID auditItemID int(11) NOT NULL;

-- change a couple of unsigned int(10) to regular int(11), for consistency
alter table auditItem change entityID entityID int(11) NOT NULL;
alter table auditItem change personID personID int(11) NOT NULL;

